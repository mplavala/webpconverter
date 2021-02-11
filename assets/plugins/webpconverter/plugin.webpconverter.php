<?php

namespace webpconverter;

define('MIME', ['image/jpeg', 'image/png']);

function serve_node($node, $atribute) {
    $src = $node->getAttribute($atribute);
    $node->setAttribute($atribute, convert($src));
}

function convert($srcIn) {
    $src = urldecode($srcIn);
    $src = ltrim($src, '/');

    if (in_array(@mime_content_type($src), MIME)) {
        $srcArray = explode('.', $src);
        $srcArray[count($srcArray) - 1] = 'webp';
        $webpSrc = implode('.', $srcArray);
        $webpSrcArray = explode('/', $webpSrc);
        array_splice($webpSrcArray, 1, 0, '.webp');
        $webpSrc = implode('/', $webpSrcArray);
        if (!file_exists($webpSrc) or (filectime($webpSrc) < filectime($src))) {
            // image does not exist or is outdated
            if (mime_content_type($src) == 'image/jpeg') {
                $image =  imagecreatefromjpeg($src);
            }
            if (mime_content_type($src) == 'image/png') {
                $image =  imagecreatefrompng($src);
            }
            array_pop($webpSrcArray);
            $webpSrcDir = implode('/', $webpSrcArray);
            if (!file_exists($webpSrcDir)) {
                // folder does not exist
                mkdir($webpSrcDir, 0755, true);
            }
            imagewebp($image, $webpSrc);
        }
        if (file_exists($webpSrc) && filesize($webpSrc) > 0 ) {
            // make sure the file really exists 
            // and is not a damaged file
            return '/' . $webpSrc;
        }
    }
    
    // either unsupported MIME type or file creation failed
    // returning original input
    return $srcIn;
}
