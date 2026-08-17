<?php
// Show first 3000 chars of bdnews24 HTML to understand structure
$html = file_get_contents('out_bdnews24.html');
echo "Total length: " . strlen($html) . "\n";
echo substr($html, 0, 3000);
