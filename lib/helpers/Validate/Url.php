<?php


require_once CMDM_PATH . "/lib/helpers/Validate/Interface.php";


class CMDM_Validate_Url implements CMDM_Validate_Interface {

    protected $_errors = array();


    public function getErrors() {
        return $this->_errors;
    }

    public function isValid($value) {
    	if (is_array($value)) {
    		foreach ($value as $v) {
    			if (!$this->isValid($v)) {
    				return false;
    			}
    		}
    		return true;
    	}
        else if(self::validate($value)){
                       return true;
        }else{
             $this->_errors = array('%label% is not valid URL address');
             return false;
        }

    }
    
    
    public static function validate($val) {
    	return filter_var($val, FILTER_VALIDATE_URL);
    }

}

?>
