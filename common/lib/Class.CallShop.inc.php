<?php
require_once("Class.A2Billing.inc.php");
require_once("Misc.inc.php");
require_once("Class.ElemBase.inc.php");
/** Booths page class

    This page is using its own class, so that the code resides in common/lib/ section and
    is thus shared among Admin/Agent UIs.

*/

class CallshopPage extends ElemBase {
	private $rights_checked = false;
	public $agentid = -1;
	public $ask_agent = false;
	public $FG_DEBUG = 0;
	public $a2billing;
	public $ndiv = 4;
	public $refills ='1.0|2.0|5.0';
	
	public function checkRights($rights){
		if (!has_rights($rights)){
			Header ("HTTP/1.0 401 Unauthorized");
			Header ("Location: PP_error.php?c=accessdenied");
			die();
		}
		$this->rights_checked = true;
	}
	
	function init($sA2Billing= null, $stdActions=true){
		if (!$this->rights_checked){
			error_log("Attempt to use Callshop w/o rights!");
			die();
		}
		if ($sA2Billing)
			$this->a2billing= &$sA2Billing;
		else
			$this->a2billing= &A2Billing::instance();
			
		if (isset($GLOBALS['FG_DEBUG']))
			$this->FG_DEBUG = $GLOBALS['FG_DEBUG'];
	}

	function RenderHead() {
		$booth_url = "booths.xml.php";
		$booth2_url = $booth_url . '?';
		if ($this->ask_agent){
			$booth_url = "booths.xml.php?aid=".$this->agentid;
			$booth2_url = $booth_url .'&';
		}
?>
<script src="javascript/callshop.js"></script>
<script type="text/javascript">
var global_reqStates = new Array( "Unknown","<?= _("Open"); ?>", "<?= _("Waiting for response");?>", "<?= _("Receiving")?>");

function select_regular(booth) {
	//alert( "Select regular customer for booth " + booth );
	window.open( "A2B_entity_cards.php?popup_select=freg&booth=" + booth);
}

function startBoothRequest(extra) {
	var url = "<?= $booth_url?>";
	if (extra != undefined)
		url = "<?= $booth2_url ?>" + extra;
		
	startRequest(url,reqStateChanged2);
}
window.onload = function() { startBoothRequest()};
</script>
<?php
	}

	function Render(){
		$dbhandle = $this->a2billing->DBHandle();
		// prepare a few variables
		$refills = explode("|",$this->refills);
	
		$QUERY= "SELECT id FROM cc_booth_v WHERE def_card_id IS NOT NULL AND agentid = ? ORDER BY id;";
			
		$res = $dbhandle -> query($QUERY,$this->agentid);
	
		if (!$res){
			if ($this->FG_DEBUG){
?>	<div class="debug">
		<?= "Error in query: $QUERY" . $dbhandle->ErrorMsg() ?>
	</div>
		<?php } 
	?>
	<br>
	<table class="userError">
		<thead> <tr> <td>
			<?= _("Error Page");?>
		</td> </tr></thead>
		<tbody><tr><td>
			<?= _("Cannot locate booths") ?>
		</td></tr> 
	</table>
	<?php
		} elseif ($res->EOF) {
	?>
	<table class="userError">
		<thead> <tr> <td>
			<?= _("No booths");?>
		</td> </tr></thead>
		<tbody><tr><td>
			<?= _("No booths!<br>Please ask the administrators to create you some.") ?>
		</td></tr> 
	</table>
	<?php
		}else {
	?>
	<!--Lang: <?=setlocale(LC_MESSAGES,0); ?>-->
	<div id="message"><?= _("Welcome!"); ?> </div>
	<br>
	<table class='Booths' border=0 cellPadding=2 cellSpacing=2 width="100%">
	<tbody>
	<?php
			$colwidth= 95/$this->ndiv;
			$i=0;
			for($i=0;($row = $res -> fetchRow());$i++)
			{
				if ( $i % $this->ndiv == 0)
					echo "<tr>\n";
	
				echo "<td width='" . $colwidth . "%' id=\"booth_" . $row['id'] . "\" >";
				?><table class="Booth" cellPadding=2 cellSpacing=2><tbody>
				<tr><td id="name" class="name" colspan=3>Booth X</td></tr>
				<tr><td id="status" class="state0" colspan=3> -- </td></tr>
				<tr><td><?=_("Credit:");?></td><td id="credit"> </td><td id="mins"></td></tr>
				<tr><td id="buttons" class="buttons" colspan=2> 
				<a href="javascript:booth_action(<?=$row['id']?>,'start');" id='button_sta' style='color:green;'><?=_("Start"); ?></a>
				<a href="javascript:booth_action(<?=$row['id']?>,'stop');" id='button_stp' style='color:red;'><?=_("Stop"); ?></a>
				<a href="invoices_cshop.php?booth=<?=$row['id']?>&nobq=1" id='button_pay' style='color:blue;'><?=_("Pay"); ?></a>
				<a href="javascript:booth_action(<?=$row['id']?>,'enable');" id='button_en'><?=_("Enable"); ?></a>
				<a href="javascript:booth_action(<?=$row['id']?>,'disable');" id='button_dis'><?=_("Disable"); ?></a>
				<a href="javascript:booth_action(<?=$row['id']?>,'unload');" id='button_unl'><?=_("Unload"); ?></a>
				<a href="javascript:booth_action(<?=$row['id']?>,'load_def');" id='button_ld'><?=_("One-Time"); ?></a>
				<a href="javascript:booth_action(<?=$row['id']?>,'empty');" id='button_emp'><?=_("Empty"); ?></a>
				<a href="javascript:select_regular(<?=$row['id']?>);" id='button_lr'><?=_("Member"); ?></a>
				<!-- <a href="javascript:select_regular(<?=$row['id']?>);" id='button_ln'><?=_("New"); ?></a> -->
				&nbsp;</td>
				<td class="refill" id="refill" >
				<?= _("Add:"); ?>
				<?php foreach($refills as $rf) { ?>
				&nbsp;<a href="javascript:refill(<?=$row['id'] .', ' . $rf ?>);"><?= ' '. $rf; ?></a>
				<?php } ?>
				</td>
				</tr>
				</tbody></table></td><?php
				if ( $i % $this->ndiv == $ndiv-1 )
					echo "</tr>\n";
			}
			?>
	</tr></tbody></table>
<?php
		}
?>
<br>
<a href='javascript:startBoothRequest()'><?= _("Refresh")?></a>
<br>
<?= _("Response:")?> <span id='response' ></span>
<?php
	}
	
	
	function PerformAction(){
		if (!$this->rights_checked){
			error_log("Attempt to use Callshop w/o rights!");
			die();
		}
		if (!is_int($this->ndiv) || ($this->ndiv < 1) || ($this->ndiv>20))
			$this->ndiv=4;
	}

};

?>
