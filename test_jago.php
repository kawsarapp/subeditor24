<?php
require __DIR__.'/vendor/autoload.php';
use Symfony\Component\DomCrawler\Crawler;

$html = file_get_contents('out_jago.html');
$cw = new Crawler($html);

$node = $cw->filter('a[href*="/news/"]')->first()->getNode(0);
if ($node) {
    echo "P1: " . $node->parentNode->getAttribute('class') . "\n";
    echo "P2: " . $node->parentNode->parentNode->getAttribute('class') . "\n";
    echo "P3: " . $node->parentNode->parentNode->parentNode->getAttribute('class') . "\n";
}
