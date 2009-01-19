<?php
require_once("Class.FormViews.inc.php");
require_once(DIR_COMMON."Class.DynConf.inc.php");

/** Just output, inline, the messages */
class HtmlOutElem{
	public $level;
	public function HtmlOutElem($level = LOG_ERR ){
		$this->level = $level;
	}
	
	public function out($level, $str){
		if ($level <$this->level)
			echo nl2br(htmlspecialchars($str))."\n";
	}
};


/** Templated Upload View! This imports data using one of the provision
    classes (based on class ImportEngine).

*/
class Ask2ImportView extends FormView {
	public $fmtcomment;	///< The text describing the import format.

	public function Ask2ImportView($fmtcom){
		$this->fmtcomment=$fmtcom;
	}
	
	function RenderHead(){
	?>
<script type="text/javascript">
// <!--

function importValidate(tform){
	if (tform.the_file.value.length < 2){
		alert ('<?= _("Please, you must first select a file !") ?>');
		tform.the_file.focus ();
		return false;
	}
}
//-->
</script>

<?php
	} //end function
	
	function Render(&$form){
		$this->RenderHead();
	
		$impMsg= str_params(_("New %1 have to be imported from a file."),
				array($form->model_name),1);
		
		$formname = $form->prefix .'Imp' ;
		?>
	<div class='impMsg'><?= $impMsg ?>
	</div>
	
	<table cellspacing="2" align="center" class="importForm">
	<form name="<?= $formname ?>" enctype="multipart/form-data" action="<?= $_SERVER['PHP_SELF']?>" method="post" onsubmit="return importValidate(this)">
	<input type="hidden" name="action" value="import">
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
class Import2View extends FormView {
	protected $importEngine;
	protected $impEngClass; ///< name of the class for the import engine
	protected $impEngArgs;	///< Array to be passed to impEngine->Init();
	protected $movedFile;
	protected $fields;
	
	public function Import2View($iec,array $iea = array()){
		$this->impEngClass = $iec;
		$this->impEngArgs= $iea;
	}
	
	public function PerformAction(&$form){
		$dbg_elem = new DbgElem();
		$dbhandle = $form->a2billing->DBHandle();
		if (!isset($this->importEngine)){
			$this->importEngine = new $this->impEngClass();
			$this->importEngine->Init(array_merge(
				$this->impEngArgs,
				array('db'=> $dbhandle)));
		}
			
		
		if ($form->FG_DEBUG>0)
			array_unshift($form->pre_elems,$dbg_elem);
		
		if ($form->FG_DEBUG>2)
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
		
		$allowed_mimetypes=$this->importEngine->getMimeTypes();
		if (!in_array($fil['type'],$allowed_mimetypes)){
			$form->pre_elems[] = new ErrorElem(str_params(_("Cannot accept file of type %1. Allowed types are: %2."),
							array($fil['type'],implode(', ',$allowed_mimetypes)),1));
			$dbg_elem->content .="Cannot accept file type \n";
			$form->setAction('ask-import');
			return;
		}
		
			//Now, check the given fields
	
		
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
	}
	
	public function Render(&$form){
		?><div class='impA-progress' name="<?= $form->prefix?>iprogress">
			<?= _("Importing uploaded data...") ?>
			<span name="<?= $form->prefix?>icount"> - </span>
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
		?><div class="debug">
		<?php
		switch ($form->FG_DEBUG){
		case 0:
		default:
			$dbg_level= LOG_CRIT;
			break;
		case 1:
			$dbg_level= LOG_ERR;
			break;
		case 2:
			$dbg_level= LOG_WARNING;
			break;
		case 3:
			$dbg_level= LOG_INFO;
			break;
		case 4:
			$dbg_level= LOG_DEBUG;
			break;
			
		}
		$this->importEngine->dbg_elem = new HtmlOutElem($dbg_level);
		try {
			$this->importEngine->parseContent($fp);
		} catch (Exception $ex){
		?></div>
		<div class="error">
		<?php
			if ($form->FG_DEBUG)
				echo "\nStopped with Exception: ";
			echo nl2br(htmlspecialchars($ex->getMessage()));
			echo "\n";
		}
		$this->importEngine->dbg_elem=null;
		?>
		</div>
		<?php
		fclose($fp);
		unset($_SESSION[$form->prefix.'importFile']);
		unset($_SESSION[$form->prefix.'importFields']);
		unset($_SESSION[$form->prefix.'importRnd']);
		@unlink($this->movedFile);
	}
	

};

/*
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
*/
?>
