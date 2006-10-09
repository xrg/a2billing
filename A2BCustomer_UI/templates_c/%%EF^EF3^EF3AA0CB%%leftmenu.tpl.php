<?php /* Smarty version 2.6.13, created on 2006-10-02 15:56:11
         compiled from leftmenu.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'leftmenu.tpl', 1, false),array('function', 'checkseleted', 'leftmenu.tpl', 95, false),)), $this); ?>
<?php echo smarty_function_config_load(array('file' => "leftmenu.conf"), $this);?>


<link rel="stylesheet" type="text/css" href="Css/menu.css" media="all">
<ul id="nav">
	

	<li><a href="userinfo.php"><strong><?php  echo gettext("ACCOUNT INFO"); ?></strong></a></li>
	
	<?php if ($this->_config[0]['vars']['cdr']): ?> 
	<li><a href=# target=_self></a></li>	
	<li><a href="balance.php"><strong><?php  echo gettext("CALL HISTORY"); ?></strong></a></li>
	<?php endif; ?>

	<?php if ($this->_config[0]['vars']['voucher']): ?>
	<li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_voucher.php?form_action=list"><strong><?php  echo gettext("VOUCHER"); ?></strong></a></li>
	<?php endif; ?>
	
	
	<?php if ($this->_config[0]['vars']['invoice']): ?>
	<li><a href=# target=_self></a></li>
	<li><a href="invoices.php"><strong><?php  echo gettext("INVOICES"); ?></strong></a></li>
	<?php endif; ?>

	<?php if ($this->_config[0]['vars']['did']): ?>    
	<li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_did.php?form_action=list"><strong><?php  echo gettext("DID"); ?></strong></a></li>
	<?php endif; ?>
	
    	<?php if ($this->_config[0]['vars']['speeddial']): ?>	
	<li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_speeddial.php?atmenu=speeddial&stitle=Speed+Dial"><strong><?php  echo gettext("SPEED DIAL"); ?></strong></a></li>
	<?php endif; ?>

    	<?php if ($this->_config[0]['vars']['ratecard']): ?>	
	<li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_ratecard.php?form_action=list"><strong><?php  echo gettext("RATECARD"); ?></strong></a></li>
	<?php endif; ?>

	<?php if ($this->_config[0]['vars']['simulator']): ?>
	<li><a href=# target=_self></a></li>
	<li><a href="simulator.php"><strong><?php  echo gettext("SIMULATOR"); ?></strong></a></li>
	<?php endif; ?>

	<?php if ($this->_config[0]['vars']['callback']): ?>
	<li><a href=# target=_self></a></li>
	<li><a href="callback.php"><strong><?php  echo gettext("CALLBACK"); ?></strong></a></li>
	<?php endif; ?>

	<?php if ($this->_config[0]['vars']['predictivedialer']): ?>
	<li><a href=# target=_self></a></li>
	<li><a href="predictivedialer.php"><strong><?php  echo gettext("PRED-DIALER"); ?></strong></a></li>
	<?php endif; ?>

	<?php if ($this->_config[0]['vars']['webphone']): ?>
	<li><a href=# target=_self></a></li>
	<li><a href="webphone.php"><strong><?php  echo gettext("WEB-PHONE"); ?></strong></a></li>
	<?php endif; ?>

    	<?php if ($this->_config[0]['vars']['callerid']): ?>
	<li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_callerid.php?atmenu=callerid&stitle=CallerID"><strong><?php  echo gettext("ADD CALLER ID"); ?></strong></a></li>
    	<?php endif; ?>

	<?php if ($this->_config[0]['vars']['password']): ?>
    <li><a href=# target=_self></a></li>
	<li><a href="A2B_entity_password.php?atmenu=password&form_action=ask-edit&stitle=Password"><strong><?php  echo gettext("PASSWORD"); ?></strong></a></li>
	<?php endif; ?>

	<li><a href=# target=_self></a></li>
	<li><a href="logout.php?logout=true" target="_parent"><font color="#DD0000"><strong><?php  echo gettext("LOGOUT"); ?></strong></font></a></li>

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
<form action="<?php echo $this->_tpl_vars['PAGE_SELF']; ?>
" method="post">
<select name="cssname" style="border: 2px outset rgb(204, 51, 0);">
<option value="default" <?php echo smarty_function_checkseleted(array(), $this);?>
>Default</option>
<option value="Css_Ale1" <?php echo smarty_function_checkseleted(array('file' => 'Css_Ale1'), $this);?>
>Style 1</option>
<option value="Css_Ale2" <?php echo smarty_function_checkseleted(array('file' => 'Css_Ale2'), $this);?>
> Style 2</option>
</select>
<input type="submit" value="Change" class="form_enter" style="border: 2px outset rgb(204, 51, 0);">
</form>
</td>
</tr>
</table>