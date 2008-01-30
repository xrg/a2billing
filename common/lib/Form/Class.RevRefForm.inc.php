<?php
require_once("Class.FormHandler.inc.php");
require_once("Class.ClauseField.inc.php");
/** Reverse Reference Form
    Sometimes, a RevRef Field is not enough. There, we prefer a whole form, with
    multiple columns, inplace editing etc
*/

class RevRefForm extends FormElemBase{
	public $Form;
	public $at_action = 'ask-edit';
	
	function RevRefForm($fldtitle,$fldname,$lkey,$reftable,$refid ){
		$this->Form = new FormHandler($reftable,null,$fldtitle);
		$this->Form->prefix=$fldname;
		$this->Form->model[0]= new ClauseField($refid,null,$lkey);
	}
	
	function InFormRender(&$form){
		if ($form->getAction()!= $this->at_action)
			return;
		// Update the parent form's parameters to this
		foreach ($form->always_follow_params as $key =>$val)
			$this->Form->addAllFollowParam($key,$val,false);
		$pkarr= $form->getPKparamsU(true);
		$pkarr[$form->prefix.'action']=$this->at_action;
		foreach( $pkarr as $key => $val)
			$this->Form->addAllFollowParam($key,$val,false);
		
		foreach($this->Form->model as $fld)
			if($fld instanceof ClauseField)
				$fld->ResetValue($form->getpost_single($fld->parentfield));
		$this->Form->PerformAction();
		$this->Form->Render();
	}
		
	function Render(){
		//no-op
	}
};


?>