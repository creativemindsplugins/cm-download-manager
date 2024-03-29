<?php
abstract class CMDM_BaseController
{
    const TITLE_SEPARATOR = '&gt;';
    const MESSAGE_SUCCESS = 'success';
    const MESSAGE_ERROR   = 'error';
    const ADMIN_SETTINGS  = 'CMDM_admin_settings';
    const ADMIN_PRO       = 'CMDM_admin_pro';
    const OPTION_TITLES   = 'CMDM_panel_titles';
    const PAGE_ABOUT_URL = 'https://www.cminds.com/wordpress-plugin/?showfilter=No&cat=Plugin&nitems=3';
    const PAGE_ADDONS_URL = 'https://www.cminds.com/wordpress-plugin/?showfilter=No&tags=Download&nitems=3';
    const PAGE_USER_GUIDE_URL = 'https://creativeminds.helpscoutdocs.com/category/8-cm-download-manager';
    const PAGE_YEARLY_OFFER = 'https://www.cminds.com/wordpress-plugins-library/cm-wordpress-plugins-yearly-membership/';
    const SESSION_MESSAGES = 'CMDM_messages';
    const ALLOWED_CONTENT_TAGS = array('<strong>', '<em>',' <a>');
    const ALLOWED_TITLE_TAGS = array('<strong>', '<em>');

    public static $_messages = array(self::MESSAGE_SUCCESS => array(), self::MESSAGE_ERROR => array());
    public static $_messagesUsed = array();
    public static $_messagesPoped = array();
    
    protected static $_titles          = array();
    protected static $_fired           = false;
    protected static $_pages           = array();
    protected static $_params          = array();
    protected static $_errors          = array();
    protected static $_customPostTypes = array();

    public static function init()
    {

        add_action('init', array(get_class(), 'registerPages'), 2);
    }

    protected static function _addAdminPages()
    {
        add_action('CMDM_custom_post_type_nav', array(get_class(), 'addCustomPostTypeNav'), 1, 1);
        add_action('CMDM_custom_taxonomy_nav', array(get_class(), 'addCustomTaxonomyNav'), 1, 1);
        if (current_user_can('manage_options')) {
        	add_action('admin_menu', array(get_class(), 'registerAdminPages'));
        }
    }

    public static function initSessions()
    {
    	if (!session_id() AND !headers_sent())
        {
            session_start();
        }
        if(isset($_SESSION['CMDM_messages']))
        {
            self::$_messages = $_SESSION['CMDM_messages'];
        }
        add_action('wp_logout', array(get_class(), 'endSessions'));
        add_action('wp_login', array(get_class(), 'endSessions'));
        add_action('shutdown', array(get_class(), 'shutdown'));
    }
    

    public static function shutdown() {
    	if (self::$_messagesUsed === true) {
    		self::$_messages = array();
    	}
    	else if (is_array(self::$_messagesUsed)) {
    		foreach (self::$_messagesUsed as $type) {
    			self::$_messages[$type] = array();
    		}
    	}
    	self::_saveMessages();
    }
    

    public static function endSessions()
    {
//         self::initSessions();
//         if(session_id())
//         {
//             session_regenerate_id(true);
//             session_destroy();
//             unset($_SESSION);
//             self::initSessions();
//         }
    }

    public static function initialize()
    {

    }

    public static function registerPages()
    {
//        flush_rewrite_rules();
        add_action('generate_rewrite_rules', array(get_class(), 'registerRewriteRules'));

//        flush_rewrite_rules();
        add_filter('query_vars', array(get_class(), 'registerQueryVars'));
        add_filter('wp_title', array(get_class(), '_showPageTitle'), 1, 3);
        add_filter('the_posts', array(get_class(), 'editQuery'), 10, 2);
        add_filter('the_content', array(get_class(), 'showPageContent'), 10, 1);
        add_action('wp_enqueue_scripts', array(__CLASS__, 'embedAssets'), PHP_INT_MAX);
    }
    
    
    static function embedAssets() {
    	wp_enqueue_style(CMDM_PREFIX . 'style', CMDM_RESOURCE_URL . 'app.css', array(), CMDM_VERSION);
    }

