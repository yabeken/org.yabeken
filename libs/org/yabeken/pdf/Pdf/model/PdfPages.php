<?php
/**
 * Pages
 * @author yabeken
 */
class PdfPages extends PdfObj{
	static protected $__Type__ = "type=name";
	static protected $__Parent__ = "type=PdfPages";
	static protected $__Kids__ = "type=PdfPage[]";
	static protected $__Count__ = "type=integer";
	
	protected $Type = "/Pages";
	protected $Parent;
	protected $Kids;
	protected $Count;
	
	protected function __set_Kids__(PdfPage $page,PdfPages $parent=null){
		$this->Kids[] = $page;
		$this->Count = count($this->Kids);
		if(!is_null($parent)) $this->Parent = $parent;
		$page->dictionary("Parent",$this);
		return $page;
	}
}