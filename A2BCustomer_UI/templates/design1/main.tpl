<HTML>
<HEAD>
	<link rel="shortcut icon" href="templates/{$SKIN_NAME}/images/favicon.ico">
	<link rel="icon" href="templates/{$SKIN_NAME}/images/animated_favicon1.gif" type="image/gif">
	
	<title>..:: {$CCMAINTITLE} ::..</title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		
			 <link href="templates/{$SKIN_NAME}/css/main.css" rel="stylesheet" type="text/css">
			   <link href="templates/{$SKIN_NAME}/css/menu.css" rel="stylesheet" type="text/css">
			   <link href="templates/{$SKIN_NAME}/css/style-def.css" rel="stylesheet" type="text/css">			
</HEAD>

<BODY leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<p class="version" align="right">{$WEBUI_VERSION} - {$WEBUI_DATE}</p>
<br>

<DIV border=0 width="1000">
{if ($EXPORT == 0)}
<div class="divleft">

	<ul id="nav">
	
       <li><a href="userinfo.php"><strong>{php} echo gettext("ACCOUNT INFO");{/php}</strong></a></li>
	   
       {if $A2Bconfig.webcustomerui.cdr==1 }
       <li><a href=# target=_self></a></li>
       <li><a href="call-history.php"><strong>{php} echo gettext("CALL HISTORY");{/php}</strong></a></li>
       {/if}

	   {if $A2Bconfig.webcustomerui.voucher==1 }
       <li><a href=# target=_self></a></li>
       <li><a href="A2B_entity_voucher.php?form_action=list"><strong>{php} echo gettext("VOUCHER");{/php}</strong></a></li>
       {/if}

	   {if $A2Bconfig.webcustomerui.invoice==1 }
       <li><a href=# target=_self></a></li>
       <li><a href="invoices.php"><strong>{php} echo gettext("INVOICES");{/php}</strong></a></li>
	   <li><a href=# target=_self></a></li>
       <li><a href="A2B_entity_billed_summary.php"><strong>{php} echo gettext("Billed Summary");{/php}</strong></a></li>
	   <li><a href=# target=_self></a></li>
       <li><a href="A2B_entity_billed_details.php"><strong>{php} echo gettext("Billed Details");{/php}</strong></a></li>
	   <li><a href=# target=_self></a></li>
       <li><a href="A2B_entity_unbilled_summary.php"><strong>{php} echo gettext("UnBilled Summary");{/php}</strong></a></li>
	   <li><a href=# target=_self></a></li>
       <li><a href="A2B_entity_unbilled_details.php"><strong>{php} echo gettext("UnBilled Details");{/php}</strong></a></li>
	   <li><a href=# target=_self></a></li>
       <li><a href="A2B_entity_call_details.php"><strong>{php} echo gettext("Call Details");{/php}</strong></a></li>
	   
	   
       {/if}

	   {if $A2Bconfig.webcustomerui.did==1 }
       <li><a href=# target=_self></a></li>
       <li><a href="A2B_entity_did.php?form_action=list"><strong>{php} echo gettext("DID");{/php}</strong></a></li>
       {/if}

	   {if $A2Bconfig.webcustomerui.speeddial==1 }
       <li><a href=# target=_self></a></li>
       <li><a href="A2B_entity_speeddial.php?atmenu=speeddial&stitle=Speed+Dial"><strong>{php} echo gettext("SPEED DIAL");{/php}</strong></a></li>
       {/if}

	   {if $A2Bconfig.webcustomerui.ratecard==1 }
       <li><a href=# target=_self></a></li>
       <li><a href="A2B_entity_ratecard.php?form_action=list"><strong>{php} echo gettext("RATECARD");{/php}</strong></a></li>
       {/if}

	   {if $A2Bconfig.webcustomerui.simulator==1 }
       <li><a href=# target=_self></a></li>
       <li><a href="simulator.php"><strong>{php} echo gettext("SIMULATOR");{/php}</strong></a></li>
       {/if}

	   {if $A2Bconfig.webcustomerui.callback==1 }
       <li><a href=# target=_self></a></li>
       <li><a href="callback.php"><strong>{php} echo gettext("CALLBACK");{/php}</strong></a></li>
       {/if}

	   {if $A2Bconfig.webcustomerui.predictivedialer==1 }
       <li><a href=# target=_self></a></li>
       <li><a href="predictivedialer.php"><strong>{php} echo gettext("PRED-DIALER");{/php}</strong></a></li>
       {/if}

	   {if $A2Bconfig.webcustomerui.webphone==1 }
       <li><a href=# target=_self></a></li>
       <li><a href="webphone.php"><strong>{php} echo gettext("WEB-PHONE");{/php}</strong></a></li>
       {/if}

	   {if $A2Bconfig.webcustomerui.callerid==1 }
       <li><a href=# target=_self></a></li>
       <li><a href="A2B_entity_callerid.php?atmenu=callerid&stitle=CallerID"><strong>{php} echo gettext("ADD CALLER ID");{/php}</strong></a></li>
       {/if}

	   {if $A2Bconfig.webcustomerui.password==1 }
	   <li><a href=# target=_self></a></li>
       <li><a href="A2B_entity_password.php?atmenu=password&form_action=ask-edit&stitle=Password"><strong>{php} echo gettext("PASSWORD");{/php}</strong></a></li>
       {/if}

       <li><a href=# target=_self></a></li>
       <li><a href="logout.php?logout=true" target="_parent"><font color="#DD0000"><strong>{php} echo gettext("LOGOUT");{/php}</strong></font></a></li>

	</ul>

	<table width="150">
	<tr>
	   <td>
			<a href="{$PAGE_SELF}?language=espanol"><img src="templates/{$SKIN_NAME}/images/flags/es.gif" border="0" title="Spanish" alt="Spanish"></a>
			<a href="{$PAGE_SELF}?language=english"><img src="templates/{$SKIN_NAME}/images/flags/us.gif" border="0" title="English" alt="English"></a>
			<a href="{$PAGE_SELF}?language=french"><img src="templates/{$SKIN_NAME}/images/flags/fr.gif" border="0" title="French" alt="French"></a>
			<a href="{$PAGE_SELF}?language=romanian"><img src="templates/{$SKIN_NAME}/images/flags/ro.gif" border="0" title="Romanian"alt="Romanian"></a>
			<a href="{$PAGE_SELF}?language=chinese"><img src="templates/{$SKIN_NAME}/images/flags/cn.gif" border="0" title="Chinese" alt="Chinese"></a>
			<a href="{$PAGE_SELF}?language=polish"><img src="templates/{$SKIN_NAME}/images/flags/pl.gif" border="0" title="Polish" alt="Polish"></a>
			<a href="{$PAGE_SELF}?language=italian"><img src="templates/{$SKIN_NAME}/images/flags/it.gif" border="0" title="Italian" alt="Italian"></a>
			<a href="{$PAGE_SELF}?language=russian"><img src="templates/{$SKIN_NAME}/images/flags/ru.gif" border="0" title="russian" alt="russian"></a>
			<a href="{$PAGE_SELF}?language=turkish"><img src="templates/{$SKIN_NAME}/images/flags/tr.gif" border="0" title="Turkish" alt="Turkish"></a>
			<a href="{$PAGE_SELF}?language=portuguese"><img src="templates/{$SKIN_NAME}/images/flags/pt.gif" border="0" title="Portuguese" alt="Portuguese"></a>
			<a href="{$PAGE_SELF}?language=urdu"><img src="templates/{$SKIN_NAME}/images/flags/pk.gif" border="0" title="Urdu" alt="Urdu"></a>
	   </td>
	</tr>
	<tr>
		<td>
			<form action="{$PAGE_SELF}" method="post">
				<select name="cssname" class="form_input_select" >
					<option value="default" {checkseleted file="default"}>Default</option>
					<option value="design1" {checkseleted file="design1"}>Design 1</option>
				</select>
				<input type="submit" value="Change" class="form_input_button" >
			</form>
		</td>
	</tr>
	</table>


</div>
<div class="divright">
{/if}