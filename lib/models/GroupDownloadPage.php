<?php
include_once CMDM_PATH . '/lib/models/PostType.php';
include_once CMDM_PATH . '/lib/models/SupportThread.php';
include_once CMDM_PATH . '/lib/helpers/Files.php';
class CMDM_GroupDownloadPage extends CMDM_PostType
{
    /**
     * Post type name
     */
    const POST_TYPE = 'cmdm_page';

    /**
     * Rewrite slug
     */
    public static $rewriteSlug = 'cmdownloads';
    private $_errors = array();

    const ADMIN_MENU                = 'CMDM_downloads_menu';
    /**
     * Name of category taxonomy
     */
    const CAT_TAXONOMY              = 'cmdm_category';
    /**
     * Directory for uploads
     */
    const UPLOAD_DIR                = 'cmdm';
    const SCREENSHOTS_DIR           = 'screenshots';
    const OPTION_ADDONS_TITLE       = 'CMDM_addons_title';
    const ALLOWED_EXTENSIONS_OPTION = 'CMDM_allowed_extensions';
    const OPTION_FORCE_BROWSER_DOWNLOAD_ENABLED = 'CMDM_force_browser_download_enabled';

    /**
     * @var CMDM_GroupDownloadPage[] singletones cache
     */
    protected static $instances = array();
    /**
     * @var array meta keys mapping
     */
    protected static $_meta     = array(
        'version' => '_version',
        'screenshots' => '_screenshots',
        'screenshots_caches' => '_screenshots_caches',
        'screenshots_v2' => '_screenshots_v2',
        'download_file' => '_download_file',
        'file_size' => '_file_size',
    	'package_type'           => '_package_type',
        'number_of_downloads' => '_number_of_downloads',
        'recommended' => '_recommendation',
        'categories' => '_categories',
        'ratings' => '_ratings',
        'support_notifications' => '_support_notifications'
    );

    /**
     * Initialize model
     */
    public static function init()
    {
        // register Deal post type
        $post_type_args = array(
            'has_archive' => TRUE,
            //'menu_position' => 4,
            'show_in_menu' => self::ADMIN_MENU,
            'rewrite' => array(
                'slug' => self::$rewriteSlug,
                'with_front' => FALSE,
            ),
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'revisions', 'post-formats'),
            'hierarchical' => false
        );
        $plural         = self::getAddonsTitle();
        self::registerPostType(self::POST_TYPE, 'Download', $plural, 'CM Downloads', $post_type_args);

        // Ticket 57536
        add_filter('CMDM_admin_parent_menu', function(){ return self::ADMIN_MENU; } );
        add_action('admin_menu', array(get_class(), 'registerAdminMenu'));
        // register Categories taxonomy
        $singular      = 'Category';
        $plural        = 'Categories';
        $taxonomy_args = array(
            'rewrite' => array(
                'slug' => self::$rewriteSlug . '/categories',
                'with_front' => FALSE,
                'show_ui' => TRUE,
                'hierarchical' => FALSE,
            ),
        );
        self::registerTaxonomy(self::CAT_TAXONOMY, array(self::POST_TYPE), $singular, $plural, $taxonomy_args);

        add_action('generate_rewrite_rules', array(get_class(), 'fixCategorySlugs'));

        // delete screenshot by ajax
        if( defined( 'DOING_AJAX' ) && DOING_AJAX ){
            add_action('wp_ajax_deletescreenshot', array(get_class(), 'deleteScreenshot'), 1, 1);
            add_action('wp_ajax_nopriv_deletescreenshot',array(get_class(), 'deleteScreenshot'), 1, 1);
        }

