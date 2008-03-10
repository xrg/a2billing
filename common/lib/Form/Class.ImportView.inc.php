<?php

require_once("Class.FormViews.inc.php");

	/** Upload View! Set this up to enable importing CSV data into entity.

	*/

//TODO: use $form->prefix for fields
//TODO: Locales!!


class AskImportView extends FormView {
	public $common = array();	///< Common fields, for which add-style elements appear
	public $mandatory = array();	///< Mandatory fields to import
	public $optional = array();	///< Optional fields
	public $multiple = array();
	public $distinct = true;
	public $multi_sep = '|';
	public $delimiter = ';';
	public $csvmode = true; ///< Defines some behaviour, like optional fields.
	public $fmtcomment;	///< The text describing the import format.
	public $bodyfield;	///< For mail mode, the body field

	function RenderHead(){
	?>
<script type="text/javascript">
// <!--

function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}

function importValidate(tform){
	if (tform.the_file.value.length < 2){
		alert ('<?= _("Please, you must first select a file !") ?>');
		tform.the_file.focus ();
		return false;
	}
}

//-->
</script>


<script language="JavaScript" type="text/javascript">
<!--
function deselectHeaders(tform)
{
//     tform.unselected_search_sources[0].selected = false;
//     tform.prefs.selected_search_sources[0].selected = false;
}

function resetHidden(tform)
{
    var tmp = '';
    for (i = 0; i < tform.selected_search_sources.length; i++) {
        tmp += tform.selected_search_sources[i].value;
        if (i < tform.selected_search_sources.length - 1)
            tmp += ":";
    }

    tform.search_sources.value = tmp;
}

function addSource(tform)
{
    for (i = 0; i < tform.unselected_search_sources.length; i++) {
        if (tform.unselected_search_sources[i].selected) {
            tform.selected_search_sources[tform.selected_search_sources.length] = new Option(tform.unselected_search_sources[i].text, tform.unselected_search_sources[i].value);
            tform.unselected_search_sources[i] = null;
            i--;
        }
    }

    resetHidden(tform);
}

function removeSource(tform)
{
    for (i = 0; i < tform.selected_search_sources.length; i++) {
        if (tform.selected_search_sources[i].selected) {
            tform.unselected_search_sources[tform.unselected_search_sources.length] = new Option(tform.selected_search_sources[i].text, tform.selected_search_sources[i].value)
            tform.selected_search_sources[i] = null;
            i--;
        }
    }

    resetHidden(tform);
}

function moveSourceUp(tform)
{
    var sel = tform.selected_search_sources.selectedIndex;
	//var sel = tform["selected_search_sources[]"].selectedIndex;
	
    if (sel == -1 || tform.selected_search_sources.length <= 1) return;

    // deselect everything but the first selected item
    tform.selected_search_sources.selectedIndex = sel;

    if (sel == 1) {
        tmp = tform.selected_search_sources[sel];
        tform.selected_search_sources[sel] = null;
        tform.selected_search_sources[tform.selected_search_sources.length] = tmp;
        tform.selected_search_sources.selectedIndex = tform.selected_search_sources.length - 1;
    } else {
        tmp = new Array();

        for (i = 1; i < tform.selected_search_sources.length; i++) {
            tmp[i - 1] = new Option(tform.selected_search_sources[i].text, tform.selected_search_sources[i].value)
        }

        for (i = 0; i < tmp.length; i++) {
            if (i + 1 == sel - 1) {
                tform.selected_search_sources[i + 1] = tmp[i + 1];
            } else if (i + 1 == sel) {
                tform.selected_search_sources[i + 1] = tmp[i - 1];
            } else {
                tform.selected_search_sources[i + 1] = tmp[i];
            }
        }

        tform.selected_search_sources.selectedIndex = sel - 1;
    }

    resetHidden(tform);
}

function moveSourceDown(tform)
{
    var sel = tform.selected_search_sources.selectedIndex;

    if (sel == -1 || tform.selected_search_sources.length <= 1) return;

    // deselect everything but the first selected item
    tform.selected_search_sources.selectedIndex = sel;

    if (sel == tform.selected_search_sources.length - 1) {
        tmp = new Array();

        for (i = 1; i < tform.selected_search_sources.length; i++) {
            tmp[i - 1] = new Option(tform.selected_search_sources[i].text, tform.selected_search_sources[i].value)
        }

        tform.selected_search_sources[1] = tmp[tmp.length - 1];
        for (i = 0; i < tmp.length - 1; i++) {
            tform.selected_search_sources[i + 2] = tmp[i];
        }

        tform.selected_search_sources.selectedIndex = 1;
    } else {
        tmp = new Array();

        for (i = 1; i < tform.selected_search_sources.length; i++) {
            tmp[i - 1] = new Option(tform.selected_search_sources[i].text, tform.selected_search_sources[i].value)
        }

        for (i = 0; i < tmp.length; i++) {
            if (i + 1 == sel) {
                tform.selected_search_sources[i + 1] = tmp[i + 1];
            } else if (i + 1 == sel + 1) {
                tform.selected_search_sources[i + 1] = tmp[i - 1];
            } else {
                tform.selected_search_sources[i + 1] = tmp[i];
            }
        }

        tform.selected_search_sources.selectedIndex = sel + 1;
    }

    resetHidden(tform);
}


// -->
</script>

<?php
	} //end function
	
	function Render(&$form){
		$this->RenderHead();
		
		if (empty($this->fmtcomment) &&($this->csvmode)){
			$this->fmtcomment = str_params( _("Use the example below to format the CSV file. Fields are separated by '%1' , </br>".
			    ". and , are used for decimal format."),
			    array($this->delimiter),1);
		}
		if ($this->csvmode)
			$impMsg= str_params(_("New %1 have to be imported from a CSV file."),
				array($form->model_name),1);
		else
			$impMsg= str_params(_("New %1 have to be imported from a file."),
				array($form->model_name),1);
		
		$fldIndex = array();
		foreach ($form->model as $key => $fld)
			if ($fld->fieldname)
				$fldIndex[$fld->fieldname]=$key;
		
		$formname = $form->prefix .'Imp' ;
		?>
	<div class='impMsg'><?= $impMsg ?>
	</div>
	
	<table cellspacing="2" align="center" class="importForm">
	<form name="<?= $formname ?>" enctype="multipart/form-data" action="<?= $_SERVER['PHP_SELF']?>" method="post" onsubmit="return importValidate(this)">
	<input type="hidden" name="action" value="import-analyze">
	<?php if (count($this->common)){ ?>
		<tr> <td colspan="3" align=center class='title'> <?= _("Common fields");?></td></tr>
	<?php		foreach($this->common as $fldname) {
				$fld = &$form->model[$fldIndex[$fldname]];
	?><tr><td class='field'><?php $fld->RenderAddTitle($form) ?></td>
		<td colspan="2" class='value'><?php $fld->DispAdd($form);?> </td>
	</tr>
	<?php		}
		}
		
		if (count($this->mandatory)){ ?>
		<tr> <td colspan="3" align=center class='title'> <?= _("Mandatory fields");?></td></tr>
	<?php		foreach($this->mandatory as $fldname) {
				if ((!$this->csvmode) && $fldname == $this->bodyfield)
					continue;
				$fld = &$form->model[$fldIndex[$fldname]];
		?><tr><td class='field'><?php $fld->RenderAddTitle($form) ?></td>
		<td class='value'><?php if (!$this->csvmode) echo ucfirst($fldname); ?></td>
		<td class='value'><div class="descr"><?= $fld->editDescr?></div></td>
	</tr>
	<?php		}
		}
		
		if (count($this->optional)){ ?>
		<tr> <td colspan="3" align=center class='title'> <?= _("Optional fields");?></td></tr>
		<?php	if ($this->csvmode) { ?>
		<tr><td colspan="3" align=center>
			<input name="search_sources" value="" type="hidden">
			<table><tbody><tr>
			<td><?php echo gettext("Unselected Fields...");?><br>
				<select name="unselected_search_sources" multiple="multiple" size="<?= count($this->optional)?>" width="50" onchange="deselectHeaders(document.forms['<?= $formname?>'])">
	<?php		foreach($this->optional as $fldname) {
				$fld = &$form->model[$fldIndex[$fldname]]; ?>
					<option value="<?= $fldname ?>"><?= htmlspecialchars($fld->fieldtitle) ?></option><?php
			}
			?>
				</select>
			</td>
			<td> <a onclick="addSource(document.forms['<?= $formname?>']); return false;"><img src="./Images/forward.png" alt="<?= _("Add field")?>" title="<?= _("Add field")?>" border="0"></a>
				<br>
			     <a onclick="removeSource(document.forms['<?= $formname?>']); return false;"><img src="./Images/back.png" alt="<?= _("Remove field")?>" title="<?= _("Remove field")?>" border="0"></a>
			</td>
			<td>
				<?= _("Selected Fields...");?><br>
				<select name="selected_search_sources" multiple="multiple" size="<?= count($this->optional)?>" width="50" onchange="deselectHeaders(document.forms['<?= $formname?>']);">
				</select>
			</td>
			<td>
				<a onclick="moveSourceUp(document.forms['<?= $formname?>']); return false;"><img src="./Images/up_black.png" alt="<?= _("Move up")?>" title="<?= _("Move up")?>" border="0"></a>
				<br>
				<a onclick="moveSourceDown(document.forms['<?= $formname?>']); return false;"><img src="./Images/down_black.png" alt="<?= _("Move down")?>" title="<?= _("Move down")?>" border="0"></a>
        		</td>
			
			</tr>
			</tbody></table>
		</td></tr>
		<?php }else  { //non-csvmode
			foreach($this->optional as $fldname) {
				$fld = &$form->model[$fldIndex[$fldname]];
		?><tr><td class='field'><?php $fld->RenderAddTitle($form) ?></td>
		<td class='value'><?php if (!$this->csvmode) echo ucfirst($fldname); ?></td>
		<td class='value'><div class="descr"><?= $fld->editDescr?></div></td>
	</tr>
	<?php			}
			}
		}
		
		?>

		<tr> <td colspan="3" align=center class='title'> <?= _("Upload File");?></td></tr>
		<tr> <td class="field"><?= _("File") ?></td>
			<td colspan="2" class="value"> 
			<p align="center"><span class="textcomment"> 
			<?= str_params(_("The maximum file size is %1 KB"),
					array($my_max_file_size / 1024),1) ?>
			</span><br>
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $my_max_file_size?>">
			<input type="hidden" name="task" value="upload">
			<input name="the_file" type="file" size="50" onFocus="this.select()" >
			</p>
		</td> </tr>
		<tr><td class="field"><?= _("Submit")?></td>
		    <td class='value' colspan="2" align='right'>
			<input type="submit" value="<?= str_params(_("Import %1"),array($form->model_name),1) ?>" class="btnsubmit" >
		    </td></tr>
	
			<?php if (count($this->examples)) {
		?>
		<tr> <td colspan="3" align="center">&nbsp; </td> </tr>
		<tr> <td colspan="3" align=center class='title'> <?= _("Examples");?></td></tr>
		<tr> <td colspan="3"> 
                    <div align="center"><span class="textcomment">
			<?= $this->fmtcomment ?>
			   </span>
			<br/>
			<?php foreach ($this->examples as $exmpl){ ?>
				<a href="<?= $exmpl[1]?>" target="superframe"><?= $exmpl[0] ?></a> -
			<?php  } ?>
		    </div>
			<iframe name="superframe" src="<?= $this->examples[0][1]?>" bgcolor="white" width=500 height=120 marginWidth=10 marginHeight=10  frameBorder=1  scrolling="auto">
			</iframe>
		</td> </tr>
		<?php  } ?>

		</form>
            </table>
	<?php
	}
};

