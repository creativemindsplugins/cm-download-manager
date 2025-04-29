<?php

abstract class CMDM_SettingsViewAbstract {
	
	protected $categories = array();
	protected $subcategories = array();
	
	
	
	public function render() {
		$result = '';
		$categories = $this->getCategories();
		foreach ($categories as $category => $title) {
			$result .= $this->renderCategory($category);
		}
		return $result;
	}
	
	
	
	public function renderCategory($category) {
		$result = '';
		$subcategories = $this->getSubcategories();
		if (!empty($subcategories[$category])) {
			foreach ($subcategories[$category] as $subcategory => $title) {
				$result .= $this->renderSubcategory($category, $subcategory);
			}
		}
		return '<div class="settings_'. $category .'"><div class="cm-settings-toggle-wrapper">
					<div class="cm-settings-collapse-toggle">Toggle all</div>
					<hr>
					<input type="submit" class="cm-sticky-submit" value="Save settings"></div>'. $result .'</div>';
	}
	
	
	abstract protected function getCategories();
	
	
	abstract protected function getSubcategories();
	
	
	
	public function renderSubcategory($category, $subcategory, $onlyContent = false) {
		$result = '';
		$subcategories = $this->getSubcategories();
		if (isset($subcategories[$category]) AND isset($subcategories[$category][$subcategory])) {
			$options = CMDM_Settings::getOptionsConfigByCategory($category, $subcategory);
			foreach ($options as $name => $option) {
				$result .= $this->renderOption($name, $option);
			}
		}
		
		$result = apply_filters('cmdm_settings_render_subcategory_content', $result, $category, $subcategory);
		
		if ($onlyContent) return $result;
		else return apply_filters('cmdm_settings_render_subcategory', sprintf(
			'<div class="cmdm-settings-section %s">
				<h3 class="cm-settings-collapse-btn dashicons-before dashicons-arrow-right">
					<span>%s</span>
				</h3>
				<div class="cm-settings-collapse-container cm-settings-collapse-close">
					%s
				</div>
			</div>',
			'settings_'. $category .'_'. $subcategory,
			esc_html($this->getSubcategoryTitle($category, $subcategory)),
			$result
		), $category, $subcategory);
	}

	
	
	public function renderOption($name, array $option = array()) {
		if (empty($option)) $option = CMDM_Settings::getOptionConfig($name);
		$result = $this->renderOptionTitle($option)
				. $this->renderOptionControls($name, $option)
				. $this->renderOptionDescription($option);
		return sprintf('<div class="cm-settings-row">%s</div>', $result);
	}
	
	
	public function renderOptionTitle($option) {
		return sprintf('<div class="cm-settings-option-name">%s:</div>', CMDM_Settings::__($option['title']));
	}
	
	
	public function renderOptionControls($name, array $option = array()) {
		if (empty($option)) $option = CMDM_Settings::getOptionConfig($name);
		$result = '';
		switch ($option['type']) {
			case CMDM_Settings::TYPE_BOOL:
				$result = $this->renderBool($name);
				break;
			case CMDM_Settings::TYPE_INT:
				$result = $this->renderInputNumber($name, $option);
				break;
			case CMDM_Settings::TYPE_TEXTAREA:
				$result = $this->renderTextarea($name);
				break;
			case CMDM_Settings::TYPE_RICH_TEXT:
				$result = $this->renderRichText($name);
				break;
			case CMDM_Settings::TYPE_RADIO:
				$result = '<div class="multiline">' . $this->renderRadio($name, $option['options']) . '</div>';
				break;
			case CMDM_Settings::TYPE_SELECT:
				$result = $this->renderSelect($name, $option['options']);
				break;
			case CMDM_Settings::TYPE_MULTISELECT:
				$result = $this->renderMultiSelect($name, $option['options']);
				break;
			case CMDM_Settings::TYPE_MULTICHECKBOX:
				$result = $this->renderMultiCheckbox($name, $option['options']);
				break;
			case CMDM_Settings::TYPE_CSV_LINE:
				$result = $this->renderCSVLine($name);
				break;
			case CMDM_Settings::TYPE_USERS_LIST:
				$result = $this->renderUsersList($name);
				break;
			case CMDM_Settings::TYPE_CUSTOM:
				$result = $this->renderCustomField($name);
				break;
			default:
				$result = $this->renderInputText($name);
		}
		return sprintf('<div class="cm-settings-option-control">%s</div>', $result);
	}
	
