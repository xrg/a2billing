<?php
include ("lib/defines.php");
include ("lib/module.access.php");

if (! has_rights (ACX_ACCESS)){ 
	   Header ("HTTP/1.0 401 Unauthorized");
	   Header ("Location: PP_error.php?c=accessdenied");	   
	   die();	   
}

	/** These states have to match the SQL logic */
	$booth_states = array();
	$booth_states[0] = array(gettext("N/A"), gettext("Not available, no cards configured."));
	$booth_states[1] = array(gettext("Empty"), gettext("No customer attached."));
	$booth_states[2] = array(gettext("Idle"),gettext("Customer attached, inactive"));
	$booth_states[3] = array(gettext("Ready"),gettext("Waiting for calls"));
	$booth_states[4] = array(gettext("Active"),gettext("Calls made, charged"));
	$booth_states[5] = array(gettext("Disabled"),gettext("Disabled by the agent"));
	
	
	
	$DBHandle  = DbConnect();
	
	$QUERY="SELECT id, name, state, mins, credit FROM cc_booth_v WHERE owner = " . trim($_SESSION["agent_id"]) . " ORDER BY id;";
		
	echo $QUERY . "<br>"; 
	$res = $DBHandle -> query($QUERY);

	if (!$res){
?>	
	<br></br>
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
				<b><font color=#3050c2 size=4><?php echo gettext("Cannot locate booths") ?></font></b><br/><br/><br/></td>
                      </tr>
                    </table>
		</td>
              </tr>
            </table>
	<?php
	} else {

		$ndiv = $A2B->config["agentcustomerui"]['password'];
		if (!is_int($ndiv) || ($ndiv < 1) || ($ndiv>20))
			$ndiv=4;
		echo "ndiv= " . $ndiv . "<br>\n";
		
		$num = $res -> numRows();
		if ($num==0) {
			echo gettext("No booths!<br>Please ask the administrators to create you some.");
		}
		else {
		?>
		<TABLE class='tableMbooths' border=0 cellPadding=2 cellSpacing=2 width="100%">
		<TBODY>
		<?php
			for($i=0;$i<$num;$i++)
			{
				$row = $res -> fetchRow();
				if ( $i % $ndiv == 0)
					echo "<tr>\n";
				$bstate= $row[2];
				if (($bstate<0) || ($bstate>5))
					$bstate=0;

				echo "<td><table class=\"tableBooth" . $bstate . '"';
				echo " width=100 cellPadding=2 cellSpacing=2>";
				echo "<tbody><tr><td>"; 
				echo $row[1] ;
				echo ": " . $booth_states[$bstate][0];
				?></td></tr>
				<tr><td>Credit: 0.0, 0mins</td></tr>
				<tr><td><?php
				switch ($bstate) {
				case 0:
					echo "N/A";
					break;
				case 1:
					echo "[Default][Regular]<br>";
					echo "[Disable]";
					break;
				case 2:
					echo "[Start] [Empty]<br>";
					echo "[Disable]";
					break;
				case 3:
					echo "[Stop]";
					break;
				case 4:
					echo "[Pay]";
					break;
				case 5:
					echo "[Enable]";
					break;
			
				};
				
				?></tr></td>
				</tbody></table></td><?php
				if ( $i % $ndiv == $ndiv-1 )
					echo "</tr>\n";
			}
		?>
		</tr></TBODY></TABLE>
		<?php
		}
	}
	
?>
<br>
<br>
