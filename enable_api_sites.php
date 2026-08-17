<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Website;

$sites = ['kalerkantho', 'ekhon', 'banglatribune', 'somoynews', 'bdnews24', 'dbcnews', 'dhakapost', 'jagonews24'];
foreach($sites as $s) {
    $w = Website::where('url', 'like', '%'.$s.'%')->first();
    if($w) {
        $w->update(['use_scraping_api' => 1]);
        echo "✅ Updated: " . $w->name . " (" . $w->url . ")\n";
    } else {
        echo "❌ NOT FOUND: $s\n";
    }
}
