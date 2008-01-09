<?php
require("lib/defines.php");
require_once(DIR_COMMON."Class.ElemBase.inc.php");

class InfoContact extends ElemBase {
	function Render(){
	?>
<br/>
<table align=center width="80%" bgcolor="white" cellpadding="5" cellspacing="5" style="border-bottom: medium dotted #AA0000">
	<tr>
		<td width="10%"><img src="./images/asterisklogo.gif"  border="1"></td>
		<td align="right"> <?php  echo TEXTCONTACT; ?> <a href="mailto:<?php  echo EMAILCONTACT; ?>"><?php  echo EMAILCONTACT; ?></a>
			

		</td>
	</tr>
</table>
<?php }
};
$PAGE_ELEMS[] = new InfoContact();
require("PP_page.inc.php");
?>
