<?php
class A2BAlarm {
};

class TestAlarm extends A2BAlarm {
	function ProcessAlarm($dbrow){
		echo "Process " .$dbrow['name']." !\n";
	}
};

// Register this alarm
$alarm_classes['test']= new TestAlarm();

?>