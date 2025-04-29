<?php
require_once CMDM_PATH . "/lib/helpers/Form/Element.php";
class CMDM_Form_Element_PLUploader extends CMDM_Form_Element
{

    public function _init()
    {
		
    }

    public function setValue($value)
    {
        if(!empty($value) && !is_array($value))
        {
            $decoded_values = explode(',', stripslashes($value));
            if(empty($decoded_values))
            {
                parent::setValue('');
                return;
            }

        }
        parent::setValue($value);
    }

    public function isValid($value, $context = null, $showError = false, $obj = null)
    {
        if(!empty($value) && !is_array($value)) $_value = explode(',', stripslashes($value));
        else $_value = $value;

        $_value = array_filter(array_map('trim', $_value), 'strlen');
        if($this->isRequired() && empty($_value))
        {
            if($showError) $this->addError(sprintf(__('%s needs to be uploaded', 'cm-download-manager'), $this->getLabel()));
            return false;
        }
        $fileUploadLimit = isset($this->_attribs['fileUploadLimit']) ? $this->_attribs['fileUploadLimit'] : 0;
        if($fileUploadLimit > 0)
        {
            if(is_array($_value) && count($_value) > $fileUploadLimit)
            {
                if($showError) $this->addError(sprintf(__('Limit of uploaded files (%s) has been exceeded!', 'cm-download-manager'), $fileUploadLimit));
                return false;
            }
        }
        return parent::isValid($value, $context);
    }

    public function render()
    {
        $value = $this->getValue();

        if(empty($value))
        {
            $value = array();
        }
        if(!is_array($value))
        {
            $value = array_map(function($id) {
            	if ($id) return CMDM_DownloadFile::getById($id);
            }, explode(',', stripslashes($value)));
        }
        
        $value = array_filter($value);
        
        $attachmentsIds = array();
        
        
        $attachmentItemTemplate = '<div class="progressWrapperList %s">
                        <div class="progressImg" style="display:block">
                            <input type="text" name="attachmentName[%d]" value="%s" title="'. esc_attr(CMDM::__('Enter the file title'))
                            .'" /><i class="progressCancel" data-name="%d">&times;</i>
							%s
                        </div>
                    </div>';
        

        ob_start();
        ?>
        <div id="<?php echo esc_attr($this->getId()); ?>_container" class="plupload <?php echo esc_attr(isset($this->_attribs['class']) ? $this->_attribs['class'] : ''); ?>">
            <div id="<?php echo esc_attr($this->getId()) ?>_filelist">
            	<?php
            	
            	printf($attachmentItemTemplate, 'template', 0, '', 0, '');
            	
                foreach($value as $attachment) {
                	$attachmentsIds[] = $attachmentId = $attachment->getId();
                	$extra = apply_filters('cmdm_upload_file_row_extra', '', $attachment->getDownloadId(), $attachment->getId());
                	printf($attachmentItemTemplate, '', $attachmentId, $attachment->getName(), $attachmentId, $extra);
                }
                	
                ?>
            </div>
            <div class="clearfix"></div>
            
            <?php printf('<input type="button" id="%s" value="%s" />',
            	esc_attr($browseButtonId = $this->getId() . '_BrowseButton'),
            	esc_attr(CMDM_Labels::getLocalized('upload_select_file_btn'))
            ); ?>
            <?php printf('<input type="hidden" id="%s" name="%s" value="%s" data-upload-url="%s" data-size-limit="%s" data-unique-names="%s" '
				. 'data-file-types-description="%s" data-file-types="%s" data-flash-url="%s" data-silverlight-url="%s" data-nonce="%s" />',
				esc_attr($this->getId()),
				esc_attr($this->getId()),
				esc_attr(implode(',', $attachmentsIds)),
				esc_attr($this->_attribs['uploadUrl']),
				esc_attr($this->_attribs['fileSizeLimit']),
				intval(!empty($this->_attribs['unique_names'])),
				esc_attr($this->_attribs['fileTypesDescription']),
				esc_attr($this->_attribs['fileTypes']),
				esc_attr(CMDM_URL . '/views/resources/plupload/plupload.flash.swf'),
				esc_attr(CMDM_URL . '/views/resources/plupload/plupload.silverlight.xap'),
                wp_create_nonce('cmdm_file_upload')
            ); ?>
        </div>
        <?php
        return ob_get_clean();
    }

    
    
}
