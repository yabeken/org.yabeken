<?php
module("PdfObj");
module("PdfParser");
module("PdfStyle");
module("model.PdfInfo");
module("model.PdfCatalog");
module("model.PdfResources");
module("model.PdfFont");
module("model.PdfImagePNG");
module("model.PdfImageJPEG");
module("model.PdfPage");
module("model.PdfFont");
module("model.PdfFontDescriptor");
/**
 * PDF
 *
 * @author Kentaro YABE
 * @license New BSD License
 */
class Pdf extends Object {
	static protected $__obj__ = "type=PdfObj[]";
	static protected $__current_page__ = "type=PdfPage,set=false";
	static protected $__current_font__ = "type=PdfFont,set=false";
	static protected $__current_style__ = "type=PdfStyle,set=false";

	protected $_mixin_ = "PdfInfo,PdfCatalog,PdfResources,PdfStyle";
	
	protected $obj = array();
	protected $current_page;
	protected $current_font;
	protected $current_style;
	
	protected function __init__(){
		$this->obj($this->m("PdfInfo"));
		$this->obj($this->m("PdfCatalog"));
		$this->obj($this->m("PdfResources"));
	}
	protected function __str__(){
		$xref = array();
		ob_start();
		print("%PDF-1.4\n");
		foreach($this->obj as &$o){
			$xref[] = ob_get_length();
			print($o->str());
		}
		$startxref = ob_get_length();
		print("xref\n");
		print(sprintf("0 %d\n",count($xref)+1));
		print("0000000000 65535 f\n");
		foreach($xref as $len){
			print(sprintf("%010d 00000 n\n",$len));
		}
		//trailer
		print("trailer\n");
		print("<<\n");
		print(sprintf("/ID [ <%s> <%s> ]\n",md5(uniqid("")),md5(uniqid(""))));
		print(sprintf("/Info %s\n",$this->m("PdfInfo")->fmId()));
		print(sprintf("/Root %s\n",$this->m("PdfCatalog")->fmId()));
		print(sprintf("/Size %d\n",count($xref)));
		print(">>\n");
		//startxref
		print("startxref\n");
		print($startxref."\n");
		//eof
		print("%%EOF\n");
		return ob_get_clean();
	}
	
	/**
	 * 新しいページを追加する
	 *
	 * @param mixed $page
	 */
	public function add_page($page=null){
		if($page instanceof ImPdfObj) return $this->import($page);
		$this->current_page = $this->obj(($page instanceof PdfPage) ? $page : new PdfPage());
		$this->current_page->Resources($this->m("PdfResources"));
		$this->m("PdfCatalog")->Pages()->Kids($this->current_page);
	}
	/**
	 * ページをインポートする
	 *
	 * @param ImPdfObj $page
	 */
	function import(ImPdfObj $page){
		if($page->isDictionary("Type") != "/Page") throw new Exception("object is not page");
		if($page->isDictionary("Parent") != "/Page") throw new Exception("parent is not page");
		$page->replace("Parent",$this->m("PdfCatalog")->Pages());
		$resources = $page->inDictionary("Resources");
		$page->replace("Resources",$this->m("PdfResources"));
		if($resources->isDictionary("Font")){
			foreach($resources->inDictionary("Font")->dictionary() as $key => $font){
				$this->add_font($key,$font);
			}
		}
		//TODO XObject
		if($resources->isDictionary("XObject")){
			foreach($resources->inDictionary("XObject")->dictionary() as $key => $xobj){
				$this->add_xobject($key,$xobj);
			}
		}
		$this->current_page = $this->obj($page);
		$this->m("PdfCatalog")->Pages()->Kids($this->current_page);
	}
	protected function add_font($name,$font){
		if($this->m("PdfResources")->Font()->isDictionary($name)) return true;
		$this->m("PdfResources")->Font()->dictionary($name,$this->obj($font));
		return true;
	}
	protected function add_xobject($name,$xobj){
		if($this->m("PdfResources")->XObject()->isDictionary($name)) return true;
		$this->m("PdfResources")->XObject()->dictionary($name,$this->obj($xobj));
		return true;
	}
	
	/**
	 * PNG画像を追加する
	 *
	 * @param string $name
	 * @param string $src
	 */
	public function add_png($name,$src){
		if($this->m("PdfResources")->XObject()->isDictionary("RI-".$name)) throw new Exception(sprintf("XObject [%s] has already existed",$name));
		$this->m("PdfResources")->XObject()->dictionary("RI-".$name,$this->obj(new PdfImagePNG($src)));
	}
	