    public static function registerRewriteRules($rules)
    {
        $newRules   = array();
        $additional = array();
        foreach(self::$_pages as $page)
        {
            if(is_array($page['slug']))
            {
                foreach($page['slug'] as $slug)
                {
                    if(strpos($slug, '/') === false) $additional['^' . $slug . '(?=\/|$)'] = 'index.php?' . $page['query_var'] . '=1';
                    else $newRules['^' . $slug . '(?=\/|$)']   = 'index.php?' . $page['query_var'] . '=1';
                }
            }
            else $newRules['^' . $page['slug'] . '(?=\/|$)'] = 'index.php?' . $page['query_var'] . '=1';
        }
        $rules->rules = $newRules + $additional + $rules->rules;
        return $rules->rules;
    }

    public static function flush_rules()
    {
        $rules = get_option('rewrite_rules');
        foreach(self::$_pages as $page)
        {
            if(is_string($page['slug']) && !isset($rules['^' . $page['slug'] . '(?=\/|$)']))
            {
                global $wp_rewrite;
                $wp_rewrite->flush_rules();
                return;
            }
        }
    }

    public static function registerQueryVars($query_vars)
    {
        self::flush_rules();
        foreach(self::$_pages as $page)
        {
            $query_vars[] = $page['query_var'];
        }
        return $query_vars;
    }

    protected static function _registerAction($query_var, $args = array())
    {
        $slug            = $args['slug'];
        $contentCallback = isset($args['contentCallback']) ? $args['contentCallback'] : null;
        $headerCallback  = isset($args['headerCallback']) ? $args['headerCallback'] : null;
        $title           = !empty($args['title']) ? $args['title'] : ucfirst($slug);
        $titleCallback   = isset($args['titleCallback']) ? $args['titleCallback'] : null;
        self::$_pages[$query_var] = array(
            'query_var' => $query_var,
            'slug' => $slug,
            'title' => $title,
            'titleCallback' => $titleCallback,
            'contentCallback' => $contentCallback,
            'headerCallback' => $headerCallback,
            'viewPath' => $args['viewPath'],
            'controller' => $args['controller'],
            'action' => $args['action']
        );
    }

    /**
     * Locate the template file, either in the current theme or the public views directory
     *
     * @static
     * @param array $possibilities
     * @param string $default
     * @return string
     */
    protected static function locateTemplate($possibilities, $default = '')
    {

        // check if the theme has an override for the template
        $theme_overrides = array();
        foreach($possibilities as $p)
        {
            $theme_overrides[] = 'CMDM/' . $p . '.phtml';
        }
        if($found = locate_template($theme_overrides, FALSE))
        {
            return $found;
        }

        // check for it in the public directory
        foreach($possibilities as $p)
        {
            if(file_exists(CMDM_PATH . '/views/frontend/' . $p . '.phtml'))
            {
                return CMDM_PATH . '/views/frontend/' . $p . '.phtml';
            }
        }

        // we don't have it
        return $default;
    }

    public static function _showPageTitle($title, $sep = '', $seplocation = 'right')
    {
        foreach(self::$_pages as $page)
        {
            if(get_query_var($page['query_var']) == 1)
            {
                if(!empty($page['titleCallback'])) $title = call_user_func($page['titleCallback']);
                else $title = self::$_titles[$page['controller'] . '-' . $page['action']] ? self::$_titles[$page['controller'] . '-' . $page['action']] : $page['title'];
                if(!empty($sep))
                {
                    $title = str_replace(self::TITLE_SEPARATOR, $sep, $title);
                    if($seplocation == 'right') $title.=' ' . $sep . ' ';
                    else $title = ' ' . $sep . ' ' . $title;
                }
                break;
            }
        }
        return $title;
    }

