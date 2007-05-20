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
$numrow = 0;	
$resmax = $DBHandle_max -> Execute($QUERY);

if ($resmax)
	$numrow = $resmax -> RecordCount();
else if ($FG_DEBUG>0) {
	echo "Error: ";
	echo $DBHandle_max->Error_Msg();
	echo "<br>No user info. <br>\n";
}

if ($numrow == 0) exit();
$customer_info =$resmax -> fetchRow();

if( $customer_info [13] != "t" && $customer_info [13] != "1" ) {
	if ($FG_DEBUG>2)
		echo "customer info[13] = " .$customer_info [13] ."<br>\n";
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
<table width="100%" align="center">
<tr>
<td align="center">	
<table width="90%" class="tablebackgroundblue" align="center">
<tr >
<td><img src="./images/personal.png" align="left" class="kikipic"/></td>
<td width="50%"><font class="fontstyle_002">
<?php echo gettext("LAST NAME");?> :</font>  <font class="fontstyle_007"><?php echo $customer_info[2]; ?></font>
<br/><font class="fontstyle_002"><?php echo gettext("FIRST NAME");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[3]; ?></font>
<br/><font class="fontstyle_002"><?php echo gettext("EMAIL");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[10]; ?></font> 
<br/><font class="fontstyle_002"><?php echo gettext("PHONE");?> :</font><font class="fontstyle_007"> <?php echo $customer_info[9]; ?></font> 
<br/><font class="fontstyle_002"><?php echo gettext("FAX");?> :</font><font class="fontstyle_007"> <?php echo $customer_info[11]; ?></font> 

</td>
<td width="50%">
<font class="fontstyle_002"><?php echo gettext("ADDRESS");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[4]; ?></font> 
<br/><font class="fontstyle_002"><?php echo gettext("ZIP CODE");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[8]; ?></font> 
<br/><font class="fontstyle_002"><?php echo gettext("CITY");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[5]; ?></font> 
<br/><font class="fontstyle_002"><?php echo gettext("STATE");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[6]; ?></font> 
<br/><font class="fontstyle_002"><?php echo gettext("COUNTRY");?> :</font> <font class="fontstyle_007"><?php echo $customer_info[7]; ?></font> 
</td></tr>
</table>


</td>
</tr>
</table>
<?php } ?>
<br>
<table width="100%" align="center" >
<tr>
	<td align="center">
		<table width="80%" align="center" class="tablebackgroundcamel">
		<tr>
			<td><img src="./images/gnome-finance.png" class="kikipic"/></td>
			<td width="50%">
			<br><font class="fontstyle_002"><?php echo gettext("CARD NUMBER");?> :</font><font class="fontstyle_007"> <?php echo $customer_info[0]; ?></font>
			<br></br>
			</td>
			<td width="50%">
			<br><font class="fontstyle_002"><?php echo gettext("BALANCE REMAINING");?> :</font><font class="fontstyle_007"> <?php echo $credit_cur.' '.$customer_info[14]; ?> </font>
			<br></br>
			</td>
			<td valign="bottom" align="right"><img src="./images/help_index.png" class="kikipic"></td>
		</tr>
		</table>
	</td>
</tr>
</table>


<?php if ($A2B->config["epayment_method"]['enable']){ ?>

<table width="100%">
<tr>
<td valign=top align=center>
<img src="./images/paypal_logo.png" /> &nbsp;&nbsp;
<img  src="http://www.moneybookers.com/images/banners/88_en_mb.png" width=88 height=31 border=0>&nbsp;&nbsp;
<img src="./images/authorize.png" />
</td>
</tr>
<tr>
<td>
<div id="div2200" style="display:visible;">
<div id="kiblue">
<div class="w4">
	<div class="w2">
<table width="80%" align="center">
	<tr>
		<?php
			$arr_purchase_amount = split(":", EPAYMENT_PURCHASE_AMOUNT);
			if (!is_array($arr_purchase_amount)) $arr_purchase_amount[0]=10;

			foreach($arr_purchase_amount as $value){
		?>

		<td align="center"> <br>
			<font size="1"><?php echo gettext("Click below to buy")."<br>".gettext("credit for");?> <font color="red"><b><?php echo $value.' '.PAYPAL_CURRENCY_CODE ?></b></font></font>
			<form action="checkout_payment.php" method="post">
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
				<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.png" border="0" name="submit"
				alt='<?php  gettext("Make payments - it's fast, free and secure!");?>'>
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
<center><span><?php echo gettext('The fee from $5 is $0.45, from $10 is $0.59, from $20 is $0.88, from $40 is $1.46.').' <br><font class="fontstyle_002">'.gettext('Paypal Fee Calculator').'</font>';?> <a target="_blank" href="http://www.ppcalc.com/">http://www.ppcalc.com/</a></b></span></center>
<br>

<?php }else{ ?>
<br></br><br></br>

<?php } ?>
<?php
	include("PP_footer.php");
?>
