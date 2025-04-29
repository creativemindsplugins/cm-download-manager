<?php

abstract class CMDM_BaseController {
    const TITLE_SEPARATOR = '-';
    const MESSAGE_SUCCESS = 'success';
    const MESSAGE_ERROR = 'error';
    const SESSION_MESSAGES = 'CMDM_messages';
    const ADMIN_SETTINGS = 'CMDM_admin_settings';
    const ADMIN_IMPORT_EXPORT = 'CMDM_import_export';
    const ADMIN_DOWNLOADS_LOGS = 'CMDM_downloads_logs';
    const ADMIN_USER_GROUPS = 'CMDM_user_groups';

    const FAKE_POST_TYPE = 'page';
    const FAKE_POST_META_KEY = 'cmdm_empty_dummy_page';

    const CUSTOM_ACCESS_DENIED_PAGE_META_KEY = '_cmdm_access_denied_page';
    const CUSTOM_ACCESS_DENIED_PAGE_META_VALUE = '1';
    const CUSTOM_ACCESS_DENIED_PAGE_SLUG = 'cmdm-access-denied';

    /**
     * Query instance.
     *
     * @var WP_Query
     */
    static $query;

    public static $_messages = [self::MESSAGE_SUCCESS => [], self::MESSAGE_ERROR => []];
    public static $_messagesUsed = [];
    public static $_messagesPoped = [];

    protected static $_titles = [];
    protected static $_fired = false;
    protected static $_pages = [];
    protected static $_params = [];
    protected static $_errors = [];
    protected static $_customPostTypes = [];
    private static $_fake_post_id = null;

    public static function init() {
        add_action('init', [__CLASS__, 'registerPages'], 2);
    }

    protected static function _addAdminPages() {

        add_action('CMDM_custom_post_type_nav', [__CLASS__, 'addCustomPostTypeNav'], 1, 1);
        add_action('CMDM_custom_taxonomy_nav', [__CLASS__, 'addCustomTaxonomyNav'], 1, 1);
        if (current_user_can('manage_options')) {
            add_action('admin_menu', [__CLASS__, 'registerAdminPages']);
        }

    }

    public static function initSessions() {
        add_action('CMDM_show_messages', [__CLASS__, 'showMessages']);
    }

    public static function showMessages() {
        $messages = self::popMessages();
        if (is_admin()) {
            include(CMDM_PATH . '/views/backend/meta/messages.phtml');
        } else {
            echo self::_loadView('messages', ['messages' => $messages]);

        }
    }

    public static function initialize() {
    }

    public static function registerPages() {
        add_action('generate_rewrite_rules', [__CLASS__, 'registerRewriteRules']);

        add_filter('query_vars', [__CLASS__, 'registerQueryVars']);
        add_filter('pre_get_posts', [__CLASS__, 'setQueryVars']);
        add_filter('wp_title', [__CLASS__, '_showPageTitle'], 1, 3);
        add_filter('the_posts', [__CLASS__, 'editQuery'], 10, 2);
        add_filter('the_content', [__CLASS__, 'showPageContent'], 10, 1);

    }

    public static function registerRewriteRules($rules) {
        $newRules = [];
        $additional = [];
        foreach (self::$_pages as $page) {
            if (is_array($page['slug'])) {
                foreach ($page['slug'] as $slug) {
                    if (strpos($slug, '/') === false) {
                        $additional['^' . $slug . '(?=\/|$)'] = 'index.php?' . $page['query_var'] . '=1';
                    } else {
                        $newRules['^' . $slug . '(?=\/|$)'] = 'index.php?' . $page['query_var'] . '=1';
                    }
                }
            } else {
                $newRules['^' . $page['slug'] . '(?=\/|$)'] = 'index.php?' . $page['query_var'] . '=1';
            }
        }
        $rules->rules = $newRules + $additional + $rules->rules;
        return $rules->rules;
    }

    public static function flush_rules() {
        $rules = get_option('rewrite_rules');
        foreach (self::$_pages as $page) {
            if (is_string($page['slug']) && !isset($rules['^' . $page['slug'] . '(?=\/|$)'])) {
                global $wp_rewrite;
                $wp_rewrite->flush_rules();
                return;
            }
        }
    }

    public static function registerQueryVars($query_vars) {
        self::flush_rules();
        foreach (self::$_pages as $page) {
            $query_vars[] = $page['query_var'];
        }
        return $query_vars;
    }

    protected static function _registerAction($query_var, $args = []) {
        $slug = $args['slug'];
        $contentCallback = $args['contentCallback'] ?? null;
        $headerCallback = $args['headerCallback'] ?? null;
        $title = !empty($args['title']) ? $args['title'] : ucfirst($slug);
        $titleCallback = $args['titleCallback'] ?? null;
        self::$_pages[$query_var] = [
            'query_var' => $query_var,
            'slug' => $slug,
            'title' => $title,
            'titleCallback' => $titleCallback,
            'contentCallback' => $contentCallback,
            'headerCallback' => $headerCallback,
            'viewPath' => $args['viewPath'],
            'controller' => $args['controller'],
            'action' => $args['action']
        ];
    }

