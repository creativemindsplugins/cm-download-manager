<?php

class CMDM_StorageProvider {


    public static function getInstance() {
        require_once 'LocalStorageService.php';
        return CMDM_LocalStorageService::getInstance();
    }

}
