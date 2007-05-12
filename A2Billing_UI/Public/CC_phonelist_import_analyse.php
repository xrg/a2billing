<?php
// Common includes
include ("../lib/defines.php");
include ("../lib/module.access.php");


set_time_limit(0);



if (! has_rights (ACX_DID)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}

getpost_ifset(array('campaign', 'task', 'status'));


$campaignval= split('-:-', $campaign);
if (!is_numeric($campaignval[0])){ 
	echo "No campaign defined !"; 
	exit();
}



 $fixfield[0]="Campaign (KEY)";	 
	
	 $field[0]="Phone Number";
	 $field[1]="Name";
	 
	 
$FG_DEBUG = 0;

if (DB_TYPE == "mysql"){
	$sp = "`";
}

// THIS VARIABLE DEFINE THE COLOR OF THE HEAD TABLE
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#FFFFFF";
$FG_TABLE_ALTERNATE_ROW_COLOR[] = "#F2F8FF";


$Temps1 = time();
//echo $Temps1;



//----------------------------------------------
//			Fonction pour l'upload file
//----------------------------------------------

	$registered_types = array(
                                        "application/x-gzip-compressed"         => ".tar.gz, .tgz",
                                        "application/x-zip-compressed"          => ".zip",
                                        "application/x-tar"                     => ".tar",
                                        "text/plain"                            => ".html, .php, .txt, .inc (etc)",
                                        "image/bmp"                             => ".bmp, .ico",
                                        "image/gif"                             => ".gif",
                                        "image/pjpeg"                           => ".jpg, .jpeg",
                                        "image/jpeg"                            => ".jpg, .jpeg",
                                        "image/png"                             => ".png",
                                        "application/x-shockwave-flash"         => ".swf",
                                        "application/msword"                    => ".doc",
                                        "application/vnd.ms-excel"              => ".xls",
                                        "application/octet-stream"              => ".exe, .fla (etc)"
                                        ); # these are only a few examples, you can find many more!

	$allowed_types = array("text/plain");


if ($FG_DEBUG == 1) echo "::::>> ".$the_file;

function validate_upload($the_file, $the_file_type) {

	global $allowed_types;

	$start_error = "\n<b>ERROR:</b>\n<ul>";

        if ($the_file == "none") { 
                $error .= "\n<li>You did not upload anything!</li>";
        } else {
                if (!in_array($the_file_type,$allowed_types)) {
                        $error .= "\n<li>"."file type is not allowed"."\n<ul>";
                        while ($type = current($allowed_types)) {
                                $error .= "\n<li>" . $registered_types[$type] . " (" . $type . ")</li>";
                                next($allowed_types);
                        }
                        $error .= "\n</ul>";
                }
                if ($error) {
                        $error = $start_error . $error . "\n</ul>";
                        return $error;
                } else {
                        return false;
                }
        }
} # END validate_upload

//INUTILE
$my_max_file_size = (int) MY_MAX_FILE_SIZE_IMPORT;


if ($FG_DEBUG == 1) echo "<br> Task :: $task";

