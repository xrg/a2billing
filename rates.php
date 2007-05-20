<?php

//echo $_SERVER['HTTP_HOST'].'<br>-------</br>'.$_SERVER['REQUEST_URI'];

// include ("http://localhost/~areski/svn/a2billing/trunk/A2Billing_UI/api/display_ratecard.php?ratecardid=4&key=0951aa29a67836b860b0865bc495225c&page_url=http://localhost/~areski/svn/a2billing/trunk/rates.php&field_to_display=t1.destination,t1.dialprefix,t1.rateinitial&column_name=Destination,Prefix,Rate/Min&field_type=,,money&".$_SERVER['QUERY_STRING']);


include ("http://adminpanel.call-labs.com/api/display_ratecard.php?ratecardid=1&key=0951aa29a67836b860b0865bc495225c&page_url=http://localhost/~areski/svn/a2billing/trunk/rates.php&field_to_display=t1.destination,t1.dialprefix,t1.rateinitial&column_name=Destination,Prefix,Rate/Min&field_type=,,money&".$_SERVER['QUERY_STRING']);





?>
