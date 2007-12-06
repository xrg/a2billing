<?php
 /* Trivial classes for HTML pages */

/** String element for HTML body.
    Just output whatever the \b $content has !*/
class StringElem {
	public $content = '';
	
	public function Render(){
		echo $content;
	}
};

?>