/** This class retrieves the file and analyzes it */
class ImportAView extends FormView {
	protected $askImport;
	protected $movedFile;
	protected $fields;
	
	public function ImportAView(AskImportView &$ai){
		$this->askImport = &$ai;
	}
	
	public function PerformAction(&$form){
		$dbg_elem = new DbgElem();
		$dbhandle = $form->a2billing->DBHandle();
		
		if ($form->FG_DEBUG>0)
			array_unshift($form->pre_elems,$dbg_elem);
		
		
		$dbg_elem->content .= print_r($_POST,true) . "\n";
		
		$fil = $_FILES[$form->prefix.'the_file'];
		if (!isset($fil)){
			$form->pre_elems[] = new ErrorElem(_("File has not been posted at all!"));
			$form->setAction('ask-import');
			return;
		}
		switch($fil['error']){
		case UPLOAD_ERR_OK:
			$dbg_elem->content .= "File uploaded OK.\n";
			break;
		case UPLOAD_ERR_INI_SIZE:
			$form->pre_elems[] = new ErrorElem(str_params(_("File size exceeds %1 limit of the system!"),array(123),1));
			$dbg_elem->content .="Error!\n";
			$form->setAction('ask-import');
			return;
		case UPLOAD_ERR_FORM_SIZE:
			$form->pre_elems[] = new ErrorElem(str_params(_("File size exceeds %1 limit for this action!"),array(123),1));
			$dbg_elem->content .="Error!\n";
			$form->setAction('ask-import');
			return;
		case UPLOAD_ERR_PARTIAL:
			$form->pre_elems[] = new ErrorElem(_("The uploaded file was only partially uploaded."));
			$dbg_elem->content .="Error!\n";
			$form->setAction('ask-import');
			return;
		case UPLOAD_ERR_NO_FILE:
			$form->pre_elems[] = new ErrorElem(_("No file was uploaded."));
			$dbg_elem->content .="Error!\n";
			$form->setAction('ask-import');
			return;
		case UPLOAD_ERR_NO_TMP_DIR:
			$form->pre_elems[] = new ErrorElem(_("Internal error, could not upload."));
			$dbg_elem->content .="Missing a temporary folder. \n";
			$form->setAction('ask-import');
			return;
		case UPLOAD_ERR_CANT_WRITE:
			$form->pre_elems[] = new ErrorElem(_("Internal error, could not upload."));
			$dbg_elem->content .="Failed to write file to disk.\n";
			$form->setAction('ask-import');
			return;
		default:
			$form->pre_elems[] = new ErrorElem(_("Internal error, could not upload."));
			$dbg_elem->content .="Unknown error:".$fil['error']. "\n";
			$form->setAction('ask-import');
			return;
		}
		
		if (isset($this->allowed_mimetypes) && !in_array($fil['type'],$this->allowed_mimetypes)){
			$form->pre_elems[] = new ErrorElem(str_params(_("Cannot accept file of type %1. Allowed types are: %2."),
							array($fil['type'],implode(', ',$this->allowed_mimetypes)),1));
			$dbg_elem->content .="Cannot accept file type \n";
			$form->setAction('ask-import');
			return;
		}
		
			//Now, check the given fields
		$this->fields = $this->askImport->mandatory;
		if($this->askImport->csvmode){
			$optionals= explode(':',$form->getpost_dirty('search_sources'));
			foreach ($optionals as $opt)
				if (!empty($opt))
				if (!in_array($opt,$this->askImport->optional)){
					$form->pre_elems[] = new ErrorElem(_("Error in submitted form."));
					$dbg_elem->content .= "You tried to pass $opt as a field.\n";
					$form->setAction('ask-import');
					return;
				}else
					$this->fields[] = $opt;
		}
		
		$tmpdir = DynConf::GetCfg('global','upload_tmpdir','/tmp');
		$tmpname = str_replace('/','-',basename($fil['name']));
		$tmpname = tempnam($tmpdir,$tmpname);
		if ($tmpname ===false) {
			$form->pre_elems[] = new ErrorElem(_("Internal error, could not upload."));
			$dbg_elem->content .="Cannot make temp file in $tmpdir \n";
			$form->setAction('ask-import');
			return;
		}
		
		if (move_uploaded_file($fil['tmp_name'],$tmpname)){
			$dbg_elem->content .="moved  \"".$fil['tmp_name'] ."\" to \"". $tmpname ."\"\n";
			$this->movedFile = $tmpname;
		}else {
			$form->pre_elems[] = new ErrorElem(_("Internal error, could not upload."));
			$dbg_elem->content .="Cannot move uploaded file to temporary. \n";
			$form->setAction('ask-import');
			return;
		}
// 		$dbg_elem->content .="copying data from  \"".$fil['tmp_name'] ."\" to \"". $tmpname ."\"\n";
// 		$this->tmpFile = @fopen($fil['tmp_name'],'rb');
// 		if ($this->tmpFile === false){
// 			$form->pre_elems[] = new ErrorElem(_("Internal error, could not upload."));
// 			$dbg_elem->content .="Cannot open uploaded file. \n";
// 			$form->setAction('ask-import');
// 			return;
// 		}
// 		
// 		$this->copyFile = fopen($tmpname,'w');
// 		if ($this->copyFile === false){
// 			$form->pre_elems[] = new ErrorElem(_("Internal error, could not upload."));
// 			$dbg_elem->content .="Cannot open copy file. \n";
// 			$form->setAction('ask-import');
// 			return;
// 		}

	}
	
