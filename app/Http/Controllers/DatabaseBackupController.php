<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DatabaseBackupService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupController extends Controller
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Check if current user is authorized super admin
     */
    protected function authorizeSuperAdmin(): void
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'অননুমোদিত অ্যাক্সেস! শুধুমাত্র Super Admin ডেটাবেজ ব্যাকআপ ও রিস্টোর পরিচালনা করতে পারবেন।');
        }
    }

    /**
     * List all backups (AJAX JSON)
     */
    public function index()
    {
        $this->authorizeSuperAdmin();
        $backups = $this->backupService->listBackups();

        return response()->json([
            'success' => true,
            'backups' => $backups,
        ]);
    }

    /**
     * Create a new backup
     */
    public function create(Request $request)
    {
        $this->authorizeSuperAdmin();

        $compress = $request->boolean('compress', true);

        try {
            $result = $this->backupService->createBackup($compress);

            if ($request->boolean('download_now')) {
                return response()->download($result['path'], $result['filename']);
            }

            return response()->json([
                'success' => true,
                'message' => '✅ ডেটাবেজ ব্যাকআপ সফলভাবে সম্পন্ন হয়েছে!',
                'backup'  => $result,
                'backups' => $this->backupService->listBackups(),
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Database Backup Failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ ব্যাকআপ তৈরিতে ত্রুটি: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download an existing backup file
     */
    public function download(Request $request, ?string $filename = null)
    {
        $this->authorizeSuperAdmin();

        $file = $filename ?? $request->query('file') ?? $request->query('filename');
        if (empty($file)) {
            return redirect()->back()->with('error', 'ব্যাকআপ ফাইলের নাম দেওয়া হয়নি।');
        }

        $path = $this->backupService->getBackupPath($file);
        if (!$path || !file_exists($path)) {
            return redirect()->back()->with('error', 'ব্যাকআপ ফাইলটি পাওয়া যায়নি বা মুছে ফেলা হয়েছে।');
        }

        $mime = str_ends_with(strtolower($file), '.gz') ? 'application/gzip' : 'application/sql';

        return response()->download($path, basename($file), [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'attachment; filename="' . basename($file) . '"'
        ]);
    }

    /**
     * Restore database from existing file OR uploaded file
     */
    public function restore(Request $request)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'filename'    => 'nullable|string',
            'backup_file' => 'nullable|file|max:102400', // max 100MB
        ]);

        $filePath = null;

        if ($request->hasFile('backup_file')) {
            $file = $request->file('backup_file');
            $extension = strtolower($file->getClientOriginalExtension());
            
            // Allow .sql, .gz
            if (!in_array($extension, ['sql', 'gz'])) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ শুধুমাত্র .sql অথবা .sql.gz ফাইল আপলোড করা যাবে।',
                ], 422);
            }

            $tempName = 'uploaded_' . time() . '_' . $file->getClientOriginalName();
            $targetDir = storage_path('app/backups');
            $file->move($targetDir, $tempName);
            $filePath = "{$targetDir}/{$tempName}";
        } elseif ($request->filled('filename')) {
            $filePath = $this->backupService->getBackupPath($request->input('filename'));
        }

        if (!$filePath || !file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => '❌ রিস্টোর করার জন্য ব্যাকআপ ফাইল পাওয়া যায়নি।',
            ], 404);
        }

        try {
            $result = $this->backupService->restoreBackup($filePath, true);

            return response()->json([
                'success' => true,
                'message' => '🎉 ডেটাবেজ সফলভাবে রিস্টোর করা হয়েছে! (' . $result['queries_count'] . ' টি কুয়েরি এক্সিকিউট হয়েছে)',
                'details' => $result,
                'backups' => $this->backupService->listBackups(),
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Database Restore Failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ ডেটাবেজ রিস্টোরে সমস্যা হয়েছে: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a backup file
     */
    public function destroy(Request $request, ?string $filename = null)
    {
        $this->authorizeSuperAdmin();

        $file = $filename ?? $request->input('filename') ?? $request->query('filename') ?? $request->query('file');
        if (empty($file)) {
            return response()->json([
                'success' => false,
                'message' => '❌ ফাইলের নাম প্রদান করা হয়নি।'
            ], 400);
        }

        $deleted = $this->backupService->deleteBackup($file);

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => '🗑️ ব্যাকআপ ফাইল সফলভাবে মুছে ফেলা হয়েছে।',
                'backups' => $this->backupService->listBackups(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => '❌ ব্যাকআপ ফাইল মুছতে ব্যর্থ হয়েছে বা ফাইলটি পাওয়া যায়নি।',
        ], 400);
    }
}
