<?php

	/** Add button */

class AddNewButton extends ElemBase {
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
		<div>
		<a href="<?= $_SERVER['PHP_SELF']. 
			$this->form->gen_AllGetParams(
				array($this->form->prefix.'action' =>'ask-add'))
			?>"><?= str_params(_("Add a new %1"),array($item),1) ?></a>
		</div>
		<?php
		}
	}
	
};


	/** Export button */

class AddExportButton extends ElemBase {
	protected $form = null;
	protected $export_csv = null;
	protected $export_xml = null;
	
	function AddExportButton(&$form){
		$this->form = &$form;
	}

	function Render(){
		$action = null;
		$item = _("item");
		if ($this->form){
			$action = $this->form->getAction();
			$items = $this->form->model_name;
		}
		else
			$action = getpost_single('action');
		if ($action == 'list'){ ?>
		<div>
		<a target="_blank" href="<?= $_SERVER['PHP_SELF']. 
			$this->form->gen_AllGetParams(
				array($this->form->prefix.'action' =>'export'))
			?>"><?= str_params(_("Export all %1"),array($items),1) ?></a>
		</div>
		<?php
		}
	}
	
};
?>
