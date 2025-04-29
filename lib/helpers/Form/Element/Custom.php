<?php

require_once CMDM_PATH."/lib/helpers/Form/Element.php";

class CMDM_Form_Element_Custom extends CMDM_Form_Element {
	
	protected $customContent = '';
	
    
    public function render() {
        return $this->getCustomContent();
    }
    
    
    function getCustomContent() {
    	return $this->customContent;
    }
    
    
    function setCustomContent($text) {
    	$this->customContent = $text;
    	return $this;
    }
    
}

