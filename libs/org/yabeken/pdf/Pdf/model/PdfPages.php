<?php
module("PdfObj");
class PdfPages extends PdfObj {
	static protected $__Kids__ = "type=PdfPage[]";
	static protected $__Parent__ = "type=PdfPages";
	
	/* Common */
	protected $Type = "/Pages";
	protected $Parent;
	
	/* Pages */
	protected $Kids;
	protected $Count;
	
	protected function setKids($page,$parent=null){
		$this->Kids[] = $page;
		$this->Count = count($this->Kids);
		if($parent instanceof self) $this->Parent = $parent;
		$page->dictionary("Parent",$this);
	}
}
?>