if ($task=='upload'){

	//---------------------------------------------------------
	//		 Effacer tout les fichiers du repertoire cache.
	//---------------------------------------------------------

	$the_file_name = $_FILES['the_file']['name'];
	$the_file_type = $_FILES['the_file']['type'];
	$the_file = $_FILES['the_file']['tmp_name'];
	
	
	if ($FG_DEBUG == 1) echo "<br> FILE  ::> ".$the_file_name;
	if ($FG_DEBUG == 1) echo "<br> THE_FILE:$the_file <br>THE_FILE_TYPE:$the_file_type";


	validate_upload($the_file,$the_file_type);				
	
        
	 $fp = fopen($the_file,  "r");  
	 if (!$fp){  /* THE FILE DOESN'T EXIST */ 
		 echo  'THE FILE DOESN T EXIST'; 
		 exit(); 
	 } 
		 
	 $chaine1 = '"\'';
         
 	$nb_imported=0;
	$nb_to_import=0;
	$DBHandle  = DbConnect();
    
	while (!feof($fp)){ 
     		
		 //if ($nb_imported==1000) break;
             $ligneoriginal = fgets($fp,4096);  /* On se déplace d'une ligne */   
			 $ligneoriginal = trim ($ligneoriginal);
			 $ligneoriginal = strtolower($ligneoriginal);
				
			 
			 for ($i = 0; $i < strlen($chaine1); $i++)   
					$ligne = str_replace($chaine1[$i], ' ', $ligneoriginal);
				
			 $ligne = str_replace(',', '.', $ligne);
			 $val= split(';', $ligne);
			 $val[0]=str_replace('"', '', $val[0]); //DH
			 $val[1]=str_replace('"', '', $val[1]); //DH			 
			 $val[0]=str_replace("'", '', $val[0]); //DH
			 $val[1]=str_replace("'", '', $val[1]); //DH
			 
			 
			 if ($status!="ok") break;
			 
			 if (substr($ligne,0,1)!='#' && substr($ligne,0,2)!='"#' && $val[0]!='' && strlen($val[0])>0){
			  
			 
				 $FG_ADITION_SECOND_ADD_TABLE  = 'cc_phonelist';		
				 $FG_ADITION_SECOND_ADD_FIELDS = 'id_cc_campaign, numbertodial, name'; //$fieldtoimport_sql
				 $FG_ADITION_SECOND_ADD_VALUE  = "'".$campaignval[0]."', '".$val[0]."', '".$val[1]."'"; 
				 
				 				 
				 
				 
				 $begin_date = date("Y");	
				 $end_date = date("-m-d H:i:s");					
				 $FG_ADITION_SECOND_ADD_FIELDS .= ', last_attempt';
			 	 $FG_ADITION_SECOND_ADD_VALUE .= ", '".$begin_date.$end_date."'";
				 				 
	
					
				 $TT_QUERY .= "INSERT INTO $sp".$FG_ADITION_SECOND_ADD_TABLE."$sp (".$FG_ADITION_SECOND_ADD_FIELDS.") values (".trim ($FG_ADITION_SECOND_ADD_VALUE).") ";
											
				 
				 $nb_to_import++;
			}
			
			if ($TT_QUERY!='' && strlen($TT_QUERY)>0 && ($nb_to_import==1) ){
				
				$nb_to_import=0;
								
				$result_query =  $DBHandle -> Execute($TT_QUERY);
				// echo "<br>TT_QUERY:".$TT_QUERY;
				// echo "<br>ERROR:".$DBHandle -> Error;
				// echo "<br>RESULT_QUERY:".$result_query;
				
								
				if ($result_query){ $nb_imported = $nb_imported + 1;
				}else{$buffer_error.= $ligneoriginal.'<br/>';}
				$TT_QUERY='';
				
			}
			
			             
		} // END WHILE EOF
		
		
		if ($TT_QUERY!='' && strlen($TT_QUERY)>0 && ($nb_to_import>0) ){
			$result_query = @ $DBHandle -> Execute($TT_QUERY);
			if ($result_query) $nb_imported = $nb_imported + $nb_to_import;
		}		
	
	
}

$Temps2 = time();
$Temps = $Temps2 - $Temps1;
//echo "<br>".$Temps2;
//echo "<br>Script Time :".$Temps."<br>";



	 

?>

<html>
<head>
<title>CallingCard : Importation PhoneList</title>
<link rel="stylesheet" href="Css/Bo_StyleCss.css" type="text/css">
<style type="text/css">
<!--
div.myscroll {
	align: left;
	height: 100px;
	width: 600px;
	overflow: auto;
	border: 1px solid #ddd;
	background-color: #FFFFFF;
	padding: 5px;
}
-->
</style>

<script type="text/javascript">
<!--

function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

function sendtoupload(form){
		
	
	if (form.the_file.value.length < 2){
		alert ('Please, you must first select a file !');
		form.the_file.focus ();
		return (false);
	}
	
    document.forms["myform"].elements["task"].value = "upload";	
	document.forms[0].submit();
}

//-->
</script>

<?php
	include("PP_header.php");
