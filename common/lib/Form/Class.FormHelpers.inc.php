<?php

	/** Add button */

class AddNewButton {
	protected $form = null;

	function AddNewButton(&$form){
		$this->form = &$form;
	}

	function Render(){
		$action = null;
		$item = _("item");
		if ($this->form){
			$action = $this->form->getAction();
			$item = $this->form->model_name_s;
		}
		else
			$action = getpost_single('action');
		if ($action == 'list'){ ?>
		<a href="<?= $_SERVER['PHP_SELF']?>?action=ask-add"><?= 
			str_params(_("Add a new %1"),array($item),1) ?></a>
		<?php
		}	
	}
	
};