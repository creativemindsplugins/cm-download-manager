<?php

include_once CMDM_PATH . '/lib/models/PostType.php';
include_once CMDM_PATH . '/lib/models/SupportThread.php';

class CMDM_GroupDownloadPage extends CMDM_PostType {

    /**
     * Post type name
     */
    const POST_TYPE = 'cmdm_page';
    const ADMIN_MENU = 'CMDM_downloads_menu';

    /**
     * Name of category taxonomy
     */
    const CAT_TAXONOMY = 'cmdm_category';

    /**
     * Directory for uploads
     */
    const UPLOAD_DIR = 'cmdm';
    const SCREENSHOTS_DIR = 'screenshots';
    const OPTION_AUTHOR_LABEL = 'CMDM_author_label';
    const OPTION_TAB_DESCRIPTION = 'CMDM_tab_description';
    const OPTION_TAB_SUPPORT = 'CMDM_tab_support';
    const OPTION_FILE_MAX_SIZE = 'CMDM_max_file_size';
    const AVAILABLE_DOWNLOADS_ZIP = 'zip';
    const AVAILABLE_DOWNLOADS_EACH = 'each';
    const AVAILABLE_DOWNLOADS_BOTH = 'both';
    const USER_META_DOWNLOAD_LOG = '_cmdm_download_log';
    const ANONYMOUS_DOWNLOAD_LOG_TRANSIENT_PREFIX = 'cmdm_anondownlog_';

    public static $allowMimeMediaTypes = [
        'audio/mp3', 'audio/mpeg', 'audio/ogg', 'video/ogg', 'video/webm', 'video/webma', 'video/mp4', 'audio/x-wav'
    ];

    /**
     * Errors array
     * @var array
     */
    private $_errors = [];

    /**
     * @var CMDM_GroupDownloadPage[] singletones cache
     */
    protected static $instances = [];

    /**
     * Rewrite slug
     */
    public static $rewriteSlug = 'cmdownloads';

    /**
     * @var array meta keys mapping
     */
    public static $_meta = [
        'version' => '_version',
        'screenshots' => '_screenshots',
        'package_type' => '_package_type',
        'download_file' => '_download_file',
        'file_size' => '_file_size',
        'number_of_downloads' => '_number_of_downloads',
        'recommended' => '_recommendation',
        'categories' => '_categories',
        'ratings' => '_ratings',
        'rating_value' => '_rating_value',
        'support_notifications' => '_support_notifications',
        'visibility' => '_visibility',
        'downloadOwner' => '_download_owner',
    ];

    public static function getAdminMenu() {
        return self::ADMIN_MENU;
    }

    /**
     * Initialize model
     */
    public static function init() {
        self::$rewriteSlug = apply_filters('CMDM_controller_slug', self::$rewriteSlug);
        // register Deal post type
        $post_type_args = [
            'has_archive' => TRUE,
            'show_in_menu' => self::ADMIN_MENU,
            'rewrite' =>
                [
                    'slug' => self::$rewriteSlug,
                    'with_front' => FALSE,
                ],
            'supports' => [
                'title', 'editor', 'thumbnail', 'custom-fields',
                'revisions', 'post-formats'
            ],
            'hierarchical' => false,
            'taxonomies' => ['post_tag', CMDM_Category::TAXONOMY],
            'publicly_queryable' => true,
            'show_in_nav_menus' => true,
            'show_ui' => true,
        ];
        $plural = CMDM_Settings::getOption(CMDM_Settings::OPTION_ADDONS_TITLE);
        self::registerPostType(self::POST_TYPE, 'Download', $plural, CMDM_Settings::getOption(CMDM_Settings::OPTION_ADDONS_TITLE), $post_type_args);

        add_filter('CMDM_admin_parent_menu', [__CLASS__, 'getAdminMenu']);
        add_action('admin_menu', [__CLASS__, 'registerAdminMenu']);
        // register Categories taxonomy
        $singular = 'CMDM Category';
        $plural = 'CMDM Categories';
        $taxonomy_args = [
            'rewrite' => [
                'slug' => self::$rewriteSlug . '/categories',
                'with_front' => FALSE,
                'show_ui' => TRUE,
                'hierarchical' => false,
            ],
            'show_admin_column' => true,
        ];
        self::registerTaxonomy(CMDM_Category::TAXONOMY, [static::POST_TYPE], $singular, $plural, $taxonomy_args);

        add_action('generate_rewrite_rules', [__CLASS__, 'fixCategorySlugs']);
        CMDM_SupportThread::init();
        include_once CMDM_PATH . '/lib/classes/StorageProvider.php';
        CMDM_Shortcodes::init();
    }

    public static function fixCategorySlugs($wp_rewrite) {
        $wp_rewrite->rules = [
                self::$rewriteSlug . '/categories/([^/]+)/?$' => $wp_rewrite->index . '?post_type=' . self::POST_TYPE . '&' . CMDM_Category::TAXONOMY . '=' . $wp_rewrite->preg_index(1),
                self::$rewriteSlug . '/categories/([^/]+)/page/?([0-9]{1,})/?$' => $wp_rewrite->index . '?post_type=' . self::POST_TYPE . '&' . CMDM_Category::TAXONOMY . '=' . $wp_rewrite->preg_index(1) . '&paged=' . $wp_rewrite->preg_index(2),
            ] + $wp_rewrite->rules;
    }

    /**
     * @static
     * @param mixed $post
     * @return CMDM_GroupDownloadPage
     */
    public static function getInstance($post) {
        if (is_scalar($post)) {
            if (!empty(static::$instances[$post]))
                return static::$instances[$post];
            else if (is_numeric($post))
                $post = get_post($post);
            else
                $post = get_post(['post_name' => $post, 'post_status' => 'any']);
        }
        if (!empty($post) and is_object($post) and $post->post_type == static::POST_TYPE) {
            if (empty(static::$instances[$post->ID])) {
                static::$instances[$post->ID] = new static($post);
            }
            return static::$instances[$post->ID];
        }

        return new static(0);
    }

    public static function registerAdminMenu() {
        if (current_user_can('manage_options')) {
            add_menu_page('Downloads', 'CM Downloads', 'manage_options', self::ADMIN_MENU, '', CMDM_URL . '/views/resources/imgs/cm-download-manager-icon.png');
            add_submenu_page(self::ADMIN_MENU, 'Add New', 'Add New', 'manage_options', 'post-new.php?post_type=' . self::POST_TYPE);
            add_submenu_page(self::ADMIN_MENU, 'Categories', 'Categories', 'manage_options', 'edit-tags.php?taxonomy=' . CMDM_Category::TAXONOMY . '&amp;post_type=' . self::POST_TYPE);
            if (isset($_GET['taxonomy']) && $_GET['taxonomy'] == CMDM_Category::TAXONOMY && isset($_GET['post_type']) && $_GET['post_type'] == self::POST_TYPE) {
                add_filter('parent_file', [__CLASS__, 'getAdminMenu'], 999);
            }
        }
    }

