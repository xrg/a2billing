<?php 
include ("../lib/defines.php");
include ("../lib/module.access.php");
include ("../lib/regular_express.inc");
include ("../lib/phpagi/phpagi-asmanager.php");

$FG_DEBUG =0;



getpost_ifset(array('action', 'atmenu','agent' ));


if (! has_rights (ACX_CUSTOMER)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");
	   die();
}

error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));

function gen_userdata($dbh,$filename,$tablename,$qry_clause, &$html_message, $hdr_lines = NULL) {
	global $FG_DEBUG;

	$FG_QUERY_EDITION='name, type, username, accountcode, regexten, callerid, amaflags, secret, md5secret, nat, dtmfmode, qualify, canreinvite, 
disallow, allow, host, callgroup, context, defaultip, fromuser, fromdomain, insecure, language, mailbox, permit, deny, mask, pickupgroup, port, 
restrictcid, rtptimeout, rtpholdtimeout, musiconhold, regseconds, ipaddr, cancallforward';

	$list_names = explode(",",$FG_QUERY_EDITION);

	$instance_table_friend = new Table($tablename,'id, '.$FG_QUERY_EDITION);
	if ($FG_DEBUG>1) $instance_table_friend->debug_st=1;
	$list_friend = $instance_table_friend -> Get_list ($dbh, $qry_clause, null, null, null, null);
	
	if (!is_array($list_friend) || count($list_friend)==0) {
		$html_message .=str_params(_("<p style='color: orange'>No entries for file %1 found</p>"),array($filename),1);
		return true;
	}
	
		
	$fd=fopen($filename,"w");
	if (!$fd){
		$html_message .=str_params( _("<p style='color: red'>Could not open buddy file %1</p>"),
			array($filename),1);
		return false;

	}else{
		if (isset($hdr_lines)){
			$line=$hdr_lines."\n";
			if (fwrite($fd, $line) === FALSE) {
				$html_message .=str_params(_("<p style='color: red'>Impossible to write to file %1</p>"),array($filename),1);
				return false;
			}
		}
		
		foreach ($list_friend as $data){
			$line="\n\n[".$data[1]."]\n";
			if (fwrite($fd, $line) === FALSE) {
				$html_message .=str_params(_("<p style='color: red'>Impossible to write to file %1</p>"),array($filename),1);
				return false;
			}
		
			for ($i=1;$i<count($data)-1;$i++){
				if (strlen($data[$i+1])>0){
					if (trim($list_names[$i]) == 'allow'){
						$codecs = explode(",",$data[$i+1]);
						$line = "";
						foreach ($codecs as $value)
							$line .= trim($list_names[$i]).'='.$value."\n";
					}else    $line = (trim($list_names[$i]).'='.$data[$i+1]."\n");
					if (fwrite($fd, $line) === FALSE){
						$html_message .=str_params(_("<p style='color: red'>Impossible to write to file %1</p>"),
							array($filename),1);
						break;
					}
				}
			}
		}
		fclose($fd);
	}
	$html_message .=str_params(_("<p style='color: green'>File %1 generated.</p>"),
		array($filename),1);

	return true;
}

function gen_all_agents($dbh,$do_sip, $do_iax,&$err_msg){
	global $FG_DEBUG;
	$ita = new Table('cc_agent','id, login,name');
	if ($FG_DEBUG > 1) $ita->debug_st=1;
	$list_agent = $ita -> Get_list ($dbh, 'active = true', null, null, null, null);
	
	if (!is_array($list_agent) || count($list_agent)==0) {
		$err_msg .=str_params(_("<p style='color: red'>No active agents found!<br>%1</p>"),array($dbh->ErrorMsg()),1);
		return false;
	}
	
	// These are put by default on a non-existing directory!
	// This is intentional, since those files contain SIP/IAX passwords.
	// they shouldn't be carelessly left in a world-readable dir.
	if (isset($A2B->config['webui']['buddy_sip_agent']))
		$buddy_sip=$A2B->config['webui']['buddy_sip_agent'];
	else
		$buddy_sip="/tmp/a2billing/additional_sip.%1.conf";
		
	if (isset($A2B->config['webui']['buddy_iax_agent']))
		$buddy_iax=$A2B->config['webui']['buddy_iax_agent'];
	else
		$buddy_iax="/tmp/a2billing/additional_iax.%1.conf";
	$succ=0;
	foreach ($list_agent as $ag){
		$hdr_lines="; Configuration for ". $ag[2] ."\n";
		if ($do_sip){
			$fname=str_params($buddy_sip,$ag);
			$qclause=str_dbparams($dbh,"name IN (SELECT callerid FROM cc_booth WHERE agentid = %1)",
				array($ag[0]));
			if (gen_userdata($dbh,$fname,'cc_sip_buddies',$qclause,$err_msg,$hdr_lines))
				$succ++;
		}
		if ($do_iax){
			$fname=str_params($buddy_iax,$ag);
			$qclause=str_dbparams($dbh,"name IN (SELECT callerid FROM cc_booth WHERE agentid = %1)",
				array($ag[0]));
			if (gen_userdata($dbh,$fname,'cc_iax_buddies',$qclause,$err_msg,$hdr_lines))
				$succ++;
		}
	}
	$co = 0;
	if ($do_sip) $co += count($list_agent);
	if ($do_iax) $co += count($list_agent);
	
	$err_msg .=str_params(_("<p style='color: blue'>Agent config files: %#1 of %#2 files created.</p>"),
		array($succ, $co),1);
	return true;
}

