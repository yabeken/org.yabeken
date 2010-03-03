<?php
/**
 * Form
 * @author yabeken
 */
class PdfForm extends PdfObj{
	protected $stream = true;
	protected $Type = "/XObject";
	protected $Subtype = "/Form";
	
	static protected $__FormType__ = "type=integer";
	protected $FormType = 1;
}