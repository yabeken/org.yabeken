<?php
module("PdfObj");

/**
 * PDF Resource
 * 
 * @author Kentaro YABE
 */
class PdfResources extends PdfObj {
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
?>