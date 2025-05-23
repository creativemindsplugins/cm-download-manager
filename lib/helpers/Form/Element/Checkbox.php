<?php

require_once CMDM_PATH."/lib/helpers/Form/Element.php";

class CMDM_Form_Element_Checkbox extends CMDM_Form_Element {
	
	protected $extraText = '';
	
    protected function _getChecked() {
        if ($this->isChecked()) return ' checked="checked"';
        else return '';
            
    }
    public function isChecked() {
        return ($this->getValue() == true);
    }
    public function setChecked($checked = true) {
        $this->setValue($checked);
        return $this;
    }
    public function render() {
        $html = '<label><input type="checkbox" id="'.$this->getId().'" name="'.$this->getId()
                .'" value="1"'.$this->_getChecked()
                .$this->_getClassName().$this->_getStyle().$this->_getReadonly().$this->_getRequired().$this->_getOnClick().' />';
        $html .= '</label>';
        return $html;
    }
    
}

?>
