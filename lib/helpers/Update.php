<?php

class CMDM_Update {
	
	const OPTION_UPDATED = 'cmdm_free_updated_version_';
	
	protected static $versions = ['2.0.1'];//array('1.7.2', '1.9.0', '2.0.1', '2.2.7', '2.4.1', '2.5.0', '2.5.12', '3.15.2', '4.1.6');
	

	public static function run() {
		global $wpdb;
		
		if (defined('DOING_AJAX') AND DOING_AJAX) return;
		
		foreach (self::$versions as $version) {
			$v = strtr($version, '.', '_');
			if (!get_option(self::OPTION_UPDATED . $v)) {
				$methodName = 'update_'. $v;
				if (method_exists(__CLASS__, $methodName)) {
					call_user_func(array(__CLASS__, $methodName));
				}
				add_option(self::OPTION_UPDATED . $v, 1);
			}
		}

	}

	public static function update_1_9_0() {
		global $wpdb;
		
		// Generate separate attachments for download files to add support of the multiple uploads.
		$posts = $wpdb->get_results($wpdb->prepare("SELECT d.* FROM $wpdb->posts d
			LEFT JOIN $wpdb->postmeta m ON d.ID = m.post_id AND m.meta_key = '_package_type'
			LEFT JOIN $wpdb->posts a ON d.ID = a.post_parent AND a.post_type = %s
			WHERE d.post_type = %s AND a.ID IS NULL AND m.meta_value = 'file'
			GROUP BY d.ID", CMDM_DownloadFile::POST_TYPE, CMDM_GroupDownloadPage::POST_TYPE));
		
		foreach ($posts as $post) {
			if ($download = CMDM_GroupDownloadPage::getInstance($post->ID)) {
				$download->rebuildAttachments();
			}
		}
		
	}
	
	
	public static function update_2_0_1() {
		global $wpdb;
		
		// Generate separate attachments for download screenshots
		$posts = $wpdb->get_results($wpdb->prepare("SELECT d.*, m.meta_value AS screenshots FROM $wpdb->posts d
			LEFT JOIN $wpdb->postmeta m ON d.ID = m.post_id AND m.meta_key = %s
			WHERE d.post_type = %s
			GROUP BY d.ID", '_screenshots', CMDM_GroupDownloadPage::POST_TYPE), ARRAY_A);

		foreach ($posts as $post) {
			$download = CMDM_GroupDownloadPage::getInstance($post['ID']);
			if ($download AND !empty($post['screenshots'])) {
				$screenshots = unserialize($post['screenshots']);
				if (is_array($screenshots) AND !empty($screenshots)) {
					foreach ($screenshots as $fileName) {
						$oldPath = $filePath = CMDM_Screenshot::getUploadDir('screenshots') . $fileName;
						$newPath = CMDM_Screenshot::getUploadDir($post['ID']) . basename($oldPath);
						$existing = $download->getScreenshotsIds();
						if (file_exists($oldPath) AND ((!file_exists($newPath) AND copy($oldPath, $newPath)) OR (file_exists($newPath) AND empty($existing)))) {
							if ($screenshot = CMDM_Screenshot::create($post['ID'], $newPath)) {
								// ok
							}
						}
					}
				}
			}
		}
		
	}
	
	
	public static function update_2_2_7() {
		global $wpdb;
		
		// Update rating cache
		$posts = $wpdb->get_col($wpdb->prepare("SELECT p.ID FROM $wpdb->posts p
			LEFT JOIN $wpdb->postmeta m ON m.meta_key = %s AND m.post_id = p.ID
			WHERE p.post_type = %s AND m.meta_id IS NULL",
			CMDM_GroupDownloadPage::$_meta['rating_value'],
			CMDM_GroupDownloadPage::POST_TYPE
		));
		
		foreach ($posts as $id) {
			if ($download = CMDM_GroupDownloadPage::getInstance($id)) {
				$stats = $download->getRatingStats();
				update_post_meta($id, CMDM_GroupDownloadPage::$_meta['rating_value'], $stats['ratingAvg']);
			}
		}
		
	}
	
	
	public static function update_2_4_1() {
		global $wpdb;
		
		// Move marked as downloaded info to usermeta
		$records = $wpdb->get_results($wpdb->prepare("SELECT post_id as downloadId, meta_value as userId
			FROM $wpdb->postmeta
	    	WHERE meta_key = %s",
    		'_cmdm_download_user_id'
		), ARRAY_A);
		$grouped = array();
		foreach ($records as $record) {
			$grouped[$record['userId']][$record['downloadId']] = $record['downloadId'];
		}
		
		foreach ($grouped as $userId => $ids) {
			update_user_meta($userId, CMDM_GroupDownloadPage::USER_META_DOWNLOAD_LOG, array_values($ids));
		}
		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta
	    	WHERE meta_key = %s OR meta_key LIKE %s",
    		'_cmdm_download_user_id', '_cmdm_download_time%'
		));
		
	}
	
	
	public static function update_3_15_2() {
		global $wpdb;
		
		// Update allowed users list for downloads with visibility=author
		// since there was a bug that the list wasn't updated properly after updating the download.
		
		$sql = $wpdb->prepare("SELECT ID, post_author FROM $wpdb->posts p
				JOIN $wpdb->postmeta pm ON pm.post_id = p.ID AND pm.meta_key = %s
				WHERE p.post_type = %s AND pm.meta_value = %s",
				'_visibility', 'cmdm_page', 'author');
		$posts = $wpdb->get_results($sql, ARRAY_A);
		
		$ids = array_map(function($post) { return $post['ID']; }, $posts);
		
		// Delete old metas
		if (!empty($ids)) {
			$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->postmeta WHERE meta_key = %s AND post_id IN (". implode(',', $ids) .")", 'cmdm_allowed_user_id'));
		}
		
		// Add new metas
		foreach ($posts as $post) {
			add_post_meta($post['ID'], 'cmdm_allowed_user_id', $post['post_author']);
		}
	}
    public static function update_4_1_6() {
		if ( false == get_option('CMDM_downloading_permissions') )  {
			$dl_option = get_option('CMDM_download_permissions') ? get_option('CMDM_download_permissions') : 'all';
			update_option('CMDM_downloading_permissions', $dl_option );
		}
    }


}
