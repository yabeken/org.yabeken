<?php
import("org.rhaco.lang.DateUtil");
/**
 * Info
 * @author yabeken
 */
class PdfInfo extends PdfObj {
	static protected $__Title__ = "type=string";
	static protected $__Author__ = "type=string";
	static protected $__Subject__ = "type=string";
	static protected $__Keywords__ = "type=string";
	static protected $__Creator__ = "type=string";
	static protected $__Producer__ = "type=string";
	static protected $__CreationDate__ = "type=string";
	static protected $__ModDate__ = "type=string";
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