<?php
include_once CMDM_PATH . '/lib/models/forms/AddDownloadForm.php';

class CMDM_CmdownloadController extends CMDM_BaseController {
    const DOWNLOAD_NONCE = 'CMDM_download_nonce';
    const OPTION_SUPPORTED_IMAGE = 'CMDM_option_supported_image';
    const OPTION_SEARCH_PLACEHOLDER = 'CMDM_option_search_placeholder';
    const OPTION_FILTER_PLACEHOLDER = 'CMDM_option_filter_placeholder';
    const OPTION_ADD_ADDONS_MENU = 'CMDM_option_add_addons_menu';
    const OPTION_ADD_DASHBOARD_MENU = 'CMDM_option_add_dashboard_menu';
    const OPTION_VERSION_FIELD_SHOW = 'CMDM_show_version';
    const OPTION_SUPPORT_SHOW = 'CMDM_support_enable';
    const OPTION_ADMIN_RECOMMEND_SHOW = 'CMDM_admin_recommend_show';
    const OPTION_DOWNLOAD_BUTTON_WIDGET_AREA = 'CMDM_download_button_widget_area';

    const AREA_SIDEBAR = 'cm-download-manager-sidebar';
    const AREA_SINGLE_TOP = 'cmdm-single-top';
    const AREA_SINGLE_SCREENSHOTS_BELOW = 'cmdm-single-screenshots-below';
    const AREA_SINGLE_BOTTOM = 'cmdm-single-bottom';

    const DEFAULT_SCREENSHOT_OPTION = 'CMDM_option_default_screenshot_free';

    public static function initialize() {
        /*
         * FREE
         */
        add_filter('wp_nav_menu_items', array(get_class(), 'addMenuItem'), 1, 1);
        add_filter('posts_search', array(get_class(), 'alterSearchQuery'), 99, 2);
        add_filter('template_include', array(get_class(), 'overrideTemplate'));
        add_action('admin_init', array(get_class(), 'adminResources'));
        add_action('CMDM_show_search_form', array(get_class(), 'showSearchForm'));
        add_action('CMDM_show_categories', array(get_class(), 'showCategories'), 1, 1);
        add_action('CMDM_show_item_categories', array(get_class(), 'showItemCategories'), 1, 1);
        add_action('CMDM_show_rating', array(get_class(), 'showRating'), 1, 1);
        add_action('CMDM_show_details', array(get_class(), 'showDetails'), 1, 1);
        add_action('CMDM_show_download_button', array(get_class(), 'showDownloadButton'), 1, 2);
        add_action('CMDM_show_support_threads_list', array(get_class(), 'showSupportThreadList'), 1, 1);
        add_action('CMDM_show_support', array(get_class(), 'showSupport'), 1, 1);
        add_action('CMDM_show_screenshots', array(get_class(), 'showScreenshots'), 1, 1);
        add_action('CMDM_show_edit_link', array(get_class(), 'showEditLink'), 1, 1);
        add_action('CMDM_show_size', array(get_class(), 'showSize'), 1, 1);
        add_action('CMDM_show_number_of_downloads', array(get_class(), 'showNumberOfDownloads'), 1, 1);
        add_filter('CMDM_title_controller', array(get_class(), 'overrideControllerTitle'), 1, 1);
        add_filter('CMDM_admin_settings', array(get_class(), 'processAddonsTitlePage'), 1, 1);
        add_filter('CMDM_admin_settings', array(get_class(), 'processSearchPlaceholderSetting'), 1, 1);
        add_filter('CMDM_admin_settings', array(get_class(), 'processMenuSetting'), 1, 1);
        add_filter('CMDM_admin_settings', array(get_class(), 'processExtensionsSetting'), 1, 1);
        add_filter('CMDM_admin_settings', array(get_class(), 'processDefaultScreenshot'), 1, 1);
        add_filter('manage_edit-' . CMDM_GroupDownloadPage::POST_TYPE . '_columns', array(get_class(), 'registerAdminColumns'));
        add_filter('manage_' . CMDM_GroupDownloadPage::POST_TYPE . '_posts_custom_column', array(get_class(), 'adminColumnDisplay'), 10, 2);
        add_action('admin_notices', array(get_class(), 'checkCategoriesAdminNotice'));
        // add_action( 'admin_notices', array(get_class(), 'checkDirectoryAccessAdminNotice'));
        do_action('CMDM_custom_post_type_nav', CMDM_GroupDownloadPage::POST_TYPE);
        do_action('CMDM_custom_taxonomy_nav', CMDM_GroupDownloadPage::CAT_TAXONOMY);
        CMDM_SupportThread::init();

        // add_filter('dynamic_sidebar_has_widgets', array(__CLASS__, 'dynamic_sidebar_has_widgets'), 10, 2);
        add_filter('dynamic_sidebar_before', array(__CLASS__, 'dynamic_sidebar_before'), 10, 2);

        self::createDefaultCategory();

        // adding threads and comments by ajax
        if (defined('DOING_AJAX') && DOING_AJAX) {
            add_action('wp_ajax_addthread', array(get_class(), '_processAddThread'));
            add_action('wp_ajax_nopriv_addthread', array(get_class(), '_processAddThread'));
            add_action('wp_ajax_addcomment', array(get_class(), '_processAddCommentToThread'));
            add_action('wp_ajax_nopriv_addcomment', array(get_class(), '_processAddCommentToThread'));
        }

    }

