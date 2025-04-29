<?php


require_once CMDM_PATH . "/lib/helpers/Validate/Interface.php";


class CMDM_Validate_Max implements CMDM_Validate_Interface {

    protected $_errors = array();
    const OUT_OF_RANGE = 'Give correct number for "%label%"';

    public function __construct() {
		$this->_errors = array();
    }

    public function getErrors() {
        return $this->_errors;
    }

    public function isValid($value) {
        if (count($value) > 3 ) {
            $this->_errors[0] = self::OUT_OF_RANGE;
            return false;
        }
        else
            return true;
    }

}

?>
