<?php
include ("lib/defines.php");
include ("lib/module.access.php");

if (! has_rights (ACX_ACCESS)){
      Header ("HTTP/1.0 401 Unauthorized");
      Header ("Location: PP_error.php?c=accessdenied");
      die();
}
include ("PP_header.php"); 

/* It would be silly to pass this entire file through gettext().
   Instead, we could have the usage files under lib/locale/<LANG>/..
   */
if (getenv('LANG')!= 'en_US')
	$res = include "lib/locales/".getenv('LANG')."/usage.php";
else
	$res = false;
	
if ($res == false){
	// Copy text below and translate!
?>


<h1>Usage Instructions</h1>
Dial the number from the VoIP phones!

<h2>Customers </h2>
Customers can make use of our service. Each customer must be assigned to a Customer ID
(or "card") so that he/she can make a phone call and be billed.<br>
At the callshop, there can be two kinds of customers:<br>
<ul>
<li><b>Regulars (Members):</b><ht> These are persons who are permanently assigned a card. 
They have an account with us.</li>
<li><b>One-timers:</b><ht> These are persons who only come once to use our service. Their
balance will be settled before they leave. Hopefully, they will come again!</li>
</ul>

<h2>Web interface</h2>
<h3>Login/logout</h3>
Of course, you need to login to gain access to the web interface. Keep your password
secure, as it is the one that ensures your money transactions are made by you!
<br>
<b>Note:</b> The login session may expire if you haven't performed any action for some time.
This is intentional and protects the safety of your login.
<b>Hint:</b> The booths page is refreshed automatically and thus causes the login session
never to expire. If you want to leave the screen for long time, you should better leave
it at the booths page.

<h3>Reading the booths *-* </h3>
The credit for the booths is NOT updated during the calls. That is, the web interface will only
show the initial credit (when the call started) and then only subtract the charges after
the call has ended.
<p> Note: You cannot credit the customer and extend an on-going call. The call will always
end at the time it was supposed to.
<?php
} //end if & copy

include ("PP_footer.php"); 
?>