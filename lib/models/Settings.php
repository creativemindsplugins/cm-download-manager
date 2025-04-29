<?php

require_once CMDM_PATH . '/lib/models/SettingsAbstract.php';

class CMDM_Settings extends CMDM_SettingsAbstract {

    const LOCAL_STORAGE = 'local';

    public static $categories = [
        'general' => 'General',
        'index' => 'Index Page',
        'download' => 'Download Page',
        'upload' => 'Upload Page',
        'access' => 'Access and moderation',
        'notifications' => 'Notifications',
        'labels' => 'Labels',
        'social_login' => 'Social Login',
        'custom_css' => 'Custom CSS',
        'remote_storage' => 'Storage Options',
    ];

    public static $subcategories = [
        'general' => [
            'navigation' => 'Navigation: permalinks, menus, searching',
            'appearance' => 'Appearance general settings',
            'login' => 'Login Widget',
            'seo' => 'SEO',
            'disclaimer' => 'Disclaimer',
            'referrals' => 'Referrals',
            'cron' => 'Cron',
        ],
        'index' => [
            'index' => 'Index page general settings',
            'layout' => 'Layout',
            'appearance' => 'Appearance',
            'files-list-appearance' => 'Files list appearance settings',
        ],
        'download' => [
            'layout' => 'Layout',
            'tabs' => 'Download sections',
            'appearance' => 'Appearance',
            'screenshots' => 'Frontend media/Screenshots',
            'preview' => 'Preview and player',
            'fields' => 'Custom fields',
            'support' => 'Support forum',
            'sidebar' => 'Sidebar',
            'logs' => 'Logs',
            'limits' => 'Limits',
            'misc' => 'Miscellaneous: downloading, counting',
        ],
        'upload' => [
            'appearance' => 'Appearance',
            'features' => 'Features',
            'categories' => 'Categories',
            'limits' => 'Limits',
            'form' => 'Fields arrangement',
            'other' => 'Other',
        ],
        'access' => [
            'access' => 'Access',
            // 			'other' => 'Other',
            'downloads' => 'Downloads moderation',
            'moderation_forum' => 'Support forum moderation',
        ],
        'notifications' => [
            'moderation' => 'Moderation',
            'download' => 'New download',
            'support' => 'Support forum',
            'other' => 'Other',
        ],
        'custom_css' => [
            'css' => 'Custom CSS',
        ],
        'remote_storage' => [
            'general' => 'General',
            'ftp' => 'FTP',
        ],
    ];


    const TEXT_DOMAIN = 'cm-download-manager-backend';

    // General Navigation
    const OPTION_CMDOWNLOADS_SLUG = 'CMDM_cmdownloads_slug';
    const OPTION_CMDOWNLOAD_SLUG = 'CMDM_cmdownload_slug';
    const OPTION_UPLOADER_SLUG = 'CMDM_uploader_slug';
    const OPTION_SINGLE_DOWNLOAD_PAGE_USE_SLUG = 'CMDM_single_download_page_use_slug';
    const OPTION_ADD_ADDONS_MENU = 'CMDM_option_add_addons_menu';
    const OPTION_ADD_DASHBOARD_MENU = 'CMDM_option_add_dashboard_menu';
    const OPTION_SEARCH_INCLUDE_CMDM = 'CMDM_search_include';
    const OPTION_SEARCH_INCLUDE_TAGS = 'CMDM_search_include_tags';
    const OPTION_ADD_WIZARD_PAGE = 'CMDM_add_wizard_menu';
    const OPTION_DASHBOARD_PAGINATION_LIMIT = 'CMDM_dashboard_pagination_limit';
    const OPTION_INDEX_PAGE_DISABLED = 'CMDM_index_page_disabled';
    const OPTION_INDEX_PAGE_CUSTOM_PAGE_ID = 'CMDM_index_page_custom_page_id';
    const OPTION_DOWNLOAD_PAGE_DISABLED = 'CMDM_download_page_disabled';
    const OPTION_DOWNLOAD_PAGE_SHOW_DETAILS_IN_LIST = 'CMDM_download_page_show_details_in_list';
    const OPTION_DASHBOARD_PAGE_DISABLED = 'CMDM_dashboard_page_disabled';
    const OPTION_DASHBOARD_PAGE_CUSTOM_ID = 'CMDM_dashboard_page_custom_id';
    const OPTION_REDIRECT_TO_INDEX_AFTER_UPLOAD = 'CMDM_reditect_after_upload';
    const OPTION_WP_ADMIN_POST_EDIT_ENABLED = 'CMDM_wp_admin_post_edit_enabled';
    const OPTION_SCREENSHOTS_UPLOAD_USE_WP_MEDIA = 'CMDM_screenshots_upload_use_wp_media';

    // General Appearance
    const OPTION_GRAVATARS_SHOW = 'CMDM_gravatars_show';
    const OPTION_LOOK_AND_FEEL_CSS = 'CMDM_look_and_feel_css';
    const OPTION_REQUIRE_DESCRIPTION = 'CMDM_require_description';
    const OPTION_UPLOAD_RICH_TEXT_EDITOR_ENABLE = 'CMDM_upload_rich_text_editor_enable';
    const OPTION_ADD_SCREENSHOTS_TO_UPLOADS = 'CMDM_add_screenshots_to_uploads';
    const OPTION_SUPPORT_THEME_DIR = 'CMDM_support_theme_dir';
    const OPTION_FRONTEND_TEMPLATE_DIR = 'CMDM_frontend_template_dir';
    const OPTION_DASHBOARD_FIX_CSS = 'CMDM_dashboard_fix_css';
    const OPTION_SHOW_CHILD_CATEGORIES_IN_DROPDOWN = 'CMDM_show_child_categories_in_dropdown';

    // Index page
    const OPTION_DEFAULT_VIEW = 'CMDM_default_view';
    const OPTION_CATEGORIES_LIST_TYPE = 'CMDM_categories_list_type';
    const OPTION_INDEX_CONTROLS_PLACE = 'CMDM_index_controls_place';
    const OPTION_INDEX_SHOW_UNCATEGORIZED = 'CMDM_index_show_uncategorized';
    const OPTION_SORTING_ORDER_BY = 'CMDM_sorting_order_by';
    const OPTION_SEE_ONLY_ADMIN = 'CMDM_see_only_admin';
    const OPTION_ADDONS_TITLE = 'CMDM_addons_title';
    const OPTION_SHOW_DESCRIPTION = 'CMDM_option_show_description';
    const OPTION_SHOW_DESCRIPTION_LENGTH = 'CMDM_option_show_description_length';
    const OPTION_SHOW_PLAYER = 'CMDM_option_show_player';
    const OPTION_PER_PAGE = 'CMDM_option_per_page';
    const OPTION_INDEX_SHOW_DASHBOARD_LINKS = 'CMDM_index_show_dashboard_links';
    const OPTION_INDEX_SHOW_ADMIN_LINKS = 'CMDM_index_show_admin_links';

    // Index Page Appearance
    const OPTION_SHOW_INDEX_LOGIN_WIDGET = 'CMDM_show_index_login_widget';
    const OPTION_SHOW_LOGIN_WIDGET = 'CMDM_show_login_widget';
    const OPTION_SHOW_LOGIN_WIDGET_FORM = 'CMDM_show_login_widget_form';
    const OPTION_SHOW_AUTHOR_COUNTER = 'CMDM_show_author_counter';
    const OPTION_HIDE_ONLY_CATEGORY = 'CMDM_hide_only_category';
    const OPTION_OPEN_CATEGORY_ON_CLICK = 'CMDM_open_category_on_click';
    const OPTION_SHOW_INFO_LINK = 'CMDM_show_info_link';
    const OPTION_SHOW_INDEX_DOWNLOAD_DATE = 'CMDM_show_index_download_date';
    const OPTION_CATEGORY_SHOW_RECURSIVE = 'CMDM_category_show_recursive';
    const OPTION_SHOW_INDEX_SORTBAR = 'CMDM_show_index_sortbar';
    const OPTION_SHOW_VIEW_TOGGLE_CONTROL = 'CMDM_show_view_toggle_control';
    const OPTION_SHOW_TITLE_NUMBER = 'CMDM_show_title_number';
    const OPTION_SHOW_LEVEL_UP_LINK = 'CMDM_show_level_up_link';
    const INDEX_PAGE_SEARCH_BAR = 'CMDM_index_page_search_bar';
    const INDEX_PAGE_SEARCH_SUBMIT = 'CMDM_index_page_search_submit';
    const FILES_LIST_TAGS_RELATION = 'CMDM_files_list_tags_relation';
    const FILES_LIST_TAGS_SCOPE = 'CMDM_files_list_tags_scope';

    // Download Page Appearance
    const OPTION_SHOW_DETAILS_TABBED = 'CMDM_option_show_details_tabbed';
    const OPTION_SHOW_CHANGELOG = 'CMDM_show_changelog';
    const OPTION_SHOW_INSTALLATION = 'CMDM_show_installation';
    const OPTION_TAB_VIDEO_DEFAULT_VISIBLE = 'CMDM_tab_video_default_visible';
    const OPTION_HIDE_EYEBALL = 'CMDM_hide_eyeball';
    const OPTION_ALLOW_BULK_DOWNLOAD = 'CMDM_allow_bulk_download';
    const OPTION_SHOW_VERSION = 'CMDM_show_version';
    const OPTION_SHOW_DOWNLOAD_PAGE_BACKLINK = 'CMDM_show_download_page_backlink';
    const OPTION_CUSTOM_BACKLINK = 'CMDM_custom_backlink';
    const OPTION_ENABLE_TAGS = 'CMDM_enable_tags';
    const OPTION_ENABLE_RATING = 'CMDM_enable_rating';
    const OPTION_SHOW_AUTHOR = 'CMDM_show_author';
    const OPTION_DOWNLOAD_SHOW_DESCRIPTION = 'CMDM_download_show_description';
    const OPTION_DOWNLOAD_SHOW_EXCERPT = 'CMDM_download_show_excerpt';
    const OPTION_DOWNLOAD_SHOW_NOTES = 'CMDM_download_show_notes';
    const OPTION_DOWNLOAD_PAGE_TEMPLATE = 'CMDM_download_page_template';
    const OPTION_DOWNLOAD_PAGE_TEMPLATE_OTHER = 'CMDM_download_page_template_other';
    const OPTION_INDEX_PAGE_TEMPLATE = 'CMDM_index_page_template';
    const OPTION_INDEX_PAGE_TEMPLATE_OTHER = 'CMDM_index_page_template_other';
    const OPTION_DASHBOARD_PAGE_TEMPLATE = 'CMDM_dashboard_page_template';
    const OPTION_DASHBOARD_PAGE_SHORTCODE = 'CMDM_dashboard_page_shortcode';
    const OPTION_DASHBOARD_PAGE_TEMPLATE_OTHER = 'CMDM_dashboard_page_template_other';
    const OPTION_BACKEND_CATEGORY_FILTER_MODE = 'CMDM_backend_category_filter_mode';
    const OPTION_MAX_WIDTH_FOR_CMDM_CONTAINER = 'CMDM_max_width_for_cmdm_container';
    const OPTION_SHOW_DOWNLOADS_NUMBER = 'CMDM_show_downloads_number';
    const OPTION_SHOW_VIEWS_NUMBER = 'CMDM_show_views_number';
    const OPTION_SHOW_REPORT_BTN = 'CMDM_show_report_btn';
    const OPTION_CMVL_SHORTCODE_ATTS = 'CMDM_cmvl_shortcode_atts';
    const OPTION_CMVL_TAB_VISIBLE = 'CMDM_cmvl_tab_visible';
    const OPTION_DOWNLOAD_BUTTON_WIDGET_AREA = 'CMDM_download_button_widget_area';
    const OPTION_DIRECT_DOWNLOAD_LINK_ENABLE = 'CMDM_direct_download_link_enable';
    const OPTION_MIME_TYPE_FUNCTION = 'CMDM_mime_type_function';
    const OPTION_DOWNLOAD_DESCRIPTION_PARSE_SHORTCODES = 'cmdm_download_description_parse_shortcodes';
    const OPTION_DOWNLOAD_TAGS_IN_LIST_ENABLED = 'cmdm_download_tags_in_list_enabled';
    const OPTION_SHORTCODES_WHITELIST = 'cmdm_shortcodes_whitelist';
    const OPTION_SHOW_ONLY_ADMINS_IN_FILTER = 'cmdm_show_only_admins_in_filter';
    const OPTION_SELECT_ALL_FILES_OF_SINGLE_DOWNLOAD = 'cmdm_select_all_files_single_download';

    // Download preview
    const OPTION_AUDIO_PLAYER = 'CMDM_audio_player';

    // Download Page Sidebar
    const OPTION_DOWNLOAD_BLOCK_ON_TOP = 'CMDM_download_block_on_top';
    const OPTION_SHOW_SOCIAL = 'CMDM_option_show_social';
    const OPTION_SHOW_RELATED = 'CMDM_option_show_related';
    const OPTION_SIDEBAR_BEFORE_WIDGET = 'CMDM_sidebar_before_widget';
    const OPTION_SIDEBAR_BEFORE_TITLE = 'CMDM_sidebar_before_title';
    const OPTION_SIDEBAR_AFTER_TITLE = 'CMDM_sidebar_after_title';
    const OPTION_SIDEBAR_AFTER_WIDGET = 'CMDM_sidebar_after_widget';

    // Logs
    const OPTION_LOG_DOWNLOADS = 'CMDM_log_downloads';
    const OPTION_EMAIL_ON_DOWNLOAD = 'CMDM_email_on_download';
    const OPTION_GEOLOCIATION_API_KEY = 'CMDM_geolocation_api_key';
    const OPTION_LIMIT_OF_EXPORT_ROWS = 'CMDM_limit_of_export_rows';

    // Referrals
    const OPTION_AFFILIATE_CODE = 'CMDM_affiliate_code';
    const OPTION_REFERRAL_ENABLED = 'CMDM_referral_enabled';

