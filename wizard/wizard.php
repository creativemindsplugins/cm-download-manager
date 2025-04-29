<?php

class CMDM_SetupWizard{

    public static $steps;
    public static $wizard_url;
    public static $wizard_path;
    public static $options_slug = 'CMDM_';
    public static $wizard_screen = 'cm-downloads_page_CMDM_setup_wizard'; //change for your plugin needs
    public static $setting_page_slug = CMDM_BaseController::ADMIN_SETTINGS;
    public static $plugin_basename;


    public static function init() {
        self::$wizard_url = plugin_dir_url(__FILE__);
        self::$wizard_path = plugin_dir_path(__FILE__);
        self::$plugin_basename = plugin_basename(CMDM_PLUGIN_FILE); //change for your plugin needs
        self::setSteps();

        add_action('admin_menu', [__CLASS__, 'add_submenu_page'],30);
        add_action('activated_plugin', [__CLASS__, 'redirectAfterInstall'], 1, 2);
        add_action('wp_ajax_cmdm_save_wizard_options',[__CLASS__,'saveOptions']);
        add_action('admin_enqueue_scripts', [ __CLASS__, 'enqueueAdminScripts' ] );
        add_action('admin_notices', [__CLASS__, 'disableAdminNotices'], 1);
        add_action('admin_print_scripts', [__CLASS__, 'disableAdminNotices'], 1);
    }


