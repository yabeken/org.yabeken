<?php
module("PdfObj");
class PdfImage extends PdfObj {
	static protected $__Width__ = "type=integer";
	static protected $__Height__ = "type=integer";
	
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

	protected $offset;

	protected function __new__($source){
		$this->stream = $source;
		$this->offset = 0;
		$this->__parse__();
	}
	protected function __parse__(){
	}
	protected function __compress__($str){
		$this->Filter = "/FlateDecode";
		return $str;
	}
	protected function seek($offset){
		$this->offset += $offset;
	}
	protected function read($length){
		$str = substr($this->stream,$this->offset,$length);
		$this->seek($length);
		return $str;
	}
}
?>