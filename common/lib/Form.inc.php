<?php
	// Include all necessary files for a typical form

require_once("Class.A2Billing.inc.php");
require_once("Misc.inc.php");

require_once("Class.ElemBase.inc.php");
include_once("Class.html.inc.php");

require_once("Form/Class.FormHandler.inc.php");
include_once("Form/Class.FormHelpers.inc.php");
// and, now, include some common fields..

include_once("Form/Class.PKeyField.inc.php");
include_once("Form/Class.TextField.inc.php");
include_once("Form/Class.OptionField.inc.php");
include_once("Form/Class.NumField.inc.php");
include_once("Form/Class.RefField.inc.php");

// include the standard views

include_once("Form/Class.FormViews.inc.php");
include_once("Form/Class.ListView.inc.php");
include_once("Form/Class.AddView.inc.php");
include_once("Form/Class.EditView.inc.php");
include_once("Form/Class.DeleteView.inc.php");

?>
