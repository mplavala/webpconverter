//<?php
/**
 * WebP Converter
 * 
 * Converts all images on the page to webp format and serves them, given that the user's browser supports webP.
 *
 * @category 	plugin
 * @version 	1.0
 * @license 	https://www.gnu.org/licenses/gpl-3.0.html GNU Public License (GPL)
 * @author      mplavala
 * @internal	@properties
 * @internal	@events OnWebPagePrerender
 * @internal	@modx_category Content
 * @internal    @installset base
 */

require_once(MODX_BASE_PATH . 'assets/plugins/webpconverter/plugin.webpconverter.php');

$e = &$modx->Event;
switch ($e->name) {
	case "OnWebPagePrerender":
		if ($modx->documentObject['contentType'] != 'text/html') {
			// not HTML output
			break;
		}
		$o = &$modx->documentOutput; // get a reference of the output
		if ((strpos( $_SERVER['HTTP_ACCEPT'], 'image/webp' ) !== false) and webpconverter\basic_checks()) {
			// webp is supported!
			// and we have all the needed functions
			$dom = new DOMDocument();
			$internalErrors = libxml_use_internal_errors(true);
			$dom->loadHTML(mb_convert_encoding($o, 'HTML-ENTITIES', 'UTF-8'));
			// standard image
			foreach ($dom->getElementsByTagName('img') as $node) {
				webpconverter\serve_node($node, 'src');
			}
			// video poster
			foreach ($dom->getElementsByTagName('video') as $node) {
				webpconverter\serve_node($node, 'poster');
			}
			// srcset inside picture
			foreach ($dom->getElementsByTagName('source') as $node) {
				webpconverter\serve_node($node, 'srcset');
			}
			
			$html = $dom->saveHTML();
			
			if ($html !== false) {
				$o = html_entity_decode($html);
			}
		}
		break;
	default :
		return; // stop here
		break;
}
