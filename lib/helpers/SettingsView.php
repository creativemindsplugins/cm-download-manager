<?php

require_once CMDM_PATH . '/lib/helpers/SettingsViewAbstract.php';

class CMDM_SettingsView extends CMDM_SettingsViewAbstract {
	
	protected function getSubcategoryTitle($category, $subcategory) {
		$subcategories = $this->getSubcategories();
		if (isset($subcategories[$category]) AND isset($subcategories[$category][$subcategory])) {
			return CMDM_Settings::__($subcategories[$category][$subcategory]);
		} else {
			return CMDM_Settings::__($subcategory);
		}
	}
	
	
	protected function getCategories() {
		return apply_filters('cmdm_settings_pages', CMDM_Settings::$categories);
	}
	
	
	protected function getSubcategories() {
		return apply_filters('cmdm_settings_pages_groups', CMDM_Settings::$subcategories);
	}

    public static function renderOnlyin( $onlyin = 'Pro' ) {
        static $renderOnce = 0;
        ob_start();
        if ( ! $renderOnce ):
            ?>
            <style>
                .onlyinpro a {
                    color: #aaa !important;
                }

                .onlyinpro {
                    color: #aaa !important;
                }

                .cm-settings-row.hide, .onlyinpro.hide {
                    display: none !important;
                }
                .cm-settings-row:has(.onlyinpro){
                    opacity: 0.8;
                }
            </style>
            <?php
            $renderOnce = 1;
        endif;
        ?>
        <div class="onlyinpro">Available in <?php echo esc_attr( $onlyin ); ?> version and above.</div>
        <?php
        $content = ob_get_clean();

        return $content;
    }

    public function renderOptionControls($name, array $option = array()) {
        if (empty($option)) $option = CMDM_Settings::getOptionConfig($name);
        $result = '';
        if(isset($option['onlyin'])){
            $result = self::renderOnlyin($option['onlyin']);
        } else {
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
        }
        return sprintf('<div class="cm-settings-option-control">%s</div>', $result);
    }

    protected function renderRadio($name, $options, $currentValue = null) {
        if (is_null($currentValue)) {
            $currentValue = CMDM_Settings::getOption($name);
        }
        $result = '';
        $fieldName = esc_attr($name);
        if (is_callable($options)) $options = call_user_func($options);
        foreach ($options as $value => $text) {
            $fieldId = esc_attr($name .'_'. $value);
            $result .= sprintf('<div><label class="cmdm-option-switch"><input type="radio" name="%s" id="%s" value="%s" %s %s/><span class="cmdm-slider"></span></label> <span>%s</span></div>',
                $fieldName, $fieldId, esc_attr($value),
                ( $currentValue == $value ? ' checked="checked"' : ''),
                ($name == CMDM_Settings::OPTION_DEFAULT_VIEW && !in_array($value,[CMDM_Settings::INDEX_VIEW_TILES, CMDM_Settings::INDEX_VIEW_LIST])? ' disabled' : ''),

                esc_html(CMDM_Settings::__($text))
            );
        }
        return $result;
    }
	
}