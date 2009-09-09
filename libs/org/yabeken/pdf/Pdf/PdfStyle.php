<?php
/**
 * PDFで文字や画像を描画する際のオプションを保持する
 *
 * @author Kentaro YABE
 */
class PdfStyle extends Object {
	static protected $__font__ = "type=string";
	static protected $__font_size__ = "type=number";
	static protected $__font_color__ = "type=string";
	static protected $__align__ = "type=choice(left,right,center,justify)";
	static protected $__width__ = "type=number";
	static protected $__height__ = "type=number";
	static protected $__char_space__ = "type=number";
	static protected $__word_space__ = "type=number";
	static protected $__scale__ = "type=number";
	static protected $__leading__ = "type=number";
	static protected $__render__ = "type=choice(0,1,2,3,4,5,6,7)";
	static protected $__rise__ = "type=number";
	static protected $__rotate__ = "type=number";
	protected $font;
	protected $font_size = 10.5;
	protected $font_color = "#000000";
	protected $align = "left";
	protected $width;
	protected $height;
	protected $char_space;
	/**
	 * Word Spacing
	 * 
	 * Note: Word spacing is applied to every occurrence of the single-byte character code 32 in a string 
	 * when using a simple font or a composite font that defines code 32 as a single-byte code. 
	 * It does not apply to occurrences of the byte value 32 in multiple-byte codes.
	 *
	 * @var numeric
	 */
	protected $word_space;
	protected $scale;
	protected $leading;
	protected $render;
	protected $rise;
	protected $rotate;
	
	protected function __str__(){
		$result = array();
		foreach($this->get_access_vars() as $key => $value){
			$result[] = sprintf("%s=%s",$key,$value);
		}
		return implode(",",$result);
	}
	protected function setFont_color($value){
		if(!preg_match("/^#[0-9a-f]{6}$/i",$value)){
			throw new Exception("invalid format");
		}
		$this->font_color = $value;
	}
	protected function setRender($value){
		$this->render = intval($value);
	}
	
	protected function formatFont_color(){
		return sprintf("%.3f %.3f %.3f rg",
					hexdec(substr($this->font_color,1,2))/255,
					hexdec(substr($this->font_color,3,2))/255,
					hexdec(substr($this->font_color,5,2))/255
				);
	}
	protected function formatChar_space(){
		return sprintf(sprintf("%s Tc",$this->char_space));
	}
	protected function formatWord_space(){
		return sprintf("%s Tw",$this->word_space);
	}
	protected function formatScale(){
		return sprintf("%s Tz",$this->scale);
	}
	protected function formatLeading(){
		return sprintf("%s Tz",$this->leading);
	}
	protected function formatRise(){
		return sprintf("%s Ts",$this->rise);
	}
	protected function formatRender(){
		return sprintf("%s Tr",$this->render);
	}
	protected function formatFont(){
		return sprintf("/%s %s Tf","RF-".$this->font,$this->font_size);
	}
	protected function formatRotate($x,$y){
		$angle = $this->rotate * pi() / 180;
		$cos = cos($angle);
		$sin = sin($angle);
		return sprintf("%.3f %.3f %.3f %.3f %.3f %.3f cm 1 0 0 1 %.3f %.3f cm",
						$cos,$sin,
						-$sin,$cos,
						$x,$y,
						-$x,-$y
					);
	}
}
?>