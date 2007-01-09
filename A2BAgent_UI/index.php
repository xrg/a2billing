<?php include (dirname(__FILE__)."/lib/company_info.php");


if (isset($_GET['language'])){
	//echo "Language:" . $_GET['language'];
	define ("LANGUAGE",$_GET['language']);
	require_once("lib/languageSettings.php");
	SetLocalLanguage();
}

?>
<html>

<head>
<link rel="shortcut icon" href="images/favicon.ico">
<link rel="icon" href="images/animated_favicon1.gif" type="image/gif">

<title>..:: <?php echo CCMAINTITLE; ?> ::..</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="Css/menu.css" rel="stylesheet" type="text/css">


<script LANGUAGE="JavaScript">
<!--
	function test()
	{
		if(document.form.pr_login.value=="" || document.form.pr_password.value=="")
		{
			alert("You must enter an user and a password!");
			return false;
		}
		else
		{
			return true;
		}
	}
-->
</script>

<style TEXT="test/css">
<!-- 
.form_enter {
	font-family: Arial, Helvetica, Sans-Serif;
	font-size: 11px;
	font-weight: bold;
	color: #FF9900;
	border: 1px solid #C1C1C1;
}
-->
</style>
</head>

<body onload="document.form.pr_login.focus()">
<table width="100%" height="75%">
<tr align="center" valign="middle">
<td>
	<form name="form" method="POST" action="index2.php" onsubmit="return test()">
	<input type="hidden" name="done" value="submit_log">

  	<?php if (isset($_GET["error"]) && $_GET["error"]==1) { ?>
		<font face="Arial, Helvetica, Sans-serif" size="2" color="red">
			<b><?= _("AUTHENTICATION REFUSED, please check your user/password!"); ?></b>
		</font>
    <?php }elseif (isset($_GET["error"]) && $_GET["error"]==2){ ?>
        <font face="Arial, Helvetica, Sans-serif" size="2" color="red">
			<b><?= _("INACTIVE ACCOUNT, Please activate your account!"); ?></b>
		</font>
    <?php }elseif (isset($_GET["error"]) && $_GET["error"]==3){ ?>
        <font face="Arial, Helvetica, Sans-serif" size="2" color="red">
			<b><?= _("BLOCKED ACCOUNT, Please contact your administrator!"); ?></b>
		</font>
    <?php } ?>
    <br><br>

	<table style="border: 1px solid #C1C1C1">
	<tr>
		<td class="form_enter" align="center">
			<img src="images/icon_arrow_orange.gif" width="15" height="15">
			<font size="3" color="red" ><b><? _("AUTHENTICATION"); ?></b></font>
		</td>
	</tr>
	<tr>
		<td style="padding: 5px, 5px, 5px, 5px" bgcolor="#EDF3FF">
			<table border="0" cellpadding="0" cellspacing="10">
			<tr align="center">
				<td rowspan="3" style="padding-left: 8px; padding-right: 8px"><img src="images/security.png"></td>
				<td></td>
				<td align="left"><font size="2" face="Arial, Helvetica, Sans-Serif"><b><?= _("User:");?></b></font></td>
				<td><input class="form_enter" type="text" name="pr_login"></td>
			</tr>
			<tr align="center">
				<td></td>
				<td align="left"><font face="Arial, Helvetica, Sans-Serif" size="2"><b><?= _("Password:") ?></b></font></td>
				<td><input class="form_enter" type="password" name="pr_password"></td>
			</tr>
			<tr align="center">
				<td></td>
				<td></td>
				<td><input type="submit" name="submit" value="LOGIN" class="form_enter"></td>
			</tr>

            <tr align="center">
                <td colspan=3><?= _("Forgot your password? Click "); ?><a href="forgotpassword.php"><?= _("here") ?></a>.</td>
            </tr>

			</table>
		</td>
	</tr>
      	</table>
	<a href="index.php?language=espanol" target="_parent"><img src="images/flags/es.gif" border="0" title="Spanish" alt="Spanish"></a>
	<a href="index.php?language=english" target="_parent"><img src="images/flags/us.gif" border="0" title="English" alt="English"></a>
	<a href="index.php?language=french" target="_parent"><img src="images/flags/fr.gif" border="0" title="French" alt="French"></a>
	<a href="index.php?language=romanian" target="_parent"><img src="images/flags/ro.gif" border="0" title="Romanian" alt="Romanian"></a>
	<a href="index.php?language=chinese" target="_parent"><img src="images/flags/cn.gif" border="0" title="Chinese" alt="Chinese"></a>
	<a href="index.php?language=polish" target="_parent"><img src="images/flags/pl.gif" border="0" title="Polish" alt="Polish"></a>
	<a href="index.php?language=italian" target="_parent"><img src="images/flags/it.gif" border="0" title="Italian" alt="Italian"></a>
	<a href="index.php?language=russian" target="_parent"><img src="images/flags/ru.gif" border="0" title="russian" alt="russian"></a>
	<a href="index.php?language=turkish" target="_parent"><img src="images/flags/tr.gif" border="0" title="Turkish" alt="Turkish"></a>
	<a href="index.php?language=portuguese" target="_parent"><img src="images/flags/pt.gif" border="0" title="Portuguese" alt="Portuguese"></a>
	<a href="index.php?language=urdu" target="_parent"><img src="images/flags/pk.gif" border="0" title="Urdu" alt="Urdu"></a>
	<a href="index.php?language=greek" target="_parent"><img src="images/flags/gr.gif" border="0" title="Greek" alt="Greek"></a>
	</form>
</td>
</tr>
</table>
</body>

</html>
