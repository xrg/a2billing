<?php

$instance_sub_table = new Table("cc_configuration", "configuration_key as cfgKey, configuration_value as cfgValue");
$QUERY = "";
$DBHandle  = DbConnect();
$return = null;
//$configuration_query = tep_db_query('select configuration_key as cfgKey, configuration_value as cfgValue from ' . TABLE_CONFIGURATION);
$configuration_query = $instance_sub_table -> Get_list($DBHandle, $QUERY, 0);

foreach ($configuration_query as $configuration)
{
    define($configuration['cfgKey'], $configuration['cfgValue']);
}

?>
