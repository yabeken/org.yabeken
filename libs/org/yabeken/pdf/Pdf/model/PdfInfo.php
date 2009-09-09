<?php
module("PdfObj");
import("org.rhaco.lang.DateUtil");
module("PdfObj");
class PdfInfo extends PdfObj {
	protected $Title;
	protected $Author;
	protected $Subject;
	protected $Keywords;
	protected $Creator;
	protected $Producer = "rhaco pdf";
	protected $CreationDate;
	protected $ModDate;
	protected $Trapped;
	
	protected function __init__(){
		$this->CreationDate(DateUtil::format_pdf(time()));
	}
}
?>