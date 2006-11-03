<?php
include ("./lib/defines.php");
include ("./lib/module.access.php");
include ("./lib/Form/Class.FormHandler.inc.php");



if (! has_rights (ACX_ACCESS)){
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}

if (!$A2B->config["webcustomerui"]['password']) exit();

/***********************************************************************************/


getpost_ifset(array('NewPassword'));


$HD_Form = new FormHandler("cc_card","card");

$HD_Form -> setDBHandler (DbConnect());
//////$instance_sub_table = new Table('cc_callerid');
/////$QUERY = "INSERT INTO cc_callerid (id_cc_card, cid) VALUES ('".$_SESSION["card_id"]."', '".$add_callerid."')";
//////$result = $instance_sub_table -> SQLExec ($HD_Form -> DBHandle, $QUERY, 0);

if($form_action=="ask-update")
{
    $instance_sub_table = new Table('cc_card');
    $QUERY = "UPDATE cc_card SET  uipass= '".$NewPassword."' WHERE ( ID = ".$_SESSION["card_id"]." ) ";
    $result = $instance_sub_table -> SQLExec ($HD_Form -> DBHandle, $QUERY, 0);
}



// #### HEADER SECTION
include("PP_header.php");

// #### HELP SECTION

echo '<br><br>'.$CC_help_password_change."<br>";


?>
<script language="JavaScript">
function CheckPassword()
{
    if(document.frmPass.NewPassword.value =='')
    {
        alert('No value in New Password entered');
        document.frmPass.NewPassword.focus();
        return false;
    }
    if(document.frmPass.CNewPassword.value =='')
    {
        alert('No Value in Confirm New Password entered');
        document.frmPass.CNewPassword.focus();
        return false;
    }
    if(document.frmPass.NewPassword.value.length < 5)
    {
        alert('Password length should be greater than or equal to 5');
        document.frmPass.NewPassword.focus();
        return false;
    }
    if(document.frmPass.CNewPassword.value != document.frmPass.NewPassword.value)
    {
        alert('Value mismatch, New Password should be equal to Confirm New Password');
        document.frmPass.NewPassword.focus();
        return false;
    }

    return true;
}
</script>

<?php
if ($form_action=="ask-update")
{
if (isset($result))
{
?>
<script language="JavaScript">
alert("Your password is updated successfully.");
</script>
<?php
}else
{
?>
<script language="JavaScript">
alert("System is failed to update your password.");
</script>

<?php
} }
?>
<br>
<form method="post" action="<?php  echo $_SERVER["PHP_SELF"]."?form_action=ask-update"?>" name="frmPass">

<table width="40%" cellpading=0 cellspacing=0 border=0 align=center bgcolor="#F6F6F6">
<tr bgcolor="#8888cc">
    <td align=left colspan=2><b><font color="#ffffff">- Change Password -</b></td>
</tr>
<tr>
    <td align=left colspan=2>&nbsp;</td>
</tr>
<tr>
    <td align=right><b>New Password :</b></td>
    <td align=left><input name="NewPassword" type="password" class="form_enter" ></td>
</tr>
<tr>
    <td align=right><b>Confirm Password :</b></td>
    <td align=left><input name="CNewPassword" type="password" class="form_enter" ></td>
</tr>
<tr>
    <td align=left colspan=2>&nbsp;</td>
</tr>
<tr>
    <td align=center colspan=2 ><input type="submit" name="submitPassword" value="&nbsp;Save&nbsp;" class="form_enter" onclick="return CheckPassword();" >&nbsp;&nbsp;<input type="reset" name="resetPassword" value="&nbsp;Reset&nbsp;" class="form_enter" > </td>
</tr>
<tr>
    <td align=left colspan=2>&nbsp;</td>
</tr>


</table>
<script language="JavaScript">

document.frmPass.NewPassword.focus();

</script>
</form>
<br>

<?php

// #### FOOTER SECTION
include("PP_footer.php");

?>