        CMDM_SupportThread::init();
    }



    public static function fixCategorySlugs($wp_rewrite)
    {
        $wp_rewrite->rules = array(
            self::$rewriteSlug . '/categories/([^/]+)/?$' => $wp_rewrite->index . '?post_type=' . self::POST_TYPE . '&' . self::CAT_TAXONOMY . '=' . $wp_rewrite->preg_index(1),
            self::$rewriteSlug . '/categories/([^/]+)/page/?([0-9]{1,})/?$' => $wp_rewrite->index . '?post_type=' . self::POST_TYPE . '&' . self::CAT_TAXONOMY . '=' . $wp_rewrite->preg_index(1) . '&paged=' . $wp_rewrite->preg_index(2),
                ) + $wp_rewrite->rules;
    }

    /**
     * @static
     * @param int $id
     * @return CMDM_GroupDownloadPage
     */
    public static function getInstance($id = 0)
    {
        if(!$id)
        {
            return NULL;
        }
        if(!isset(self::$instances[$id]) || !self::$instances[$id] instanceof self)
        {
            self::$instances[$id] = new self($id);
        }
        if(self::$instances[$id]->post->post_type != self::POST_TYPE)
        {
            return NULL;
        }
        return self::$instances[$id];
    }

    public static function registerAdminMenu()
    {
    	if (current_user_can('manage_options')) {
	        // Ticket 57536
          $page = add_menu_page('Downloads', 'CM Downloads', 'manage_options', self::ADMIN_MENU, function(){ return; });
	        add_submenu_page(self::ADMIN_MENU, 'Add New', 'Add New', 'manage_options', 'post-new.php?post_type=' . self::POST_TYPE);
	        add_submenu_page(self::ADMIN_MENU, 'Categories', 'Categories', 'manage_options', 'edit-tags.php?taxonomy=' . self::CAT_TAXONOMY . '&amp;post_type=' . self::POST_TYPE);
	        if ( isset($_GET['taxonomy']) && ( $_GET['taxonomy'] === self::CAT_TAXONOMY ) && isset($_GET['post_type']) && ( $_GET['post_type'] === self::POST_TYPE ) )
	        {
	            add_filter('parent_file', function(){ return self::ADMIN_MENU; }, 999);
	        }
    	}
    }

    public static function getAddonsTitle()
    {
        return get_option(self::OPTION_ADDONS_TITLE, 'CM Downloads');
    }

    /**
     * Get description of download
     * @return string
     */
    public function getDescription()
    {
        return $this->post->post_content;
    }

    /**
     * Set description for download
     * @param string $_description
     * @param bool $save Save immediately?
     * @return CMDM_GroupDownloadPage
     */
    public function setDescription($_description, $save = false)
    {
        $this->post->post_content = nl2br($_description);
        if($save) $this->savePost();
        return $this;
    }

    /**
     * Get excerpt
     * @return string
     */
    public function getExcerpt()
    {
        return $this->post->post_excerpt;
    }

    /**
     * Set excerpt
     * @param string $_excerpt
     * @param bool $save Save immediately?
     * @return CMDM_GroupDownloadPage
     */
    public function setExcerpt($_excerpt, $save = false)
    {
        $this->post->post_excerpt = $_excerpt;
        if($save) $this->savePost();
        return $this;
    }

    /**
     * Set status
     * @param string $_status
     * @param bool $save Save immediately?
     * @return CMDM_GroupDownloadPage
     */
    public function setStatus($_status, $save = false)
    {
        $this->post->post_status = $_status;
        if($save) $this->savePost();
        return $this;
    }

    public function getStatus()
    {
        $status = $this->post->post_status;
        if($status == 'draft') return __('not published', 'cm-download-manager');
        elseif($status == 'publish') return __('published', 'cm-download-manager');
    }

    /**
     * Get author ID
     * @return int Author ID
     */
    public function getAuthorId()
    {
        return $this->post->post_author;
    }

    /**
     * Get author
     * @return WP_User
     */
    public function getAuthor()
    {
        return get_userdata($this->getAuthorId());
    }

    /**
     * Set author
     * @param int $_author
     * @param bool $save Save immediately?
     * @return CMDM_GroupDownloadPage
     */
    public function setAuthor($_author, $save = false)
    {
        $this->post->post_author = $_author;
        if($save) $this->savePost();
        return $this;
    }

    /**
     * Get when item was updated
     * @param string $format
     * @return string
     */
    public function getUpdated($format = '')
    {
        if(empty($format)) $format = get_option('date_format');
        return date_i18n($format, strtotime($this->post->post_modified));
    }

    public function setUpdated($date = null)
    {
        if ( empty($date) ) {
			$date = current_time('mysql');
		}
        $this->post->post_modified = $date;
        $this->savePost();
        return $this;
    }

    public function getRatingsList()
    {
        $ratings = $this->getPostMeta(self::$_meta['ratings']);
        if(empty($ratings)) $ratings = array();
        return $ratings;
    }

    public function addRating($userId, $rating)
    {
        $ratings   = $this->getRatingsList();
        $ratings[] = array(
            'timestamp' => time(),
            'user' => $userId,
            'rating' => $rating
        );
        $this->savePostMeta(array(self::$_meta['ratings'] => $ratings));
    }

    public function getRatingStats()
    {
        $ratings     = $this->getRatingsList();
        $ratingCount = count($ratings);
        $ratingAvg   = 0;
        if($ratingCount > 0)
        {
            $sum = 0;
            foreach($ratings as $record)
            {
                $sum+=intval($record['rating']);
            }
            $ratingAvg = ($sum * 1.0) / ($ratingCount * 1.0);
        }
        return array(
            'ratingsCount' => $ratingCount,
            'ratingAvg' => $ratingAvg
        );
    }

    public function isRatingAllowed($userId)
    {
        $allowed = true;
        $ratings = $this->getRatingsList();
        foreach($ratings as $record)
        {
            if($record['user'] == $userId)
            {
                $allowed = false;
                break;
            }
        }
        return $allowed;
    }

    public function getVersion()
    {
        return $this->getPostMeta(self::$_meta['version']);
    }

    public function setVersion($_version)
    {
        $this->savePostMeta(array(self::$_meta['version'] => $_version));
        return $this;
    }

    public function getScreenshots()
    {
        return $this->getPostMeta(self::$_meta['screenshots']);
    }

    public function getScreenshotsCaches()
    {
        return $this->getPostMeta(self::$_meta['screenshots_caches']);
    }

    /**
     * Save screenshots. Old version
     * @param [array|json] $_screenshots: list of filenames 
     */
    public function setScreenshots($_screenshots)
    {
        if(!is_array($_screenshots)) $_screenshots = json_decode(stripslashes($_screenshots));
        $this->savePostMeta(array(self::$_meta['screenshots'] => $_screenshots));
        return $this;
    }

    // (+RA) get screenshots uploaded by WP Uploader to DB
    public function getScreenshotsV2()
    {
        $screenshotsV2 = array();
        $result = $this->getPostMeta(self::$_meta['screenshots_v2']);
        if(isset($result) && $result){
            $screenshotsV2 = is_array($result) ? $result : array($result);
        }
        return $screenshotsV2;
    }

    /**
     * Save screenshots. Version 2.0
     * @param [array|json] $_screenshots: list of media ids 
     */
    public function setScreenshotsV2($_screenshots)
    {
        if(!is_array($_screenshots)) $_screenshots = json_decode( sanitize_text_field($_screenshots) );
        $this->savePostMeta(array(self::$_meta['screenshots_v2'] => $_screenshots));
        return $this;
    }

    // (+RA)
    public function migrateOldScreenshotsToV2($screenshots, $post_id = 0){
        $ids = array();
        foreach ($screenshots as $fname) {
            $ids[] = $this->migrateOldScreenshotToV2($fname, $post_id);
        }
        if($ids){
            $this->setScreenshotsV2($ids);
        }
        return $ids;
    }

    // (+RA)
    public function migrateOldScreenshotToV2($fname, $post_id = 0){
        $id = false;
        $url = self::getScreenshotsUrl() . $fname;
        $id = CMDM_Files::uploadToWp(array("url" => $url, "post_id" => $post_id));
        return $id;
    }

    public function setScreenshotsCaches($_screenshots_caches)
    {
        if(!is_array($_screenshots_caches)) $_screenshots_caches = json_decode(stripslashes($_screenshots_caches), true);
        $this->savePostMeta(array(self::$_meta['screenshots_caches'] => $_screenshots_caches));
        return $this;
    }

    public function getDownloadFile()
    {
        return $this->getPostMeta(self::$_meta['download_file']);
    }

    public function setDownloadFile($_download_file)
    {
        $name         = time() . '_' . sanitize_file_name($_download_file['name']);
        $uploadFolder = $this->getUploadPath();
        if($uploadFolder)
        {
            $target = $uploadFolder . $name;
            if(move_uploaded_file($_download_file['tmp_name'], $target))
            {
                $this->setMimeType($_download_file['type'])
                        ->setFileSize($_download_file['size']);

                $this->savePostMeta(array(self::$_meta['package_type'] => 'file',
                    self::$_meta['download_file'] => $name));
            }
            else
            {
                $this->addError(__('File upload failed!'));
            }
        }
        return $this;
    }

    protected function addError($errorMsg)
    {
        $this->_errors[] = $errorMsg;
    }

    protected function getErrors()
    {
        if($this->_errors)
        {
            return $this->_errors;
        }
        return null;
    }

    public function isOwnerNotified()
    {
        return $this->getPostMeta(self::$_meta['support_notifications']);
    }

    public function setOwnerNotified($notifications = false)
    {
        $this->savePostMeta(array(self::$_meta['support_notifications'] => (bool) $notifications));
        return $this;
    }

    public function getMimeType()
    {
        $filename = $this->getFilePath();
        $mimeType = $this->post->post_mime_type;
        if(!$mimeType)
        {
            $mimeType = get_post_mime_type($this->post);
        }
        if(!$mimeType)
        {
        	if (function_exists('finfo_open')) {
	            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
	            $mimeType = finfo_file($finfo, $filename);
	            finfo_close($finfo);
	            $this->setMimeType($mimeType);
        	}
        }
        return $mimeType;
    }

    public function setMimeType($_mime, $save = false)
    {
        global $wpdb;
        $this->post->_post_mime_type = $_mime;
        if(isset($this->post->ID))
        // had to use $wpdb, because wp_update_post does not update post_mime_type field
            $wpdb->query('UPDATE ' . $wpdb->base_prefix . 'posts SET post_mime_type="' . $_mime . '" WHERE ID="' . $this->post->ID . '"');
       // if($save) $this->savePost();
        return $this;
    }

    public function getFileSize()
    {
        return $this->getPostMeta(self::$_meta['file_size']);
    }

    public function setFileSize($_file_size)
    {
        $this->savePostMeta(array(self::$_meta['file_size'] => $_file_size));
        return $this;
    }

    public function isRecommended()
    {
        return $this->getPostMeta(self::$_meta['recommended']) == 1;
    }

    public function setRecommended($_recommended)
    {
        $this->savePostMeta(array(self::$_meta['recommended'] => (int) $_recommended));
        return $this;
    }

    public function addNumberOfDownloads()
    {
        $oldNumber = $this->getNumberOfDownloads();
        $oldNumber = empty($oldNumber) ? 0 : $oldNumber;
        $this->savePostMeta(array(self::$_meta['number_of_downloads'] => $oldNumber + 1));
        return $this;
    }

    public function getNumberOfDownloads()
    {
        $counter = $this->getPostMeta(self::$_meta['number_of_downloads']);
        return $counter ? $counter : 0;
    }

    public function isEditAllowed($userId)
    {
        return (user_can($userId, 'manage_options') || $this->getAuthorId() == $userId);
    }

    public function setCategories($categories = array())
    {
        $categories = array_map('intval', $categories);
        wp_set_object_terms($this->getId(), $categories, self::CAT_TAXONOMY);
        return $this;
    }

    public function getCategories($withNames = false)
    {
        $list  = array();
        $terms = wp_get_object_terms($this->getId(), self::CAT_TAXONOMY);
        if(is_array($terms)) foreach($terms as $term)
            {
                if($withNames) $list[$term->term_id] = $term->name;
                else $list[]               = $term->term_id;
            }
        return $list;
    }

    public function getUploadUrl()
    {
        $uploadDir = wp_upload_dir();
        $baseDir   = $uploadDir['baseurl'] . '/' . self::UPLOAD_DIR . '/';
        $dir       = $baseDir . $this->getId() . '/';
        wp_mkdir_p($dir);
        return $dir;
    }

    public function getUploadPath()
    {
        $uploadDir = wp_upload_dir();
        if($uploadDir['error'])
        {
            $this->addError(__('Error while getting wp_upload_dir():' . $uploadDir['error']));
            return '';
        }
        else
        {
            $dir = $uploadDir['basedir'] . '/' . self::UPLOAD_DIR . '/' . $this->getId() . '/';
            if(!is_dir($dir))
            {
                if(!wp_mkdir_p($dir))
                {
                    $this->addError(__('Script couldn\'t create the upload folder:' . $dir));
                    return '';
                }
            }
            return $dir;
        }
    }

    public static function getScreenshotsPath()
    {
        $uploadDir = wp_upload_dir();
        $baseDir   = $uploadDir['basedir'] . '/' . self::UPLOAD_DIR . '/';
        $dir       = $baseDir . self::SCREENSHOTS_DIR . '/';
        wp_mkdir_p($dir);
        return $dir;
    }

    public static function getScreenshotsUrl()
    {
        $uploadDir = wp_upload_dir();
        $baseDir   = $uploadDir['baseurl'] . '/' . self::UPLOAD_DIR . '/';
        $dir       = $baseDir . self::SCREENSHOTS_DIR . '/';
        return $dir;
    }

    public static function saveScreenshot($file)
    {
        $pathinfo = pathinfo($file['name']);
        $name     = strtolower((time() . uniqid()) . '.' . $pathinfo['extension']);
        $target   = self::getScreenshotsPath() . $name;
        if(@move_uploaded_file($file['tmp_name'], $target))
        {
            return $name;
        }
        else throw new Exception('File could not be saved.');
    }

    /**
     * New download method
     * @since 1.6.0
     * @author Marcin
     */
    public function download()
    {
        $filepath = $this->getFilePath();
        if(is_file($filepath))
        {
            $this->addNumberOfDownloads();

            $ext = pathinfo($filepath, PATHINFO_EXTENSION);
            if(!empty($ext))
            {
                $ext = '.' . $ext;
            }

            $mimeType = $this->getMimeType();
            $fileSize = filesize($filepath);

            while(ob_get_level())
            {
                @ob_end_clean();
            }

            if(strpos($ext, 'mp3'))
            {
                $mimeType = 'application/octet-stream';
            }

            if(headers_sent($headersFile, $headersLine))
            {
                die('Headers file:' . $headersFile . ' on line: ' . $headersLine);
            }

            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false); // required for certain browsers
            if (self::forceBrowserDownloadEnabled()) {
            	$mimeType = 'application/octet-stream';
            	header('Content-Description: File Transfer');
            	header("Content-Disposition: attachment; filename=\"" . $this->getFileName() . "\";");
            	header("Content-Transfer-Encoding: binary");
            	header("Content-Length: " . $fileSize);
            }
            header("Content-type: " . $mimeType);
            readfile($filepath);
            exit;
        }
        else
        {
            /*
             * Broken download - new name to easily nail down the issue
             */
            header("Pragma: public");
            header("Expires: -1");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: 0");
            header('Content-Disposition: attachment; filename="broken-download.txt"');
            exit;
        }
        exit;
    }

    public function getFileName() {
    	$filepath = $this->getFilePath();
    	if( is_file($filepath) ) {
    		$ext = pathinfo($filepath, PATHINFO_EXTENSION);
    		if( !empty($ext) ) $ext = '.' . $ext;
    		return sanitize_file_name($this->getTitle()) . $ext;
    	}
    }


    public static function forceBrowserDownloadEnabled() {
    	return get_option(self::OPTION_FORCE_BROWSER_DOWNLOAD_ENABLED, 1);
    }

    static function showVersionField() {
    	return get_option(CMDM_CmdownloadController::OPTION_VERSION_FIELD_SHOW, 0);
    }

    static function showSupport() {
    	return get_option(CMDM_CmdownloadController::OPTION_SUPPORT_SHOW, 0);
    }

    static function showAdminRecommend() {
    	return get_option(CMDM_CmdownloadController::OPTION_ADMIN_RECOMMEND_SHOW, 0);
    }

    static function getDownloadButtonWidgetArea() {
    	return get_option(CMDM_CmdownloadController::OPTION_DOWNLOAD_BUTTON_WIDGET_AREA, CMDM_CmdownloadController::AREA_SINGLE_SCREENSHOTS_BELOW);
    }

    public function getFilePath()
    {
        $file = $this->getDownloadFile();
        if($file)
        {
            $dir = $this->getUploadPath();
            if($dir)
            {
                $filepath = $dir . $file;
                return $filepath;
            }
        }
        return '';
    }

    public function old_download()
    {
        if($this->getPackageType() == 'file')
        {
            header('Content-type: ' . $this->getMimeType());
            $filepath = $this->getFilePath();
            $ext      = pathinfo($filepath, PATHINFO_EXTENSION);

            if(ob_get_level())
            {
                ob_clean();
                ob_end_flush();
            }
            if(!empty($ext)) $ext = '.' . $ext;
            header('Content-Disposition: attachment; filename="' . sanitize_file_name($this->getTitle()) . $ext . '"');
            readfile($filepath);
            exit;
        }
        else
        {
            wp_redirect($this->getDownloadUrl(), 303);
        }
        exit;
    }

    public static function newInstance($data = array())
    {
        $id = wp_insert_post(array(
            'post_status' => 'publish',
            'post_type' => self::POST_TYPE,
            'post_title' => $data['title'],
            'post_name' => sanitize_title_with_dashes($data['title']),
            'post_author' => get_current_user_id(),
        ));
        if($id instanceof WP_Error)
        {
            return $id->get_error_message();
        }
        else
        {   
            $instance = self::getInstance($id);

            if(isset($data['categories']) && is_array($data['categories'])){
                $data['categories'] = array_map('sanitize_text_field', $data['categories']);
            } 
            if(isset($data['package']) && is_array($data['package'])){
                $data['package'] = array_map('sanitize_text_field', $data['package']);
            } 

            $instance->setDescription($data['description'], true)
                    ->setVersion( sanitize_text_field($data['version'] ?? '') )
                    ->setCategories($data['categories'])
                    ->setDownloadFile($data['package'])
                    ->setRecommended(empty($data['admin_supported']) ? 0 : 1)
                    ->setOwnerNotified( sanitize_text_field($data['support_notifications']))
                    ->setScreenshotsV2( sanitize_text_field($data['screenshots_v2']) );

            if(isset($data['screenshots'])){
                $instance->setScreenshots( sanitize_text_field($data['screenshots']) )
                         ->setScreenshotsCaches( sanitize_text_field($data['screenshots-caches']) );

            }
            
            if(isset($data['visibility']))
            {
                $instance->setVisibility( sanitize_text_field($data['visibility']) );
            }

            $errors = $instance->getErrors();
            if(empty($errors))
            {
                $instance->setUpdated()
                        ->savePost();
                return $instance;
            }
            else
            {
                return implode('<br/>', $errors);
            }
        }
    }

    public function update($data = array())
    {   
        if(isset($data['title'])) $this->setTitle($data['title']);
        if(isset($data['description'])) $this->setDescription($data['description']);
        $this->savePost();
        if(isset($data['version'])) $this->setVersion( sanitize_text_field($data['version']) );
        if(isset($data['categories']) && is_array($data['categories'])){
            $this->setCategories( array_map('sanitize_text_field', $data['categories']) );
        } 
        if(isset($data['type'])) $this->setType( sanitize_text_field($data['type']) );
        if(isset($data['package']) && !empty($data['package']) && is_array($data['package'])){
           $this->setDownloadFile( array_map('sanitize_text_field', $data['package']) ); 
        } 
        if(isset($data['screenshots'])) $this->setScreenshots( sanitize_text_field($data['screenshots']) );
        if(isset($data['screenshots-caches'])) $this->setScreenshotsCaches( sanitize_text_field($data['screenshots-caches']) );
        if(isset($data['admin_supported'])) $this->setRecommended( sanitize_text_field($data['admin_supported']) );
        else $this->setRecommended('false');
        if(isset($data['support_notifications'])) $this->setOwnerNotified( sanitize_text_field($data['support_notifications']) );
        else $this->setOwnerNotified('false');

        if(isset($data['visibility'])) $this->setVisibility(  sanitize_text_field($data['visibility']) );
        if(isset($data['screenshots_v2'])) $this->setScreenshotsV2( sanitize_text_field($data['screenshots_v2']) );

        $errors = $this->getErrors();

        if(empty($errors))
        {
            $this->setUpdated();
            return $this;
        }
        else
        {
            return implode('<br/>', $errors);
        }
    }

    public function delete()
    {
        $delRes = wp_delete_post($this->getId(), true);
        if($delRes !== false ){
            $this->deleteFiles();
        }
        return $delRes !== false;
    }

    private function deleteFiles(){
        // delete downloaded file
        self::getUploadPath() ? CMDM_BaseController::deleteDir(self::getUploadPath()) : $errors[] = "Error to delete downloaded file";

        // delete linked screenshots and screenshots cache
        $screenshotsFilesPath = self::getScreenshotsPath();
        $screenshotsFiles = self::getScreenshots();

        $screenshotsCachesPath = self::getScreenshotsPath() . 'cache/';
        $screenshotsCaches = self::getScreenshotsCaches();

        if(isset($screenshotsFiles) && is_array($screenshotsFiles)){
            foreach ($screenshotsFiles as $screenshotsFile) {
                if( file_exists($screenshotsFilesPath . "/" . $screenshotsFile) ){
                    if( unlink($screenshotsFilesPath . "/" . $screenshotsFile) ){
                        if( isset($screenshotsCaches) && is_array($screenshotsCaches) ){
                            $key = array_search($screenshotsFile, array_column($screenshotsCaches, 'fileName'));
                            if( file_exists($screenshotsCachesPath . "/" . $screenshotsCaches[$key]["cacheName"]) ){
                                unlink($screenshotsCachesPath . "/" . $screenshotsCaches[$key]["cacheName"]);
                            }
                        }
                    }
                }
            }
        }
        return;
    }

    public static function getTotalCount()
    {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type=%s AND post_status='publish'", self::POST_TYPE);
        return $wpdb->get_var($sql);
    }

    public static function getMainCategories($hideEmpty = false, $fullInfo = false)
    {
        $terms = get_terms(self::CAT_TAXONOMY, array('hide_empty' => $hideEmpty));
        $list  = array();
        foreach($terms as $val)
        {
            if($fullInfo) $list[$val->term_id] = array(
                    'name' => $val->name,
                    'count' => $val->count,
                    'url' => get_term_link($val, self::CAT_TAXONOMY)
                );
            else $list[$val->term_id] = $val->name;
        }
        return $list;
    }

    public static function getDownloadsByUser($userId)
    {
        $args   = array(
            'post_type' => self::POST_TYPE,
            'author' => $userId,
            'fields' => 'ids',
            'post_status' => array('draft', 'publish'),
        	'posts_per_page' => -1,
        );
        $wp     = new WP_Query($args);
        $result = array();
        foreach($wp->get_posts() as $id)
        {
            $result[] = self::getInstance($id);
        }
        return $result;
    }

    public static function processImage($img, $size, $hash = false)
    {   
        ob_start();
        $hash = $hash ? $hash : md5($img . $size);
        try
        {
            $imgPath        = self::getScreenshotsPath() . $img;
            $imageInfo      = getimagesize($imgPath);
            $originalWidth  = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            list($filetype, $ext) = explode('/', $imageInfo['mime']);
            $cacheDir       = self::getScreenshotsPath() . 'cache/';
            if(!file_exists($cacheDir)) mkdir($cacheDir);
            if(!file_exists($cacheDir . $hash))
            {
                switch($ext)
                {
                    case 'gif':
                        $createFunc = 'imagecreatefromgif';
                        $targetFunc = 'imagepng';
                        $ext        = 'png';
                        break;
                    case 'png':
                        $createFunc = 'imagecreatefrompng';
                        $targetFunc = 'imagepng';
                        break;
                    case 'jpeg':
                    case 'jpg':
                        $createFunc = 'imagecreatefromjpeg';
                        $targetFunc = 'imagejpeg';
                        break;
                }
                $originalImg   = $createFunc($imgPath);
                list($targetWidth, $targetHeight) = explode('x', $size);
                $originalRatio = ($originalWidth * 1.0) / ($originalHeight * 1.0);
                $targetRatio   = ($targetWidth * 1.0) / ($targetHeight * 1.0);
                if($targetRatio > $originalRatio)
                {//original img is higher, do not fit to width
                    $targetWidth = $originalRatio * $targetHeight;
                }
                elseif($targetRatio < $originalRatio)
                {
                    $targetHeight = $targetWidth / $originalRatio;
                }

                $left = $top  = 0;

                $dst   = imagecreatetruecolor($targetWidth, $targetHeight);
                $white = imagecolorallocate($dst, 255, 255, 255);
                imagefilledrectangle($dst, 0, 0, $targetWidth, $targetHeight, $white);
                imagecopyresampled($dst, $originalImg, $left, $top, 0, 0, $targetWidth, $targetHeight, $originalWidth, $originalHeight);
                imagedestroy($originalImg);
                $targetFunc($dst, $cacheDir . $hash);
                imagedestroy($dst);
            }

            header('Content-type: ' . implode('/', array($filetype, $ext)));
            ob_clean();
            flush();
            readfile($cacheDir . $hash);
            exit;
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
            exit;
        }
    }

    // +RA delete linked screenshots and screenshots cache if screenshot deleted in the Add Download form
    public static function deleteScreenshot(){
        $responce = array();
        $responce['errors'] = array();

        // delete linked screenshots and screenshots cache
        $screenshotsFilesPath = self::getScreenshotsPath();
        $screenshotsCachesPath = self::getScreenshotsPath() . 'cache/';

        $screenshotsFile = isset($_POST["fileName"]) ? sanitize_text_field($_POST["fileName"]) : "";
        $screenshotsCache = isset($_POST["cacheName"]) ? sanitize_text_field($_POST["cacheName"]) : "";

        if( file_exists($screenshotsFilesPath . "/" . $screenshotsFile) ){
            if( unlink($screenshotsFilesPath . "/" . $screenshotsFile) ){
                if( file_exists($screenshotsCachesPath . "/" . $screenshotsCache) ){
                    if(!unlink($screenshotsCachesPath . "/" . $screenshotsCache)){
                        $responce[] = "Screenshot cache (" . $screenshotsFile . ") delete error";
                    }
                }else{
                    $responce[] = "Screenshot cache (" . $screenshotsCache . ") does not exist";
                }
            }else{
                $responce[] = "Screenshot file (" . $screenshotsFile . ") delete error";
            }
        }else{
            $responce[] = "Screenshot file (" . $screenshotsFile . ") does not exist";
        }
        echo json_encode($responce);
        wp_die();
    }

}
include_once CMDM_PATH . '/lib/models/api.php';
?>
