<?php

include_once CMDM_PATH . '/lib/models/forms/AddDownloadForm.php';

class CMDM_CmdownloadController extends CMDM_BaseController {

    const DOWNLOAD_NONCE = 'CMDM_download_nonce';
    const OPTION_FILTER_PLACEHOLDER = 'CMDM_option_filter_placeholder';

    private static $error404 = false;
    private static $phpDepricated54 = "5.4";

    public static function initialize() {

        add_action('admin_init', [__CLASS__, 'adminResources']);

        add_action('pre_get_posts', [__CLASS__, 'registerVisibilityFilter'], PHP_INT_MAX, 1);

        add_filter('the_posts', [__CLASS__, 'checkIfDisabled'], PHP_INT_MAX, 2);
        add_filter('posts_search', [__CLASS__, 'alterSearchQuery'], 99, 2);
        add_filter('CMDM_admin_settings', [__CLASS__, 'processLabelsSettings'], 1, 1);
        add_filter('CMDM_admin_settings', [__CLASS__, 'processExtensionSettings'], 1, 1);
        add_filter('manage_edit-' . CMDM_GroupDownloadPage::POST_TYPE . '_columns', [__CLASS__, 'registerAdminColumns']);
        add_filter('manage_' . CMDM_GroupDownloadPage::POST_TYPE . '_posts_custom_column', [__CLASS__, 'adminColumnDisplay'], 10, 2);
        add_action('admin_notices', [__CLASS__, 'checkDirectoryAccessAdminNotice']);
        add_action('admin_notices', [__CLASS__, 'printAdminWarnings']);
        add_filter('posts_join', [__CLASS__, 'excludeAdminMediaAttachmentsJoin'], 10, 2);
        add_filter('posts_where', [__CLASS__, 'excludeAdminMediaAttachmentsWhere'], 10, 2);
        add_action('cmdm_display_supported_shortcodes', [__CLASS__, 'cmdm_display_supported_shortcodes'], 1);
        add_action('before_delete_post', [__CLASS__, 'before_delete_post'], 10, 1);
        add_action('cmdm_load_scripts', [__CLASS__, 'loadScripts']);

        add_action('cmdm_after_add_downloads_number', [__CLASS__, 'handleAfterAddDownloadsNumber']);

        add_filter('template_include', [__CLASS__, 'overrideTemplate']);
        add_action('wp_ajax_cmdm_screenshot_from_wp', [__CLASS__, 'screenshotFromWP']);
        add_action('CMDM_index_controls', [__CLASS__, 'displayIndexControls'], 10, 3);
        add_filter('wp_nav_menu_items', [__CLASS__, 'addMenuItem'], 1, 1);
        add_action('CMDM_show_search_form', [__CLASS__, 'showSearchForm']);
        add_action('CMDM_show_dashboard', [__CLASS__, 'showDashboard'], 1, 1);
        add_action('CMDM_show_dashboard_menu', [__CLASS__, 'showDashboardMenu'], 1, 1);
        add_action('CMDM_show_rating', [__CLASS__, 'showRating'], 1, 1);
        add_action('CMDM_show_support_threads_list', [__CLASS__, 'showSupportThreadList'], 1, 1);
        add_action('CMDM_show_support', [__CLASS__, 'showSupport'], 1, 1);
        add_action('CMDM_show_edit_link', [__CLASS__, 'showEditLink'], 1, 1);
        add_action('CMDM_show_size', [__CLASS__, 'showSize'], 1, 1);
        add_filter('CMDM_title_controller', [__CLASS__, 'overrideControllerTitle'], 1, 1);
        add_filter('the_content', [__CLASS__, 'filterSingleContent'], PHP_INT_MAX, 1);
        add_filter('the_content', [__CLASS__, 'filterIndexContent'], PHP_INT_MAX, 1);
        add_filter('dynamic_sidebar_before', [__CLASS__, 'dynamic_sidebar_before'], 10, 2);
        do_action('CMDM_custom_post_type_nav', CMDM_GroupDownloadPage::POST_TYPE);
        do_action('CMDM_custom_taxonomy_nav', CMDM_Category::TAXONOMY);

        add_action('wp_login_failed', [__CLASS__, 'front_end_login_fail']);//??

        CMDM_SupportThread::init();

    }

    public static function front_end_login_fail($username) {
        $referrer = $_SERVER['HTTP_REFERER'];
        if (!empty($referrer) && !strstr($referrer, 'wp-login') && !strstr($referrer, 'wp-admin')) {
            wp_redirect(add_query_arg('login', 'failed', $referrer));
            exit;
        }
    }

    static function printAdminWarnings() {

        if (!current_user_can('manage_options'))
            return;

        if (PHP_VERSION == '5.4.43') {
            printf('<div class="error"><p>%s</p></div>',
                'Your PHP version 5.4.43 contains a serious bug related to the anonymous functions. ' .
                'For this reason the CM Download Manager plugin won\'t work properly.<br />Please ask your hosting provider to change the PHP version for your website.');
        }

        // Check memory limit
        $memoryLimit = ini_get('memory_limit');
        if ($memoryLimit != -1) {
            $memoryLimitUnit = preg_replace('/[0-9]/', '', $memoryLimit);
            $memoryLimitNumber = preg_replace('/[^0-9]/', '', $memoryLimit);
            if ('G' == strtoupper($memoryLimitUnit))
                $memoryLimitNumber *= 1024;
            if ($memoryLimitNumber < 256) {
                printf('<div class="error"><p>%s</p></div>',
                    sprintf('We are sorry, but the CM Download Manager plugin requires at least 256 MB memory, but your php.ini memory_limit is set to %s. ' .
                        'Please contact with your hosting provider and ask to increase the memory limit.', $memoryLimit)
                );
            }
        }
    }

    public static function filterIndexContent($content) {
        if (is_main_query() and self::$query->is_post_type_archive(CMDM_GroupDownloadPage::POST_TYPE)) {
            if ($term = self::$query->get_queried_object() and !empty($term->term_id) and $term->taxonomy == CMDM_Category::TAXONOMY
                and $category = CMDM_Category::getInstance($term)) {
                if (!$category->isVisible()) {
                    $errors = [CMDM_Labels::getLocalized('error_not_permissions_view_page')];
                    return self::_loadView('cmdownload/widget/permissions', compact('errors'));
                }
            }
            $displayOptions = CMDM_Settings::getDisplayOptionsDefaults();
            $displayOptions['header'] = false;
            $displayOptions['searchBar'] = CMDM_Settings::getOption(CMDM_Settings::INDEX_PAGE_SEARCH_BAR);
            $displayOptions['searchSubmit'] = CMDM_Settings::getOption(CMDM_Settings::INDEX_PAGE_SEARCH_SUBMIT);
            $content = self::_loadView('cmdownload/widget/index', [
                'query' => self::$query,
                'displayOptions' => $displayOptions,
            ]);
        }
        return $content;
    }

    static function filterSingleContent($content) {
        if ( ! is_main_query() || ! in_the_loop() ) {
            return $content;
        }
        
        if (is_main_query() and self::$query->is_single() and self::$query->get('post_type') == CMDM_GroupDownloadPage::POST_TYPE) {
            if ($download = CMDM_GroupDownloadPage::getInstance(self::$query->post)) {
                if ($download->isVisible()) {
                    $messages = self::popMessages();
                    $content = self::_loadView('messages', ['messages' => $messages]);

                    $content .= '<div class="widget-area cmdm-single-top-sidebar cmdm-content-area">';
                    ob_start();
                    dynamic_sidebar(CMDM_Settings::AREA_SINGLE_TOP);
                    $content .= ob_get_clean() . '</div>';

                    $content .= CMDM_SingleHeaderShortcode::shortcode(['showtitle'=>0]);

                    $content .= '<div class="widget-area cmdm-single-screenshots-below-sidebar cmdm-content-area">';
                    ob_start();
                    dynamic_sidebar(CMDM_Settings::AREA_SINGLE_SCREENSHOTS_BELOW);
                    $content .= ob_get_clean() . '</div>';
                    $content .= CMDM_SingleContentShortcode::shortcode();
                    $content .= '<div class="widget-area cmdm-single-bottom-sidebar cmdm-content-area">';
                    ob_start();
                    dynamic_sidebar(CMDM_Settings::AREA_SINGLE_BOTTOM);
                    $content .= ob_get_clean() . '</div>';
                } else {
                    $errors = [CMDM_Labels::getLocalized('error_not_permissions_view_page')];
                    $content = self::_loadView('cmdownload/widget/permissions', compact('errors'));
                }
            }
        }
        return $content;
    }

