<?php
include ("lib/defines.php");
include ("lib/module.access.php");

if (! has_rights (ACX_ACCESS)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}

//require (LANGUAGE_DIR.FILENAME_USERINFO);


$QUERY = "SELECT  username, credit, lastname, firstname, address, city, state, country, zipcode, phone, email, fax, lastuse, activated, currency FROM cc_card WHERE username = '".$_SESSION["pr_login"]."' AND uipass = '".$_SESSION["pr_password"]."'";

$DBHandle_max  = DbConnect();
$resmax = $DBHandle_max -> query($QUERY);
$numrow = $resmax -> numRows();
if ($numrow == 0) exit();


$customer_info =$resmax -> fetchRow();

if( $customer_info [13] != "t" && $customer_info [13] != "1" ) {
	 exit();
}

$customer = $_SESSION["pr_login"];

getpost_ifset(array('posted', 'Period', 'frommonth', 'fromstatsmonth', 'tomonth', 'tostatsmonth', 'fromday', 'fromstatsday_sday', 'fromstatsmonth_sday', 'today', 'tostatsday_sday', 'tostatsmonth_sday', 'dsttype', 'sourcetype', 'clidtype', 'channel', 'resulttype', 'stitle', 'atmenu', 'current_page', 'order', 'sens', 'dst', 'src', 'clid'));

$currencies_list = get_currencies();

if (!isset($currencies_list[strtoupper($customer_info [14])][2]) || !is_numeric($currencies_list[strtoupper($customer_info [14])][2])) $mycur = 1;
else $mycur = $currencies_list[strtoupper($customer_info [14])][2];
$credit_cur = $customer_info[1] / $mycur;
$credit_cur = round($credit_cur,3);
?>

<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

//-->
</script>

<?php
	include("PP_header.php");
?><br>


<?php if ($A2B->config["webcustomerui"]['customerinfo']){ ?>
<table width="100%">
<tr>

<td>
<div id="div1000" style="display:visible;">
<div id="kiblue"><div class="w4">
	<img src="Css/kicons/personal.gif" class="kikipic"/>
	<div class="w2">
<table width="100%">
<tr>
<td width="50%">
<b><?php echo gettext("LAST NAME");?> :</b> <?php echo $customer_info[2]; ?>
<br/><b><?php echo gettext("FIRST NAME");?> :</b> <?php echo $customer_info[3]; ?>
<br/><b><?php echo gettext("EMAIL");?> :</b> <?php echo $customer_info[10]; ?>
<br/><b><?php echo gettext("PHONE");?> :</b> <?php echo $customer_info[9]; ?>
<br/><b><?php echo gettext("FAX");?> :</b> <?php echo $customer_info[11]; ?>

</td>
<td>
<b><?php echo gettext("ADDRESS");?> :</b> <?php echo $customer_info[4]; ?>
<br/><b><?php echo gettext("ZIP CODE");?> :</b> <?php echo $customer_info[8]; ?>
<br/><b><?php echo gettext("CITY");?> :</b> <?php echo $customer_info[5]; ?>
<br/><b><?php echo gettext("STATE");?> :</b> <?php echo $customer_info[6]; ?>
<br/><b><?php echo gettext("COUNTRY");?> :</b> <?php echo $customer_info[7]; ?>
</td></tr>
</table>

</div></div></div>
</td>
</tr>
</table>
<?php } ?>

<table width="100%">
<tr>
<td width="55"></td>
<td>
<div id="kiki"><div class="w1">
	<img src="Css/kicons/gnome-finance.gif" class="kikipic"/>
	<div class="w2">
<table width="90%">
<tr>
<td width="45%">
<br><b><?php echo gettext("CARD NUMBER");?> :</b>  <?php echo $customer_info[0]; ?>
<br></br>
</td>
<td>
<br><b><?php echo gettext("BALANCE REMAINING");?> :</b> <?php echo $credit_cur.' '.$customer_info[14]; ?> 
<br></br>
</td></tr>
</table>

</div></div></div>
</td>
<td width="55"></td>
</tr>
</table>


<?php if ($A2B->config["webcustomerui"]['paypal']){ ?>

<table width="100%">
<tr>
<td>
<div id="div2200" style="display:visible;">
<div id="kiblue"><div class="w4">
	<img src="Css/kicons/paypal.gif" class="kikipic"/>
	<div class="w2">
<table width="80%" align="center">
	<tr>
		<?php
			$arr_purchase_amount = split(":", PAYPAL_PURCHASE_AMOUNT);
			if (!is_array($arr_purchase_amount)) $arr_purchase_amount[0]=10;

			foreach($arr_purchase_amount as $value){
		?>

		<td align="center"> <br>
			<font size="1"><?php echo gettext("Click below to buy<br>credit for");?> <font color="red"><b><?php echo $value.' '.PAYPAL_CURRENCY_CODE ?></font></b></font>
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
				<input type="hidden" name="notify_url" value="<?php echo PAYPAL_NOTIFY_URL ?>">
				<input type="hidden" name="rm" value="2">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="<?php echo PAYPAL_EMAIL ?>">
				<input type="hidden" name="item_name" value="<?php echo PAYPAL_ITEM_NAME ?>">
				<input type="hidden" name="item_number" value="<?php echo $customer_info[0]; ?>">
				<input type="hidden" name="amount" value="<?php echo $value ?>">
				<input type="hidden" name="no_note" value="1">
				<input type="hidden" name="currency_code" value="<?php echo PAYPAL_CURRENCY_CODE ?>">
				<input type="hidden" name="bn" value="PP-BuyNowBF">
				<input type="hidden" name="no_shipping" value="1">
				<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" border="0" name="submit" 
				alt='<?php  gettext("Make payments with PayPal - it's fast, free and secure!");?>'>
			</form>
		</td>
		<?php } ?>

	</tr>
</table>

</div></div></div>
</div>
</td>
</tr>
</table>
<center><span><?php echo gettext('The fee from $5 is $0.45, from $10 is $0.59, from $20 is $0.88, from $40 is $1.46. <br><b>Paypal Fee Calculator');?> <a target="_blank" href="http://www.ppcalc.com/">http://www.ppcalc.com/</a></b></span></center>
<br>

<?php }else{ ?>
<br></br><br></br>

<?php } ?>
<?php
	include("PP_footer.php");
?>
