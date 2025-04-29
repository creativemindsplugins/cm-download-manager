<?php

class CMDM_User {

	
	static function getRoles($userId) {
		if ($user = get_userdata($userId)) {
			return $user->roles;
		} else {
			return array();
		}
	}
	
}
