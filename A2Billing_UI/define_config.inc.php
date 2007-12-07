<?php
require_once("lib/Class.ConfigTmpl.inc.php");

$conf_groups= array();

$cur_grp = new ConfigGroupTmpl('global','This configuration group handles the global settings for application');
$conf_groups[] = $cur_grp;

$cur_grp = new ConfigGroupTmpl('callback','This configuration group handles callback settings.');
$conf_groups[] = $cur_grp;

$cur_grp = new ConfigGroupTmpl('','');
$conf_groups[] = $cur_grp;

$cur_grp = new ConfigGroupTmpl('','');
$conf_groups[] = $cur_grp;

$cur_grp = new ConfigGroupTmpl('','');
$conf_groups[] = $cur_grp;


?>