    /**
     * @param WP_Query $query
     *
     * @return bool
     */
    public static function checkGetQuery(WP_Query $query) {
        $returned = (
            !self::isAjax() and !self::isPostRequest()
            and (
                $query->get('post_type') == CMDM_GroupDownloadPage::POST_TYPE
                or $query->get('CMDM-cmdownload-dashboard')
                or trim($_SERVER['REQUEST_URI'], '/') == CMDM_Settings::getOption('CMDM_cmdownloads_slug')
            )
        );

        return $returned;
    }

    /**
     * @param WP_Query $query
     *
     * @return bool
     */
    public static function checkOptionIndexPageDisabled(WP_Query $query) {
        $returned = (
            !$query->is_single()
            and !$query->get('CMDM-cmdownload-dashboard')
            and false !== (bool)CMDM_Settings::getOption(CMDM_Settings::OPTION_INDEX_PAGE_DISABLED)
        );

        return $returned;
    }

    /**
     * @param WP_Query $query
     *
     * @return bool
     */
    public static function checkOptionDashboardPageDisabled(WP_Query $query) {
        $returned = (
            !$query->is_single()
            and $query->get('CMDM-cmdownload-dashboard')
            and !$query->get('CMDM-cmdownload-add')
            and !$query->get('CMDM-cmdownload-edit')
            and false !== (bool)CMDM_Settings::getOption(CMDM_Settings::OPTION_DASHBOARD_PAGE_DISABLED)
        );

        return $returned;
    }

    /**
     * @param $posts
     * @param WP_Query $query
     *
     * @return mixed
     */
    public static function checkIfDisabled($posts, WP_Query $query) {
        $check = self::checkGetQuery($query);
        if (false !== (bool)$check) {
            switch (true) {
                case (false !== (bool)self::checkOptionIndexPageDisabled($query)):
                    $query->is_404 = true;
                    self::$error404 = true;
                    break;
                case (false !== (bool)self::checkOptionDashboardPageDisabled($query)):
                    $query->is_404 = true;
                    self::$error404 = true;
                    break;
            }
        }

        return $posts;
    }

    public static function registerSidebars() {

        register_sidebar([
            'id' => CMDM_Settings::AREA_SIDEBAR,
            'name' => 'CM Download Manager Single Sidebar',
            'description' => 'This sidebar is shown on CM Download Manager Index'
        ]);

        register_sidebar([
            'id' => CMDM_Settings::AREA_SINGLE_TOP,
            'name' => 'CMDM single top',
            'description' => 'Widget area displayed at top of the single download page.'
        ]);

        register_sidebar([
            'id' => CMDM_Settings::AREA_SINGLE_SCREENSHOTS_BELOW,
            'name' => 'CMDM Below Screenshots',
            'description' => 'Widget area displayed below screenshots on the single download page.'
        ]);

        register_sidebar([
            'id' => CMDM_Settings::AREA_SINGLE_BOTTOM,
            'name' => 'CMDM single bottom',
            'description' => 'Widget area displayed at bottom of the single download page.'
        ]);
    }

    static function dynamic_sidebar_before($index, $hasWidgets) {

        global $wp_registered_sidebars;

        if (is_admin())
            return;

        $isSidebar = ($index == 'sidebar' or $index == 'sidebar-1');

        // Display download button
        $area = CMDM_Settings::getOption(CMDM_Settings::OPTION_DOWNLOAD_BUTTON_WIDGET_AREA);
        if ($area == $index or ($area == CMDM_Settings::AREA_SIDEBAR and $isSidebar)) {
            $download = CMDM_GroupDownloadPage::getInstance(get_the_id());
            $downloadId = $download->getId();
            $version = $download->getVersion();
            $updated = $download->getUpdated();
            $adminSupported = $download->isRecommended();
            CMDM_BaseController::loadScripts();
            echo CMDM_BaseController::_loadView('cmdownload/meta/details',
                compact('download', 'version', 'updated', 'adminSupported', 'downloadId'));

            $post = get_post();

            if (!empty($post) AND $post->post_type == CMDM_GroupDownloadPage::POST_TYPE) {
                $atts['id'] = $post->ID;
                $shortcodeId = md5(serialize($atts));
                $atts['label'] = CMDM_Labels::getLocalized('download_button_file');
                $id = get_the_id();
                $nonce = wp_create_nonce(CMDM_CmdownloadController::DOWNLOAD_NONCE);
                $url = $download->getDownloadFormUrl();
                $args = array(
                    'action_url'        => ($download->canDownload() ? $url : null),
                    'nonce'             => $nonce,
                    'download_id'       => $id,
                    'errors'            => CMDM_BaseController::popMessages(CMDM_CmdownloadController::MESSAGE_ERROR . $shortcodeId),
                    'shortcodeId'       => $shortcodeId,
                    'download_label'    => $atts['label'],
                );

                echo CMDM_BaseController::_loadView('cmdownload/meta/download-form', $args);
            }
            echo '<div class="cmdm-clear"></div>';
        }
    }

    public static function getDefaultScreenshot() {
        $defaultScreenshotUrl = CMDM_Settings::getOption(CMDM_Settings::OPTION_DEFAULT_SCREENSHOT);
        if (substr(parse_url($defaultScreenshotUrl, PHP_URL_SCHEME), 0, 4) !== 'http') {
            $defaultScreenshotUrl = CMDM_URL . $defaultScreenshotUrl;
        }
        return $defaultScreenshotUrl;
    }

    public static function processExtensionSettings($params = []) {
        $post_allowed_extensions = sanitize_text_field($_POST['CMDM_allowed_extensions'] ?? '');
        $post_allowed_extensions = str_replace(' ', '', $post_allowed_extensions);


        if (!empty($post_allowed_extensions)) {
            $getMimeTypes = wp_get_mime_types();
            $getMimeTypes['pal'] = 'text/plainpal';
            $getMimeTypes['grva'] = 'text/plaingrva';
            $getMimeTypes = array_flip($getMimeTypes);
            $new_extensions_with_mime_types = [];

            if ($post_allowed_extensions == '*'){
                foreach ($getMimeTypes as $key => $mimeType) {
                    if (strstr($mimeType, '|')){
                        $many_mime_types = explode('|', $mimeType);
                        foreach ($many_mime_types as $type){
                            $new_extensions_with_mime_types[$type] = $type;
                        }
                    }
                    else {
                        $new_extensions_with_mime_types[$mimeType] = $mimeType;
                    }
                }
                $new_extensions_with_mime_types = array_values($new_extensions_with_mime_types);

            }
            else {
                $new_extensions = array_unique(explode(',', $post_allowed_extensions));

                foreach ($new_extensions as $key => $oneExtension) {
                    foreach ($getMimeTypes as $mimeType) {
                        if (strstr($mimeType, $oneExtension)) {
                            $new_extensions_with_mime_types[$key] = $oneExtension;
                        }
                    }
                }
            }

            update_option(CMDM_Settings::OPTION_ALLOWED_EXTENSIONS, $new_extensions_with_mime_types);
        }
        $extensions = CMDM_Settings::getOption(CMDM_Settings::OPTION_ALLOWED_EXTENSIONS);
        $params['CMDM_allowed_extensions'] = $extensions;
        return $params;
    }