    static function createDefaultCategory() {
        $categories = get_terms(CMDM_GroupDownloadPage::CAT_TAXONOMY, array('fields' => 'ids', 'hide_empty' => false, 'parent' => 0));
        if (empty($categories)) {
            $term = wp_insert_term('General', CMDM_GroupDownloadPage::CAT_TAXONOMY);
        }
    }

    public static function registerSidebars() {
        register_sidebar(array(
            'id' => self::AREA_SIDEBAR,
            'name' => 'CM Download Manager Sidebar',
            'description' => 'This sidebar is shown on CM Download Manager Index'
        ));
        register_sidebar(array(
            'id' => self::AREA_SINGLE_TOP,
            'name' => 'CMDM Download Top',
            'description' => 'Widget area displayed at top of the single download page.'
        ));
        register_sidebar(array(
            'id' => self::AREA_SINGLE_SCREENSHOTS_BELOW,
            'name' => 'CMDM Below Screenshots',
            'description' => 'Widget area displayed below screenshots on the single download page.'
        ));
        register_sidebar(array(
            'id' => self::AREA_SINGLE_BOTTOM,
            'name' => 'CMDM Download Bottom',
            'description' => 'Widget area displayed at bottom of the single download page.'
        ));
    }

    static function dynamic_sidebar_before($index, $hasWidgets) {

        // Display download button
        if (CMDM_GroupDownloadPage::getDownloadButtonWidgetArea() == $index) {
            do_action('CMDM_show_details', get_the_id());
            do_action('CMDM_show_download_button', get_the_id());
        }
    }

    public static function processAddonsTitlePage($params = array()) {
        if (!empty($_POST['addons_title']) AND self::canSaveSettings()) {
            update_option(CMDM_GroupDownloadPage::OPTION_ADDONS_TITLE, sanitize_text_field($_POST['addons_title']));
        }
        $params['addons_title'] = CMDM_GroupDownloadPage::getAddonsTitle();
        return $params;
    }

    public static function processMenuSetting($params = array()) {
        if (!empty($_POST) AND self::canSaveSettings()) {
            update_option(self::OPTION_ADD_ADDONS_MENU, isset($_POST['add_addons_menu']) ? 1 : 0);
            update_option(self::OPTION_ADD_DASHBOARD_MENU, isset($_POST['add_dashboard_menu']) ? 1 : 0);
        }
        $params['add_addons_menu'] = self::addAddonsMenu();
        $params['add_dashboard_menu'] = self::addDashboardMenu();
        return $params;
    }

    public static function processSearchPlaceholderSetting($params = array()) {
        $canSaveSettings = self::canSaveSettings();
        if (!empty($_POST['search_placeholder_text']) AND $canSaveSettings) {
            update_option(self::OPTION_SEARCH_PLACEHOLDER, sanitize_text_field($_POST['search_placeholder_text']));
        }
        $params['searchPlaceholder'] = self::getSearchPlaceholder();

        if (isset($_POST['forceBrowserDownload']) AND $canSaveSettings) {
            update_option(CMDM_GroupDownloadPage::OPTION_FORCE_BROWSER_DOWNLOAD_ENABLED, intval($_POST['forceBrowserDownload']));
        }
        $params['forceBrowserDownload'] = CMDM_GroupDownloadPage::forceBrowserDownloadEnabled();

        if (isset($_POST[self::OPTION_VERSION_FIELD_SHOW]) AND $canSaveSettings) {
            update_option(self::OPTION_VERSION_FIELD_SHOW, intval($_POST[self::OPTION_VERSION_FIELD_SHOW]));
        }
        $params['showVersionField'] = CMDM_GroupDownloadPage::showVersionField();

        if (isset($_POST[self::OPTION_SUPPORT_SHOW]) AND $canSaveSettings) {
            update_option(self::OPTION_SUPPORT_SHOW, intval($_POST[self::OPTION_SUPPORT_SHOW]));
        }
        $params['showSupport'] = CMDM_GroupDownloadPage::showSupport();

        if (isset($_POST[self::OPTION_ADMIN_RECOMMEND_SHOW]) AND $canSaveSettings) {
            update_option(self::OPTION_ADMIN_RECOMMEND_SHOW, intval($_POST[self::OPTION_ADMIN_RECOMMEND_SHOW]));
        }
        $params['showAdminRecommend'] = CMDM_GroupDownloadPage::showAdminRecommend();

        if (isset($_POST[self::OPTION_DOWNLOAD_BUTTON_WIDGET_AREA]) AND $canSaveSettings) {
            update_option(self::OPTION_DOWNLOAD_BUTTON_WIDGET_AREA, $_POST[self::OPTION_DOWNLOAD_BUTTON_WIDGET_AREA]);
        }
        $params['downloadButtonPosition'] = CMDM_GroupDownloadPage::getDownloadButtonWidgetArea();

        return $params;
    }

