<?php
ob_start();
include plugin_dir_path(__FILE__) . 'views/plugin_compare_table.php';
$plugin_compare_table = ob_get_contents();
ob_end_clean();
$cminds_plugin_config = array(
	'plugin-is-pro'				 => false,
	'plugin-has-addons'			 => TRUE,
	'plugin-addons'        => array(
		array(
			'title' => 'Tooltip Glossary Plugin',
			'description' => 'Create a WordPress glossary, encyclopedia, or dictionary of terms and display responsive tooltips on hover.',
			'link' => 'https://wordpress.org/plugins/enhanced-tooltipglossary/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPTooltipGlossaryS.png',
		),
		array(
			'title' => 'Questions and Answers Forum',
			'description' => 'Manage Q&A forums with the WordPress questions and answers plugin. Let users ask questions, provide answers, and engage with your community.',
			'link' => 'https://wordpress.org/plugins/cm-answers/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPQuestionsAndAnswersS.png',
		),
		array(
			'title' => 'Registration and Invitation Codes',
			'description' => 'Manage user registration forms with invitation codes and control access. Simplify login and registration processes using Ajax based solution.',
			'link' => 'https://wordpress.org/plugins/cm-invitation-codes/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPUserRegistrationAndInvitationCodesS.png',
		),
		array(
			'title' => 'Map Locations Manager',
			'description' => 'Display locations on an interactive map with Google Maps. Use as a store locator, showcase business locations, and improve navigation.',
			'link' => 'https://wordpress.org/plugins/cm-map-locations/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPMapLocationsandStoreLocaterS.png',
		),
		array(
			'title' => 'Popup Banners',
			'description' => 'Create and customize popups. Display messages, Call to actions, promotions, or announcements to engage visitors and boost interaction.',
			'link' => 'https://wordpress.org/plugins/cm-pop-up-banners/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPPopUpBannersS.png',
		),
		array(
			'title' => 'Email Blacklist Registration',
			'description' => 'Block unwanted email registrations on your site with this email blacklist plugin. Protect your site by preventing spam sign-ups.',
			'link' => 'https://wordpress.org/plugins/cm-email-blacklist/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPEmailDomainBlacklistS.png',
		),
	),
	'plugin-specials'        => array(
		array(
			'title' => 'MicroPayments Digital Wallet',
			'description' => 'MicroPayments establishes a digital wallet system, allowing users to seamlessly exchange points for goods, gift points, or directly purchase points using credit cards.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/micropayments/?discount=CMINDS10',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/MicropaymentsS.png',
		),
		array(
			'title' => 'Map Routes Manager',
			'description' => 'Draw map routes and generate a catalog of routes and trails with points of interest using Google maps.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/google-maps-routes-manager-plugin-for-wordpress-by-creativeminds/?discount=CMINDS10',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPMapRoutesManagerS.png',
		),
		array(
			'title' => 'Site Access and Content Restriction',
			'description' => 'A robust membership solution and content restriction plugin that supports role-based access to content on your WordPress website.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/membership-plugin-for-wordpress/?discount=CMINDS10',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPSiteRestrictionS.png',
		),
		array(
			'title' => 'Secure Login 2FA Authentication',
			'description' => 'Offers a robust solution for WordPress two-factor authentication and provide a better account security for your WordPress users.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/secure-login-two-factor-authentication-wordpress/?discount=CMINDS10',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPSecureLoginAndTwoFactorS.png',
		),
		array(
			'title' => 'Booking Calendar',
			'description' => 'Enable customers to effortlessly schedule appointments and make payments directly through your website.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/schedule-appointments-manage-bookings-plugin-wordpress/?discount=CMINDS10',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPBookingCalendarS.png',
		),
		array(
			'title' => 'Invitation Code Content Access',
			'description' => 'Generate restricted access codes for specific content, pages, and files. Each code can have a limited number of uses and an expiration date.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/invitation-code-content-access-plugin-wordpress/?discount=CMINDS10',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPContentAccessInvitationCodeS.png',
		),
	),
	'plugin-bundles'        => array(
		array(
			'title' => '99+ Free Pass Plugins Suite',
			'description' => 'Get all CM 99+ WordPress plugins and addons. Includes unlimited updates and one year of priority support.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/cm-wordpress-plugins-yearly-membership/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPBundleWPSuiteS.png',
		),
		array(
			'title' => 'Essential Publishing Plugin Package',
			'description' => 'Enhance your WordPress publishing with a bundle of seven plugins that elevate content generation, presentation, and user engagement on your site.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/essential-wordpress-publishing-tools-bundle/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPBundlePublishingS.png',
		),
		array(
			'title' => 'Essential Content Marketing Tools',
			'description' => 'Enhance your WordPress content marketing with seven plugins for improved content generation, presentation, and user engagement.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/essential-wordpress-content-marketing-tools-bundle/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPBundleContentS.png',
		),
		array(
			'title' => 'Essential Security Plugins',
			'description' => 'Enhance your WordPress security with a bundle of five plugins that provide additional ways to protect your content and site from spammers, hackers, and exploiters.',
			'link' => 'https://www.cminds.com/wordpress-plugins-library/essential-wordpress-security-tools-plugin-bundle/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPBundleSecurityS.png',
		),
	),
	'plugin-services'        => array(
		array(
			'title' => 'WordPress Custom Hourly Support',
			'description' => 'Hire our expert WordPress developers on an hourly basis, offering a-la-carte service to craft your custom WordPress solution.',
			'link' => 'https://www.cminds.com/wordpress-services/wordpress-custom-hourly-support-package/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPServicesHourlySupportS.png',
		),
		array(
			'title' => 'Performance and Optimization Analysis',
			'description' => 'Receive a comprehensive review of your WordPress website with optimization suggestions to enhance its speed and performance.',
			'link' => 'https://www.cminds.com/wordpress-services/wordpress-performance-and-speed-optimization-analysis-service/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPServicesPerformanceS.png',
		),
		array(
			'title' => 'WordPress Plugin Installation',
			'description' => 'We offer professional installation and configuration of plugins or add-ons on your site, tailored to your specified requirements.',
			'link' => 'https://www.cminds.com/wordpress-services/plugin-installation-service-for-wordpress-by-creativeminds/',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPServicesExtensionInstallationS.png',
		),
		array(
			'title' => 'WordPress Consulting',
			'description' => 'Purchase consulting hours to receive assistance in designing or planning your WordPress solution. Our expert consultants are here to help bring your vision to life.',
			'link' => 'https://www.cminds.com/wordpress-services/consulting-planning-hourly-support-service-wordpress-creativeminds/#description',
			'image' => plugin_dir_url( __FILE__ ) . 'views/icons/WPServicesConsultingS.png',
		),
	),
	'plugin-version'			 => '3.0.6',
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
?>