	public function Render(&$form){
		?><div class='impA-progress' name="<?= $form->prefix?>iprogress">
			<?= _("Analyzing uploaded data...") ?>
		<div>
		
		<?php
		$fp = fopen($this->movedFile,  "r");
		if (!$fp){
			?><div class="error">
				<?= _("Error: Cannot open uploaded file") ?>
			</div>
			<?php
			return;
		}
		
		// Construct an array of the fields to be imported,
		// structure: $fields2[<num>] = array(<name>,<key>,<bool:!aggregate>)
		$fldIndex = array();
		foreach ($form->model as $key => $fld)
			if ($fld->fieldname)
				$fldIndex[$fld->fieldname]=$key;

		$fields2= array();
		foreach($this->fields as $fld)
			if (in_array($fld,$this->askImport->multiple))
				$fields2[] = array($fld,$fldIndex[$fld],false);
			else
				$fields2[] = array($fld,$fldIndex[$fld],true);
		
		unset($fldIndex);

		?> <table cellSpacing="2" align='center' class="<?= $form->list_class?>">
		<?php
		
		$this->RenderListHeads($fields2,$form);
		
		// echo nl2br(print_r($fields2,true));
		$nrows = 0;
		$delimiter = $this->askImport->delimiter;
		$multi_sep = $this->askImport->multi_sep;
		$max_rows = 10; //TODO
		$last_a = null;
		$last_b = null;
		
		?><tbody>
		<?php
		
		while (($larr = fgetcsv($fp,4096,$delimiter))!==false){
			if ($larr===null)
				continue;
			if(count($larr)<count($fields2)){
				if ($form->FG_DEBUG)
					echo "Less fields came!<br>\n";
				if ($form->FG_DEBUG>2)
					echo nl2br(print_r($larr,true). "\n");
				break;
			}
			$arr_a = array();
			$arr_b = array();
				//split the data into 2 arrays.
			foreach($fields2 as $fld){
				$val = $form->model[$fld[1]]->buildValue(current($larr),$form);
				if($fld[2])
					$arr_a[] = $val;
				else
					$arr_b[] = $val;
				next($larr);
			}
				//If non-multiple data matches, we reuse the line
			if ($this->askImport->distinct && ($arr_a == $last_a)){
				foreach($arr_b as $kb => $b)
					$last_b[$kb] = array_merge($last_b[$kb],
						explode($multi_sep, $b));
			}
			else{
				if (!empty($last_a))
					$this->RenderRow($fields2,$last_a,$last_b,$form);
				$last_a = $arr_a;
				$last_b = array();
				foreach($arr_b as $b)
					$last_b[] =explode($multi_sep,$b);
				$nrows++;
			}
			
			
			if ($nrows > $max_rows)
				break;
			// Will actually skip the latest $last_a
		}
		?></tbody> </table>

	<form action=<?= $_SERVER['PHP_SELF']?> method=post name="<?= $form->prefix?>Imp" id="<?= $form->prefix ?>Imp">
	<?php	// The uploaded file should never be revealed to the client. Thus, we keep that
		// in _SESSION.
		$_SESSION[$form->prefix.'importFile'] = $this->movedFile;
		$_SESSION[$form->prefix.'importFields'] = implode(',',$this->fields);
			// Also, protect against multiple uploads in the same session
		$str ='';
		for ($i=0;$i<6;$i++)
			$str .= mt_rand(0,9);
		$_SESSION[$form->prefix.'importRnd'] = $str;
		
		$hidden_arr = array( 'action' => 'import', 'sub_action' => '', 'rnd' => $str);
		foreach ($this->askImport->common as $co)
			$hidden_arr[$co] = $form->getpost_dirty($co);

		if (strlen($form->prefix)>0){
			$arr2= array();
			foreach($hidden_arr as $key => $val)
				$arr2[$form->prefix.$key] = $val;
			$hidden_arr = $arr2;
		}

		$form->gen_PostParams($hidden_arr,true); 
		?>
		<button type=submit>
		<?= str_params(_("Import these %1"),array($form->model_name),1) ?>
		<img src="./Images/icon_arrow_orange.png" ></button>
		</form>
		<?php
		
		fclose($fp);
	}
	
