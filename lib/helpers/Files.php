<?php

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

class CMDM_Files {

    public $params = array();
    public static $errors = array();

    public function __construct() {
    }

    public function init($params) {
    }

    public static function uploadToWp($params = array("url" => "", "post_id" => "")) {
        $id = false;
        $post_id = isset($params["post_id"]) ? (int)$params["post_id"] : 0;

        $tmp = download_url($params["url"]);
        if( is_wp_error( $tmp ) ){
            self::$errors[] = $tmp->get_error_messages();
            return false;
        }

        $file_array = array(
            'name'     => basename( $params["url"] ),
            'type'     => wp_check_filetype( basename( $params["url"] ) ),
            'tmp_name' => $tmp,
            'error'    => UPLOAD_ERR_OK,
            'size'     => filesize($tmp),
        );
        $id = media_handle_sideload( $file_array, $post_id );
        if( is_wp_error( $id ) ){
            self::$errors[] = $id->get_error_messages();
            return false;
        }
        @unlink( $tmp );
        return $id;
    }

    public static function getErrors() {
        return self::$errors;
    }
}

?>
