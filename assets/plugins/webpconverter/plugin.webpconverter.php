<?php

namespace webpconverter;

define('MIME', ['image/jpeg', 'image/png']);

function serve_node($node, $atribute) {
    $src = $node->getAttribute($atribute);
    $node->setAttribute($atribute, convert($src));
}

function convert($srcIn) {
    $src = urldecode($srcIn);

    $srcMime = '';
    
    if (file_exists($src)) {
    	// we set the MIME type as variable to test whether it is supported
    	// if file_exists evaulates to false, then $srcMime is empty, hence not valid MIME type
	    $srcMime = mime_content_type($src);
	}

    if (in_array($srcMime, MIME)) {
    	// create new file name - change extension
        $srcArray = explode('.', $src);
        $srcArray[count($srcArray) - 1] = 'webp';
        $webpSrc = implode('.', $srcArray);
    	// create new file name - add .webp folder
    	// TODO this only works for URLs not begining with /, this has to be fixed!
        $webpSrcArray = explode('/', $webpSrc);
        array_splice($webpSrcArray, 1, 0, '.webp');
        $webpSrc = implode('/', $webpSrcArray);
        if (!file_exists($webpSrc) or (filectime($webpSrc) < filectime($src))) {
            // image does not exist or is outdated
            if ($srcMime == 'image/jpeg') {
                $image =  imagecreatefromjpeg($src);
                imagepalettetotruecolor($image);
            }
            if ($srcMime == 'image/png') {
                $image =  imagecreatefrompng($src);
                imagepalettetotruecolor($image);
				imagealphablending($image, true);
				imagesavealpha($image, true);
            }
            array_pop($webpSrcArray);
            $webpSrcDir = implode('/', $webpSrcArray);
            if (!file_exists($webpSrcDir)) {
                // folder does not exist
                mkdir($webpSrcDir, 0755, true);
            }
            // create webp image
            imagewebp($image, $webpSrc);
            // free up memory
            imagedestroy($image);
        }
        if (file_exists($webpSrc) && filesize($webpSrc) > 0 ) {
            // make sure the file really exists 
            // and is not a damaged file
            return $webpSrc;
        }
    }
    
    // either unsupported MIME type or file creation failed
    // returning original input
    return $srcIn;
}
