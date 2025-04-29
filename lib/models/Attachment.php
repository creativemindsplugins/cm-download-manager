<?php

abstract class CMDM_Attachment {

    const POST_TYPE = 'attachment';

    const UPLOAD_DIR = 'cmdm';
    const TMP_DIR = 'tmp';
    const DEFAULT_MIME_TYPE = 'application/octet-stream';

    const STATUS_INHERIT = 'inherit';
    const STATUS_PRIVATE = 'private';
    const DEFAULT_STATUS = 'private';
    const FINAL_STATUS = 'private';

    const R_ID = 'id';
    const R_OBJECT = 'object';

    const IMAGE_SIZE_THUMB = 'thumbnail';
    const IMAGE_SIZE_MEDIUM = 'medium';
    const IMAGE_SIZE_MEDIUM_LARGE = 'medium_large';
    const IMAGE_SIZE_FULL = 'full';


    protected $post;
    protected $meta;

    public $storage;


    public function __construct($post) {

        $this->post = (object)$post;
        $this->meta = get_post_meta($post->ID);

        if (!isset($this->meta['storage']) ||
            empty($this->meta['storage']) ||
            empty($this->meta['storage'][0])) {

            // @note: all downloads before FTP functionaly don't have storage meta - so it's local storage
            $this->storage = 'locale';
        }

    }


    public static function select($postId, $include = null) {
        $args = [
            'post_type' => static::POST_TYPE,
            'numberposts' => null,
            'post_status' => null,
            'post_parent' => $postId,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'posts_per_page' => 999,
        ];

        if (!empty($include)) $args['include'] = $include;
        $attachments = get_posts($args);
        $result = [];
        if ($attachments) {
            foreach ($attachments as $attachment) {
                $result[] = new static($attachment);
            }
        }
        return $result;
    }


    public static function getById($id) {
        if ($post = get_post($id)) {
            return new static($post);
        }
    }


    public static function handleUpload($postId = null, $fieldName = 'upload') {
        $attachments = [];
        if (!empty($_FILES[$fieldName]) && is_array($_FILES[$fieldName])) {
            if (is_array($_FILES[$fieldName]['name'])) {
                foreach ($_FILES[$fieldName]['name'] as $i => $name) {
                    $phpExtension = stristr($_FILES[$fieldName]['name'][$i], '.php');
                    if (!static::isExtensionAllowed($_FILES[$fieldName]['name'][$i]) || $phpExtension) {
                        throw new Exception('Not allowed extension.');
                    }
                    $attachments[] = static::addUploadedFile($postId,
                        $_FILES[$fieldName]['name'][$i],
                        $_FILES[$fieldName]['tmp_name'][$i],
                        $_FILES[$fieldName]['type'][$i],
                        $_FILES[$fieldName]['size'][$i]
                    )->getId();
                }
            } else {

                if (($fieldName == 'send_to_user') &&
                    (!$_FILES[$fieldName]['name'] || !$_FILES[$fieldName]['tmp_name'] || !$_FILES[$fieldName]['type'] || !$_FILES[$fieldName]['size'])) {
                    return $attachments;
                }
                $phpExtension = stristr($_FILES[$fieldName]['name'], '.php');
                if (!static::isExtensionAllowed($_FILES[$fieldName]['name']) || $phpExtension) {
                    throw new Exception('Not allowed extension.');
                }

                $attachments[] = static::addUploadedFile($postId,
                    $_FILES[$fieldName]['name'],
                    $_FILES[$fieldName]['tmp_name'],
                    $_FILES[$fieldName]['type'],
                    $_FILES[$fieldName]['size']
                )->getId();
            }
        }
        return $attachments;
    }


    static function isExtensionAllowed($fileName) {
        $allowed = CMDM_Settings::getOption(CMDM_Settings::OPTION_ALLOWED_EXTENSIONS);
        if ($allowed == '*' or (is_array($allowed) and reset($allowed) == '*')) return true;
        if (!is_array($allowed)) $allowed = explode(',', $allowed);
        $allowed = array_map('strtolower', array_filter(array_map('trim', $allowed)));
        $pos = strrpos($fileName, '.');
        if ($pos !== false) {
            $ext = strtolower(substr($fileName, $pos + 1, 9999));
            return in_array($ext, $allowed);
        } else {
            return false;
        }
    }


