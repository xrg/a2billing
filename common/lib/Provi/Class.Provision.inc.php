<?php

/** Base class for provisioning engines
*/
abstract class ProviEngine {

	/** Return the mimetype of the generated content */
	abstract public function getMimeType();
	abstract public function Init(array $args);
	
	abstract public function genContent(&$outstream);
};

?>