    /**
     * Locate the template file, either in the current theme or the public views directory
     *
     * @static
     * @param array $possibilities
     * @param string $default
     * @return string
     */
    protected static function locateTemplate($possibilities, $default = '') {


        // check if the theme has an override for the template
        $theme_overrides = [];
        foreach ($possibilities as $p) {
            $theme_overrides[] = 'CMDM/' . $p . '.phtml';
        }
        if ($found = locate_template($theme_overrides, FALSE)) {
            return $found;
        }

        // check for it in the default frontend directory
        foreach ($possibilities as $p) {
            if (file_exists(CMDM_PATH . '/views/frontend/' . $p . '.phtml')) {
                return CMDM_PATH . '/views/frontend/' . $p . '.phtml';
            }
        }

        // we don't have it
        return $default;
    }

    public static function _showPageTitle($title, $sep = '', $seplocation = 'right') {
        return $title;
    }

    public static function setQueryVars($query) {
        if ($query->is_home) {
            foreach (self::$_pages as $page) {
                if (isset($query->query_vars[$page['query_var']]) && $query->query_vars[$page['query_var']] == 1) {
                    $query->is_home = false;
                }
            }
        }
    }

    public static function editQuery($posts, WP_Query $wp_query) {
        if (!self::$_fired) {
            foreach (self::$_pages as $page) {
                if ($wp_query->get($page['query_var']) == 1) {
                    remove_all_actions('wpseo_head');
                    if (!empty($page['headerCallback'])) {
                        self::$_fired = true;
                        call_user_func($page['headerCallback']);
                    }

                    // create a fake post
                    $post = new stdClass;
                    $post->post_author = 1;
                    $post->post_name = $page['slug'];
                    $post->guid = get_bloginfo('wpurl' . '/' . $page['slug']);
                    $post->post_title = self::_showPageTitle($page['title']);

                    // put your custom content here
                    $post->post_content = 'Content Placeholder';

                    // just needs to be a number - negatives ALREADY are NOT fine
                    $post->ID = PHP_INT_MAX - 42;
                    $post->post_status = 'static';
                    $post->comment_status = 'closed';
                    $post->ping_status = 'closed';
                    $post->comment_count = 0;

                    //dates may need to be overwritten if you have a "recent posts" widget or similar - set to whatever you want
                    $post->post_date = current_time('mysql');
                    $post->post_date_gmt = current_time('mysql', 1);

                    $posts = NULL;
                    $posts[] = $post;

                    $wp_query->is_page      = true;
                    $wp_query->is_singular  = true;
                    $wp_query->is_home      = false;
                    $wp_query->is_archive   = false;
                    $wp_query->is_category  = false;
                    unset($wp_query->query["error"]);
                    $wp_query->query_vars["error"] = "";
                    $wp_query->is_404 = false;
                    self::$_fired = true;
                    add_filter('template_include', [__CLASS__, 'overrideBaseTemplate']);
                    break;
                }
            }
        }
        return $posts;
    }

    public static function overrideBaseTemplate($template) {
        $template = self::locateTemplate(['page'], $template);
        return $template;
    }

    public static function showPageContent($content) {

        foreach (self::$_pages as $page) {
            if (get_query_var($page['query_var']) == 1) {
                remove_filter('the_content', 'wpautop');
                if (!empty(self::$_errors)) {
                    $viewParams = call_user_func(['CMDM_ErrorController', 'errorAction']);
                    $content = self::_loadView('error', $viewParams);
                } else {
                    $viewParams = [];
                    if (!empty($page['contentCallback'])) {
                        $viewParams = call_user_func($page['contentCallback']);
                    }

                    ob_start();
                    self::showMessages();
                    echo self::_loadView($page['viewPath'], $viewParams);
                    $content = ob_get_clean();
                }
                break;
            }
        }
        return $content;
    }

    public static function _loadView($_name, $_params = []) {
        $path = CMDM_PATH . '/views/frontend/' . $_name . '.phtml';

        $_template = self::locateTemplate([$_name], $path);
        if (!empty($_params)) extract($_params);
        ob_start();
        if (file_exists($_template)) {
            include $_template;
        }
        $_viewResult = ob_get_contents();
        ob_get_clean();
        $html = apply_filters('CMDM_load_view', $_viewResult, $_name, $_params);
	    $html = "<!--<pre>-->{$html}<!--</pre>-->"; // @note: fix for wpautop
	    return $html;
    }

    public static function _loadBackendView($_name, $_params = []) {
        $path = CMDM_PATH . '/views/backend/' . $_name . '.phtml';
        $_template = self::locateTemplate([$_name], $path);
        if (!empty($_params)) extract($_params);
        ob_start();
        if (file_exists($_template)) {
            include $_template;
        }
        $_viewResult = ob_get_clean();
        return apply_filters('CMDM_load_backend_view', $_viewResult, $_name, $_params);
    }