    public static function processDefaultScreenshot($params = array()) {
        if (!empty($_POST['upload_default_screenshot']) AND self::canSaveSettings()) {
            update_option(self::DEFAULT_SCREENSHOT_OPTION, $_POST['upload_default_screenshot']);
        }
        $params['default_screenshot'] = self::getDefaultScreenshot();
        return $params;
    }

    public static function processExtensionsSetting($params = array()) {
        $post_allowed_extensions = sanitize_text_field($_POST['allowed_extensions'] ?? '');
        $post_allowed_extensions = str_replace(' ', '', $post_allowed_extensions);

        if (!empty($post_allowed_extensions) && self::canSaveSettings()) {

            $new_extensions = array_unique(explode(',', $post_allowed_extensions));
            $new_extensions_with_mime_types = [];
            $getMimeTypes = wp_get_mime_types();
            $getMimeTypes = array_flip($getMimeTypes);
            foreach ($new_extensions as $key => $oneExtension) {
                foreach ($getMimeTypes as $mimeType) {
                    if (strstr($mimeType, $oneExtension)) {
                        $new_extensions_with_mime_types[$key] = $oneExtension;
                    }
                }
            }

            update_option(CMDM_GroupDownloadPage::ALLOWED_EXTENSIONS_OPTION, $new_extensions_with_mime_types);
        }

        $params['allowed_extensions'] = get_option(
            CMDM_GroupDownloadPage::ALLOWED_EXTENSIONS_OPTION,
            self::getDefaultAllowedExtensions()
        );

        return $params;
    }

    static function getDefaultAllowedExtensions() {
        return explode(',', 'zip,doc,docx,pdf,jpg,gif,png,jpeg');
    }

    public static function addAddonsMenu() {
        return get_option(self::OPTION_ADD_ADDONS_MENU, 0);
    }

    public static function addDashboardMenu() {
        return get_option(self::OPTION_ADD_DASHBOARD_MENU, 0);
    }

    public static function alterSearchQuery($search, $query) {
        if (((isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == CMDM_GroupDownloadPage::POST_TYPE) && (!isset($query->query_vars['widget']) || $query->query_vars['widget'] !== true)) && !$query->is_single && !$query->is_404 && !$query->is_author && isset($_GET['CMDsearch'])) {
            global $wpdb;
            $search_term = sanitize_text_field($_GET['CMDsearch']);
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
                    // $term = esc_sql(like_escape($term));
                    // $search .= "{$searchand}(($wpdb->posts.post_title LIKE '{$n}{$term}{$n}') OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}'))";
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
        if ((CMDM_GroupDownloadPage::POST_TYPE == get_post_type($post_id)) && ($_GET['action'] === 'edit')) {
            wp_redirect(self::getUrl('cmdownload', 'edit', array('id' => $post_id)), 301);
            exit;
        } elseif (isset($_GET['post_type']) && ($_GET['post_type'] === CMDM_GroupDownloadPage::POST_TYPE) && ($pagenow === 'post-new.php')) {
            wp_redirect(self::getUrl('cmdownload', 'add'), 301);
            exit;
        }
    }

    public static function getDefaultScreenshot() {
        return get_option(self::DEFAULT_SCREENSHOT_OPTION, CMDM_RESOURCE_URL . 'imgs/no_screenshot.png');
    }

    public static function overrideControllerTitle($title) {
        if ($title === 'Cmdownload') {
            $title = 'CM Download';
        }

        return $title;
    }

    /**
     * (+RA) Adding question to download item
     */
    public static function _processAddThread() {
        $post_id = isset($_POST['post_id']) ? sanitize_text_field($_POST['post_id']) : "";
        $title = "";
        $content = "";
        if (isset($_POST['thread_title'])) {
            $title = esc_attr(strip_tags($_POST['thread_title'], implode(CMDM_BaseController::ALLOWED_TITLE_TAGS)));
        }
        if (isset($_POST['thread_title']) && isset($_POST['thread_comment'])) {
            $content = esc_attr(strip_tags($_POST['thread_comment'], implode(CMDM_BaseController::ALLOWED_CONTENT_TAGS)));
        }

        $notify = isset($_POST['thread_notify']) ? (bool)$_POST['thread_notify'] : "";
        $author_id = get_current_user_id();
        $error = false;
        $messages = array();

        try {
            // CSRF protection
            if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cmdm_topic_add')) {
                throw new Exception(serialize(array('Invalid nonce')));
            }
            if (!$post_id) {
                throw new Exception(serialize(array('Invalid cmdm post id')));
            }
            $comment_id = CMDM_SupportThread::addThread($post_id, $title, $content, $author_id, $notify);
        } catch (Exception $e) {
            $messages = unserialize($e->getMessage());
            $error = true;
        }

        if (defined('DOING_AJAX') && DOING_AJAX) {
            header('Content-type: application/json');
            echo json_encode(array('success' => (int)(!$error), 'comment_id' => $comment_id, 'message' => $messages));
            wp_die();
        } else {
            wp_redirect(get_permalink($post_id) . '#support', 303);
            exit;
        }
    }

