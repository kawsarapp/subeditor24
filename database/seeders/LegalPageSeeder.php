<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LegalPage;

class LegalPageSeeder extends Seeder
{
    /**
     * Seed default legal pages
     */
    public function run(): void
    {
        $defaults = LegalPage::getDefaultPages();

        foreach ($defaults as $slug => $data) {
            LegalPage::updateOrCreate(
                ['slug' => $slug],
                [
                    'title'             => $data['title'],
                    'badge'             => $data['badge'],
                    'subtitle'          => $data['subtitle'],
                    'content'           => $data['content'],
                    'meta_title'        => $data['meta_title'],
                    'meta_description'  => $data['meta_description'],
                    'last_updated_date' => $data['last_updated_date'],
                    'sort_order'        => $data['sort_order'],
                    'is_active'         => true,
                ]
            );
        }
    }
}