    public static function addUploadedFile($postId, $name, $tmpName, $mimeType, $size) {
        $fileName = static::sanitizeFileName($name);
        $filePath = static::getUploadDir($postId) . $fileName;
        if (move_uploaded_file($tmpName, $filePath)) {
            return static::create($postId, $filePath, $name, $mimeType);
        } else {
            throw new Exception('Failed to move uploaded file.');
        }
    }


    public static function create($parentPostId, $filePath, $title = null, $mimeType = null, $status = null) {
        if (is_null($status)) {
            $status = static::DEFAULT_STATUS;
        }

        if (is_null($mimeType)) {
            $mimeType = static::recognizeMimeType($filePath);
            if (empty($mimeType)) $mimeType = static::DEFAULT_MIME_TYPE;
        }

        if (is_null($title)) {
            $title = static::sanitizeFileName(basename($filePath), false);
        }

        $attachment = [
            'guid' => $filePath,
            'post_mime_type' => $mimeType,
            'post_title' => $title,
            'post_content' => '',
            'post_status' => $status,
            'post_type' => static::POST_TYPE,
        ];
        $attach_id = wp_insert_attachment($attachment, $filePath, $parentPostId);
        /*static::updateMetaData($attach_id, $filePath);*/
        update_post_meta($attach_id, 'cmdm_attachment', '1');
        update_post_meta($attach_id, 'cmdm_file_size', filesize(get_attached_file($attach_id)));
        return new static(get_post($attach_id));
    }


    public static function updateMetaData($postId, $filePath) {
        // you must first include the image.php file
        // for the function wp_generate_attachment_metadata() to work
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        $attach_data = wp_generate_attachment_metadata($postId, $filePath);
        return wp_update_attachment_metadata($postId, $attach_data);
    }