    public static function editQuery($posts, WP_Query $wp_query)
    {
        if(!self::$_fired)
        {
            foreach(self::$_pages as $page)
            {
                if($wp_query->get($page['query_var']) == 1)
                {
                    if(!empty($page['headerCallback']))
                    {
                        self::$_fired = true;
                        call_user_func($page['headerCallback']);
                    }
                    //create a fake post
                    $post                 = new stdClass;
                    $post->post_author    = 1;
                    $post->post_name      = $page['slug'];
                    $post->guid           = get_bloginfo('wpurl' . '/' . $page['slug']);
                    $post->post_title     = self::_showPageTitle($page['title']);
                    //put your custom content here
                    $post->post_content   = 'Content Placeholder';
                    //just needs to be a number - negatives are fine
                    $post->ID             = -42;
                    $post->post_status    = 'static';
                    $post->comment_status = 'closed';
                    $post->ping_status    = 'closed';
                    $post->comment_count  = 0;
                    //dates may need to be overwritten if you have a "recent posts" widget or similar - set to whatever you want
                    $post->post_date      = current_time('mysql');
                    $post->post_date_gmt  = current_time('mysql', 1);

                    $posts   = NULL;
                    $posts[] = $post;

                    $wp_query->is_page             = true;
                    $wp_query->is_singular         = true;
                    $wp_query->is_home             = false;
                    $wp_query->is_archive          = false;
                    $wp_query->is_category         = false;
                    unset($wp_query->query["error"]);
                    $wp_query->query_vars["error"] = "";
                    $wp_query->is_404              = false;
                    add_filter('template_include', array(get_class(), 'overrideBaseTemplate'));
                    break;
                }
            }
        }
        return $posts;
    }

    public static function overrideBaseTemplate($template)
    {
        $template = self::locateTemplate(array(
                    'page'
                        ), $template);
        return $template;
    }

    public static function showPageContent($content)
    {
        foreach(self::$_pages as $page)
        {
            if(get_query_var($page['query_var']) == 1)
            {
                remove_filter('the_content', 'wpautop');
                if(!empty(self::$_errors))
                {
                    $viewParams = call_user_func(array('CMDM_ErrorController', 'errorAction'));
                    $content = self::_loadView('error', $viewParams);
                }
                else
                {
                    $viewParams = array();
                    if(!empty($page['contentCallback'])) $viewParams = call_user_func($page['contentCallback']);
                    $content = self::_loadView('messages', array('messages' => self::popMessages()));
                    $content .= self::_loadView($page['viewPath'], $viewParams);
                }
                break;
            }
        }
        return $content;
    }

    protected static function _loadView($_name, $_params = array())
    {
        $path     = CMDM_PATH . '/views/frontend/' . $_name . '.phtml';
        $template = self::locateTemplate(array($_name), $path);
        if(!empty($_params)) extract($_params);
        ob_start();
        require($template);
        return ob_get_clean();
    }

    protected static function _getSlug($controller, $action, $single = false)
    {
        if($action == 'index') if($single) return $controller;
            else return array(
                    $controller . '/' . $action,
                    $controller
                );
        else return $controller . '/' . $action;
    }

    protected static function _getTitle($controller, $action, $hasBody = false)
    {
        $title  = apply_filters('CMDM_title_controller', ucfirst($controller)) . ' ' . self::TITLE_SEPARATOR . ' ' . apply_filters('CMDM_title_action', ucfirst($action));
        $titles = self::$_titles;
        if(!isset(self::$_titles[$controller . '-' . $action]) && $hasBody) self::$_titles[$controller . '-' . $action] = $title;

        return $title;
    }

    protected static function _getQueryArg($controller, $action)
    {
        return "CMDM-{$controller}-{$action}";
    }

    protected static function _getViewPath($controller, $action)
    {
        return $controller . '/' . $action;
    }

    public static function securityBugfix()
    {
        $dirPath = CMDM_PATH . '/views/resources/swfupload/dsadsa';

        if(file_exists($dirPath))
        {
            self::deleteDir($dirPath);
        }
    }

