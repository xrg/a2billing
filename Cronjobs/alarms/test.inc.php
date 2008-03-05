<?php

class TestAlarm extends A2BAlarm {
	function ProcessAlarm(AlmInstance $inst){
		echo "Process " .$inst->name." !\n";
		$inst->Save();
	}
};

// Register this alarm
$alarm_classes['test']= new TestAlarm();

?>