?>

      
	  <?php	  
	  		echo $CC_help_phonelist;	  
	  ?>


          
		<?php  if ($status!="ok"){?> 
		
		<center>As a preview for the import, we have made a quick analyze of the first line of your csv file.<br/>
		Please check out if everything look correct!</center>
		
		<table align=center border="0" cellpadding="2" cellspacing="2" width="300">
			<tbody>
                <tr class="form_head">                  					
                  <td class="tableBody" style="padding: 2px;" align="center" width="50%"> 
                    <strong> <span class="white_link">FIELD </span> </strong>
				  </td>
				  <td class="tableBody" style="padding: 2px;" align="center" width="50%"> 
                    <strong> <span class="white_link">VALUE </span> </strong>
				  </td>
                </tr>
				<tr bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[1]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[1]?>'">  
				 <td class="tableBody" align="left" valign="top"><font color="red"><b><?php echo strtoupper($fixfield[0])?></b></font></td>
				 <td class="tableBody" align="center" valign="top"><font color="red"><b><?php echo $campaignval[1]?> (<?php echo $campaignval[0]?>)</b></font></td>
				</tr>
				<?php  for ($i=0;$i<count($field);$i++){ ?>
               	<tr bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[($i+1)%2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[($i+1)%2]?>'">  
				 <td class="tableBody" align="left" valign="top"><b><?php echo strtoupper($field[$i])?></b></td>
				 <td class="tableBody" align="center" valign="top"><?php echo $val[$i]?></td>
				</tr>
				<?php  } ?>
				<?php  for ($i=0;$i<count($fieldtoimport);$i++){ ?>
               	<tr bgcolor="<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[($i)%2]?>"  onMouseOver="bgColor='#C4FFD7'" onMouseOut="bgColor='<?php echo $FG_TABLE_ALTERNATE_ROW_COLOR[($i)%2]?>'">  
				 <td class="tableBody" align="left" valign="top"><b><?php echo strtoupper($fieldtoimport[$i])?></b></td>
				 <td class="tableBody" align="center" valign="top"><?php echo $val[$i+3]?></td>
				</tr>
				<?php  } ?>
				
			</tbody>
		</table>
						                  		 

			
			
<br></br>
		<table width="95%" border="0" cellspacing="2" align="center" class="records">
			
              <form name="myform" enctype="multipart/form-data" action="CC_phonelist_import_analyse.php" method="post" >
                <INPUT type="hidden" name="campaign" value="<?php echo $campaign?>">				
				<INPUT type="hidden" name="search_sources" value="<?php echo $search_sources?>">
				
                <tr> 
                  <td colspan="2"> 
                    <div align="center"><span class="textcomment"> 
                       Please check if the datas above are correct. <br><b>If Yes</b>, you can continue the import. 
					  Otherwise you must fix your csv file!
                      </span></div>
                  </td>
                </tr>                
                <tr> 
                  <td colspan="2"> 
                    <p align="center">
                      <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $my_max_file_size?>">
                      <input type="hidden" name="task" value="upload">
					  <input type="hidden" name="status" value="ok">
                      <input name="the_file" type="file" size="50" onFocus=this.select() class="saisie1">
                      <input type="button" style="border: 2px outset rgb(204, 51, 0);"   value="Continue to Import the PhoneList" onFocus=this.select() class="form_enter" name="submit1" onClick="sendtoupload(this.form);">
                      <br>
                      &nbsp; </p>
                  </td>
                </tr>
                
                <tr> 
                  <td bgcolor="#E6E6E6" class="souligner" colspan="2"><b> 
                    <?php echo $translate[P34_9]?>
                    </b></td>
                </tr>
               
              </form>
            </table>
			
			<?php }else{ ?>
			
			</br>
			<table width="75%" border="0" cellspacing="2" align="center" class="records">
			
				<TR> 
				  	<TD style="border-bottom: medium dotted #ED2525" align="center">&nbsp;</TD>
				</TR>
                <tr> 
				  <td colspan="2" bgcolor="#EDEDED" style="padding-left: 5px; padding-right: 3px;" align=center>
                    <div align="center"><span class="textcomment"> 
                       
					  <br>
					  The import of the new TariffPlan have been realized with success!<br>
					  <?php echo $nb_imported?> new tariff-rates have been imported into your Database.
                      </span></div>
					  <br><br>
					  
					  
					  <?php  if (!empty($buffer_error)){ ?>
					  <center>
					  	 <b><i>Line that has not been inserted!</i></b>
						 <div class="myscroll">
							  <span style="color: red;">
							  <?php echo $buffer_error?> 
							  </span>  
						 </div>
						</center>
						<br>
					 <?php  } ?>
					 
                  </td>
                </tr>
			</table>
			
			<?php }?>
			<br>
<?php
	include("PP_footer.php");
?>
