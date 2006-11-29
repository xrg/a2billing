<?php
include ("lib/defines.php");
include ("lib/module.access.php");

if (! has_rights (ACX_ACCESS)){
      Header ("HTTP/1.0 401 Unauthorized");
      Header ("Location: PP_error.php?c=accessdenied");
      die();
}


getpost_ifset(array('sid', 'carry',  'choose_currency'));

$DBHandle  = DbConnect();
?>
<p class='pay-title'> <?= _("Payment / Pay back") ?> </p>
	<br><br><br>
<?php
if (! isset($carry))
	$carry = 'f';
if (isset($sid)) {
	$QUERY = "SELECT pay_session(" .
		$DBHandle->Quote($sid) . ', ' .
		$DBHandle->Quote($_SESSION['agent_id']) . ',' .
		'true, ' . //close session
		$DBHandle->Quote($carry) . ') ;';
	echo htmlspecialchars($QUERY);
	$res = $DBHandle->query($QUERY);
} else $res = false;

if ($res){
	echo gettype($res) . "<br>";
	$row = $res->fetchRow();
	print_r($row);
	$sum = $row[0];
	?>
	
	<p class="pay-message">
		<?= _("Paid ") . $sum ?>
	</p>
<?php } else { // no result from find sid
	echo $DBHandle->ErrorMsg() . "<br>";
	echo gettext("Cannot pay session!");
}

?>