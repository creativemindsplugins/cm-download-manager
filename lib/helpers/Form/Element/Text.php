<?php

require_once CMDM_PATH."/lib/helpers/Form/Element.php";

class CMDM_Form_Element_Text extends CMDM_Form_Element {
	
    protected function _init() {
        $this->addFilter('stringTrim')->addFilter('stripTags');
    }
    protected function _getMaxLength() {
        if (!empty($this->_attribs['maxLength'])) {
            return ' maxlength="' . $this->_attribs['maxLength'] . '"';
        } else {
            return '';
        }
    }
    protected function _getAutocomplete() {
                if (isset($this->_attribs['autocomplete'])) {
            // return ' autocomplete="off"';
            return ' autocomplete="nope"';
        } else {
            return '';
        }
    }
    public function setSize($size) {
        $this->_attribs['size'] = $size;
        return $this;
    }
     protected function _getSize() {
        $html = '';
        if (!empty($this->_attribs['size'])) {
            $html .= ' maxlength="' . $this->_attribs['size'] . '"';
        }
        return $html;
    }
    
    public function setType($type) {
    	$this->_attribs['type'] = $type;
    	return $this;
    }
    
    protected function _getType() {
    	if (empty($this->_attribs['type'])) {
    		return 'text';
    	} else {
    		return $this->_attribs['type'];
    	}
    }
    
    public function render() {
//     	var_dump($this->_attribs);
        $html = '<input type="'. $this->_getType() .'" id="'.$this->getId().'" name="'.$this->getId()
                .'" value="'.str_replace(array('[', ']'), array('&#91;', '&#93;'),esc_attr($this->getValue())).'"'
                .$this->_getClassName().$this->_getStyle().$this->_getReadonly().$this->_getRequired().$this->_getPlaceHolder().$this->_getMaxLength().$this->_getOnClick().$this->_getDisabled().$this->_getSize().$this->_getAutocomplete().' />';
        return apply_filters('cmdm_upload_form_text_field_render', $html, $this, $this->getId());
    }
    
    public function getValue() {
        return (is_scalar($this->_value) ? stripslashes($this->_value) : $this->_value);
    }
}

?>
