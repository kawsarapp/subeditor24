<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSetting;
use App\Models\CreditHistory;
use App\Models\BgRemoveLog;
use App\Models\Template;
use App\Models\NewsItem;
use App\Services\PhotoRoomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomPhotoCardController extends Controller
{
    protected PhotoRoomService $photoRoomService;
    protected string $mediaPath;

    public function __construct(PhotoRoomService $photoRoomService)
    {
        $this->photoRoomService = $photoRoomService;
        $this->mediaPath = public_path('uploads/studio');

        if (!File::exists($this->mediaPath)) {
            File::makeDirectory($this->mediaPath, 0755, true);
        }
    }

    /**
     * Display the Custom Photo Card Studio
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // 1. Fetch available PNG frames from uploads/studio
        $frames = [];
        if (File::exists($this->mediaPath)) {
            $files = File::files($this->mediaPath);
            foreach ($files as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['png', 'webp', 'jpg', 'jpeg'])) {
                    $imgInfo = @getimagesize($file->getPathname());
                    $width = $imgInfo[0] ?? null;
                    $height = $imgInfo[1] ?? null;

                    $frames[] = [
                        'name' => $file->getFilename(),
                        'url'  => asset('uploads/studio/' . $file->getFilename()),
                        'width' => $width,
                        'height' => $height,
                        'aspect_ratio' => ($width && $height) ? round($width / $height, 2) : null,
                        'is_png' => ($ext === 'png'),
                        'time' => filemtime($file->getPathname())
                    ];
                }
            }

            usort($frames, fn($a, $b) => $b['time'] - $a['time']);
        }

        // 2. Fetch existing DB templates
        $dbTemplates = Template::where('is_active', true)->latest()->get();

        // 3. Optional News Item
        $newsItem = null;
        if ($request->has('news_id')) {
            $newsItem = NewsItem::withoutGlobalScopes()->find($request->input('news_id'));
        }

        // 4. Fetch dynamic uploaded fonts from uploads/studio
        $dynamicMediaFonts = [];
        if (File::exists($this->mediaPath)) {
            $fontExts = ['ttf', 'otf', 'woff', 'woff2'];
            foreach (File::files($this->mediaPath) as $f) {
                $ext = strtolower($f->getExtension());
                if (in_array($ext, $fontExts)) {
                    $rawName = pathinfo($f->getFilename(), PATHINFO_FILENAME);
                    $cleanName = preg_replace('/^\d+_/', '', $rawName);
                    $cleanName = trim(str_replace(['_', '-'], ' ', $cleanName));
                    $fmt = $ext === 'ttf' ? 'truetype' : ($ext === 'otf' ? 'opentype' : $ext);
                    $dynamicMediaFonts[] = [
                        'family' => $cleanName,
                        'url'    => asset('uploads/studio/' . $f->getFilename()),
                        'format' => $fmt,
                    ];
                }
            }
        }

        // 5. User credit and limit info
        $creditCost = (int) (UserSetting::getSettingWithFallback($user->id, 'bg_remove_credit_cost') ?: 1);
        $dailyUsed = $user->todays_bg_remove_count ?? 0;
        $dailyLimit = $user->daily_bg_remove_limit ?? 20;

        return view('news.custom-photo-card.index', compact(
            'frames',
            'dbTemplates',
            'newsItem',
            'dynamicMediaFonts',
            'creditCost',
            'dailyUsed',
            'dailyLimit'
        ));
    }

    /**
     * AJAX: Get media frames list
     */
    public function getMediaFrames()
    {
        $frames = [];
        if (File::exists($this->mediaPath)) {
            $files = File::files($this->mediaPath);
            foreach ($files as $file) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['png', 'webp', 'jpg', 'jpeg'])) {
                    $imgInfo = @getimagesize($file->getPathname());
                    $frames[] = [
                        'name' => $file->getFilename(),
                        'url'  => asset('uploads/studio/' . $file->getFilename()),
                        'width' => $imgInfo[0] ?? null,
                        'height' => $imgInfo[1] ?? null,
                        'is_png' => ($ext === 'png'),
                        'time' => filemtime($file->getPathname())
                    ];
                }
            }
            usort($frames, fn($a, $b) => $b['time'] - $a['time']);
        }

        return response()->json(['success' => true, 'frames' => $frames]);
    }

    /**
     * Remove Background using PhotoRoom API (Secure Backend Proxy)
     */
    public function removeBackground(Request $request)
    {
        $user = Auth::user();

        // 1. Permission check
        if (!$user->hasPermission('can_custom_photo_card') && $user->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'আপনার এই ফিচারে প্রবেশের অনুমতি নেই।'
            ], 403);
        }

        // 2. Daily limit check
        if (!$user->hasDailyBgRemoveLimitRemaining()) {
            return response()->json([
                'success' => false,
                'message' => "আপনার আজকের দৈনিক ব্যাকগ্রাউন্ড রিমুভ লিমিট ({$user->daily_bg_remove_limit}টি) শেষ হয়ে গেছে।"
            ], 429);
        }

        // 3. Credit cost check
        $creditCost = (int) (UserSetting::getSettingWithFallback($user->id, 'bg_remove_credit_cost') ?: 1);
        if ($user->role !== 'super_admin' && $user->credits < $creditCost) {
            return response()->json([
                'success' => false,
                'message' => "অপর্যাপ্ত ক্রেডিট! ব্যাকগ্রাউন্ড রিমুভ করতে অন্তত {$creditCost} ক্রেডিট প্রয়োজন।"
            ], 402);
        }

        // 4. Validate input
        $request->validate([
            'image' => 'required', // Can be file or base64 or URL
        ]);

        $imageSource = null;
        if ($request->hasFile('image')) {
            $imageSource = $request->file('image');
        } elseif ($request->filled('image')) {
            $imageSource = $request->input('image');
        }

        if (!$imageSource) {
            return response()->json([
                'success' => false,
                'message' => 'কোনো ইমেজ সোর্স পাওয়া যায়নি।'
            ], 400);
        }

        // 5. Call PhotoRoom Service
        $result = $this->photoRoomService->removeBackground($imageSource);

        if ($result['success']) {
            // Deduct credits if not super admin
            if ($user->role !== 'super_admin' && $creditCost > 0) {
                $user->decrement('credits', $creditCost);

                CreditHistory::create([
                    'user_id' => $user->id,
                    'amount' => -$creditCost,
                    'type' => 'bg_remove',
                    'description' => 'AI Background Removal',
                ]);
            }

            // Log usage
            BgRemoveLog::create([
                'user_id' => $user->id,
                'original_image_url' => is_string($imageSource) ? Str::limit($imageSource, 500) : 'uploaded_file',
                'output_image_url' => $result['url'],
                'credits_deducted' => ($user->role === 'super_admin') ? 0 : $creditCost,
                'status' => 'success',
            ]);

            return response()->json([
                'success' => true,
                'output_url' => $result['url'],
                'remaining_credits' => $user->credits,
                'daily_used' => $user->todays_bg_remove_count,
                'daily_limit' => $user->daily_bg_remove_limit ?? 20,
                'message' => 'ব্যাকগ্রাউন্ড সফলভাবে রিমুভ করা হয়েছে!'
            ]);
        }

        // Log failure
        BgRemoveLog::create([
            'user_id' => $user->id,
            'original_image_url' => is_string($imageSource) ? Str::limit($imageSource, 500) : 'uploaded_file',
            'output_image_url' => null,
            'credits_deducted' => 0,
            'status' => 'failed',
            'error_message' => $result['error'],
        ]);

        return response()->json([
            'success' => false,
            'message' => $result['error'] ?? 'ব্যাকগ্রাউন্ড রিমুভ করতে সমস্যা হয়েছে।'
        ], 500);
    }

    /**
     * Upload custom PNG frame
     */
    public function uploadFrame(Request $request)
    {
        $request->validate([
            'frame' => 'required|image|mimes:png,webp|max:10240' // max 10MB
        ]);

        if ($request->hasFile('frame')) {
            $file = $request->file('frame');
            $originalName = $file->getClientOriginalName();
            $filename = 'frame_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            
            $file->move($this->mediaPath, $filename);
            $fullPath = $this->mediaPath . '/' . $filename;
            $imgInfo = @getimagesize($fullPath);

            return response()->json([
                'success' => true,
                'filename' => $filename,
                'url' => asset('uploads/studio/' . $filename),
                'width' => $imgInfo[0] ?? null,
                'height' => $imgInfo[1] ?? null,
            ]);
        }

        return response()->json(['success' => false, 'message' => 'ফ্রেম আপলোড ব্যর্থ হয়েছে।']);
    }

    /**
     * Save customized template layout
     */
    public function saveTemplate(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'layout_data' => 'required',
            'thumbnail' => 'nullable|string',
        ]);

        try {
            $thumbnailUrl = null;
            if ($request->filled('thumbnail')) {
                $thumbData = $request->input('thumbnail');
                if (preg_match('/^data:image\/(\w+);base64,/', $thumbData, $type)) {
                    $thumbData = substr($thumbData, strpos($thumbData, ',') + 1);
                    $decoded = base64_decode($thumbData);
                    $thumbDir = public_path('uploads/custom_templates');
                    if (!File::exists($thumbDir)) {
                        File::makeDirectory($thumbDir, 0755, true);
                    }
                    $thumbFile = 'thumb_' . time() . '_' . Str::random(6) . '.png';
                    file_put_contents($thumbDir . '/' . $thumbFile, $decoded);
                    $thumbnailUrl = asset('uploads/custom_templates/' . $thumbFile);
                }
            }

            $layout = is_string($request->input('layout_data'))
                ? json_decode($request->input('layout_data'), true)
                : $request->input('layout_data');

            $template = Template::create([
                'name' => $request->input('name'),
                'thumbnail_url' => $thumbnailUrl,
                'frame_url' => $request->input('frame_url'),
                'layout_data' => $layout,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'template' => [
                    'id' => $template->id,
                    'name' => $template->name,
                    'thumbnail_url' => $template->thumbnail_url,
                    'frame_url' => $template->frame_url,
                    'layout_data' => $template->layout_data,
                ],
                'message' => 'টেমপ্লেট সফলভাবে সংরক্ষিত হয়েছে!'
            ]);
        } catch (\Exception $e) {
            Log::error("Save Template Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete custom template
     */
    public function deleteTemplate($id)
    {
        try {
            $template = Template::findOrFail($id);
            $template->delete();
            return response()->json(['success' => true, 'message' => 'টেমপ্লেট মুছে ফেলা হয়েছে!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
