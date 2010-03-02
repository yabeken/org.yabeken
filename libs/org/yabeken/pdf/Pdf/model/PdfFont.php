<?php
module("PdfObj");
class PdfFont extends PdfObj{
	static protected $__half_width__ = "type=integer";
	protected $half_width = 500;
	
	static protected $__Type__ = "type=name";
	static protected $__Subtype__ = "type=name";
	static protected $__Widths__ = "type=integer{},set=false";
	
	protected $Type = "/Font";
	protected $Subtype;
	// Type1 / TrueType
	protected $Name;
	protected $BaseFont;
	protected $FirstChar;
	protected $LastChar;
	protected $Widths;
	protected $FontDescriptor;
	protected $Encoding;
	protected $ToUnicode;
	// Type3
	protected $FontBBox;
	protected $FontMatrix;
	protected $CharProcs;
	protected $Resources;
	// CIDFont
	protected $CIDSystemInfo;
	protected $DW;
	protected $W;
	protected $DW2;
	protected $W2;
	protected $CIDToGIDMap;
	// Type0
	protected $DescendantFonts;

	/**
	 * 文字列幅を取得する
	 * @param string $str
	 */
	final public function calc_width($str){
		$len = mb_strlen($str,"UTF-8");
		if($len == 0) return 0;
		if($len > 1){
			$r = 0;
			for($i=0;$i<$len;$i++){
				$r += $this->calc_width(mb_substr($str,$i,1,"UTF-8"));
			}
			return $r;
		}
		
//		if(method_exists($this,"__calc_width__")) return $this->__calc_width__($str);
		
		if($this->is_Widths(ord($str))) return $this->in_Widths(ord($str)) / 1000;
		
//		if(preg_match("/^\xef(?:\xbd[\xa1-\xbf]|\xbe[\x80-\x9f])$/",$str)){
//			return $this->half_width();
//		}
//		
//		if($this->DescendantFonts){
//			foreach($this->DescendantFonts as $font){
//				$width = $font->str_width($str);
//				if(is_numeric($width)){
//					return $width;
//				}
//			}
//		}
//		return $this->is_DW() ? $this->DW() / 1000 : 0;
	}
	final public function encode($str){
		return mb_convert_encoding($str,"UTF-16BE","UTF-8");
	}
}