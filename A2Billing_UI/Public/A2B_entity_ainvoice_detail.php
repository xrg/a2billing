<?php
$menu_section='menu_invoicing';
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/Form/Class.FormHandler.inc.php");

if (! has_rights (ACX_INVOICING)){ 
	Header ("HTTP/1.0 401 Unauthorized");
	Header ("Location: PP_error.php?c=accessdenied");
	die();
}

getpost_ifset(array('id','action','pay_amount','printable'));

$DBHandle = DbConnect();
$payment_sl = array();
$payment_sl["0"] = array( gettext("UNPAID"), "0");
$payment_sl["1"] = array( gettext("SENT-UNPAID"), "1");
$payment_sl["2"] = array( gettext("SENT-PAID"),  "2");
$payment_sl["3"] = array( gettext("PAID"),  "3");

$FG_DEBUG = 0;
$show_actions = true;
$show_history  = true;
$show_calls = true;
$show_calls_cumul = true;
$show_charges = true;
$currency = 'EUR';
$num_cols = 2;
$num_rows = 0;
$date_format = 'DD/MM/IYYY HH24:MI';
$date_only_format = 'DD/MM/IYYY';

if (isset($printable) && ($printable = 1)){
	$show_actions = false;
	$show_history  = false;
	$show_calls = true;
	$show_calls_cumul = true;
	$show_charges = true;
	$currency = 'EUR';
	$num_cols = 2;
	$num_rows = 50;
	$disp_printable = true;
}

include('PP_header.php');

?>
<style>
div.invoice_heading {
	font-size: 200%;
	margin-top: 1em;
	margin-bottom: 0.5em;
}
table.invoice_sum {
	background: #f0f0f0;
}
table.invoice_data {
}

table.invoice_list {
	border: thin solid black;
}
table.invoice_list thead td {
	border-bottom: thin solid black;
	font-weight: bold;
}

table.invoice_list td {
	border-bottom: thin solid gray;
}

table.invoice_list .total {
	font-weight: bold;
}

table.invoice_cols td {
	vertical-align: top;
}

</style>
<?php

switch ($action) {
	case 'pay':
		$QUERY = str_dbparams($DBHandle, 'SELECT agent_pay_invoice(%#1, %2);',
			array($id,$pay_amount));
		if ($FG_DEBUG > 2) echo "Pay Query: " . htmlspecialchars($QUERY) ."<br>\n";
		$res = $DBHandle->Execute($QUERY);
		if (! $res) {
			?><div> <?= _("Cannot pay invoice!") ?><br>
			<?= "Message: ". $DBHandle->ErrorMsg() ?></div>
			<?php
		}
	break;
};

$QUERY = str_dbparams($DBHandle,'SELECT to_char(invoicecreated_date,%4) AS invoicecreated_date, '.
	'to_char(cover_startdate,%4) AS cover_startdate, to_char(cover_enddate, %4) AS cover_enddate, '.
	'orderref, format_currency(amount, %3, %2) AS amount, format_currency(tax, %3, %2) AS tax, format_currency(total, %3, %2) AS total, total AS raw_total, '.
	'invoicetype, filename, payment_status, '.
	'name, email, location, vat, commission '.
	' FROM cc_invoices, cc_agent WHERE cc_invoices.agentid = cc_agent.id AND cc_invoices.id = %#1;',
	array($id, $currency,strtoupper(BASE_CURRENCY),$date_format));
$res = $DBHandle->Execute($QUERY);

if (!$res){
	if ($FG_DEBUG >0){
		echo "Query failed: " . htmlspecialchars($QUERY). "<br>\n";
		echo $DBHandle->ErrorMsg();
		echo "<br><br>\n";
	}
	exit(_("Cannot locate invoice."));
}
if ($res->RecordCount()<1)
	exit(_("Cannot locate invoice."));

$info_invoice = $res->fetchRow();

if ($FG_DEBUG>3)
	print_r($info_invoice);
?>
<div>
<table width="60%" cellpadding="0" cellspacing="0" class="invoice_data">
	<tr>
	<td width="35%">&nbsp; </td>
	<td width="65%">&nbsp; </td>
	</tr>
	<tr>
	<td><?php echo gettext("Name");?>&nbsp;: </td>
	<td><?= htmlspecialchars($info_invoice['name']) ?></td>
	<tr>
	<td><?php echo gettext("Address");?>&nbsp;: </td>
	<td><?= htmlspecialchars($info_invoice['location']) ?></td>
	</tr>
	<tr>
	<td><?php echo gettext("Invoice date");?>&nbsp;: </td>
	<td><?php display_dateformat($info_invoice['invoicecreated_date']); ?></td>
	</tr>
	<tr>
	<td><?php echo gettext("Period from");?>&nbsp;: </td>
	<td><?php display_dateformat($info_invoice['cover_startdate']); ?></td>
	</tr>
	<tr>
	<td><?php echo gettext("Period to");?>&nbsp;: </td>
	<td><?php display_dateformat($info_invoice['cover_enddate']); ?></td>
	</tr>

	<tr>
	<td><?php echo gettext("Status");?>&nbsp;: </td>
	<td><?= $payment_sl[$info_invoice['payment_status']][0] ?></td>
	</tr>
	<tr>
	<td><?php echo gettext("Order");?>&nbsp;: </td>
	<td><?= htmlspecialchars($info_invoice['orderref']) ?></td>
	</tr>