function reload_userdata($host, $uname, $password, $issip, &$err_msg){
	global $FG_DEBUG;
	$as = new AGI_AsteriskManager();
	// && CONNECTING  connect($server=NULL, $username=NULL, $secret=NULL)
	$res = $as->connect($host, $uname, $password);
	
	if (!$res) {
		$err_msg .= str_params( _("<p><font color=red>Cannot connect to asterisk manager @%1<br>Please check manager configuration...</font></p>"),
			array($host),1);
		return false;
	}
	if ( $issip )
		$res = $as->Command('sip reload');
	else
		$res = $as->Command('iax2 reload');
	if($res)
		$err_msg.=str_params(_("<p><font color=green>The %2 file at %1 has been reload</font></p>"),
			array($host,($issip)?'sip':'iax'));
	return $res;
}

$DBHandle  = DbConnect();

switch ($action){
case 'reload':
	if (isset($agent) && ($agent != '')){
		$error_msg = "Don't know how to handle agent reloads yet!";
	}else{
		$iss= ($atmenu == "sipfriend")?true: false;
		reload_userdata(MANAGER_HOST,MANAGER_USERNAME,MANAGER_SECRET,$iss,$error_msg);
	}
	
	break;
case 'gen':

	if (( $atmenu == "sipfriend" ) || ( $atmenu == "both" )){
		$TABLE_BUDDY = 'cc_sip_buddies';
		$buddyfile = BUDDY_SIP_FILE;
		
		if (gen_userdata($DBHandle,$buddyfile,$TABLE_BUDDY,'',$error_msg)){
			$_SESSION["is_sip_changed"]=0;
		}
	}
	if (( $atmenu == "iaxfriend" ) || ( $atmenu == "both" )){
		$TABLE_BUDDY = 'cc_iax_buddies';
		$buddyfile = BUDDY_IAX_FILE;
	
		if (gen_userdata($DBHandle,$buddyfile,$TABLE_BUDDY,'',$error_msg)) {
			$_SESSION["is_iax_changed"]=0;
		}
	}

	if (($_SESSION["is_iax_changed"]==0) &&( $_SESSION["is_iax_changed"]=0)){
		$_SESSION["is_sip_iax_changed"]=0;
	}
	break;
case 'gen-agents':

	$do_sip=false;
	$do_iax=false;
	if (( $atmenu == "sipfriend" ) || ( $atmenu == "both" ))
		$do_sip=true;
	if (( $atmenu == "iaxfriend" ) || ( $atmenu == "both" ))
		$do_iax=true;
		
		
	gen_all_agents($DBHandle,$do_sip,$do_iax,$error_msg);
	break;
	
default:
	$error_msg = _("Unknown action:"). $action;
}
	
?>



<?php
	include("PP_header.php");
?>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

function openURL(theLINK)
{
      // grab index number of the selected option
      selInd = document.theForm.choose_list.selectedIndex;
      // get value of the selected option
      goURL = document.theForm.choose_list.options[selInd].value;
      // redirect browser to the grabbed value (hopefully a URL)
     self.location.href = theLINK + goURL;
}

function openURLFilter(theLINK)
{
      selInd = document.theFormFilter.choose_list.selectedIndex;
	  if(selInd==0){return false;}
      goURL = document.theFormFilter.choose_list.options[selInd].value;
      this.location.href = theLINK + goURL;
}

//-->
</script>

           <br><br><br>
<?php
	echo $CC_help_sipfriend_reload;
?>

	  <table width="<?php echo $FG_HTML_TABLE_WIDTH?>" border="0" align="center" cellpadding="0" cellspacing="0" >
	  
		<TR> 
          <TD style="border-bottom: medium dotted #252525"> &nbsp;</TD>
        </TR>
		<tr><FORM NAME="sipfriend">
            <td height="31" bgcolor="#CCCCCC" style="padding-left: 5px; padding-right: 3px;" align=center>
			<br><br>
			<b>
			<?php 	
				if (strlen($error_msg)>0){
					echo $error_msg;
				}
				
			?>
			
			
			<br><br><br>
			<a href="<?php  echo $PHP_SELF."?atmenu=$atmenu&action=reload";?>"><img src="../Images/icon_refresh.gif" />
				<?php echo gettext("Click here to reload Asterisk Server"); ?>
			</a>
			
			<br><br><br>
			
			</b>
			  </td></FORM>
          </tr>
	   </table>
	  <br><br>


	  <br>


<?php
	include("PP_footer.php");
?>
