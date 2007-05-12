<?php
include ("./lib/defines.php");
include ("./lib/module.access.php");
include ("./lib/Form/Class.FormHandler.inc.php");
include ("./lib/epayment/includes/general.php");
include ("./lib/epayment/includes/html_output.php");
include("./lib/epayment/includes/PP_header.php");


getpost_ifset(array('errcode'));

?>

<br>
<br>
<table width=80% align=center class="infoBox">
<tr height="15">
    <td colspan=2 class="infoBoxHeading">&nbsp;Message</td>
</tr>
<tr>
    <td width=50%>&nbsp;</td>
    <td width=50%>&nbsp;</td>
</tr>
<tr>
    <td align=center colspan=2>
    Thank you for your purchase at A2Billing&nbsp;
    <?php
      switch($errcode)
      {
          case -2:
            echo gettext("We are sorry your transaction is failed. Please try later or check your provided information.");
          break;
          case -1:
            echo gettext("We are sorry your transaction is denied. Please try later or check your provided information.");
          break;
          case 0:
            echo gettext("We are sorry your transaction is pending.");
          break;
          case 1:
            echo gettext("Your transaction is in progress.");
          break;
          case 2:
            echo gettext("Your transaction was successful.");
          break;
      }
    ?>  &nbsp;
    </td>
</tr>
<tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
</tr>
<tr>
    <td align=center colspan=2><a href="<?php echo tep_href_link("userinfo.php","", 'SSL', false, false);?>">[Home]</a></td>
</tr>

</table>




