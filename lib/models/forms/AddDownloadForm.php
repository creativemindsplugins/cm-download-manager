<?php
include_once CMDM_PATH . '/lib/helpers/Form.php';
class CMDM_AddDownloadForm extends CMDM_Form
{   

    public function init($params = array())
    {
        if(isset($params['edit_id']))
        {
            $editId = $params['edit_id'];
            $this->addElement(
                    CMDM_Form_Element::factory('hidden', 'edit_id')
                            ->setValue($editId)
            );
        }
        $nonce = wp_create_nonce('edit');
        $this->addElement(
            CMDM_Form_Element::factory('hidden', '_wpnonce')
                ->setValue($nonce)
        );
        $this->setId('CMDM_AddDownloadForm')
                ->setEnctype('multipart/form-data')
                ->addElement(
                        CMDM_Form_Element::factory('text', 'title')
                        ->setLabel(__('Title', 'cm-download-manager'))
                        ->setRequired()
                );
        if (CMDM_GroupDownloadPage::showVersionField()) {
                $this->addElement(
                        CMDM_Form_Element::factory('text', 'version')
                        ->setLabel(__('Version', 'cm-download-manager'))
                        ->setRequired()
                );
        }

        //
        $download_id = CMDM_BaseController::_getParam("id");
        $screenshotsCaches = array();
        $screenshotsV2 = array();
        // Edit
        if($download_id){
            $download = CMDM_GroupDownloadPage::getInstance($download_id);
            $screenshotsCaches = $download->getScreenshotsCaches();
            $screenshotsV2 = $download->getScreenshotsV2();
            if(empty($screenshotsV2)){
                // migrate screenshots from previous version to v2
                $screenshots = $download->getScreenshots();
                if($screenshots && is_array($screenshots)){
                    $download->migrateOldScreenshotsToV2($screenshots);
                }
                $screenshotsV2 = $download->getScreenshotsV2();
            }
        }
        $screenshotsCachesValue = $screenshotsCaches ? json_encode($screenshotsCaches) : "[]";

        //
        $this->addElement(
                CMDM_Form_Element::factory('fileUploader', 'package')
                ->setLabel(__('File', 'cm-download-manager'))
                ->setDescription('(' . __('Allowed extensions', 'cm-download-manager') . ': ' . implode(', ', get_option(CMDM_GroupDownloadPage::ALLOWED_EXTENSIONS_OPTION, CMDM_CmdownloadController::getDefaultAllowedExtensions())) . ')')
                ->addValidator('fileExtension', get_option(CMDM_GroupDownloadPage::ALLOWED_EXTENSIONS_OPTION, CMDM_CmdownloadController::getDefaultAllowedExtensions()))
                ->setRequired()
        )
        ->addElement(CMDM_Form_Element::factory('multiCheckbox', 'categories')
                ->setLabel(__('Category (max. 3)', 'cm-download-manager'))
                ->setRequired()
                ->setOptions(CMDM_GroupDownloadPage::getMainCategories())
        )
        ->addElement(
                CMDM_Form_Element::factory('visual', 'description')
                ->setLabel(__('Description', 'cm-download-manager'))
                ->setSize(5, 100)
                ->setRequired()
        )

        // Part of the previuos version of the screenshots system
        // TO DO: Remove in the next versions
        // ->addElement(
        //         CMDM_Form_Element::factory('PLUploader', 'screenshots')
        //         ->setLabel(__('Screenshots', 'cm-download-manager'))
        //         ->setDescription(sprintf(__('(Max. %d. File Size Limit %s)', 'cm-download-manager'), 4, '1mb'))
        //         ->setAttribs(array(
        //             'uploadUrl' => home_url('/cmdownload/screenshots'),
        //             'fileSizeLimit' => '1mb',
        //             'fileTypes' => 'jpg,gif,png',
        //             'fileTypesDescription' => __('Images', 'cm-download-manager'),
        //             'screenshotsCaches' => $screenshotsCachesValue
        //         ))
        // )
        // 
        // Part of the previuos version of the screenshots system
        // TO DO: Remove in the next versions
        // $this->addElement(
        //         CMDM_Form_Element::factory('hidden', 'screenshots-caches')
        //         ->setValue($screenshotsCachesValue)
        // );
        
        ->addElement(
                CMDM_Form_Element::factory('WpUploader', 'screenshots_v2')
                ->setLabel(__('Screenshots', 'cm-download-manager'))
                ->setValue($screenshotsV2)
        );

        if(current_user_can('manage_options') AND CMDM_GroupDownloadPage::showAdminRecommend())
        {
            $this->addElement(
                    CMDM_Form_Element::factory('checkbox', 'admin_supported')
                            ->setLabel(__('Admin Recommended', 'cm-download-manager'))
            );
        }
        $this->addElement(
                CMDM_Form_Element::factory('checkbox', 'support_notifications')
                        ->setLabel(__('Notify me on new support topics', 'cm-download-manager'))
        );
        $this->addElement(
                CMDM_Form_Element::factory('submit', 'submit')
                        ->setValue(isset($editId) ? __('Update', 'cm-download-manager') : __('Add', 'cm-download-manager'))
                        ->setAttribs(array(
                            'class' => 'button',
                        ))
        );
        if(isset($editId))
        {
            $this->getElement('package')->setRequired(false);
        }
    }

}