	protected function RenderListHeads($fields2,$form){
		?> <thead><tr> <?php
		foreach ($fields2 as $fld){
			$mfld = &$form->model[$fld[1]];
			echo "<td";
			if ($mfld->listWidth)
				echo ' width="'.$mfld->listWidth .'"';
			echo '>';
			if ($mfld->fieldacr){
				echo '<acronym title="'.htmlspecialchars($mfld->fieldtitle).'" >';
				echo htmlspecialchars($mfld->fieldacr);
				echo '<acronym>';
				
			}else
				echo htmlspecialchars($mfld->fieldtitle);
			echo "</td>";
		}
		
		?></tr></thead> <?php
	}

	protected function RenderRow($fields2,$last_a,$last_b,&$form){
		reset($last_a);
		reset($last_b);
		echo "<tr>";
		foreach($fields2 as $fld){
			echo "<td>";
			$mfld = &$form->model[$fld[1]];
			if ($fld[2]){
				echo htmlspecialchars(current($last_a));
				next($last_a);
			}else{
				echo htmlspecialchars(implode(', ',current($last_b)));
				next($last_b);
			}
			echo "</td>";
		}
		echo "</tr>\n";
		
	}
};

/** Import a file in mail-like format */
class ImportMailAView extends ImportAView {
	public $comment_char = '#';
	public $delim_line = '-------- Mail --------';
	
