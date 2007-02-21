<?php
	include ("defines.php");
	include ("Class.Table.php");
	include ("class.phpmailer.php");


	$FG_DEBUG = 1;
	$DBHandle  = DbConnect();


	$QUERY = "SELECT mailtype, fromemail, fromname, subject, messagetext, messagehtml FROM cc_templatemail WHERE mailtype='reminder' ";
		$res = $DBHandle -> query($QUERY);
		$num = $res -> RecordCount( );
		if (!$num) exit();
		
		for($i=0;$i<$num;$i++)
		{				
			$listtemplate [] =$res-> fetchRow();				 
		}				
		list($mailtype, $from, $fromname, $subject, $messagetext, $messagehtml) = $listtemplate [0];
		if ($FG_DEBUG == 1) echo "<br><b>mailtype : </b>$mailtype</br><b>from:</b> $from</br><b>fromname :</b> $fromname</br><b>subject</b> : $subject</br><b>ContentTemplate:</b></br><pre>$messagetext</pre></br><hr>";
		



	$QUERY = "SELECT username, lastname, firstname, email, uipass, credit FROM cc_card WHERE activated='TRUE' AND credit<500 ";
	
		$res = $DBHandle -> query($QUERY);
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
			//$message = str_replace('$username', $form->getValue('username'), $messagetext);
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
