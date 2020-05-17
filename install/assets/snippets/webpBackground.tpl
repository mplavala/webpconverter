//<?php
/**
 * webpBackground
 *
 * Converts image path to webP format and serves them, given that the user's browser supports webP.
 *
 * @category 	snippet
 * @version 	1.0
 * @license 	https://www.gnu.org/licenses/gpl-3.0.html GNU Public License (GPL)
 * @author      mplavala
 * @internal	@properties
 * @internal	@modx_category Content
 */

<?php
require_once(MODX_BASE_PATH . 'assets/plugins/webpconverter/plugin.webpconverter.php');

$src = (isset($src)) ? $src : '';

if( strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false ) {
	// webp is supported!
	echo webpconverter\convert($src);
} else {
	echo $src;
}