	public function Render(&$form){
		?><div class='impA-progress' name="<?= $form->prefix?>iprogress">
			<?= _("Analyzing uploaded data...") ?>
		<div>
		
		<?php
		$fp = fopen($this->movedFile,  "r");
		if (!$fp){
			?><div class="error">
				<?= _("Error: Cannot open uploaded file") ?>
			</div>
			<?php
			return;
		}
		
		$all_fields = array();
		foreach ($this->askImport->mandatory as $fld)
			$all_fields[ucfirst($fld)] = $fld;
		
		foreach ($this->askImport->optional as $fld)
			$all_fields[ucfirst($fld)] = $fld;
		
		// echo nl2br(print_r($fields2,true));
		$nrows = 0;
		$commentc = $this->comment_char;
		$comment_len = strlen($this->comment_char);
		//$multi_sep = $this->askImport->multi_sep;
		$max_rows = 10; //TODO
		$bodyfld=$this->askImport->bodyfield;
		if (empty($bodyfld))
			$bodyfld="message";
				
		if (!feof($fp)){	// only one, not "while"
			$temail=array();
			// Sub-loop: get headers
			while (!feof($fp)){
				$line =fgets($fp);
				if (! $line)
					break;
				$line2=trim($line); // but also leave $line intact
				if (substr($line2,0,$comment_len)== $commentc)
					continue;
				if (($pos=strpos($line2,':'))===false)
					break;
				$fld = substr($line2,0,$pos);
				if (!isset($all_fields[$fld])){
					if ($form->FG_DEBUG>1)
						echo "Skipping tag \"$fld\"<br>\n";
					continue;
				}
				$temail[$all_fields[$fld]] = substr($line2,$pos+1);
			}
			
				// skip the first line of the message, if it's whitespace
			if ($line && trim($line)=="")
				$line="";
			
			$temail[$bodyfld] = "";
			// Second loop: message body
			do{
				if ($line == '')
					continue;
				if ($line == $this->delim_line."\n")
					break;
				$temail[$bodyfld] .=$line;
			
			}while (($line = fgets($fp))!==false);
			
			if ($form->FG_DEBUG>2)
				echo "Got one mail!\n";
			
			$this->RenderMail($temail,$form);
		}
		?>
		
	<form action=<?= $_SERVER['PHP_SELF']?> method=post name="<?= $form->prefix?>Imp" id="<?= $form->prefix ?>Imp">
	<?php	// The uploaded file should never be revealed to the client. Thus, we keep that
		// in _SESSION.
		$_SESSION[$form->prefix.'importFile'] = $this->movedFile;
			// Also, protect against multiple uploads in the same session
		$str ='';
		for ($i=0;$i<6;$i++)
			$str .= mt_rand(0,9);
		$_SESSION[$form->prefix.'importRnd'] = $str;
		
		$hidden_arr = array( 'action' => 'import', 'sub_action' => '', 'rnd' => $str);
		foreach ($this->askImport->common as $co)
			$hidden_arr[$co] = $form->getpost_dirty($co);

		if (strlen($form->prefix)>0){
			$arr2= array();
			foreach($hidden_arr as $key => $val)
				$arr2[$form->prefix.$key] = $val;
			$hidden_arr = $arr2;
		}

		$form->gen_PostParams($hidden_arr,true); 
		?>
		<button type=submit>
		<?= str_params(_("Import these %1"),array($form->model_name),1) ?>
		<img src="./Images/icon_arrow_orange.png" ></button>
		</form>
		<?php
		
		fclose($fp);
	}
	
