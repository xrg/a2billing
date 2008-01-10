<?php

	$language_list = array();
	
	/** The great language array:
	@param name 0. i18ned name
	@param abbrev 1. abbreviation, must match asterisk langs and will be stored in db
	@param cname 2. name in english, technical
	@param locale 3. locale
	@param enc 4. encoding
	@param sela 5. selectable for voice etc.
	@param flag 6.flag. If present, selectable for web locale */
	
	function load_Lang($name,$abbrev,$cname,$locale,$enc,$sela,$flag){
		global $language_list;
		$language_list[] = array('name' => $name ,
			'abbrev' => $abbrev,
			'cname' => $cname,
			'locale' => $locale,
			'enc' => $enc,
			'sela' => $sela,
			'flag' => $flag );
	}
	
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


function get_sel_languages() {
	global $language_list;
	$ret = array();
	foreach($language_list as $lang)
		if ($lang['sela'])
		$ret[] = array($lang['name'],$lang['abbrev']);
	return $ret;
}

function SetLocalLanguage($set_lang) {
	$slectedLanguage = "en_US";
	$languageEncoding = "en_US.iso88591";
	$charEncoding = "iso88591";
	global $language_list;
	global $FG_DEBUG;
	$ret='en';
	
	foreach ($language_list as $lang)
		if ($lang['cname'] == $set_lang){
		$slectedLanguage = $lang['locale'];
		$languageEncoding = $lang['locale'] . "." . $lang['enc'];
		$charEncoding = $lang['enc'] ;
		$ret=$lang['abbrev'];
	}
	
	//Code here to set the Encoding of the Lanuages and its Envirnoment Variables

	//@setlocale(LC_TIME,$languageEncoding);
	putenv("LANG=".$slectedLanguage);
	putenv("LANGUAGE=".$slectedLanguage);
	$res= setlocale(LC_ALL,$slectedLanguage, $languageEncoding);
	if (!$res  && ($FG_DEBUG > 1))
		error_log("Could not set locale to $slectedLanguage, $languageEncoding");
//       bindtextdomain($domain,"./lib/locale/");
	bindtextdomain(MESSAGE_DOMAIN, "lib/locale/");
	textdomain(MESSAGE_DOMAIN);
	bind_textdomain_codeset(MESSAGE_DOMAIN,$charEncoding);
	define('CHARSET', $charEncoding);
	if ($FG_DEBUG>4)
		trigger_error("Locale: " . setlocale(LC_MESSAGES,0) ." : " . $slectedLanguage,E_USER_NOTICE);
	
	return $ret;
}

?>
