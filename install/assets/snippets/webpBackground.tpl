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
 * @reportissues https://github.com/mplavala/webpconverter/issues/
 * @documentation Github repository https://github.com/mplavala/webpconverter
 * @internal	@properties &debug=Log debugging messages;list;No,Yes;No
 * @internal	@modx_category Content
 */

require_once(MODX_BASE_PATH . 'assets/plugins/webpconverter/plugin.webpconverter.php');

$src = (isset($src)) ? $src : '';

if ((strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false or strpos( $_SERVER['HTTP_USER_AGENT'], 'Firefox' ) !== false) and webpconverter\basic_checks($modx)) {
	// webp is supported or the browser is Firefox which does not send image/webp in HTTP_ACCEPT header!
	// and we have all the needed functions
	echo webpconverter\convert($src, $modx, $debug);
} else {
	echo $src;
}