    public static function recognizeMimeType($path) {
        if (!file_exists($path)) return;
        $mimeType = null;
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $path);
            finfo_close($finfo);
        }
        if (empty($mimeType)) {
            $mimeType = static::recognizeMimeTypeFromExtension($path);
        }
        return $mimeType;
    }


    public static function recognizeMimeTypeFromExtension($fileName) {
        $mimeType = null;
        $name = basename($fileName);
        $ext = strtolower(substr($name, strrpos($name, '.') + 1, 5));
        $images = ['jpg', 'jpeg', 'gif', 'png', 'bmp'];
        if (in_array($ext, $images)) {
            $mimeType = 'image/' . $ext;
        }
        return $mimeType;
    }


    public static function sanitizeFileName($title, $unique = true) {
        return ($unique ? time() . '_' : '') . sanitize_file_name($title);
    }


    public function getPost() {
        return $this->post;
    }


    public function setPostParent($id) {
        $this->post->post_parent = $id;
        return $this;
    }


    public function attach($postId) {
        $oldPath = $this->getPath();
        $this->setPostParent($postId)->setStatus(static::FINAL_STATUS)->save();
        $newPath = static::getUploadDir($postId) . basename($oldPath);
        if ($oldPath != $newPath) {
            if (rename($oldPath, $newPath)) {
                /*static::updateMetaData($this->getId(), $newPath);*/
                $this->setFilePath($newPath);
                return $this;
            }
        } else {
            /*static::updateMetaData($this->getId(), $newPath);*/
            return $this;
        }
    }


    public function getId() {
        return intval($this->post->ID);
    }


    public function getParentPostId() {
        return intval($this->post->post_parent);
    }


    public function getDownloadId() {
        return $this->getParentPostId();
    }


    public function getDownload() {
        return CMDM_GroupDownloadPage::getInstance($this->getDownloadId());
    }


    public function getName() {
        return $this->post->post_title;
    }


    public function getTitle() {
        return $this->getName();
    }


    public function setName($name) {
        if (!empty($name)) {
            $this->post->post_title = $name;
        }
        return $this;
    }


    public function getPath() {
        return get_attached_file($this->getId());
    }


    public function getMimeType() {
        if (!empty($this->post->post_mime_type)) {
            return $this->post->post_mime_type;
        }
    }


    public function setMimeType($mime) {
        $this->post->post_mime_type = $mime;
        return $this;
    }


    public function save() {
        return wp_update_post((array)$this->post);
    }


    public function getSize() {
        return @filesize($this->getPath());
    }


    public function getFileName() {
        return $this->getName();
        /*$filepath = $this->getPath();
        if( is_file($filepath) ) {
            return preg_replace('/^[0-9]+_/', '', basename($filepath));
        }*/
    }


    public function getIconTag($size = 'thumbnail', $icon = true, $atts = []) {
        return wp_get_attachment_image($this->getId(), $size, $icon, $atts);
    }

    public function setStatus($status) {
        $this->post->post_status = $status;
        return $this;
    }


    public function setFilePath($filePath) {
        update_attached_file($this->getId(), $filePath);
        return $this;
    }


    public function getURL() {
        return wp_get_attachment_url($this->getId());
    }


    public function getUploadPath() {
        $parentId = $this->getParentPostId();
        return static::getUploadDir(empty($parentId) ? static::TMP_DIR : $parentId);
    }


    public function download($file_list = null) {
        //check to do filepath
        $filepath = $this->getPath();

        if (file_exists($filepath) and is_file($filepath)) {
            $download = $this->getDownload();
            $download->addNumberOfDownloads();

            $fileSize = $this->getSize();

            $ext = pathinfo($filepath, PATHINFO_EXTENSION);
            if (!empty($ext)) $ext = '.' . $ext;
            $mimeType = $this->getMimeType();
            if (strpos($ext, 'mp3')) $mimeType = static::DEFAULT_MIME_TYPE;

            set_time_limit(3600 * 24);

            // header("Pragma: public");
            // header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0, max-age=0");
            header("Cache-Control: private", false); // required for certain browsers
            if (CMDM_Settings::getOption(CMDM_Settings::OPTION_FORCE_BROWSER_DOWNLOAD_ENABLED)) {
                $mimeType = 'application/octet-stream';
                header('Content-Description: File Transfer');
                if (CMDM_GroupDownloadPage::isIeBrowser()) {
                    header("Content-Disposition: attachment; filename=\"" . mb_convert_encoding($this->getFileName(), "ISO-8859-2", "UTF-8") . "\";");
                } else {
                    header("Content-Disposition: attachment; filename=\"" . $this->getFileName() . "\";");
                }
                header("Content-Transfer-Encoding: binary");
                header("Content-Length: " . $fileSize);
            }
            header("Content-type: " . $mimeType);
            readfile($filepath);

        } else {
            throw new Exception('File not found in the filesystem.');
        }

    }


    public static function getUploadDir($name) {
        if (empty($name)) $name = static::TMP_DIR;
        $uploadDir = wp_upload_dir();
        if ($uploadDir['error']) {
            throw new Exception(__('Error while getting wp_upload_dir():' . $uploadDir['error']));
        } else {
            $dir = $uploadDir['basedir'] . '/' . static::UPLOAD_DIR . '/' . $name . '/';
            if (!is_dir($dir)) {
                if (!wp_mkdir_p($dir)) {
                    throw new Exception(__('Script couldn\'t create the upload folder:' . $dir));
                }
            }
            return $dir;
        }
    }


    public static function getTmpPath() {
        return static::getUploadDir(static::TMP_DIR);
    }


    function setMenuOrder($order) {
        $this->post->menu_order = $order;
        return $this;
    }


    function delete() {
        $path = $this->getPath();
        if (file_exists($path) && !is_dir($path)) {
            unlink($path);
        }
        return wp_delete_post($this->getId(), $hard = true);
    }

    static function getModifyTime($path) {
        if (!file_exists($path)) {
            return;
        }
        return filemtime($path);
    }

}
