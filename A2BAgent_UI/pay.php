<?php
include ("lib/defines.php");
include ("lib/module.access.php");

if (! has_rights (ACX_ACCESS)){
      Header ("HTTP/1.0 401 Unauthorized");
      Header ("Location: PP_error.php?c=accessdenied");
      die();
}


getpost_ifset(array('sid', 'carry',  'choose_currency','pback'));

$DBHandle  = DbConnect();

if (!isset($pback))
	$pback = 0;
include ("PP_header.php"); 

?>
<p class='pay-title'> <?php 
	if ($pback == 0) 
		echo _("Payment");
	else	echo _("Pay back"); 
	?> </p>
	<br><br><br>
<?php
if (! isset($carry))
	$carry = 'f';
if (isset($sid)) {
	if ($pback == 0 )
		$sql_cmd = "SELECT format_currency(0 - pay_session( %1, %2, true, %3), %4, %5);";
	else	$sql_cmd = "SELECT format_currency(pay_session( %1, %2, true, %3), %4, %5);";
	$QUERY = str_dbparams($DBHandle, $sql_cmd ,
		array($sid ,$_SESSION['agent_id'],$carry, strtoupper(BASE_CURRENCY), $_SESSION['currency'])) ;
	//echo htmlspecialchars($QUERY);
	$res = $DBHandle->query($QUERY);
} else $res = false;

if ($res){
	//echo gettype($res) . "<br>";
	$row = $res->fetchRow();
	//print_r($row);
	$sum = $row[0];
	?>
	
	<p class="pay-message">
		<?php if ($pback == 0 ) 
			echo _("Paid ") . $sum ;
		else	echo _("Paid back ") . $sum ; ?>
	</p>
<?php } else { // no result from find sid
	echo $DBHandle->ErrorMsg() . "<br>";
	echo gettext("Cannot pay session!");
}
?>
<br> <br>
	<p class='pay-bb'> <a href="booths.php"><?= _("Back to booths") ?> </a>
	</p>

<?php
include ("PP_footer.php"); 
?>