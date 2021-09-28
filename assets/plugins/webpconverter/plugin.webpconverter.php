<?php

namespace webpconverter;

if (!defined('MODX_BASE_PATH')) {
    die('What are you doing? Get out of here!');
}

// supported MIME types
define('MIME', ['image/jpeg', 'image/png']);
// root for webp cahce
define('CACHEPATH', 'assets/cache/webp');


function basic_checks($modx) {
    if (!function_exists('mime_content_type')) {
        $modx->logEvent(1, 3, 'PHP function mime_content_type does not exist.', 'WebP Converter - function does not exist');
        return false;
    }
    if (!function_exists('imagewebp')) {
        $modx->logEvent(1, 3, 'PHP function imagewebp does not exist.', 'WebP Converter - function does not exist');
        return false;
    }
    return true;
}


function check_cache_folder() {
    $path = MODX_BASE_PATH . CACHEPATH;
    if (!file_exists($path)) {
        // folder does not exist
        mkdir($path, 0777, true);
    }
    if (!file_exists($path . '/.htaccess')) {
        // there is no .htaccess
        file_put_contents($path . '/.htaccess', "order deny,allow\nallow from all\n");
    }
}


function serve_node($node, $atribute, $modx, $debug) {
    if ($node->getAttribute('data-webpconverter-exclude') == 1 ||
        $node->getAttribute('data-webpconverter-exclude') == 'on' ||
        $node->getAttribute('data-webpconverter-exclude') == 'yes') {
        // the attribure data-webpconverter-exclude exists and turned on
        return;
    }
    if ($node->getAttribute($atribute)) {
        // the attribute exists, we can handle it
        $src = $node->getAttribute($atribute);
        $node->setAttribute($atribute, convert($src, $modx, $debug));
    }
}


function get_absolute_path($path, $URI) {
    if ($path[0] != '/') {
    // relative path
    $URIArray = explode('/', $URI);
    array_pop($URIArray);
    $container = implode('/', $URIArray);
    // return absolute path with container
    return $container . '/' . $path;
    } else {
    // absolute path, just return original
    return $path;
    }
}


function get_webp_filename($fileName) {
    return $fileName . '.webp';
}


function get_webp_path($path) {
    // remove assets from path and explode
    $path = str_replace('assets', '', $path);
    $pathArray = explode('/', $path);

    // we start from $cahcePath
    $finalPath = CACHEPATH;

    // add folder structure to $cachePath
    foreach ($pathArray as $folder) {
        if (!empty($folder)) {
            $finalPath .= '/' . $folder;
        }
    }
    // return final path, startin with /
    return '/' . $finalPath;
}


function convert($srcIn, $modx, $debug) {

    // check cache folder first
    check_cache_folder();

    // check the plugin settings
    $debug = isset($debug) ? $debug : 'No'; // debug flag
    $debug = ($debug == 'No') ? false : true;

    $src = rawurldecode($srcIn);
    // change relative path to absolute path, starting with /
    $src = get_absolute_path($src, $_SERVER['REQUEST_URI']);

    $srcMime = '';

    // file path on server, including file name
    $srcServerFile = MODX_BASE_PATH . ltrim($src, '/');

    if (file_exists($srcServerFile)) {
        // we set the MIME type as variable to test whether it is supported
        // if file_exists evaulates to false, then we return original input
        $srcMime = mime_content_type($srcServerFile);
    } else {
        if ($debug) {
            $modx->logEvent(1, 2, 'Image ' . $srcIn . ' does not exist. Either there is no file at ' . $srcServerFile . ', or the path is not accessible.', 'WebP Converter - image does not exist');
            }
        return $srcIn;
    }

    if (!in_array($srcMime, MIME)) {
        // unsuported MIME type or image does not exist
        // returning original input
        if ($debug) {
            if ($srcMime == '') {
                $modx->logEvent(1, 2, 'The MIME type of image ' . $srcIn . ' cannot be correctly detected.', 'WebP Converter - MIME type cannot be detected');
            } else {
                $modx->logEvent(1, 2, 'Unsuported MIME type. Image ' . $srcIn . ' has MIME type ' . $srcMime . '. Only ' . implode(', ', MIME) . ' are supported.', 'WebP Converter - unsupported MIME type');
            }
        }
        return $srcIn;
    }

    $filename = pathinfo($src)['basename'];
    $path = pathinfo($src)['dirname'];

    // create new file name and path
    $webpFileName = get_webp_filename($filename);
    $webpPath = get_webp_path($path);
    // webp absolute path for src
    $webpSrc = $webpPath . '/' . $webpFileName;

    // webp path on server, without file name
    $webpServerPath = MODX_BASE_PATH . ltrim($webpPath, '/');
    // webp path on server, with file name
    $webpServerFile = $webpServerPath . '/' . $webpFileName;

    if (!file_exists($webpServerFile) or (filectime($webpServerFile) < filectime($srcServerFile))) {
        // image does not exist or is outdated
        if ($srcMime == 'image/jpeg') {
            $image =  imagecreatefromjpeg($srcServerFile);
            imagepalettetotruecolor($image);
        }
        if ($srcMime == 'image/png') {
            $image =  imagecreatefrompng($srcServerFile);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }
        if (!file_exists($webpServerPath)) {
            // folder does not exist
            mkdir($webpServerPath, 0777, true);
        }
        // create webp image
        imagewebp($image, $webpServerFile);
        // free up memory
        imagedestroy($image);
    }
    if (file_exists($webpServerFile) && filesize($webpServerFile) > 0 ) {
        // make sure the file really exists and that it is not a damaged
        if (filesize($webpServerFile) < filesize($srcServerFile)) {
            // the WebP image is smaller
            return $webpSrc;
        } else {
            // the original image is smaller
            if ($debug) {
                $modx->logEvent(1, 2, 'Image ' . $srcIn . ' was supposed to be replaced by ' . $webpServerFile . ', but the WebP version is larger than original.', 'WebP Converter - WebP image larger than original');
            }
            return $srcIn;
        }
    }

    // file creation failed or file doesn't exist
    // returning original input
    if ($debug) {
        $modx->logEvent(1, 3, 'Image ' . $srcIn . ' was supposed to be replaced by ' . $webpServerFile . ', but the WebP version does not exist, or is corrupted.', 'WebP Converter - WebP image failure');
    }
    return $srcIn;
}

