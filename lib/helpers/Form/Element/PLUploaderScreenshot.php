<?php
require_once CMDM_PATH . "/lib/helpers/Form/Element.php";
class CMDM_Form_Element_PLUploaderScreenshot extends CMDM_Form_Element
{

    public function _init()
    {
//        wp_deregister_script('plupload');
        wp_register_script('CMDM-plupload', CMDM_URL . '/views/resources/plupload/plupload.full.js', array('jquery'));
        wp_enqueue_script('CMDM-plupload');
        wp_register_script('CMDM-plupload-queue', CMDM_URL . '/views/resources/plupload/jquery.plupload.queue/jquery.plupload.queue.js', array('CMDM-plupload'));
        wp_enqueue_script('CMDM-plupload-queue');
        wp_register_style('CMDM-plupload-queue-style', CMDM_URL . '/views/resources/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css');
        wp_enqueue_style('CMDM-plupload-queue-style');
        wp_enqueue_style('json2');
    }

	public function setValue($value)
	{
		if(!empty($value) && !is_array($value))
		{
			$decodedValue = explode(',', stripslashes($value));
			if(empty($decodedValue))
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
                if($showError) $this->addError(sprintf(__e('Limit of uploaded files (%s) has been exceeded!', 'cm-download-manager'), $fileUploadLimit));
                return false;
            }
        }
        return parent::isValid($value, $context);
    }

    public function render()
    {
        $value = $this->getValue();
		if(empty($value)) $value = array();
		if(!is_array($value)) $value = explode(',', stripslashes($value));

        ob_start();
        ?>
        <div id="<?php echo esc_attr($this->getId()); ?>_container" class="plupload <?php echo esc_attr(isset($this->_attribs['class']) ? $this->_attribs['class'] : '');
        		?>" data-first-set-thumb="<?php echo intval(CMDM_Settings::getOption(CMDM_Settings::OPTION_SET_FIRST_IMAGE_AS_FETATURED)); ?>">
            <div id="<?php echo esc_attr($this->getId()); ?>_filelist">
                <?php foreach($value as $screenshotId) : ?>
                	<?php if ($screenshotId AND $screenshot = CMDM_Screenshot::getById($screenshotId)): ?>
	                    <div class="progressWrapper">
	                        <div class="progressImg" style="display:block">
	                            <i class="progressCancel" data-id="<?php echo esc_attr($screenshotId); ?>">&times;</i>
	                            <img src="<?php echo esc_attr($screenshot->getSmallUrl()); ?>" data-id="<?php echo esc_attr($screenshotId); ?>" />
	                        </div>
	                    </div>
	                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="clearfix"></div>
            <?php printf('<input type="button" id="%s" value="%s" />',
            	esc_attr($browseButtonId = $this->getId() . '_BrowseButton'),
            	esc_attr(CMDM_Labels::getLocalized('upload_select_files_btn'))
            ); ?>
            <?php printf('<input type="hidden" id="%s" name="%s" value="%s" data-upload-url="%s" data-size-limit="%s" data-unique-names="%s" '
				. 'data-file-types-description="%s" data-file-types="%s" data-flash-url="%s" data-silverlight-url="%s" data-screenshot="1" />',
				esc_attr($this->getId()),
				esc_attr($this->getId()),
				esc_attr(implode(',', $value)),
				esc_attr($this->_attribs['uploadUrl']),
				esc_attr($this->_attribs['fileSizeLimit']),
				intval(!empty($this->_attribs['unique_names'])),
				esc_attr($this->_attribs['fileTypesDescription']),
				esc_attr($this->_attribs['fileTypes']),
				esc_attr(CMDM_URL . '/views/resources/plupload/plupload.flash.swf'),
				esc_attr(CMDM_URL . '/views/resources/plupload/plupload.silverlight.xap')
            ); ?>
        </div>
        <?php
        return ob_get_clean();
    }
}