	public function RenderMail($temail,$form){
		$fldIndex = array();
		foreach ($form->model as $key => $fld)
			if ($fld->fieldname)
				$fldIndex[$fld->fieldname]=$key;
		
		$bodyfld=$this->askImport->bodyfield;
		if (empty($bodyfld))
			$bodyfld="message";

		?>
		<table cellspacing="2" align="center" class="importMailForm">
		<tbody>
		<tr><td colspan="3" align=center class='title'>&nbsp;&nbsp;</td><tr>
		<?php 
			foreach($this->askImport->mandatory as $fld){
				if ($fld == $bodyfld)
					continue;
			?>
		<tr><td class="field"><?php
				$form->model[$fldIndex[$fld]]->RenderAddTitle($form);
		?></td><td class="value"><?php
				if(isset($temail[$fld]))
					echo htmlspecialchars($temail[$fld]);
		?></td></tr>
		<?php
			}
		?>
		<tr><td class="field">&nbsp;</td><td class= "value">&nbsp;</td></tr>
		<?php
			foreach($this->askImport->optional as $fld){
				if ($fld == $bodyfld)
					continue;
			?>
		<tr><td class="field"><?php
				$form->model[$fldIndex[$fld]]->RenderAddTitle($form);
		?></td><td class="value"><?php
				if(isset($temail[$fld]))
					$form->model[$fldIndex[$fld]]->DispList($temail,$form);
		?></td></tr>
		<?php
			}
		?>
		<tr><td colspan="3" align=center class='title'><?= $form->model[$fldIndex[$bodyfld]]->fieldtitle ?></td></tr>
		<tr><td class="message" colspan="2">
		<?= nl2br(htmlspecialchars($temail[$bodyfld])) ?>
		</td></tr></tbody>
		</table>
		<?
	}
};
/** This class performs the SQL import */
class ImportView extends FormView {
	protected $askImport;
	protected $movedFile;
	
	public function ImportView(AskImportView &$ai){
		$this->askImport = &$ai;
	}
	
