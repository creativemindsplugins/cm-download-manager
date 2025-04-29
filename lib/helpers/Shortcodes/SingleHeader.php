<?php

class CMDM_SingleHeaderShortcode {
	
	static function shortcode($atts = array()) {
		global $post;
		
		$atts = shortcode_atts(array(
			'showtitle' => 1,
			'id' => null,
		), $atts);
		
		if (empty($atts['id']) AND !empty($post) AND $post->post_type = CMDM_GroupDownloadPage::POST_TYPE) {
			$atts['id'] = $post->ID;
		}
		
		$showTitle = $atts['showtitle'];
		
		if (!empty($atts['id']) AND $download = CMDM_GroupDownloadPage::getInstance($atts['id']) AND $download->isVisible()) {
			$post = $download->getPost();
			CMDM_BaseController::loadScripts();
			return CMDM_BaseController::_loadView('cmdownload/widget/single-header', compact('download', 'showTitle'));
		}
		
	}
	
}
