<?php

namespace webpconverter;

if (!defined('MODX_BASE_PATH')) {
    die('What are you doing? Get out of here!');
}

//supported MIME types
define('MIME', ['image/jpeg', 'image/png']);
// root for webp cahce
define('CACHEPATH', 'assets/cache/webp');


function basic_checks() {
    return function_exists('mime_content_type') and function_exists('imagewebp');
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


function serve_node($node, $atribute) {
    if ($node->getAttribute($atribute)) {
        // the attribute exists, we can handle it
        $src = $node->getAttribute($atribute);
        $node->setAttribute($atribute, convert($src));
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


function convert($srcIn) {

    // check cache folder first
    check_cache_folder();

    $src = rawurldecode($srcIn);
    // change relative path to absolute path, starting with /
    $src = get_absolute_path($src, $_SERVER['REQUEST_URI']);

    $srcMime = '';

    // file path on server, including file name
    $srcServerFile = MODX_BASE_PATH . ltrim($src, '/');

    if (file_exists($srcServerFile)) {
        // we set the MIME type as variable to test whether it is supported
        // if file_exists evaulates to false, then $srcMime is empty, hence not valid MIME type
        // if MIME type is not valid, then we return the original path later
        $srcMime = mime_content_type($srcServerFile);
    }

    if (in_array($srcMime, MIME)) {
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
            // make sure the file really exists and that is not a damaged file (size greater than 0)
            return $webpSrc;
        }
    }

    // either unsupported MIME type or file creation failed
    // returning original input
    return $srcIn;
}

