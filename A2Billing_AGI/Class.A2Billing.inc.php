<?php
require_once('a2blib/Class.AbstractSession.inc.php');

/** Static configuration, core variables

	This class serves 2 purposes: loading the .ini file and
	providing the db handle

*/

class A2Billing extends AbstractSession {
	public static function &instance() {
		if (!self::$the_instance){
			self::$the_instance = new self();
			self::$the_instance->load_config();
		}
		
		return self::$the_instance;
	}
};

class ASession extends A2Billing {
};

?>