    public static function processLabelsSettings($params = []) {
        if (self::isPostRequest()) {
            if (!empty($_POST)) {
                CMDM_Settings::processPostRequest();
                delete_option('rewrite_rules');
                flush_rewrite_rules(true);
            }
            // Process labels
            $labels = CMDM_Labels::getLabels();
            foreach ($labels as $labelKey => $label) {
                if (isset($_POST['label_' . $labelKey])) {
                    CMDM_Labels::setLabel($labelKey, stripslashes($_POST['label_' . $labelKey]));
                }
            }
        }
        return $params;
    }

    public static function alterSearchQuery($search, WP_Query $query) {
        global $wpdb;

        if ($query->query_vars['post_type'] == CMDM_GroupDownloadPage::POST_TYPE &&
            /* isset($query->query_vars['widget']) && $query->query_vars['widget'] !== true && */
            !$query->is_single && !$query->is_404 && !$query->is_author && isset($_GET['CMDsearch'])) {
            $search_term = $_GET['CMDsearch'];
            if (!empty($search_term)) {
                $search = '';
                $query->is_search = true;
                // added slashes screw with quote grouping when done early, so done later
                $search_term = stripslashes($search_term);
                preg_match_all('/".*?("|$)|((?<=[\r\n\t ",+])|^)[^\r\n\t ",+]+/', $search_term, $matches);
                @ $terms = array_map('_search_terms_tidy', $matches[0]);

                $n = '%';
                $searchand = ' AND ';
                foreach ((array)$terms as $term) {
                    $search .= $wpdb->prepare(" AND ($wpdb->posts.post_title LIKE %s OR $wpdb->posts.post_content LIKE %s)", "%$term%", "%$term%");
                    //                 	$term = esc_sql(method_exists($wpdb, 'esc_like') ? $wpdb->esc_like($term) : like_escape($term));
                    //                     $search .= "{$searchand}(($wpdb->posts.post_title LIKE '{$n}{$term}{$n}') OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}'))";
                }
                add_filter('get_search_query', function ($q) use ($search_term) {
                    return $search_term;
                }, 99, 1);
                remove_filter('posts_request', 'relevanssi_prevent_default_request');
                remove_filter('the_posts', 'relevanssi_query');
            }
        }
        return $search;
    }

    public static function adminResources() {
        global $pagenow;
        $post_id = isset($_GET['post']) ? (int)$_GET['post'] : -1;
        if (CMDM_GroupDownloadPage::POST_TYPE == get_post_type($post_id) && $_GET['action'] == 'edit') {
            wp_redirect(self::getUrl('cmdownload', 'edit', ['id' => $post_id]), 301);
            exit;
        } elseif (isset($_GET['post_type']) && $_GET['post_type'] == CMDM_GroupDownloadPage::POST_TYPE && $pagenow == 'post-new.php') {
            wp_redirect(self::getUrl('cmdownload', 'add'), 301);
            exit;
        }
    }

    public static function registerVisibilityFilter($query) {
        if ($query->is_main_query() and (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == CMDM_GroupDownloadPage::POST_TYPE) || (isset($query->query_vars['widget']) && $query->query_vars['widget'] !== true) and !is_admin()) {
            if (!$query->is_single && !$query->is_404) {
                if ($limit = $query->get('widget_limit')) {
                    $query->set('posts_per_page', $limit);
                } else if (get_query_var('CMDM-cmdownload-dashboard') != '1') {
                    $query->set('posts_per_page', 10);
                } else {
                    $query->set('posts_per_page', -1);
                }

                if (!is_admin()) {
                    CMDM_GroupDownloadPage::filterVisibility($query);
                }
            }
        }
    }

    protected static function _processAddThread() {
        global $wp_query;
        $post = $wp_query->post;
        $download = CMDM_GroupDownloadPage::getInstance($post->ID);
        $title = sanitize_text_field($_POST['thread_title']);
        $content = sanitize_textarea_field($_POST['thread_comment']);
        $notify = !empty($_POST['thread_notify']);
        $author_id = get_current_user_id();
        $error = false;
        $messages = [];
        try {
            // CSRF protection
            if (empty($_POST['nonce']) or !wp_verify_nonce($_POST['nonce'], 'cmdm_topic_add')) {
                throw new Exception(serialize(['Invalid nonce']));
            }
            if (!$download or !$download->isVisible()) {
                throw new Exception(serialize(['Access denied.']));
            }
            $comment_id = CMDM_SupportThread::addThread($post->ID, $title, $content, $author_id, $notify);
        } catch (Exception $e) {
            $messages = unserialize($e->getMessage());
            $error = true;
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

            header('Content-type: application/json');
            echo json_encode([
                'success' => (int)(!$error),
                'comment_id' => $comment_id,
                'message' => $messages,
            ]);
            exit;
        } else {
            if ($error) {
                self::_addMessage(self::MESSAGE_ERROR, CMDM::__('An error occurred.'));
            } else {
                self::_addMessage(self::MESSAGE_SUCCESS,
                    CMDM::__('Support thread has been created.'));
            }
            wp_redirect(get_permalink($post->ID) . '#support', 303);
            exit;
        }
    }

    protected static function _processAddCommentToThread() {
        global $wp_query;
        $post = $wp_query->post;
        $download = CMDM_GroupDownloadPage::getInstance($post->ID);
        $parent = get_query_var('CMDM-parent-id');
        $content = sanitize_textarea_field($_POST['thread_comment']);
        $notify = !empty($_POST['thread_notify']);
        $resolved = !empty($_POST['thread_resolved']);
        $author_id = get_current_user_id();
        $error = false;
        $messages = [];
        try {
            // CSRF protection
            if (empty($_POST['nonce']) or !wp_verify_nonce($_POST['nonce'], 'cmdm_topic_comment')) {
                throw new Exception(serialize(['Invalid nonce']));
            }
            if (!$download or !$download->isVisible()) {
                throw new Exception(serialize(['Access denied.']));
            }
            $comment_id = CMDM_SupportThread::addCommentToThread($post->ID, $parent, $content, $author_id, $notify, $resolved);
        } catch (Exception $e) {
            $messages = unserialize($e->getMessage());
            $error = true;
        }
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {

            header('Content-type: application/json');
            echo json_encode([
                'success' => (int)(!$error), 'comment_id' => $comment_id,
                'commentData' => CMDM_SupportThread::getCommentData($comment_id),
                'message' => $messages
            ]);
            exit;
        } else {
            $msg = CMDM_Labels::getLocalized('support_answer_added_msg');
            CMDM_BaseController::addMessage(self::MESSAGE_SUCCESS, $msg);

            wp_redirect(get_permalink($post->ID) . 'topic/' . $parent . '/#comment-' . $comment_id, 303);
            exit;
        }
    }

    protected static function _processListThread() {
        global $wp_query, $post;
        if ($post and $download = CMDM_GroupDownloadPage::getInstance($post->ID) and $download->isVisible()) {
            $page = $wp_query->query_vars['CMDM-comment-page'];
            $threads = CMDM_SupportThread::getThreadsForDownload($post->ID, $page);
            do_action('CMDM_show_support_threads_list', $threads['items']);
        } else {
            wp_redirect(self::getAccessDeniedPagePermalink());
        }
        exit;
    }

    protected static function _showThreadDetails() {
        global $wp_query;
        $post = $wp_query->post;
        $thread_id = $wp_query->query_vars['CMDM-comment-id'];
        $thread = CMDM_SupportThread::getThread($thread_id);
        if ($post and $download = CMDM_GroupDownloadPage::getInstance($post->ID) and $download->isVisible()) {
            echo self::_loadView('cmdownload/thread', ['thread' => $thread, 'author' => $download->getAuthor()->display_name]);
        } else {
            wp_redirect(self::getAccessDeniedPagePermalink());
        }
        exit;
    }