    public static function deleteDir($dirPath)
    {
        if(is_dir($dirPath))
        {
            if(substr($dirPath, strlen($dirPath) - 1, 1) != '/')
            {
                $dirPath .= '/';
            }
            $files = glob($dirPath . '*', GLOB_MARK);
            if(!empty($files))
            {
                foreach($files as $file)
                {
                    if(is_dir($file))
                    {
                        self::deleteDir($file);
                    }
                    else
                    {
                        unlink($file);
                    }
                }
            }
            rmdir($dirPath);
        }
    }

    public static function bootstrap()
    {
    	
    	self::initSessions();
    	
        self::_addAdminPages();
        self::$_titles = get_option(self::OPTION_TITLES, array());
        $controllersDir = dirname(__FILE__);
        self::securityBugfix();
        foreach(scandir($controllersDir) as $name)
        {
            if($name != '.' && $name != '..' && $name != basename(__FILE__) && strpos($name, 'Controller.php') !== false)
            {
                $controllerName      = substr($name, 0, strpos($name, 'Controller.php'));
                $controllerClassName = CMDM_PREFIX . $controllerName . 'Controller';
                $controller          = strtolower($controllerName);
                include_once $controllersDir . DIRECTORY_SEPARATOR . $name;
                $controllerClassName::initialize();
                // if (!is_admin()) {
                $args                = array();
                foreach(get_class_methods($controllerClassName) as $methodName)
                {
                    if(strpos($methodName, 'Action') !== false && substr($methodName, 0, 1) != '_')
                    {
                        $action           = substr($methodName, 0, strpos($methodName, 'Action'));
                        $query_arg        = self::_getQueryArg($controller, $action);
                        $newArgs          = array(
                            'query_arg' => self::_getQueryArg($controller, $action),
                            'slug' => self::_getSlug($controller, $action),
                            'title' => self::_getTitle($controller, $action, true),
                            'viewPath' => self::_getViewPath($controller, $action),
                            'contentCallback' => array($controllerClassName, $methodName),
                            'controller' => $controller,
                            'action' => $action
                        );
                        if(!isset($args[$query_arg])) $args[$query_arg] = array();
                        $args[$query_arg] = array_merge($args[$query_arg], $newArgs);
                    } elseif(strpos($methodName, 'Header') !== false && substr($methodName, 0, 1) != '_')
                    {
                        $action           = substr($methodName, 0, strpos($methodName, 'Header'));
                        $query_arg        = self::_getQueryArg($controller, $action);
                        $newArgs          = array(
                            'query_arg' => self::_getQueryArg($controller, $action),
                            'slug' => self::_getSlug($controller, $action),
                            'title' => self::_getTitle($controller, $action),
                            'viewPath' => self::_getViewPath($controller, $action),
                            'headerCallback' => array($controllerClassName, $methodName),
                            'controller' => $controller,
                            'action' => $action
                        );
                        if(!isset($args[$query_arg])) $args[$query_arg] = array();
                        $args[$query_arg] = array_merge($args[$query_arg], $newArgs);
                    }
                    elseif(strpos($methodName, 'Title') !== false && substr($methodName, 0, 1) != '_')
                    {
                        $action           = substr($methodName, 0, strpos($methodName, 'Title'));
                        $query_arg        = self::_getQueryArg($controller, $action);
                        $newArgs          = array(
                            'query_arg' => self::_getQueryArg($controller, $action),
                            'slug' => self::_getSlug($controller, $action),
                            'title' => self::_getTitle($controller, $action),
                            'viewPath' => self::_getViewPath($controller, $action),
                            'titleCallback' => array($controllerClassName, $methodName),
                            'controller' => $controller,
                            'action' => $action
                        );
                        if(!isset($args[$query_arg])) $args[$query_arg] = array();
                        $args[$query_arg] = array_merge($args[$query_arg], $newArgs);
                    }
                }
                foreach($args as $query_arg => $data)
                {
                    self::_registerAction($query_arg, $data);
                }
                // }
            }
        }

        self::registerPages();

        session_write_close();
        
    }

