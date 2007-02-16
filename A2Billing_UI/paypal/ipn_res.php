<?php
include ("../lib/defines.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("../Public/form_data/FG_var_card.inc");


$DBHandle  = DbConnect();

/* FOR TEST
$txn_id = '44444444'.rand(1,999);
$item_number='2222222222';
$quantity  = 1; $tax =1.2;
$mc_gross = "50"; $mc_fee = "5"; $mc_currency = 'eur';
*/


// paypal email
$paypal_email = PAYPAL_EMAIL;

// email address where script should send notifications
$error_email = PAYPAL_ERROR_EMAIL;

// email header
$em_headers  = "From: ".PAYPAL_FROM_NAME." <".PAYPAL_FROM_EMAIL.">\n";		
$em_headers .= "Reply-To: ".PAYPAL_FROM_EMAIL."\n";
$em_headers .= "Return-Path: ".PAYPAL_FROM_EMAIL."\n";
$em_headers .= "Organization: ".PAYPAL_COMPANY_NAME."\n";
$em_headers .= "X-Priority: 3\n";


// -----------------


require("ipn_cls.php");

$paypal_info = $HTTP_POST_VARS;
$paypal_ipn = new paypal_ipn($paypal_info);

foreach ($paypal_ipn->paypal_post_vars as $key=>$value) {
	if (getType($key)=="string") {
		eval("\$$key=\$value;");
	}
}

$paypal_ipn->send_response();
$paypal_ipn->error_email = $error_email;

if (!$paypal_ipn->is_verified()) {
	$paypal_ipn->error_out("Bad order (PayPal says it's invalid)" . $paypal_ipn->paypal_response , $em_headers);
	die();
}


switch( $paypal_ipn->get_payment_status() )
{
	case 'Pending':
		
		$pending_reason=$paypal_ipn->paypal_post_vars['pending_reason'];
					
		if ($pending_reason!="intl") {
			$paypal_ipn->error_out("Pending Payment - $pending_reason", $em_headers);
			break;
		}


	case 'Completed':
	
		$date = date("D M j G:i:s T Y", time());
		error_log ("\n\n[$date] Get a request - \n\n"."payer_id , payment_date , txn_id , first_name , last_name , payer_email , payer_status , payment_type , memo , item_name , item_number , quantity , mc_gross , mc_currency , address_name , address_street , address_city , address_state , address_zip , address_country , address_status , payer_business_name , payment_status , pending_reason , reason_code , txn_type", 3, PAYPAL_LOGFILE);	
		
		/*$qry= "SELECT i.mc_gross, i.mc_currency FROM item_table as i WHERE i.item_number='$item_number'";		
		$res=mysql_query ($qry);
		$config=mysql_fetch_array($res);*/
	
		if ($paypal_ipn->paypal_post_vars['txn_type']=="reversal") {
			$reason_code=$paypal_ipn->paypal_post_vars['reason_code'];
			$paypal_ipn->error_out("PayPal reversed an earlier transaction.", $em_headers);
			// you should mark the payment as disputed now
		} else {
					
			/*if (
				(strtolower(trim($paypal_ipn->paypal_post_vars['business'])) == $paypal_email) && (trim($mc_currency)==$config['mc_currency']) && (trim($mc_gross)-$tax == $quantity*$config['mc_gross']) 
				) {*/
			if (strtolower(trim($paypal_ipn->paypal_post_vars['business'])) == $paypal_email) {

					$field_insert = "payer_id , payment_date , txn_id , first_name , last_name , payer_email , payer_status , payment_type , memo , item_name , item_number , quantity , mc_gross , mc_fee , tax , mc_currency , address_name , address_street , address_city , address_state , address_zip , address_country , address_status , payer_business_name , payment_status , pending_reason , reason_code , txn_type";
					
					$value_insert = "'$payer_id', '$payment_date', '$txn_id', '$first_name', '$last_name', '$payer_email', '$payer_status', '$payment_type', '$memo', '$item_name', '$item_number', $quantity, $mc_gross, $mc_fee, $tax, '$mc_currency', '$address_name', '".nl2br($address_street)."', '$address_city', '$address_state', '$address_zip', '$address_country', '$address_status', '$payer_business_name', '$payment_status', '$pending_reason', '$reason_code', '$txn_type'";
				
					$instance_sub_table = new Table("cc_paypal", $field_insert);
					
					$result_query = $instance_sub_table -> Add_table ($DBHandle, $value_insert, null, null);	
					
					if (!$result_query ){									
						$paypal_ipn->error_out("[item_number:$item_number] - This was a duplicate transaction or wrong sql (cc_paypal -> $field_insert|$value_insert) error:".$instance_sub_table -> errstr, $em_headers);
						break;
					}else{
						$paypal_ipn->error_out("[item_number:$item_number] -This was a successful transaction", $em_headers);	
					}
					
				$cardnumber = $item_number;
				if ($cardnumber>0){
						/* CHECK IF THE CARDNUMBER IS ON THE DATABASE */
						$instance_table_card = new Table("cc_card", "username, id");
						$FG_TABLE_CLAUSE_card = "username='".$cardnumber."'";
						$list_tariff_card = $instance_table_card -> Get_list ($DBHandle, $FG_TABLE_CLAUSE_card, null, null, null, null, null, null);						
						print_r($list_tariff_card);
						if ($cardnumber == $list_tariff_card[0][0]) $id = $list_tariff_card[0][1];
				}
				
				if ($id>0){
					$mycur = $currencies_list[strtoupper($mc_currency)][2];
					$addcredit = ($mc_gross-$mc_fee) / $mycur;		
				
					
					$instance_table = new Table("cc_card", "username, id");
					$param_update .= " credit = credit+'".$addcredit."'";
					$FG_EDITION_CLAUSE = " id='$id'";
					$instance_table -> Update_table ($DBHandle, $param_update, $FG_EDITION_CLAUSE, $func_table = null);
					
					$field_insert = "date, credit, card_id";
					$value_insert = "now(), '$addcredit', '$id'";
					$instance_sub_table = new Table("cc_logrefill", $field_insert);			
					$result_query = $instance_sub_table -> Add_table ($DBHandle, $value_insert, null, null);
					
					$field_insert = "date, payment, card_id";
					$value_insert = "now(), '$addcredit', '$id'";
					$instance_sub_table = new Table("cc_logpayment", $field_insert);			
					$result_query = $instance_sub_table -> Add_table ($DBHandle, $value_insert, null, null);		
				
				}
				
			} else {
				$paypal_ipn->error_out("Someone attempted a sale using a manipulated URL  ($qry)", $em_headers);
			}
		}
		break;
		
	case 'Failed':
		// this will only happen in case of echeck.
		$paypal_ipn->error_out("Failed Payment", $em_headers);
	break;

	case 'Denied':
		// denied payment by us
		$paypal_ipn->error_out("Denied Payment", $em_headers);
	break;

	case 'Refunded':
		// payment refunded by us
		$paypal_ipn->error_out("Refunded Payment", $em_headers);
	break;

	case 'Canceled':
		// reversal cancelled
		// mark the payment as dispute cancelled		
		$paypal_ipn->error_out("Cancelled reversal", $em_headers);
	break;

	default:
		// order is not good
		$paypal_ipn->error_out("Unknown Payment Status - " . $paypal_ipn->get_payment_status(), $em_headers);
	break;

} 

?>
