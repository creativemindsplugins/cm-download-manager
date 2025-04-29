<?php

require_once 'Attachment.php';

class CMDM_Screenshot extends CMDM_Attachment {

	const FINAL_STATUS = 'private';
	
	public static function selectForDownload($downloadId, $resultType = self::R_ID) {
		global $wpdb;
		$status = static::STATUS_PRIVATE;
		$ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts
			WHERE post_type = %s
			AND post_parent = %d
			AND post_status = %s
			AND post_mime_type LIKE 'image/%%'
			ORDER BY menu_order ASC",
				static::POST_TYPE, $downloadId, $status));
		if(empty($ids)){
            $download = CMDM_GroupDownloadPage::getInstance($downloadId);
            $result = $download->getPostMeta('_screenshots_v2');
            if(isset($result) && $result){
                $ids = is_array($result) ? $result : array($result);
            }
        }
		if ($resultType == self::R_OBJECT) {
			return array_filter(array_map(array(__CLASS__, 'getById'), $ids));
		} else {
			return $ids;
		}
	}
	
	
	public function getSmallUrl() {
		return $this->getUrl('small');
	}
	
	
	public function getUrl($size = CMDM_Attachment::IMAGE_SIZE_THUMB, $icon = false) {
		if ($image = wp_get_attachment_image_src($this->getId(), $size, $icon)) {
			return reset($image);
		}
//		return wp_get_attachment_url($this->getId());
	}
	
	
	public function isDownloadsThumbnail() {
		if ($downloadId = $this->getDownloadId()) {
			return (get_post_thumbnail_id($downloadId) == $this->getId());
		} else return false;
	}

}
