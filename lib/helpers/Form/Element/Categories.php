<?php

require_once CMDM_PATH . "/lib/helpers/Form/Element.php";

class CMDM_Form_Element_Categories extends CMDM_Form_Element {

	protected $_options = array();
	protected $_removeable = true;
	protected $afterHTML = '';

	public function setOptions($options) {
		$this->_options = $options;
		return $this;
	}

	public function getOptions() {
		return $this->_options;
	}

	public function setRemoveable($removeable = true) {
		$this->_removeable = $removeable;
		return $this;
	}

	public function isRemoveable() {
		return $this->_removeable;
	}

	public function render() {
		
		$html = '<input type="text" class="cmdm-find-category" placeholder="'. CMDM_Labels::getLocalized('upload_find_category') .'">';
		$html .='<ul ' . $this->_getClassName() . '>';
		$html .= $this->_renderOptions($this->getOptions());
		$html .="</ul>";
        $html .= $this->afterHTML;
		return $html;
	}

	protected function _renderOptions($options, $depth = 0) {
		$html = '';
		$dropdown = CMDM_Settings::getOption(CMDM_Settings::OPTION_SHOW_CHILD_CATEGORIES_IN_DROPDOWN);
		foreach($options as $term) {
			$key = $term->term_id;
			$category = CMDM_Category::getInstance($term);
			if (!$category->canUpload()) continue;
			$val = $term->name;
			$readonly = $this->getReadonly();
			$html .= '<li>';

            $html .= '<input type="checkbox" value="' . $key . '" id="' . $this->_id . $key . '"  name="' . $this->_id . '[]"';
            if ( ( !empty($this->_value) && in_array($key, $this->_value) ) ) {
                if( !$this->isRemoveable() ) $readonly = true;
                $html.=' checked="checked"';
            }
            $_readonly = '';
            if( $readonly )
            {
                $_readonly = ' readonly="readonly"';
            }
            $html .= $this->_getStyle() . $_readonly . $this->_getOnClick();
            // if ($key == $this->getValue())
            //     $html .= ' selected="selected"';
            $html .= ' />';

			
			$html .= '<label for="' . $this->_id . $key . '">' . $val . '</label>';
			if (!empty($term->children)) {
				$renderOptions = $this->_renderOptions($term->children, $depth+1);
				if($dropdown) {
					$svg = file_get_contents(CMDM_PATH . '/views/resources/imgs/caret-down.svg');
					$html .= '<div class="cmdm-categories-show">'. $svg . '</div>';
					$html .= '<ul class="cmdm-categories-children" style="display: none" >'. $renderOptions .'</ul>';
				}
				else {
					$html .= '<ul class="cmdm-categories-children" >'. $renderOptions  .'</ul>';
				}
			}
			$html .= '</li>';
		}
		return $html;
	}
    public function __toString() {
        $html = '';
		$class = esc_attr($this->getId());
        $required = ($this->isRequired())?'<span class="required">*</span>':'';
        $label = $this->getLabel();
        $description = $this->getDescription();
        $description = empty($description) ? '' : '<span class="field_descr cmdm-help-sign" data-tooltip="' . $description . '">?</span>';
        $label = empty($label)?'':'<label data="" for="'.$this->getId().'" class="CMDM-form-label">'.$this->getLabel()  . $required . '</label>'.$description;
        $html .= apply_filters('cmdm_add_text_before_row', $html, $this->getId());
        $html .= '<tr class="'. $class .'"><td>'.$label.'</td><td>'.$this->render().'</td></tr>';
        return $html;
    }

}
