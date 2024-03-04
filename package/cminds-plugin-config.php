<?php

ob_start();
include plugin_dir_path(__FILE__) . 'views/plugin_compare_table.php';
$plugin_compare_table = ob_get_contents();
ob_end_clean();

$cminds_plugin_config = array(
	'plugin-is-pro'				 => false,
	'plugin-has-addons'			 => TRUE,
	'plugin-version'			 => '2.8.2',
	'plugin-abbrev'				 => 'cmdm',
	'plugin-file'				 => CMDM_PLUGIN_FILE,
	'plugin-affiliate'               => '',
	'plugin-redirect-after-install'  => admin_url( 'admin.php?page=CMDM_admin_settings' ),
	'plugin-show-guide'                 => TRUE,
	'plugin-guide-text'                 => '    <div style="display:block">
        <ol>
         <li>Go to the plugin <strong>"Setting"</strong> and click on <strong>"Link to downloads frontend list"</strong></li>
         <li>Click on  <strong>"Manage My Downloads"</strong> button at the right side of the screen</li>
            <li>From the user dashboard click on <strong>Add New</strong> to upload your first download</li>
            <li>Fill up for form and upload your first download, make sure you mark the category.</li>
            <li><strong>View</strong> the download created</li>
            <li>In the <strong>Plugin Settings</strong> you can set the file extensions which are accepted, the default image and more.</li>
            <li>You can add or change category names from the <strong>Plugin Admin Menu</strong></li>
            <li><strong>Troubleshooting:</strong> Make sure that you are using Post name permalink structure in the WP Admin Settings -> Permalinks.</li>
            <li><strong>Troubleshooting:</strong> If post type archive does not show up or displays 404 then install Rewrite Rules Inspector plugin and use the Flush rules button.</li>
            <li><strong>Troubleshooting:</strong> If the settings cannot be saved eg. 403 Forbidden error shows up after pressed the Save button, then contact your hosting provider and ask for the restrictions for POST requests to the /wp-admin/admin.php.</li>
        </ol>
    </div>',
	'plugin-guide-video-height'         => 240,
	'plugin-guide-videos'               => array(
		array( 'title' => 'Installation tutorial', 'video_id' => '159673805' ),
	),
   'plugin-upgrade-text'           => 'Good Reasons to Upgrade to Pro',
    'plugin-upgrade-text-list'      => array(
        array( 'title' => 'Why you should upgrade to Pro', 'video_time' => '0:00' ),
        array( 'title' => 'Improved downloads index', 'video_time' => '0:05' ),
        array( 'title' => 'Multiple files download', 'video_time' => '0:30' ),
        array( 'title' => 'Video and audio downloads', 'video_time' => '0:55' ),
        array( 'title' => 'Download preview', 'video_time' => '1:28' ),
        array( 'title' => 'Zip compression', 'video_time' => '1:52' ),
        array( 'title' => 'Download password protection', 'video_time' => '2:30' ),
        array( 'title' => 'Restrict user access', 'video_time' => '3:08' ),
        array( 'title' => 'Ask for email before download', 'video_time' => '3:49' ),
        array( 'title' => 'Host download files externally', 'video_time' => '4:23' ),
        array( 'title' => 'Group access settings', 'video_time' => '4:48' ),
        array( 'title' => 'Upload and forum moderation', 'video_time' => '5:23' ),
        array( 'title' => 'User dashboard and profile', 'video_time' => '5:54' ),
        array( 'title' => 'Log and statistics', 'video_time' => '6:15' ),
        array( 'title' => 'Search downloads ', 'video_time' => '6:43' ),
   ),
    'plugin-upgrade-video-height'   => 240,
    'plugin-upgrade-videos'         => array(
        array( 'title' => 'Download Manager Premium Features', 'video_id' => '271498666' ),
    ),
	'plugin-dir-path'			 => plugin_dir_path( CMDM_PLUGIN_FILE ),
	'plugin-dir-url'			 => plugin_dir_url( CMDM_PLUGIN_FILE ),
	'plugin-basename'			 => plugin_basename( CMDM_PLUGIN_FILE ),
	'plugin-icon'				 => '',
	'plugin-name'				 => 'CM Download Manager',
	'plugin-license-name'		 => 'CM Download Manager',
	'plugin-slug'				 => '',
	'plugin-short-slug'			 => 'cm-download-manager',
    'plugin-campign'             => '?utm_source=cmdmfree&utm_campaign=freeupgrade',
	'plugin-menu-item'			 => 'CMDM_downloads_menu',
	'plugin-textdomain'			 => 'cm-download-manager',
	'plugin-userguide-key'		 => '2721-cm-download-cmdm-getting-started-free-version-tutorial',
	'plugin-store-url'			 => 'https://www.cminds.com/wordpress-plugins-library/downloadsmanager?utm_source=cmdmfree&utm_campaign=freeupgrade&upgrade=1',
	'plugin-support-url'		 => 'https://www.cminds.com/contact/',
	'plugin-review-url'			 => 'http://wordpress.org/support/view/plugin-reviews/cm-download-manager',
	'plugin-changelog-url'		 => 'https://www.cminds.com/wordpress-plugins-library/cm-download-manager-changelog/',
	'plugin-licensing-aliases'	 => array( 'CM Download Manager' ),
	'plugin-compare-table'	 => $plugin_compare_table,

);