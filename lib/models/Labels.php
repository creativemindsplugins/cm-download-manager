<?php

class CMDM_Labels {
	
	const FILENAME = 'labels.tsv';
	const OPTION_LABEL_PREFIX = 'cmdm_label_';
	const DB_OPTION_LABELS_NAME = 'cmdm_option_labels';
	
	protected static $labels = array();
	protected static $labelsByCategories = array();
	private static $labels_cache = array();
    protected static $available_labels = [
        'Author',
        'front_menu_dashboard',
        'front_menu_downloads',
        'dashboard_menu_all_downloads',
        'search_placeholder',
        'index_download_info_link',
        'index_no_results',
        'index_pagination_showing',
        'index_one_download',
        'index_d_downloads',
        'index_all_downloads',
        'dashboard_table_id',
        'dashboard_table_title',
        'dashboard_table_status',
        'dashboard_date',
        'dashboard_table_downloads_num',
        'dashboard_table_actions',
        'dashboard_no_downloads',
        'dashboard_action_unpublish',
        'dashboard_action_publish',
        'dashboard_action_edit',
        'dashboard_action_delete',
        'dashboard_action_delete_confirm',
        'upload_title_field',
        'upload_file_field',
        'download_updated_view_link_title',
        'upload_allowed_extensions',
        'upload_thumb_field',
        'upload_thumb_field_desc',
        'upload_thumb_click_to_set',
        'upload_screenshots_field',
        'upload_form_add_btn',
        'upload_form_update_btn',
        'upload_select_files_btn',
        'upload_select_file_btn',
        'upload_screenshots_field_description',
        'upload_menu_add_new',
        'upload_menu_delete',
        'upload_menu_view',
        'upload_category_field',
        'upload_admin_recommended',
        'upload_version',
        'upload_allowed_extensions',
        'upload_notify_support',
        'upload_added_msg',
        'upload_updated_msg',
        'upload_deleted_msg',
        'upload_published_msg',
        'upload_unpublished_msg',
        'download_not_permitted',
        'download_button_file',
        'back_to_all_downloads',
        'download_read_more_link',
        'download_info_header',
        'download_version',
        'download_downloads_num',
        'download_updated_time',
        'download_admin_recommended',
        'download_tab_description',
        'download_tab_support',
        'support_thread_resolved',
        'support_no_topics',
        'support_create_topic_call',
        'support_th_topic',
        'support_th_posts',
        'support_th_last_poster',
        'support_th_updated',
        'support_leave_comment_header',
        'support_topic_form_title',
        'support_topic_form_content',
        'support_topic_form_notify',
        'support_topic_form_post_btn',
        'support_topic_form_allow_markup',
        'support_topic_form_wrap_code',
        'support_topic_posted_success',
        'support_topic_login_call',
        'support_back',
        'support_post_answer_header',
        'support_answer_form_content',
        'support_topic_mark_resolved',
        'support_topic_post_answer_btn',
        'support_answer_added_msg',
        'panel_title_cmdownload_add',
        'panel_title_cmdownload_edit',
        'panel_title_cmdownload_dashboard',
        'panel_title_error_error',
        'rating_success',
        'filter_search_by_title',
        'filter_search_by_date_range'
    ];

	
	public static function init() {
		self::loadLabels();
		do_action('cmdm_labels_init');
	}
	

	public static function getLabel($label_key) {
		$option_name = self::OPTION_LABEL_PREFIX . $label_key;
		$default = self::getDefaultLabel($label_key);

		if ( empty( self::$labels_cache ) ) {
			self::$labels_cache = unserialize( get_option( self::DB_OPTION_LABELS_NAME ) );
		}
		if ( !isset( self::$labels_cache[ $option_name ] ) ) {
			self::$labels_cache[ $option_name ] = $default;
		}

		$result = self::$labels_cache[ $option_name ];

		if (empty($result)) {
			$result = (empty($default) ? $label_key : $default);
		}
		return $result;
	}
	
	public static function setLabel($label_key, $value) {
		$option_name = self::OPTION_LABEL_PREFIX . $label_key;

		if ( empty( self::$labels_cache ) ) {
			self::$labels_cache = unserialize( get_option( self::DB_OPTION_LABELS_NAME ) );
		}
		self::$labels_cache[ $option_name ] = $value;

		update_option( self::DB_OPTION_LABELS_NAME, serialize( self::$labels_cache ), $autoload = true);
	}
	
	public static function getLocalized($labelKey) {
		return CMDM::__(self::getLabel($labelKey));
	}
	
	
	public static function getDefaultLabel($key) {
		if ($label = self::getLabelDefinition($key)) {
			return $label['default'];
		}
	}
	
	
	public static function getDescription($key) {
		if ($label = self::getLabelDefinition($key)) {
			return $label['desc'];
		}
	}
	
	
	public static function getLabelDefinition($key) {
		$labels = self::getLabels();
		return (isset($labels[$key]) ? $labels[$key] : NULL);
	}
	
	
	public static function getLabels() {
		return self::$labels;
	}
	
	
	public static function getLabelsByCategories() {
		return self::$labelsByCategories;
	}
	
	
	public static function getDefaultLabelsPath() {
		return dirname(__FILE__) .'/'. self::FILENAME;
	}

	
	public static function loadLabels($path = null) {
		$file = explode("\n", file_get_contents(empty($path) ? self::getDefaultLabelsPath() : $path));
		foreach ($file as $row) {
			$row = explode("\t", trim($row));
			if (count($row) >= 2) {
				$label = array(
					'default' => $row[1],
					'desc' => (isset($row[2]) ? $row[2] : null),
					'category' => (isset($row[3]) ? $row[3] : null),
				);
				self::$labels[$row[0]] = $label;
				self::$labelsByCategories[$label['category']][] = $row[0];
			}
		}
	}

    public static function isAvailable($key){
        if( in_array($key, self::$available_labels)){
            return true;
        }
        return false;
    }
	
	
}

add_action('cmdm_load_label_file', array('CMDM_Labels', 'loadLabels'), 1);
