<?php

require_once 'Attachment.php';

class CMDM_DownloadFile extends CMDM_Attachment {

	const NONCE_AUDIO = 'CMDM_preview';
	const FINAL_STATUS = 'inherit';
	

	public static function selectForDownload(CMDM_GroupDownloadPage $download, $resultType = self::R_ID) {
		global $wpdb;
		static $attch_id_array = array();
		$dl_id = $download->getId();

		if ( ( ! isset( $attch_id_array[ $dl_id ] ) ) || empty( $attch_id_array[ $dl_id ] ) ) {
			$attch_id_array[ $dl_id ] = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_parent = %d AND post_status = %s ORDER BY menu_order ASC",	static::POST_TYPE, $dl_id, static::STATUS_INHERIT));
		}
		if ($resultType == self::R_OBJECT) {
			return array_map(array(__CLASS__, 'getById'), $attch_id_array[ $dl_id ]);
		} else {
			return $attch_id_array[ $dl_id ];
		}
	}
	
	
	public function getDownloadFormUrl() {
		$result = CMDM_CmdownloadController::getUrl('cmdownload', 'get');
    	$result .= 'file/' . urlencode(str_replace('.php', '-php', $this->getFileName()));
		return $result;
    }

    public function getSize() {
        return get_post_meta($this->getId(), 'cmdm_file_size', true);
    }

}
