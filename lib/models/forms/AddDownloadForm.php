<?php

include_once CMDM_PATH . '/lib/helpers/Form.php';

class CMDM_AddDownloadForm extends CMDM_Form
{
    protected $_editId = null;

    protected $is_admin;

    public function init($params = array())
    {

        $user = new WP_user(get_current_user_id());
        $this->is_admin = ($user) ? in_array('administrator', $user->roles): false;

    	$this->setAction($_SERVER['REQUEST_URI']);

    	wp_register_script('CMDM-plupload', CMDM_URL . '/views/resources/plupload/plupload.full.js', array('jquery'));
    	wp_enqueue_script('CMDM-plupload');
    	wp_register_script('CMDM-plupload-queue', CMDM_URL . '/views/resources/plupload/jquery.plupload.queue/jquery.plupload.queue.js', array('CMDM-plupload'));
    	wp_enqueue_script('CMDM-plupload-queue');
    	wp_register_style('CMDM-plupload-queue-style', CMDM_URL . '/views/resources/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css');
    	wp_enqueue_style('CMDM-plupload-queue-style');
    	wp_enqueue_style('json2');

    	wp_enqueue_script('cmdm-upload-helper', CMDM_RESOURCE_URL . 'js/upload-helper.js', array('jquery', 'CMDM-plupload', 'CMDM-plupload-queue', 'jquery-ui-sortable'), CMDM_VERSION);
    	wp_localize_script('cmdm-upload-helper', 'CMDM_UploadHelper', array(
    		'ajax_url' => admin_url( 'admin-ajax.php' ),
    		'limitSingleFile' => 1,
    	));
    	// WP Media Library
    	wp_enqueue_script('media-upload');


    	$this->addElement(
                    CMDM_Form_Element::factory('hidden', 'nonce')
                            ->setValue(wp_create_nonce('cmdm_edit_form'))
            , 1);

        if( isset($params['edit_id']) )
        {
            $this->_editId = $params['edit_id'];
            $editId = $params['edit_id'];
            $this->addElement(
                    CMDM_Form_Element::factory('hidden', 'edit_id')
                            ->setValue($editId)
            , 1);
        }

        if ( !empty( $this->_editId )) {
        	$this->addElement(
        	    CMDM_Form_Element::factory('custom', 'show_download_id')->setLabel('ID')->setCustomContent($this->_editId)
            , 1 );
        }

        $types = ['file' => CMDM_Labels::getLocalized('upload_type_file')];

        $this->setId('CMDM_AddDownloadForm')
                ->setEnctype('multipart/form-data');

        $titleElement = CMDM_Form_Element::factory('text', 'title')
                ->setLabel(CMDM_Labels::getLocalized('upload_title_field'))
                ->setRequired();

        if( is_null($this->_editId) ) // check unique title only when adding download
        {
//             $titleElement->addValidator('unique');
        }
        $this->addElement($titleElement, 3);

        if( CMDM_Settings::getOption(CMDM_Settings::OPTION_SHOW_VERSION) )
        {
            $this->addElement(
                    CMDM_Form_Element::factory('text', 'version')
                            ->setLabel(CMDM_Labels::getLocalized('upload_version'))
                            ->setRequired()
            , 2);
        }

        $allowedExtensions = CMDM_Settings::getOption(CMDM_Settings::OPTION_ALLOWED_EXTENSIONS);

        if (in_array('php', $allowedExtensions)){
            unset($allowedExtensions[array_search('php', $allowedExtensions)]);
        }
        $combobox = CMDM_Form_Element::factory('combobox', 'packageType')
            ->setLabel(CMDM_Labels::getLocalized('upload_download_type'))
            ->setOptions($types)
            ->setRequired()
            ->setClassName('package-type')
            ->setTrClass('hidden');

        $this->addElement($combobox, 6);

        $this->addElement(
                        CMDM_Form_Element::factory('PLUploader', 'package')
                        ->setLabel(CMDM_Labels::getLocalized('upload_file_field'))
                        ->setDescription('(' . CMDM_Labels::getLocalized('upload_allowed_extensions') . ' ' . implode(', ', $allowedExtensions) . ')')

                		->setAttribs(array(
	                        	'class' => 'cmdm-plupload-queue cmdm-package-file',
	                            'uploadUrl'            => home_url('/cmdownload/upload'),
	                            'fileSizeLimit'        => '99999mb',
	                            'fileTypes'            => implode(',', $allowedExtensions),
	                            'fileTypesDescription' => CMDM_Labels::getLocalized('upload_allowed_extensions') . ': ' . implode(', ', $allowedExtensions),
                				'unique_names' => false,
                                'fileUploadLimit' => 1
	                        ))
                , 7);



        $label = sprintf(CMDM_Labels::getLocalized('upload_category_field'), 3);

        $categoriesInputs = CMDM_Form_Element::factory('categories', 'categories')
                        ->setLabel($label)
                        ->setRequired(CMDM_Settings::getOption(CMDM_Settings::OPTION_CATEGORIES_REQUIRED))
                        ->setOptions(CMDM_GroupDownloadPage::getCategoriesTreeArray())
        				->setClassName('cmdm-categories-tree')
                        ->addValidator('max');

        $this->addElement($categoriesInputs, 5);


        if (CMDM_Settings::getOption(CMDM_Settings::OPTION_DOWNLOAD_SHOW_DESCRIPTION)) {
	        $descriptionInput = CMDM_Form_Element::factory('visual', 'description')
	        				->setLabel(CMDM_Labels::getLocalized('download_tab_description'))
	                        ->setSize(5, 100)
                            ->setRequired();
	        $this->addElement($descriptionInput, 4);
        }

        if ( CMDM_Settings::getOption(CMDM_Settings::OPTION_ALLOW_SCREENSHOTS) /* OR current_user_can('manage_options') */ ) {
			$desc = CMDM_Labels::getLocalized('upload_screenshots_field_description');
			if (!CMDM_Settings::getOption(CMDM_Settings::OPTION_ALLOW_SCREENSHOTS)) {
				$desc .= ' ' . CMDM_Labels::getLocalized('upload_screenshots_disabled_msg');
			}
			$screenshotsField = ((current_user_can('upload_files')) ?
								CMDM_Form_Element::factory('ScreenshotThickBox', 'screenshots') :
								CMDM_Form_Element::factory('PLUploaderScreenshot', 'screenshots'));

			$this->addElement(
					$screenshotsField
							->setLabel(CMDM_Labels::getLocalized('upload_screenshots_field'))
							->setDescription($desc)
							->setAttribs(array(
								'class' => 'cmdm-plupload-queue',
								'uploadUrl'            => home_url('/cmdownload/screenshots'),
								'fileSizeLimit'        => '10mb',
								'fileTypes'            => 'jpg,jpeg,gif,png',
								'fileTypesDescription' => CMDM_Labels::getLocalized('upload_images'),
								'unique_names' => true,
							))
				, 12);
			$this->addElement(
					CMDM_Form_Element::factory('DownloadThumbnail', 'thumbnail')
							->setLabel(CMDM_Labels::getLocalized('upload_thumb_field'))
							->setDescription(CMDM_Labels::getLocalized('upload_thumb_field_desc'))
				, 13);
		}


        if( current_user_can('manage_options') )
        {
            $this->addElement(
                    CMDM_Form_Element::factory('checkbox', 'admin_supported')
                            ->setLabel(CMDM_Labels::getLocalized('upload_admin_recommended'))
                , 19);
        }
        $this->addElement(
                CMDM_Form_Element::factory('checkbox', 'support_notifications')
                        ->setLabel(CMDM_Labels::getLocalized('upload_notify_support'))
            , 20);

        do_action('cmdm_upload_form_bottom', $this, $this->_editId);

        $button_class = "cmdm-button";
        $single_download_button_class = 'cmdm-button cmdm-button-primary cmdm-button-large';
        $this->addElement(
                CMDM_Form_Element::factory('submit', 'submit')
                        ->setValue(isset($editId) ? CMDM_Labels::getLocalized('upload_form_update_btn') : CMDM_Labels::getLocalized('upload_form_add_btn'))
        				->setClassName($single_download_button_class)
        , 998);


	    do_action('cmdm_after_upload_form_bottom', $this, $this->_editId);

        if( isset($editId) )
        {
        	if ($package = $this->getElement('package')) {
        		$package->setRequired(false);
        	}
        }

    }

    public function isValid(array $data, $showErrors = false)
    {

        $this->populate($data);
        $valid = true;
        if( !empty($this->_editId) )
        {
            $download = CMDM_GroupDownloadPage::getInstance($this->_editId);
        }

        foreach($this->_elements as $key => $value)
        {
            if( $key == 'package' )
            {
                if( $this->getElement('packageType')->getValue() == 'file' )
                {
                    if( empty($this->_editId) || !$download->getAttachmentsIds() )
                    {
                        $value['elem']->setRequired();
                    }
                    $valid = $value['elem']->isValid($this->getElement($key)->getValue(), $data, $showErrors, $this) && $valid;
                }
            }
            else
            {
                $valid = $value['elem']->isValid($this->getElement($key)->getValue(), $data, $showErrors) && $valid;
            }
        }
        return $valid;
    }


    public function getEditId() {
		return $this->_editId;
	}

}
