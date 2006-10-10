{config_load file=leftmenu.conf}
sss
<link rel="stylesheet" type="text/css" href="Css/menu.css" media="all">
<ul id="nav">
	

	<li><a href="userinfo.php"><strong>{php} echo gettext("ACCOUNT INFO");{/php}</strong></a></li>
	
	{if #cdr#} 
	<li><a href=# target=_self></a></li>	
	<li><a href="call-history.php"><strong>{php} echo gettext("CALL HISTORY");{/php}</strong></a></li>
	{/if}

	{if #voucher#}
	<li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_voucher.php?form_action=list"><strong>{php} echo gettext("VOUCHER");{/php}</strong></a></li>
	{/if}
	
	
	{if #invoice#}
	<li><a href=# target=_self></a></li>
	<li><a href="invoices.php"><strong>{php} echo gettext("INVOICES");{/php}</strong></a></li>
	{/if}

	{if #did#}    
	<li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_did.php?form_action=list"><strong>{php} echo gettext("DID");{/php}</strong></a></li>
	{/if}
	
    	{if #speeddial#}	
	<li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_speeddial.php?atmenu=speeddial&stitle=Speed+Dial"><strong>{php} echo gettext("SPEED DIAL");{/php}</strong></a></li>
	{/if}

    	{if #ratecard#}	
	<li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_ratecard.php?form_action=list"><strong>{php} echo gettext("RATECARD");{/php}</strong></a></li>
	{/if}

	{if #simulator#}
	<li><a href=# target=_self></a></li>
	<li><a href="simulator.php"><strong>{php} echo gettext("SIMULATOR");{/php}</strong></a></li>
	{/if}

	{if #callback#}
	<li><a href=# target=_self></a></li>
	<li><a href="callback.php"><strong>{php} echo gettext("CALLBACK");{/php}</strong></a></li>
	{/if}

	{if #predictivedialer#}
	<li><a href=# target=_self></a></li>
	<li><a href="predictivedialer.php"><strong>{php} echo gettext("PRED-DIALER");{/php}</strong></a></li>
	{/if}

	{if #webphone#}
	<li><a href=# target=_self></a></li>
	<li><a href="webphone.php"><strong>{php} echo gettext("WEB-PHONE");{/php}</strong></a></li>
	{/if}

    	{if #callerid#}
	<li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_callerid.php?atmenu=callerid&stitle=CallerID"><strong>{php} echo gettext("ADD CALLER ID");{/php}</strong></a></li>
    	{/if}

	{if #password#}
    <li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_password.php?atmenu=password&form_action=ask-edit&stitle=Password"><strong>{php} echo gettext("PASSWORD");{/php}</strong></a></li>
	{/if}

	<li><a href=# target=_self></a></li>
	<li><a href="logout.php?logout=true" target="_parent"><font color="#DD0000"><strong>{php} echo gettext("LOGOUT");{/php}</strong></font></a></li>

</ul>

<table>
<tr>
	<td>
		<a href="index2.php?language=espanol" target="_parent"><img src="images/flags/es.gif" border="0" title="Spanish" alt="Spanish"></a>
		<a href="index2.php?language=english" target="_parent"><img src="images/flags/us.gif" border="0" title="English" alt="English"></a>
		<a href="index2.php?language=french" target="_parent"><img src="images/flags/fr.gif" border="0" title="French" alt="French"></a>
		<a href="index2.php?language=romanian" target="_parent"><img src="images/flags/ro.gif" border="0" title="Romanian"alt="Romanian"></a>
		<a href="index2.php?language=chinese" target="_parent"><img src="images/flags/cn.gif" border="0" title="Chinese" alt="Chinese"></a>
		<a href="index2.php?language=polish" target="_parent"><img src="images/flags/pl.gif" border="0" title="Polish" alt="Polish"></a>
		<a href="index2.php?language=italian" target="_parent"><img src="images/flags/it.gif" border="0" title="Italian" alt="Italian"></a>
        <a href="index2.php?language=russian" target="_parent"><img src="images/flags/ru.gif" border="0" title="russian" alt="russian"></a>
		<a href="index2.php?language=turkish" target="_parent"><img src="images/flags/tr.gif" border="0" title="Turkish" alt="Turkish"></a>
        <a href="index2.php?language=portuguese" target="_parent"><img src="images/flags/pt.gif" border="0" title="Portuguese" alt="Portuguese"></a>
        <a href="index2.php?language=urdu" target="_parent"><img src="images/flags/pk.gif" border="0" title="Urdu" alt="Urdu"></a>
	</td>
</tr>
<tr>
<td>
<form action="{$PAGE_SELF}" method="post">
<select name="cssname" style="border: 2px outset rgb(204, 51, 0);">
<option value="default" {checkseleted}>Default</option>
<option value="Css_Ale1" {checkseleted file="Css_Ale1"}>Style 1</option>
<option value="Css_Ale2" {checkseleted file="Css_Ale2"}> Style 2</option>
</select>
<input type="submit" value="Change" class="form_enter" style="border: 2px outset rgb(204, 51, 0);">
</form>
</td>
</tr>
</table>