	public function renderOptionDescription($option) {
		$result = (isset($option['desc']) ? $option['desc'] : '');
		return sprintf('<div class="cm-settings-option-desc">%s</div>', $result);
	}
	
	
	protected function renderInputText($name, $value = null) {
		if (is_null($value)) {
			$value = CMDM_Settings::getOption($name);
		}
		return sprintf('<input type="text" name="%s" value="%s" />', esc_attr($name), esc_attr($value));
	}
	
	protected function renderInputNumber($name,  $option = array()) {
		$min = (isset($option['min'])) ? "min='" . floatval($option['min']) . "'" : "";
		$max = (isset($option['max'])) ? "max='". floatval($option['max']) . "'" : "";
		$step = (isset($option['step'])) ? "step='". floatval($option['step']) . "'" : "";
		return sprintf('<input type="number" name="%s" value="%s" %s %s %s/>', esc_attr($name), esc_attr(CMDM_Settings::getOption($name)), $min, $max, $step);
	}
	
	protected function renderCSVLine($name) {
		$value = CMDM_Settings::getOption($name);
		if (is_array($value)) $value = implode(',', $value);
		return $this->renderInputText($name, $value);
	}
	
	
	protected function renderCustomField($name) {
		$options = CMDM_Settings::getOptionsConfig();
		if (isset($options[$name]) AND isset($options[$name]['content'])) {
			$config = $options[$name];
			$content = $options[$name]['content'];
			$value = CMDM_Settings::getOption($name);
			if (is_callable($content)) $content = call_user_func($content, $name, $value, $config);
			return $content;
		}
	}
	
	
	protected function renderUsersList($name) {
		return sprintf('<div class="suggest-user" data-field-name="%s">
			<ul>%s</ul>
			<div><span>Find user:</span><input type="text" /> <input type="button" value="'. esc_attr(CMDM::__('Add')) .'" /></div>
		</div>', $name, $this->renderUsersListItems($name));
	}
	
	
	protected function renderUsersListItems($name) {
		$value = CMDM_Settings::getOption($name);
		if (!empty($value)) $users = get_users(array('include' => $value));
		$result = '';
		if (!empty($users)) foreach ($users as $user) {
			$result .= self::renderUsersListItem($name, $user->ID, $user->user_login);
		}
		return $result;
	}
	
	
	static public function renderUsersListItem($name, $userId, $login) {
		$template = '<li data-user-id="%d" data-user-login="%s">
			<a href="%s">%s</a> <a href="" class="btn-list-remove">&times;</a>
			<input type="hidden" name="%s[]" value="%d" /></li>';
		return sprintf($template,
			intval($userId),
			$login,
			esc_attr(get_edit_user_link($userId)),
			esc_html($login),
			$name,
			intval($userId)
		);
	}
	
	
	protected function renderTextarea($name) {
		return sprintf('<textarea name="%s" cols="60" rows="5">%s</textarea>', esc_attr($name), esc_html(CMDM_Settings::getOption($name)));
	}
	
	
	protected function renderBool($name) {
//		return $this->renderRadio($name, array(0 => 'No', 1 => 'Yes'), intval(CMDM_Settings::getOption($name)));
		return $this->renderCheckbox($name, intval(CMDM_Settings::getOption($name)));
	}


	protected function renderCheckbox($name, $currentValue = null) {
		if (is_null($currentValue)) {
			$currentValue = CMDM_Settings::getOption($name);
		}
		$result = '';
		$checked = ( (int) $currentValue !== 0 ? ' checked="checked"' : '');
		$fieldName = esc_attr($name);
		$fieldId = esc_attr($name .'_'. $currentValue);
			$result .= sprintf('<label class="cmdm-option-switch"><input type="hidden" name="%s" value="0"/><input type="checkbox" name="%s" id="%s" value="%s" %s onclick="this.value=this.checked?1:0;"/><span class="cmdm-slider"></span></label>',
				$fieldName,$fieldName, $fieldId, $currentValue, $checked);
		return $result;
	}


	protected function renderRadio($name, $options, $currentValue = null) {
		if (is_null($currentValue)) {
			$currentValue = CMDM_Settings::getOption($name);
		}
		$result = '';
		$fieldName = esc_attr($name);
		if (is_callable($options)) $options = call_user_func($options);
		foreach ($options as $value => $text) {
            if($name == CMDM_Settings::OPTION_DEFAULT_VIEW && !in_array($value,['tiles','list'])){
                $disabled = true;
            } else {
                $disabled = false;
            }
			$fieldId = esc_attr($name .'_'. $value);
			$result .= sprintf('<div><label class="cmdm-option-switch"><input type="radio" name="%s" id="%s" value="%s" %s %s/><span class="cmdm-slider"></span></label> <span>%s</span></div>',
					$fieldName, $fieldId, esc_attr($value),
					( $currentValue == $value ? ' checked="checked"' : ''),
                    $disabled ? 'disabled="disabled"' : '',
					esc_html(CMDM_Settings::__($text))
			);
		}
		return $result;
	}

	
	protected function renderSelect($name, $options, $currentValue = null) {
		return sprintf('<div class="cmdm-settings-select-el"><select name="%s">%s</select></div>', esc_attr($name), $this->renderSelectOptions($name, $options, $currentValue));
	}
	
	
	protected function renderSelectOptions($name, $options, $currentValue = null) {
		if (is_null($currentValue)) {
			$currentValue = CMDM_Settings::getOption($name);
		}
		$result = '';
		if (is_callable($options)) $options = call_user_func($options, $name);
		foreach ($options as $value => $text) {
		    if(($name == CMDM_Settings::OPTION_VIEWING_PERMISSIONS || $name == CMDM_Settings::OPTION_DOWNLOADING_PERMISSIONS || $name == CMDM_Settings::OPTION_ADDING_PERMISSIONS) && !in_array($value,['all','loggedin','roles']) ){
		        $disabled = true;
            } else {
                $disabled = false;
            }
			$result .= sprintf('<option value="%s" %s>%s</option>',
				esc_attr($value),
				( $this->isSelected($value, $currentValue) ? ' selected="selected"' : '') . ( $disabled ? 'disabled="disabled"' : '' ),
				esc_html(CMDM_Settings::__($text))
			);
		}
		return $result;
	}
	
	
	protected function isSelected($option, $value) {
		if (is_array($value)) {
			return in_array($option, $value);
		} else {
			return ($option == $value);
		}
	}
	
	
	
	protected function renderMultiSelect($name, $options, $currentValue = null) {
		return sprintf('<div><select name="%s[]" multiple="multiple">%s</select></div>',
			esc_attr($name), $this->renderSelectOptions($name, $options, $currentValue));
	}
	
	
	protected function renderMultiCheckbox($name, $options, $currentValue = null) {
		$result = '';
		if (is_callable($options)) {
			$options = call_user_func($options);
		}
		foreach ($options as $value => $label) {
			$result .= $this->renderMultiCheckboxItem($name, $value, $label, $currentValue);
		}
//		return $result ;
		return '<div class="cmdm-option-multicheckbox">' . $result . '</div>';
	}
	
	
	protected function renderMultiCheckboxItem($name, $value, $label, $currentValue = null) {
		if (is_null($currentValue)) $currentValue = CMDM_Settings::getOption($name);
		if (!is_array($currentValue)) $currentValue = array();
		return sprintf('<div><label class="cmdm-option-switch"><input type="checkbox" name="%s[]" value="%s" %s/>
				<span class="cmdm-slider"></span></label> <span>%s</span></div>',
			esc_attr($name),
			esc_attr($value),
			(in_array($value, $currentValue) ? ' checked="checked"' : ''),
			esc_html(CMDM_Settings::__($label))
		);
	}
	
	
	protected function renderRichText($name) {
		ob_start();
		wp_editor(CMDM_Settings::getOption($name), $name, array(
			'textarea_name' => $name,
			'textarea_rows' => 10,
		));
		return ob_get_clean();
	}
	
	
}
