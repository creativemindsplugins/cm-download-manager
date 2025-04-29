<?php
require_once CMDM_PATH . "/lib/helpers/Form/Element.php";
class CMDM_Form_Element_MyUpload extends CMDM_Form_Element
{
	

    public function _init() {
    	
        
    }

    public function setValue($value)
    {
        if(!empty($value) && !is_array($value))
        {
            $decodedValue = json_decode(stripslashes($value));
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
        if(!empty($value) && !is_array($value)) $_value = json_decode(stripslashes($value));
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

    public function renderScript()
    {
        
        return '';
    }

    public function render() {

        $value = $this->getValue();
        if(empty($value)) $value = array();
        if(!is_array($value))$value = json_decode(stripslashes($value));

        ob_start();
        ?>
		<div>UPLOAD here</div>
        <?php
        return ob_get_clean();
        
    }
}