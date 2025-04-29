<?php

function CMDM_number_of_downloads($id = 0)
{
    if( $id == 0 )
    {
        global $post;
        $id = $post->ID;
    }
    $download = CMDM_GroupDownloadPage::getInstance($id);
    if( $download )
    {
        return $download->getNumberOfDownloads();
    }
    else
    {
        return 0;
    }
}

function CMDM_update_date($id = 0)
{
    if( $id == 0 )
    {
        global $post;
        $id = $post->ID;
    }
    $download = CMDM_GroupDownloadPage::getInstance($id);
    if( $download )
    {
        return $download->getUpdated();
    }
    else
    {
        return 0;
    }
}

function CMDM_is_top_query($query = null)
{
    global $wp_query;
    if (empty($query)) $query = $wp_query;
    return (isset($query->is_top) && $query->is_top === true);
}

function CMDM_get_url($controller, $action = '', $params = array())
{
    return CMDM_BaseController::getUrl($controller, $action, $params);
}

function CMDM_get_screenshots($id = 0) {
    if( $id == 0 )
    {
        global $post;
        $id = $post->ID;
    }
    $download = CMDM_GroupDownloadPage::getInstance($id);
    if( $download )
    {
        $screenshots = $download->getScreenshots();
        if (CMDM_Settings::getOption(CMDM_Settings::OPTION_HIDE_THUMB_SCREENSHOTS)) {
        	return array_filter($screenshots, function($screenshot) { return !$screenshot->isDownloadsThumbnail(); });
        }
        return $screenshots;
    }
    else
    {
        return 0;
    }
}

function CMDM_get_default_screenshot()
{
    return CMDM_CmdownloadController::getDefaultScreenshot();
}