    /**
     * (+RA) Adding comment to question to download item
     */
    public static function _processAddCommentToThread() {
        $post_id = isset($_POST['post_id']) ? sanitize_text_field($_POST['post_id']) : "";
        $thread_id = isset($_POST['thread_id']) ? sanitize_text_field($_POST['thread_id']) : "";
        $content = esc_attr(strip_tags($_POST['thread_comment'], implode(CMDM_BaseController::ALLOWED_CONTENT_TAGS)));
        $notify = isset($_POST['thread_notify']) ? (bool)sanitize_text_field($_POST['thread_notify']) : false;
        $resolved = isset($_POST['thread_resolved']) ? (bool)sanitize_text_field($_POST['thread_resolved']) : false;
        $author_id = get_current_user_id();
        $error = false;
        $messages = array();
        try {
            // CSRF protection
            if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cmdm_topic_comment')) {
                throw new Exception(serialize(array('Invalid nonce')));
            }
            if (!$post_id) {
                throw new Exception(serialize(array('Invalid cmdm post id')));
            }
            $comment_id = CMDM_SupportThread::addCommentToThread($post_id, $thread_id, $content, $author_id, $notify, $resolved);
        } catch (Exception $e) {
            $messages = unserialize($e->getMessage());
            $error = true;
        }
        $target_url = $error ? get_permalink($post_id) . 'topic/' . $thread_id . '/' : get_permalink($post_id) . 'topic/' . $thread_id . '/#comment-' . $comment_id;
        if (defined('DOING_AJAX') && DOING_AJAX) {
            header('Content-type: application/json');
            echo json_encode(array(
                'success' => (int)(!$error),
                'comment_id' => $comment_id,
                'commentData' => CMDM_SupportThread::getCommentData($comment_id),
                'message' => $messages,
                'target_url' => $target_url
            ));
            wp_die();
        } else {
            wp_redirect($target_url, 303);
            exit;
        }
    }

    protected static function _processListThread() {
        global $wp_query;
        $post = $wp_query->post;
        $page = $wp_query->query_vars['CMDM-comment-page'];
        $threads = CMDM_SupportThread::getThreadsForDownload($post->ID, $page);
        do_action('CMDM_show_support_threads_list', $threads['items']);
        exit;
    }

    protected static function _showThreadDetails() {
        global $wp_query;
        $post = $wp_query->post;
        $thread_id = $wp_query->query_vars['CMDM-comment-id'];
        $thread = CMDM_SupportThread::getThread($thread_id);
        echo self::_loadView('cmdownload/thread', array('thread' => $thread));
        exit;
    }

    public static function processQueryVars() {
        $action = get_query_var('CMDM-comment-action');
        if (!empty($action)) {
            switch ($action) {
                case 'add':
                    if (get_query_var('CMDM-parent-id') > 0) self::_processAddCommentToThread();
                    else self::_processAddThread();
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

    /**
     * (+RA) Include templates on front
     */
    public static function overrideTemplate($template) {
        if (get_query_var('post_type') == CMDM_GroupDownloadPage::POST_TYPE || is_tax(CMDM_GroupDownloadPage::CAT_TAXONOMY)) {
            if (is_single() || is_404()) {
                // for image slider
                wp_enqueue_style(CMDM_PREFIX . 'slick', CMDM_RESOURCE_URL . 'vendor/slick/slick.min.css', array(), "1.8.1");
                wp_enqueue_style(CMDM_PREFIX . 'slick-theme', CMDM_RESOURCE_URL . 'vendor/slick/slick-theme.css', array(), "1.8.1");
                wp_enqueue_script(CMDM_PREFIX . 'slick', CMDM_RESOURCE_URL . 'vendor/slick/slick.min.js', array('jquery'), "1.8.1", true);
                //
                wp_enqueue_script(CMDM_PREFIX . 'single', CMDM_RESOURCE_URL . 'js/single.js', array('jquery', CMDM_PREFIX . 'slick'), CMDM_VERSION, true);

                self::processQueryVars();
                $template = self::locateTemplate(array(
                    'cmdownload/single'
                ), $template);
            } else {
                if (isset($_GET['show']) && ($_GET['show'] === 'contributors')) {
                    $template = self::locateTemplate(array(
                        'cmdownload/authors',
                    ), $template);
                } else $template = self::locateTemplate(array(
                    'cmdownload/index'
                ), $template);
            }
            add_filter('body_class', array(get_class(), 'adjustBodyClass'), 20, 2);
        }
        return $template;
    }

    public static function adjustBodyClass($wp_classes, $extra_classes) {
        foreach ($wp_classes as $key => $value) {
            if ($value == 'singular') unset($wp_classes[$key]);
        }
        return array_merge($wp_classes, (array)$extra_classes);
    }

    public static function addMenuItem($items) {
        $link = self::_loadView('cmdownload/meta/menu-item', array(
                'dashboardUrl' => self::addDashboardMenu() ? self::getUrl('cmdownload', 'dashboard') : null,
                'categoriesUrl' => self::addAddonsMenu() ? self::getUrl(CMDM_GroupDownloadPage::$rewriteSlug, '') : null
            )
        );
        return $items . $link;
    }

    public static function showRating($id) {
        $download = CMDM_GroupDownloadPage::getInstance($id);
        if ($download instanceof CMDM_GroupDownloadPage) {
            $ratingCounter = 0;
            $allowed = is_user_logged_in() ? $download->isRatingAllowed(get_current_user_id()) : false;
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
        if (!$allowed OR empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cmdm_rate')) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        } elseif (self::_isPost() && $download instanceof CMDM_GroupDownloadPage && !empty($user) && $rating > 0 && $rating <= 5) {
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

    public static function getSearchPlaceholder() {
        return get_option(self::OPTION_SEARCH_PLACEHOLDER, 'Search...');
    }

    public static function showDetails($id) {
        $download = CMDM_GroupDownloadPage::getInstance($id);
        if ($download AND $download instanceof CMDM_GroupDownloadPage) {
            $author = $download->getAuthor()->display_name;
            $version = $download->getVersion();
            $updated = $download->getUpdated();
            $adminSupported = $download->isRecommended();
            echo self::_loadView('cmdownload/meta/details', compact('author', 'version', 'updated', 'adminSupported'));
        }
    }

    public static function showDownloadButton($id) {
        if ($download = CMDM_GroupDownloadPage::getInstance($id)) {
            $nonce = wp_create_nonce(self::DOWNLOAD_NONCE);
            $url = self::getUrl('cmdownload', 'get') . '/file/' . urlencode(str_replace('.php', '-php', $download->getFileName()));
            echo self::_loadView('cmdownload/meta/download-form', array('action_url' => $url, 'nonce' => $nonce, 'download_id' => $id));
        }
    }

    public static function showSupport($id) {
        //    	wp_register_script('cmdm-jquery-form', CMDM_RESOURCE_URL . '/js/jquery.form.js', array('jquery'));
        $items = CMDM_SupportThread::getThreadsForDownload($id, false);
        echo self::_loadView('cmdownload/meta/support', $items);
    }

    public static function showSupportThreadList($items) {
        if (!is_array($items)) $items = array($items);
        echo self::_loadView('cmdownload/meta/support-thread-list', compact('items'));
    }

    public static function showScreenshots($id) {
        echo self::_loadView('cmdownload/meta/screenshots');
    }

    public static function showSize($id) {
        $download = CMDM_GroupDownloadPage::getInstance($id);
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max(intval($download->getFileSize()), 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $pow = max($pow, 1);
        $bytes /= (1 << (10 * $pow));
        echo number_format(round($bytes, 2), 2) . ' ' . $units[$pow];
    }

    public static function showNumberOfDownloads($id) {
        echo CMDM_GroupDownloadPage::getInstance($id)->getNumberOfDownloads();
    }

    public static function showEditLink($id) {
        $download = CMDM_GroupDownloadPage::getInstance($id);
        if ($download instanceof CMDM_GroupDownloadPage && $download->isEditAllowed(get_current_user_id())) echo self::_loadView('cmdownload/meta/edit-link', array('url' => self::getUrl('cmdownload', 'edit', array('id' => $id))));
    }

    public static function showItemCategories($id) {
        $download = CMDM_GroupDownloadPage::getInstance($id);
        if ($download instanceof CMDM_GroupDownloadPage) {
            echo self::_loadView('cmdownload/meta/categories', array('categories' => $download->getCategories(true), 'taxonomy' => CMDM_GroupDownloadPage::CAT_TAXONOMY));
        }
    }

    public static function showCategories() {
        echo self::_loadView('cmdownload/categories');
    }

    public static function addHeader() {
        if (self::_userRequired()) {

            $form = CMDM_Form::getInstance('AddDownloadForm');

            if (self::_isPost() && $form->isValid($_POST)) {
                $item = CMDM_GroupDownloadPage::newInstance($form->getValues());

                if ($item instanceof CMDM_GroupDownloadPage) {
                    self::addMessage(self::MESSAGE_SUCCESS, sprintf(__('"%s" has been succesfully added', 'cm-download-manager'),
                            esc_html($item->getTitle()))
                        . ' - <a href="' . esc_attr(get_permalink($item->getId())) . '">' . __('View', 'cm-download-manager') . ' &raquo;</a>');
                } else {
                    self::addMessage(self::MESSAGE_ERROR, __('There was an error while adding new element', 'cm-download-manager') . ': "' . $item . '"');
                }
                wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                exit;
            }
        }
    }

    public static function addAction() {
        $form = CMDM_Form::getInstance('AddDownloadForm');
        if (self::_isPost() && !$form->isValid($_POST, true)) {
            $form->populate($_POST);
        }
        return array('form' => $form);
    }

    public static function editHeader() {
        if (self::_userRequired()) {
            $id = self::_getParam('id');

            if (stristr($id, '?_wpnonce')) {
                $id = stristr($id, '?_wpnonce', true);
            }

            if (empty($id) || !is_numeric($id)) {
                wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                exit;
            } else {
                $download = CMDM_GroupDownloadPage::getInstance($id);
                $name = $download->getTitle();
                if (!$download->isEditAllowed(get_current_user_id())) {
                    self::_addError('You are not allowed to edit this element');
                    return;
                } else {

                    $form = CMDM_Form::getInstance('AddDownloadForm', array('edit_id' => $id));

                    if (self::_isPost() && $form->isValid($_POST)) {
                        $nonce = wp_verify_nonce($_POST['__wpnonce'], 'edit');
                        if ($nonce) {
                            $item = $download->update($form->getValues());
                            if ($item instanceof CMDM_GroupDownloadPage) {
                                self::addMessage(self::MESSAGE_SUCCESS, sprintf(__('"%s" has been succesfully updated', 'cm-download-manager'), $name)
                                    . ' - <a href="' . esc_attr(get_permalink($id)) . '">View &raquo;</a>');
                                wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                                exit;
                            } else {
                                self::addMessage(self::MESSAGE_ERROR, __('There was an error while editing element', 'cm-download-manager') . ': "' . $item . '"');
                            }
                        } else {
                            self::addMessage(self::MESSAGE_ERROR, __('There was an error while editing element', 'cm-download-manager') . ': "' . $item . '"');
                        }

                        wp_redirect(self::getUrl('cmdownload', 'edit', array('id' => $id)), 303);
                        exit;

                    }
                }
            }
        }
    }

    public static function editAction() {
        $id = self::_getParam('id');

        $instance = CMDM_GroupDownloadPage::getInstance($id);

        $form = CMDM_Form::getInstance('AddDownloadForm', array('edit_id' => $id));

        if (self::_isPost() && !$form->isValid($_POST, true)) $form->populate($_POST);
        else $form->setDefaults(array(
            'title' => $instance->getTitle(),
            'version' => $instance->getVersion(),
            'categories' => $instance->getCategories(),
            'package' => $instance->getDownloadFile(),
            'description' => $instance->getDescription(),
            'screenshots' => $instance->getScreenshots(),
            'screenshots_v2' => $instance->getScreenshotsV2(),
            'admin_supported' => $instance->isRecommended(),
            'support_notifications' => $instance->isOwnerNotified()
        ));
        return array('form' => $form);
    }

    public static function dashboardHeader() {
        self::_userRequired();
    }

    public static function dashboardAction() {
        return array('myDownloads' => CMDM_GroupDownloadPage::getDownloadsByUser(get_current_user_id()));
    }

    public static function showSearchForm() {
        echo self::_loadView('cmdownload/widget/search', array('searchAction' => home_url(CMDM_GroupDownloadPage::$rewriteSlug), 'searchQuery' => get_search_query(), 'placeholder' => self::getSearchPlaceholder()));
    }

    public static function getHeader() {
        if (self::_userRequired()) {
            if (isset($_POST['_wpnonce']) /* && wp_verify_nonce($_POST['_wpnonce'], self::DOWNLOAD_NONCE) */ && is_numeric($_POST['id'])) {
                $p = sanitize_text_field($_POST['id']);
                $download = CMDM_GroupDownloadPage::getInstance($p);
                if (!empty($download) && $download instanceof CMDM_GroupDownloadPage) {
                    $download->download();
                }
            } else {
                wp_redirect(self::getUrl('downloads', ''), 303);
                exit;
            }
        }
    }

    public static function delHeader() {
        if (self::_userRequired()) {
            $id = self::_getParam('id');
            if (stristr($id, '?_wpnonce')) {
                $id = stristr($id, '?_wpnonce', true);
            }

            if (empty($id) || !is_numeric($id)) {
                wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                exit;
            } else {
                $download = CMDM_GroupDownloadPage::getInstance($id);
                if (!$download->isEditAllowed(get_current_user_id())) {
                    self::_addError(__('You are not allowed to delete this element', 'cm-download-manager'));
                    return;
                } else {
                    $name = $download->getTitle();
                    $nonce = wp_verify_nonce($_REQUEST['_wpnonce'], 'del' . $download->getId());
                    if ($nonce == true) {
                        if ($download->delete()) self::addMessage(self::MESSAGE_SUCCESS, sprintf(__('"%s" has been succesfully deleted', 'cm-download-manager'), $name));
                        else self::addMessage(self::MESSAGE_ERROR, sprintf(__('There was an error while deleting "%s"', 'cm-download-manager'), $name));
                        wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                    } else {
                        wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                    }
                    exit;
                }
            }
        }
    }

    public static function publishHeader() {
        if (self::_userRequired()) {
            $id = self::_getParam('id');
            if (stristr($id, '?_wpnonce')) {
                $id = stristr($id, '?_wpnonce', true);
            }
            if (empty($id) || !is_numeric($id)) {
                wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                exit;
            } else {
                $download = CMDM_GroupDownloadPage::getInstance($id);
                $name = $download->getTitle();
                if (!$download->isEditAllowed(get_current_user_id())) {
                    self::_addError(__('You are not allowed to change status of this element', 'cm-download-manager'));
                    return;
                } else {
                    $nonce = wp_verify_nonce($_REQUEST['_wpnonce'], 'publish' . $download->getId());
                    if ($nonce == true) {
                        $download->setStatus('publish', true);
                        self::addMessage(self::MESSAGE_SUCCESS, sprintf(__('"%s" has been succesfully published', 'cm-download-manager'), $name));
                        wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                    } else {
                        wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                    }
                    exit;
                }
            }
        }
    }

    public static function unpublishHeader() {
        if (self::_userRequired()) {
            $id = self::_getParam('id');
            if (stristr($id, '?_wpnonce')) {
                $id = stristr($id, '?_wpnonce', true);
            }
            if (empty($id) || !is_numeric($id)) {
                wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                exit;
            } else {
                $download = CMDM_GroupDownloadPage::getInstance($id);
                $name = $download->getTitle();
                if (!$download->isEditAllowed(get_current_user_id())) {
                    self::_addError(__('You are not allowed to change status of this element', 'cm-download-manager'));
                    return;
                } else {
                    $nonce = wp_verify_nonce($_REQUEST['_wpnonce'], 'unpublish' . $download->getId());
                    if ($nonce == true) {
                        $download->setStatus('draft', true);
                        self::addMessage(self::MESSAGE_SUCCESS, sprintf(__('"%s" has been succesfully unpublished', 'cm-download-manager'), $name));
                        wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                    } else {
                        wp_redirect(self::getUrl('cmdownload', 'dashboard'), 303);
                    }
                    exit;
                }
            }
        }
    }

    public static function screenshotsHeader() {
        if (self::_isPost()) {
            // Check post_max_size (http://us3.php.net/manual/en/features.file-upload.php#73762)
            $POST_MAX_SIZE = ini_get('post_max_size');
            $unit = strtoupper(substr($POST_MAX_SIZE, -1));
            $multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

            if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier * (int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
                self::handleUploadError(__("POST exceeded maximum allowed size.", 'cm-download-manager'));
                exit;
            }

            // Settings
            $upload_name = "upload";
            $max_file_size_in_bytes = 1048576;    // 1MB in bytes
            $extension_whitelist = array("jpg", "gif", "png", "jpeg"); // Allowed file extensions
            $valid_chars_regex = '.A-Z0-9_ !@#$%^&()+={}\[\]\',~`-';    // Characters allowed in the file name (in a Regular Expression format)
            // Other variables
            $MAX_FILENAME_LENGTH = 260;
            $file_name = "";
            $file_extension = "";
            $uploadErrors = array(
                0 => __("There is no error, the file uploaded with success", 'cm-download-manager'),
                1 => __("The uploaded file exceeds the upload_max_filesize directive in php.ini", 'cm-download-manager'),
                2 => __("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form", 'cm-download-manager'),
                3 => __("The uploaded file was only partially uploaded", 'cm-download-manager'),
                4 => __("No file was uploaded", 'cm-download-manager'),
                6 => __("Missing a temporary folder", 'cm-download-manager')
            );


            // Validate the upload
            if (!isset($_FILES[$upload_name])) {
                self::handleUploadError("No upload found in \$_FILES for " . $upload_name);
                exit;
            } else if (isset($_FILES[$upload_name]["error"]) && $_FILES[$upload_name]["error"] != 0) {
                self::handleUploadError($uploadErrors[$_FILES[$upload_name]["error"]]);
                exit;
            } else if (!isset($_FILES[$upload_name]["tmp_name"]) || !@is_uploaded_file($_FILES[$upload_name]["tmp_name"])) {
                self::handleUploadError("Upload failed is_uploaded_file test.");
                exit;
            } else if (!isset($_FILES[$upload_name]['name'])) {
                self::handleUploadError("File has no name.");
                exit;
            }

            // Validate the file size (Warning: the largest files supported by this code is 2GB)
            $file_size = @filesize($_FILES[$upload_name]["tmp_name"]);
            if (!$file_size || $file_size > $max_file_size_in_bytes) {
                self::handleUploadError(__("File exceeds the maximum allowed size", 'cm-download-manager'));
                exit;
            }

            if ($file_size <= 0) {
                self::handleUploadError("File size outside allowed lower bound");
                exit;
            }


            // Validate file name (for our purposes we'll just remove invalid characters)
            $file_name = preg_replace('/[^' . $valid_chars_regex . ']|\.+$/i', "", basename($_FILES[$upload_name]['name']));
            $file_name = sanitize_file_name($file_name);
            if (strlen($file_name) == 0 || strlen($file_name) > $MAX_FILENAME_LENGTH) {
                self::handleUploadError(__("Invalid file name", 'cm-download-manager'));
                exit;
            }


            // Validate that we won't over-write an existing file
            //             if( file_exists($save_path . $file_name) )
            //             {
            //                 self::handleUploadError(__("File with this name already exists", 'cm-download-manager'));
            //                 exit;
            //             }

            // Validate file extension
            $path_info = pathinfo($_FILES[$upload_name]['name']);
            $file_extension = $path_info["extension"];
            $is_valid_extension = false;
            foreach ($extension_whitelist as $extension) {
                if (strcasecmp($file_extension, $extension) == 0) {
                    $is_valid_extension = true;
                    break;
                }
            }
            if (!$is_valid_extension) {
                self::handleUploadError(__("Invalid file extension", 'cm-download-manager'));
                exit;
            }
            try {
                $img = CMDM_GroupDownloadPage::saveScreenshot($_FILES[$upload_name]);
            } catch (Exception $e) {
                self::handleUploadError($e->getMessage());
            }

            $size = '60x60';
            $hash = md5($img . $size);

            $imgSrc = CMDM_get_url('cmdownload', 'screenshot', array('size' => $size, 'img' => $img, 'hash' => $hash)) . "/"; // call screenshotHeader()

            header('Content-type: application/json');
            echo json_encode(array('jsonrpc' => 2.0, 'result' => null, 'id' => "id", 'fileName' => $img, 'imgSrc' => $imgSrc, 'hash' => $hash));
            exit;
        }
    }

    protected static function handleUploadError($message) {
        header("HTTP/1.1 500 Internal Server Error");
        $response = array('success' => 0, 'message' => $message);
        echo json_encode($response);
        exit;
    }

    public static function screenshotHeader() {
        $image = self::_getParam('img');
        $size = self::_getParam('size');
        $hash = self::_getParam('hash');
        // wp_redirect(CMDM_GroupDownloadPage::getScreenshotsUrl() . $image, 303);
        CMDM_GroupDownloadPage::processImage($image, $size, $hash);
        exit;
    }

    public static function registerAdminColumns($columns) {
        $columns['author'] = 'Author';
        $columns['number_of_downloads'] = 'Number of downloads';
        $columns['status'] = 'Status';
        return $columns;
    }

    public static function adminColumnDisplay($columnName, $id) {
        $download = CMDM_GroupDownloadPage::getInstance($id);
        if (!$download) return;
        switch ($columnName) {
            case 'author':
                echo $download->getAuthor()->display_name;
                break;
            case 'number_of_downloads':
                echo $download->getNumberOfDownloads();
                break;
            case 'status':
                echo $download->getStatus();
                break;
        }
    }


    public static function checkCategoriesAdminNotice() {
        global $wpdb;
        $categoriesCount = intval($wpdb->get_var($wpdb->prepare("SELECT count(*) FROM $wpdb->term_taxonomy x
        	WHERE x.taxonomy = %s", CMDM_GroupDownloadPage::CAT_TAXONOMY)));
        if ($categoriesCount == 0) {
            printf('<div class="error"><p>%s<a href="%s" class="button" style="margin-left:1em;">%s</a></p></div>',
                CMDM::__('CM Download Manager: you have to define at least one category.'),
                esc_attr('edit-tags.php?taxonomy=' . urlencode(CMDM_GroupDownloadPage::CAT_TAXONOMY) . '&post_type=' . urlencode(CMDM_GroupDownloadPage::POST_TYPE)),
                CMDM::__('Go to Categories')
            );
        }
    }


    public static function checkDirectoryAccessAdminNotice() {
        $uploadDir = wp_upload_dir();
        $denyFile = $uploadDir['basedir'] . '/' . CMDM_GroupDownloadPage::UPLOAD_DIR . '/.htaccess';
        if (!file_exists($denyFile)) {
            printf('<div class="error"><p>%s<a href="#" class="button" style="margin-left:1em;" ' .
                'onclick="jQuery(this).parent().parent().find(\'.more\').show();return false">%s</a></p>
    			<div class="more" style="display:none">
    				<p>Create file "%s".<br />Include in this file the following:
    				<pre style="background:#f0f0f0;padding:5px;">Order Deny,Allow' . PHP_EOL . 'Deny from all' . PHP_EOL . '&lt;FilesMatch "\.(?i:jpg|jpeg|png|gif|webp)$">' .
                PHP_EOL . '&nbsp;&nbsp;&nbsp;Allow from all' . PHP_EOL . '&lt;/FilesMatch></pre></p>
    				<p><strong>Notice:</strong> if you are using the preview option, add your documents extensions to the regexp (pdf|doc|docx).</p>
    			</div></div>',
                CMDM::__('CM Download Manager: to protect your upload directory please create the following .htaccess file.'),
                CMDM::__('More'),
                $denyFile
            );
        }
    }


}