	/**
	 * JPEG画像を追加する
	 *
	 * @param string $name
	 * @param string $src
	 */
	public function add_jpeg($name,$src){
		if($this->m("PdfResources")->XObject()->isDictionary("RI-".$name)) throw new Exception(sprintf("XObject [%s] has already existed",$name));
		$this->m("PdfResources")->XObject()->dictionary("RI-".$name,$this->obj(new PdfImageJPEG($src)));
	}
	
	/**
	 * 画像を表示する
	 *
	 * @param string $name
	 * @param numeric $x
	 * @param numeric $y
	 * @param unknown_type $style
	 */
	public function image($x,$y,$name,$style=null){
		$current_style = $style ? $this->m("PdfStyle")->str() : "";
		if($style) $this->dict($style);
		if(!$this->m("PdfResources")->XObject()->isDictionary("RI-".$name)) throw new Exception(sprintf("XObject [%s] does not exist",$name));
		$image = $this->m("PdfResources")->XObject()->inDictionary("RI-".$name);
		$contents = $this->current_page->inDictionary("Contents");
		$contents->stream("q");
		//scale
		$width = $this->isWidth() ? $this->width() : $image->Width();
		$height = $this->isHeight() ? $this->height() : $image->Height();
		if($this->isScale()){
			$width = $width * $this->scale() / 100;
			$height = $height * $this->scale() / 100;
		}
		$contents->stream(sprintf("%.2F 0 0 %.2F %.2F %.2F cm",$width,$height,$x,$y));
		$contents->stream(sprintf("/%s Do","RI-".$name));
		$contents->stream("Q");
		
		$this->dict($current_style);
	}
	
	/**
	 * 文字を書き込む
	 *
	 * @param numeric $x
	 * @param numeric $y
	 * @param string $str
	 * @param dict $style
	 */
	public function text($x,$y,$str,$style=null){
		$current_style = $style ? $this->m("PdfStyle")->str() : "";
		if($style) $this->dict($style);
		$str = str_replace(array("¥r","¥n"),"",$str);
		switch($this->align()){
			case "justify":
			case "left":
				break;
			case "center":
				$x -= $this->current_font->str_width($str) * $this->font_size() / 2;
				break;
			case "right":
				$x -= $this->current_font->str_width($str) * $this->font_size();
				break;
		}
		if($this->isWidth()){
			$len = mb_strlen($str);
			$width = $this->current_font->str_width($str) * $this->font_size();
			if($len==1){
				$x += $width / 2;
			}else{
				$this->char_space(($this->width() - $width) / ($len - 1));
			}
		}
		if(!$str==="") return;
		$contents = $this->current_page->inDictionary("Contents");
		$contents->stream("BT");
		$contents->stream("q");
		if($this->isRotate()) $contents->stream($this->fmRotate($x,$y));
		if($this->isChar_space()) $contents->stream($this->fmChar_space());
		if($this->isWord_space()) $contents->stream($this->fmWord_space());
		if($this->isScale()) $contents->stream($this->fmScale());
		if($this->isLeading()) $contents->stream($this->fmLeading());
		if($this->isRise()) $contents->stream($this->fmRise());
		if($this->isRender()) $contents->stream($this->fmRender());
		$contents->stream($this->fmFont());
		$contents->stream($this->fmFont_color());
		$contents->stream(sprintf("%s %s Td",$x, $y));
		$contents->stream(sprintf("(%s) Tj",PdfObj::escape($this->current_font->encode($str))));
		$contents->stream("Q");
		$contents->stream("ET");
		
		$this->dict($current_style);
	}
	
	/**
	 * PDFオブジェクトを登録する
	 *
	 * @param PdfObj $obj
	 * @return PdfObj
	 */
	protected function setObj($obj){
		$this->obj[] = $obj;
		$obj->id(count($this->obj));
		foreach($obj->ref() as $o){
			if(!$o->isId()) $this->setObj($o);
		}
		return $obj;
	}
	
	/**
	 * mixinされたfontプロパティのフック
	 *
	 * @param mixed $font
	 */
	protected function __mixin_font__($font){
		if(!$font) return;
		$class = ($font instanceof PdfFont) ? get_class($font) : $font;
		if($this->m("PdfResources")->Font()->isDictionary("RF-".$class)){
			$this->current_font = $this->m("PdfResources")->Font()->inDictionary("RF-".$class);
			return;
		}
		$font = ($font instanceof PdfFont) ? $font : new $class();
		if(!($font instanceof PdfFont)) throw new Exception("invalid font");
		$this->m("PdfResources")->Font()->dictionary("RF-".$class,$this->obj($font));
		$this->current_font = $font;
	}
}
?>