</table>

<?php
if ($show_actions){
	if ($info_invoice['payment_status']< 2)
		echo "Edit!<br>\n";
	if (($info_invoice['payment_status']== 0)||($info_invoice['payment_status'] == 1)){
	?>
	<form name="payForm" action="<?php echo $_SERVER['PHP_SELF']?>" method="post">
		<INPUT type="hidden" name="action" value="pay">
		<input type="hidden" name="id" value="<?= $id ?>">
		<?= str_params(_("Pay (in %1):"),array(BASE_CURRENCY),1) ?>
		<input class="form_enter" name="pay_amount" size="10" maxlength="10" value="<?= $info_invoice['raw_total'] ?>">
		<input class="form_enter"  value=" <?= _("PAY");?> " type="submit">
	</form>
	<?php
	}
	if ($info_invoice['payment_status']== 0){
		echo "Send!<br>\n";
	}
	?>
	<a href="<?= $_SERVER['PHP_SELF'] .'?id='.$id.'&printable=1' ?>"><?= _("Printable version") ?></a>
	<br>
	<?php
} ?>

<?php
if ($show_history){
	$QUERY = str_dbparams($DBHandle,'SELECT to_char(invoicesent_date, %2) AS date, invoicestatus FROM cc_invoice_history' .
		' WHERE invoiceid = %#1 ORDER BY invoicesent_date DESC;', array($id,$date_format));
	$res = $DBHandle->Execute($QUERY);

	if (!$res){
		if ($FG_DEBUG >0){
			echo "Query failed: " . htmlspecialchars($QUERY). "<br>\n";
			echo $DBHandle->ErrorMsg();
			echo "<br><br>\n";
		}
	}else if($res->RecordCount()){
		?><div class="invoice_heading"><?= _("History") ?> </div>
		<table class="invoice_list">
<?php
		while ($row = $res->fetchRow()){
		?><tr><td><?= _("At:")?> <?= $row['date'] ?></td>
		<td><?= $payment_sl[$row['invoicestatus']][0] ?> </td></tr>
<?php
		}
		?></table>
<?php
	}
}
?>
<?php if ($show_calls){
	$QUERY = str_dbparams($DBHandle,"SELECT fmt_mins(SUM(sessiontime) ::INTEGER) AS ttime, ".
		" format_currency(SUM(sessionbill) * %2, %4, %3) AS bill ".
		"FROM cc_call WHERE invoice_id = %#1;", 
		array($id,(1.0 - $info_invoice['commission']), $currency, strtoupper(BASE_CURRENCY)));
	$res = $DBHandle->Execute($QUERY);
	if ($res)
		$sum_row = $res->fetchRow();

	if ($show_calls_cumul)
		$QUERY = str_dbparams($DBHandle,"SELECT to_char(date_trunc('day',starttime), %5) AS stime, ".
		"destination AS dest, ".
		" fmt_mins(SUM(sessiontime) :: INTEGER), format_currency(SUM(sessionbill) * %2, %4, %3) AS bill ".
		"FROM cc_call WHERE invoice_id = %#1 AND sessionbill > 0.0 GROUP BY date_trunc('day',starttime), destination ".
		" ORDER BY date_trunc('day',starttime);", 
		array($id,(1.0 - $info_invoice['commission']), $currency, strtoupper(BASE_CURRENCY),$date_only_format));
	else 
	$QUERY = str_dbparams($DBHandle,"SELECT to_char(starttime, %5) AS stime, ".
		"substring(calledstation from '#\"%%#\"___' for '#') || '***' AS dest, ".
		" fmt_mins(sessiontime), format_currency(sessionbill * %2, %4, %3) AS bill ".
		"FROM cc_call WHERE invoice_id = %#1 AND sessionbill > 0.0 ORDER BY starttime;", 
		array($id,(1.0 - $info_invoice['commission']), $currency, strtoupper(BASE_CURRENCY),$date_format));
	$res = $DBHandle->Execute($QUERY);
	?> <div class="invoice_heading"><?= _("Call Details") ?> </div>
<?php
	if (!$res){
		?> <?= _("No calls found!") ?> <?php
		if ($FG_DEBUG >0){
			echo "Query failed: " . htmlspecialchars($QUERY). "<br>\n";
			echo $DBHandle->ErrorMsg();
			echo "<br><br>\n";
		}
	}else {
		$n = 0;
		$ncol = 0;
		if ($num_rows == 0 )
			$num_rows = ($res->RecordCount() + $num_cols +1 ) / $num_cols;
		
		?>
		<table class="invoice_cols">
	<?php
		$row = true ; // for the first one
		while ($row){
		if ( ($ncol++) % $num_cols == 0)
			echo "<tr>";
		echo "<td>";
?>
		<table class='invoice_list'>
		<thead><tr><td><?= _("Date") ?></td> <td><?= _("Destination") ?></td> <td><?= _("Duration") ?></td> <td><?= _("Charge") ?></td>
		</tr>
		</thead>
		<tbody>
<?php
		while ($row = $res->fetchRow()){
			echo "<tr>";
			for ($i = 0; $i<4 ; $i++)
				echo "<td>" .htmlspecialchars($row[$i]) . "</td>";
			echo "</tr>\n";
			if ( (++$n) % $num_rows == 0 )
				break;
		}
		
		if ((! $row) && $sum_row){
			?> <tr><td> </td></tr>
			<tr class="total"> <td colspan=2> <?= _("Total:") ?> </td>
			<td><?= $sum_row['ttime'] ?> </td> <td> <?= $sum_row['bill'] ?> </td></tr>
			<?php
		}
		?>
		</tbody>
		</table>
<?php
		echo "</td>";
		if ( $ncol % $num_cols == 0)
			echo "</tr>";
		}; //while
		
		// if stopped at an odd column, make up.
		if ( $ncol % $num_cols != 0)
			echo "</tr>";
	?>
	</table>
	<?php
	}

} ?>