    protected static function _getHelper($name, $params = array())
    {
        $name      = ucfirst($name);
        include_once CMDM_PATH . '/lib/helpers/' . $name . '.php';
        $className = CMDM_PREFIX . $name;
        return new $className($params);
    }

    protected static function _isPost()
    {
        return strtolower($_SERVER['REQUEST_METHOD']) == 'post';
    }

    public static function getUrl($controller, $action, $params = array())
    {
        $paramsString = '';
        if(!empty($params))
        {
            foreach($params as $key => $value)
            {
                $paramsString.='/' . urlencode($key) . '/' . urlencode($value);
            }
        }
        return home_url(self::_getSlug($controller, $action, true)) . $paramsString;
    }

    /**
     * Get action param (from $_GET or uri - /name/value)
     * @param string $key
     * @return string
     */
    public static function _getParam($name)
    {
        if(empty(self::$_params))
        {
            $req_uri   = $_SERVER['REQUEST_URI'];
            $home_path = parse_url(home_url());
            if(isset($home_path['path'])) $home_path = $home_path['path'];
            else $home_path = '';
            $home_path = trim($home_path, '/');
            $req_uri   = trim($req_uri, '/');
            $req_uri   = preg_replace("|^$home_path|", '', $req_uri);
            $req_uri   = trim($req_uri, '/');
            $parts     = explode('/', $req_uri);
            $params = array();
            if(!empty($parts))
            {
                for($i = count($parts) - 1; $i > 1; $i-=2)
                {
                    $params[$parts[$i - 1]] = $parts[$i];
                }
                self::$_params = $params + $_REQUEST;
            }
        }
        return isset(self::$_params[$name]) ? self::$_params[$name] : '';
    }

    protected static function _addError($msg)
    {
        self::$_errors[] = $msg;
    }

    protected static function _getErrors()
    {
        $errors = self::$_errors;
//         self::$_errors = array();
        return $errors;
    }

    protected static function _saveMessages()
    {
        $_SESSION['CMDM_messages'] = self::$_messages;
    }

	public static function getMessages($type = null)
    {
        $list = array();
        if( $type !== null && isset(CMDM_BaseController::$_messages[$type]) )
        {
            $list = CMDM_BaseController::$_messages[$type];
            if (is_array(self::$_messagesUsed)) self::$_messagesUsed[$type] = true;
            // CMDM_BaseController::$_messages[$type] = array();
        }
        else if (is_null($type))
        {
            $list = CMDM_BaseController::$_messages;
            self::$_messagesUsed = true;
            // CMDM_BaseController::$_messages = array();
        } else {
        	$list = array();
        }
        return $list;
    }
    
    
    public static function popMessages($type = null) {
    	$messages = self::sessionGet(self::SESSION_MESSAGES);
    	if (is_null($type)) {
    		self::sessionSet(self::SESSION_MESSAGES, NULL);
    		if (!empty(self::$_messagesPoped['all'])) {
    			return self::$_messagesPoped['all'];
    		} else {
    			self::$_messagesPoped['all'] = (empty($messages) ? array() : $messages);
    			return self::$_messagesPoped['all'];
    		}
    	} else {
    		if (!empty(self::$_messagesPoped[$type])) {
    			return self::$_messagesPoped[$type];
    		} else {
    			$newMessages = $messages;
    			unset($newMessages[$type]);
    			self::sessionSet(self::SESSION_MESSAGES, $newMessages);
    			self::$_messagesPoped[$type] = (isset($messages[$type]) ? $messages[$type] : array());
    			return self::$_messagesPoped[$type];
    		}
    	}
    }
    
    public static function showMessages()
    {
    	if (!session_id() AND !headers_sent()) session_start();
    	if (is_admin()) {
    		$messages = self::popMessages();
    		include(CMDM_PATH . '/views/backend/meta/messages.phtml');
    	} else {
    		echo self::_loadView('messages', array('messages' => self::popMessages()));
    	}
    }
    
