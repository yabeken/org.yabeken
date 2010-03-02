<?php
module("PdfObj");
/**
 * Resources
 * @author Kentaro YABE
 */
class PdfResources extends PdfObj{
	static protected $__Font__ = "type=dictionary";
	static protected $__XObject__ = "type=dictionary";
	
	protected $ExtGState;
	protected $ColorSpace;
	protected $Patter;
	protected $Shading;
	protected $XObject;
	protected $ProcSet = array("/PDF","/Text","/ImageB","/ImageC","/ImageI");
	protected $Properties;
	protected $Font;
	
	protected function __init__(){
		$this->Font = new PdfObj();
		$this->XObject = new PdfObj();
	}
}