    public static function processRequestArgs(array $args): array {

        if (empty($_REQUEST)) {
            return $args;
        }

        $request = $_REQUEST;

        global $wpdb;
        $post_type = static::POST_TYPE;
        $post__in = [];

        $meta_query = $args['meta_query'] ?? [];
        if ( !empty($request['cmdm_search']) ) {
            $args['s'] = sanitize_text_field($request['cmdm_search']);

        } elseif ( !empty($request['query']) ) {
            $args['s'] = sanitize_text_field($request['query']);
        }

        if ( !empty($request['date_from']) || !empty($request['date_to']) ) {

			$date_query = [
                'after' => '',
                'before' => '',
                'inclusive' => true,
            ];

	        $format = 'm/d/Y';

            if ($request['date_from']) {
                $date_query['after'] = date('Y-m-d', DateTime::createFromFormat($format, $request['date_from'])->getTimestamp());
            }
            if ($request['date_to']) {
                $date_query['before'] = date('Y-m-d', DateTime::createFromFormat($format, $request['date_to'])->getTimestamp());
            }

            $args['date_query'] = [$date_query];
        }

        $args['meta_query'] = $meta_query;

        return $args;
    }

    private static function processCategoryParam(array $tax_query, array $params = []) {

        if (isset($params['category']) && !empty($params['category'])) {
            $cats = $params['category'];
        }

        if (isset($params['cat']) && !empty($params['cat'])) {
            $cats = $params['cat'];
        }

        if (empty($cats)) {
            return $tax_query;
        }

        $txq = [
            'relation' => 'OR',
            'taxonomy' => 'cmdm_category',
            'terms' => $cats,
            'field' => is_numeric($cats[0]) ? 'term_id' : 'slug',
            'operator' => 'IN',
        ];

        $tax_query[] = $txq;

        return $tax_query;
    }

    function getCreatedDate() {
        return $this->post->post_date;
    }

    function getModifiedDate() {
        return $this->post->post_modified;
    }

    /**
     * Get description of download
     * @return string
     */
    public function getDescription() {
        return $this->post->post_content;
    }

    /**
     * Set description for download
     * @param string $_description
     * @param bool $save Save immediately?
     * @return CMDM_GroupDownloadPage
     */
    public function setDescription($_description, $save = false) {
        $this->post->post_content = $_description;
        if ($save)
            $this->savePost();
        return $this;
    }

    /**
     * Get excerpt
     * @return string
     */
    public function getExcerpt() {
        return $this->post->post_excerpt;
    }

    /**
     * Set excerpt
     * @param string $_excerpt
     * @param bool $save Save immediately?
     * @return CMDM_GroupDownloadPage
     */
    public function setExcerpt($_excerpt, $save = false) {
        $this->post->post_excerpt = $_excerpt;
        if ($save)
            $this->savePost();
        return $this;
    }

    /**
     * Set post status
     * @param string $_status
     * @param bool $save Save immediately?
     * @return CMDM_GroupDownloadPage
     */
    public function setStatus($_status, $save = false) {
        $this->post->post_status = $_status;
        if ($save)
            $this->savePost();
        return $this;
    }

    /**
     * Get post status
     * @return string
     */
    public function getStatus() {
        return $this->post->post_status;
    }

    /**
     * Get status label
     * @return string
     */
    public function getStatusLabel() {

        $statusLabel = '';
        $status = $this->getStatus();

        if ($status == 'publish') {
            $statusLabel = CMDM_Labels::getLocalized('download_status_publish');
        } else {
            $statusLabel = CMDM_Labels::getLocalized('download_status_draft');
        }

        $statusLabel = apply_filters('cmdm_status_label', $status, $statusLabel);

        return $statusLabel;
    }

    public function isPublished() {
        return ($this->post->post_status == 'publish');
    }

    public function canChangeStatus() {
        return $this->isEditAllowed();
    }

    static function canUserEditOthersDownloads($userId = null) {
        if (is_null($userId)) {
            $userId = get_current_user_id();
        }
        return user_can($userId, 'manage_options');
    }

    public function canDownloadBasic($userId = null) {
        if (is_null($userId))
            $userId = get_current_user_id();

        if (user_can($userId, 'manage_options'))
            return true;

        $isVisible = $this->isVisible($userId);
        $permission = CMDM_Settings::getOption(CMDM_Settings::OPTION_DOWNLOADING_PERMISSIONS);

        $canDownload = ($isVisible and self::checkPermission($permission, $userId));

        return $canDownload;
    }

    public function canDownload($userId = null) {
        return apply_filters('cmdm_download_can_download', $this->canDownloadBasic(), $this, $userId);
    }

    public static function checkPermission($permission, $userId = null) {
        if (is_null($userId))
            $userId = get_current_user_id();
        switch ($permission) {
            case CMDM_Settings::ACCESS_ALL:
                return true;
            case CMDM_Settings::ACCESS_USERS:
                return !empty($userId);
            default:
                return false;
        }
    }

    /**
     * Get author ID
     * @return int Author ID
     */
    public function getAuthorId() {
        return $this->post->post_author;
    }

    public function getAuthorName(WP_User $user = null) {
        if ($user == null) {
            $user = $this->getAuthor();
        }
        return $user->display_name;
    }

    /**
     * Get author
     * @return WP_User
     */
    public function getAuthor() {
        return get_userdata($this->getAuthorId());
    }