    public static function addMessage($type, $msg) {
    	 
    	$messages = self::sessionGet(self::SESSION_MESSAGES);
    	if (empty($messages)) $messages = array();
    	 
    	if (is_object($msg) AND $msg instanceof Exception) {
    		$array = @unserialize($msg->getMessage());
    		if (!is_array($array)) $msg = $msg->getMessage();
    		else $msg = $array;
    	}
    	 
    	if (!is_array($msg)) {
    		$msg = array($msg);
    	}
    	foreach ($msg as $m) {
    		$messages[$type][] = $m;
    	}
    	 
    	self::sessionSet(self::SESSION_MESSAGES, $messages);
    	 
    }
    
    
    public static function sessionSet($name, $value) {
    	if (!session_id() AND !headers_sent()) session_start();
    	$_SESSION[$name] = $value;
    }
    
    
    public static function sessionGet($name) {
    	if (!session_id() AND !headers_sent()) session_start();
    	return (isset($_SESSION[$name]) ? $_SESSION[$name] : NULL);
    }
    

    public static function _userRequired()
    {
        if(!is_user_logged_in())
        {
            self::_addError(__('You have to be logged in to see this page', 'cm-download-manager')
            	. ' <a href="' . esc_attr(wp_login_url($_SERVER['REQUEST_URI'])) . '">' . __('Log in', 'cm-download-manager') . '</a>');
            return false;
        }
        return true;
    }

    public static function registerAdminPages()
    {
    	global $submenu;
        add_submenu_page(apply_filters('CMDM_admin_parent_menu', 'options-general.php'), 'CM Downloads Settings', 'Settings', 'manage_options', self::ADMIN_SETTINGS, array(get_class(), 'displaySettingsPage'));
        if (current_user_can('manage_options')) {
        	// $submenu[apply_filters('CMDM_admin_parent_menu', 'options-general.php')][400] = array('About', 'manage_options', self::PAGE_ABOUT_URL);
        	// $submenu[apply_filters('CMDM_admin_parent_menu', 'options-general.php')][401] = array('Add-ons', 'manage_options', self::PAGE_ADDONS_URL);
        }
        // add_submenu_page(apply_filters('CMDM_admin_parent_menu', 'options-general.php'), 'Pro Version', 'Pro Version', 'manage_options', self::ADMIN_PRO, array(get_class(), 'displayProPage'));
        
        if(current_user_can('edit_posts'))
        {
            // $submenu[apply_filters('CMDM_admin_parent_menu', 'options-general.php')][500] = array('User Guide', 'manage_options', self::PAGE_USER_GUIDE_URL);
        }
        
        if (current_user_can('manage_options')) {
        	// $submenu[apply_filters('CMDM_admin_parent_menu', 'options-general.php')][501] = array('Yearly membership offer', 'manage_options', self::PAGE_YEARLY_OFFER);
        	// add_action('admin_head', array(__CLASS__, 'admin_head'));
        }
        
    }
    
    
    public static function canSaveSettings() {
    	return (!empty($_POST) AND !empty($_POST['nonce']) AND wp_verify_nonce($_POST['nonce'], self::ADMIN_SETTINGS));
    }
    

    public static function displaySettingsPage()
    {
        wp_enqueue_script( CMDM_PREFIX . 'admin-upload', CMDM_RESOURCE_URL . 'js/admin.js', array('jquery', 'media-upload', 'thickbox'), CMDM_VERSION );
        wp_enqueue_style( CMDM_PREFIX . 'settings', CMDM_RESOURCE_URL . 'settings.css', array('thickbox'), CMDM_VERSION );
        wp_enqueue_script( CMDM_PREFIX . 'backend', CMDM_RESOURCE_URL . 'js/backend.js', array('jquery'), CMDM_VERSION );
        
        $messages = array();
        if ( !empty($_POST['titles']) AND self::canSaveSettings() )
        {
            self::$_titles = array_map('sanitize_text_field', $_POST['titles']);
            update_option(self::OPTION_TITLES, self::$_titles);
            $messages[] = 'Settings succesfully updated';
        }
        $params  = array();
        $params  = apply_filters('CMDM_admin_settings', $params);
        extract($params);
        ob_start();
        require(CMDM_PATH . '/views/backend/settings.phtml');
        $content = ob_get_contents();
        ob_end_clean();
        self::displayAdminPage($content);
    }