    //Cron
    const CRON_ENABLE = 'cron_enable_limits';
    //    const CRON_TIME_REMOVE_NOT_ASSIGNED_ATTACHMENTS = 'cron_time_remove_not_assigned_attachments';
    const CRON_TIME_REMOVE_EXPIRED_DOWNLOADS_PERMANENTLY = 'cron_time_remove_expired_downloads_permanently';

    // Download Limits
    const OPTION_DOWNLOAD_LIMIT_ENABLE = 'CMDM_download_limit_enable';
    const OPTION_DOWNLOAD_LIMIT_NUMBER = 'CMDM_download_limit_number';
    const OPTION_DOWNLOAD_LIMIT_HOURS = 'CMDM_download_limit_hours';

    // Upload Limits
    const OPTION_UPLOAD_LIMIT_ENABLE = 'CMDM_upload_limit_enable';
    const OPTION_UPLOAD_LIMIT_NUMBER = 'CMDM_upload_limit_number';
    const OPTION_UPLOAD_LIMIT_HOURS = 'CMDM_upload_limit_hours';


    // Download Page Misc
    const OPTION_FORCE_BROWSER_DOWNLOAD_ENABLED = 'CMDM_force_browser_download_enabled';
    const OPTION_BYPASS_PHP_FILESIZE = 'CMDM_bypass_php_filesize';
    const OPTION_INCREMENT_VIEWS = 'CMDM_increment_views';
    const OPTION_CUSTOM_FIELD1 = 'CMDM_option_custom_field1';
    const OPTION_CUSTOM_FIELD2 = 'CMDM_option_custom_field2';
    const OPTION_KEEP_ORIGINAL_FILE_NAME = 'CMDM_keep_original_file_name';
    const OPTION_FILE_NAME_IN_DOWNLOAD_FORM_URL = 'CMDM_file_name_in_download_form_url';
    const OPTION_REMOVE_WPSEO_HEAD_ACTION = 'CMDM_remove_wpseo_head_action';

    // Download Support
    const OPTION_SUPPORT_SHOW = 'CMDM_support_enable';
    const OPTION_THREAD_NOTIFICATIONS_SUPPORT = 'CMDM_thread_notifications_support';
    const OPTION_SUPPORT_THREAD_SORTING = 'CMDM_thread_sorting';
    const OPTION_DEBUG_EMAIL = 'cmdm_debug_email';

    // Download Screenshots
    const OPTION_ALLOW_SCREENSHOTS = 'CMDM_allow_screenshots';
    const OPTION_SET_FIRST_IMAGE_AS_FETATURED = 'CMDM_set_first_image_as_featured';
    const OPTION_HIDE_THUMB_SCREENSHOTS = 'CMDM_hide_thumb_screenshots';
    const OPTION_THUMB_FROM_CATEGORY = 'CMDM_thumb_from_category';
    const OPTION_SHOW_SLIDESHOW = 'CMDM_option_show_slideshow';
    const OPTION_SLIDESHOW_AUTOPLAY = 'CMDM_option_slideshow_autoplay';
    const OPTION_DEFAULT_SCREENSHOT = 'CMDM_option_default_screenshot_free';
    const OPTION_ENABLE_YOUTUBE_LINK = 'CMDM_option_enable_youtube_link';

    // Upload
    const OPTION_ALLOWED_EXTENSIONS = 'CMDM_allowed_extensions';
    const OPTION_ALLOWED_TYPES = 'CMDM_allowed_types';
    const OPTION_UPLOAD_BUTTON_LINK = 'CMDM_upload_button_link';
    const OPTION_CATEGORIES_REQUIRED = 'CMDM_option_max_categories_required';
    const OPTION_UPLOAD_CATEGORIES_FIELD_SCROLL = 'CMDM_upload_categories_field_scroll';
    const OPTION_MAX_CATEGORIES_NUM = 'CMDM_option_max_categories_num';
    const OPTION_SHOW_PASSWORD = 'CMDM_show_password';
    const OPTION_EXPIRATION = 'CMDM_show_expiration';
    const OPTION_EXPIRATION_DELETING_PERIOD = 'CMDM_expiration_deleting_period';
    const OPTION_PREVIEW_SUPPORT = 'CMDM_show_filepreview';
    const OPTION_PLAYER_SUPPORT = 'CMDM_show_musicplayer';
    const OPTION_PREVIEW_PLAYER_MASK_URL = 'CMDM_preview_player_mask_url';
    const OPTION_PLAYER_MASK_URL = 'CMDM_player_mask_url';
    const OPTION_PREVIEW_EACH_ATTACHMENT_ENABLE = 'CMDM_preview_each_attachment_enable';
    const OPTION_REQUIRE_NAME_EMAIL_SUPPORT = 'CMDM_require_name_email_support';
    const OPTION_ALLOW_SINGLE_DOWNLOAD_SUPPORT = 'CMDM_allow_single_download_support';
    const OPTION_ALLOW_SPECIFIC_FILES_DOWNLOAD_SUPPORT = 'CMDM_allow_specific_files_download_support';
    const OPTION_ASSOCIATE_ONLY_SUBCATEGORIES = 'CMDM_associate_only_subcategories';
    const OPTION_HIDE_THE_ONLY_CATEGORY = 'CMDM_hide_the_only_category';
    const OPTION_UPLOAD_AGREEMENT_TEXT = 'CMDM_upload_agreement_text';
    const OPTION_UPLOAD_LIMIT_ONLY_SINGLE_FILE = 'CMDM_upload_limit_only_single_file';
    const OPTION_SHOW_SHORTCODES_DOWNLOADS_EDIT_FRONTEND = 'CMDM_option_show_shortcodes_downloads_edit_frontend';
    const OPTION_UPLOAD_PAGE_SHOW_ID = 'CMDM_upload_page_show_id';
    const OPTION_WP_ADMIN_SHOW_ID_COLUMN = 'CMDM_wp_admin_show_download_id_col';
    const OPTION_UPLOAD_PAGE_SHOW_DIRECT_DOWNLOAD_LINK = 'CMDM_upload_page_show_direct_download_link';
    const OPTION_FORM_TITLE_POSITION = 'CMDM_form_title_position';
    const OPTION_FORM_DESCRIPTION_POSITION = 'CMDM_form_description_position';
    const OPTION_FORM_TYPE_POSITION = 'CMDM_form_type_position';
    const OPTION_FORM_FILE_POSITION = 'CMDM_form_file_position';
    const OPTION_FORM_CATEGORY_POSITION = 'CMDM_form_category_position';
    const OPTION_FORM_TAG_POSITION = 'CMDM_form_tag_position';
    const OPTION_FORM_EXCERPT_POSITION = 'CMDM_form_excerpt_position';
    const OPTION_FORM_NOTES_POSITION = 'CMDM_form_notes_position';
    const OPTION_FORM_SCREENSHOTS_POSITION = 'CMDM_form_screenshot_position';
    const OPTION_FORM_YOUTUBE_LINK_POSITION = 'CMDM_youtube_link_position';
    const OPTION_FORM_THUMBNAIL_POSITION = 'CMDM_form_thumbnail_position';
    const OPTION_FORM_PASSWORD_POSITION = 'CMDM_form_password_position';
    const OPTION_FORM_SINGLE_DOWNLOAD_POSITION = 'CMDM_form_single_download_position';
    const OPTION_FORM_SPECIFIC_FILES_POSITION = 'CMDM_form_specific_files_position';
    const OPTION_FORM_PREVIEW_POSITION = 'CMDM_form_preview_position';
    const OPTION_FORM_MEDIA_PLAYER_POSITION = 'CMDM_form_media_player_position';
    const OPTION_FORM_RECOMMENDED_POSITION = 'CMDM_form_recommended_position';
    const OPTION_FORM_NUMBER_OF_DOWNLOADS_POSITION = 'CMDM_form_number_of_downloads_position';
    const OPTION_FORM_NOTIFY_POSITION = 'CMDM_form_notify_position';
    const OPTION_FORM_RULE_FOR_NONLOGGED_POSITION = 'CMDM_form_rule_for_nonlogged_position';
    const OPTION_FORM_UPLOAD_ID_POSITION = 'CMDM_form_upload_id_position';
    const OPTION_FORM_VERSION_POSITION = 'CMDM_form_version_position';
    const OPTION_FORM_DIRECT_LINK_POSITION = 'CMDM_form_direct_link_position';
    const OPTION_FORM_AGREEMENT_POSITION = 'CMDM_form_agreement_position';
    const OPTION_FORM_EXPIRATION_POSITION = 'CMDM_form_expiration_position';
    const OPTION_FORM_CUSTOM_FIELD_POSITION = 'CMDM_form_custom_field_position';
    const OPTION_FORM_CUSTOM_FIELD2_POSITION = 'CMDM_form_custom_field2_position';
    const OPTION_FORM_CHANGELOG_POSITION = 'CMDM_form_changelog_position';
    const OPTION_FORM_INSTALLATION_POSITION = 'CMDM_form_installation_position';
    const OPTION_CREATE_CATEGORY_IN_SEPARATE_ROW = 'CMDM_form_create_category_in_separate_row';

    // SEO
    const OPTION_INDEX_META_TITLE = 'cmdm_index_meta_title';
    const OPTION_INDEX_META_DESC = 'cmdm_index_meta_desc';
    const OPTION_INDEX_META_KEYWORDS = 'cmdm_index_meta_keywords';
    const OPTION_NOINDEX_NON_CANONICAL = 'cmdm_noindex_non_canonical';
    const OPTION_NOINDEX_CONTRIBUTOR = 'cmdm_noindex_contributor';
    const OPTION_NO_YOAST_CATEGORIES = 'cmdm_no_yoast_categories';
    const OPTION_NO_YOAST_DOWNLOADS = 'cmdm_no_yoast_downloads';


    // Access
    const OPTION_ADDING_PERMISSIONS = 'CMDM_adding_permissions';
    //    const OPTION_VIEW_FILES = 'CMDM_view_files';
    const OPTION_VIEWING_PERMISSIONS = 'CMDM_viewing_permissions';
    const OPTION_VISIBILITY_PRECHECKED = 'CMDM_visibility_prechecked';
    const OPTION_PREVIEW_PLAYER_PERMISSIONS = 'CMDM_preview_player_permissions';
    const OPTION_DOWNLOADING_PERMISSIONS = 'CMDM_downloading_permissions';
    const OPTION_EDIT_OTHERS_DOWNLOADS_CAPABILITY = 'CMDM_edit_others_downloads_capability';
    const OPTION_APPROVING_NEW_UPLOADS_PERMISSIONS = 'CMDM_approving_new_uploads_permissions';
    const OPTION_APPROVING_NEW_UPLOADS_GROUPS = 'CMDM_approving_new_uploads_user_groups';
    const OPTION_BROWSE_WP_DIR_PERMISSIONS = 'CMDM_browse_wp_dir_permissions';
    const OPTION_INHERIT_CATEGORIES_ACCESS_RESTRICTIONS = 'CMDM_inherit_categories_access_restrictions';
    const OPTION_ALLOW_CREATE_CATEGORIES = 'CMDM_allow_create_categories';
    const OPTION_ADDING_USER_GROUPS = 'CMDM_adding_user_groups';
    const OPTION_VIEWING_USER_GROUPS = 'CMDM_viewing_user_groups';
    const OPTION_DOWNLOADING_USER_GROUPS = 'CMDM_downloading_user_groups';
    const OPTION_VIEWING_ROLES = 'CMDM_viewing_roles';
    const OPTION_ADDING_ROLES = 'CMDM_adding_roles';
    const OPTION_USE_ACCESS_DENIED_CUSTOM_PAGE = 'CMDM_use_access_denied_custom_page';
    const OPTION_ACCESS_DENIED_CUSTOM_PAGE = 'CMDM_access_denied_custom_page';

    const OPTION_ADMIN_ACCESS_ONLY = 'CMDM_admin_access_only';
    const OPTION_ALLOW_ADMIN_DELETE = 'CMDM_allow_admin_delete';
    const OPTION_SUBSCRIBERS_ACCESS = 'CMDM_subscribers_access';

    // Moderation
    const OPTION_APPROVE_DOWNLOADS = 'CMDM_approve_downloads';
    const OPTION_QUESTION_AUTO_APPROVE = 'CMDM_question_auto_approve';
    const OPTION_ANSWER_AUTO_APPROVE = 'CMDM_answer_auto_approve';
    const OPTION_AUTO_APPROVE_AUTHORS = 'CMDM_answer_authors_auto_approved';

    // Notifications Download
    const OPTION_NEW_DOWNLOAD_ADMIN_NOTIFICATION_EMAIL = 'CMDM_new_download_admin_notification_email';
    const OPTION_NEW_DOWNLOAD_MODERATOR_EMAIL_SUBJECT = 'CMDM_new_download_moderator_email_subject';
    const OPTION_NEW_DOWNLOAD_MODERATOR_EMAIL_BODY = 'CMDM_new_download_moderator_email_body';
    const OPTION_MODERATOR_APPROVED_EMAIL_ENABLE = 'CMDM_moderator_approved_email_enable';
    const OPTION_AUTO_APPROVED_EMAIL_ENABLE = 'CMDM_auto_approved_email_enable';
    const OPTION_MODERATOR_APPROVED_EMAIL_SUBJECT = 'CMDM_moderator_approved_email_subject';
    const OPTION_MODERATOR_APPROVED_EMAIL_BODY = 'CMDM_moderator_approved_email_body';
    const OPTION_ENABLE_CATEGORY_FOLLOWING = 'CMDM_enable_category_following';
    const OPTION_NEW_DOWNLOAD_NOTIFICATION_SUBJECT = 'CMDM_notification_subject';
    const OPTION_NEW_DOWNLOAD_NOTIFICATION_HIDE_EMAILS = 'CMDM_notification_hide_emails';
    const OPTION_NEW_DOWNLOAD_NOTIFICATION_BODY = 'CMDM_notification_body';
    const OPTION_ACCESS_NOTIFICATION_MODE = 'CMDM_access_notification_mode';
    const OPTION_NEW_DOWNLOAD_NOTIFY_USER_GROUPS = 'CMDM_new_download_notify_user_groups';
    const OPTION_NEW_DOWNLOAD_NOTIFY_ROLES = 'CMDM_new_download_notify_user_roles';
    const OPTION_EXPIRATION_NOTIFICATION = 'CMDM_expiration_notification';
    const OPTION_EXPIRATION_NOTIFICATION_SUBJECT = 'CMDM_expiration_notification_subject';
    const OPTION_EXPIRATION_NOTIFICATION_BODY = 'CMDM_expiration_notification_body';