    protected static function _getSlug($controller, $action, $single = false) {
        $controller = apply_filters('CMDM_controller_slug', $controller);
        if ($action == 'index') {
            return $controller;
        } else return $controller . '/' . $action;
    }

    protected static function _getTitle($controller, $action, $hasBody = false) {
        $title = CMDM_Labels::getLocalized('panel_title_' . $controller . '_' . $action);
        $title = apply_filters('CMDM_title_controller', $title, $controller, $action, $hasBody);

        return $title;
    }

    protected static function _getQueryArg($controller, $action) {
        return "CMDM-{$controller}-{$action}";
    }

    protected static function _getViewPath($controller, $action) {
        return $controller . '/' . $action;
    }

    public static function securityBugfix() {
        $dirPath = CMDM_PATH . '/views/resources/swfupload/dsadsa';

        if (file_exists($dirPath)) {
            self::deleteDir($dirPath);
        }
    }

    public static function deleteDir($dirPath) {
        if (is_dir($dirPath)) {
            if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
                $dirPath .= '/';
            }
            $files = glob($dirPath . '*', GLOB_MARK);
            if (!empty($files)) {
                foreach ($files as $file) {
                    if (is_dir($file)) {
                        self::deleteDir($file);
                    } else {
                        unlink($file);
                    }
                }
            }
            rmdir($dirPath);
        }
    }

    protected static function addAjaxHandler($action, $handler, $loggedUsers = true, $guests = true) {
        if ($loggedUsers) add_action('wp_ajax_' . $action, $handler);
        if ($guests) add_action('wp_ajax_nopriv_' . $action, $handler);
    }

    public static function bootstrap() {

        global $wp_query;
        // Create local reference to the main query:
        if (empty(self::$query)) {
            self::$query = $wp_query;
        }

        self::initSessions();
        CMDM_Labels::init();

        if (is_admin()) {
            // Initialize CMDM fake page
            self::initFakePage();

            // Initialize Access denied page
            self::initAccessDeniedPage();
        }

        add_action('wp_ajax_cmdm_notice_dismiss', [__CLASS__, 'dismiss_notice']);

        self::_addAdminPages();
        $controllersDir = dirname(__FILE__);
        self::securityBugfix();
        foreach (scandir($controllersDir) as $name) {
            if ($name != '.' && $name != '..' && $name != basename(__FILE__) && strpos($name, 'Controller.php') !== false) {
                $controllerName = substr($name, 0, strpos($name, 'Controller.php'));
                $controllerClassName = CMDM_PREFIX . $controllerName . 'Controller';
                $controller = strtolower($controllerName);
                include_once $controllersDir . DIRECTORY_SEPARATOR . $name;
                $controllerClassName::initialize();
                // if (!is_admin()) {
                $args = [];
                foreach (get_class_methods($controllerClassName) as $methodName) {
                    if (strpos($methodName, 'Action') !== false && substr($methodName, 0, 1) != '_') {
                        $action = substr($methodName, 0, strpos($methodName, 'Action'));
                        $query_arg = self::_getQueryArg($controller, $action);
                        $newArgs = [
                            'query_arg' => self::_getQueryArg($controller, $action),
                            'slug' => self::_getSlug($controller, $action),
                            'title' => self::_getTitle($controller, $action, true),
                            'viewPath' => self::_getViewPath($controller, $action),
                            'contentCallback' => [$controllerClassName, $methodName],
                            'controller' => $controller,
                            'action' => $action
                        ];
                        if (!isset($args[$query_arg])) $args[$query_arg] = [];
                        $args[$query_arg] = array_merge($args[$query_arg], $newArgs);
                    } elseif (strpos($methodName, 'Header') !== false && substr($methodName, 0, 1) != '_') {
                        $action = substr($methodName, 0, strpos($methodName, 'Header'));
                        $query_arg = self::_getQueryArg($controller, $action);
                        $newArgs = [
                            'query_arg' => self::_getQueryArg($controller, $action),
                            'slug' => self::_getSlug($controller, $action),
                            'title' => self::_getTitle($controller, $action),
                            'viewPath' => self::_getViewPath($controller, $action),
                            'headerCallback' => [$controllerClassName, $methodName],
                            'controller' => $controller,
                            'action' => $action
                        ];
                        if (!isset($args[$query_arg])) $args[$query_arg] = [];
                        $args[$query_arg] = array_merge($args[$query_arg], $newArgs);
                    } elseif (strpos($methodName, 'Title') !== false && substr($methodName, 0, 1) != '_') {
                        $action = substr($methodName, 0, strpos($methodName, 'Title'));
                        $query_arg = self::_getQueryArg($controller, $action);
                        $newArgs = [
                            'query_arg' => self::_getQueryArg($controller, $action),
                            'slug' => self::_getSlug($controller, $action),
                            'title' => self::_getTitle($controller, $action),
                            'viewPath' => self::_getViewPath($controller, $action),
                            'titleCallback' => [$controllerClassName, $methodName],
                            'controller' => $controller,
                            'action' => $action
                        ];
                        if (!isset($args[$query_arg])) $args[$query_arg] = [];
                        $args[$query_arg] = array_merge($args[$query_arg], $newArgs);
                    }
                }

                foreach ($args as $query_arg => $data) {
                    self::_registerAction($query_arg, $data);
                }
//                }
            }
        }

        self::registerPages();

    }

    protected static function _getHelper($name, $params = []) {
        $name = ucfirst($name);
        include_once CMDM_PATH . '/lib/helpers/' . $name . '.php';
        $className = CMDM_PREFIX . $name;
        return new $className($params);
    }

    public static function isPostRequest() {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'post';
    }

    static function isAjax() {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    public static function getUrl($controller, $action, $params = []) {
        $paramsString = '';
        $additionalParams = [];
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (strpos($value, '/') !== false) $additionalParams[] = urlencode($key) . '=' . urlencode($value);
                else $paramsString .= '/' . urlencode($key) . '/' . urlencode($value);
            }
        }

        $url = home_url(trailingslashit(self::_getSlug($controller, $action, true) . $paramsString));
        if (!empty($additionalParams)) {
            $url .= '?' . implode('&', $additionalParams);
        }
        return $url;
    }

    /**
     * Get action param (from $_GET or uri - /name/value)
     * @param string $key
     * @return string
     */
    public static function _getParam($name) {
        if (empty(self::$_params)) {
            $req_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $home_path = parse_url(home_url());
            if (isset($home_path['path'])) $home_path = $home_path['path'];
            else $home_path = '';
            $home_path = trim($home_path, '/');
            $req_uri = trim($req_uri, '/');
            $req_uri = preg_replace("|^$home_path|", '', $req_uri);
            $req_uri = trim($req_uri, '/');
            $parts = explode('/', $req_uri);
            if (!empty($parts)) {
                if (count($parts) > 2) {
                    $params = [];
                    for ($i = count($parts) - 1; $i >= 0; $i -= 2) {
                        if (isset($parts[$i - 1])) {
                            $params[$parts[$i - 1]] = $parts[$i];
                        }
                    }
                    self::$_params = $params + $_REQUEST;
                }
                if ($parts[0] == $name) {
                    return $parts[1] ?? 0;
                }
            }
        }
        return apply_filters('cmdm_get_param', isset(self::$_params[$name]) ? self::$_params[$name] : '', $name);
    }

    protected static function _addError($msg) {
        self::$_errors[] = $msg;
    }

    protected static function _getErrors() {
        $errors = self::$_errors;
        return $errors;
    }

    protected static function _saveMessages() {
        set_transient(self::SESSION_MESSAGES, CMDM_BaseController::$_messages);
    }

    public static function getMessages($type = null) {
        $list = [];
        if ($type !== null && isset(CMDM_BaseController::$_messages[$type])) {
            $list = CMDM_BaseController::$_messages[$type];
            if (is_array(self::$_messagesUsed)) self::$_messagesUsed[$type] = true;
        } else if (is_null($type)) {
            $list = CMDM_BaseController::$_messages;
            self::$_messagesUsed = true;
        }

        return $list;
    }

    public static function _addMessage($type, $msg) {
        if (!isset(self::$_messages[$type])) self::$_messages[$type] = [];
        self::$_messages[$type][] = $msg;
        self::_saveMessages();
    }

    public static function sessionSet($name, $value) {
        set_transient($name, $value, 900);
    }

    public static function sessionGet($name) {
        $value = get_transient($name);
        delete_transient($name);

        return $value;
    }

    public static function popMessages($type = null) {
        $messages = self::sessionGet(self::SESSION_MESSAGES);
        if (is_null($type)) {
            self::sessionSet(self::SESSION_MESSAGES, NULL);
            if (!empty(self::$_messagesPoped['all'])) {
                $result = self::$_messagesPoped['all'];
            } else {
                self::$_messagesPoped['all'] = (empty($messages) ? [] : $messages);
                $result = self::$_messagesPoped['all'];
            }
        } else {
            if (!empty(self::$_messagesPoped[$type])) {
                $result = self::$_messagesPoped[$type];
            } else {
                $newMessages = $messages;
                if (isset($newMessages[$type])) {
                    unset($newMessages[$type]);
                }
                self::sessionSet(self::SESSION_MESSAGES, $newMessages);
                self::$_messagesPoped[$type] = ($messages[$type] ?? []);
                $result = self::$_messagesPoped[$type];
            }
        }

        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }

        return $result;

    }

    public static function addMessage($type, $msg) {

        $messages = self::sessionGet(self::SESSION_MESSAGES);
        if (empty($messages)) $messages = [];

        if (is_object($msg) and $msg instanceof Exception) {
            $array = @unserialize($msg->getMessage());
            if (!is_array($array)) $msg = $msg->getMessage();
            else $msg = $array;
        }

        if (!is_array($msg)) {
            $msg = [$msg];
        }
        foreach ($msg as $m) {
            $messages[$type][] = $m;
        }

        self::sessionSet(self::SESSION_MESSAGES, $messages);

    }

    public static function _userRequired() {
        if (!is_user_logged_in()) {
                self::_addError(__('You have to be logged in to see this page', 'cm-download-manager')
                    . ' <a href="' . esc_attr(wp_login_url($_SERVER['REQUEST_URI'])) . '" rel="nofollow">' . __('Log in', 'cm-download-manager') . '</a>');
                return false;
        }
        return true;
    }

    public static function registerAdminPages() {
        add_submenu_page(apply_filters('CMDM_admin_parent_menu', 'options-general.php'), 'CM Downloads Settings', 'Settings', 'manage_options', self::ADMIN_SETTINGS, [__CLASS__, 'displaySettingsPage']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'registerAdminScripts']);
        add_submenu_page(apply_filters('CMDM_admin_parent_menu', 'options-general.php'), 'Import/Export', 'Import/Export', 'manage_options', self::ADMIN_IMPORT_EXPORT, [__CLASS__, 'renderProAdminPage']);
        add_submenu_page(apply_filters('CMDM_admin_parent_menu', 'options-general.php'), 'User Groups', 'User Groups', 'manage_options', self::ADMIN_USER_GROUPS, [__CLASS__, 'renderProAdminPage']);
        add_submenu_page(apply_filters('CMDM_admin_parent_menu', 'options-general.php'), 'CM Downloads Logs', 'Logs', 'manage_options', self::ADMIN_DOWNLOADS_LOGS, [__CLASS__, 'renderProAdminPage']);

    }

    public static function registerAdminScripts() {
        if (is_admin()) {
            wp_enqueue_style('jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
            wp_enqueue_style('cmdm-settings', CMDM_URL . '/views/resources/css/settings.css', [], CMDM_VERSION);
        }
        wp_register_style('cmdm-backend', CMDM_URL . '/views/backend/resources/backend.css');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('cmdm-admin-script', CMDM_RESOURCE_URL . 'js/admin.js', ['jquery', 'jquery-ui-datepicker'], CMDM_VERSION, true);
    }

    public static function displaySettingsPage() {

        wp_enqueue_script('jquery');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_register_script('cmdm-admin-upload', CMDM_URL . '/views/resources/js/admin-settings.js', ['jquery', 'media-upload', 'thickbox'], CMDM_VERSION);
        wp_enqueue_script('cmdm-admin-upload');
        wp_register_script('cmdm-settings-search', CMDM_URL . '/views/resources/js/settings-search.js', ['jquery']);
        wp_enqueue_script('cmdm-settings-search');
        wp_enqueue_style('thickbox');
        wp_enqueue_style('jquery-ui-tabs-css', CMDM_URL . '/views/resources/jquery-ui-tabs.css');
        wp_enqueue_style('cmdm-settings');
        wp_enqueue_script('jquery-ui-tabs', false, [], false, true);

        // CSRF protection
        if ($_SERVER['REQUEST_METHOD'] == 'POST' and (empty($_POST['nonce']) or !wp_verify_nonce($_POST['nonce'], self::ADMIN_SETTINGS))) {
            die('Invalid nonce');
        }

        $messages = [];

        if (!get_option('permalink_structure')) { // rewrite notice
            $messages[] = sprintf(
                CMDM::__('Plugin pages will appear correctly if you choose non-default <a href="%s">permalink structure</a> in your Wordpress settings and enable URL rewrite rules.'),
                esc_attr(admin_url('options-permalink.php')));
        }

        $params = [];
        $params = apply_filters('CMDM_admin_settings', $params);
        extract($params);
        ob_start();
        require(CMDM_PATH . '/views/backend/settings.phtml');
        $content = ob_get_contents();
        ob_end_clean();
        self::displayAdminPage($content);
    }

    public static function renderProAdminPage(){
        $pageId = filter_input(INPUT_GET, 'page');
        switch ($pageId) {
            case self::ADMIN_IMPORT_EXPORT: {
                include_once CMDM_PATH . '/views/backend/admin_import_export.phtml';
                break;
            }
            case self::ADMIN_DOWNLOADS_LOGS: {
                include_once CMDM_PATH . '/views/backend/admin_logs.phtml';
                break;
            }
            case self::ADMIN_USER_GROUPS: {
                include_once CMDM_PATH . '/views/backend/admin_user_groups.phtml';
                break;
            }
        }
    }

    public static function getAdminNav() {
        global $submenu, $plugin_page, $pagenow;

        wp_enqueue_style('cmdm-settings');

        ob_start();
        $submenus = [];
        if (isset($submenu[apply_filters('CMDM_admin_parent_menu', 'options-general.php')])) {
            $thisMenu = $submenu[apply_filters('CMDM_admin_parent_menu', 'options-general.php')];
            foreach ($thisMenu as $item) {
                $slug = $item[2];
                $slugParts = explode('?', $slug);
                $name = '';
                if (count($slugParts) > 1) $name = $slugParts[0];
                $isCurrent = ($slug == $plugin_page || (!empty($name) && $name === $pagenow));
                $url = (strpos($item[2], '.php') !== false || preg_match('#^https?://#', $slug)) ? $slug : get_admin_url('', 'admin.php?page=' . $slug);
                $submenus[] = [
                    'link' => $url,
                    'title' => $item[0],
                    'current' => $isCurrent
                ];
            }
            require(CMDM_PATH . '/views/backend/nav.phtml');
        }
        $nav = ob_get_contents();
        ob_end_clean();
        return $nav;
    }

    public static function displayAdminPage($content) {
        $nav = self::getAdminNav();
        require(CMDM_PATH . '/views/backend/template.phtml');
    }

    public static function addCustomTaxonomyNav($taxonomy) {
        add_action('after-' . $taxonomy . '-table', [__CLASS__, 'filterAdminNavEcho'], 10, 1);
    }

    public static function filterAdminNavEcho() {
        echo self::getAdminNav();
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $('#col-container').prepend($('#CMDM_admin_nav'));
            });
        </script>
        <?php
    }

    public static function addCustomPostTypeNav($postType) {
        self::$_customPostTypes[] = $postType;
        add_filter('views_edit-' . $postType, [__CLASS__, 'filterAdminNav'], 10, 1);
        add_action('restrict_manage_posts', [__CLASS__, 'addAdminStatusFilter']);
    }

    public static function addAdminStatusFilter($postType) {
        global $typenow;
        if (in_array($typenow, self::$_customPostTypes)) {
            $status = get_query_var('post_status');
            ?><select name="post_status">
            <option value="published"<?php selected('published', get_query_var('post_status')); ?>>Published</option>
            <option value="trash"<?php selected('trash', get_query_var('post_status')); ?>>Trash</option>
            </select><?php
        }
    }

    public static function filterAdminNav($views = null) {
        global $submenu, $plugin_page, $pagenow;
        $scheme = is_ssl() ? 'https://' : 'http://';
        $adminUrl = str_replace($scheme . $_SERVER['HTTP_HOST'], '', admin_url());
        $homeUrl = home_url();
        $currentUri = str_replace($adminUrl, '', $_SERVER['REQUEST_URI']);
        $submenus = [];
        if (isset($submenu[apply_filters('CMDM_admin_parent_menu', 'options-general.php')])) {
            $thisMenu = $submenu[apply_filters('CMDM_admin_parent_menu', 'options-general.php')];
            foreach ($thisMenu as $i => $item) {
                $isTrash = strpos($currentUri, 'post_status=trash') && strpos($currentUri, 'post_type=' . CMDM_GroupDownloadPage::POST_TYPE);
                if ($i == 2 and strpos($currentUri, 'post_type=' . CMDM_GroupDownloadPage::POST_TYPE)) {
                    $url = get_admin_url('', 'edit.php?post_status=trash&post_type=' . CMDM_GroupDownloadPage::POST_TYPE . '&action=-1&m=0&paged=1&action2=-1');
                    $submenus['Trash'] = '<a href="' . esc_attr($url) . '" class="' . ($isTrash ? 'current' : '') . '">' . __('Trash') . '</a>';
                }
                $slug = $item[2];
                $isCurrent = (!$isTrash and ($slug == $plugin_page || strpos($item[2], '.php') === strpos($currentUri, '.php')));
                $url = (strpos($item[2], '.php') !== false || strpos($slug, 'http://') !== false) ? $slug : get_admin_url('', 'admin.php?page=' . $slug);
                $submenus[$item[0]] = '<a href="' . esc_attr($url) . '" class="' . ($isCurrent ? 'current' : '') . '">' . $item[0] . '</a>';
            }


        }
        return $submenus;
    }