<?php if ($show_charges){
	$QUERY = str_dbparams($DBHandle,"SELECT count(id) AS cnt, ".
		" format_currency(SUM(amount), %3, %2) AS bill ".
		"FROM cc_charge WHERE invoice_id = %#1;", 
		array($id, $currency, strtoupper(BASE_CURRENCY)));
	$res = $DBHandle->Execute($QUERY);
	if ($res)
		$csum_row = $res->fetchRow();

	$QUERY = str_dbparams($DBHandle,"SELECT to_char(creationdate, %4) AS stime, ".
		"cc_texts.txt AS chargetype, description, ".
		"format_currency(amount, %3, %2) AS bill ".
		"FROM cc_charge, cc_texts WHERE invoice_id = %#1 AND cc_texts.id = cc_charge.chargetype " .
		"AND cc_texts.lang = %5 ORDER BY creationdate;", 
		array($id, $currency, strtoupper(BASE_CURRENCY),$date_format,'C'));
	$res = $DBHandle->Execute($QUERY);
	?> <div class="invoice_heading"> <?= _("List of Charges") ?> </div>
<?php
	if (!$res){
		?> <?= _("No charges found.") ?> <?php
		if ($FG_DEBUG >0){
			echo "Query failed: " . htmlspecialchars($QUERY). "<br>\n";
			echo $DBHandle->ErrorMsg();
			echo "<br><br>\n";
		}
	}else {
		$n = 0;
		$ncol = 0;
		if ($num_rows == 0 )
			$num_rows = ($res->RecordCount() + $num_cols +1 ) / $num_cols;
		
		?>
		<table class="invoice_cols">
	<?php
		$row = true ; // for the first one
		while ($row){
		if ( ($ncol++) % $num_cols == 0)
			echo "<tr>";
		echo "<td>";
?>
		<table class="invoice_list">
		<thead><tr><td><?= _("Date") ?></td> <td><?= _("Type") ?></td> <td><?= _("Description") ?></td> <td><?= _("Charge") ?></td>
		</tr>
		</thead>
		<tbody>
<?php
		while ($row = $res->fetchRow()){
			echo "<tr>";
			for ($i = 0; $i<4 ; $i++)
				echo "<td>" .htmlspecialchars($row[$i]) . "</td>";
			echo "</tr>\n";
			if ( (++$n) % $num_rows == 0 )
				break;
		}
		
		if ((! $row) && $csum_row){
			?> <tr><td> </td></tr>
			<tr class="total"> <td colspan=2> <?= _("Total:") ?> </td>
			<td><?= $csum_row['cnt'] ?> </td> <td> <?= $csum_row['bill'] ?> </td></tr>
			<?php
		}
		?>
		</tbody>
		</table>
<?php
		echo "</td>";
		if ( $ncol % $num_cols == 0)
			echo "</tr>";
		}; //while
		
		// if stopped at an odd column, make up.
		if ( $ncol % $num_cols != 0)
			echo "</tr>";
	?>
	</table>
	<?php
	}

} ?>

<table width="60%" cellpadding="0" cellspacing="0" class="invoice_sum">
	<tr>
	<td width="35%">&nbsp; </td>
	<td width="65%">&nbsp; </td>
	</tr>
	<tr>
	<td><?php echo gettext("Amount");?>&nbsp;: </td>
	<td><?= htmlspecialchars($info_invoice['amount']) ?></td>
	<tr>
	<td><?= str_params(gettext("VAT (%1)%%"), array($info_invoice['vat']),1) ?>&nbsp;: </td>
	<td><?= htmlspecialchars($info_invoice['tax']) ?></td>
	</tr>
	<tr>
	<td><?php echo gettext("Total");?>&nbsp;: </td>
	<td><?php display_dateformat($info_invoice['total']); ?></td>
	</tr>
</table>

</div>

<?
include('PP_footer.php');
?>