    // Notifications Support Forum
    const OPTION_NEW_THREAD_NOTIFICATION = 'CMDM_new_thread_notification';
    const OPTION_NEW_THREAD_NOTIFICATION_TITLE = 'CMDM_new_thread_notification_title';
    const OPTION_THREAD_NOTIFICATION = 'CMDM_thread_notification';
    const OPTION_THREAD_NOTIFICATION_TITLE = 'CMDM_thread_notification_title';

    // Disclaimer
    const OPTION_CONTENT_DISCLAIMER = 'CMDM_option_content_disclaimer';
    const OPTION_CONTENT_DISCLAIMER_ACCEPT = 'CMDM_option_content_disclaimer_accept';
    const OPTION_CONTENT_DISCLAIMER_REJECT = 'CMDM_option_content_disclaimer_reject';
    const OPTION_CONTENT_DISCLAIMER_STATUS = 'CMDM_option_content_disclaimer_status';
    const OPTION_SHOW_DISCLAIMER_AFTER_LOGIN = 'CMDM_option_show_disclaimer_after_login';

    const OPTION_CUSTOM_CSS_URL = 'CMDM_custom_css_url';


    const SUPPORT_DISABLED = 0;
    const SUPPORT_ENABLED = 2;
    const SUPPORT_PER_DOWNLOAD = 1;

    // Mime type options
    const MIME_AUTO = 'auto';
    const MIME_BY_ARRAY = 'array';
    const MIME_FILE_INFO = 'info';
    const MIME_TYPE_FUNCTION = 'function';

    const SEARCH_INCLUDE_TAGS_DISABLE = 0;
    const SEARCH_INCLUDE_TAGS_SINGLE = 1;
    const SEARCH_INCLUDE_TAGS_MULTIPLE_ONE = 2;
    const SEARCH_INCLUDE_TAGS_MULTIPLE_ALL = 3;

    const ACCESS_NOTIFICATION_MODE_GROUPS = 1;
    const ACCESS_NOTIFICATION_MODE_USERS = 2;

    const INDEX_VIEW_TILES = 'tiles';
    const INDEX_VIEW_LIST = 'list';
    const INDEX_VIEW_CATEGORY = 'category';
    const INDEX_VIEW_TABLE = 'table';

    const CATEGORIES_LIST_NORMAL = 'normal';
    const CATEGORIES_LIST_TWO_LEVELS = 'two-levels';
    const CATEGORIES_LIST_TREE = 'tree';
    const CATEGORIES_LIST_HIDE = 'hide';

    const INDEX_CONTROLS_PLACE_TOP = 'top';
    const INDEX_CONTROLS_PLACE_BELOW_CATEGORIES = 'below-categories';

    const INDEX_ORDER_LAST_MODIFICATION = 'post_modified';
    const INDEX_ORDER_NEWEST = 'newest';
    const INDEX_ORDER_RATING = 'rating';
    const INDEX_ORDER_MANUAL = 'menu_order';
    const INDEX_ORDER_DOWNLOADS = 'downloads';
    const INDEX_ORDER_NAME_ASC = 'post_title';
    const INDEX_ORDER_NAME_DESC = 'post_title_desc';

    const ACCESS_NOBODY = 'nobody';
    const ACCESS_ALL = 'all';
    const ACCESS_USERS = 'loggedin';
    const ACCESS_USER_GROUPS = 'user_groups';

    const BACKEND_CATEGORY_FILTER_NORMAL = 'normal';
    const BACKEND_CATEGORY_FILTER_CHILDREN = 'children';

    const AREA_SINGLE_TOP = 'cmdm-single-top';
    const AREA_SINGLE_BOTTOM = 'cmdm-single-bottom';
    const AREA_SINGLE_SCREENSHOTS_BELOW = 'cmdm-single-screenshots-below';
    const AREA_SIDEBAR = 'cm-download-manager-sidebar';

    const LOG_DOWNLOADS_NO = 0;
    const LOG_DOWNLOADS_USERS = 1;
    const LOG_DOWNLOADS_ALL = 2;

    const DEFAULT_SCREENSHOT = '/views/resources/imgs/no_screenshot.png';

    //FTP
    const ATTACHMENT_STORAGE_TYPE = 'CMDM_general_remote_storage_settings';
    const FTP_SERVER = 'CMDM_ftp_server';
    const FTP_LOGIN = 'CMDM_ftp_login';
    const FTP_PASSWORD = 'CMDM_ftp_password';
    const FTP_PORT = 'CMDM_ftp_port';
    const FTP_SSL = 'CMDM_ftp_ssl';
    //    const FTP_PASSIVE_MODE = 'CMDM_ftp_passive_mode';
    const FTP_REMOTE_BASE_PATH = 'CMDM_ftp_target_path';
    const FTP_CHECK_CONNECTION = 'CMDM_ftp_check_connection';
    const FTP_TIMEOUT = 'CMDM_ftp_timeout';
    const FTP_SITE_PATH = 'CMDM_ftp_site_path';

    static $buildInTemplates = [
        'frontend' => 'Old-style default',
        'frontend-2016' => '2016 version',
        'frontend-2020' => '2020 beta version',
        'frontend-flat' => '2020 flat design',
    ];

    public static function AccessViewOptions() {

        return [
            'all' => CMDM_Labels::getLocalized('upload_visibility_all'),
            'loggedin' => CMDM_Labels::getLocalized('upload_visibility_loggedin'),
            'publish_posts' => CMDM_Labels::getLocalized('upload_visibility_publish_posts'),
            'edit_posts' => CMDM_Labels::getLocalized('upload_visibility_edit_posts'),
            'manage_options' => CMDM_Labels::getLocalized('upload_visibility_manage_options'),
            'roles' => CMDM_Labels::getLocalized('upload_visibility_roles'),
            'users' => CMDM_Labels::getLocalized('upload_visibility_users'),
            'author' => CMDM_Labels::getLocalized('upload_visibility_author')
        ];
    }


    public static function getOptionsConfig() {

        return apply_filters('cmdm_options_config', [

            // General Navigation
            self::OPTION_CMDOWNLOADS_SLUG => [
                'type' => self::TYPE_STRING,
                'default' => 'cmdownloads',
                'category' => 'general',
                'subcategory' => 'navigation',
                'title' => 'Default index page permalink part',
                'desc' => 'Enter the single part of the URL permalink (without slashes) to the default downloads index page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_CMDOWNLOAD_SLUG => [
                'type' => self::TYPE_STRING,
                'default' => 'cmdownload',
                'category' => 'general',
                'subcategory' => 'navigation',
                'title' => 'Default dashboard page permalink part',
                'desc' => 'Enter the single part of the URL permalink (without slashes) to the user\'s default downloads dashboard page. '
                    . 'Must be different from the downloads permalink above.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_UPLOADER_SLUG => [
                'type' => self::TYPE_STRING,
                'default' => 'uploader',
                'category' => 'general',
                'subcategory' => 'navigation',
                'title' => 'Uploader page permalink part',
                'desc' => 'Enter the single part of the URL permalink (without slashes) to the uploader page. Must be different than the other permalinks above.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SINGLE_DOWNLOAD_PAGE_USE_SLUG => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'general',
                'subcategory' => 'navigation',
                'title' => 'Use slug for the single download page permalinks',
                'desc' => 'If disabled, the permalink for a download page will not include the index page slug: <kbd>/document-page</kbd>.<br>'
                    . 'If enabled it will include the slug: <kbd>/cmdownloads/document-page</kbd>.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ADD_ADDONS_MENU => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'navigation',
                'title' => 'Add "Downloads" index page to the site menu',
                'desc' => 'Notice: you need to have "wp_nav_menu_items" filter in your theme template.',
            ],
            self::OPTION_ADD_DASHBOARD_MENU => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'navigation',
                'title' => 'Add "My Downloads" dashboard page to the site menu',
                'desc' => 'Notice: you need to have "wp_nav_menu_items" filter in your theme template.',
            ],
            self::OPTION_SEARCH_INCLUDE_CMDM => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'navigation',
                'title' => 'Include downloads in the standard WP search results',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SEARCH_INCLUDE_TAGS => [
                'type' => self::TYPE_RADIO,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'navigation',
                'title' => 'Search downloads by tags in the standard WP search',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ADD_WIZARD_PAGE => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'general',
                'subcategory' => 'navigation',
                'title' => 'Display "Setup Wizard" in plugin menu',
                'desc' => 'Uncheck this to remove Setup Wizard from plugin menu.'
            ],
            self::OPTION_REFERRAL_ENABLED => [
                'type' => static::TYPE_BOOL,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'referrals',
                'title' => 'Enable referrals',
                'desc' => 'Enable referrals link at the bottom of the download page.<br />'
                    . 'Refer new users to any of the CM Plugins and you\'ll receive <strong>a minimum of 16%</strong> of their purchase! '
                    . 'For more information please visit CM Plugins <a href="http://www.cminds.com/referral-program/" target="new">Affiliate page</a>.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_AFFILIATE_CODE => [
                'type' => static::TYPE_STRING,
                'default' => '',
                'category' => 'general',
                'subcategory' => 'referrals',
                'title' => 'Affiliate Code',
                'desc' => 'Please add your affiliate code in here.',
                'onlyin' => 'Pro'
            ],

            //

            self::CRON_ENABLE => [
                'type' => static::TYPE_BOOL,
                'default' => 1,
                'category' => 'general',
                'subcategory' => 'cron',
                'title' => 'Disable/enable Cron Settings for attachments',
                'desc' => 'Disable/enable Cron Settings for delete attachments without posts',
                'onlyin' => 'Pro'
            ],
            /*self::CRON_SETTINGS_TIME => array(
                'type' => static::TYPE_INT,
                'default' => 7,
                'category' => 'general',
                'subcategory' => 'cron',
                'title' => 'Select ',
                'desc' => 'Select how much hours will be check for delete attachments without posts',
            ),*/
            self::CRON_TIME_REMOVE_EXPIRED_DOWNLOADS_PERMANENTLY => [
                'type' => static::TYPE_BOOL,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'cron',
                'title' => 'Remove expired downloads permanently',
                'desc' => 'Enable this option if you want to remove expired downloads by cron permamently, without trash.',
                'onlyin' => 'Pro'
            ],

            // General Appearance
            self::OPTION_FRONTEND_TEMPLATE_DIR => [
                'type' => self::TYPE_SELECT,
                'default' => 'frontend',
                'options' => static::$buildInTemplates,
                'category' => 'general',
                'subcategory' => 'appearance',
                'title' => 'Style for the front-end',
                'desc' => 'Choose one of the build-in styles that will be applied on the CMDM front-end.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_LOOK_AND_FEEL_CSS => [
                'type' => self::TYPE_SELECT,
                'options' => ['' => '-- none --', '201704' => 'April 2017'],
                'category' => 'general',
                'subcategory' => 'appearance',
                'title' => 'Additional look and feel CSS for the front-end',
                'desc' => 'Choose one of the build-in additional CSS that will be applied on the CMDM front-end.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SUPPORT_THEME_DIR => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'general',
                'subcategory' => 'appearance',
                'title' => 'Support template overriding by theme dir',
                'desc' => 'If enabled, the templates will be overriden by using files in the "CMDM" directory created in your theme folder.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_GRAVATARS_SHOW => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'general',
                'subcategory' => 'appearance',
                'title' => 'Show gravatars next to user name',
                'onlyin' => 'Pro'
            ],
            self::OPTION_BACKEND_CATEGORY_FILTER_MODE => [
                'type' => self::TYPE_RADIO,
                'category' => 'general',
                'subcategory' => 'appearance',
                'title' => 'WP-admin Categories tree filter mode',
                'default' => 'normal',
                'onlyin' => 'Pro'
            ],
            self::OPTION_MAX_WIDTH_FOR_CMDM_CONTAINER => [
                'type' => self::TYPE_INT,
                'category' => 'general',
                'subcategory' => 'appearance',
                'title' => 'Max-width of the CMDM container (in %)',
                'default' => 70,
                'min' => 40,
                'max' => 80,
                'desc' => 'Set the maximum width of the Download Manager content area as a percentage of the page width. Applies to index, user dashboard, upload, and download pages. Valid range: from 40% to 80%.'
            ],

            // Logs
            static::OPTION_LOG_DOWNLOADS => [
                'type' => self::TYPE_RADIO,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'logs',
                'title' => 'Log downloads of',
                'onlyin' => 'Pro'
            ],
            static::OPTION_GEOLOCIATION_API_KEY => [
                'type' => self::TYPE_STRING,
                'category' => 'download',
                'subcategory' => 'logs',
                'title' => 'Geolocation API key',
                'desc' => 'Enter an ipinfodb.com IP Location API key to track the downloads locations in logs.'
                    . 'If you don\'t have an API key, <a href="http://ipinfodb.com/register.php" target="_blank">register new account</a>.',
                'onlyin' => 'Pro'
            ],
            static::OPTION_LIMIT_OF_EXPORT_ROWS => [
                'type' => self::TYPE_INT,
                'default' => 100,
                'category' => 'download',
                'subcategory' => 'logs',
                'title' => 'Limit of export rows',
                'desc' => '',
                'onlyin' => 'Pro'
            ],
            static::OPTION_EMAIL_ON_DOWNLOAD => [
                'type' => self::TYPE_STRING,
                'category' => 'download',
                'subcategory' => 'logs',
                'title' => 'Send to this email address the names and emails provided by non logged in users',
                'desc' => 'Enter the email address to send user\'s provided data. Emails will be sent only if checked relevant option in the download.',
                'onlyin' => 'Pro'
            ],

            // Index page
            self::OPTION_INDEX_PAGE_DISABLED => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'index',
                'title' => 'Disable index page',
                'desc' => 'If "Yes" is chosen, the index page won\'t be available and will display the standard 404 page. ' .
                    'The shortcodes such as cmdm-index will be still working.<br />You may also need to disable Index page menu.',
            ],
            self::OPTION_INDEX_PAGE_CUSTOM_PAGE_ID => [
                'type' => self::TYPE_INT,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'index',
                'title' => 'Downloads Index Page ID',
                'desc' => 'Select the ID of the page with the [cmdm-index] shortcode. All backlinks will be pointing to that page.',
            ],
            self::OPTION_SORTING_ORDER_BY => [
                'type' => self::TYPE_RADIO,
                'options' => [
                    self::INDEX_ORDER_NAME_ASC => 'name ascending',
                    self::INDEX_ORDER_NAME_DESC => 'name descending',
                    self::INDEX_ORDER_LAST_MODIFICATION => 'recently modified',
                    self::INDEX_ORDER_NEWEST => 'newest',
                    self::INDEX_ORDER_RATING => 'rating',
                    self::INDEX_ORDER_DOWNLOADS => 'downloads number',
                    self::INDEX_ORDER_MANUAL => 'manual',
                ],
                'default' => self::INDEX_ORDER_NEWEST,
                'category' => 'index',
                'subcategory' => 'index',
                'title' => 'Order by',
                'desc' => 'Set the default sorting order when user view the downloads index page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ADDONS_TITLE => [
                'type' => self::TYPE_STRING,
                'default' => 'CM Downloads',
                'category' => 'index',
                'subcategory' => 'index',
                'title' => 'Downloads listing title',
            ],
            self::OPTION_SEE_ONLY_ADMIN => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'files-list-appearance',
                'title' => 'Display only admin downloads to users',
                'desc' => 'if enabled user can see only his and admin downloads',
                'onlyin' => 'Pro'
            ],
            self::OPTION_PER_PAGE => [
                'type' => self::TYPE_INT,
                'default' => 10,
                'category' => 'index',
                'subcategory' => 'index',
                'title' => 'Downloads per page',
                'desc' => 'Number of download items to show per each page.',
                'onlyin' => 'Pro'
            ],

