<?php

class CMDM_CmdmCategoryController extends CMDM_BaseController {

	public static function initialize() {

        add_action('admin_notices', [__CLASS__, 'checkCategoriesAdminNotice']);
		add_action('CMDM_show_categories', [__CLASS__, 'showCategories'], 1, 2);
		add_action('CMDM_show_item_categories', [__CLASS__, 'showItemCategories'], 1, 1);
        add_action( 'cmdm_category_add_form_fields',[__CLASS__,'addProTermFields']);

		if (is_admin()) {
            self::createDefaultCategory();
        }

	}

    static function createDefaultCategory() {
        if (defined('CMDM_CREATE_DEFAULT_CATEGORY') and CMDM_CREATE_DEFAULT_CATEGORY == 1) {
            return;
        }

        global $wpdb;
        define('CMDM_CREATE_DEFAULT_CATEGORY', 1);

        $count = $wpdb->get_var($wpdb->prepare("
                    SELECT COUNT(*) FROM $wpdb->term_taxonomy WHERE taxonomy = %s
                ", CMDM_Category::TAXONOMY));

        if ($count == 0) {
            wp_insert_term('General', CMDM_Category::TAXONOMY);
        }
    }

	public static function checkCategoriesAdminNotice() {
		global $wpdb;
		$categoriesCount = intval($wpdb->get_var($wpdb->prepare("SELECT count(*) FROM $wpdb->term_taxonomy x
			WHERE x.taxonomy = %s", CMDM_Category::TAXONOMY)));
			if ($categoriesCount == 0) {
			printf('<div class="error"><p>%s<a href="%s" class="button" style="margin-left:1em;">%s</a></p></div>',
        		CMDM::__('CM Download Manager: you have to define at least one category.'),
        		esc_attr('edit-tags.php?taxonomy=' . CMDM_Category::TAXONOMY . '&post_type=' . CMDM_GroupDownloadPage::POST_TYPE),
	        		CMDM::__('Go to Categories')
	        		);
		}
	}

	public static function showCategories($currentCategory = null, $displayOptions = [])
	{
		global $wp_query;
		if (empty($currentCategory) AND $wp_query->is_archive() AND !$wp_query->is_tag() AND !$wp_query->is_search()) {
			$currentCategory = $wp_query->get_queried_object();
		}
		echo self::_loadView('cmdownload/categories', compact('currentCategory', 'displayOptions'));
	}

	public static function showItemCategories($id)
	{
		$download = CMDM_GroupDownloadPage::getInstance($id);
		if( $download instanceof CMDM_GroupDownloadPage )
		{
			echo self::_loadView('cmdownload/meta/categories', [
				'categories' => $download->getCategoriesList(),
				'taxonomy' => CMDM_Category::TAXONOMY
            ]);
		}
	}

    public static function addProTermFields(){
        include CMDM_PATH .'/views/backend/meta/category-pro-fields.phtml';
    }

}
