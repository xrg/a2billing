#!/usr/bin/php -q
<?php
/***************************************************************************
 *            XX.php
 *
 *  13 April 2007
 *  Purpose: To check account of each Users and send an email if the balance is less than the first argument.
 *  Copyright  2007  User : Belaid Arezqui
 *  ADD THIS SCRIPT IN A CRONTAB JOB
 *
 *  The sample above will run the script every day of each month at 6AM
	crontab -e
	0 6 1 * * php /var/lib/asterisk/agi-bin/libs_a2billing/crontjob/XXXXX.php
	
	
	field	 allowed values
	-----	 --------------
	minute	 0-59
	hour		 0-23
	day of month	 1-31
	month	 1-12 (or names, see below)
	day of week	 0-7 (0 or 7 is Sun, or use names)
	
****************************************************************************/

exit();
include ("defines.php");
include ("Class.Table.php");
include ("class.phpmailer.php");


$FG_DEBUG = 1;
$DBHandle  = DbConnect();
$num = 0;	


$QUERY = "SELECT mailtype, fromemail, fromname, subject, messagetext, messagehtml FROM cc_templatemail WHERE mailtype='reminder' ";
$res = $DBHandle -> Execute($QUERY);
if ($res)
	$num = $res -> RecordCount( );

if (!$num) exit();

for($i=0;$i<$num;$i++)
{				
	$listtemplate [] =$res-> fetchRow();				 
}				
list($mailtype, $from, $fromname, $subject, $messagetext, $messagehtml) = $listtemplate [0];
if ($FG_DEBUG == 1) echo "<br><b>mailtype : </b>$mailtype</br><b>from:</b> $from</br><b>fromname :</b> $fromname</br><b>subject</b> : $subject</br><b>ContentTemplate:</b></br><pre>$messagetext</pre></br><hr>";

$QUERY = "SELECT username, lastname, firstname, email, uipass, credit FROM cc_card WHERE activated='TRUE' AND credit<500 ";

$res = $DBHandle -> Execute($QUERY);
$num = 0;
if ($res)
	$num = $res -> RecordCount( );

if (!$num) exit();

for($i=0;$i<$num;$i++)
{				
	$list [] =$res -> fetchRow();				 
}

if ($FG_DEBUG == 1) echo "</br><b>BELOW LIST OF THE CARD WITH LESS THAN 5 DOLLARS:</b><hr></br>";
 $keepmessagetext = $messagetext;		 
 foreach ($list as $recordset){ 
	
	$messagetext = $keepmessagetext;
 
	list($username, $lastname, $firstname, $email, $uipass, $credit) = $recordset;
	if ($FG_DEBUG == 1) echo "<br># $username, $lastname, $firstname, $email, $uipass, $credit #</br>";
	
	$messagetext = str_replace('$name', $lastname, $messagetext);
	$messagetext = str_replace('$card_gen', $username, $messagetext);
	$messagetext = str_replace('$password', $uipass, $messagetext);
	
	$mail = new phpmailer();
	$mail -> From     = $from;
	$mail -> FromName = $fromname;
	//$mail -> IsSendmail();
	$mail -> IsSMTP();
	$mail -> Subject  = $subject;
	$mail -> Body    = $messagetext ; //$HTML;
	//$mail -> AltBody = $messagetext;	// Plain text body (for mail clients that cannot read 	HTML)
	//$mail -> ContentType = "multipart/alternative";
	$mail->AddAddress($recordset[3]);

	$mail->Send();
	echo " ::> MAIL SENT TO ".$recordset[3]."!!!";
}


?>
