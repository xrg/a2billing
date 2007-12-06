<?php
require_once(DIR_COMMON."Class.A2Billing.inc.php");

	/** Generic form
	    The form is the main handler of data->html interaction.

	*/

class FormHandler
{	
	public $FG_DEBUG = 0;
	protected $action = null;
	private $rights_checked = false;
	
	public $a2billing; ///< Reference to an a2billing instance
	
	
	/** Before this class can be initted, its rights should be
	   proven. Any attempt to use the class w/o them will fail. */
	public function checkRights($rights){
		if (!has_rights($rights)){
			Header ("HTTP/1.0 401 Unauthorized");
			Header ("Location: PP_error.php?c=accessdenied");
			die();
		}
		$this->rights_checked = true;
	}

	function init($sA2Billing= null){
		if (!$this->rights_checked){
			error_log("Attempt to use FormHandler w/o rights!");
			die();
		}
		if ($sA2Billing)
			$this->a2billing= &$sA2Billing;
		else
			$this->a2billing= &A2Billing::instance();
	}


	/** Render the view/edit form for the HTML body */
	public function Render(){
		if (!$this->rights_checked){
			error_log("Attempt to use FormHandler w/o rights!");
			die();
		}
	}
};

?>