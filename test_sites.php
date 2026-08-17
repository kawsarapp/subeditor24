<?php
require __DIR__.'/vendor/autoload.php';
use Symfony\Component\DomCrawler\Crawler;

function analyzeLinks($htmlFile, $domain, $label) {
    echo "===== $label =====\n";
    $html = file_get_contents($htmlFile);
    $cw = new Crawler($html);

    $found = $cw->filter('a')->each(function($n) use($domain) {
        $href = $n->attr('href') ?? '';
        $text = trim($n->text());
        if (strlen($text) > 40 && str_contains($href, $domain)) {
            $pClass = $n->getNode(0)->parentNode->getAttribute('class');
            return ['href'=>$href, 'text'=>substr($text,0,60), 'parent_class'=>$pClass, 'a_class'=>$n->attr('class')];
        }
        return null;
    });
    print_r(array_slice(array_filter($found), 0, 5));
}

// fetch bdnews24
$html = file_get_contents('https://bangla.bdnews24.com/archive');
file_put_contents('out_bdnews24.html', $html);
analyzeLinks('out_bdnews24.html', 'bdnews24.com', 'BDNews24 Bangla');