	public function PerformAction(&$form){
		$dbg_elem = new DbgElem();
		
		if ($form->FG_DEBUG>0)
			array_unshift($form->pre_elems,$dbg_elem);
		
		$dbg_elem->content .= print_r($_POST,true) . "\n";
		if ((!isset($_SESSION[$form->prefix.'importRnd'])) || 
			($_SESSION[$form->prefix.'importRnd'] != $form->getpost_single('rnd'))){
			$form->pre_elems[] = new ErrorElem(_("Session Error, cannot import!"));
			$dbg_elem->content .="Random didn't match!\n";
			if ($form->FG_DEBUG>3) $dbg_elem->content .= print_r($_SESSION,true);
			$form->setAction('idle');
			return;
		}
	
		if (!isset($_SESSION[$form->prefix.'importFile']) ||
			!is_readable($_SESSION[$form->prefix.'importFile'])){
			$form->pre_elems[] = new ErrorElem(_("Session Error, file vanished!"));
			$dbg_elem->content .="Cannot read ".$_SESSION[$form->prefix.'importFile'] ." \n";
			$form->setAction('idle');
			return;
		}
		
		$dbg_elem->content .="Ready to open ".$_SESSION[$form->prefix.'importFile'] ." \n";
		$this->movedFile= $_SESSION[$form->prefix.'importFile'];
	
		if (!isset($_SESSION[$form->prefix.'importFields'])) {
			$form->pre_elems[] = new ErrorElem(_("Session Error !"));
			$dbg_elem->content .="Fields vanished from session. \n";
			$form->setAction('idle');
			return;
		}
	
	}
	
