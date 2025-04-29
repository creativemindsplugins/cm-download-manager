<?php

class CMDM_SidebarHelper {

	static function getWidgetSidebars($widgetId) {
		$widgetId = strtolower($widgetId);
		$results = array();
		$sidebars = get_option('sidebars_widgets');
		foreach ($sidebars as $sidebarId => $widgets) {
			if (is_array($widgets)) foreach ($widgets as $sidebarWidget) {
				if (substr($sidebarWidget, 0, strlen($widgetId)) == $widgetId) {
					if ('wp_inactive_widgets' != $sidebarId) {
						$results[] = $sidebarId;
					}
				}
			}
		}
		return $results;
	}
}