    public static function processQueryVars() {
        $action = get_query_var('CMDM-comment-action');
        if (!empty($action)) {
            switch ($action) {
                case 'add':
                    if (get_query_var('CMDM-parent-id') > 0)
                        self::_processAddCommentToThread();
                    else
                        self::_processAddThread();
                    break;
                case 'show':
                    self::_showThreadDetails();
                    break;
                case 'list':
                default:
                    self::_processListThread();
                    break;
            }
        }
    }

    public static function overrideTemplate($template) {
        global $wp_query, $errors, $wpdb;

        add_action('wp_enqueue_scripts', [__CLASS__, 'loadScripts']);

        // Redirect CMDM attachment to the CMDM page
        if ($wp_query->is_attachment() and $post = reset($wp_query->posts) and $post->post_parent
            and $parent = get_post($post->post_parent) and $parent->post_type == CMDM_GroupDownloadPage::POST_TYPE) {
            if ($download = CMDM_GroupDownloadPage::getInstance($parent) and $download->isVisible()) {
                wp_redirect(get_permalink($parent->ID), 301);
            } else {
                wp_redirect(CMDM::permalink(), 301);
            }
            exit;
        }

        if (
            false !== strstr(phpversion(), self::$phpDepricated54)
            and false !== (bool)CMDM_Settings::getOption(CMDM_Settings::OPTION_INDEX_PAGE_DISABLED)
            and false !== strlen(trim(CMDM_Settings::getOption('CMDM_cmdownloads_slug')))
            and trim($_SERVER['REQUEST_URI'], '/') == CMDM_Settings::getOption('CMDM_cmdownloads_slug')
        ) {
            include(get_404_template());
            get_footer();
            exit;
        }

        if (get_query_var('post_type') == CMDM_GroupDownloadPage::POST_TYPE || is_tax(CMDM_Category::TAXONOMY) || (isset($wp_query->query['post_type']) and $wp_query->query['post_type'] == CMDM_GroupDownloadPage::POST_TYPE)) {

            if (is_404()) {
                // leave default 404 template
                $template = get_404_template();
            } else if (is_single()) {

                self::processQueryVars();

                global $post;
                if ($post and $download = CMDM_GroupDownloadPage::getInstance($post->ID)) {
                    $post->comment_status = 'closed';
                    $post->ping_status = 'closed';
                    if (!$download->isVisible()) {
                        return self::prepareSinglePage('CM Downloads', 'Permissions'); // content will be overwritten
                    } else {
                        $wp_query->set('cmdm_download_page', 1);
                        self::$query->set('cmdm_download_page', 1);
                    }


                    $template = self::locateTemplate(['cmdownload/single'], $template);
                }
                add_filter('body_class', [__CLASS__, 'adjustBodyClass'], 20, 1);
            } else {

                wp_enqueue_script('jquery');
                if ($cmdm_category = self::$query->get('cmdm_category') and $term = get_term_by('slug', $cmdm_category, CMDM_Category::TAXONOMY)) {
                    $category = CMDM_Category::getInstance($term);
                    if (!$category->isVisible()) {
                        $template = self::locateTemplate(['permissions'], $template);
                        $template = self::prepareSinglePage('CM Downloads', 'Permissions');
                        return $template;
                    }
                }


                $template = self::locateTemplate(['cmdownload/index'], $template);

                $wp_query->set('is_cmdm_index', 1);
                self::$query->set('is_cmdm_index', 1);
            }
            add_filter('body_class', [__CLASS__, 'adjustBodyClass'], 20, 1);
        } elseif (get_query_var('CMDM-cmdownload-dashboard') == 1 or get_query_var('CMDM-cmdownload-edit') == 1 or get_query_var('CMDM-cmdownload-add') == 1) {
            if (is_404()) {
                // leave default 404 template
                $template = get_404_template();
            } else {
                add_action('wp_enqueue_scripts', [__CLASS__, 'loadScripts']);

                remove_filter('the_content', 'wpautop');
                remove_filter('the_content', 'do_shortcode');

                $template = self::locateTemplate(['cmdownload/dashboard_layout'], $template);

                add_filter('body_class', [__CLASS__, 'adjustBodyClass'], 20, 1);
            }
        }

        return $template;
    }

    public static function getIndexTitle() {
        $title = CMDM_CmdownloadController::getTitle(self::$query);
        $title .= sprintf(' <span class="num">(%d)</span>', self::$query->found_posts);
        return $title;
    }

    static function pageBodyClass($classes) {
        if (self::$query->is_single()) {
            $template = CMDM_Settings::getDownloadPageTemplate();
        } else {
            $template = CMDM_Settings::getIndexPageTemplate();
        }
        $classes[] = 'page';
        $classes[] = 'page-template';
        $classes[] = 'page-template-' . sanitize_html_class(str_replace('.', '-', $template));
        if (stripos($template, 'full-width') !== false) {
            $classes[] = 'full-width';
        }
        return $classes;
    }

    public static function overrideControllerTitle($title) {
        if ($title == 'Cmdownload') {
            return 'Downloads';
        } else if ($title == 'panel_title_cmdownload_link') {
            if ($id = static::_getParam('id') and $download = CMDM_GroupDownloadPage::getInstance($id))
                return $download->getTitle();
        }
        return $title;
    }

    public static function getTitle($query = null) {
        global $wp_query;
        if (empty($query))
            $query = $wp_query;

        $currentCategory = $query->get_queried_object();

         if ($query->is_tax()) {
            $term = $currentCategory;
            $term_id = $term->term_id;
            $title = $term->name;
            while (!empty($term->parent)) {
                $term = get_term($term->parent, CMDM_Category::TAXONOMY);
                $link = '<a href="' . esc_attr(get_term_link($term)) . '" class="cmdm-category-link">' . $term->name . '</a>';
                $title = $link . ' &raquo; ' . $title;
            }
        } elseif ($query->is_search()) {
            $title = __('Search Results for', 'cm-download-manager') . ' <em>"' . esc_html(get_search_query()) . '"</em>';
        } else {
            $title = CMDM_Settings::getOption(CMDM_Settings::OPTION_ADDONS_TITLE);
        }
        return $title;
    }

    public static function getCurrentIndexView($widgetOptions = null) {
        $view = null;
        if (!empty($_GET['view'])) {
            $view = $_GET['view'];
        } else if ($widgetOptions and isset($widgetOptions['atts']['view'])) {
            $view = $widgetOptions['atts']['view'];
        }
        if (empty($view)) {
            $view = CMDM_Settings::getOption(CMDM_Settings::OPTION_DEFAULT_VIEW);
        }
        return $view;
    }

    public static function getQueryUrl($query = null, $page = null, $view = null, $sort = null) {
        global $wp_query;
        if (empty($query))
            $query = $wp_query;
        if (is_null($page))
            $page = $query->get('paged');
        $category = ($query->is_tax() ? $query->get_queried_object() : null);
        if (empty($search) and !empty($_GET['CMDsearch']))
            $search = $_GET['CMDsearch'];
        else
            $search = null;

        return self::getIndexUrl($page, $view, $category, $query->get('tag'), $search, $sort);
    }

    public static function getIndexUrl($page = 1, $view = null, $category = null, $tag = null, $search = null, $sort = null) {

        $pagePart = ($page > 1 ? 'page/' . $page . '/' : '');

        if ($category and is_scalar($category)) {
            if (is_numeric($category))
                $category = get_term($category, CMDM_Category::TAXONOMY);
            else
                $category = get_term_by('slug', $category, CMDM_Category::TAXONOMY);
        }
        if (!empty($category) and $category->taxonomy == CMDM_Category::TAXONOMY) {
            $url = get_term_link($category);
        } else {
            $url = get_post_type_archive_link(CMDM_GroupDownloadPage::POST_TYPE);
        }
        $url = trailingslashit($url) . $pagePart;

        if (!empty($tag)) {
            $url = add_query_arg(urlencode_deep(['tag' => $tag]), $url);
        }
        if (!empty($search)) {
            $url = add_query_arg(urlencode_deep(['CMDsearch' => $search]), $url);
        }
        if (!empty($view)) {
            $url = add_query_arg(urlencode_deep(['view' => $view]), $url);
        }

        return $url;
    }