    public static function setSteps()
    {
        self::$steps = [
            1 => ['title' => 'Initial Setup',
                'description' => '<p>Set up the basic configurations to establish the foundation for your download management.</p>',
                'options' => [
                    0 => [
                        'name' => 'CMDM_index_page_disabled',
                        'title' => 'Downloads Index Page',
                        'type' => 'radio',
                        'options' => [
                            0 => [
                                'title' => 'Use default page',
                                'value' => 0
                            ],
                            1 => [
                                'title' => 'Create custom page',
                                'value' => 1
                            ],
                        ],
                        'hint' => 'Choose whether to use the default, non-editable index page or create a custom index page that you can fully edit and customize. If you choose to create a custom page, the plugin will generate a new WordPress page and set it as the default index page. You can edit this page using the WordPress page editor. A link to the page will always be available above the general plugin settings.'
                    ],
                    1 => [
                        'name' => 'CMDM_dashboard_page_disabled',
                        'title' => 'Downloads Dashboard Page',
                        'type' => 'radio',
                        'options' => [
                            0 => [
                                'title' => 'Use default page',
                                'value' => 0
                            ],
                            1 => [
                                'title' => 'Create custom page',
                                'value' => 1
                            ],
                        ],
                        'hint' => 'Decide whether to use the default, non-editable dashboard page or generate a custom dashboard page that allows editing and customization. If you choose to create a custom page, the plugin will generate a new WordPress page and set it as the default dashboard page. You can edit this page using the WordPress page editor. A link to the page will always be available above the general plugin settings.'
                    ],
                    2 => [
                        'name' => 'CMDM_add_menu_links',
                        'title' => 'Add links to the site menu',
                        'type' => 'multicheckbox',
                        'options' => [
                            0 => [
                                'title' => '"Downloads" index page',
                                'value' => 'CMDM_option_add_addons_menu'
                            ],
                            1 => [
                                'title' => '"My Downloads" dashboard page',
                                'value' => 'CMDM_option_add_dashboard_menu'
                            ],
                        ],
                        'hint' => 'Select which links to add to your site menu: the Downloads Index Page, the Dashboard Page, or both.'
                    ],
                    3 => [
                        'name'  => 'CMDM_allowed_extensions',
                        'title' => 'Allowed file extensions',
                        'type'  => 'string',
                        'value' => implode(',',CMDM_Settings::getOption('CMDM_allowed_extensions')),
                        'hint'  => 'Specify the file extensions that users are allowed to upload through the upload form. Enter extensions separated by commas (e.g., jpg,png,pdf)'
                    ],
                    4 => [
                        'name'  => 'CMDM_max_width_for_cmdm_container',
                        'title' => 'Max-width of the CMDM container (in %)',
                        'type'  => 'int',
                        'min' => 40,
                        'max' => 80,
                        'value' => 70,
                        'hint'  => 'Set the maximum width of the Download Manager content area as a percentage of the page width. Applies to index, user dashboard, upload, and download pages. Valid range: from 40% to 80%.'
                    ],
                ],

            ],
            2 => ['title' =>'Index Page Settings',
                'description' => '<p>Customize the appearance and functionality of the downloads index page.</p>',
                'options' => [
                    0 => [
                        'name' => 'CMDM_default_view',
                        'title' => 'Default downloads list view',
                        'type' => 'radio',
                        'options' => [
                            0 => [
                                'title' => 'List',
                                'value' => 'list'
                            ],
                            1 => [
                                'title' => 'Tiles',
                                'value' => 'tiles'
                            ],
                        ],
                        'value' => 1,
                        'hint' => 'Set the default view format for users when viewing the downloads index page.'
                    ],
                    1 => [
                        'name' => 'CMDM_index_show_dashboard_links',
                        'title' => 'Show dashboard links on the index page',
                        'type' => 'bool',
                        'value' => 1,
                        'hint' => 'If enabled, the "All downloads," "My downloads," and "Add new" links will be shown on the index page, providing easy navigation to these sections.'
                    ],
                    2 => [
                        'name' => 'CMDM_index_page_search_bar',
                        'title' => 'Display search bar',
                        'type' => 'bool',
                        'value' => 1,
                        'hint' => 'Choose whether to display a search bar on the downloads index page to allow users to search for specific downloads.'
                    ],
                ],
            ],
            3 => ['title' =>'Download Page Settings',
                'description' => '<p>Control what information and features are displayed on each download page.</p>',
                'options' => [
                    0 => [
                        'name' => 'CMDM_download_show_description',
                        'title' => 'Show description',
                        'type' => 'bool',
                        'value' => 1,
                        'hint' => 'Enabling this option will display the description content on the download page.'
                    ],
                    1 => [
                        'name' => 'CMDM_show_author',
                        'title' => 'Show author',
                        'type' => 'bool',
                        'value' => 1,
                        'hint' => 'If disabled, author information will not be displayed on the download page.'
                    ],
                    2 => [
                        'name' => 'CMDM_enable_rating',
                        'title' => 'Enable rating',
                        'type' => 'bool',
                        'value' => 1,
                        'hint' => 'Allow users to rate downloads.'
                    ],
                    3 => [
                        'name' => 'CMDM_support_enable',
                        'title' => 'Enable support tab for downloads',
                        'type' => 'bool',
                        'value' => 1,
                        'hint' => 'Show a support tab on each download page, providing users with access to additional help or related content.'
                    ],
                    4 => [
                        'name' => 'CMDM_allow_screenshots',
                        'title' => 'Enable screenshots',
                        'type' => 'bool',
                        'value' => 1,
                        'hint' => 'If enabled, screenshots will be shown on the download page. Disabling this option will hide them.'
                    ],
                    5 => [
                        'name' => 'CMDM_download_button_widget_area',
                        'title' => 'Download button position',
                        'type' => 'radio',
                        'options' => [
                            0 => [
                                'title' => 'Aside',
                                'value' => 'cm-download-manager-sidebar'
                            ],
                            1 => [
                                'title' => 'Top',
                                'value' => 'cmdm-single-top'
                            ],
                            2 => [
                                'title' => 'Below screenshots',
                                'value' => 'cmdm-single-screenshots-below'
                            ],
                            3 => [
                                'title' => 'Bottom',
                                'value' => 'cmdm-single-bottom'
                            ],
                        ],
                        'hint' => 'Choose the position where the download button will be displayed on the page.'
                    ],

                ],
            ],
            4 => ['title' =>'Access Control Settings',
                'description' => '<p>Define who can add, view and download files on your site.</p>',
                'options' => [
                    0 => [
                        'name' => 'CMDM_adding_permissions',
                        'title' => 'Who can add new downloads',
                        'type' => 'radio',
                        'options' => [
                            0 => [
                                'title' => 'Logged-in users',
                                'value' => 'loggedin'
                            ],
                            1 => [
                                'title' => 'Users from specific WP roles',
                                'value' => 'roles'
                            ],
                        ],
                        'value' => 1,
                        'hint' => 'Select who can add new downloads and access the downloads dashboard.'
                    ],
                    1 => [
                        'name' => 'CMDM_adding_roles',
                        'title' => 'Which WP roles can add new downloads',
                        'type' => 'multicheckbox',
                        'options' => self::getRolesOptions(),
                        'value' => 1,
                        'hint' => 'This option lets you choose specific WordPress user roles that can add new downloads and have access to the dashboard page. This setting is used when "Users from specific WP roles" is selected above.'
                    ],
                    2 => [
                        'name' => 'CMDM_viewing_permissions',
                        'title' => 'Who can view downloads',
                        'type' => 'radio',
                        'options' => [
                            0 => [
                                'title' => 'All users (including guests)',
                                'value' => 'all'
                            ],
                            1 => [
                                'title' => 'Logged-in users',
                                'value' => 'loggedin'
                            ],
                            2 => [
                                'title' => 'Users from specific WP roles',
                                'value' => 'roles'
                            ],
                        ],
                        'value' => 1,
                        'hint' => 'Select who can access the download pages.'
                    ],
                    3 => [
                        'name' => 'CMDM_viewing_roles',
                        'title' => 'Which WP roles can view downloads',
                        'type' => 'multicheckbox',
                        'options' => self::getRolesOptions(),
                        'value' => 1,
                        'hint' => 'This option lets you choose specific WordPress user roles that can view the download pages. This setting is used when "Users from specific WP roles" is selected above.'
                    ],
                    4 => [
                        'name' => 'CMDM_downloading_permissions',
                        'title' => 'Who can download files',
                        'type' => 'radio',
                        'options' => [
                            0 => [
                                'title' => 'All users (including guests)',
                                'value' => 'all'
                            ],
                            1 => [
                                'title' => 'Logged-in users',
                                'value' => 'loggedin'
                            ],
                        ],
                        'value' => 1,
                        'hint' => 'Select who is allowed to download files.'
                    ],

                ],
            ],
            5 => ['title' =>'Dashboard',
                'content' => "<p>The initial setup is complete.</p>
            <ul style='list-style:pointer; padding: 0 15px; margin: 0; line-height: 1em;'>
            <ul style='list-style:pointer; padding: 0 15px; margin: 0; line-height: 1em;'>
                <li>In the plugin <a href='" . admin_url( 'admin.php?page='. self::$setting_page_slug) ."' target='_blank'>Settings</a>, you can find links to the Downloads Index page and Downloads Dashboard page.</li>
                <li>The plugin menu includes a link where the admin can manage all <a href='" . admin_url( 'edit.php?post_type='.CMDM_GroupDownloadPage::POST_TYPE) . "' target='_blank'>Downloads</a>.</li>
            </ul><br/>
            <div class='cm_wizard_image_holder'>
                <a href='". self::$wizard_url . "assets/img/wizard_step_5.png' target='_blank'>
                    <img src='" . self::$wizard_url . "assets/img/wizard_step_5.png' width='750px'/>
                </a>
            </div>"],
        ];
        return;
    }

    public static function add_submenu_page(){
        if(CMDM_Settings::getOption('CMDM_add_wizard_menu',1)){
            add_submenu_page( CMDM_GroupDownloadPage::ADMIN_MENU, 'Setup Wizard', 'Setup Wizard', 'manage_options', self::$options_slug . 'setup_wizard',[__CLASS__,'renderWizard'],20 );
        }
    }

    public static function enqueueAdminScripts(){
        $screen = get_current_screen();

        if ($screen && $screen->id === self::$wizard_screen) {
            wp_enqueue_style('wizard-css', self::$wizard_url . 'assets/wizard.css',[],CMDM_VERSION);
            wp_enqueue_script('wizard-js', self::$wizard_url . 'assets/wizard.js',['jquery'],CMDM_VERSION);
            wp_localize_script('wizard-js', 'wizard_data', ['ajaxurl' => admin_url('admin-ajax.php')]);
        }
    }

    public static function disableAdminNotices() {
        $current_screen = get_current_screen();
        if ($current_screen && $current_screen->id === self::$wizard_screen) {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
        }
    }

    public static function redirectAfterInstall($plugin, $network_activation = false){
        if (self::$plugin_basename !== $plugin) {
            return;
        }
        $activation_redirect_wizard = CMDM_Settings::getOption('CMDM_add_wizard_menu', 1 );
        $url = $activation_redirect_wizard ? admin_url( 'admin.php?page=CMDM_setup_wizard' ) : admin_url('admin.php?page=CMDM_admin_settings');
        wp_redirect($url);
        exit();
    }

    public static function saveOptions(){
        if (isset($_POST['data'])) {
            // Parse the serialized data
            parse_str($_POST['data'], $formData);


            if(!wp_verify_nonce($formData['_wpnonce'],'wizard-form')){
                wp_send_json_error();
            }
            foreach($formData as $key => $value){
                if( !str_contains($key, self::$options_slug) ){
                    continue;
                }
                if($key == 'CMDM_index_page_disabled' && $value){
                    $custom_post_id = CMDM_Settings::getOption('CMDM_index_page_custom_page_id');
                    if(!$custom_post_id || !get_post($custom_post_id)){
                        $custom_post = wp_insert_post([
                            'post_title'    => 'CM Downloads Index',
                            'post_content'  => '[cmdm-index]',
                            'post_status'   => 'publish',
                            'post_type'     => 'page'
                        ]);
                        if($custom_post) CMDM_Settings::setOption('CMDM_index_page_custom_page_id',$custom_post);
                    }
                }
                elseif($key == 'CMDM_dashboard_page_disabled' && $value){
                    $custom_post_id = CMDM_Settings::getOption('CMDM_dashboard_page_custom_id');
                    if(!$custom_post_id || !get_post($custom_post_id)){
                        $custom_post = wp_insert_post([
                            'post_title'    => 'CM Downloads Dashboard',
                            'post_content'  => '[cmdm-my-downloads]',
                            'post_status'   => 'publish',
                            'post_type'     => 'page'
                        ]);
                        if($custom_post) CMDM_Settings::setOption('CMDM_dashboard_page_custom_id',$custom_post);
                    }
                }
                elseif($key == 'CMDM_add_menu_links'){
                    foreach($value as $option){
                        CMDM_Settings::setOption($option, 1);
                    }
                    continue;
                }
                elseif($key == 'CMDM_allowed_extensions'){
                    $sanitized_value = sanitize_text_field($value);
                    $value = array_map('trim', explode(',',$sanitized_value));
                    if(!isset($formData['CMDM_add_menu_links']) || !in_array('CMDM_option_add_addons_menu',$formData['CMDM_add_menu_links'])){
                        CMDM_Settings::setOption('CMDM_option_add_addons_menu', 0);
                    }
                    if(!isset($formData['CMDM_add_menu_links']) || !in_array('CMDM_option_add_dashboard_menu',$formData['CMDM_add_menu_links'])){
                        CMDM_Settings::setOption('CMDM_option_add_dashboard_menu', 0);
                    }
                }
                elseif($key == 'CMDM_index_page_search_bar'){
                    CMDM_Settings::setOption('CMDM_index_page_search_submit', $value);
                }
                if(is_array($value)){
                    $sanitized_value = array_map('sanitize_text_field', $value);
                    CMDM_Settings::setOption($key, $sanitized_value);
                    continue;
                }
                $sanitized_value = sanitize_text_field($value);
                CMDM_Settings::setOption($key, $sanitized_value);
            }
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    public static function renderWizard(){
        require 'view/wizard.php';
    }

    public static function renderSteps(){
        $output = '';
        $steps = self::$steps;
        foreach($steps as $num => $step){
            $output .= "<div class='cm-wizard-step step-{$num}' style='display:none;'>";
            $output .= "<h1>" . self::getStepTitle($num) . "</h1>";
            $output .= "<div class='step-container'>
                            <div class='cm-wizard-menu-container'>" . self::renderWizardMenu($num)." </div>";
            $output .= "<div class='cm-wizard-content-container'>";
            if (isset($step['description'])){
                $output .= $step['description'];
            }
            if(isset($step['options'])){
                $output .= "<form>";
                $output .= wp_nonce_field('wizard-form');
                foreach($step['options'] as $option){
                    $output .=  self::renderOption($option);
                }
                $output .= "</form>";
            }
            if (isset($step['content'])){
                $output .= $step['content'];
            }
            $output .= '</div></div>';
            $output .= self::renderStepsNavigation($num);
            $output .= '</div>';
        }
        return $output;
    }

    public static function renderStepsNavigation($num){
        $settings_url = admin_url( 'admin.php?page='. self::$setting_page_slug );
        $output = "<div class='step-navigation-container'>
            <button class='prev-step' data-step='{$num}'>Previous</button>";
        if($num == count(self::$steps)){
            $output .= "<button class='finish' onclick='window.location.href = \"$settings_url\" '>Finish</button>";
        } else {
         $output .= "<button class='next-step' data-step='{$num}'>Next</button>";
        }
        $output .= "<p><a href='$settings_url'>Skip the setup wizard</a></p></div>";
        return $output;
    }

    public static function renderOption($option){
        switch($option['type']) {
            case 'bool':
                return self::renderBool($option);
            case 'int':
                return self::renderInt($option);
            case 'string':
                return self::renderString($option);
            case 'radio':
                return self::renderRadioSelect($option);
            case 'select':
                return self::renderSelect($option);
            case 'color':
                return self::renderColor($option);
            case 'multicheckbox':
                return self::renderMulticheckbox($option);
        }
    }

    public static function renderBool($option){
         $checked = checked($option['value'],CMDM_Settings::getOption( $option['name'] ),false);
         $output = "<div class='form-group'>
                <label for='{$option['name']}' class='label'>{$option['title']}<div class='cm_field_help' data-title='{$option['hint']}'></div></label>";
        if($option['value'] === 1 || $option['value'] === 0 ){
            $oposite_val = intval(!$option['value']);
            $output .= "<input type='hidden' name='{$option['name']}' value='{$oposite_val}'>";
        }
        $output .= "<input type='checkbox' id='{$option['name']}' name='{$option['name']}' class='toggle-input' value='{$option['value']}' {$checked}>
                <label for='{$option['name']}' class='toggle-switch'></label>
            </div>";
        return $output;
    }

    public static function renderInt($option){
        $min = isset($option['min']) ? "min='{$option['min']}'" : '';
        $max = isset($option['max']) ? "max='{$option['max']}'" : '';
        $step = isset($option['step']) ? "step='{$option['step']}'" : '';
        $value = CMDM_Settings::getOption( $option['name'], $option['value']);
        return "<div class='form-group'>
                <label for='{$option['name']}' class='label'>{$option['title']}<div class='cm_field_help' data-title='{$option['hint']}'></div></label>
                <input type='number' id='{$option['name']}' name='{$option['name']}' value='{$value}' {$min} {$max} {$step}/>
            </div>";
    }

    public static function renderString($option){
        $value = CMDM_Settings::getOption( $option['name'], $option['value']);
        if(is_array($value)) $value = implode(',',$value);
        return "<div class='form-group'>
                <label for='{$option['name']}' class='label'>{$option['title']}<div class='cm_field_help' data-title='{$option['hint']}'></div></label>
                <input type='text' id='{$option['name']}' name='{$option['name']}' value='{$value}'/>
            </div>";
    }

    public static function renderRadioSelect($option){
        $options = $option['options'];
        $output = "<div class='form-group'>
                <label class='label'>{$option['title']}<div class='cm_field_help' data-title='{$option['hint']}'></div></label>
                <div>";
        if(is_callable($option['options'], false, $callable_name)) {
            $options = call_user_func($option['options']);
        }
        foreach($options as $item) {
            $checked = checked($item['value'],CMDM_Settings::getOption( $option['name'] ),false);
            $output .= "<input type='radio' id='{$option['name']}_{$item['value']}' name='{$option['name']}' value='{$item['value']}' {$checked}/>
                <label for='{$option['name']}_{$item['value']}'>{$item['title']}</label><br>";
        }
        $output .= "</div></div>";
        return $output;
    }

    public static function renderColor($option) {
        ob_start(); ?>
        <script>
            jQuery(function ($) {
                $('input[name="<?php echo esc_attr($option['name']); ?>"]').wpColorPicker();
            });
        </script> <?php
        $output = ob_get_clean();
        $value = CMDM_Settings::getOption( $option['name'], $option['value']);
        $output .= "<div class='form-group'>
            <label for='{$option['name']}' class='label'>{$option['title']}<div class='cm_field_help' data-title='{$option['hint']}'></div></label>";
        $output .= sprintf('<input type="text" name="%s" value="%s" />', esc_attr($option['name']), esc_attr($value));
        $output .= "</div>";
        return $output;
    }

    public static function renderSelect($option){
        $options = $option['options'];
    $output = "<div class='form-group'>
                <label for='{$option['name']}' class='label'>{$option['title']}<div class='cm_field_help' data-title='{$option['hint']}'></div></label>
                <select id='{$option['name']}' name='{$option['name']}'>";
        if(is_callable($option['options'], false, $callable_name)) {
            $options = call_user_func($option['options']);
        }
        foreach($options as $item) {
        $selected = selected($item['value'],CMDM_Settings::getOption( $option['name'] ),false);
        $output .= "<option value='{$item['value']}' {$selected}>{$item['title']}</option>";
    }
    $output .= "</select></div>";
        return $output;
}
    public static function renderMulticheckbox($option){
        $options = $option['options'];
        $output = "<div class='form-group'>
                <label class='label'>{$option['title']}<div class='cm_field_help' data-title='{$option['hint']}'></div></label>
                <div>";
        if(is_callable($option['options'], false, $callable_name)) {
            $options = call_user_func($option['options']);
        }
        foreach($options as $item) {
            if($option['name'] == 'CMDM_add_menu_links'){
                $checked = checked(1, CMDM_Settings::getOption($item['value']),false);
            } else {
                $checked = in_array($item['value'], CMDM_Settings::getOption($option['name'])) ? 'checked' : '';
            }
            $output .= "<input type='checkbox' id='{$option['name']}_{$item['value']}' name='{$option['name']}[]' value='{$item['value']}' {$checked}/>
                <label for='{$option['name']}_{$item['value']}'>{$item['title']}</label><br>";
        }
        $output .= "</div></div>";
        return $output;
    }

    public static function renderWizardMenu($current_step){
        $steps = self::$steps;
        $output = "<ul class='cm-wizard-menu'>";
        foreach ($steps as $key => $step) {
            $num = $key;
            $selected = $num == $current_step ? 'class="selected"' : '';
            $output .= "<li {$selected} data-step='$num'>Step $num: {$step['title']}</li>";
        }
        $output .= "</ul>";
        return $output;
    }

    public static function getStepTitle($current_step){
        $steps = self::$steps;
        $title = "Step {$current_step}: ";
        $title .= $steps[$current_step]['title'];
        return $title;
    }

    //Custom functions
    public static function getRolesOptions(){
        global $wp_roles;
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        $result = array();
        if (!empty($wp_roles) AND is_array($wp_roles->roles)) foreach ($wp_roles->roles as $name => $role) {
            $result[] = [
                'title' => $role['name'],
                'value' => $name
            ];
        }
        return $result;
    }

}

CMDM_SetupWizard::init();
