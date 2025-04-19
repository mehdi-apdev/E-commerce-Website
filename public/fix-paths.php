<?php
$directory = __DIR__; // Racine de /public
$recursive = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
$pattern = '/\/my-eshop\/public/';

foreach ($recursive as $file) {
    if ($file->isFile() && in_array($file->getExtension(), ['js', 'html', 'css'])) {
        $content = file_get_contents($file->getPathname());

        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, '/', $content);
            file_put_contents($file->getPathname(), $newContent);
            echo "✅ Corrigé : " . $file->getPathname() . "<br>";
        }
    }
}
?>
