<?php

class CMDM_LocalStorageService {

    private static $instances = [];

    protected function __construct() {}

    public static function getInstance() {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public function save($downloadId, $attachments) {

        return array_filter($attachments, function ($attachment) use ($downloadId) {
            if (!$attachment->getParentPostId()) {
                $attachment->attach($downloadId);
                return true;
            }
            return ($attachment->getParentPostId() == $downloadId);
        });
    }

    public function download(CMDM_GroupDownloadPage $download) {
        $download->download();
    }

    public function delete(CMDM_GroupDownloadPage $download) {
        return $download->delete();

    }

    public function downloadMultipleAttachemnts(CMDM_GroupDownloadPage $download, $attachmentsIds, $single_dl = true) {
        $attachments = array_filter(array_map(function ($id) use ($download, $single_dl) {
            $attachment = CMDM_DownloadFile::getById($id);
            if ($single_dl) {
                if ($attachment->getParentPostId() == $download->getId()) {
                    return $attachment;
                }
            } else {
                return $attachment;
            }
        }, $attachmentsIds));
        foreach ($attachments as $attachment) {
            $file_name[] = $attachment->getFileName();
        }
        if (count($download->getAttachmentsIds()) == count($attachments)) {
            // Download in standard way all attachments
            $download->download($file_name);
        } else if (count($attachments) == 1) {
            // Download single attachment in standard way
            $attachment = reset($attachments);
            $attachment->download($file_name);
            flush();
            ob_flush();
            exit;
        } else {
            // Create a ZIP file to download multiple attachments
            $dir = get_temp_dir();
            $zip_file_name = 'cmdm_' . $download->getId() . '_' . sha1(serialize($attachmentsIds)) . '.zip';
            $filePath = trailingslashit($dir) . $zip_file_name;

            if (!class_exists('ZipArchive')) {
                throw new Exception('ZipArchive class is not available.');
            }
            $zip = new ZipArchive();
            if ($zip->open($filePath, ZipArchive::CREATE) !== TRUE) {
                throw new Exception("cannot open <$filePath>\n");
            }
            foreach ($attachments as $attachment) {
                $zip->addFile($attachment->getPath(), $attachment->getFileName());
            }
            $zip->close();

            $fileSize = filesize($filePath);

            $ext = pathinfo($filePath, PATHINFO_EXTENSION);
            if (!empty($ext))
                $ext = '.' . $ext;

            $mimeType = 'application/zip';
            set_time_limit(3600 * 24);
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false); // required for certain browsers
            header('Content-Description: File Transfer');
            if (CMDM_GroupDownloadPage::isIeBrowser()) {
                header("Content-Disposition: attachment; filename=\"" . mb_convert_encoding($download->getFileName(), "ISO-8859-2", "UTF-8") . "\";");
            } else {
                header("Content-Disposition: attachment; filename=\"" . $zip_file_name . "\";");
            }
            header("Content-Transfer-Encoding: binary");
            header("Content-Length: " . $fileSize);

            header("Content-type: " . $mimeType);

            flush();
            ob_flush();
            readfile($filePath);
            exit;
        }
    }

    public function saveScreenshots(CMDM_Screenshot $screenshot, CMDM_GroupDownloadPage $download){
        $screenshot->attach($download->getId());
    }

}