	public function Render(&$form){
		$dbhandle = $form->a2billing->DBHandle();
		$fldIndex = array();
		
		
		?><div class='impA-progress' name="<?= $form->prefix?>iprogress">
			<?= _("Importing uploaded data...") ?>
			<span name="<?= $form->prefix?>icount"> </span>
		<div>
		
		<?php
			// Construct, again, the list of fields
		foreach ($form->model as $key => $fld)
			if ($fld->fieldname)
				$fldIndex[$fld->fieldname]=$key;

		$fields2= array();
		$returning = array();
		$fields = explode(',',$_SESSION[$form->prefix.'importFields']);
		foreach($fields as $fld){
			$retk = null;
			$ext = false;
				//does it aggregate over CSV rows?
			$aggr = in_array($fld,$this->askImport->multiple);
			
				// does it belong to the primary INSERT or to
				// some subsequent?
			if ($form->model[$fldIndex[$fld]] instanceof RevRef){
				$ext = true;
				$retk = $form->model[$fldIndex[$fld]]->localkey;
			}

			$fields2[] = array($fld,$fldIndex[$fld],$aggr,$ext, $retk);
		}
		
		unset($fields);
		
		if ($form->FG_DEBUG >4) {
			echo nl2br(htmlspecialchars(print_r($fields2,true)));
			echo "<br>\n";
		}
		
		// Build primary INSERT

		$ins_keys = array();
		//$ins_values = array();
		$ins_qm = array();
		$ins_returning = array();
		
			// Find 
		foreach($this->askImport->common as $fld){
			$ins_keys[] = $fld;
			$ins_qm[] = str_dbparams($dbhandle, "%!1",
				array( $form->model[$fldIndex[$fld]]->
					buildValue( $form->getpost_dirty($fld),$form)));
		}
		
		foreach($fields2 as $fld)
			if (!$fld[3]){
				$ins_keys[]=$fld[0];
				$ins_qm[] = '?';
			}else
				$ins_returning[] = $fld[4];

		$insert_pri = "INSERT INTO ". $form->model_table ."(" .
			implode(', ',$ins_keys) . ") VALUES(". 
			implode(',', $ins_qm).")";
		if (count($ins_returning))
			$insert_pri .= " RETURNING ".implode(', ',$ins_returning);
		$insert_pri .=";";

		
		if ($form->FG_DEBUG >1) {
			echo "Insert query: ". htmlspecialchars($insert_pri) . "<br>\n";
		}

		$fp = fopen($this->movedFile,  "r");
		if (!$fp){
			?><div class="error">
				<?= _("Error: Cannot open uploaded file") ?>
			</div>
			<?php
			return;
		}

		$nrows = 0;
		$nlines = 0;
		$delimiter = $this->askImport->delimiter;
		$multi_sep = $this->askImport->multi_sep;
		$last_a = null;
		$reted = null;
// 		$last_b = null;

			//Everything must be in one transaction, to avoid partially imported
			//data
		$dbhandle->StartTrans();
		
			// The actual import loop!
		while (($larr = fgetcsv($fp,4096,$delimiter))!==false){
			if ($larr===null)
				continue;
			if(count($larr)<count($fields2)){
				if ($form->FG_DEBUG)
					echo "Less fields came!<br>\n";
				if ($form->FG_DEBUG>2)
					echo nl2br(print_r($larr,true). "\n");
				$dbhandle->FailTrans();
				break;
			}
			$nlines++;
			
			$arr_a = array();
			$arr_b = array();
			$arr_ext = array();
				//split the data into 2 arrays.
			foreach($fields2 as $fld){
				$val = $form->model[$fld[1]]->buildValue(current($larr),$form);
				if(!$fld[2])
					$arr_a[] = $val;
				else if (!$fld[3])
					$arr_b[] = $val;
				else
					$arr_c[$fld[0]] =$val;
				next($larr);
			}
				//If non-multiple data matches, we reuse the line
			if ($this->askImport->distinct && ($arr_a == $last_a)){
			}
			else{
				if ($form->FG_DEBUG>2 && ($nrows<100)) {
					echo "Data:" . htmlspecialchars(implode(', ',$arr_a)) . "<br>\n";
				}
				$res = $dbhandle->Execute($insert_pri,$arr_a);
				
				if(!$res){ ?>
				<div class="error">
					<?= _("Database error, cannot import!"); ?>
				</div>
				<?php if ($form->FG_DEBUG) {
					echo $dbhandle->ErrorMsg();
					echo "<br>\n";
					}
					$dbhandle->FailTrans();
					return;
				}elseif(count($ins_returning) && $res->EOF){ ?>
				<div class="error">
					<?= _("Database error, rows not imported!"); ?>
				</div>
				<?php if ($form->FG_DEBUG) {
					echo "No result from insert operation!";
					echo "<br>\n";
					}
					$dbhandle->FailTrans();
					return;
				}else
					$reted = $res->fetchRow();
					
				if ($form->FG_DEBUG && !$res->EOF)
					echo "Second result after INSERT? weird..<br>\n";
				
				if ($form->FG_DEBUG>2 && ($nlines <10) && count($ins_returning))
					echo "Returned: " . print_r($reted,true) . "<br>\n";
				$last_a = $arr_a;
				$nrows++;
			}
			
			if (count($arr_c))
			    foreach ($fields2 as $fld) {
				if(!$fld[3])
					continue;
				$mfld = &$form->model[$fld[1]];
				$cqry = "INSERT INTO $mfld->reftable ($mfld->refid, $mfld->refname) VALUES ";
				$data = explode($multi_sep,$arr_c[$fld[0]]);
				if (! count($data)) continue;
				$cqry_val = array();
				foreach($data as $dat)
					$cqry_val[] = str_dbparams($dbhandle,"(%1, %2)",array($reted[$mfld->localkey],$dat));
				
				$cqry .= implode(",\n",$cqry_val) . ";";
				
				if ($form->FG_DEBUG>2 && ($nlines<100)) {
					echo "Extra: " . htmlspecialchars($cqry) ."<br>\n";
				}
				$res = $dbhandle->Execute($cqry);
				if (!$res){ ?>
				<div class="error">
					<?= _("Database error, secondary rows not imported!"); ?>
				</div>
				<?php if ($form->FG_DEBUG) {
					if ($form->FG_DEBUG>2)
						echo "Query: " . htmlspecialchars($cqry) ."<br>\n";
					echo $dbhandle->ErrorMsg();
					echo "<br>\n";
					}
					$dbhandle->FailTrans();
					return;
				}
			}

			if (($nlines %1000) == 0) {
				// reset the timer and give us another 20sec
				set_time_limit(20);
				if ($form->FG_DEBUG>1)
					echo "Rows found: $nrows<br>\n";
				?>
	<script language="JavaScript" type="text/javascript">
	document.getElementsByName("<?= $form->prefix?>icount")[0].innerHTML = "<?= 
		str_params(_("%1 lines processed: %2 rows"), array($nlines,$nrows),1) ?>";
	window.status = "<?= 
		str_params(_("%1 lines processed: %2 rows"), array($nlines,$nrows),1) ?>";
		</script>
				<?php
				@ob_end_flush(); // Make sure we flush the http data
				
			}
		} //while fgets
		
		if ($dbhandle->CompleteTrans() ){
		?>
	<script language="JavaScript" type="text/javascript">
	document.getElementsByName("<?= $form->prefix?>icount")[0].innerHTML = "<?= 
		str_params(_("%1 lines processed: %2 rows"), array($nlines,$nrows),1) ?>";
	window.status = "<?= 
		str_params(_("%1 lines processed: %2 rows"), array($nlines,$nrows),1) ?>";
		</script>
		<?php
		}
		else{
			echo _("Import of data aborted.");
			echo "<br>\n";
		}
		
		unset($_SESSION[$form->prefix.'importFile']);
		unset($_SESSION[$form->prefix.'importFields']);
		unset($_SESSION[$form->prefix.'importRnd']);
		@unlink($this->movedFile);
	} // Render
};

?>
