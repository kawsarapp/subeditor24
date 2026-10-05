<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LegalPage;
use App\Http\Controllers\PricingController;

class LegalPageController extends Controller
{
    /**
     * Display a specific legal/policy page by slug
     */
    public function show(string $slug)
    {
        $page = LegalPage::where('slug', $slug)->first();

        // If not found in DB, check if it's in default templates
        if (!$page) {
            $defaults = LegalPage::getDefaultPages();
            if (isset($defaults[$slug])) {
                $page = LegalPage::create([
                    'slug'              => $slug,
                    'title'             => $defaults[$slug]['title'],
                    'badge'             => $defaults[$slug]['badge'],
                    'subtitle'          => $defaults[$slug]['subtitle'],
                    'content'           => $defaults[$slug]['content'],
                    'meta_title'        => $defaults[$slug]['meta_title'],
                    'meta_description'  => $defaults[$slug]['meta_description'],
                    'last_updated_date' => $defaults[$slug]['last_updated_date'],
                    'sort_order'        => $defaults[$slug]['sort_order'],
                    'is_active'         => true,
                ]);
            } else {
                abort(404);
            }
        }

        if (!$page->is_active && (!auth()->check() || auth()->user()->role !== 'super_admin')) {
            abort(404);
        }

        $allPages = LegalPage::active()->orderBy('sort_order')->get();
        $pricingConfig = PricingController::getPageConfig();

        return view('pages.legal-show', compact('page', 'allPages', 'pricingConfig'));
    }
}
