<?php
include_once CMDM_PATH . '/lib/models/GroupDownloadPage.php';
include_once CMDM_PATH . '/lib/controllers/BaseController.php';
class CMDM
{

	const TEXT_DOMAIN = 'cm-download-manager';

    public static function init()
    {
        CMDM_GroupDownloadPage::init();
        add_action('init', array('CMDM_BaseController', 'bootstrap'));
        add_action( 'widgets_init', array('CMDM_CmdownloadController', 'registerSidebars') );
        // if(get_option('CMDM_afterActivation') == 1)
        // {
        //     add_action('admin_notices', array(get_class(), 'showProMessages'));
        // }
        add_action( 'template_redirect', array( __CLASS__, 'refresh_permalinks_on_bad_404' ) );
    }

    public static function install()
    {
        CMDM_GroupDownloadPage::init();
        CMDM_GroupDownloadPage::registerPostTypes();
        CMDM_GroupDownloadPage::registerTaxonomies();
        update_option('CMDM_afterActivation', 1);
        delete_option('rewrite_rules');
        flush_rewrite_rules(true);
 		$downloads = get_posts(array('post_type' => CMDM_GroupDownloadPage::POST_TYPE));
 		foreach ( $downloads as $key => $dl ) {
 			$comquery = new WP_Comment_Query( array( 'post_id' => $dl->ID, 'status' => 'trash'  ) );
			foreach( $comquery->comments as $comment ){
				$old_status = get_comment_meta($comment->comment_ID, 'cmdm_comment_old_status', true);
				if ( ( $old_status !== false ) && ( $old_status !== 'trash' ) ) {
				update_comment_meta($comment->comment_ID, 'cmdm_comment_old_status', $comment->comment_approved);
					wp_set_comment_status($comment->comment_ID, $old_status);
				}
			}
 		}
    }

    public static function uninstall()
    {
    	delete_option('rewrite_rules');
    	flush_rewrite_rules(true);
 		$downloads = get_posts(array('post_type' => CMDM_GroupDownloadPage::POST_TYPE));
 		foreach ( $downloads as $dl ) {
 			$comquery = new WP_Comment_Query( array( 'post_id' => $dl->ID, 'status' => 'all'  ) );
			foreach( $comquery->comments as $comment ){
				update_comment_meta($comment->comment_ID, 'cmdm_comment_old_status', $comment->comment_approved);
				wp_set_comment_status($comment->comment_ID, 'trash');
			}
 		}
	}

	public static function __($msg)
    {
        return __($msg, self::TEXT_DOMAIN);
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
            $slug  = get_option(CMDM_GroupDownloadPage::OPTION_ADDONS_TITLE, 'CM Downloads');
            $parts = explode( '/', $wp->request );

            if ( $slug !== $parts[0] ) {
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
