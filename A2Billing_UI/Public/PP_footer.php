<?php
	if (isset($displayfooter) && $displayfooter==0){ echo '</body></html>'; exit();}

    include_once (dirname(__FILE__)."/../lib/company_info.php");
	if (isset($HD_Form->DBHandle)){ DbDisconnect($HD_Form->DBHandle);}
?>


<br></br>
<div id="kiblue"><div class="w1"><?php  echo COPYRIGHT; ?></div></div>
<br>
</body>
</html>
