<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
{
    private function checkSuperAdmin()
    {
        if (Auth::user()->role !== 'super_admin') {
            abort(403, 'Only Super Admin can manage templates.');
        }
    }

    /**
     * Template list
     */
    public function index()
    {
        $templates = Template::latest()->get();
        return view('admin.templates.index', compact('templates'));
    }

    /**
     * Create form
     */
    public function create()
    {
        return view('admin.templates.create-edit', ['template' => null]);
    }

    /**
     * Save new template
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'frame_url'   => 'required|url',
            'thumbnail_url' => 'nullable|url',
            'font_url'    => 'nullable|url',
            'layout_data' => 'required|string',
        ]);

        $layoutData = json_decode($request->layout_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['layout_data' => 'Invalid JSON format.'])->withInput();
        }

        Template::create([
            'name'          => $request->name,
            'frame_url'     => $request->frame_url,
            'thumbnail_url' => $request->thumbnail_url ?? $request->frame_url,
            'font_url'      => $request->font_url ?: null,
            'layout_data'   => $layoutData,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.templates.index')
            ->with('success', "✅ '{$request->name}' template সফলভাবে যোগ হয়েছে!");
    }

    /**
     * Edit form
     */
    public function edit($id)
    {
        $template = Template::findOrFail($id);
        return view('admin.templates.create-edit', compact('template'));
    }

    /**
     * Update template
     */
    public function update(Request $request, $id)
    {
        $template = Template::findOrFail($id);

        $request->validate([
            'name'          => 'required|string|max:100',
            'frame_url'     => 'required|url',
            'thumbnail_url' => 'nullable|url',
            'font_url'      => 'nullable|url',
            'layout_data'   => 'required|string',
        ]);

        $layoutData = json_decode($request->layout_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['layout_data' => 'Invalid JSON format.'])->withInput();
        }

        $template->update([
            'name'          => $request->name,
            'frame_url'     => $request->frame_url,
            'thumbnail_url' => $request->thumbnail_url ?? $request->frame_url,
            'font_url'      => $request->font_url ?: null,
            'layout_data'   => $layoutData,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.templates.index')
            ->with('success', "✅ '{$template->name}' আপডেট হয়েছে!");
    }

    /**
     * Toggle active/inactive
     */
    public function toggle($id)
    {
        $template = Template::findOrFail($id);
        $template->is_active = !$template->is_active;
        $template->save();

        $status = $template->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়';
        return back()->with('success', "'{$template->name}' এখন {$status}।");
    }

    /**
     * Delete template
     */
    public function destroy($id)
    {
        $template = Template::findOrFail($id);
        $name = $template->name;
        $template->delete();
        return back()->with('success', "🗑️ '{$name}' ডিলিট হয়েছে।");
    }
}
