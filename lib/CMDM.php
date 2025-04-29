<?php

class CMDM
{
    const TEXT_DOMAIN = 'cm-download-manager';

    static $version;

    public static function init() {
        add_action('init', array('CMDM_Update', 'run'), 0);
        CMDM_GroupDownloadPage::init();
        add_action('init', array('CMDM_BaseController', 'bootstrap'), 1);
        add_action( 'widgets_init', array('CMDM_CmdownloadController', 'registerSidebars') );
        add_action( 'template_redirect', array( __CLASS__, 'refresh_permalinks_on_bad_404' ) );
    }

    public static function install($networkwide)
    {
        global $wpdb;

        self::install_blog();
        delete_option('rewrite_rules');
        flush_rewrite_rules(true);
    }

    public static function uninstall() {
        delete_option('rewrite_rules');
        flush_rewrite_rules(true);
    	add_action( 'pre_get_comments', array(__CLASS__, 'filterAdminComments'), 99, 1 );
		$comquery = get_comments( array( 'post_type' => CMDM_GroupDownloadPage::POST_TYPE, 'status' => 'all') );
		foreach( $comquery as $comment ){
			update_comment_meta($comment->comment_ID, 'cmdm_comment_old_status', $comment->comment_approved);
			wp_set_comment_status($comment->comment_ID, 'trash');
		}
    }

    public static function install_blog() {
        self::restore_comments();
    }

	public static function restore_comments() {
		$comquery = get_comments( array( 'post_type' => CMDM_GroupDownloadPage::POST_TYPE, 'status' => 'trash', 'type__in' => ['comment', 'cmdm_download_log'] ) );
		foreach( $comquery as $comment ){
			$old_status = get_comment_meta($comment->comment_ID, 'cmdm_comment_old_status', true);
			if ( ( $old_status !== false ) && ( $old_status !== 'trash' ) ) {
			update_comment_meta($comment->comment_ID, 'cmdm_comment_old_status', $comment->comment_approved);
				wp_set_comment_status($comment->comment_ID, $old_status);
			}
		}
	}

	public static function filterAdminComments($clauses) {
        $clauses->query_vars['type__not_in'] = array();
	}

    public static function __($msg)
    {
        return __($msg, self::TEXT_DOMAIN);
    }

	public static function getReferer() {
    	global $wp_query;

    	$isEditPage = function($url) { return false; };
    	$isTheSameHost = function($a, $b) {
    		return parse_url($a, PHP_URL_HOST) == parse_url($b, PHP_URL_HOST);
    	};

    	$canUseReferer = (!empty($_SERVER['HTTP_REFERER'])
    			AND $isTheSameHost($_SERVER['HTTP_REFERER'], site_url())
    			AND !$isEditPage($_SERVER['HTTP_REFERER']));
    	$canUseCurrentPost = (is_single() AND !empty($wp_query->post) AND $wp_query->post->post_type == CMDM_GroupDownloadPage::POST_TYPE
    			AND $isEditPage($_GET));

    	if (!empty($_GET['backlink'])) { // GET backlink param
    		return base64_decode(urldecode($_GET['backlink']));
    	}
    	else if (!empty($_POST['backlink'])) { // POST backlink param
    		return $_POST['backlink'];
    	}
    	else if ($canUseReferer) { // HTTP referer
    		return $_SERVER['HTTP_REFERER'];
    	}
    	else if ($canUseCurrentPost) { // Question permalink
    		return get_permalink($wp_query->post->ID);
    	} else { // CMDM index page
    		return get_post_type_archive_link(CMDM_GroupDownloadPage::POST_TYPE);
    	}
    }


    public static function permalink() {
    	return get_post_type_archive_link(CMDM_GroupDownloadPage::POST_TYPE);
    }

    /**
     * Auto flush permalinks wth a soft flush when a 404 error is detected on an
     * CMDM_Page.
     *
     * @return string
     *
     */
    public static function refresh_permalinks_on_bad_404() {
        global $wp;

        if ( ! is_404() ) {
            return;
        }

        if ( isset( $_GET['cm-flush'] ) ) { // WPCS: CSRF ok.
            return;
        }

        if ( false === get_transient( 'cm_refresh_404_permalinks' ) ) {
            $parts = explode( '/', $wp->request );

            if ( CMDM_GroupDownloadPage::$rewriteSlug !== $parts[0] ) {
                return;
            }

            flush_rewrite_rules( false );

            set_transient( 'cm_refresh_404_permalinks', 1, HOUR_IN_SECONDS * 12 );

            $redirect_url = home_url( add_query_arg( array( 'cm-flush' => 1 ), $wp->request ) );
            wp_safe_redirect( esc_url_raw( $redirect_url ), 302 );
            exit();
        }
    }
}
