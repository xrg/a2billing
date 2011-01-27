<?php
require_once("a2blib/LangUtils.inc.php");
load_Lang(_("English"), "en", "english","en_US","iso88591",true,"us.png");
load_Lang( _("Arabic"), "ar","arabic","en_US","iso88591",false,null);
load_Lang( _("Brazilian"),"br","brazilian","pt_BR","iso88591",true,"br.png");
load_Lang( _("Chinese"), "zh", "chinese", "zh_TW","UTF-8",false,"cn.png");
load_Lang( _("French"), "fr", "french","fr_FR","iso88591",true,"fr.png");
load_Lang( _("German"),"de","german","de","iso88591",false,null);
load_Lang( _("Greek"),"gr","greek","el_GR","UTF-8",true,"gr.png");
load_Lang( _("Italian"),"it","italian","it_IT","iso88591",false,"it.png");
load_Lang( _("Polish"),"pl","polish","pl_PL","iso88591",false,"pl.png");
load_Lang( _("Portuguese"),"pt","portuguese","pt_PT","iso88591",false,"pt.png");
load_Lang( _("Romanian"),"ro","romanian","ro_RO","iso88591",true,"ro.png");
load_Lang( _("Russian"),"ru","russian","ru_RU","iso8859-5",false,"ru.png");
load_Lang( _("Spanish"), "es", "espanol","es_ES","iso88591",true,"es.png");
load_Lang( _("Turkish"),"tr","turkish","tr_TR","iso8859-9",false,"tr.png");
load_Lang( _("Urdu"),"pk","urdu","ur_PK","UTF-8",false,"pk.png");

if (isset($_GET['language'])){
if ($FG_DEBUG >0) echo "<!-- lang explicitly set to ".$_GET['language'] ."-->\n";
$_SESSION["language"] = $_GET['language'];
}
elseif(isset($_SESSION["lang_db"])){
	foreach($language_list as $lang)
	if ($lang['abbrev'] == $_SESSION["lang_db"])
		$_SESSION["language"] = $lang['cname'];
	if ($FG_DEBUG >0) trigger_error("Lang Selected by db: ". $_SESSION["language"], E_USER_NOTICE);
}
elseif (!isset($_SESSION["language"]))
{
$_SESSION["language"]=negot_language('english');
}

define ("LANGUAGE",$_SESSION["language"]);
define ("LIBDIR", dirname(__FILE__)."/");

$lang_abbr=SetLocalLanguage($_SESSION["language"]);
if ($FG_DEBUG >5) trigger_error("lang abbr: $lang_abbr",E_USER_NOTICE);

?>