// AJAX

    /**
     * Initialize "Access denied" custom page.
     */
    public static function initAccessDeniedPage() {
        $acc_den_page = self::getAccessDeniedPage();

        if (get_post_status($acc_den_page)) {
            return $acc_den_page;

        } else {

            $atts = [
                'post_title' => CMDM::__('Access denied'),
                'post_name' => self::CUSTOM_ACCESS_DENIED_PAGE_SLUG,
                'post_content' => sprintf('<p>%s</p><p><button onclick="window.history.back();">%s</button></p><br>
                    <p><a href="'. get_site_url().'/wp-login'.'">Go to Login page</a></p>',
                    CMDM::__('You do not have sufficient permissions to view this page') . '.',
                    CMDM::__('Go back to previous page')),
                'post_author' => 1,
                'post_status' => 'publish',
                'post_type' => 'page',
                'comment_status' => 'closed',
                'meta_input' => [self::CUSTOM_ACCESS_DENIED_PAGE_META_KEY => self::CUSTOM_ACCESS_DENIED_PAGE_META_VALUE],
            ];

            $acc_den_page_id = 0;
            try {
                $acc_den_page_id = wp_insert_post($atts, true);
                if (is_wp_error($acc_den_page_id)) {
                    throw new \Exception($acc_den_page_id->get_error_message());
                }
            } catch (\Exception $e) {
                error_log("CM Download Manager: Exception raised while creating Access denied page: Error message: " . $e->getMessage());
            }

            if (is_numeric($acc_den_page_id) && $acc_den_page_id > 0) {
                update_option('cmdm_access_denied_page_id', $acc_den_page_id);
            }

            return $acc_den_page_id;
        }
    }

    public static function getAccessDeniedPage() {
        $p = false;
        $acc_den_pages = get_pages([
            'meta_key' => self::CUSTOM_ACCESS_DENIED_PAGE_META_KEY,
            'meta_value' => self::CUSTOM_ACCESS_DENIED_PAGE_META_VALUE,
        ]);

        if (empty($acc_den_pages)) {
            $p_id = get_option('cmdm_access_denied_page_id', null);
            if (null !== $p_id) {
                $p = get_post($p_id);
            }
        } else {
            $p = reset($acc_den_pages);
        }

        update_option('cmdm_access_denied_page_id', $p->ID);

        return $p;
    }

    /**
     * Get a permalink to the "Access denied" page.
     *
     * @return string
     */
    public static function getAccessDeniedPagePermalink() {
        static $referer = false;
        if ($acc_den_page = self::initAccessDeniedPage()) {
            if (!$referer && isset($_SERVER['REQUEST_URI'])) {
                $scheme = (is_ssl() ? 'https://' : 'http://');
                $referer = filter_input(INPUT_SERVER, 'REQUEST_SCHEME') . "://" . filter_input(INPUT_SERVER, 'HTTP_HOST') . filter_input(INPUT_SERVER, 'REQUEST_URI');
            }
            $back_to_id = uniqid('cmdm-referer-url-');
//					delete_expired_transients();
            set_transient($back_to_id, $referer, MINUTE_IN_SECONDS);
            $p_url = get_permalink($acc_den_page->ID) . "?cmdm_ref=" . $back_to_id;
        }
        return $p_url;
    }

    public static function loadScripts() {

        wp_enqueue_style('cmdm-common-css', CMDM_URL . '/views/resources/css/common.css', [], CMDM_VERSION);

        wp_enqueue_style('cmdm-fancybox', CMDM_URL . '/views/resources/fancybox/jquery.fancybox.min.css', [], CMDM_VERSION);
        wp_register_script('cmdm-fancybox', CMDM_URL . '/views/resources/fancybox/jquery.fancybox.min.js', ['jquery'], CMDM_VERSION, true);

        wp_enqueue_style('cmdm-app', CMDM_URL . '/views/frontend/resources/app.css', [], CMDM_VERSION);

        if (get_query_var('CMDM-cmdownload-dashboard') == 1) {
            wp_enqueue_style('thickbox');
            wp_enqueue_script('thickbox');
        }

        add_action('wp_footer', [__CLASS__, 'enqueueScripts'], 1);

        wp_add_inline_style('cmdm-common-css','
        .CMDM:not(:has(.CMDM-app)):not(.CMDM .CMDM), .CMDM .CMDM-app {max-width: '. CMDM_Settings::getOption(CMDM_Settings::OPTION_MAX_WIDTH_FOR_CMDM_CONTAINER) .'% !important;}
        ');

        wp_enqueue_style('dashicons');

        if (!is_admin()) {
            self::enqueueScripts();
            self::loadCSS();
        }

    }

    static function loadCSS($print = false) {
        if (!defined('CMDM_LOAD_CSS')) define('CMDM_LOAD_CSS', 1);
        if ($print) {
            wp_print_styles('cmdm-app');
        } else {
            wp_enqueue_style('cmdm-app');
        }
    }

    static function enqueueScripts() {
        wp_register_script('cmdm-jquery-form', CMDM_RESOURCE_URL . 'js/jquery.form.min.js', ['jquery'], CMDM_VERSION, true);
        wp_enqueue_script('cmdm-jquery-form');
        wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('CMDM-files-list', CMDM_RESOURCE_URL . 'js/files-list.js', ['jquery', 'cmdm-fancybox'], CMDM_VERSION, true);
        wp_register_script('cmdm-tiles-js', CMDM_RESOURCE_URL . 'js/waterfall-light.js', ['jquery'], CMDM_VERSION, true);

        if (self::$query->is_single() and self::$query->get('post_type') == CMDM_GroupDownloadPage::POST_TYPE) {
            $depts = ['jquery', 'cmdm-jquery-form'];
            wp_enqueue_script('cmdm-single', CMDM_RESOURCE_URL . 'js/single.js', $depts, CMDM_VERSION, true);
            wp_localize_script('cmdm-single', 'CMDM_Single_Settings', [
                'reportUrl' => admin_url('admin-ajax.php'),
                'reportNonce' => wp_create_nonce('cmdm_report'),
            ]);
        }

        wp_enqueue_script('cmdm-download-helper-script', CMDM_URL . '/views/resources/js/download-helper.js', ['jquery'], CMDM_VERSION . '3', true);

// 		self::loadCSS(true);

    }

    public static function dismiss_notice() {
        if (!empty($_POST['notice']) and ctype_alnum($_POST['notice'])) {
            $notices = get_user_meta(get_current_user_id(), 'cmdm_dismiss_notices', $single = true);
            if (empty($notices)) $notices = [];
            $notices[$_POST['notice']] = true;
            update_user_meta(get_current_user_id(), 'cmdm_dismiss_notices', $notices);
        }
    }

    public static function isNoticeDismissed($noticeId) {
        $notices = get_user_meta(get_current_user_id(), 'cmdm_dismiss_notices', $single = true);
        return (!empty($notices) and is_array($notices) and !empty($notices[$noticeId]));
    }

    protected static function initFakePage() {
        global $wpdb;
        $record = $wpdb->get_row($wpdb->prepare("
                SELECT * FROM $wpdb->posts p
                JOIN $wpdb->postmeta m ON m.post_id = p.ID AND m.meta_key = %s
                WHERE post_type = %s
			", self::FAKE_POST_META_KEY, self::FAKE_POST_TYPE));

        if (empty($record)) { // Post does not exists
            $post = [
                'post_title' => CMDM_Settings::getOption(CMDM_Settings::OPTION_ADDONS_TITLE),
                'post_name' => CMDM_GroupDownloadPage::$rewriteSlug,
                'post_content' => 'CM Download Manager',
                'post_author' => get_current_user_id(),
                'post_status' => 'future',
                'post_date' => Date('Y-m-d H:i:s', time() + 3600 * 24 * 365 * 10),
                'post_type' => self::FAKE_POST_TYPE,
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            ];

            if (self::$_fake_post_id = wp_insert_post($post)) {
                add_post_meta(self::$_fake_post_id, self::FAKE_POST_META_KEY, 1);
            }

        } else { // Change back to "future"
            if ($record->post_status != 'future') {
                $record->post_status = 'future';
                $record->post_date = Date('Y-m-d H:i:s', time() + 3600 * 24 * 365 * 10);
                self::$_fake_post_id = wp_update_post($record);
            }
        }
    }

    static protected function prepareSinglePage($title, $content, $newQuery = false) {
        global $wp, $wp_query, $wp_the_query, $post, $wpdb;

        // Call this filter to set the WP SEO title before the $wp_query instance will be replaced:
        $wp_seo_title = apply_filters('wp_title', $title, '', '');

        if ($newQuery) {
            $args = [
                'post_type' => self::FAKE_POST_TYPE,
                'meta_key' => self::FAKE_POST_META_KEY,
                'meta_value' => '1'
            ];
            $wp_query = new WP_Query($args);
            if ($wp_query->post_count == 0 or empty($wp_query->posts) or empty($wp_query->posts[0])) {
                $wp_query->posts[0] = $wp_query->post = self::getFakePost();
                $wp_query->found_posts = $wp_query->post_count = 1;
            }
            $wp_query->posts[0]->post_title = $title;
            $wp_query->posts[0]->post_content = $content;
            $post = $wp_query->post = reset($wp_query->posts);
            $wp_the_query = $wp_query;
            $wp_query->is_single = true;
            $wp_query->is_singular = true;
            $wp_query->is_page = true;
        }
        $wp_query->set('cmdm_prepared_single', 1);
        $wp_query->set('cmdm_title', $title);
        add_filter('the_title', [__CLASS__, 'filterTitle'], 10, 2);
        return locate_template(['page.php', 'single.php'], false, false);
    }

    static function filterTitle($title, $id = null) {
        global $wp_query;
        $fakePost = self::getFakePost();
        if (is_main_query() and is_single() and get_query_var('cmdm_prepared_single') and $cmdmTitle = get_query_var('cmdm_title')
            and $title == str_replace('-', '&#8211;', $fakePost->post_title)) {
            $title = $cmdmTitle;
        }
        return $title;
    }

    static function getFakePost() {
        global $wpdb;
        static $fake_post_cache = null;

        if (null === $fake_post_cache) {
            if (null === self::$_fake_post_id) {
                $record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->posts p
					JOIN $wpdb->postmeta m ON m.post_id = p.ID AND m.meta_key = %s
					WHERE post_type = %s",
                    self::FAKE_POST_META_KEY, self::FAKE_POST_TYPE));
                if ($record) {
                    $fake_post = new WP_Post($record);
                    self::$_fake_post_id = $fake_post->ID;
                } else {
                    self::initFakePage();
                }
            }
            $fake_post_cache = get_post(self::$_fake_post_id);
        }

        return $fake_post_cache;
    }
}