    public static function adjustBodyClass($wp_classes) {
        $key = array_search('singular', $wp_classes);
        if (!empty($key)) {
            unset($wp_classes[$key]);
        }
        if (get_query_var('CMDM-cmdownload-dashboard')) {
            $wp_classes[] = 'cmdm-dashboard-page';
        }
        $wp_classes[] = 'cmdm-body';

        $theme_name = strtolower(wp_get_theme());
        $wp_classes[] = 'cmdm-body cmdm-' . str_replace(" ", "-", $theme_name);

        return array_merge($wp_classes);
    }

    public static function addMenuItem($items) {

        $index_permalink = self::getUrl('cmdownloads', '');
        if (CMDM_Settings::getOption(CMDM_Settings::OPTION_INDEX_PAGE_DISABLED) && CMDM_Settings::getOption(CMDM_Settings::OPTION_INDEX_PAGE_CUSTOM_PAGE_ID) != 0){
            $index_permalink = get_permalink(CMDM_Settings::getOption(CMDM_Settings::OPTION_INDEX_PAGE_CUSTOM_PAGE_ID));
        }

        $dashboard_permalink = self::getUrl('cmdownload', 'dashboard');
        if (CMDM_Settings::getOption(CMDM_Settings::OPTION_DASHBOARD_PAGE_DISABLED) && CMDM_Settings::getOption(CMDM_Settings::OPTION_DASHBOARD_PAGE_CUSTOM_ID) != 0){
            $dashboard_permalink = get_permalink(CMDM_Settings::getOption(CMDM_Settings::OPTION_DASHBOARD_PAGE_CUSTOM_ID));
        }

        $link = self::_loadView('cmdownload/meta/menu-item', [
                'dashboardUrl' => CMDM_Settings::getOption(CMDM_Settings::OPTION_ADD_DASHBOARD_MENU) ? $dashboard_permalink : null,
                'categoriesUrl' => CMDM_Settings::getOption(CMDM_Settings::OPTION_ADD_ADDONS_MENU) ? $index_permalink : null
            ]
        );
        return $items . $link;
    }

    public static function showRating($id) {

        if (!CMDM_Settings::getOption(CMDM_Settings::OPTION_ENABLE_RATING))
            return;

        $download = CMDM_GroupDownloadPage::getInstance($id);
        if ($download instanceof CMDM_GroupDownloadPage) {
            $allowed = is_user_logged_in() && $download->isRatingAllowed(get_current_user_id());
            $stats = $download->getRatingStats();
            $ratingCounter = $stats['ratingsCount'];
            $avgRating = round($stats['ratingAvg']);

            echo self::_loadView('cmdownload/meta/rating', compact('id', 'ratingCounter', 'avgRating', 'allowed'));
        }
    }