            // Appearance Login Widget
            self::OPTION_SHOW_INDEX_LOGIN_WIDGET => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'login',
                'title' => 'Display login widget on the index page',
                'desc' => 'Choose whether to show login widget on the index page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_LOGIN_WIDGET => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'login',
                'title' => 'Display login widget on the download page',
                'desc' => 'Choose whether to show login widget on the download page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_LOGIN_WIDGET_FORM => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'general',
                'subcategory' => 'login',
                'title' => 'Display login form on the login widget',
                'desc' => 'Choose whether to show login form on the login widget. If disabled, only social login will be displayed.',
                'onlyin' => 'Pro'
            ],


            // Upload
            self::OPTION_DASHBOARD_PAGE_DISABLED => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'appearance',
                'title' => 'Disable front-end Dashboard',
                'desc' => 'If "Yes" is chosen, users without manage_options capability won\'t be able to display the Dashboard index and the Add/Edit pages.<br />'
                    . 'Notice: you may also need to disable the Dashboard "My Download" menu.',
            ],
            self::OPTION_DASHBOARD_PAGE_CUSTOM_ID => [
                'type' => self::TYPE_INT,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'appearance',
                'title' => 'Downloads Dashboard Page ID',
                'desc' => 'Select the ID of the page with the [cmdm-my-downloads] shortcode. All backlinks will be pointing to that page.',
            ],
            self::OPTION_DASHBOARD_PAGE_TEMPLATE => [
                'type' => self::TYPE_SELECT,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'appearance',
                'title' => 'Page template for dasbhoard',
                'desc' => 'Choose the page template of the current theme or set default.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DASHBOARD_PAGE_SHORTCODE => [
                'type' => self::TYPE_SELECT,
                'default' => 'CMDM_MyDownloadsShortcode',
                'category' => 'upload',
                'subcategory' => 'appearance',
                'title' => 'Shortcode displayed on dashboard page',
                'desc' => 'Choose the shortcode to display on dashboard page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DASHBOARD_PAGE_TEMPLATE_OTHER => [
                'type' => self::TYPE_STRING,
                'category' => 'upload',
                'subcategory' => 'appearance',
                'title' => 'Other page template for dashboard',
                'desc' => 'Enter the other name of the page template if your template is not on the list above. '
                    . 'This option have priority over the selected page template. Leave blank to reset.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_REDIRECT_TO_INDEX_AFTER_UPLOAD => [
                'type' => self::TYPE_SELECT,
                'default' => 0,
                'options' => [
                    '0' => 'stay on edit page',
                    '1' => 'CMDM index page',
                    '2' => 'back to previous page',
                    '3' => 'open category page',
                ],
                'category' => 'upload',
                'subcategory' => 'other',
                'title' => 'Redirect after upload',
                'desc' => 'Select the page to redirect after new upload submit.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_WP_ADMIN_POST_EDIT_ENABLED => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'other',
                'title' => 'Enable wp-admin downloads edit',
                'desc' => 'If disabled, clicking on the Edit button in the wp-admin will redirect you to the CMDM Dashboard on the front-end. ' .
                    'If enabled, you will be able to edit downloads in the wp-admin dashboard - this may be useful to manage custom options ' .
                    'of the other plugins.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SCREENSHOTS_UPLOAD_USE_WP_MEDIA => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'upload',
                'subcategory' => 'other',
                'title' => 'Use regular Wordpress Media uploader for upload screenshots',
                'desc' => 'If enabled the standard WP Media uploader popup with the "Insert into post" button will be used to upload or select '
                    . 'the screenshot image.<br />If disabled - the old direct method will be used. However notice that the old method '
                    . 'may not work in all web browsers and it\'s not supported as well as the WP Media.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_UPLOAD_PAGE_SHOW_DIRECT_DOWNLOAD_LINK => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'other',
                'title' => 'Show direct download link',
                'desc' => 'If enabled the direct download link will be displayed on the upload page. '
                    . 'This link can be used to send it by email or in a chat message and it will '
                    . 'allow a receiver to download the file directly (after login and permission check).',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DASHBOARD_PAGINATION_LIMIT => [
                'type' => self::TYPE_INT,
                'default' => 20,
                'category' => 'upload',
                'subcategory' => 'appearance',
                'title' => 'Dashboard downloads per page',
                'desc' => 'Limit downloads displayed at the same time on the "My Downloads" dashboard page. Set 0 to disable limitation.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_SHORTCODES_DOWNLOADS_EDIT_FRONTEND => [
                'type' => self::TYPE_BOOL,
                'default' => false,
                'category' => 'upload',
                'subcategory' => 'appearance',
                'title' => 'Show available shortcodes on the edit page',
                'desc' => 'If enabled, available shortcodes will be shown on the downloads edit page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_UPLOAD_PAGE_SHOW_ID => [
                'type' => self::TYPE_BOOL,
                'default' => true,
                'category' => 'upload',
                'subcategory' => 'appearance',
                'title' => 'Show download ID on the upload page',
                'desc' => 'If enabled the download ID will be displayed on the upload page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_WP_ADMIN_SHOW_ID_COLUMN => [
                'type' => self::TYPE_BOOL,
                'default' => true,
                'category' => 'upload',
                'subcategory' => 'appearance',
                'title' => 'Show download ID column in wp-admin',
                'desc' => 'If enabled the download ID column will be displayed in the Downloads table in wp-admin.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DASHBOARD_FIX_CSS => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'appearance',
                'title' => 'Fix dashboard CSS',
                'desc' => 'Enable this option if you noticed issues with the WP Editor\'s CSS or the top user bar icons don\'t display on the upload page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ALLOWED_EXTENSIONS => [
                'type' => self::TYPE_CSV_LINE,
                'default' => ['zip', 'doc', 'docx', 'pdf', 'jpg', 'gif', 'png', 'jpeg'],
                'category' => 'upload',
                'subcategory' => 'features',
                'title' => 'Allowed file extensions',
                'desc' => 'Separate with comma. Define which file types are permitted to upload. Enter * for all file types (may not work in all web browsers).',
                ],
            self::OPTION_ALLOWED_TYPES => [
                'type' => self::TYPE_MULTICHECKBOX,
                'default' => ['file'],
                'category' => 'upload',
                'subcategory' => 'features',
                'title' => 'Type of download available for uploaders',
                'desc' => 'Define which options should the user have when adding new download.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_UPLOAD_BUTTON_LINK => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'features',
                'title' => 'Upload button redirects on upload page if enabled',
                'desc' => 'If enabled user will be redirected to an upload page, if disabled - upload form will be opened in frame.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_UPLOAD_LIMIT_ONLY_SINGLE_FILE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'features',
                'title' => 'Limit upload only single file per download resource',
                'desc' => 'If enabled user will be limited to upload only single file into the download resource. '
                    . 'If disabled user can upload multiple files to a single resource.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_CHILD_CATEGORIES_IN_DROPDOWN => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'categories',
                'title' => 'Show child categories in dropdown',
                'desc' => '',
            ],
            self::OPTION_CATEGORIES_REQUIRED => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'upload',
                'subcategory' => 'categories',
                'title' => 'Categories required',
                'afterSave' => function ($fieldName) {
                    if (CMDM_Settings::getOption($fieldName)) {
                        if ($term = get_term_by('name', 'Uncategorized', CMDM_Category::TAXONOMY)) {
                            wp_delete_term($term->term_id, 'cmdm_category');
                        }
                    } else {
                        wp_insert_term('Uncategorized', CMDM_Category::TAXONOMY);
                    }
                },
            ],
            self::OPTION_UPLOAD_CATEGORIES_FIELD_SCROLL => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'categories',
                'title' => 'Display categories in a scrollable box',
                'desc' => 'If you have many categories and it takes too much space you can display them in the scrollable box. '
                    . 'The filter input field will be displayed to allow user search categories in the convenient way.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_MAX_CATEGORIES_NUM => [
                'type' => self::TYPE_INT,
                'default' => 3,
                'category' => 'upload',
                'subcategory' => 'categories',
                'title' => 'Max number of categories',
                'desc' => 'Max number of categories to choose for download. Choose 0 to disable the limitation.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_PASSWORD => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'upload',
                'subcategory' => 'features',
                'title' => 'Enable password protection for downloads',
                'desc' => 'If disabled, password textbox will not be shown in add/edit forms.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_EXPIRATION => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'features',
                'title' => 'Enable download expiration',
                'desc' => 'If enabled, user can choose expiration date for the download',
                'onlyin' => 'Pro'
            ],
            self::OPTION_EXPIRATION_DELETING_PERIOD => [
                'type' => self::TYPE_INT,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'features',
                'title' => 'Time period after which, the Download will be deleted after first download (days)',
                'desc' => 'If enabled, then Download will be deleted after specified amount of days, after first download (enter 0 to disable)',
                'onlyin' => 'Pro'
            ],
            self::OPTION_REQUIRE_NAME_EMAIL_SUPPORT => [
                'type' => self::TYPE_RADIO,
                'options' => static::getSupportOptions(),
                'default' => self::SUPPORT_PER_DOWNLOAD,
                'category' => 'upload',
                'subcategory' => 'features',
                'title' => 'Require name and email support mode',
                'desc' => 'Choose whether the "Require non logged in users to provide name and email" option should be customizable '
                    . 'by the uploader or enabled/disabled for all downloads.',
                'onlyin' => 'Pro'
            ],
            /* Next two parameters had confused meaning.
            To correct that issue their titles were interchanged to reflect each parameter real purpose.
            For explicit reason parameters names left untouched.
            So now these two parameters have wrong names but right titles and descriptions.
            */
            self::OPTION_ALLOW_SINGLE_DOWNLOAD_SUPPORT => [
                'type' => self::TYPE_RADIO,
                'options' => static::getSupportOptions(),
                'default' => self::SUPPORT_PER_DOWNLOAD,
                'category' => 'upload',
                'subcategory' => 'features',
                'title' => 'Allow specific files download',
                'desc' => 'Choose whether the "Allow specific files download" option should be customizable by the uploader or enabled/disabled for all downloads. If this option is enabled and a download contains more than one file, file names will come as a list in frontend with tied checkboxes to allow the selection of files for downloading.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ALLOW_SPECIFIC_FILES_DOWNLOAD_SUPPORT => [
                'type' => self::TYPE_BOOL,
                'default' => false,
                'category' => 'upload',
                'subcategory' => 'features',
                'title' => 'Allow single file download',
                'desc' => 'If enabled, the users selection is limited by one file for download at once. File names will be listed by default within drop-down select control. Option can be overrided by specific download option.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ASSOCIATE_ONLY_SUBCATEGORIES => [
                'type' => self::TYPE_BOOL,
                'default' => false,
                'category' => 'upload',
                'subcategory' => 'categories',
                'title' => 'Associate downloads only with subcategories',
                'desc' => 'If enabled user won\'t be able to associate downloads with the top-level categories and only with the subcategories.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_HIDE_THE_ONLY_CATEGORY => [
                'type' => self::TYPE_BOOL,
                'default' => false,
                'category' => 'upload',
                'subcategory' => 'categories',
                'title' => 'Hide category selection field if category is single one',
                'desc' => 'If enabled, category selection field will be hidden when list of accessible categories for current user contains one category.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_UPLOAD_AGREEMENT_TEXT => [
                'type' => self::TYPE_TEXTAREA,
                'category' => 'upload',
                'subcategory' => 'other',
                'title' => 'Agreement text',
                'desc' => 'Enter the agreement text displayed on the upload page. User will be required to check the agreement checkbox before he upload a file. '
                    . 'Leave empty to disable.',
                'onlyin' => 'Pro'
            ],
            //Form Appearance
            self::OPTION_FORM_UPLOAD_ID_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 1,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => 'ID position',
                'desc' => 'Position of the ID row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_VERSION_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 2,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Version" position',
                'desc' => 'Position of the "Version" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_TITLE_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 3,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Title" position',
                'desc' => 'Position of the "Title" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_DESCRIPTION_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 4,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Description" position',
                'desc' => 'Position of the "Description" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_CATEGORY_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 5,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Category" position',
                'desc' => 'Position of the "Category" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_TYPE_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 6,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Download type" position',
                'desc' => 'Position of the "Download type" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_FILE_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 7,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Upload File" position',
                'desc' => 'Position of the "Upload File" row in the form',
                'onlyin' => 'Pro'
            ],

            self::OPTION_FORM_TAG_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 8,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Tag" position',
                'desc' => 'Position of the "Tag" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_EXCERPT_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 9,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Excerpt" position',
                'desc' => 'Position of the "Excerpt" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_NOTES_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 10,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Notes" position',
                'desc' => 'Position of the "Notes" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_YOUTUBE_LINK_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 11,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Youtube link" position',
                'desc' => 'Position of the "Youtube link" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_SCREENSHOTS_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 12,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Screenshots" position',
                'desc' => 'Position of the "Screenshots" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_THUMBNAIL_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 13,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Thumbnail" position',
                'desc' => 'Position of the "Thumbnail" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_PASSWORD_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 14,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Protect by password" position',
                'desc' => 'Position of the "Protect by password" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_SINGLE_DOWNLOAD_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 15,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Allow single file download" position',
                'desc' => 'Position of the "Allow single file download" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_SPECIFIC_FILES_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 16,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Allow selection of specific files" position',
                'desc' => 'Position of the "Allow selection of specific files" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_PREVIEW_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 17,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Enable Preview" position',
                'desc' => 'Position of the "Enable Preview" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_MEDIA_PLAYER_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 18,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Enable Media Player" position',
                'desc' => 'Position of the "Enable Media Player" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_RECOMMENDED_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 19,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Admin Recommended" position',
                'desc' => 'Position of the "Admin Recommended" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_NOTIFY_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 20,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Notify me on new support topics" position',
                'desc' => 'Position of the "Notify me on new support topics" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_RULE_FOR_NONLOGGED_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 21,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Require non logged in users to provide name and email" position',
                'desc' => 'Position of the "Require non logged in users to provide name and email" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_NUMBER_OF_DOWNLOADS_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 22,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Times downloaded" position',
                'desc' => 'Position of the "Times downloaded" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_INSTALLATION_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 23,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Installation" position',
                'desc' => 'Position of the "Installation" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_CHANGELOG_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 24,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Changelog" position',
                'desc' => 'Position of the "Changelog" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_CUSTOM_FIELD_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 25,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Custom Field" position',
                'desc' => 'Position of the "Custom Field" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_CUSTOM_FIELD2_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 26,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Custom Field2" position',
                'desc' => 'Position of the "Custom Field2" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_DIRECT_LINK_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 27,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Direct Download Link" position',
                'desc' => 'Position of the "Direct Download Link" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_AGREEMENT_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 28,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"Agreement text" position',
                'desc' => 'Position of the "Agreement text" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FORM_EXPIRATION_POSITION => [
                'type' => self::TYPE_INT,
                'default' => 29,
                'category' => 'upload',
                'subcategory' => 'form',
                'title' => '"File Expiration" position',
                'desc' => 'Position of the "File Expiration" row in the form',
                'onlyin' => 'Pro'
            ],
            self::OPTION_CREATE_CATEGORY_IN_SEPARATE_ROW => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'other',
                'title' => 'Show Add Category in a different row',
                'desc' => 'If this option enabled, the Category List and Add Category will be in different rows.',
                'onlyin' => 'Pro'
            ],

            // Upload Limits
            self::OPTION_UPLOAD_LIMIT_ENABLE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'limits',
                'title' => 'Enable upload limits per user',
                'desc' => 'Plugin will prevent from upload if user has uploaded N files in last X hours.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_UPLOAD_LIMIT_HOURS => [
                'type' => self::TYPE_INT,
                'default' => 1,
                'category' => 'upload',
                'subcategory' => 'limits',
                'title' => 'Limitation window [hours]',
                'desc' => 'Plugin will prevent from upload if user has uploaded N files in last X hours.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_UPLOAD_LIMIT_NUMBER => [
                'type' => self::TYPE_INT,
                'default' => 10,
                'category' => 'upload',
                'subcategory' => 'limits',
                'title' => 'Limit number of uploads',
                'onlyin' => 'Pro'
            ],


            // SEO
            self::OPTION_INDEX_META_TITLE => [
                'type' => self::TYPE_STRING,
                'category' => 'general',
                'subcategory' => 'seo',
                'title' => 'Title bar for index page',
                'desc' => 'Enter text which appears on the browser\'s title bar on the index page for better search engines positioning. '
                    . 'If blank then default title will be used.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_INDEX_META_DESC => [
                'type' => self::TYPE_STRING,
                'category' => 'general',
                'subcategory' => 'seo',
                'title' => 'Meta description for index page',
                'desc' => 'Enter the meta description for the index page for better search engines positioning.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_INDEX_META_KEYWORDS => [
                'type' => self::TYPE_STRING,
                'category' => 'general',
                'subcategory' => 'seo',
                'title' => 'Meta keywords',
                'desc' => 'Enter the meta keywords for better search engines positioning.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_NOINDEX_NON_CANONICAL => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'seo',
                'title' => 'Add noindex meta to the non-canonical pages',
                'desc' => 'If enabled, the &lt;meta name="robots" content="noindex"&gt; tag will be added to the non-canonical page, '
                    . 'for example the downloads list sorted by non-default parameter.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_NOINDEX_CONTRIBUTOR => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'seo',
                'title' => 'Add noindex meta to the contributor page',
                'desc' => 'If enabled, the &lt;meta name="robots" content="noindex"&gt; tag will be added to the contributor page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_NO_YOAST_CATEGORIES => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'seo',
                'title' => 'Exclude categories from Yoast sitemap',
                'desc' => 'If enabled, Yoast will collect CMDM categories to sitemap.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_NO_YOAST_DOWNLOADS => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'seo',
                'title' => 'Exclude downloads from Yoast sitemap',
                'desc' => 'If enabled, Yoast will collect CMDM downloads to sitemap.',
                'onlyin' => 'Pro'
            ],

            // Index Page Appearance
            self::OPTION_INDEX_PAGE_TEMPLATE => [
                'type' => self::TYPE_SELECT,
                'options' => [__CLASS__, 'getPageTemplatesOptions'],
                'default' => 'page.php',
                'category' => 'index',
                'subcategory' => 'layout',
                'title' => 'Page template for index',
                'desc' => 'Choose the page template of the current theme or set default.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_INDEX_PAGE_TEMPLATE_OTHER => [
                'type' => self::TYPE_STRING,
                'category' => 'index',
                'subcategory' => 'layout',
                'title' => 'Other page template for index',
                'desc' => 'Enter the other name of the page template if your template is not on the list above. '
                    . 'This option have priority over the selected page template. Leave blank to reset.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DEFAULT_VIEW => [
                'type' => self::TYPE_RADIO,
                'options' => [
                    self::INDEX_VIEW_TILES => 'tiles',
                    self::INDEX_VIEW_LIST => 'list',
                    self::INDEX_VIEW_CATEGORY => 'category (pro only)',
                    self::INDEX_VIEW_TABLE => 'table (pro only)',
                ],
                'default' => self::INDEX_VIEW_TILES,
                'category' => 'index',
                'subcategory' => 'layout',
                'title' => 'Default downloads list view',
                'desc' => 'Set the default view when user view the downloads index page.',
                //
            ],
            self::OPTION_CATEGORIES_LIST_TYPE => [
                'type' => self::TYPE_RADIO,
                'default' => self::CATEGORIES_LIST_NORMAL,
                'category' => 'index',
                'subcategory' => 'layout',
                'title' => 'Categories list type',
                'desc' => 'Choose how to display the categories list on the index page and the categories pages.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_INDEX_CONTROLS_PLACE => [
                'type' => self::TYPE_RADIO,
                'default' => self::INDEX_CONTROLS_PLACE_TOP,
                'category' => 'index',
                'subcategory' => 'layout',
                'title' => 'Show index controls',
                'onlyin' => 'Pro'
            ],
            self::OPTION_INDEX_SHOW_UNCATEGORIZED => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'layout',
                'title' => 'Show uncategorized downloads in categories view',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_DESCRIPTION => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'index',
                'subcategory' => 'files-list-appearance',
                'title' => 'Enable description on download list page',
                'desc' => 'If set will show download description below title.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_DESCRIPTION_LENGTH => [
                'type' => self::TYPE_INT,
                'default' => 20,
                'category' => 'index',
                'subcategory' => 'files-list-appearance',
                'title' => 'Limit number of description words',
                'desc' => 'Show number of words by default after that a read more link will appear.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_PLAYER => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'files-list-appearance',
                'title' => 'Enable to show player on download list page',
                'desc' => 'If set will show player widget bar below download title',
                'onlyin' => 'Pro'
            ],
            self::OPTION_INDEX_SHOW_DASHBOARD_LINKS => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Show dashboard links on the index page',
                'desc' => 'If enabled the "All downloads", "My downloads" and "Add new" links will be displayed on the index page.',
                //
            ],
            self::OPTION_INDEX_SHOW_ADMIN_LINKS => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Show admin links on the index page',
                'desc' => 'If option enabled and current user has admin rights, the "Unpublish", "Edit", "Delete" links will be displayed for each download on the index page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_AUTHOR_COUNTER => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Show author counters',
                'desc' => 'Decide to display number of uploads, support questions and answers below the author name.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_HIDE_ONLY_CATEGORY => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Hide categories if only one is defined',
                'desc' => 'If enabled, the categories region won\'t be visible on the index page when only one category has been defined.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_OPEN_CATEGORY_ON_CLICK => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Open category on click',
                'desc' => 'If enabled, clicked category in categories tree view widget ([cmdm-categories-tree] shortcode) will be opened to view its content.
				If disabled, click on category will open "New download" window to clicked category',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_INFO_LINK => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Show "Info" link',
                'desc' => 'If disabled, the info link next to download won\'t be shown.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_INDEX_DOWNLOAD_DATE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Show the last update date for each download',
                'onlyin' => 'Pro'
            ],
            self::OPTION_CATEGORY_SHOW_RECURSIVE => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Show downloads recursively',
                'desc' => 'If enabled, in a specified category will be displayed downloads from this category and all descendant categories.<br />' .
                    'If disabled, category will show only directly assigned downloads (as in the file system directories).',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_VIEW_TOGGLE_CONTROL => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Show view toggle control',
                'desc' => 'If enabled then toggle  view select element will be shown on CMDM index page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_INDEX_SORTBAR => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Show sorting options',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_TITLE_NUMBER => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Show total downloads next to the page title',
                'desc' => 'If enabled then next to the page or category title will be display number of total downloads found.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_LEVEL_UP_LINK => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Show "One level up" link',
                'desc' => 'Will show one level up when browsing categories.',
                'onlyin' => 'Pro'
            ],
            self::INDEX_PAGE_SEARCH_BAR => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Display search bar',
                //
            ],

            self::INDEX_PAGE_SEARCH_SUBMIT => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'appearance',
                'title' => 'Display search bar submit button',
                //
            ],

            self::FILES_LIST_TAGS_RELATION => [
                'type' => self::TYPE_RADIO,
                'default' => 'and',
                'category' => 'index',
                'subcategory' => 'files-list-appearance',
                'title' => 'Select relation for tags filter',
                'desc' => "Choose relation of tags from tags attribute within cmdm-files-list shortcode. <strong>AND</strong> filters downloads which have all tags from 'tags' attribute. <strong>OR</strong> means downloads which tagged by any tag from the list.",
                'onlyin' => 'Pro'
            ],
            self::FILES_LIST_TAGS_SCOPE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'files-list-appearance',
                'title' => 'Limit tags set by listed categories',
                'desc' => "If it's enabled, tags filter will contain tags relied to files of categories listed in <i>category</i> attribute.",
                'onlyin' => 'Pro'
            ],
            self::OPTION_HIDE_EYEBALL => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'files-list-appearance',
                'title' => 'Hide icon "Eyeball" into file\'s list',
                'desc' => 'If enabled, the "Eyeball" icon will be hidden from the file list.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ALLOW_BULK_DOWNLOAD => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'files-list-appearance',
                'title' => 'Allow bulk download',
                'desc' => 'If enabled, files list will have "bulk download" button to download user\'s files in certain category.',
                'onlyin' => 'Pro'
            ],

            self::OPTION_DOWNLOAD_PAGE_SHOW_DETAILS_IN_LIST => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'index',
                'subcategory' => 'files-list-appearance',
                'title' => 'Show Details link in Download List',
                'desc' => 'Enable this option if you want to show the "Details" button in the Download List',
                'onlyin' => 'Pro'
            ],

            // Download Page Appearance
            self::OPTION_DOWNLOAD_PAGE_TEMPLATE => [
                'type' => self::TYPE_SELECT,
                'default' => 'page.php',
                'category' => 'download',
                'subcategory' => 'layout',
                'title' => 'Page template for download',
                'desc' => 'Choose the page template of the current theme or set default.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DOWNLOAD_PAGE_TEMPLATE_OTHER => [
                'type' => self::TYPE_STRING,
                'category' => 'download',
                'subcategory' => 'layout',
                'title' => 'Other page template for download',
                'desc' => 'Enter the other name of the page template if your template is not on the list above. '
                    . 'This option have priority over the selected page template. Leave blank to reset.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DOWNLOAD_BUTTON_WIDGET_AREA => [
                'type' => self::TYPE_RADIO,
                'options' => [
                    self::AREA_SIDEBAR => 'aside',
                    self::AREA_SINGLE_TOP => 'top',
                    self::AREA_SINGLE_SCREENSHOTS_BELOW => 'below screenshots',
                    self::AREA_SINGLE_BOTTOM => 'bottom',
                ],
                'default' => self::AREA_SINGLE_SCREENSHOTS_BELOW,
                'category' => 'download',
                'subcategory' => 'layout',
                'title' => 'Download button position',
                'desc' => 'Choose where to display the download button widget.',
                //
            ],
            self::OPTION_SHOW_DETAILS_TABBED => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'layout',
                'title' => 'Use tabs to divide the download page',
                'desc' => 'If disabled, all information sections will be displayed one on top of the other.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DOWNLOAD_SHOW_DESCRIPTION => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'tabs',
                'title' => 'Show description',
                'desc' => 'If enabled, displays the description input on the upload page and the description content on the download page.',
                //
            ],
            self::OPTION_DOWNLOAD_SHOW_NOTES => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'tabs',
                'title' => 'Show notes',
                'desc' => 'If enabled, displays the notes input field on the upload page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DOWNLOAD_SHOW_EXCERPT => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'tabs',
                'title' => 'Show excerpt',
                'desc' => 'If enabled, displays the excerpt.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_CHANGELOG => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'tabs',
                'title' => 'Enable changelog panel for downloads',
                'desc' => 'If disabled, changelog textbox will not be shown also in add/edit forms.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_INSTALLATION => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'tabs',
                'title' => 'Enable installation panel for downloads',
                'desc' => 'If disabled, installation textbox will not be shown also in add/edit forms.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_TAB_VIDEO_DEFAULT_VISIBLE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'tabs',
                'title' => 'Video tab visible as default',
                'desc' => 'If download contains a video attachment then the video tab will be visible by default instead of description tab.',
                'onlyin' => 'Pro'
            ],

            self::OPTION_SHOW_VERSION => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'appearance',
                'title' => 'Enable version parameter for downloads',
                'desc' => 'If disabled, version textbox will not be shown also in add/edit forms.',
                //
            ],
            self::OPTION_SHOW_DOWNLOAD_PAGE_BACKLINK => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'appearance',
                'title' => 'Show "Back to All Downloads" link',
                //
            ],
            self::OPTION_CUSTOM_BACKLINK => [
                'type' => self::TYPE_STRING,
                'default' => '',
                'category' => 'download',
                'subcategory' => 'appearance',
                'title' => 'Custom backlink URL',
                'desc' => 'Enter custom URL or leave empty to disable',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ENABLE_TAGS => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'appearance',
                'title' => 'Enable tags',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ENABLE_RATING => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'appearance',
                'title' => 'Enable rating',
                //
            ],
            self::OPTION_SHOW_AUTHOR => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'appearance',
                'title' => 'Show author',
                'desc' => 'If disabled, then no author info will be available.',
                //
            ],
            self::OPTION_REQUIRE_DESCRIPTION => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'features',
                'title' => 'Require description',
                'desc' => 'If enabled and the "Show description" option is enabled, the description will be required to create new download.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_UPLOAD_RICH_TEXT_EDITOR_ENABLE => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'upload',
                'subcategory' => 'features',
                'title' => 'Enable rich text editor',
                'desc' => 'If enabled the WP Editor will be used for the description/installation/changelog fields. '
                    . 'If disabled then only a simple HTML textarea fields will be available.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ADD_SCREENSHOTS_TO_UPLOADS => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'upload',
                'subcategory' => 'features',
                'title' => 'Add screenshots to uploads',
                'desc' => 'If enabled, then all screenshots attached to current downloads will be added to zip file',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_DOWNLOADS_NUMBER => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'appearance',
                'title' => 'Show downloads number',
                //
            ],
            self::OPTION_SHOW_VIEWS_NUMBER => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'appearance',
                'title' => 'Show views number',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_REPORT_BTN => [
                'type' => self::TYPE_RADIO,
                'default' => self::ACCESS_NOBODY,
                'options' => [
                    self::ACCESS_NOBODY => 'Nobody',
                    self::ACCESS_USERS => 'Logged-in users',
                    self::ACCESS_ALL => 'All users including guests',
                ],
                'category' => 'download',
                'subcategory' => 'appearance',
                'title' => 'Show report button to',
                'desc' => 'If enabled, the report button will be displayed below the download button on the download\'s page. ' .
                    'User will be able to report spam or something else and he will be required to enter the reason.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_ONLY_ADMINS_IN_FILTER => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'appearance',
                'title' => 'Show only administators in author filter list',
                'desc' => '',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SELECT_ALL_FILES_OF_SINGLE_DOWNLOAD => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'appearance',
                'title' => 'Select all files of single download by default',
                'desc' => 'If this option enabled, all files in list will be selected for downloading, otherwise all will be deselected. This option works when "Allow specific files download" is enabled.',
                'onlyin' => 'Pro'
            ],

            // Download Page Sidebar
            self::OPTION_DOWNLOAD_BLOCK_ON_TOP => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'sidebar',
                'title' => 'Place the Download block on top',
                'desc' => 'If enabled, the Download block with the download button will be displayed before the Details block.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_SOCIAL => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'sidebar',
                'title' => 'Show social share widget',
                'desc' => 'If enabled, social share widget and icons will be shown on the download page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_RELATED => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'sidebar',
                'title' => 'Show related downloads widget',
                'desc' => 'If enabled, the related downloads widget will be shown on the download page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SIDEBAR_BEFORE_WIDGET => [
                'type' => self::TYPE_STRING,
                'category' => 'download',
                'subcategory' => 'sidebar',
                'title' => 'HTML before widget block',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SIDEBAR_BEFORE_TITLE => [
                'type' => self::TYPE_STRING,
                'category' => 'download',
                'subcategory' => 'sidebar',
                'title' => 'HTML before widget title',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SIDEBAR_AFTER_TITLE => [
                'type' => self::TYPE_STRING,
                'category' => 'download',
                'subcategory' => 'sidebar',
                'title' => 'HTML after widget title',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SIDEBAR_AFTER_WIDGET => [
                'type' => self::TYPE_STRING,
                'category' => 'download',
                'subcategory' => 'sidebar',
                'title' => 'HTML after widget block',
                'onlyin' => 'Pro'
            ],

            // Download Page Screenshots
            self::OPTION_ALLOW_SCREENSHOTS => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'screenshots',
                'title' => 'Enable screenshots',
                'desc' => 'If disabled, screenshots won\'t be displayed on the download page.',
                //
            ],
            self::OPTION_SET_FIRST_IMAGE_AS_FETATURED => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'screenshots',
                'title' => 'First screenshot as the featured image',
                'desc' => 'If enabled the first screenshot will be set as the featured image by default.',
                //
            ],
            self::OPTION_HIDE_THUMB_SCREENSHOTS => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'screenshots',
                'title' => 'Hide featured image in screenshots',
                'desc' => 'If enabled, the featured image will be not shown on the screenshots area.',
                //
            ],
            self::OPTION_THUMB_FROM_CATEGORY => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'screenshots',
                'title' => 'Display category icon if no thumbnail available',
                'desc' => 'You can set icon per each category. If no thumbnail available for the download '
                    . 'then the plugin will display category\'s icon.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_SLIDESHOW => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'screenshots',
                'title' => 'Enable slideshow for download screenshots',
                'desc' => 'If disabled, only single screenshot will be displayed.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SLIDESHOW_AUTOPLAY => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'screenshots',
                'title' => 'Enable slideshow autoplay',
                'desc' => 'If disabled, images on download page will not automaticly rotate.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DEFAULT_SCREENSHOT => [
                'type' => self::TYPE_CUSTOM,
                'default' => CMDM_URL . static::DEFAULT_SCREENSHOT,
                'category' => 'download',
                'subcategory' => 'screenshots',
                'title' => '"No screenshot" image',
                'desc' => 'Set the default no screenshot image.',
                'content' => function ($name, $value, $config) {
                    return CMDM_BaseController::_loadView('../backend/settings/screenshot-default', compact('name', 'value'));
                },
                //
            ],
            self::OPTION_ENABLE_YOUTUBE_LINK => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'screenshots',
                'title' => 'Enable youtube video',
                'desc' => 'Allows to attach youtube video to download and set it at screenshot area.',
                'onlyin' => 'Pro'
            ],

            // Download Page Support
            self::OPTION_SUPPORT_SHOW => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'support',
                'title' => 'Enable support tab for downloads',
                'desc' => 'Support tab will be shown in each download page.',
                //
            ],
            self::OPTION_THREAD_NOTIFICATIONS_SUPPORT => [
                'type' => self::TYPE_RADIO,
                'options' => static::getSupportOptions(),
                'default' => self::SUPPORT_PER_DOWNLOAD,
                'category' => 'download',
                'subcategory' => 'support',
                'title' => 'Support forum notifications mode',
                'desc' => 'Choose whether the "Notify me on new support topics" option should be customizable by the uploader or enabled/disabled for all downloads.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SUPPORT_THREAD_SORTING => [
                'type' => self::TYPE_RADIO,
                'options' => ['ASC' => 'Ascending', 'DESC' => 'Descending'],
                'default' => 'DESC',
                'category' => 'download',
                'subcategory' => 'support',
                'title' => 'Support comments sorting order',
                'desc' => 'Sorting by comment time.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DEBUG_EMAIL => [
                'type' => self::TYPE_CUSTOM,
                'category' => 'notifications',
                'subcategory' => 'other',
                'title' => 'Test sending emails',
                'desc' => 'Use this feature if you don\'t receive the email from the plugin to test if your Wordpress is sending emails properly.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_EXPIRATION_NOTIFICATION => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'notifications',
                'subcategory' => 'other',
                'title' => 'Enable notification about file expiration',
                'desc' => 'Enable notification about file expiration after first file downloading<br /> <strong>Works if "Time period after which, the Download will be deleted after first download" is enabled</strong>',
                'onlyin' => 'Pro'
            ],
            self::OPTION_EXPIRATION_NOTIFICATION_SUBJECT => [
                'type' => self::TYPE_STRING,
                'default' => '[[blogname]] Thank you for downloading file - [title]',
                'category' => 'notifications',
                'subcategory' => 'other',
                'title' => 'New downloading notification subject',
                'desc' => 'Subject for the notification email send after first downloading.<br />'
                    . 'You can use the following shortcodes:<br />[blogname] - name of the blog<br />[title] - new download title<br />'
                    . '[link] - link to download page<br />[author] - uploader name',
                'onlyin' => 'Pro'
            ],
            self::OPTION_EXPIRATION_NOTIFICATION_BODY => [
                'type' => self::TYPE_TEXTAREA,
                'default' => "Hi,\nThank you for downloading the file\n[title] will be removed from server on [expiration]\n\n[link]",
                'category' => 'notifications',
                'subcategory' => 'other',
                'title' => 'New downloading notification body',
                'desc' => 'Body for the notification email send after first downloading.<br />'
                    . 'You can use the following shortcodes:<br />[blogname] - name of the blog<br />[title] - new download title<br />'
                    . '[link] - link to download page<br />[author] - uploader name<br />[expiration] - expiration date',
                'onlyin' => 'Pro'
            ],

            // Downloads Limits
            self::OPTION_DOWNLOAD_LIMIT_ENABLE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'limits',
                'title' => 'Enable download limits per user',
                'desc' => 'Plugin will prevent from download if user has downloaded N files in last X hours.'
                    . '<br><strong>Notice:</strong> this will work only when you enable the Logs option.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DOWNLOAD_LIMIT_HOURS => [
                'type' => self::TYPE_INT,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'limits',
                'title' => 'Limitation window [hours]',
                'desc' => 'Plugin will prevent from download if user has downloaded N files in last X hours.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DOWNLOAD_LIMIT_NUMBER => [
                'type' => self::TYPE_INT,
                'default' => 10,
                'category' => 'download',
                'subcategory' => 'limits',
                'title' => 'Limit number of downloads',
                'onlyin' => 'Pro'
            ],

            // Download Page Misc
            self::OPTION_FORCE_BROWSER_DOWNLOAD_ENABLED => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'misc',
                'title' => 'Force browser to download files',
                'desc' => 'If disabled, browser will open some file types instead of downloading it, for example PDF and images.',
            //
            ],
            self::OPTION_BYPASS_PHP_FILESIZE => [
                'type' => self::TYPE_INT,
                'default' => 9999999,
                'category' => 'download',
                'subcategory' => 'misc',
                'title' => 'Bypass PHP for files larger than [MB]',
                'desc' => 'If file is larger than set size, the user will be redirected to the file URL address so browser could download file directly.<br />'
                    . 'Set 0 to bypass PHP always.<br /><strong>Notice:</strong> don\'t use htaccess restrictions in order to allow users download '
                    . 'files directly from the web server.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_KEEP_ORIGINAL_FILE_NAME => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'misc',
                'title' => 'Keep the original file name',
                'desc' => 'If enabled when user downloads the file then the original file name will be served. '
                    . 'Otherwise the downloaded file title will be the same as the download\'s title. This option works only when didn\'t bypassed PHP.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_FILE_NAME_IN_DOWNLOAD_FORM_URL => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'misc',
                'title' => 'Add file name to the download form URL',
                'desc' => 'If enabled then the file name with extension will be added to the download form URL. '
                    . 'Disable this option if you experience issues with redirections or the download doesn\'t work.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_REMOVE_WPSEO_HEAD_ACTION => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'misc',
                'title' => 'Remove WPSEO_HEAD action',
                'desc' => 'Set to true by default. Try to disable if the shortcode removes some of hooks.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_INCREMENT_VIEWS => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'misc',
                'title' => 'Increment number of views every time the page is refreshed',
                'desc' => 'If enabled, views counter will be increased every time the page is refreshed. If disabled, cookie will be set to block user '
                    . 'from increasing the counter on current machine.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DIRECT_DOWNLOAD_LINK_ENABLE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'misc',
                'title' => 'Enable direct download links',
                'desc' => 'If enabled you will be able to get a link to download a file directly by it\'s ID.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_MIME_TYPE_FUNCTION => [
                'type' => self::TYPE_RADIO,
                'options' => static::getMimeTypeOptions(),
                'default' => self::MIME_AUTO,
                'category' => 'download',
                'subcategory' => 'misc',
                'title' => 'Choose the way of getting file mime type',
                'desc' => 'Different functions for getting files mime type (for developers):'
                    . '<br>  Auto - Check many params'
                    . '<br>  Using array - Get mime type by parsing file title and comparing it with an array of mime types'
                    . '<br>  File Info - Using File Info class'
                    . '<br>  mime_content_type - Using mime_content_type function',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DOWNLOAD_DESCRIPTION_PARSE_SHORTCODES => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'appearance',
                'title' => 'Parse shortcodes in the description',
                'desc' => 'Enable this option if you want to use the WP shortcodes in the download\'s description. '
                    . 'If disabled the shortcodes won\'t be processed.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DOWNLOAD_TAGS_IN_LIST_ENABLED => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'files-list-appearance',
                'title' => 'Show tags list in files-list item description',
                'desc' => 'Enable to see the tags implemented for the download. Disabled by default. '
                    . 'Disabled by default.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHORTCODES_WHITELIST => [
                'type' => self::TYPE_CSV_LINE,
                'category' => 'download',
                'subcategory' => 'appearance',
                'title' => 'Shortcodes whitelist',
                'desc' => 'You can allow to use only specific shortcodes (if parsing shortcodes option is enabled). '
                    . 'Please enter only shortcode names (without brackets) separated by comma. Leave empty to allow using any shortcode.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DOWNLOAD_PAGE_DISABLED => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'layout',
                'title' => 'Disable download page',
                'desc' => 'Notice: you may also need to disable the index page.',
                'onlyin' => 'Pro'
            ],

            // Custom fields
            self::OPTION_CUSTOM_FIELD1 => [
                'type' => self::TYPE_STRING,
                'category' => 'download',
                'subcategory' => 'fields',
                'title' => 'Custom field #1',
                'desc' => 'Enter the custom field label.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_CUSTOM_FIELD2 => [
                'type' => self::TYPE_STRING,
                'category' => 'download',
                'subcategory' => 'fields',
                'title' => 'Custom field #2',
                'desc' => 'Enter the custom field label.',
                'onlyin' => 'Pro'
            ],

            // Download Preview & player
            self::OPTION_AUDIO_PLAYER => [
                'type' => self::TYPE_RADIO,
                'options' => [
                    'dewplayer' => 'dewplayer',
                    'jplayer' => 'jPlayer',
                    'audio' => 'WP core audio',
                    'braudio' => 'Browser audio element',
                    'sound_manager' => 'SoundManager 2'
                ],
                'default' => 'dewplayer',
                'category' => 'download',
                'subcategory' => 'preview',
                'title' => 'Choose which audio player should be shown',
                'desc' => CMDM_Settings::__('If music player is not enabled, this setting will have no effect.') . '<br />' .
                    CMDM_Settings::__('Read description of each player:') . '
							<a href="http://www.alsacreations.fr/dewplayer.html">dewplayer</a>,
							<a href="http://jplayer.org/">jPlayer</a>,
							<a href="http://make.wordpress.org/core/2013/04/08/audio-video-support-in-core/">WP core audio</a>
							<br /><br />' . CMDM_Settings::__('WARNING: It\'s known that jPlayer and core audio player won\'t work on some mp3 files.'),
                'onlyin' => 'Pro'
            ],
            self::OPTION_PREVIEW_SUPPORT => [
                'type' => self::TYPE_RADIO,
                'options' => static::getSupportOptions(),
                'default' => self::SUPPORT_PER_DOWNLOAD,
                'category' => 'download',
                'subcategory' => 'preview',
                'title' => 'Preview support mode',
                'desc' => 'Choose whether the download preview should be customizable by the uploader or enabled/disabled for all downloads.'
                    . '<br /><br /><strong>Notice:</strong> if you are using the htaccess protection, add your documents\' '
                    . 'extensions to the regexp:'
                    . '<pre style="background:#f0f0f0;padding:5px;">&lt;FilesMatch "\.(?i:jpg|jpeg|png|gif|webp<strong>|pdf|doc|docx</strong>)$"&gt;<br />&nbsp;&nbsp;&nbsp;Allow from all<br />&lt;/FilesMatch&gt;</pre>',
                'onlyin' => 'Pro'
            ],
            self::OPTION_PREVIEW_EACH_ATTACHMENT_ENABLE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'download',
                'subcategory' => 'preview',
                'title' => 'Enable preview for each attachment',
                'desc' => 'If enabled, users will be able to preview each attachment in the download resource separately.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_PLAYER_SUPPORT => [
                'type' => self::TYPE_RADIO,
                'options' => static::getSupportOptions(),
                'default' => self::SUPPORT_PER_DOWNLOAD,
                'category' => 'download',
                'subcategory' => 'preview',
                'title' => 'Player support mode',
                'desc' => 'Choose whether the download player should be customizable by the uploader or enabled/disabled for all downloads.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_PREVIEW_PLAYER_MASK_URL => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'download',
                'subcategory' => 'preview',
                'title' => 'Mask URL address of the original file for preview and player',
                'desc' => 'If enabled the files for preview will be served through the PHP script which will mask their actual source URL. '
                    . 'If disabled the file will be served directly from HTTP server.',
                'onlyin' => 'Pro'
            ],

            // Moderation Downloads
            self::OPTION_APPROVE_DOWNLOADS => [
                'type' => self::TYPE_BOOL,
                'default' => 1,
                'category' => 'access',
                'subcategory' => 'downloads',
                'title' => 'Auto-approve new downloads',
                'onlyin' => 'Pro'
            ],

            // Moderation support forum
            self::OPTION_QUESTION_AUTO_APPROVE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'access',
                'subcategory' => 'moderation_forum',
                'title' => 'Auto-approve new questions',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ANSWER_AUTO_APPROVE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'access',
                'subcategory' => 'moderation_forum',
                'title' => 'Auto-approve new answers',
                'onlyin' => 'Pro'
            ],
            self::OPTION_AUTO_APPROVE_AUTHORS => [
                'type' => self::TYPE_USERS_LIST,
                'category' => 'access',
                'subcategory' => 'moderation_forum',
                'title' => 'Auto-approve questions and answers from following users',
                'onlyin' => 'Pro'
            ],

            // Access
            self::OPTION_ADDING_PERMISSIONS => [
                'type' => self::TYPE_SELECT,
                'options' => [
                    self::ACCESS_USERS => 'Logged-in users',
                    'roles' => 'Users from specific WP roles',
                    '1' => 'Users with "edit_posts" capability',
                    '2' => 'Users with "publish_posts" capability',
                    '3' => 'Users with "manage_options" capability',
                    self::ACCESS_USER_GROUPS => 'Users from specific CMDM user groups',
                ],
                'default' => self::ACCESS_USERS,
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Who can add new downloads',
                'desc' => 'You can specify which users will have access to dashboard.',
            ],
            self::OPTION_ADDING_ROLES => [
                'type' => self::TYPE_MULTICHECKBOX,
                'options' => function () {
                    return CMDM_Settings::getRolesOptions();
                },
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Which WP roles can add new downloads',
            ],
            self::OPTION_ADDING_USER_GROUPS => [
                'type' => self::TYPE_MULTICHECKBOX,
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Which groups can add new downloads',
                'onlyin' => 'Pro'
            ],
            self::OPTION_VIEWING_PERMISSIONS => [
                'type' => self::TYPE_SELECT,
                'options' => [
                    self::ACCESS_ALL => 'All users (including guests)',
                    self::ACCESS_USERS => 'Logged-in users',
                    'roles' => 'Users from specific WP roles',
                    'author' => 'Only author',
                    'edit_posts' => 'Users with "edit_posts" capability',
                    'publish_posts' => 'Users with "publish_posts" capability',
                    'manage_options' => 'Users with "manage_options" capability',
                    'user' => 'Let download author decide',
                    self::ACCESS_USER_GROUPS => 'Users from specific CMDM user groups',
                ],
                'default' => self::ACCESS_ALL,
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Who can view downloads',
                'desc' => 'You can specify which users will have read-only access to downloads.',
            ],
            self::OPTION_VIEWING_ROLES => [
                'type' => self::TYPE_MULTICHECKBOX,
                'options' => function () {
                    return CMDM_Settings::getRolesOptions();
                },
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Which WP roles can view downloads',
            ],

            self::OPTION_VISIBILITY_PRECHECKED => [
                'type' => self::TYPE_SELECT,
                'options' => self::AccessViewOptions(),
                'default' => 'all',
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Prechecked option for Visibility select',
                'desc' => 'Define which option should be prechecked if "Who can view downloads" set to "Let download author decide"',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ADMIN_ACCESS_ONLY => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Allow only admin to view subscriber\'s downloads',
                'desc' => 'If enabled only admins will be able to view subscriber downloads.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SUBSCRIBERS_ACCESS => [
                'type' => self::TYPE_MULTICHECKBOX,
                'options' => self::AccessViewOptions(),
                'default' => array_keys(self::AccessViewOptions()),
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Upload options for Subscribers',
                'desc' => 'Define from which view options subscribers can choosing while adding new download<br>
                <strong>Working only if "Who can view downloads" option is set as "Let download author decide"</strong>',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ALLOW_ADMIN_DELETE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Allow admin to delete files even if he can\'t edit it',
                'desc' => 'If enabled ALL admins will be able to delete subscriber downloads.',
                'onlyin' => 'Pro'
            ],

            self::OPTION_VIEWING_USER_GROUPS => [
                'type' => self::TYPE_MULTICHECKBOX,
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Which groups can view downloads',
                'onlyin' => 'Pro'
            ],
            self::OPTION_PREVIEW_PLAYER_PERMISSIONS => [
                'type' => self::TYPE_SELECT,
                'options' => [
                    self::ACCESS_ALL => 'All users (including guests)',
                    self::ACCESS_USERS => 'Logged-in users',
                    'edit_posts' => 'Users with "edit_posts" capability',
                    'publish_posts' => 'Users with "publish_posts" capability',
                    'manage_options' => 'Users with "manage_options" capability',
                ],
                'default' => self::ACCESS_ALL,
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Who can use preview and use media player',
                'desc' => 'You can specify which users will have permission to preview files and use media player - even if all users can view the download page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_DOWNLOADING_PERMISSIONS => [
                'type' => self::TYPE_SELECT,
                'options' => [
                    self::ACCESS_ALL => 'All users (including guests)',
                    self::ACCESS_USERS => 'Logged-in users',
                    'edit_posts' => 'Users with "edit_posts" capability',
                    'publish_posts' => 'Users with "publish_posts" capability',
                    'manage_options' => 'Users with "manage_options" capability',
                    self::ACCESS_USER_GROUPS => 'Users from specific CMDM user groups',
                    'user' => 'Let download author decide',
                ],
                'default' => self::ACCESS_ALL,
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Who can download files',
                'desc' => 'You can specify which users will have permission to download files - even if all users can view the download page.',
                //
            ],
            self::OPTION_DOWNLOADING_USER_GROUPS => [
                'type' => self::TYPE_MULTICHECKBOX,
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Which CMDM groups can download',
                'onlyin' => 'Pro'
            ],
            self::OPTION_EDIT_OTHERS_DOWNLOADS_CAPABILITY => [
                'type' => self::TYPE_STRING,
                'default' => 'manage_options',
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'WP capability required to edit other users\' downloads',
                'desc' => 'You can specify which Wordpress capability will allow users to edit the other users\'s downloads. '
                    . 'By default it\'s only admin with manage_options capability.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_APPROVING_NEW_UPLOADS_PERMISSIONS => [
                'type' => self::TYPE_SELECT,
                'options' => [
                    'manage_options' => 'Users with "manage_options" capability',
                    self::ACCESS_USER_GROUPS => 'Users from specific CMDM user groups',
                ],
                'default' => 'manage_options',
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Who can approve new uploads',
                'desc' => 'You can specify which users will have permission to approve new files.<br /><strong>Warning:</strong> If you choose CMDM groups, users in this group have to have the capability "edit_others_posts"',
                'onlyin' => 'Pro'
            ],
            self::OPTION_APPROVING_NEW_UPLOADS_GROUPS => [
                'type' => self::TYPE_MULTICHECKBOX,
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Which groups can approve uploads',
                'onlyin' => 'Pro'
            ],
            self::OPTION_BROWSE_WP_DIR_PERMISSIONS => [
                'type' => self::TYPE_SELECT,
                'options' => [
                    self::ACCESS_USERS => 'Logged-in users',
                    'edit_posts' => 'Users with "edit_posts" capability',
                    'publish_posts' => 'Users with "publish_posts" capability',
                    'manage_options' => 'Users with "manage_options" capability',
                    'nobody' => 'Nobody',
                ],
                'default' => 'manage_options',
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Who can use "Browse WP upload dir" on the upload page',
                'desc' => 'You can specify which users will have permission to use the server-side file browser when adding or editting a file.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_INHERIT_CATEGORIES_ACCESS_RESTRICTIONS => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Inherit parent\'s access restrictions in subcategories',
                'desc' => 'If enabled, the subcategories without own access restrictions will inherit the access settings from the parent category.<br />'
                    . '<strong>Warning:</strong> this option is in the beta mode and some restricted documents can be still listed in widgets '
                    . 'and included in counters.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ALLOW_CREATE_CATEGORIES => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Allow users to create categories',
                'desc' => 'If enabled, users will be allowed to create new categories on the Upload Page.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_USE_ACCESS_DENIED_CUSTOM_PAGE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Use the custom WP page for the Access Denied view',
                'desc' => 'If enabled, users which have no access to the resources will be redirected to the custom Wordpress page "Access denied"'
                    . ' which you can customize. The page will be created automaticaly.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ACCESS_DENIED_CUSTOM_PAGE => [
                'type' => self::TYPE_SELECT,
                'default' => 0,
                'options' => ['0' => 'Access denied custom page', '1' => 'Site home page', '2' => 'Site login page'],
                'category' => 'access',
                'subcategory' => 'access',
                'title' => 'Access denied custom page',
                'desc' => 'Select page to be opened (via redirect) once visitor restricted to view download page.',
                'onlyin' => 'Pro'
            ],


            // Notifications
            self::OPTION_NEW_DOWNLOAD_ADMIN_NOTIFICATION_EMAIL => [
                'type' => self::TYPE_CSV_LINE,
                'default' => '',
                'category' => 'notifications',
                'subcategory' => 'moderation',
                'title' => 'Email address for the admin notifications',
                'desc' => 'If you want to send the email to the admin each time someone uploaded a file add his email address to this field. '
                    . 'You can add comma separated multiple emails.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_NEW_DOWNLOAD_MODERATOR_EMAIL_SUBJECT => [
                'type' => self::TYPE_STRING,
                'default' => '[[blogname]] Download needs moderation - [title]',
                'category' => 'notifications',
                'subcategory' => 'moderation',
                'title' => 'Subject for the moderator\'s email',
                'desc' => 'Subject for the notification email send to moderator after user uploaded new download.<br />'
                    . 'You can use the following shortcodes:<br />[blogname] - name of the blog<br />[title] - new download title<br />'
                    . '[link] - link to download page<br />[author] - uploader name',
                'onlyin' => 'Pro'
            ],
            self::OPTION_NEW_DOWNLOAD_MODERATOR_EMAIL_BODY => [
                'type' => self::TYPE_TEXTAREA,
                'default' => "Hi,\n[author] has uploaded new file:\n\n[title]\n[link]",
                'category' => 'notifications',
                'subcategory' => 'moderation',
                'title' => 'Body for the moderator\'s email',
                'desc' => 'Body for the notification email send to moderator after user uploaded new file.<br />'
                    . 'You can use the following shortcodes:<br />[blogname] - name of the blog<br />[title] - new download title<br />'
                    . '[link] - link to download page<br />[author] - uploader name<br>[author_email] - uploader email<br>[moderate_link] - link to moderation',
                'onlyin' => 'Pro'
            ],
            self::OPTION_MODERATOR_APPROVED_EMAIL_ENABLE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'notifications',
                'subcategory' => 'moderation',
                'title' => 'Send notification to the user after moderator\'s action',
                'desc' => 'If enabled the file author will be notified over the email after moderator accepted or rejected his file.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_AUTO_APPROVED_EMAIL_ENABLE => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'notifications',
                'subcategory' => 'moderation',
                'title' => 'Send notification to the user, if auto-approve option enabled',
                'desc' => 'If enabled the file author will be notified over the email about success of new upload.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_MODERATOR_APPROVED_EMAIL_SUBJECT => [
                'type' => self::TYPE_STRING,
                'default' => '[[blogname]] Moderator has [action] your file',
                'category' => 'notifications',
                'subcategory' => 'moderation',
                'title' => 'Subject for moderation result email',
                'desc' => 'Subject for the notification email send to the user after moderator approved or rejected his file.<br />'
                    . 'You can use the following shortcodes:<br />[blogname] - name of the blog<br />[title] - new download title<br />'
                    . '[link] - link to download page<br />[author] - uploader name<br>[action] - text: approved or rejected (change in Labels tab)',
                'onlyin' => 'Pro'
            ],
            self::OPTION_MODERATOR_APPROVED_EMAIL_BODY => [
                'type' => self::TYPE_TEXTAREA,
                'default' => "Hi,\nadministrator has [action] your file:\n\n[title]",
                'category' => 'notifications',
                'subcategory' => 'moderation',
                'title' => 'Body for moderation result email',
                'desc' => 'Body for the notification email send to the user after moderator approved or rejected his file.<br />'
                    . 'You can use the following shortcodes:<br />[blogname] - name of the blog<br />[title] - new download title<br />'
                    . '[link] - link to download page<br />[author] - uploader name<br>[action] - text: approved or rejected (change in Labels tab)',
                'onlyin' => 'Pro'
            ],

            self::OPTION_NEW_DOWNLOAD_NOTIFICATION_HIDE_EMAILS => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'notifications',
                'subcategory' => 'download',
                'title' => 'Hide recipient addresses',
                'desc' => 'If option enabled, all recipient emails will be set within "Bcc:" field of message. "Cc:" field will contain site admin email address.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_NEW_DOWNLOAD_NOTIFICATION_SUBJECT => [
                'type' => self::TYPE_STRING,
                'default' => '[[blogname]] New download - [title]',
                'category' => 'notifications',
                'subcategory' => 'download',
                'title' => 'New download notification subject',
                'desc' => 'Subject for the notification email send after creating new download.<br />'
                    . 'You can use the following shortcodes:<br />[blogname] - name of the blog<br />[title] - new download title<br />'
                    . '[link] - link to download page<br />[author] - uploader name',
                'onlyin' => 'Pro'
            ],
            self::OPTION_NEW_DOWNLOAD_NOTIFICATION_BODY => [
                'type' => self::TYPE_TEXTAREA,
                'default' => "Hi,\n[author] has uploaded new package:\n\n[title]\n[link]",
                'category' => 'notifications',
                'subcategory' => 'download',
                'title' => 'New download notification body',
                'desc' => 'Body for the notification email send after creating new download.<br />'
                    . 'You can use the following shortcodes:<br />[blogname] - name of the blog<br />[title] - new download title<br />'
                    . '[link] - link to download page<br />
                        [author] - uploader name<br />
                        [recipient_first_name] - the First Name of the recipient <br />
                        [recipient_last_name] - the Last Name of the recipient',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ACCESS_NOTIFICATION_MODE => [
                'type' => self::TYPE_MULTICHECKBOX,
                'options' => [
                    self::ACCESS_NOTIFICATION_MODE_GROUPS => 'Groups\' members',
                    self::ACCESS_NOTIFICATION_MODE_USERS => 'Users assigned directly',
                ],
                'category' => 'notifications',
                'subcategory' => 'download',
                'title' => 'Send additional email about granted access to',
                'desc' => 'If new download has access restricted to chosen groups, chosen users or the download\'s category '
                    . 'has restricted access to some groups, then you can notify these users about access granted for them to this download.<br /><br />'
                    . 'Choose "Groups\' members" if you want to notify the restricted groups members.<br />'
                    . 'Choose "Users assigned directly" to notify users assigned directly to some downloads.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_ENABLE_CATEGORY_FOLLOWING => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'notifications',
                'subcategory' => 'download',
                'title' => 'Enable category following',
                'desc' => 'If enabled, the "Follow" button will be added to each category and users will be able to follow new uploads.',
                'onlyin' => 'Pro'
            ],

            // Notifications support forum
            self::OPTION_NEW_THREAD_NOTIFICATION_TITLE => [
                'type' => self::TYPE_STRING,
                'default' => 'Someone has added a new topic to your download support page',
                'category' => 'notifications',
                'subcategory' => 'support',
                'title' => 'New topic notification title',
                //
            ],
            self::OPTION_NEW_THREAD_NOTIFICATION => [
                'type' => self::TYPE_TEXTAREA,
                'default' => "Someone has added a new topic to your download support page\n\nDownload: [addon_title]\nTopic: [thread_title]\nClick to see: [comment_link]",
                'category' => 'notifications',
                'subcategory' => 'support',
                'title' => 'New topic notification message',
                //
            ],
            self::OPTION_THREAD_NOTIFICATION_TITLE => [
                'type' => self::TYPE_STRING,
                'default' => 'Someone has posted a new comment on the topic you subscribed to',
                'category' => 'notifications',
                'subcategory' => 'support',
                'title' => 'Topic comment notification title',
                //
            ],
            self::OPTION_THREAD_NOTIFICATION => [
                'type' => self::TYPE_TEXTAREA,
                'default' => "Someone has posted a new comment on the topic you subscribed to\n\nDownload: [addon_title]\nTopic: [thread_title]\nClick to see: [comment_link]",
                'category' => 'notifications',
                'subcategory' => 'support',
                'title' => 'Topic comment notification message',
                //
            ],


            // Disclaimer
            self::OPTION_CONTENT_DISCLAIMER_STATUS => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'disclaimer',
                'title' => 'Show disclaimer for first time users',
                'desc' => 'In case you want disclaimer to appear for new user you need to select yes.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_SHOW_DISCLAIMER_AFTER_LOGIN => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'general',
                'subcategory' => 'disclaimer',
                'title' => 'Show disclaimer only for logged in users',
                'desc' => 'In case if you want the disclaimer to appear only after the user logged in.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_CONTENT_DISCLAIMER => [
                'type' => self::TYPE_TEXTAREA,
                'category' => 'general',
                'subcategory' => 'disclaimer',
                'title' => 'Disclaimer Text',
                'desc' => 'Please describe in details the message which will appear in the disclaimer.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_CONTENT_DISCLAIMER_ACCEPT => [
                'type' => self::TYPE_STRING,
                'default' => 'Accept Terms',
                'category' => 'general',
                'subcategory' => 'disclaimer',
                'title' => 'Disclaimer Accept Button text',
                'desc' => 'Please specify what will appear in the disclaimer accept button.',
                'onlyin' => 'Pro'
            ],
            self::OPTION_CONTENT_DISCLAIMER_REJECT => [
                'type' => self::TYPE_STRING,
                'default' => 'Reject Terms',
                'category' => 'general',
                'subcategory' => 'disclaimer',
                'title' => 'Disclaimer Reject Button text',
                'desc' => 'Please specify what will appear in the disclaimer reject button.',
                'onlyin' => 'Pro'
            ],


            // Custom CSS
            self::OPTION_CUSTOM_CSS_URL => [
                'type' => self::TYPE_STRING,
                'category' => 'custom_css',
                'subcategory' => 'css',
                'title' => 'Custom CSS file URL',
                'desc' => 'Enter the URL of the custom CSS file that you want to embed on every CMDM page.',
                'onlyin' => 'Pro'
            ],

            // REMOTE STORAGE
            self::ATTACHMENT_STORAGE_TYPE => [
                'type' => self::TYPE_RADIO,
                'default' => self::LOCAL_STORAGE,
                'category' => 'remote_storage',
                'subcategory' => 'general',
                'title' => 'Choose a storage provider',
                'desc' => '',
                'onlyin' => 'Pro'
            ],
            self::FTP_SERVER => [
                'type' => self::TYPE_STRING,
                'category' => 'remote_storage',
                'subcategory' => 'ftp',
                'title' => 'Host',
                'desc' => 'Enter the host name',
                'onlyin' => 'Pro'
            ],
            self::FTP_LOGIN => [
                'type' => self::TYPE_STRING,
                'category' => 'remote_storage',
                'subcategory' => 'ftp',
                'title' => 'User name',
                'desc' => 'Enter the FTP user name',
                'onlyin' => 'Pro'
            ],
            self::FTP_PASSWORD => [
                'type' => self::TYPE_STRING,
                'category' => 'remote_storage',
                'subcategory' => 'ftp',
                'title' => 'User Password',
                'desc' => 'Enter the password of the FTP user',
                'onlyin' => 'Pro'
            ],
            self::FTP_PORT => [
                'type' => self::TYPE_STRING,
                'default' => '21',
                'category' => 'remote_storage',
                'subcategory' => 'ftp',
                'title' => 'Port',
                'desc' => 'Enter the FTP port',
                'onlyin' => 'Pro'
            ],
            self::FTP_SSL => [
                'type' => self::TYPE_BOOL,
                'default' => 0,
                'category' => 'remote_storage',
                'subcategory' => 'ftp',
                'title' => 'Use SSL',
                'desc' => '',
                'onlyin' => 'Pro'
            ],
            self::FTP_SITE_PATH => [
                'type' => self::TYPE_STRING,
                'default' => 'https://sitename.example',
                'category' => 'remote_storage',
                'subcategory' => 'ftp',
                'title' => 'Public site URL of the FTP server',
                'desc' => 'Enter the public url of the site where the user will download files from',
                'onlyin' => 'Pro'
            ],
            self::FTP_REMOTE_BASE_PATH => [
                'type' => self::TYPE_STRING,
                'default' => '/cmdm_ftp/',
                'category' => 'remote_storage',
                'subcategory' => 'ftp',
                'title' => 'FTP directory path',
                'desc' => 'Enter the path of the directory where you will store the files on your FTP server',
                'onlyin' => 'Pro'
            ],
            self::FTP_CHECK_CONNECTION => [
                'type' => self::TYPE_CUSTOM,
                'category' => 'remote_storage',
                'subcategory' => 'ftp',
                'title' => 'Check FTP options',
                'desc' => 'Don\'t forget to save settings',
                'content' => '',
                'onlyin' => 'Pro'
            ],
        ]);
    }


    public static function getSupportOptions() {
        return [
            self::SUPPORT_DISABLED => 'disabled for all downloads',
            self::SUPPORT_ENABLED => 'enabled for all downloads',
            self::SUPPORT_PER_DOWNLOAD => 'customizable for each download',
        ];
    }

    public static function getMimeTypeOptions() {
        return [
            self::MIME_AUTO => 'Auto (Default)',
            self::MIME_BY_ARRAY => 'Using array',
            self::MIME_FILE_INFO => 'File Info',
            self::MIME_TYPE_FUNCTION => 'mime_content_type'
        ];
    }


    public static function getDisplayOptionsDefaults() {
        return [
            'searchBar' => CMDM_Settings::getOption(CMDM_Settings::INDEX_PAGE_SEARCH_BAR),
            'header' => true,
            'categories' => true,
            'allDownloads' => true,
            'controls' => 'top',
            'dashboardLinks' => CMDM_Settings::getOption(CMDM_Settings::OPTION_INDEX_SHOW_DASHBOARD_LINKS),
        ];
    }


    public static function getPageTemplatesOptions() {
        $theme = wp_get_theme();
        $templates = (array)$theme->get_page_templates();
        $result = [
            0 => 'CMDM default',
        ];
        if ($pageTemplate = locate_template('page.php', false, false)) {
            $result['page.php'] = 'Theme\'s page.php';
        }
        return array_merge($result, $templates);
    }


    public static function getCustomPageTemplate($template) {
        $available = CMDM_Settings::getPageTemplatesOptions();
        if (!empty($template) and isset($available[$template])) {
            return $template;
        } else {
            return 0;
        }
    }

    public static function getIndexPageTemplate() {
        return static::getCustomPageTemplate('page.php');
    }

    public static function getDownloadPageTemplate() {
        return static::getCustomPageTemplate('page.php');
    }

}