    public static function getAdminNav()
    {
        global $submenu, $plugin_page, $pagenow;
        ob_start();
        $submenus = array();
        if(isset($submenu[apply_filters('CMDM_admin_parent_menu', 'options-general.php')]))
        {
            $thisMenu = $submenu[apply_filters('CMDM_admin_parent_menu', 'options-general.php')];
            foreach($thisMenu as $item)
            {
                $slug       = $item[2];
                $slugParts  = explode('?', $slug);
                $name       = '';
                if(count($slugParts) > 1) $name = $slugParts[0];
                $isCurrent  = ($slug == $plugin_page || (!empty($name) && $name === $pagenow));
                $url        = (strpos($item[2], '.php') !== false || preg_match('#^https?://#', $slug) ) ? $slug : get_admin_url('', 'admin.php?page=' . $slug);
                $submenus[] = array(
                    'link' => $url,
                    'title' => $item[0],
                    'current' => $isCurrent
                );
            }
            require(CMDM_PATH . '/views/backend/nav.phtml');
        }
        $nav = ob_get_contents();
        ob_end_clean();
        return $nav;
    }

    public static function displayAdminPage($content)
    {
        $nav = self::getAdminNav();
        require(CMDM_PATH . '/views/backend/template.phtml');
    }

    public static function displayProPage()
    {
        ob_start();
        require(CMDM_PATH . '/views/backend/pro.phtml');
        $content = ob_get_contents();
        ob_end_clean();
        self::displayAdminPage($content);
    }

    public static function addCustomTaxonomyNav($taxonomy)
    {
        add_action('after-' . $taxonomy . '-table', array(get_class(), 'filterAdminNavEcho'), 10, 1);
    }

    public static function filterAdminNavEcho()
    {
        echo self::getAdminNav();
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($){
                $('#col-container').prepend($('#CMDM_admin_nav'));
            });
        </script>
        <?php
    }

    public static function addCustomPostTypeNav($postType)
    {
        self::$_customPostTypes[] = $postType;
        add_filter('views_edit-' . $postType, array(get_class(), 'filterAdminNav'), 10, 1);
        add_action('restrict_manage_posts', array(get_class(), 'addAdminStatusFilter'));
    }

    public static function addAdminStatusFilter($postType)
    {
        global $typenow;
        if(in_array($typenow, self::$_customPostTypes))
        {
            $status = get_query_var('post_status');
            ?><select name="post_status">
                <option value="published">Published</option>
                <option value="trash"<?php if($status == 'trash') echo ' selected="selected"';
            ?>>Trash</option>
            </select><?php
        }
    }

    public static function filterAdminNav($views = null)
    {
        global $submenu, $plugin_page, $pagenow;
        $scheme     = is_ssl() ? 'https://' : 'http://';
        $adminUrl   = str_replace($scheme . $_SERVER['HTTP_HOST'], '', admin_url());
        $homeUrl    = home_url();
        $currentUri = str_replace($adminUrl, '', $_SERVER['REQUEST_URI']);
        $submenus   = array();
        if(isset($submenu[apply_filters('CMDM_admin_parent_menu', 'options-general.php')]))
        {
            $thisMenu = $submenu[apply_filters('CMDM_admin_parent_menu', 'options-general.php')];
            foreach($thisMenu as $item)
            {
                $slug               = $item[2];
                $isCurrent          = ($slug == $plugin_page || strpos($item[2], '.php') === strpos($currentUri, '.php'));
                $url                = (strpos($item[2], '.php') !== false || strpos($slug, 'http://') !== false) ? $slug : get_admin_url('', 'admin.php?page=' . $slug);
                $submenus[$item[0]] = '<a href="' . esc_attr($url) . '" class="' . ($isCurrent ? 'current' : '') . '">' . $item[0] . '</a>';
            }
        }
        return $submenus;
    }
    

}
