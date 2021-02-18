<?php

namespace webpconverter;

define('MIME', ['image/jpeg', 'image/png']);

function serve_node($node, $atribute) {
	if ($node->getAttribute($atribute)) {
		// the attribute exists, we can handle it
	    $src = $node->getAttribute($atribute);
	    $node->setAttribute($atribute, convert($src));
    }
}

function get_webp_filename($fileName) {
    return $fileName . '.webp';
}

function get_webp_path($path) {
	$pathArray = explode('/', $path);
	// look for assets folder in array
	$index = array_search('assets', $pathArray);
	if (($index === false) or !is_int($index)) {
		// non-standard scenario, just add .webp to the begining
		array_unshift($pathArray, '.webp');
	} else {
		// standard scenario, add .webp after assets
		array_splice($pathArray, $index+1, 0, '.webp');
	}
	return implode('/', $pathArray);
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
    	$fileName = pathinfo($src)['basename'];
    	$path = pathinfo($src)['dirname'];
    	// create new file name and path
		$webpFileName = get_webp_filename($fileName);
		$webpPath = get_webp_path($path);
		
        $webpSrc = $webpPath . '/' . $webpFileName;

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
            if (!file_exists($webpPath)) {
                // folder does not exist
                mkdir($webpPath, 0755, true);
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

