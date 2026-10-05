<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LegalPage;
use Illuminate\Support\Str;

class LegalPageAdminController extends Controller
{
    /**
     * List all legal & policy pages
     */
    public function index()
    {
        $pages = LegalPage::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.legal-pages.index', compact('pages'));
    }

    /**
     * Show edit form for a legal page
     */
    public function edit(int $id)
    {
        $page = LegalPage::findOrFail($id);
        return view('admin.legal-pages.edit', compact('page'));
    }

    /**
     * Show create form for a new custom page
     */
    public function create()
    {
        $page = new LegalPage([
            'is_active'         => true,
            'sort_order'        => (LegalPage::max('sort_order') ?? 0) + 1,
            'last_updated_date' => date('F d, Y'),
        ]);
        return view('admin.legal-pages.edit', compact('page'));
    }

    /**
     * Save / Update legal page
     */
    public function save(Request $request)
    {
        $id = $request->input('id');

        $rules = [
            'title'             => 'required|string|max:255',
            'slug'              => 'required|string|max:255|unique:legal_pages,slug,' . ($id ?? 'NULL'),
            'badge'             => 'nullable|string|max:255',
            'subtitle'          => 'nullable|string|max:500',
            'content'           => 'required|string',
            'meta_title'        => 'nullable|string|max:255',
            'meta_description'  => 'nullable|string|max:500',
            'last_updated_date' => 'nullable|string|max:100',
            'sort_order'        => 'nullable|integer',
        ];

        $validated = $request->validate($rules);
        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = (int)($validated['sort_order'] ?? 0);

        if ($id) {
            $page = LegalPage::findOrFail($id);
            $page->update($validated);
            $msg = '✅ ' . $page->title . ' পেজটি সফলভাবে আপডেট করা হয়েছে!';
        } else {
            $page = LegalPage::create($validated);
            $msg = '✅ নতুন পলিসি পেজ সফলভাবে তৈরি করা হয়েছে!';
        }

        return redirect()->route('admin.legal-pages.index')->with('success', $msg);
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(int $id)
    {
        $page = LegalPage::findOrFail($id);
        $page->is_active = !$page->is_active;
        $page->save();

        return response()->json([
            'success'   => true,
            'is_active' => $page->is_active,
            'message'   => 'স্ট্যাটাস সফলভাবে পরিবর্তন করা হয়েছে!'
        ]);
    }

    /**
     * Reset a default page to its original template
     */
    public function resetToDefault(int $id)
    {
        $page = LegalPage::findOrFail($id);
        $defaults = LegalPage::getDefaultPages();

        if (isset($defaults[$page->slug])) {
            $default = $defaults[$page->slug];
            $page->update([
                'title'             => $default['title'],
                'badge'             => $default['badge'],
                'subtitle'          => $default['subtitle'],
                'content'           => $default['content'],
                'meta_title'        => $default['meta_title'],
                'meta_description'  => $default['meta_description'],
                'last_updated_date' => $default['last_updated_date'],
            ]);

            return back()->with('success', '✅ ' . $page->title . ' এর কনটেন্ট ডিফল্ট টেমপ্লেটে রিসেট করা হয়েছে!');
        }

        return back()->with('error', 'এই পেজের জন্য কোনো ডিফল্ট টেমপ্লেট নেই।');
    }

    /**
     * Delete custom page (protect core default pages from deletion)
     */
    public function delete(int $id)
    {
        $page = LegalPage::findOrFail($id);
        $protectedSlugs = ['privacy-policy', 'terms-and-conditions', 'refund-policy', 'contact-us', 'about-us'];

        if (in_array($page->slug, $protectedSlugs)) {
            return back()->with('error', '❌ এই মূল আইনি পেজটি মুছে ফেলা যাবে না। প্রয়োজনবোধে নিষ্ক্রিয় (Inactive) করতে পারেন।');
        }

        $page->delete();
        return redirect()->route('admin.legal-pages.index')->with('success', '🗑️ পেজটি সফলভাবে মুছে ফেলা হয়েছে।');
    }
}
