<?php
require_once CMDM_PATH . "/lib/helpers/Form/Element.php";

class CMDM_Form_Element_WpUploader extends CMDM_Form_Element
{
    protected function _init() {
        wp_enqueue_media();
        wp_enqueue_style( CMDM_PREFIX . 'wp-uploader', CMDM_RESOURCE_URL . 'form-element-wpuploader/styles.css', array(), CMDM_VERSION);
        wp_enqueue_script( CMDM_PREFIX . 'wp-uploader', CMDM_RESOURCE_URL . 'form-element-wpuploader/scripts.js', array('jquery'), CMDM_VERSION, true );
    }

    public function render()
    {
        $screenshotsV2 = $this->getValue();
        if(!is_array($screenshotsV2)){
            $screenshotsV2 = json_decode($screenshotsV2); 
        }

        $mimetypes = wp_get_mime_types();
        $allowed_mime = array($mimetypes["jpg|jpeg|jpe"], $mimetypes["gif"], $mimetypes["png"], $mimetypes["bmp"], $mimetypes["tiff|tif"]);

        ob_start();
        ?>

        <div class="cmdm-wp-uploader-wrapper">
            <ul>
                <?php if($screenshotsV2) :?>
                    <?php foreach ($screenshotsV2 as $screenshotId):?>
                        <?php $img_src = wp_get_attachment_image_url( $screenshotId, array(70, 70) ); ?>
                        <?php if($img_src): ?>
                            <li>
                                <img class="cmdm-thumbnail" src="<?php echo $img_src;?>" />
                                <button type="button" class="cmdm-remove-image-button" id="<?php echo $screenshotId;?>"><span class="dashicons dashicons-no-alt"></span></button>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
            <div>
                <button type="button" class="cmdm-upload-image-button button" data-allowed-types="<?php echo esc_attr(json_encode($allowed_mime)) ?>">
                    <?php _e('Load Image', 'cm-download-manager'); ?>
                </button>
                <input type="hidden" name="<?php echo $this->getId();?>" id="<?php echo $this->getId();?>" value="<?php echo json_encode($screenshotsV2) ?>" autocomplete = "off" />
            </div>
        </div>

        <?php
        $html = ob_get_contents();
        ob_clean();

        return $html;
    }
}