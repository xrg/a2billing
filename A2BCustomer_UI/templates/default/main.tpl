<HTML>
<HEAD>
	<link rel="shortcut icon" href="templates/default/images/favicon.ico">
	<link rel="icon" href="templates/default/images/animated_favicon1.gif" type="image/gif">
	
	<title>..:: {$CCMAINTITLE} ::..</title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		{if ($CSS_NAME!="" && $CSS_NAME!="default")}
			   <link href="templates/default/css/{$CSS_NAME}.css" rel="stylesheet" type="text/css">
		{else}
			   <link href="templates/default/css/main.css" rel="stylesheet" type="text/css">
			   <link href="templates/default/css/menu.css" rel="stylesheet" type="text/css">
			   <link href="templates/default/css/style-def.css" rel="stylesheet" type="text/css">
		{/if}
			   
			
</HEAD>

<BODY leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<p class="version" align="right">{$WEBUI_VERSION} - {$WEBUI_DATE}</p>
<br>

<DIV border=0 width="1000">

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

	<table>
	<tr>
	   <td>
			<a href="index2.php?language=espanol" target="_parent"><img src="templates/default/images/flags/es.gif" border="0" title="Spanish" alt="Spanish"></a>
			<a href="index2.php?language=english" target="_parent"><img src="templates/default/images/flags/us.gif" border="0" title="English" alt="English"></a>
			<a href="index2.php?language=french" target="_parent"><img src="templates/default/images/flags/fr.gif" border="0" title="French" alt="French"></a>
			<a href="index2.php?language=romanian" target="_parent"><img src="templates/default/images/flags/ro.gif" border="0" title="Romanian"alt="Romanian"></a>
			<a href="index2.php?language=chinese" target="_parent"><img src="templates/default/images/flags/cn.gif" border="0" title="Chinese" alt="Chinese"></a>
			<a href="index2.php?language=polish" target="_parent"><img src="templates/default/images/flags/pl.gif" border="0" title="Polish" alt="Polish"></a>
			<a href="index2.php?language=italian" target="_parent"><img src="templates/default/images/flags/it.gif" border="0" title="Italian" alt="Italian"></a>
			<a href="index2.php?language=russian" target="_parent"><img src="templates/default/images/flags/ru.gif" border="0" title="russian" alt="russian"></a>
			<a href="index2.php?language=turkish" target="_parent"><img src="templates/default/images/flags/tr.gif" border="0" title="Turkish" alt="Turkish"></a>
			<a href="index2.php?language=portuguese" target="_parent"><img src="templates/default/images/flags/pt.gif" border="0" title="Portuguese" alt="Portuguese"></a>
			<a href="index2.php?language=urdu" target="_parent"><img src="templates/default/images/flags/pk.gif" border="0" title="Urdu" alt="Urdu"></a>
	   </td>
	</tr>
	<tr>
		<td>
			<form action="{$PAGE_SELF}" method="post">
				<select name="cssname" style="border: 2px outset rgb(204, 51, 0);">
					<option value="default" {checkseleted}>Default</option>
					<option value="template1" {checkseleted file="template1"}>Template 1</option>
				</select>
				<input type="submit" value="Change" class="form_enter" style="border: 2px outset rgb(204, 51, 0);">
			</form>
		</td>
	</tr>
	</table>


</div>
<div class="divright">
