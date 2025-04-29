<?php
abstract class CMDM_SettingsAbstract {
	const TEXT_DOMAIN = '';
	const TYPE_BOOL = 'bool';
	const TYPE_INT = 'int';
	const TYPE_STRING = 'string';
	const TYPE_TEXTAREA = 'textarea';
	const TYPE_RICH_TEXT = 'rich_text';
	const TYPE_RADIO = 'radio';
	const TYPE_SELECT = 'select';
	const TYPE_MULTISELECT = 'multiselect';
	const TYPE_MULTICHECKBOX = 'multicheckbox';
	const TYPE_CSV_LINE = 'csv_line';
	const TYPE_USERS_LIST = 'users_list';
	const TYPE_CUSTOM = 'custom';

	public static $categories = array();
	public static $subcategories = array();
	public static $options = array();

	public static function getOptionsConfig() {
		return array();
	}

	public static function getOptionsConfigByCategory($category, $subcategory = null) {
		if ( empty(static::$options) ) {
			static::$options = static::getOptionsConfig();
		} elseif ( !empty($category) ) {
			$categories = static::getCategories();
			if ( !empty($categories) && !in_array($category, $categories) ) {
				static::$options = static::getOptionsConfig();
			}
		}
		$result = array();
		foreach (static::$options as $name => $val) {
			if ($val['category'] == $category) {
				if (is_null($subcategory) OR $val['subcategory'] == $subcategory) {
					$result[$name] = $val;
				}
			}
		}
		return $result;
	}

	public static function getOptionConfig($name) {
		if ( empty(static::$options) || !isset(static::$options[$name]) ) {
			static::$options = static::getOptionsConfig();
		}
		if (isset(static::$options[$name])) {
			return static::$options[$name];
		}
		
		return null;
	}

	public static function setOption($name, $value) {
		static::$options = array();
		$options = static::getOptionsConfig();
		if ( isset($options[$name]) ) {
			$field = $options[$name];
			update_option($name, static::cast($value, $field['type']), $autoload = true);
			if (isset($field['afterSave']) AND is_callable($field['afterSave'])) {
				call_user_func($field['afterSave'], $name);
			}
		} else {
			update_option($name, $value, $autoload = true);
		}
		static::$options = static::getOptionsConfig();
	}

	public static function getOption($name) {
		if ( ! isset(static::$options[$name])) {
			static::$options = static::getOptionsConfig();
		}
		if ( isset( static::$options[ $name ] ) ) {
			$field = static::$options[$name];
			$defaultValue = ($field['default'] ?? null);
			$result = static::cast(get_option($name, $defaultValue), $field['type'], $defaultValue);
		} else {
			$result = get_option($name);
		}
		return apply_filters(get_called_class() . '_'. __FUNCTION__, $result, $name);
	}

	public static function getCategories() {
		$categories = array();
		if ( empty(static::$options) ) {
			static::$options = static::getOptionsConfig();
		}
		foreach ( static::$options as $option ) {
			$categories[] = $option['category'];
		}
		return $categories;
	}

	public static function getSubcategories($category) {
		$subcategories = array();
		if ( empty(static::$options) ) {
			static::$options = static::getOptionsConfig();
		}
		foreach ( static::$options as $option) {
			if ($option['category'] == $category) {
				$subcategories[] = $option['subcategory'];
			}
		}
		return $subcategories;
	}

	protected static function boolval($val) {
		return (boolean) $val;
	}

	protected static function arrayval($val) {
		if (is_array($val)) return $val;
		else if (is_object($val)) return (array)$val;
		else return array();
	}

	protected static function cast($val, $type, $defaultValue = null) {
		if ($type == static::TYPE_STRING) {
			return trim(strval($val));
		}
		else if ($type == static::TYPE_BOOL) {
			return (intval($val) ? 1 : 0);
		}
		else if (in_array($type, array(static::TYPE_MULTISELECT, static::TYPE_USERS_LIST, static::TYPE_MULTICHECKBOX))) {
			if (empty($val)) return array();
			else return $val;
		}
		else if ($type == static::TYPE_RADIO) {
			if (is_null($val)) {
				$val = $defaultValue;
			}
			return $val;
		}
		else if ($type == static::TYPE_MULTISELECT OR $type == static::TYPE_USERS_LIST) {
			if (empty($val)) return array();
			else return $val;
		}
		else {
			$castFunction = $type . 'val';
			if (function_exists($castFunction)) {
				return call_user_func($castFunction, $val);
			}
			else if (method_exists(__CLASS__, $castFunction)) {
				return call_user_func(array(__CLASS__, $castFunction), $val);
			} else {
				return $val;
			}
		}
	}

	protected static function csv_lineval($value) {
		if (!is_array($value)) $value = explode(',', $value);
		return $value;
	}

	public static function processPostRequest() {
		$options = static::getOptionsConfig();
		$post = array_map('stripslashes_deep', $_POST);
		do_action('cmdm_settings_save_before', $post, static::getOptionsConfig());
		foreach ($options as $name => $optionConfig) {
			if (isset($post[$name])) {
				static::setOption($name, $post[$name]);
			}
		}
		do_action('cmdm_settings_save_after', $post, static::getOptionsConfig());
	}

	public static function userId($userId = null) {
		if (empty($userId)) $userId = get_current_user_id();
		return $userId;
	}

	public static function isLoggedIn($userId = null) {
		$userId = static::userId($userId);
		return !empty($userId);
	}
	public static function getRolesOptions() {
		global $wp_roles;
		$result = array();
		if (!empty($wp_roles) AND is_array($wp_roles->roles)) foreach ($wp_roles->roles as $name => $role) {
			$result[$name] = $role['name'];
		}
		return $result;
	}
	public static function getPagesOptions() {
		$pages = get_pages(array('number' => 100));
		$result = array(null => '--');
		if (is_array($pages)) foreach ($pages as $page) {
			$result[$page->ID] = $page->post_title;
		}
		return $result;
	}
	static function writeLocalizationFile() {
		$added = array();
		$textDomain = static::TEXT_DOMAIN;
		$printLine = function($text) use ($textDomain, &$added) {
			if (is_numeric($text)) return;
			if (empty($added[$text])) {
				$added[$text] = true;
				printf('# @ %s'. PHP_EOL .'msgid "%s"'. PHP_EOL .'msgstr "%s"'. PHP_EOL . PHP_EOL,
					$textDomain,
					str_replace('"', '\"', $text),
					str_replace('"', '\"', $text)
				);
			}
		};
		$config = static::getOptionsConfig();
		foreach ($config as $optionName => $option) {
			$printLine($option['title']);
			if (!empty($option['desc'])) {
				$printLine($option['desc']);
			}
			if (!empty($option['options']) AND is_array($option['options'])) {
				foreach ($option['options'] as $optionLabel) {
					$printLine($optionLabel);
				}
			}
		}
		foreach (static::$categories as $key => $category) {
			$printLine($category);
			if (isset(static::$subcategories[$key])) {
				foreach (static::$subcategories[$key] as $subcategory) {
					$printLine($subcategory);
				}
			}
		}
	}
	static function __($msg) {
		return __($msg, static::TEXT_DOMAIN);
	}
}
