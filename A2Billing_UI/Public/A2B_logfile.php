<?php
include ("../lib/defines.php");
include ("../lib/Form/Class.FormHandler.inc.php");
include ("./form_data/FG_var_callback.inc");


getpost_ifset(array('nb', 'view_log', 'filter'));


/***********************************************************************************/


// #### HEADER SECTION
include("PP_header.php");

// #### HELP SECTION
//echo '<br><br>'.$CC_help_callerid_list;
?>
<br>
<a href="#" target="_self" onclick="imgidclick('img1000','div1000','help.png','viewmag.png');"><img style="" id="img1000" src="../Css/kicons/viewmag.png" onmouseover="this.style.cursor='hand';" height="16" width="16"></a>
<div id="div1000" style="">
<div id="kiki"><div class="w1">
	<img src="../Css/kicons/cache.png" class="kikipic">
	<div class="w2">
Browse for log file.<br> Use to locate the log file on a remote Web server.<br>
It can generate combined reports for all logs. This tool can be use for extraction 
and presentation of information from various logfiles.
<br>
</div></div></div>
</div>

<center>
<?php

function array2drop_down($name, $currentvalue, $arr_value){
	echo '<SELECT name="'.$name.'" class="form_enter">';
		if (is_array($arr_value) && count($arr_value)>=1){
			foreach ($arr_value as $ind => $value){
				if ($ind!=$currentvalue){
					echo '<option value="'.$ind.'">'.$value.'</option>';
				}else{
					echo '<option value="'.$ind.'" selected="selected">'.$value.'</option>';
				}
			}
		}
	echo '</SELECT>';
}

/*
$directory = '/var/log/asterisk/agi/';
$d = dir($directory);

while(false!==($entry=$d->read()))
{
	if(is_file($directory.$entry) && $entry!='.' && $entry!='..')
		$arr_log[] = $directory.$entry;
}
$d->close();
sort($arr_log);
*/

$arr_log[0] = '/var/log/asterisk/agi/daemon-callback.log';
$arr_log[1] = '/var/log/asterisk/agi/webcallback-dropmedia.log';

$arr_nb = array(25=>25, 50=>50, 100=>100, 250=>250, 500=>500, 1000=>1000, 2500=>2500);
$nb = $nb?$nb:50;
?>

<form method="get">
Browse log file : <?=array2drop_down('view_log', $view_log, $arr_log)?> - 
<?=array2drop_down('nb', $nb, $arr_nb)?>

Filter : <input class="form_enter" name="filter" size="20" maxlength="30" value="<?php echo $filter; ?>">

<input class="form_enter" style="border: 2px outset rgb(204, 51, 0);" value=" Submit Query " type="submit">
</form>
<hr/>
</center>
<?php
if(isset($_GET['view_log']))
{
	$f = $arr_log[$_GET['view_log']];
	$arr = stat($f);
	echo '<title>'.$f.'</title>';
	echo '<font size="3"><pre>';
	//echo '<a href="view-source:'.WEBROOT.'/log/'.$f.'" target="_new">'.$f.'</a> ['.compute_size($arr['size']).'] last modified: '.date('r', $arr['mtime'])."\n\n";
	echo '<b><a href="view-source:'.WEBROOT.'/log/'.$f.'" target="_new">'.$f.'</a> ['.($arr['size']).'] last modified: '.date('r', $arr['mtime'])."</b>\n\n";

	$arr = file($f);
	$arr = array_reverse($arr);
	$i = 0;
	foreach($arr as $k=>$v)
	{
		$v = trim($v);
		if(!empty($v))
		{
			$i++;			
			if (strlen($filter)>0){
				$pos1 = stripos($v, $filter);
				if ($pos1 !== false) {
					$arr_tmp[] = $v;
				}
			}else{
				$arr_tmp[] = $v;
			}			
			//echo $v."\n";
		}
		if($i>=$nb) break;
	}
	$arr_tmp = array_reverse($arr_tmp);
	foreach($arr_tmp as $v)
		echo $v."\n";
	//debug($arr_tmp);
	/*
	$fp = fopen($arr_log[$_GET['view_log']], 'r');
	while(!feof($fp))
	{
		$line = fgets($fp);
		$line = trim($line);
		if(!empty($line)) echo $line."\n";
		
	}
	fclose($fp);
	*/
	echo '</pre></font>';
}


// #### FOOTER SECTION
include("PP_footer.php");
?>
