<?php
include ("../lib/defines.php");
include ("../lib/module.access.php");

$FG_DEBUG =0;




if (! has_rights (ACX_FILE_MANAGER)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}

	include("PP_header.php");
?>
           <br><br><br>
	  
<?php
	echo $CC_help_musiconhold;
?>

	    
	 
	
		
			<table width="70%" border="0" align="center" cellpadding="0" cellspacing="5" >
	  
				<TR> 
				  <TD style="border-bottom: medium dotted #EEEEEE" colspan=2> &nbsp;</TD>
				</TR>
				<?php  for ($i=1;$i<=NUM_MUSICONHOLD_CLASS;$i++){ ?>
				<tr>
					<td bgcolor="#EEEEEE" height="31" align="center">
						<img src="../Css/kicons/stock-panel-multimedia.png"/>
					</td>
					<td bgcolor="#EEEEEE" height="31" align="center">
						<a href="CC_upload.php?acc=<?php echo $i?>"><?php echo gettext("CUSTOM THE MUSICONHOLD CLASS");?> : <b>ACC_<?php echo $i?></b></a>
					</td>
				</tr>
				<?php  } ?>
				
			   </table>
	   
	   
	   
	  
	  
	 
	  
	  <br>
	 
	 
<?php
	include("PP_footer.php");
?>
