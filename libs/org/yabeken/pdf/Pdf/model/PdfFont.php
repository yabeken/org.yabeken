<?php
module("PdfObj");
class PdfFont extends PdfObj{
	protected $Type = "/Font";
	protected $Subtype;
	/* Type1*/
	protected $Name;
	protected $BaseFont;
	protected $FirstChar;
	protected $LastChar;
	protected $Widths;
	protected $FontDescriptor;
	protected $Encoding;
	protected $ToUnicode;
	/* Type3 */
	protected $FontBBox;
	protected $FontMatrix;
	protected $CharProcs;
	protected $Resources;
	/* CIDFont */
	protected $CIDSystemInfo;
	protected $DW;
	protected $W;
	protected $DW2;
	protected $W2;
	protected $CIDToGIDMap;
	/* Type0 */
	protected $DescendantFonts;

	final public function str_width($str){
		$len = mb_strlen($str,mb_internal_encoding());
		if($len == 0) return 0;
		if($len > 1){
			$r = 0;
			for($i=0;$i<$len;$i++){
				$r += $this->str_width(mb_substr($str,$i,1,mb_internal_encoding()));
			}
			return $r;
		}
		if(isset($this->Widths[$str])){
			return $this->Widths[$str] / 1000;
		}
		//half width
		if(preg_match("/^\xef(?:\xbd[\xa1-\xbf]|\xbe[\x80-\x9f])$/",$str)){
			return 0.5;
		}
		if($this->DescendantFonts){
			foreach($this->DescendantFonts as $font){
				$width = $font->str_width($str);
				if(is_numeric($width)){
					return $width;
				}
			}
		}
		return $this->__str_width__($str);
	}
	protected function __str_width__($str){
		return $this->DW ? $this->DW / 1000 : 0;
	}
	final public function encode($str){
		return $this->__encode__($str);
	}
	protected function __encode__($str){
		return mb_convert_encoding($str,"UTF-16BE");
	}
}
?>