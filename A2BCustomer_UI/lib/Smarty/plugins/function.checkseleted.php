<?php

function smarty_function_checkseleted($params, &$smarty)
{
	
	if($params["file"]=="" && $_REQUEST["sitestyle"]=="")
	{
		return "selected";
	}
	else
	{
		if($_REQUEST["sitestyle"]==$params["file"])
		{
			return "selected";
		}
		else
		{
			return "";
		}
	}
}
?>
