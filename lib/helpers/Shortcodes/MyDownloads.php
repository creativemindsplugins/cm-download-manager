<?php

class CMDM_MyDownloadsShortcode {
	
	const PARAM_PAGE = 'page_cmdm';
	const PARAM_SEARCH = 'cmdm_search';
	
	public static function init() {
		add_shortcode('cmdm-my-downloads', array(__CLASS__, 'shortcode'));
	}
	
	
	static function shortcode($atts = array()) {
		
		CMDM_BaseController::loadScripts();

		$atts = shortcode_atts(array(
			'edit' => 1,
			'delete' => 1,
			'limit' => 20,
			'page' => filter_input(INPUT_GET, static::PARAM_PAGE) ?: 1,
			'search' => filter_input(INPUT_POST, static::PARAM_SEARCH),
            'show_menu' => 1
		), $atts);

		if (is_user_logged_in()) {
		    if($atts['show_menu']){
                ob_start();
                do_action('CMDM_show_dashboard_menu');
                $dashboard_menu = ob_get_clean();
            } else {
                $dashboard_menu = '';
            }

			return  '<div class="CMDM">'.$dashboard_menu . static::getContent($atts) . '</div>';
		}
		
	}
	
	
	static function getQuery($atts) {
		 
		$args = CMDM_GroupDownloadPage::getDownloadsByUserQuery($userId = null, $atts['limit'], $atts['page']);

        $args = CMDM_GroupDownloadPage::processRequestArgs($args);

        return new WP_Query($args);
		 
	}
	
	
	
	static function getContent($atts) {
		$query = static::getQuery($atts);
		$downloads = array_filter(array_map(array('CMDM_GroupDownloadPage', 'getInstance'), $query->get_posts()));

		$baseUrl = remove_query_arg(static::PARAM_PAGE, $_SERVER['REQUEST_URI']);
		$page = $atts['page'];
		$lastPage = ($atts['limit'] > 0 ? ceil($query->found_posts/$atts['limit']) : 1);

		$pagination = array(
			'page' => $page,
			'lastPage' => $lastPage,
			'baseUrl' => $baseUrl,
			'nextPageUrl' => ($page == $lastPage ? null : add_query_arg(static::PARAM_PAGE, $page+1, $baseUrl)),
			'prevPageUrl' => ($page == 1 ? null : add_query_arg(static::PARAM_PAGE, $page-1, $baseUrl)),
		);

		return CMDM_BaseController::_loadView('cmdownload/meta/dashboard-table', array(
			'myDownloads' => $downloads,
			'pagination' => $pagination,
			'search' => $atts['search'],
		));
	
	}
	
}
