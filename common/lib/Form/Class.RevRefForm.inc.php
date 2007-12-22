<?php
require_once("Class.FormHandler.inc.php");
require_once("Class.ClauseField.inc.php");
/** Reverse Reference Form
    Sometimes, a RevRef Field is not enough. There, we prefer a whole form, with
    multiple columns, inplace editing etc
*/

class RevRefForm extends FormElemBase{
	public $Form;
	protected $localkey;
	
	function RevRefForm($fldtitle,$fldname,$lkey,$reftable,$refid ){
		$this->localkey = $lkey;
		$this->Form = new FormHandler($reftable,null,$fldtitle);
		$this->Form->prefix=$fldname;
		$this->Form->model[0]= new ClauseField($refid,null);
	}
	
	function InFormRender(&$form){
		if ($form->getAction()!= 'ask-edit')
			return;
		echo "Render:". $this->Form->getpost_single('action');
		// Update the parent form's parameters to this
		foreach ($form->always_follow_params as $key =>$val)
			$this->Form->addAllFollowParam($key,$val,false);
		$mod_pk= $form->getModelPK();
		$this->Form->addAllFollowParam($form->prefix.'action',$form->getAction(),false);
		$this->Form->addAllFollowParam($form->prefix.$mod_pk->fieldname,$form->getpost_single($mod_pk->fieldname),false);
		
		$this->Form->model[0]->ResetValue($form->getpost_single($this->localkey));
		$this->Form->PerformAction();
		$this->Form->Render();
	}
		
	function Render(){
		//no-op
	}
};


?>