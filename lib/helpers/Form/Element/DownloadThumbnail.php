<?php

require_once CMDM_PATH."/lib/helpers/Form/Element.php";

class CMDM_Form_Element_DownloadThumbnail extends CMDM_Form_Element {

	public function render() {
		
		wp_enqueue_script('cmdm-form-thumbnail', CMDM_RESOURCE_URL . 'js/form-thumbnail.js', array('jquery'));
		wp_localize_script('cmdm-form-thumbnail', 'cmdm_thumbnail_data', ['img_title' => CMDM::__('Set as thumbnail')]);
		
		$value = $this->getValue();

        if (is_null($value)) {
            $value = '';
        }

		if (!empty($value)) {
			$thumb = CMDM_Screenshot::getById($value);
		}
		$html = '<input type="hidden" id="'.$this->getId().'" name="'.$this->getId()
			.'" value="'.htmlentities(stripslashes($value)).'"'
			.$this->_getClassName()
			.' />';
		$html .= sprintf('<div class="choose">%s</div><div class="thumb"><a href="#">&times;</a>%s</div>',
			CMDM_Labels::getLocalized('upload_thumb_click_to_set'),
			(empty($thumb) ? '' : sprintf('<img src="%s" alt="Thumb" />', esc_attr($thumb->getUrl())))
		);
		return sprintf('<div class="cmdm-form-thumbnail %s">%s</div>', (empty($thumb) ? ' empty' : ''), $html);
	}

}
