<?php
module("PdfObj");
/**
 * Image
 * @author Kentaro YABE
 */
class PdfImage extends PdfObj{
	protected $stream = true;
	
	static protected $__Type__ = "type=name";
	static protected $__Subtype__ = "type=name";
	static protected $__Width__ = "type=integer";
	static protected $__Height__ = "type=integer";
	static protected $__ColorSpace__ = "type=mixed";
	static protected $__BitsPerComponent__ = "type=integer";
	static protected $__ImageMask__ = "type=mixed";
	static protected $__Mask__ = "type=mixed";
	static protected $__Decode__ = "type=mixed";
	static protected $__Interpolate__ = "type=mixed";
	static protected $__Alternates__ = "type=mixed";
	
	protected $Type = "/XObject";
	protected $Subtype = "/Image";
	protected $Width;
	protected $Height;
	protected $ColorSpace;
	protected $BitsPerComponent;
	protected $ImageMask;
	protected $Mask;
	protected $Decode;
	protected $Interpolate;
	protected $Alternates;
}