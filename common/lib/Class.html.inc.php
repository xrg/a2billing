<?php
 /* Trivial classes for HTML pages */

/** String element for HTML body.
    Just output whatever the \b $content has !*/
class StringElem extends ElemBase {
	public $content;
	
	function StringElem($txt = ''){
		$this->content = $txt;
	}
	
	public function Render(){
		echo $this->content;
	}
};

class DbgElem extends StringElem{
	public $content = '';
	
	public function Render(){
	?>
	<div class="debug" style="border:2px solid black">
		<?= nl2br(htmlspecialchars($this->content)) ?>
	</div>
	<?php
	}
};

class ErrorElem extends StringElem{
	public $content = '';
	
	public function Render(){
	?>
	<div class="error" style="border: 2px solid red">
		<?= nl2br(htmlspecialchars($this->content)) ?>
	</div>
	<?php
	}
};

?>