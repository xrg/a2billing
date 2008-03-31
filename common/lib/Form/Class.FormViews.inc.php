<?php
// Trivial views

abstract class FormView {
	function PerformAction(&$form){
	}
	abstract function Render(&$form);
	public function RenderSpecial($rmode,&$form, &$robj){
	}
	public function RenderHeadSpecial($rmode,&$form, &$robj){
	}
};

class IdleView extends FormView {
	public function PerformAction(&$form){
	}
	public function Render(&$form){
	}
};

// 	case 'object-edit':

class ObjEditView extends FormView {
	public function PerformAction(&$form){
		$subfld=$form->getpost_single('sub_action');
		foreach($form->model as $fld)
			if ($fld->fieldname == $subfld){
				$act=$fld->PerformObjEdit($form);
				if ($act)
					$form->setAction($act);
				break;
			}
	}

	public function Render(&$form){
	}
};

class DbgDumpView extends FormView {
	public function Render(&$form){
		echo "<div><pre>\n";
		htmlspecialchars(print_r($form,true));
		echo "\n</pre></div>\n";
	}
};


?>
