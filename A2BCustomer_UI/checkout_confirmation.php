<?php

include ("./lib/defines.php");
include ("./lib/module.access.php");
include ("./lib/Form/Class.FormHandler.inc.php");

include ("./lib/epayment/classes/payment.php");
include ("./lib/epayment/classes/order.php");
include ("./lib/epayment/classes/currencies.php");
include ("./lib/epayment/includes/general.php");
include ("./lib/epayment/includes/html_output.php");
include ("./lib/epayment/includes/configure.php");
include ("./lib/epayment/includes/sessions.php");
include ("./lib/epayment/includes/loadconfiguration.php");


//include ("./form_data/FG_var_callerid.inc");

if (! has_rights (ACX_ACCESS)){
	   //Header ("HTTP/1.0 401 Unauthorized");
	   //Header ("Location: PP_error.php?c=accessdenied");
	   //die();
}
getpost_ifset(array('amount'));
$HD_Form = new FormHandler("cc_payment_methods","payment_method");

$HD_Form -> setDBHandler (DbConnect());
$HD_Form -> init();

$_SESSION["p_module"] = $payment;
$_SESSION["p_amount"] = $amount;

include("PP_header.php");
$HD_Form -> create_toppage ($form_action);

$payment_modules = new payment($payment);
$order = new order($amount);



if (is_array($payment_modules->modules)) {
    $payment_modules->pre_confirmation_check();
  }
?>

<?php
  if (isset($$payment->form_action_url)) {
    $form_action_url = $$payment->form_action_url;
  } else {
    $form_action_url = tep_href_link("checkout_process.php", '', 'SSL');
  }

  echo tep_draw_form('checkout_confirmation.php', $form_action_url, 'post');

  if (is_array($payment_modules->modules)) {
    echo $payment_modules->process_button();
  }
?>
 <br>
 <br>
<table width=80% align=center class="infoBox">
<tr height="15">
    <td colspan=2 class="infoBoxHeading">&nbsp;Please confirm your order;</td>
</tr>
<tr>
    <td width=50%>&nbsp;</td>
    <td width=50%>&nbsp;</td>
</tr>
<tr>
    <td align=right>Total Amount: &nbsp;</td>
    <td align=left><?php echo $amount?> USD</td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr>
</table>
<br>
<table class="infoBox" width="80%" cellspacing="0" cellpadding="2" align=center>
   <tr height="25">
   <td  align=left class="main"> <b>Please click button to confirm your order.</b>
   </td>
          <td align=right halign=center >
            <input type="image" src="./images/button_confirm_order.gif" alt="Confirm Order" border="0" title="Confirm Order">
             &nbsp;</td>
          </tr>
 </table>
</form>