    public static function rateHeader() {
        $id = self::_getParam('id');
        $rating = intval(self::_getParam('rating'));
        $download = CMDM_GroupDownloadPage::getInstance($id);
        $user = is_user_logged_in() ? get_current_user_id() : null;
        $allowed = $download->isRatingAllowed($user);
        if (!$allowed or empty($_POST['nonce']) or !wp_verify_nonce($_POST['nonce'], 'cmdm_rate')) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        } elseif (self::isPostRequest() && $download instanceof CMDM_GroupDownloadPage && !empty($user) && $rating > 0 && $rating <= 5) {
            $download->addRating($user, $rating);
            $stats = $download->getRatingStats();
            $ratingCounter = $stats['ratingsCount'];
            $avgRating = round($stats['ratingAvg']);
            header('Content-type: application/json');
            echo json_encode(compact('ratingCounter', 'avgRating'));
            exit;
        } else {
            header('HTTP/1.1 400 Bad Request');
            exit;
        }
    }

    public static function showSupport($id) {
        if (CMDM_Settings::getOption(CMDM_Settings::OPTION_SUPPORT_SHOW)) {
                $items = CMDM_SupportThread::getThreadsForDownload($id, false);
                echo self::_loadView('cmdownload/meta/support', $items);
        }
    }

    public static function showSupportThreadList($items) {
        if (CMDM_Settings::getOption(CMDM_Settings::OPTION_SUPPORT_SHOW)) {
            if (!is_array($items))
                $items = [$items];
            echo self::_loadView('cmdownload/meta/support-thread-list', compact('items'));
        }
    }

    public static function showSize($id) {
        $download = CMDM_GroupDownloadPage::getInstance($id);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max(intval($download->getFileSize()), 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $pow = max($pow, 1);
        $bytes /= (1 << (10 * $pow));
        echo number_format(round($bytes, 2), 2) . ' ' . $units[$pow];
    }

    // edit button controller
    public static function showEditLink($id) {
        $download = CMDM_GroupDownloadPage::getInstance($id);
        if ($download and $download instanceof CMDM_GroupDownloadPage && $download->isEditAllowed()) {
            echo self::_loadView('cmdownload/meta/edit-link', ['url' => self::getUrl('cmdownload', 'edit', ['id' => $id])]);
        }
    }

    public static function addHeader() {
        set_query_var('CMDM-cmdownload-dashboard', 1);
        /*
         * Initialize postError as an empty string
         */
        $postError = '';
        if (self::_userRequired()) {
            if (!CMDM_GroupDownloadPage::canUpload()) {
                if (!get_current_user_id()) {
                    self::_addError(__('You have to be logged in to see this page', 'cm-download-manager')
                        . ' <a href="' . esc_attr(wp_login_url($_SERVER['REQUEST_URI'])) . '" rel="nofollow">' . __('Log in', 'cm-download-manager') . '</a>');
                } else {
                    self::_addError(__('You have no permissions to perform this operation', 'cm-download-manager'));
                }
                return;
            }

            $form = CMDM_Form::getInstance('AddDownloadForm');

            if (self::isPostRequest() && $form->isValid($_POST) && empty($postError)) {
                $params = self::processSumitAddForm($form);
                if (isset($params['redirect']) && !empty($params['redirect'])) {
                    wp_redirect($params['redirect'], 303);
                    exit;
                }
            }

            if (!empty($postError)) {
                CMDM_BaseController::addMessage(self::MESSAGE_ERROR, $postError);
            }
        }

    }

    public static function addAction() {
        $form = CMDM_Form::getInstance('AddDownloadForm');
        if (self::isPostRequest() && !$form->isValid($_POST, true)) {
            $form->populate($_POST);
        }
        return ['form' => $form];
    }

    public static function editHeader() {
        set_query_var('CMDM-cmdownload-dashboard', 1);
        if (self::_userRequired()) {
            if (!CMDM_GroupDownloadPage::canShowDashboard()) {
                self::_addError(CMDM_Labels::getLocalized('error_not_permissions_view_page'));
                return;
            }
            $id = self::_getParam('id');
            if (empty($id) || !is_numeric($id)) {
                wp_redirect(self::getUrl('download', 'dashboard'), 303);
                exit;
            } else {
                // @note: if user is download's owner
                $owner_id = CMDM_GroupDownloadPage::getInstance($id)->getDownloadOwner();
                $is_owner = get_current_user_id() == $owner_id;
                if (!$is_owner) {
                    // @note: if user can't edit other downloads -- redirect
                    if (!CMDM_GroupDownloadPage::canUserEditOthersDownloads()) {
                        self::redirect();
                    }
                }

                global $wp;
                $page_url = home_url($wp->request);

                $download = CMDM_GroupDownloadPage::getInstance($id);
                if (!$download or !$download->isEditAllowed()) {
                    self::_addError('You are not allowed to edit this element');
                    return;
                } else {
                    $name = $download->getTitle();
                    $form = CMDM_Form::getInstance('AddDownloadForm', ['edit_id' => $id]);
                    if (self::isPostRequest() && $form->isValid($_POST)) {

                        $values = $form->getValues();

                        // CSRF protection
                        if (empty($values['nonce']) or !wp_verify_nonce($values['nonce'], 'cmdm_edit_form')) {
                            self::_addError('Invalid nonce.');
                            return;
                        }

                        self::updateAttachmentsNames();

                        $item = $download->update($values);

                        if ($item instanceof CMDM_GroupDownloadPage) {
                            $viewLink = ' - <a href="' . esc_attr(get_permalink($id)) . '">' . CMDM_Labels::getLocalized('download_updated_view_link_title') . '</a>';
                            CMDM_BaseController::addMessage(self::MESSAGE_SUCCESS, sprintf(CMDM_Labels::getLocalized('upload_updated_msg'), $name)
                                . $viewLink);
                        } else {
                            CMDM_BaseController::addMessage(self::MESSAGE_ERROR, __('There was an error while editing element', 'cm-download-manager') . ': "' . $item . '"');
                        }
                        wp_redirect($_SERVER['REQUEST_URI'], 303);
                        exit;
                    }
                }
            }
        }
    }

    public static function editAction() {
        $id = self::_getParam('id');
        $instance = CMDM_GroupDownloadPage::getInstance($id);

        if (!$instance->getId()) {
            $form = "Download doesn't exist.";
            return ['form' => $form, 'download' => $instance];
        }

        $form = CMDM_Form::getInstance('AddDownloadForm', ['edit_id' => $id]);
        if (self::isPostRequest() && !$form->isValid($_POST, true)) {
            $form->populate($_POST);
        } else {
            $form->setDefaults([
                'title' => $instance->getTitle(),
                'version' => $instance->getVersion(),
                'categories' => $instance->getCategoriesIds(),
                'packageType' => $instance->getPackageType(),
                'package' => $instance->getAttachments(),
                'description' => $instance->getDescription(),
                'excerpt' => $instance->getExcerpt(),
                'screenshots' => $instance->getScreenshotsIds(),
                'thumbnail' => $instance->getThumbnailId(),
                'admin_supported' => $instance->isRecommended(),
                'support_notifications' => $instance->isOwnerNotified(),
                'numberOfDownloads' => $instance->getNumberOfDownloads(),
            ]);
            do_action('cmdm_upload_form_set_defaults', $form, $id);
        }

        return ['form' => $form, 'download' => $instance];
    }

    protected static function updateAttachmentsNames() {
        if (!empty($_POST['attachmentName'])) {
            foreach ($_POST['attachmentName'] as $attachmentId => $attachmentName) {
                if (!$attachmentId) {
                    continue;
                }
                if ($attachment = CMDM_DownloadFile::getById($attachmentId)) {
                    $attachment->setName($attachmentName)->save();
                }
            }
        }
    }

    public static function dashboardHeader() {
        if (self::_userRequired()) {
            if (!CMDM_GroupDownloadPage::canShowDashboard()) {
                self::_addError(CMDM_Labels::getLocalized('error_not_permissions_view_page'));
            }
        }
    }

    public static function showDashboard() {
        if (self::_userRequired()) {
            echo CMDM_MyDownloadsShortcode::shortcode(['permalink' => 1, 'show_menu' => 0]);
        }
    }

    public static function showDashboardMenu() {
        global $post;

        $canShowDashboard = CMDM_GroupDownloadPage::canShowDashboard();
        $canUpload = CMDM_GroupDownloadPage::canUpload();
        $viewPermalink = null;
        $editPermalink = null;
        $deletePermalink = null;
        $download = null;

        if (self::$query->get('CMDM-cmdownload-edit') and $id = self::_getParam('id')) { // Dashboard
            $download = CMDM_GroupDownloadPage::getInstance($id);
        } else if (self::$query->is_single() and !empty($post) and $post->post_type == CMDM_GroupDownloadPage::POST_TYPE) {
            $download = CMDM_GroupDownloadPage::getInstance($post->ID);
        }

        if ($canShowDashboard and $download and $download->isEditAllowed()) {
            if (self::$query->get('CMDM-cmdownload-edit')) { // Dashboard
                $viewPermalink = $download->getPermalink();
                $deletePermalink = $download->getDeleteUrl();
            } else { // Front-end
                $editPermalink = $download->getEditUrl();
                $deletePermalink = $download->getDeleteUrl();
            }
        }

        $deletePermalink = apply_filters('cmdm_dashboard_menu_delete_url', $deletePermalink);
        $indexUrl = apply_filters('cmdm_dashboard_menu_index_url', CMDM_get_url('cmdownloads', ''));
        $dashboardUrl = apply_filters('cmdm_dashboard_menu_dashboard_url', CMDM_get_url('cmdownload', 'dashboard'));
        $addUrl = apply_filters('cmdm_dashboard_menu_add_url', CMDM_get_url('cmdownload', 'add'));

        echo self::_loadView('cmdownload/meta/dashboard-menu',
            compact('viewPermalink', 'editPermalink', 'deletePermalink', 'canShowDashboard', 'canUpload', 'indexUrl', 'dashboardUrl', 'addUrl'));
    }

    public static function showSearchForm($url = null) {
        $searchAction = (empty($url) ? home_url(CMDM_GroupDownloadPage::$rewriteSlug) : $url);
        $searchSubmit = CMDM_Settings::getOption(CMDM_Settings::INDEX_PAGE_SEARCH_SUBMIT);
        echo self::_loadView('cmdownload/widget/search', [
            'searchAction' => $searchAction,
            'searchSubmit' => $searchSubmit,
            'searchQuery' => get_search_query(),
            'placeholder' => CMDM_Labels::getLocalized('search_placeholder')
        ]);
    }

    public static function getHeader() {

        $downloadId = (filter_input(INPUT_POST, 'id') ?: filter_input(INPUT_GET, 'cmdm_download_id'));
        if ($downloadId && is_numeric($downloadId) && static::_getHeaderVerifyNonce($downloadId)) {
            $download = CMDM_GroupDownloadPage::getInstance($downloadId);
            $shortcodeId = self::_getParam('shortcodeId');
            if (!empty($download) && $download instanceof CMDM_GroupDownloadPage && ($download->isVisible(get_current_user_id()))) {

                $validateErrors = apply_filters('cmdm_validate_before_download', [], $download->getId());
                if (!empty($validateErrors)) {
                    if (is_array($validateErrors)) {
                        foreach ($validateErrors as $error) {
                            CMDM_BaseController::addMessage(self::MESSAGE_ERROR . $shortcodeId, $error);
                        }
                    }
                } else {
                    try {
                        $StorageService = CMDM_StorageProvider::getInstance();
                        $StorageService->download($download);
                    } catch (Exception $e) {
                        CMDM_BaseController::addMessage(self::MESSAGE_ERROR . $shortcodeId, $e->getMessage());
                        //	wp_redirect(isset($_POST['backurl']) ? $_POST['backurl'] : get_permalink($download->getId()), 303);
                    }
                    exit;
                }
            }

            if (is_user_logged_in()) {
                $url = CMDM::getReferer() ?: static::getAccessDeniedPagePermalink() ?: self::getUrl(CMDM_GroupDownloadPage::$rewriteSlug, '');
            } else {
                $url = CMDM::getReferer() ?: wp_login_url($_SERVER['REQUEST_URI']);
            }
            wp_redirect($url);
            exit;
        } else {
            $url = static::getAccessDeniedPagePermalink() ?: self::getUrl(CMDM_GroupDownloadPage::$rewriteSlug, '');
            wp_redirect($url);
            exit;
        }
    }

    protected static function _getHeaderVerifyNonce($downloadId) {
        if ($postNonce = filter_input(INPUT_POST, 'cmdm_nonce')
            and wp_verify_nonce($postNonce, static::DOWNLOAD_NONCE)) {
            return true;
        }
        return false;
    }

    // delete button
    public static function delHeader() {
        if (self::_userRequired()) {
            if (!CMDM_GroupDownloadPage::canShowDashboard()) {
                self::_addError(CMDM_Labels::getLocalized('error_not_permissions_view_page'));
                return;
            }
            $id = self::_getParam('id');
            $nonce = self::_getParam('nonce');
            if (empty($id) || !is_numeric($id) || !wp_verify_nonce($nonce, 'cmdm_download_delete')) {
                wp_redirect(apply_filters('cmdm_delete_download_return_url', self::getUrl('cmdownload', 'dashboard')), 303);
                exit;
            } else {
                $download = CMDM_GroupDownloadPage::getInstance($id);
                $user = wp_get_current_user();
                if (!$download or !$download->isEditAllowed()) {
                    self::_addError(__('You are not allowed to delete this element', 'cm-download-manager'));
                    return;
                } else {
                    self::deleteDownload($download);
                }
            }
        }
    }

    public static function deleteDownload($download) {
        $name = $download->getTitle();

        if ((CMDM_StorageProvider::getInstance())->delete($download)) {
            CMDM_BaseController::addMessage(
                self::MESSAGE_SUCCESS,
                sprintf(CMDM_Labels::getLocalized('upload_deleted_msg'),
                    $name)
            );

        } else {

            CMDM_BaseController::addMessage(
                self::MESSAGE_ERROR,
                sprintf(__('There was an error while deleting "%s"',
                    'cm-download-manager'), $name)
            );
        }

        $redirect_url = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : self::getUrl('cmdownload', 'dashboard');
        wp_redirect(apply_filters('cmdm_delete_download_return_url', $redirect_url, 303));
        exit;
    }

    public static function publishHeader() {
        if (self::_userRequired()) {

            $id = self::_getParam('id');
            if (empty($id) || !is_numeric($id)) {
                wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                exit;
            }
            $download = CMDM_GroupDownloadPage::getInstance($id);

            if (!CMDM_GroupDownloadPage::canShowDashboard() or !$download) {
                self::_addError(CMDM_Labels::getLocalized('error_not_permissions_view_page'));
                return;
            }

            $name = $download->getTitle();
            if (!$download->canChangeStatus()) {
                self::_addError(__('You are not allowed to change status of this element', 'cm-download-manager'));
                return;
            } else {
                $download->setStatus('publish', true);
                CMDM_BaseController::addMessage(self::MESSAGE_SUCCESS, sprintf(CMDM_Labels::getLocalized('upload_published_msg'), $name));
                wp_redirect(apply_filters('cmdm_publish_download_return_url', self::getUrl('cmdownload', 'dashboard')), 303);
                exit;
            }
        }
    }

    public static function unpublishHeader() {
        if (self::_userRequired()) {

            $id = self::_getParam('id');
            if (empty($id) || !is_numeric($id)) {
                wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                exit;
            }
            $download = CMDM_GroupDownloadPage::getInstance($id);

            if (!CMDM_GroupDownloadPage::canShowDashboard()) {
                self::_addError(CMDM_Labels::getLocalized('error_not_permissions_view_page'));
                return;
            }

            $name = $download->getTitle();
            if (!$download->canChangeStatus()) {
                self::_addError(__('You are not allowed to change status of this element', 'cm-download-manager'));
                return;
            } else {
                $download->setStatus('draft', true);
                CMDM_BaseController::addMessage(self::MESSAGE_SUCCESS, sprintf(CMDM_Labels::getLocalized('upload_unpublished_msg'), $name));
                wp_redirect(apply_filters('cmdm_publish_download_return_url', self::getUrl('cmdownload', 'dashboard')), 303);
                exit;
            }
        }
    }

    public static function visibilityFilter($downloads) {
        if (!is_array($downloads))
            return $downloads;
        foreach ($downloads as $key => $download) {
            $instance = CMDM_GroupDownloadPage::getInstance($download);
            if (!$instance->isVisible()) {
                unset($downloads[$key]);
            }
        }
        return $downloads;
    }

    public static function uploadHeader() {
        try {

            if (!self::isPostRequest()) {
                $msg = __('Access to this page is forbidden.', 'cm-download-manager');
                CMDM_BaseController::addMessage(self::MESSAGE_ERROR, $msg);
                wp_redirect(self::getUrl('cmdownload', 'dashboard'), 403);
                exit;
            }

            if (!CMDM_GroupDownloadPage::canUpload()) {
                throw new Exception('Wrong access data');
            }

            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cmdm_file_upload')) {
                throw new Exception('Wrong access data');
            }

            $attachments = CMDM_DownloadFile::handleUpload(null);
            $attachmentId = array_pop($attachments);
            if (empty($attachmentId)) {
                throw new Exception('No attachments uploaded. Max file size is: ' . ini_get('upload_max_filesize'));
            }
            $attachment = CMDM_DownloadFile::getById($attachmentId);
            if (empty($attachment)) {
                throw new Exception('Missing attachment id=' . $attachmentId);
            }
            $response = [
                'jsonrpc' => 2.0, 'result' => null,
                'id' => $attachment->getId(),
                'fileName' => $attachment->getFileName(),
            ];

        } catch (Exception $e) {
            $response = [
                'error' => 'Cannot upload file: ' . $e->getMessage(),
                'exceptionMessage' => $e->getMessage(),
                'exceptionCode' => $e->getCode(),
                'exceptionClass' => get_class($e),
            ];
        }

        header('Content-type: application/json');
        echo json_encode($response);
        exit;
    }

    public static function screenshotsHeader() {
        if (self::isPostRequest()) {
            // Check post_max_size (http://us3.php.net/manual/en/features.file-upload.php#73762)
            $POST_MAX_SIZE = ini_get('post_max_size');
            $unit = strtoupper(substr($POST_MAX_SIZE, -1));
            $multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

            if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier * (int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
                self::handleUploadError(__("POST exceeded maximum allowed size.", 'cm-download-manager'));
                exit;
            }


            try {
                $screenshots = CMDM_Screenshot::handleUpload(null);
                $screenshotId = array_pop($screenshots);
                $screenshot = CMDM_Screenshot::getById($screenshotId);

                $response = [
                    'jsonrpc' => 2.0, 'result' => null,
                    'id' => $screenshot->getId(),
                    'fileName' => $screenshot->getFileName(),
                    'imgSrc' => $screenshot->getSmallUrl(),
                ];
            } catch (Exception $e) {
                $response = ['error' => 'Cannot upload file. Max file size is ' . ini_get('upload_max_filesize') . '.'];
            }

            header('Content-type: application/json');
            echo json_encode($response);
            exit;
        } else {
            $msg = __('Access to this page is forbidden.', 'cm-download-manager');
            CMDM_BaseController::addMessage(self::MESSAGE_ERROR, $msg);
            wp_redirect(self::getUrl('cmdownload', 'dashboard'), 403);
            exit;
        }
    }

    protected static function handleUploadError($message) {
        header("HTTP/1.1 500 Internal Server Error");
        $response = ['success' => 0, 'message' => $message];
        echo json_encode($response);
        exit;
    }

    public static function screenshotHeader() {
        $image = self::_getParam('img');
        $size = self::_getParam('size');
        CMDM_GroupDownloadPage::processImage($image, $size);
        exit;
    }

    public static function registerAdminColumns($columns) {
        $columns['author'] = 'Author';
        $columns['number_of_downloads'] = 'Times downloaded';
        $columns['status'] = 'Status';
        return $columns;
    }

    public static function adminColumnDisplay($columnName, $id) {
        $download = CMDM_GroupDownloadPage::getInstance($id);
        if (!$download) {
            return;
        }

        if ($columnName == 'author') {
            echo $download->getAuthorName();

        } elseif ($columnName == 'number_of_downloads') {
            echo $download->getNumberOfDownloadsColumn();

        } elseif ($columnName == 'status') {
            echo $download->getStatusLabel();
        }
    }

    public static function checkDirectoryAccessAdminNotice() {
        if (!current_user_can('manage_options'))
            return;
        try {
            $denyFile = array_filter(explode('/', CMDM_Attachment::getUploadDir(null)));
            array_pop($denyFile);
            $denyFile = '/' . implode('/', $denyFile) . '/.htaccess';
            $noticeId = md5(__METHOD__);
            if (!@file_exists($denyFile) and !self::isNoticeDismissed($noticeId)) {
                printf('<div class="error cmdm-protect-upload-dir cmdm-notice" data-notice-id="%s"><p>
	    				%s
		    			<a href="#" class="button button-more" style="margin-left:1em;" data-alt-text="%s">%s</a>
		    			<a href="#" class="cmdm-dismiss" style="float:right">%s</a>
	    			</p>
	    			<div class="more" style="display:none">
	    				<p>Create file "%s".<br />Include in this file the following:
	    				<pre style="background:#f0f0f0;padding:5px;">Order Deny,Allow' . PHP_EOL . 'Deny from all' . PHP_EOL . '&lt;FilesMatch "\.(?i:jpg|jpeg|png|gif|webp)$">' .
                    PHP_EOL . '&nbsp;&nbsp;&nbsp;Allow from all' . PHP_EOL . '&lt;/FilesMatch></pre></p>
	    				<p><strong>Notice:</strong> if you are using the preview option, add your documents extensions to the regexp (pdf|doc|docx).</p>
	    			</div></div>',
                    $noticeId,
                    CMDM::__('CM Download Manager: to add an additional protection for your upload directory you can create the following .htaccess file.'),
                    CMDM::__('Less'),
                    CMDM::__('More'),
                    CMDM::__('Dismiss'),
                    $denyFile
                );
            }
        } catch (Exception $e) {
            printf('<div class="error"><p>%s</p></div>', 'CM Download Manager: your wp-content/upload dir may not be writable! The plugin may not work.');
        }
    }

    /**
     * Creates CMDM screenshot from image grabed with WP Media Library.
     */
    public static function screenshotFromWP() {
        global $wpdb;

        header('content-type: application/json');
        $response = ['error' => 'An error occurred.'];

        if (isset($_POST['url'])) {
            $fileName = basename($_POST['url']);
            $postId = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND (meta_value LIKE %s OR meta_value LIKE %s)",
                '_wp_attachment_metadata', '%"' . $fileName . '"%', '%/' . $fileName . '"%'));
            if ($postId) {
                $sourcePath = get_attached_file($postId);
                $fileTargetPath = CMDM_DownloadFile::getUploadDir(0) . $fileName;
                if (file_exists($sourcePath) and is_readable($sourcePath)) {
                    if ($result = copy($sourcePath, $fileTargetPath)) {
                        chmod($fileTargetPath, 0666);
                        try {

                            // $screenshot = CMDM_Screenshot::create(null, $fileTargetPath);
                            $screenshot = CMDM_Screenshot::create($postId, $fileTargetPath);

                            $response = [
                                'jsonrpc' => 2.0, 'result' => null,
                                'id' => $screenshot->getId(),
                                'fileName' => $screenshot->getFileName(),
                                'imgSrc' => $screenshot->getUrl('thumbnail'),
                            ];
                        } catch (Exception $e) {
                            $response = ['error' => $e->getMessage()];
                        }
                    } else {
                        $response = ['error' => 'Cannot copy attachment.'];
                    }
                } else {
                    $response = ['error' => 'Attachment not found in filesystem.'];
                }
            } else {
                $response = ['error' => 'Attachment not found in database.'];
            }
        } else {
            $response = ['error' => 'Invalid request.'];
        }

        echo json_encode($response);
        exit;
    }

    static function displayIndexControls($displayOptions, $query, $view) {
        echo self::_loadView('cmdownload/meta/index-controls', compact('displayOptions', 'query', 'view'));
    }

    static function excludeAdminMediaAttachmentsJoin($join, WP_Query $query) {
        global $wpdb, $pagenow;
        if (is_admin() and !empty($pagenow) and in_array($pagenow, ['upload.php', 'media-upload.php'])
            and $query->is_main_query() and $query->get('post_type') == 'attachment') {
            $join .= $wpdb->prepare(" LEFT JOIN $wpdb->posts cmdm_parent
				ON cmdm_parent.ID = $wpdb->posts.post_parent AND cmdm_parent.post_type = %s", CMDM_GroupDownloadPage::POST_TYPE);
        }
        return $join;
    }

    static function excludeAdminMediaAttachmentsWhere($where, WP_Query $query) {
        global $wpdb, $pagenow;
        if (is_admin() and !empty($pagenow) and in_array($pagenow, ['upload.php', 'media-upload.php'])
            and $query->is_main_query() and $query->get('post_type') == 'attachment') {
            $where .= " AND cmdm_parent.ID IS NULL ";
        }
        return $where;
    }

    static function cmdm_display_supported_shortcodes() {
        echo self::_loadView('../backend/shortcodes');
    }

    static function before_delete_post($postId) {
        if ($post = get_post($postId) and
            $post->post_type == CMDM_GroupDownloadPage::POST_TYPE and
            $download = CMDM_GroupDownloadPage::getInstance($post)) {
            $attachments_ids = $download->getAttachmentsIds();

            foreach ($attachments_ids as $id) {
                if ($obj = CMDM_DownloadFile::getById($id)) {
                    $obj->delete();
                } else {
                    wp_delete_post($id, true);
                }
            }

            $screenshots_ids = $download->getScreenshotsIds();
            foreach ($screenshots_ids as $id) {
                if ($obj = CMDM_Screenshot::getById($id)) {
                    $obj->delete();
                } else {
                    wp_delete_post($id, true);
                }
            }

            $path = $download->getUploadPath();

            if (is_dir($path)) {
                $objects = scandir($path);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        unlink($path . "/" . $object);
                    }
                }
                rmdir($path);
            }
        }
    }

    public static function handleAfterAddDownloadsNumber(CMDM_GroupDownloadPage $download) {
        $current_user_id = get_current_user_id();

        if ($current_user_id == $download->getAuthorId()) {
            return;
        }

        $number_of_downloads = $download->getNumberOfDownloads();
        if ($number_of_downloads > 0 || $number_of_downloads == 0) {
            $number_of_downloads++;
        }

        $download->setNumberOfDownloads($number_of_downloads);
    }

    public static function processSumitAddForm($form) {

        $item = null;
        self::updateAttachmentsNames();
        try {
            $values = $form->getValues();
            // CSRF protection
            if (empty($values['nonce']) or !wp_verify_nonce($values['nonce'], 'cmdm_edit_form')) {
                throw new Exception('Invalid nonce');
            }
            // addNewDownload createNewDownload newDownload addDownload
            // This line adding new download
            $item = CMDM_GroupDownloadPage::newInstance($values);
            do_action('cmdm_after_insert_download', $item);
        } catch (Exception $e) {
            CMDM_BaseController::addMessage(self::MESSAGE_ERROR, $e->getMessage());
        }

        if (!empty($item) and $item instanceof CMDM_GroupDownloadPage) {
            $viewLink = ' - <a href="' . esc_attr(get_permalink($item->getId())) . '" target="_top">' . CMDM::__('View') . ' &raquo;</a>';
            $msg = sprintf(CMDM_Labels::getLocalized($item->isPublished() ? 'upload_added_msg' : 'upload_held_moderation_msg'), $item->getTitle())
                . $viewLink;
            $msg .= do_action('cmdm_upload_msg_success');

            CMDM_BaseController::addMessage(self::MESSAGE_SUCCESS, $msg);
            $params = ['id' => $item->getId()];
            if ($layout = self::_getParam('layout')) {
                $params['layout'] = $layout;
            }
            $url = self::getUrl('cmdownload', 'edit', $params);
            return ['redirect' => apply_filters('cmdm_download_add_success_url', $url, $item)];
        } else {
            CMDM_BaseController::addMessage(self::MESSAGE_ERROR, __('There was an error while adding new download', 'cm-download-manager') . ($item ? ': "' . $item . '"' : ''));
        }

        return ['redirect' => self::getUrl('cmdownload', 'add')];
    }
}
