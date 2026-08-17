<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Website;

// এই সাইটগুলোর জন্য Universal API দরকার নেই
$revert = ['dbcnews', 'dhakapost', 'jagonews24', 'ekhon'];
foreach($revert as $s) {
    $w = Website::where('url', 'like', '%'.$s.'%')->first();
    if($w) {
        $w->update(['use_scraping_api' => 0]);
        echo "↩️ Reverted: " . $w->name . " (" . $w->url . ")\n";
    } else {
        echo "❌ NOT FOUND: $s\n";
    }
}
