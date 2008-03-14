<?php

/** Derivate of ListView, which renders in LaTeX mode !
*/

class ListCsvView extends ListView {

public function RenderSpecial($rmode,&$form, &$robj){
	if ($rmode!='csv') return;
	
	$dbhandle = &$form->a2billing->DBHandle();
		
	$res = $this->performQuery($form,$dbhandle,$rmode);
	if (!$res)
		return;
	if ($res->EOF) /*&& cur_page==0) */ {
		if ($form->list_no_records)
			echo '# '.$list_no_records;
		else echo '# '. str_params(_("No %1 found!"),array($form->model_name_s),1);
	} else {
		// now, DO render the table!
		$renrow=array();
		while ($row = $res->fetchRow()){
			if ($form->FG_DEBUG > 4) {
				echo '# ';
				str_replace("\n"," ", print_r($row,true));
				echo "\n";
			}
			
			$renrow=array();
			foreach ($form->model as $fld)
				if($fld->does_list)
					$renrow[]= $fld->renderSpecial($row,$form,$rmode,$robj);
			echo implode(', ', $renrow); //todo: escape
			echo "\n";
		}

	} // query table

	}
};

?>