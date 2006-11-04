<?php
include ("./lib/defines.php");
//require (LANGUAGE_DIR.FILENAME_PP_ERROR);

if (!isset($c))	$c="0";

$error["0"] = gettext("ERROR : ACCESS REFUSED");
$error["notvalid"] = gettext("The CODE is not a VALID CODE or has been already used! Please check it and try again.");
$error["syst"] = gettext("Sorry a problem occur on our system, please try later!");
$error["errorpage"] = gettext("There is an error on this page!");
$error["accessdenied"] = gettext("Sorry, you don t have access to this page !");
$error["construction"] = gettext("Sorry, this page is in construction !");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>A2BILLING</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body bgcolor="#F0F0E8" leftmargin="5" topmargin="5" marginwidth="0" marginheight="5">

			<br></br><br></br>
            <table width="460" border="2" align="center" cellpadding="1" cellspacing="2" bordercolor="#eeeeff" bgcolor="#FFFFFF">
			  <tr bgcolor=#4e81c4>

					<td>
						<div align="center"><b><font color="white" size=5><?php echo gettext("Error Page");?></font></b></div>
					</td>
			  </tr>
              <tr>
                <td align="center" colspan=2>
                    <table width="100%" border="0" cellpadding="5" cellspacing="5">
                      <tr>
                        <td align="center"><br/>
						<img src="./Css/kicons/system-config-rootpassword.png">
						<br/>

						 <b><font color=#3050c2 size=4><?php echo $error[$c]?></font></b><br/><br/><br/></td>
                      </tr>
                    </table>
				</td>
              </tr>



            </table>


</body>
</html>
