<?php


require_once CMDM_PATH."/lib/helpers/Form/Element.php";

class CMDM_Form_Element_Combobox extends CMDM_Form_Element {
    protected $_options = array();
    public function setOptions($options) {
        $this->_options = $options;
        return $this;
    }
    public function getOptions() {
        $options = (array)$this->_options;
        if ($this->getPlaceHolder()) {
            array_unshift($options, $this->getPlaceHolder());
        }
        return (array)$options;
    }
    protected function _renderOptions() {
	
		$value = $this->getValue();
		if($value == null) {
			$value = 'all';
		}

        $html = '';
        foreach ($this->getOptions() as $key=>$val) {
            $html .= '<option value="'.($key===0?'':$key).'"';
            if ((is_array($value)&&in_array($key,$value)) || (!is_array($value)&& (string)$key == (string)$value)) {
            	$html .= ' selected="selected"';
            }
            $html .= '>'.$val.'</option>';
        }
        return $html;
    }
    public function render() {
		$attribs = array();
		if(!empty($this->_attribs))
			foreach($this->_attribs as $attr_name=>$value)
				$attribs[] = $attr_name.'="'.$value.'"';
				
		if(!empty($attribs))
			$attribs = implode(' ',$attribs);

		$name = $this->getId();
		if (!empty($this->_attribs['multiple'])) {
			$name .= '[]';
		}
		
        $html = '<select id="'.$this->getId().'" name="'. $name
                .'"'.(!empty($attribs)?' '.$attribs.' ':'').$this->_getClassName().$this->_getStyle().$this->_getReadonly().$this->_getDisabled().$this->_getOnClick().$this->_getRequired().'>';
        $html .= $this->_renderOptions();
        $html .= '</select>';
        return $html;
    }
}

?>
