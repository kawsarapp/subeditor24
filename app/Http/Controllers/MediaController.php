<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MediaController extends Controller
{
    private $mediaPath;

    public function __construct()
    {
        // Settings the folder where all media will be saved
        $this->mediaPath = public_path('uploads/studio');
        
        // Create directory if it doesn't exist
        if (!File::exists($this->mediaPath)) {
            File::makeDirectory($this->mediaPath, 0755, true);
        }
    }

    public function index()
    {
        $files = File::files($this->mediaPath);
        
        $mediaFiles = [];
        foreach ($files as $file) {
            $mediaFiles[] = [
                'name' => $file->getFilename(),
                'size' => $this->formatSizeUnits($file->getSize()),
                'type' => $file->getExtension(),
                'url'  => asset('uploads/studio/' . $file->getFilename()),
                'time' => filemtime($file->getPathname())
            ];
        }

        // Sort by time descending (newest first)
        usort($mediaFiles, function($a, $b) {
            return $b['time'] - $a['time'];
        });

        return view('admin.media.index', compact('mediaFiles'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|mimes:png,jpg,jpeg,webp,ttf,otf,woff,woff2|max:10240' // max 10MB
        ]);

        if ($request->hasFile('files')) {
            $uploadedNames = [];
            foreach ($request->file('files') as $file) {
                // Ensure unique name or use original name
                $originalName = $file->getClientOriginalName();
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
                
                $file->move($this->mediaPath, $filename);
                $uploadedNames[] = $filename;
            }
            return response()->json(['success' => true, 'files' => $uploadedNames]);
        }

        return response()->json(['success' => false, 'message' => 'No files uploaded.']);
    }

    public function rename(Request $request)
    {
        $request->validate([
            'old_name' => 'required|string',
            'new_name' => 'required|string',
        ]);

        $oldName = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $request->old_name);
        $newName = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $request->new_name);

        // Make sure it retains extension if not provided
        $oldExt = pathinfo($oldName, PATHINFO_EXTENSION);
        $newExt = pathinfo($newName, PATHINFO_EXTENSION);
        if (!$newExt) {
            $newName .= '.' . $oldExt;
        }

        $oldPath = $this->mediaPath . '/' . $oldName;
        $newPath = $this->mediaPath . '/' . $newName;

        if (File::exists($oldPath)) {
            if (File::exists($newPath)) {
                return response()->json(['success' => false, 'message' => 'এই নামে একটি ফাইল আগে থেকেই আছে।']);
            }
            File::move($oldPath, $newPath);
            return response()->json(['success' => true, 'new_url' => asset('uploads/studio/' . $newName), 'new_name' => $newName]);
        }

        return response()->json(['success' => false, 'message' => 'ফাইল খুঁজে পাওয়া যায়নি।']);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'filename' => 'required|string'
        ]);

        $filename = preg_replace('/[^a-zA-Z0-9_\.-]/', '', $request->filename);
        $path = $this->mediaPath . '/' . $filename;

        if (File::exists($path)) {
            File::delete($path);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'ফাইল খুঁজে পাওয়া যায়নি।']);
    }

    private function formatSizeUnits($bytes)
    {
        if ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }
        return $bytes;
    }
}