    public function getAuthorUploadsCount(WP_User $user = null) {
        global $wpdb;

        if ($user == null) {
            $user = $this->getAuthor();
        }
        return $this->getAuthorStats('uploads', $wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_author = %d AND post_type = %s AND post_status = 'publish'",
            $user->ID,
            self::POST_TYPE
        ));
    }

    public function getAuthorQuestionsCount(WP_User $user = null) {
        global $wpdb;

        if ($user == null) {
            $user = $this->getAuthor();
        }

        return $this->getAuthorStats('questions', $wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->comments c
    		JOIN $wpdb->posts p ON p.ID = c.comment_post_ID
    		WHERE p.post_type = %s AND c.user_id = %d AND c.comment_parent=0 AND c.comment_approved=1  AND c.comment_type='' ",
            self::POST_TYPE,
            $user->ID
        ));
    }

    public function getAuthorAnswersCount(WP_User $user = null) {
        global $wpdb;

        if ($user == null) {
            $user = $this->getAuthor();
        }

        return $this->getAuthorStats('answers', $wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->comments c
    		JOIN $wpdb->posts p ON p.ID = c.comment_post_ID
    		WHERE p.post_type = %s AND c.user_id = %d AND c.comment_parent<>0 AND c.comment_approved=1",
            self::POST_TYPE,
            $user->ID
        ));
    }

    protected function getAuthorStats($metaKey, $query) {
        global $wpdb;
        $lifetime = 300; // This is cached for 5 minutes
        $metaKey = '_cmdm_cnt_' . $metaKey;
        $user = $this->getAuthor();
        $stats = get_user_meta($user->ID, $metaKey, $single = true);
        if (empty($stats) or !isset($stats['count']) or empty($stats['time']) or $stats['time'] < time() - $lifetime) {
            $count = $wpdb->get_var($query);
            $result = update_user_meta($user->ID, $metaKey, ['count' => $count, 'time' => time()]);
            return $count;
        } else {
            return $stats['count'];
        }
    }

    public function getAuthorUrl(WP_User $user = null) {
        if ($user == null) {
            $user = $this->getAuthor();
        }

        return CMDM_BaseController::getUrl('uploader', $user->user_nicename);
    }

    /**
     * Get when item was updated
     * @param string $format
     * @return string
     */
    public function getUpdated($format = '') {
        if (empty($format))
            $format = get_option('date_format');
        return date_i18n($format, strtotime($this->post->post_modified));
    }

    public function setUpdated($date = null) {
        if (empty($date))
            $date = current_time('mysql');
        $this->post->post_modified = $date;
        $this->savePost();
        return $this;
    }

    public function getRatingsList() {
        $ratings = $this->getPostMeta(self::$_meta['ratings']);
        if (empty($ratings))
            $ratings = [];
        return $ratings;
    }

    public function addRating($userId, $rating) {
        $ratings = $this->getRatingsList();
        $ratings[] = [
            'timestamp' => time(),
            'user' => $userId,
            'rating' => $rating
        ];
        $this->savePostMeta([self::$_meta['ratings'] => $ratings]);
        $ratingStats = $this->getRatingStats();
        $this->savePostMeta([self::$_meta['rating_value'] => $ratingStats['ratingAvg']]);
    }

    public function getRatingStats() {
        $ratings = $this->getRatingsList();
        $ratingCount = count($ratings);
        $ratingAvg = 0;
        if ($ratingCount > 0) {
            $sum = 0;
            foreach ($ratings as $record) {
                $sum += intval($record['rating']);
            }
            $ratingAvg = ($sum * 1.0) / ($ratingCount * 1.0);
        }
        return [
            'ratingsCount' => $ratingCount,
            'ratingAvg' => $ratingAvg
        ];
    }

    public function getRatingInteger() {
        $rating = $this->getRatingStats();
        return round($rating['ratingAvg']);
    }

    public function getRatingValue() {
        return $this->getPostMeta(self::$_meta['rating_value']);
    }

    public function isRatingAllowed($userId) {
        if (!CMDM_Settings::getOption(CMDM_Settings::OPTION_ENABLE_RATING))
            return false;

        $allowed = true;
        $ratings = $this->getRatingsList();
        foreach ($ratings as $record) {
            if ($record['user'] == $userId) {
                $allowed = false;
                break;
            }
        }
        return $allowed;
    }

    public function getVersion() {
        return $this->getPostMeta(self::$_meta['version']);
    }

    public function setVersion($_version) {
        $this->savePostMeta([self::$_meta['version'] => $_version]);
        return $this;
    }

    public function getScreenshots() {
        return CMDM_Screenshot::selectForDownload($this->getId(), CMDM_Screenshot::R_OBJECT);
    }

    public function getScreenshotsIds() {
        return CMDM_Screenshot::selectForDownload($this->getId(), CMDM_Screenshot::R_ID);
    }

    public function setScreenshots($ids) {

        $StorageService = CMDM_StorageProvider::getInstance();


        global $wpdb;

        if (!is_array($ids))
            $ids = array_filter(explode(',', stripslashes($ids)));
        if (empty($ids))
            $ids = [];
        if (is_array($ids)) {
            $oldIds = $this->getScreenshotsIds();
            $toRemove = array_diff($oldIds, $ids);
            $toAdd = array_diff($ids, $oldIds);
            if (!empty($toRemove)) {
                foreach ($toRemove as $id) {
                    wp_delete_attachment($id, true);
                }
            }
            if (!empty($toAdd)) {
                foreach ($toAdd as $id) {
                    if ($screenshot = CMDM_Screenshot::getById($id) and $screenshot->getDownloadId() != $this->getId()) {
                        //$screenshot->attach($this->getId());
                        $StorageService->saveScreenshots($screenshot, $this);
                    }
                }
            }

            foreach ($ids as $i => $id) {
                $wpdb->query($wpdb->prepare("UPDATE $wpdb->posts SET menu_order = %d WHERE ID = %d", $i, $id));
            }
        }
        return $this;
    }

    /**
     * Get Ids of the attachments to edit.
     *
     * @return array|null
     */
    public function getAttachmentsIds() {
        if ($this->getPackageType() == 'file') {
            $result = CMDM_DownloadFile::selectForDownload($this);
            if (empty($result)) {
                $this->rebuildAttachments();
                return CMDM_DownloadFile::selectForDownload($this);
            } else {
                return $result;
            }
        } else {
            return [];
        }
    }

    /**
     * Get attachments objects.
     *
     * @return array|null
     */
    public function getAttachments() {
        if ($this->getPackageType() == 'file') {
            $result = CMDM_DownloadFile::selectForDownload($this, CMDM_DownloadFile::R_OBJECT);
            if (empty($result)) {
                $this->rebuildAttachments();
                return CMDM_DownloadFile::selectForDownload($this, CMDM_DownloadFile::R_OBJECT);
            } else {
                return $result;
            }
        } else {
            return [];
        }
    }

    public function rebuildAttachments() {
        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $data = (array)$this->post;
        unset($data['ID']);
        $data['post_type'] = CMDM_DownloadFile::POST_TYPE;
        $data['post_status'] = CMDM_DownloadFile::STATUS_INHERIT;
        $data['post_parent'] = $this->post->ID;
        $metaFileName = $this->getPostMeta('_download_file');
        $data['post_title'] = basename($metaFileName);
        $data['guid'] = $this->getUploadPath() . $metaFileName;
        $filePath = $data['guid'];
        $data['post_content'] = '';

        // Insert the attachment.
        $attach_id = wp_insert_attachment((object)$data, $filePath, $this->post->ID);
        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata($attach_id, $filePath);
        wp_update_attachment_metadata($attach_id, $attach_data);
    }

    public function setDownloadFile($_download_file) {
        global $wpdb;

        if (is_string($_download_file))
            $_download_file = (array)explode(',', $_download_file);
        if (is_array($_download_file))
            $attachmentsIds = array_filter($_download_file);
        if (empty($attachmentsIds)) {
            throw new Exception('Missing files.');
        }
        //not created directory
        $oldAttachmentsIds = $this->getAttachmentsIds();
        //created directory
        $attachmentsToRemove = array_diff($oldAttachmentsIds, $attachmentsIds);
        $attachmentsToAdd = array_diff($attachmentsIds, $oldAttachmentsIds);
        $differentAttachments = ($attachmentsToRemove or $attachmentsToAdd);

        // Get attachments' objects
        $attachments = [];
        foreach ($attachmentsIds as $id) {
            $attachments[] = CMDM_DownloadFile::getById($id);
        }
        // Attach to post
        $downloadId = $this->getId();
        $attachments = array_filter($attachments);


        $StorageService = CMDM_StorageProvider::getInstance();
        $StorageService->save($downloadId, $attachments);

        if (empty($attachments)) {
            throw new Exception('Missing attachments.');
        }

        // Remove old attachments
        foreach ($attachmentsToRemove as $id) {
            wp_delete_attachment($id, $force = true);
        }
        $attachment = reset($attachments);
        $this->savePostMeta(['_download_file' => basename($attachment->getPath())]);
        $this->setMimeType($attachment->getMimeType());
        $this->setFileSize($attachment->getSize());
        $this->savePostMeta([
            self::$_meta['package_type'] => 'file',
        ]);

        /* @note: removing remaining files from tmp */
        $tmpDir = CMDM_DownloadFile::getUploadDir(null);
        if (!file_exists($tmpDir) || is_file($tmpDir) || empty($attachments)) {
            return $this;
        }

        foreach ($attachments as $attachment) {

            $metaDataFilesName = get_post_meta($attachment->getId(), '_wp_attachment_metadata', true);

            if (empty($metaDataFilesName) || !isset($metaDataFilesName['sizes']) || empty($metaDataFilesName['sizes'])) {
                continue;
            }

            foreach ($metaDataFilesName['sizes'] as $oneFile) {
                $file = $tmpDir . $oneFile['file'];
                if (file_exists($file) && is_file($file)) {
                    unlink($file);
                }
            }
        }

        return $this;
    }

    public function getFilePath() {
        return trailingslashit($this->getUploadPath()) . $this->getDownloadFile();
    }

    public function getUploadPath() {
        return CMDM_Attachment::getUploadDir($this->getId());
    }

    protected function addError($errorMsg) {
        $this->_errors[] = $errorMsg;
    }

    protected function getErrors() {
        if ($this->_errors) {
            return $this->_errors;
        }
        return null;
    }

    public function getDownloadFormUrl() {
        $result = CMDM_CmdownloadController::getUrl('cmdownload', 'get');
        if ($this->isPackageFile()) {
            $result .= 'file/';
            $result .= urlencode(str_replace('.php', '-php', $this->getFileName()));
        }
        return $result;
    }

    public function getPermalink() {
        return get_permalink($this->getId());
    }

    public function getEditUrl() {
        return CMDM_CmdownloadController::getUrl('cmdownload', 'edit', ['id' => $this->getId()]);
    }

    public function getDeleteUrl() {
        return CMDM_CmdownloadController::getUrl('cmdownload', 'del', [
            'id' => $this->getId(),
            'nonce' => wp_create_nonce('cmdm_download_delete'),
        ]);
    }

    public function getPackageType() {
        $value = $this->getPostMeta(self::$_meta['package_type']);
        if (empty($value)) {
            $value = 'file';
        }
        return $value;
    }

    public function isOwnerNotified() {
        return $this->getPostMeta(self::$_meta['support_notifications']);
    }

    public function setOwnerNotified($notifications = false) {
        $this->savePostMeta([self::$_meta['support_notifications'] => (bool)$notifications]);
        return $this;
    }

    public function setMimeType($mime) {
        global $wpdb;
        $this->post->post_mime_type = $mime;
        $wpdb->update($wpdb->posts, ['post_mime_type' => $mime], ['ID' => $this->ID]);
        return $this;
    }

    public function getMimeType() {

        if ($this->getPackageType() == 'file') {

            $mimeType = $this->post->post_mime_type;

            if (!$mimeType) {
                $mimeType = get_post_mime_type($this->ID);
            }

            if (!$mimeType) {
                $mimeType = CMDM_DownloadFile::recognizeMimeType($this->getFilePath());
            }
        }

        if (empty($mimeType))
            $mimeType = 'application/octet-stream';
        return $mimeType;
    }

    public function getFileSize() {
        return $this->getPostMeta(self::$_meta['file_size']);
    }

    public function setFileSize($_file_size) {
        $this->savePostMeta([self::$_meta['file_size'] => $_file_size]);
        return $this;
    }

    public function isRecommended() {
        return $this->getPostMeta(self::$_meta['recommended']) == 1;
    }

    public function setRecommended($_recommended) {
        $this->savePostMeta([self::$_meta['recommended'] => (int)$_recommended]);
        return $this;
    }

    public function addNumberOfDownloads() {
        $oldNumber = $this->getNumberOfDownloads();
        $oldNumber = empty($oldNumber) ? 0 : $oldNumber;
        $this->savePostMeta([self::$_meta['number_of_downloads'] => $oldNumber + 1]);
        return $this;
    }

    public function getNumberOfDownloads() {
        return intval($this->getPostMeta(self::$_meta['number_of_downloads']));
    }

    public function getNumberOfDownloadsColumn() {
        $counter = $this->getNumberOfDownloads();
        return $counter;
    }

    public function setNumberOfDownloads($number) {
        $this->savePostMeta([self::$_meta['number_of_downloads'] => (int)$number]);
    }

    public function isEditAllowed($userId = null) {
        if (is_null($userId)) {
            $userId = get_current_user_id();
        }
        return (user_can($userId, 'manage_options') || ($this->getAuthorId() == $userId));
    }

    public function isDeleteAllowed($userId = null) {
        if (is_null($userId))
            $userId = get_current_user_id();
        return (user_can($userId,'manage_options') || $this->getAuthorId() == $userId);
    }

    public function setCategories($categories = []) {
        if (!is_array($categories))
            $categories = [$categories];
        $categories = array_map('intval', $categories);
        wp_set_object_terms($this->getId(), $categories, CMDM_Category::TAXONOMY);
        return $this;
    }

    public function getCategoriesIds() {
        $result = [];
        $terms = get_the_terms($this->getId(), CMDM_Category::TAXONOMY);
        if ($terms && !is_wp_error($terms)) {  // Condition added since uncategorized downloads forced error at 'foreach' point
            foreach ($terms as $term) {
                $result[] = $term->term_id;
            }
        }

        return $result;
    }

    public function getCategories() {
        return array_map(['CMDM_Category', 'getInstance'], $this->getCategoriesIds());
    }

    public function getCategoriesList() {
        $terms = get_the_terms($this->getId(), CMDM_Category::TAXONOMY);
        $result = [];
        if ($terms && !is_wp_error($terms)) {  // Condition added since uncategorized downloads forced error at 'foreach' point
            foreach ($terms as $term) {
                $result[$term->term_id] = $term->name;
            }
        }

        return $result;
    }

    public static function getScreenshotsPath() {
        $uploadDir = wp_upload_dir();
        $baseDir = $uploadDir['basedir'] . '/' . self::UPLOAD_DIR . '/';
        $dir = $baseDir . self::SCREENSHOTS_DIR . '/';
        wp_mkdir_p($dir);
        return $dir;
    }

    public static function saveScreenshot($file) {
        $pathinfo = pathinfo($file['name']);
        $name = strtolower((time() . uniqid()) . '.' . $pathinfo['extension']);
        $target = self::getScreenshotsPath() . $name;
        if (@move_uploaded_file($file['tmp_name'], $target)) {
            return $name;
        } else
            throw new Exception('File could not be saved.');
    }

    public static function isIeBrowser() {
        $ie = false;
        $ieMatches = [];
        preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $ieMatches);
        if (count($ieMatches) < 2) {
            preg_match('/Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*)/', $_SERVER['HTTP_USER_AGENT'], $ieMatches);
        }
        if (count($ieMatches) > 1) {
            $ie = true;
        }

        return $ie;
    }

    /**
     * New download method
     * @throws Exception
     * @author Marcin
     * @since 1.6.0
     */
    public function download($file_names = null) {
        do_action('cmdm_download_before', $this);

        if (!$this->canDownload()) {
            throw new Exception(CMDM_Labels::getLocalized('download_not_permitted'));
        }

        if ($this->getPackageType() == 'file') {
            error_reporting(0);
            $level = ob_get_level();
            while ($level > 0) {
                @ob_end_clean();
                $level--;
            }
            if (headers_sent($headersFile, $headersLine))
                die('Headers file:' . $headersFile . ' on line: ' . $headersLine);

            try {

                $filepath = $this->getFilePath();

                if (file_exists($filepath) and is_file($filepath)) {
                    if ($file_names == null) {
                        $file_names = $this->getFileName();
                    }
                    do_action('cmdm_before_add_downloads_number', $this);
                    $this->addNumberOfDownloads();
                    do_action('cmdm_after_add_downloads_number', $this);
                    $fileSize = $this->getFileSize();

                    $ext = pathinfo($filepath, PATHINFO_EXTENSION);
                    if (!empty($ext))
                        $ext = '.' . $ext;
                    $mimeType = $this->getMimeType();
                    if (strpos($ext, 'mp3'))
                        $mimeType = 'application/octet-stream';

                    set_time_limit(3600 * 24);
                    header("Pragma: public");
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Cache-Control: private", false); // required for certain browsers
                    if (CMDM_Settings::getOption(CMDM_Settings::OPTION_FORCE_BROWSER_DOWNLOAD_ENABLED)) {
                        $mimeType = 'application/octet-stream';
                        header("Content-type: " . $mimeType);
                        header('Content-Description: File Transfer');
                        if (self::isIeBrowser()) {
                            header("Content-Disposition: attachment; filename=\"" .
                                mb_convert_encoding($this->getFileName(), "ISO-8859-2", "UTF-8") . "\";");
                        } else {
                            header("Content-Disposition: attachment; filename=\"" . $this->getFileName() . "\";");
                        }
                        header("Content-Transfer-Encoding: binary");
                        header("Content-Length: " . $fileSize);
                    } else {
                        header("Content-type: " . (empty($mimeType) ? 'application/octet-stream' : $mimeType));
                    }
                    readfile($filepath);
                } else {
                    throw new Exception('File ' . $filepath . ' not found in the filesystem.');
                }

                exit;
            } catch (Exception $e) {
                die($e->getMessage());
            }

            exit;
        }
        exit;
    }

    public function getFileName() {
        if ($this->isPackageFile()) {
            $ext = explode('.', $this->getFilePath());
            return CMDM_DownloadFile::sanitizeFileName($this->getTitle(), false) . '.' . array_pop($ext);
        }
    }

    public function isPackageFile() {
        return ($this->getPackageType() == 'file');
    }

    public function getDownloadFile() {
        return $this->getPostMeta('_download_file');
    }

    /**
     * Create new download instance.
     *
     * @param array $data
     * @throws Exception
     */
    public static function newInstance($data = []) {

        $postData = [
            'post_status' => 'publish',
            'post_type' => self::POST_TYPE,
            'post_title' => $data['title'],
            'post_name' => sanitize_title_with_dashes($data['title']),
            'post_author' => apply_filters('cmdm_download_add_post_author', get_current_user_id()),
        ];

        do_action('cmdm_upload_before', $postData, $data);

        $id = wp_insert_post($postData);

        if ($id instanceof WP_Error) {
            return $id->get_error_message();
        } else {
            $instance = self::getInstance($id);
            if (isset($data['description']))
                $instance->setDescription($data['description'], true);
            if (isset($data['version']))
                $instance->setVersion($data['version']);
            if (isset($data['numberOfDownloads']))
                $instance->setNumberOfDownloads(intval($data['numberOfDownloads']));
            else
                $instance->setNumberOfDownloads(0);
            $instance->setCategories($data['categories']);
            $instance->savePostMeta([self::$_meta['rating_value'] => 0]);
            $instance->savePostMeta([self::$_meta['package_type'] => $data['packageType']]);
            if ($data['packageType'] == 'file' && isset($data['package']) && !empty($data['package'])) {
                $instance->setDownloadFile($data['package']);
            }

            if (isset($data['screenshots']))
                $instance->setScreenshots($data['screenshots']);
            $instance->setRecommended(isset($data['admin_supported']) ? $data['admin_supported'] : 'false')
                ->setOwnerNotified(!empty($data['support_notifications']));

            $instance->setThumbnail(isset($data['thumbnail']) ? $data['thumbnail'] : null);

            $errors = $instance->getErrors();
            if (empty($errors)) {
                $instance->setUpdated()->savePost();

                do_action('cmdm_upload_after', $instance, $data);
                do_action('cmdm_download_update_success', $instance, $data);

                return $instance;
            } else {
                return implode('<br/>', $errors);
            }
        }
    }

    public function update($data = []) {

        if (isset($data['title']))
            $this->setTitle($data['title']);
        if (isset($data['description']))
            $this->setDescription($data['description']);
        $this->savePost();

        if (isset($data['version']))
            $this->setVersion($data['version']);

        if (isset($data['categories'])) {
            $this->setCategories($data['categories']);
        } else {
            $this->setCategories([]);
        }

        if (isset($data['type']))
            $this->setType($data['type']);

        if (!$this->getRatingValue()) {
            $this->savePostMeta([self::$_meta['rating_value'] => 0]);
        }
        $this->savePostMeta([self::$_meta['package_type'] => $data['packageType']]);
        if ($data['packageType'] == 'file' && isset($data['package']) && !empty($data['package'])) {
            $files = $data['package'];
            $this->setDownloadFile($files);
        }

        if (isset($data['screenshots']))
            $this->setScreenshots($data['screenshots']);
        if (isset($data['thumbnail']))
            $this->setThumbnail($data['thumbnail']);

        if (current_user_can('manage_options')) {
            if (isset($data['admin_supported']))
                $this->setRecommended($data['admin_supported']);
            if (isset($data['numberOfDownloads']))
                $this->setNumberOfDownloads($data['numberOfDownloads']);
        }
        if (isset($data['support_notifications']))
            $this->setOwnerNotified($data['support_notifications']);
        else
            $this->setOwnerNotified(false);

        $errors = $this->getErrors();
        if (empty($errors)) {
            $this->setUpdated();
            do_action('cmdm_download_update_success', $this, $data);
            return $this;
        } else {
            return implode('<br/>', $errors);
        }
    }

    public function delete() {
        return wp_delete_post($this->getId(), true) !== false;
    }

    public static function getTotalCount() {
        $atts = ['limit' => -1, 'sort' => 'ID'];
        $query_args = [];
        $query = self::createQuery($atts, $query_args);
        return count($query->posts);
    }

    static function createQuery($atts, $queryArgs = []) {
        $queryArgs['post_type'] = self::POST_TYPE;
        $queryArgs['post_status'] = 'publish';
        $queryArgs['fields'] = 'ids';

        if ($atts['limit'] > 0) {
            $queryArgs['posts_per_page'] = $atts['limit'];
            $queryArgs['paged'] = $atts['page'];
        } else {
            $queryArgs['nopaging'] = true;
        }

        if (!empty($atts['author_id'])) {
            $queryArgs['author'] = $atts['author_id'];
        } elseif (!empty($atts['author'])) {
            $queryArgs['author'] = $atts['author'];
        }

        if (!empty($atts['query'])) {
            $queryArgs['s'] = $atts['query'];
        }

        if (isset($atts['month'])) {
            $queryArgs['monthnum'] = $atts['month'];
        }

        if (isset($atts['year'])) {
            $queryArgs['year'] = $atts['year'];
        }

        $relation = 'and';

        if (!empty($atts['category'])) {
            if (!is_array($atts['category'])) {
                $categories = explode(',', $atts['category']);
            } else {
                $categories = $atts['category'];
            }
            $categories = array_map(function ($cat) {
                return trim($cat);
            }, $categories);

            $field_type = is_object(get_term_by('slug', $categories[0], CMDM_Category::TAXONOMY)) ? 'slug' : 'term_id';

            $queryArgs['tax_query'][0] = [
                'relation' => $relation,
                [
                    'taxonomy' => CMDM_Category::TAXONOMY,
                    'field' => $field_type,
                    'terms' => $categories,
                    'include_children' => true,
                ],
            ];
        }

        $sort = 'newest';
        $queryArgs = array_merge($queryArgs, self::getQueryOrderArgs($sort));

        $queryArgs = apply_filters('cmdm_before_query_args', $queryArgs);

        $query = new WP_Query($queryArgs);
        self::filterVisibility($query);

        return $query;
    }

    public static function getCategoriesTreeArray($parentId = null, $depth = 0, $onlyVisible = true) {
        $terms = get_terms(CMDM_Category::TAXONOMY, [
            'orderby' => 'name',
            'hide_empty' => 0,
            'parent' => $parentId
        ]);
        $results = [];
        foreach ($terms as $term) {
            if (!$onlyVisible or ($category = CMDM_Category::getInstance($term) and ($category->isVisible() or $category->canUpload()))) {
                $term->children = self::getCategoriesTreeArray($term->term_id, $depth + 1, $onlyVisible);
                $results[] = $term;
            }
        }
        return $results;
    }

    public static function getDownloadsQuery($atts = []) {
        $the_query = false;

        if (is_user_logged_in()) {
            $user = wp_get_current_user();

            if (!$user) {
                return false;
            }

            $isAdministrator = false;
            if (in_array('administrator', $user->roles)) {
                $isAdministrator = true;
            }

			// for Admins only
            if (current_user_can('administrator') || $isAdministrator) {
                return CMDM_GroupDownloadPage::getAllDownloadsQuery($atts);
            }

			// for other users
	        return CMDM_GroupDownloadPage::getDownloadsQueryByUser($user->ID, $atts);
        }

        // for guests
        $option_view_permissions = CMDM_Settings::getOption(CMDM_Settings::OPTION_VIEWING_PERMISSIONS);

        if ($option_view_permissions === CMDM_Settings::ACCESS_ALL) {
            $the_query = CMDM_GroupDownloadPage::getDownloadsQueryForGuest($atts);
        }

        return $the_query;
    }

    public static function getDownloadsByUserQuery($userId = null, $limit = null, $page = null) {

        if (empty($userId)) {
            $userId = get_current_user_id();
        }

        $args = [
            'post_type' => self::POST_TYPE,
            'fields' => 'ids',
            'post_status' => ['publish','draft'],
            'order' => 'DESC',
            'posts_per_page' => 20
        ];

        $roles = get_user_by('id', $userId)->roles;
        if (!in_array('administrator', $roles)) {
            $args['author'] = $userId;
        }

        if (!get_user_by('id', $userId)->roles) {
            if (!empty($limit)) {
                $args['posts_per_page'] = $limit;
                $args['paged'] = 1;
            }
        }

        if (!empty($page)) {
            $args['paged'] = $page;
        }

        return $args;
    }

    public static function getDownloadsQueryByUser($userId, $params = []) {
        if (empty($userId)) {
            return [];
        }

        $limit = $params['limit'] ?? -1;
        $page = $params['page'] ?? 1;

        if (is_null($limit)) {
            $limit = -1;
        }

        $args = self::prepareDownloadsArgsByUserParams($userId, $params);

        if (!empty($args)) {
            $args = self::processRequestArgs($args);
            $args = self::processRequestArgsSort($args, $params['sort'] ?? '');
        }

	    $args = apply_filters('cmdm_before_query_args', $args);

        return new WP_Query($args);
    }

    public static function getDownloadsQueryForGuest($params = []) {
        $limit = $params['limit'] ?? -1;
        $page = $params['page'] ?? 1;

        if (is_null($limit)) {
            $limit = -1;
        }

        $args = self::prepareDownloadsForGuestQuery($params);

        if (!empty($args)) {
            $tax_query = [];
            $tax_query = self::processCategoryParam($tax_query, $params);

            if (!empty($tax_query)) {
                $tax_query['relation'] = 'AND';
                $args['tax_query'] = $tax_query;
            }
            $args = self::processRequestArgs($args);
            $args = self::processRequestArgsSort($args, $params['sort'] ?? '');
            $args = apply_filters('cmdm_before_query_args', $args);
        }

        return new WP_Query($args);
    }

    public static function getDownloadsByCategory($categoryId, $page = 1, $perPage = 10) {
        $category = CMDM_Category::getInstance($categoryId);
        $recursive = true;
        return $category->getPosts($onlyVisible = true, $recursive, $perPage, $page);
    }

    public static function preparQueryArgsForFilterVisibility($args, $visibleForAdmin = true) {

        $currentUserId = get_current_user_id();
        $global_permissions = CMDM_Settings::getOption(CMDM_Settings::OPTION_VIEWING_PERMISSIONS); // Permissions set in CM downloads settings

        $post__in = [];

        switch ($global_permissions) {
            case CMDM_Settings::ACCESS_ALL:
            {
                break;
            }
            case CMDM_Settings::ACCESS_USERS:
            {
                if ($currentUserId <= 0) {
                    $post__in = [-1];
                }
                break;
            }
            case 'roles':
            {
                if ($currentUserId <= 0) {
                    $post__in = [-1];
                    break;
                }
                $userRoles = CMDM_User::getRoles($currentUserId);
                $viewing_roles = CMDM_Settings::getOption(CMDM_Settings::OPTION_VIEWING_ROLES);

                if (!is_array($userRoles) || empty($userRoles)) {
                    $post__in = [-1];
                    break;
                }
                if (!$viewing_roles || empty($viewing_roles)) {
                    break;
                }

                $array_intersect = array_intersect($userRoles, $viewing_roles);
                if (empty($array_intersect)) {
                    $post__in = [-1];
                    break;
                }
                break;
            }
            default:
            {
                break;
            }
        }

        if (!empty($post__in)) {
            $args['post__in'] = $post__in;
        }

        return $args;
    }

    /**
     * Filters the visibility of the download items
     * @param WP_Query $query
     * @return null - $query is passed by reference!
     */
    public static function filterVisibility($query, $visibleForAdmin = true) {
        if (current_user_can('manage_options')) {
            return $query;
        }

        if ($query->get('cmdm_visibility_filter_active')) {
            return $query;
        } else {
            $query->set('cmdm_visibility_filter_active', 1);
        }

        $query_args = self::preparQueryArgsForFilterVisibility([], $visibleForAdmin = true);
        foreach ($query_args as $key => $arg) {
            $query->set($key, $arg);
        }
    }

    public static function processImage($img, $size) {
        $hash = md5($img . $size);
        try {
            $imgPath = self::getScreenshotsPath() . '/' . $img;
            $imageInfo = getimagesize($imgPath);
            $originalWidth = $imageInfo[0];
            $originalHeight = $imageInfo[1];
            list($filetype, $ext) = explode('/', $imageInfo['mime']);
            $cacheDir = self::getScreenshotsPath() . '/cache/';
            if (!file_exists($cacheDir))
                wp_mkdir_p($cacheDir);
            if (!file_exists($cacheDir . $hash)) {
                $imgPath = self::getScreenshotsPath() . '/' . $img;
                switch ($ext) {
                    case 'gif':
                        $createFunc = 'imagecreatefromgif';
                        $targetFunc = 'imagepng';
                        $ext = 'png';
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
                $originalImg = $createFunc($imgPath);
                list($targetWidth, $targetHeight) = explode('x', $size);
                $originalRatio = ($originalWidth * 1.0) / ($originalHeight * 1.0);
                $targetRatio = ($targetWidth * 1.0) / ($targetHeight * 1.0);
                if ($targetRatio > $originalRatio) {//original img is higher, do not fit to width
                    $targetWidth = $originalRatio * $targetHeight;
                } elseif ($targetRatio < $originalRatio) {
                    $targetHeight = $targetWidth / $originalRatio;
                }

                $left = $top = 0;

                $dst = imagecreatetruecolor($targetWidth, $targetHeight);
                $white = imagecolorallocate($dst, 255, 255, 255);
                imagefilledrectangle($dst, 0, 0, $targetWidth, $targetHeight, $white);
                imagecopyresampled($dst, $originalImg, $left, $top, 0, 0, $targetWidth, $targetHeight, $originalWidth, $originalHeight);
                imagedestroy($originalImg);
                $targetFunc($dst, $cacheDir . $hash);
                imagedestroy($dst);
            }
            header('Content-type: ' . implode('/', [$filetype, $ext]));
            ob_clean();
            flush();
            readfile($cacheDir . $hash);
            exit;
        } catch (Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    public static function canShowDashboard($userId = null) {
        if (is_null($userId)) {
            $userId = get_current_user_id();
        }
        return empty($userId) ? false : self::canUpload($userId);
    }

    public static function canUpload($userId = null) {
        if (current_user_can('manage_options'))
            return true;

        if (is_null($userId))
            $userId = get_current_user_id();

        if (!$userId) {
            $result = false;
        } else {
            $permissions = CMDM_Settings::getOption(CMDM_Settings::OPTION_ADDING_PERMISSIONS);
            if($permissions == 'roles'){
                $allowed_roles = CMDM_Settings::getOption(CMDM_Settings::OPTION_ADDING_ROLES);
                if (user_can($userId, 'manage_options')) {
                    $result = true;
                } elseif (empty($allowed_roles)) {
                    $result = true;
                } else {
                    $userRoles = CMDM_User::getRoles($userId);
                    $inner = array_intersect($userRoles, $allowed_roles);
                    $result = !empty($inner);
                }
            } else {
                $result = true;
            }
        }

        return apply_filters('cmdm_can_upload', $result, $userId);
    }

    public function getAuthorBlock() {
        if ($user = $this->getAuthor()) {
            $result = $user->display_name;
        } else {
            $user = null;
            $result = CMDM::__('unknown');
        }

        return apply_filters('cmdm_author_block', $result, $user, $this);
    }

    /**
     * Check whether given user can see given download.
     *
     * @param string $userId If NULL - get current user ID
     * @param string $download_id If NULL - get this object's download ID
     * @return boolean
     */
    public function isVisible($userId = null) {

        if (is_null($userId)) {
            $userId = get_current_user_id();
        }

        $download_id = $this->getId();

        if (is_null($download_id)) {
            error_log('This should not happen!');
            return false;
        }

        if (current_user_can('manage_options') || user_can($userId, 'manage_options'))
            return true;

        $global_permissions = CMDM_Settings::getOption(CMDM_Settings::OPTION_VIEWING_PERMISSIONS); // Permissions set in CM downloads settings
        $globalResult = false;

        $visibility = $global_permissions;
        $allowed_roles = CMDM_Settings::getOption(CMDM_Settings::OPTION_VIEWING_ROLES);

        if ($visibility == 'all') {
            if ($userId != get_current_user_id()) {
                $globalResult = false;
            } else {
                $globalResult = true;
            }
        } elseif ($userId <= 0) {
            $globalResult = false;
        } elseif ($this->getAuthorId() == $userId) {
            $globalResult = true;
        } elseif ($visibility == CMDM_Settings::ACCESS_USERS) {
            $globalResult = true;
        } elseif ($visibility == 'roles') {
            if (user_can($userId, 'manage_options')) {
                $globalResult = true;
            } elseif (empty($allowed_roles)) {
                $globalResult = true;
            } else {
                $userRoles = CMDM_User::getRoles($userId);
                $inner = array_intersect($userRoles, $allowed_roles);
                $globalResult = !empty($inner);
            }
        } elseif (!empty($visibility)) {
            $globalResult = user_can($userId, $visibility);
        }

        return apply_filters('cmdm_filter_is_download_visible', $globalResult, $userId, $this->getId(), $this);
    }

    public function getDownloadOwner() {
        return get_post($this->getId())->post_author;
    }

    public static function getDownloadsPermalink() {
        return CMDM_get_url('cmdownloads');
    }

    public function setThumbnail($screenshot) {
        if (is_object($screenshot) and $screenshot instanceof CMDM_Screenshot) {
            $screenshot = $screenshot->getId();
        }
        if (empty($screenshot)) {
            delete_post_meta($this->getId(), '_thumbnail_id');
        } else {
            set_post_thumbnail($this->getId(), $screenshot);
        }
        return $this;
    }

    public function getThumbnailId() {
        return get_post_thumbnail_id($this->getId());
    }

    public function getThumbnailUrl($size = CMDM_Attachment::IMAGE_SIZE_THUMB) {
        if ($thumb = $this->getThumbnail()) {
            return $thumb->getUrl($size);
        }
    }

    public function getThumbnail() {
        if ($id = $this->getThumbnailId()) {
            return CMDM_Screenshot::getById($id);
        }
    }

    /**
     * Saninitize array, convert types and filter keys
     * @param array $arr array to be sanitized
     * @param array $descriptors array of descriptors for <code>$arr</code> fields
     * @return array
     * @throws InvalidArgumentException
     */
    public static function sanitize_array(array $arr, array $descriptors)
    {
        static $mappers = null;

        if ( $mappers === null ) {
            $mappers = [
                'integer' => 'intval',
                'int' => 'intval',
                'double' => 'doubleval',
                'float' => 'doubleval',
                'string' => 'strval',
                'trim' => 'trim',
                'boolean' => 'boolval',
                'bool' => 'boolval'
            ];
        }

        $result = [];

        foreach ($descriptors as $key => $desc) {
            list($type, $default) = is_array($desc) ? $desc : [(string)$desc, null];

            if ( $type !== '*' && !array_key_exists($type, $mappers) ) {
                throw new InvalidArgumentException();
            }

            if ( array_key_exists($key, $arr) ) {
                if ( $type === '*' ) {
                    $result[$key] = $arr[$key];
                } else {
                    $result[$key] = call_user_func($mappers[$type], $arr[$key]);
                }
            } else {
                $result[$key] = $default;
            }
        }

        return $result;
    }

    public function getBacklinkUrl() {
        $url = CMDM::getReferer();
        if (CMDM_Settings::getOption(CMDM_Settings::OPTION_INDEX_PAGE_DISABLED) && CMDM_Settings::getOption(CMDM_Settings::OPTION_INDEX_PAGE_CUSTOM_PAGE_ID) != 0){
            return $url;
        }
        $home = get_post_type_archive_link(CMDM_GroupDownloadPage::POST_TYPE);
        $homePath = parse_url($home, PHP_URL_PATH);
        $categoriesPath = $homePath . 'categories/';
        if (substr(parse_url($url, PHP_URL_PATH), 0, strlen($categoriesPath)) == $categoriesPath || $url == $home) {
            return $url;
        } else {
            $categories = $this->getCategories();
            if (count($categories) == 1) {
                return $categories[0]->getPermalink();
            } else {
                return $home;
            }
        }
    }

    static function registerCustomOrder(WP_Query $query, $sort) {
        if($sort == 'newest') {
            $query->set('orderby', 'post_date');
            $query->set('order', 'DESC');
        }
    }

    static function processRequestArgsSort($args, $sort) {
        if($sort == 'newest') {
                $args['orderby'] = 'post_date';
                $args['order'] = 'DESC';
        }

        return $args;
    }

    static function getQueryOrderArgs($sort) {
        $vars = [];
        $vars['order'] = 'DESC';
        if($sort == 'newest') {
            $vars['orderby'] = 'post_date';
        }
        return $vars;
    }

    public static function getAllowedDownloadsIds($user_id, $atts = []) {

		if (!is_user_logged_in()) {
			return [];
		}

        $args = [
            'post_type' => self::POST_TYPE,
            'fields' => 'ids',
            'post_status' => ['publish'],
            'order' => 'DESC',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'OR',
                [
                    'key' => 'cmdm_allowed_user_id',
                    'value' => $user_id
                ],
                [
                    'key' => '_visibility',
                    'value' => ['all', 'loggedin'],
                    'compare' => 'IN',
                ],
                [
                    'key' => '_visibility',
                    'compare' => 'NOT EXISTS',
                ]
            ]
        ];


        if (isset($atts['monthnum'])) {
            $args['monthnum'] = $atts['monthnum'];
        }

        if (isset($atts['year'])) {
            $args['year'] = $atts['year'];
        }

        if ( CMDM_Settings::getOption(CMDM_Settings::OPTION_VIEWING_PERMISSIONS) == 'loggedin') {
            return get_posts($args);
        }

	    if ( CMDM_Settings::getOption(CMDM_Settings::OPTION_VIEWING_PERMISSIONS) != 'user') {
			return [];
	    }

        return get_posts($args);
    }

    public static function getFilteredDownloadsIds($atts = []) {

        $args = [
            'post_type' => self::POST_TYPE,
            'fields' => 'ids',
            'post_status' => $atts['post_status'] ?? ['publish'],
            'order' => 'DESC',
            'posts_per_page' => -1,
            'meta_query' => $atts['meta_query'] ?? [],
            'tax_query' => $atts['tax_query'] ?? [],
        ];

        if (isset($atts['monthnum'])) {
            $args['monthnum'] = $atts['monthnum'];
        }

        if (isset($atts['year'])) {
            $args['year'] = $atts['year'];
        }

        $args = self::preparQueryArgsForFilterVisibility($args);

        return get_posts($args);
    }

    public static function prepareDownloadsArgsByUserParams($userId = null, $params = []) {

        $limit = intval($params['limit'] ?? -1);
        $page = intval($params['page'] ?? 1);

        if (empty($userId)) {
            $userId = get_current_user_id();
        }

        $post_status = ['publish'];
        if ( !empty($params['post_status']) ) {
            $post_status = $params['post_status'];
        }

        $args = [
            'post_type' => self::POST_TYPE,
            'author' => $userId,
            'fields' => 'ids',
            'post_status' => $post_status,
            'order' => 'DESC',
            'posts_per_page' => -1,
        ];


        $filter_atts = [];

        if ( !empty($params['monthnum']) ) {
            $filter_atts['monthnum'] = $params['monthnum'];
        }

        if ( !empty($params['year']) ) {
            $filter_atts['year'] = $params['year'];
        }

        if (!empty($filter_atts)) {
            $args = array_merge($args, $filter_atts);
        }

        $_query = new WP_Query($args);
        $author_post_ids = $_query->get_posts();

        $allowed_downloads_ids = self::getAllowedDownloadsIds($userId, $filter_atts);
        $all_allowed_downloads_ids = array_unique(array_merge($author_post_ids, $allowed_downloads_ids));

        $tax_query = [];
        $tax_query = self::processCategoryParam($tax_query, $params);

        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
            $filter_atts['tax_query'] = $tax_query;
        }

        $filtered_downloads_ids = self::getFilteredDownloadsIds($filter_atts);

        $view_permission_option = CMDM_Settings::getOption(CMDM_Settings::OPTION_VIEWING_PERMISSIONS);
        if ($view_permission_option === 'loggedin'){
            $download_ids = $filtered_downloads_ids;
        } else {
            $download_ids = array_unique(
                array_merge($author_post_ids, $allowed_downloads_ids)
            );
        }


        if (empty($download_ids)) {
            return [];
        }

        $args = [
            'post__in' => $download_ids,
            'post_type' => self::POST_TYPE,
            'fields' => 'ids',
            'post_status' => ['publish'],
            'order' => 'DESC',
        ];

        if (!empty($limit)) {
            $args['posts_per_page'] = $limit;
            $args['paged'] = 1;
        }

        if ( !empty($page) ) {
            $args['paged'] = $page;
        }

        return $args;
    }


	public static function prepareDownloadsForGuestQuery( $params = [] ) {

		$limit = intval($params['limit'] ?? - 1);
		$page  = intval($params['page'] ?? 1);

		$args = [
			'post_type'      => self::POST_TYPE,
			'fields'         => 'ids',
			'post_status'    => [ 'publish' ],
			'order'          => 'DESC',
			'posts_per_page' => - 1,
		];

		$filter_atts = [];

		if ( isset( $params['monthnum'] ) ) {
			$filter_atts['monthnum'] = $params['monthnum'];
		}

		if ( isset( $params['year'] ) ) {
			$filter_atts['year'] = $params['year'];
		}

		if ( ! empty( $filter_atts ) ) {
			$args = array_merge( $args, $filter_atts );
		}

		$download_ids = get_posts( $args );

		if ( empty( $download_ids ) ) {
			return [];
		}

		$args = [
			'post__in'    => $download_ids,
			'post_type'   => self::POST_TYPE,
			'fields'      => 'ids',
			'post_status' => [ 'publish' ],
			'order'       => 'DESC',
		];

		if ( ! empty( $limit ) ) {
			$args['posts_per_page'] = $limit;
			$args['paged']          = 1;
		}

		if ( ! empty( $page ) )
			$args['paged'] = $page;

		return $args;
    }

    public static function prepareAllDownloadsQueryArgs($limit = null, $page = null) {

        global $wpdb; // WP_Query Does not search posts in categories

        $download_ids = $wpdb->get_col('SELECT p.* FROM ' . $wpdb->posts . ' p '
            . 'WHERE post_type="' . self::POST_TYPE . '" '
            . 'ORDER BY p.menu_order ASC, p.post_modified DESC');

        $args = [
            'post_type' => self::POST_TYPE,
            'fields' => 'ids',
            'post_status' => ['publish', 'cmdm_pending_payment'],
            'order' => 'DESC',
            'posts_per_page' => -1,
        ];

        if (!empty($download_ids)) {
            $args['post__in'] = $download_ids;
        }

        if (!empty($limit)) {
            $args['posts_per_page'] = $limit;
            $args['paged'] = 1;
        }

        if (!empty($page)) {
            $args['paged'] = $page;
        }

        return $args;
    }

    public static function getAllDownloadsQuery($params = []): WP_Query {
        $limit = intval($params['limit'] ?? 10);
        $page = intval($params['page'] ?? 1);

        $args = self::prepareAllDownloadsQueryArgs($limit, $page);
        $args = self::processRequestArgs($args);
        $args = self::processRequestArgsSort($args, $params['sort'] ?? '');

        if (isset($params['month']) || isset($params['monthnum'])) {
            $args['monthnum'] = $params['monthnum'] ?? $params['month'] ?? '';
            if (empty($args['month'])) {
                unset($args['month']);
            }
        }

        if (!empty($params['year'])) {
            $args['year'] = $params['year'];
        }

        $tax_query = [];
        $tax_query = self::processCategoryParam($tax_query, $params);

        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
            $args['tax_query'] = $tax_query;
        }

        $args = apply_filters('cmdm_before_query_args', $args);

        return new WP_Query($args);
    }
}

include_once CMDM_PATH . '/lib/models/api.php';
