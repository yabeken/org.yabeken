<?php
import("org.yabeken.io.stream.TextStream");
import("org.yabeken.io.stream.FileStream");
module("PdfException");
module("PdfObj");
module("PdfParser");
module("model.PdfCatalog");
module("model.PdfFont");
module("model.PdfFontDescriptor");
module("model.PdfForm");
module("model.PdfImage");
module("model.PdfInfo");
module("model.PdfPage");
module("model.PdfPages");
module("model.PdfResources");
/**
 * PDF
 * CSS と似たような形式でスタイル指定が可能
 * 
 * TODO テンプレートの登録と適用
 * TODO 画像の回転ができてない予感？
 * TODO ページ出力時等のイベント処理
 * TODO 太字と斜体字
 * TODO メモリ節約のためのバイナリファイルの扱い
 * TODO 圧縮方式のサポート
 * TODO 背景色などのスタイリングをどこまでサポート？
 * 
 * MEMO
 * 自動改行などはサポートしない -> 継承した別ライブラリで実装
 * ユニット変換はサポートしない -> 慣れの問題
 * 座標原点は左下 -> PDFの仕様に沿う
 * HTMLは取り込まない -> 継承した別ライブラリで実装
 * 
 * @author yabeken
 * @license New BSD License
 * @version alpha
 */
class Pdf extends Object{
	private $_obj_ = array();
	private $_style_props_ = array();
	
	static protected $_parser_ = array();
	protected $_template_ = array();
	protected $_page_ = array();
	protected $_path_ = array();
	protected $_path_origin_ = array();
	protected $_paths_ = array();
	protected $_style_ = array();
	protected $_cur_page_;
	protected $_cur_font_;
	
	static protected $__info__ = "type=PdfInfo,set=false";
	protected $info;
	protected $_catalog_;
	protected $_resources_;
	
	static protected $__transform__ = "type=number[]";
	/**
	 * 座標原点
	 * @var number[2]
	 */
	protected $transform = array(0,0);
	
	static protected $__font__ = "type=string,style=true";
	static protected $__font_size__ = "type=number,style=true";
	static protected $__color__ = "type=color,style=true";
	protected $font;
	protected $font_size = 10.5;
	protected $color = "#000000";
	
	static protected $__align__ = "type=choice(normal,left,right,center,justify),style=true";
	static protected $__rotate__ = "type=number,style=true";
	static protected $__width__ = "type=number,style=true";
	static protected $__height__ = "type=number,style=true";
	/**
	 * Text Alignment
	 * @var string
	 */
	protected $align = "normal";
	/**
	 * Rotation Angle
	 * @var number
	 */
	protected $rotate;
	/**
	 * Text Writing Width
	 * @var number
	 */
	protected $width;
	/**
	 * Text Writing Height
	 * @var number
	 */
	protected $height;
	
	static protected $__char_space__ = "type=number,style=true";
	static protected $__leading__ = "type=number,style=true";
	static protected $__render__ = "type=choice(0,1,2,3,4,5,6,7),style=true";
	static protected $__rise__ = "type=number,style=true";
	static protected $__scale__ = "type=number,style=true";
	static protected $__word_space__ = "type=number,style=true";
	/**
	 * Character space
	 * @var number
	 */
	protected $char_space;
	/**
	 * Leading
	 * @var numberic
	 */
	protected $leading;
	/**
	 * Render
	 * @var integer
	 */
	protected $render;
	/**
	 * Rise
	 * @var number
	 */
	protected $rise;
	/**
	 * Scale in percentage
	 * @var number 0..100
	 */
	protected $scale;
	/**
	 * Word Spacing
	 * 
	 * Note: Word spacing is applied to every occurrence of the single-byte character code 32 in a string 
	 * when using a simple font or a composite font that defines code 32 as a single-byte code. 
	 * It does not apply to occurrences of the byte value 32 in multiple-byte codes.
	 * @var number
	 */
	protected $word_space;
	
	static protected $__line_width__ = "type=number,style=true";
	static protected $__line_cap__ = "type=choice(0,1,2),style=true";
	static protected $__line_join__ = "type=choice(0,1,2),style=true";
	static protected $__miter_limit__ = "type=number,style=true";
	static protected $__dash__ = "type=string,style=true";
	static protected $__line_color__ = "type=color,style=true";
	static protected $__flatness__ = "type=intger,style=true";
	/**
	 * Line Width
	 * @var number
	 */
	protected $line_width;
	/**
	 * Line Cap
	 * @var number 0,1,2
	 */
	protected $line_cap;
	/**
	 * Line Join Style
	 * @var number 0,1,2
	 */
	protected $line_join;
	/**
	 * Miter Limit
	 * @var number
	 */
	protected $miter_limit;
	/**
	 * Dash
	 * @var string
	 */
	protected $dash;
	/**
	 * Line Color
	 * @var color
	 */
	protected $line_color = "#000000";
	/**
	 * Flatness Tolerance
	 * @var integer
	 */
	protected $flatness;
	
	static protected $__border_color__ = "type=color[],style=true";
	static protected $__border_style__ = "type=choice(none,dotted,dashed,solid,double,groove,ridge,inset,outset)";
	static protected $__background_color__ = "type=color,style=true";
	/**
	 * Border Color
	 * @var color[4]
	 */
	protected $border_color;
	/**
	 * Border Style
	 * @var string
	 */
	protected $border_style = "none";
	/**
	 * Background Color
	 * @var color
	 */
	protected $background_color;
	
	final protected function __init__(){
		$this->_resources_ = $this->add_obj(new PdfResources());
		$this->_catalog_ = $this->add_obj(new PdfCatalog());
		$this->info = $this->add_obj(new PdfInfo());
		foreach($this->props() as $name){
			if($this->a($name,"style")===true){
				$this->_style_props_[] = $name;
			}
		}
	}
	final protected function __str__(){
		$xref = array();
		ob_start();
		println("%PDF-1.4");
		foreach(array_keys($this->_obj_) as $id){
			$xref[] = ob_get_length();
			println($this->get_obj($id)->str());
			$this->_obj_[$id]->str();
		}
		$startxref = ob_get_length();
		println("xref");
		println(sprintf("0 %d",count($xref)+1));
		println("0000000000 65535 f");
		foreach($xref as $len){
			println(sprintf("%010d 00000 n",$len));
		}
		//trailer
		println("trailer");
		println("<<");
		println(sprintf("/ID [ <%s> <%s> ]",md5(uniqid("")),md5(uniqid(""))));
		println(sprintf("/Info %s",$this->info->fm_id()));
		println(sprintf("/Root %s",$this->_catalog_->fm_id()));
		println(sprintf("/Size %d",count($xref)+1));
		println(">>");
		//startxref
		println("startxref");
		println($startxref);
		//eof
		print("%%EOF");
		return ob_get_clean();
	}
	final protected function __set__($args,$param){
		//TODO test
		if(!$param->set) throw new InvalidArgumentException('Processing not permitted [set]');
		if($args[0] === null || $args[0] === '') return null;
		$arg = $args[0];
		switch($param->type){
			case "color":
				if(preg_match("/^#[0-9a-f]{6}$/i",$arg)!==1) $this->invalid_argument($param,$arg);
				return $arg;
			case "colors":
				//TODO colors
				throw new Exception("not implemented");
		}
		return parent::__set__($args,$param);
	}
	/**
	 * 現在のページに書き込む
	 * @param $rawdata
	 */
	protected function write_contents($rawdata){
		$this->_cur_page_->in_dictionary("Contents")->value(str($rawdata));
	}
	/**
	 * 座標変換
	 * @param number $x
	 * @param number $y
	 * @return number[2]
	 */
	protected function coordinate_transform($x,$y){
		if(!is_numeric($x) || !is_numeric($y)) throw new PdfException("invalid argument");
		$x += $this->transform[0];
		$y += $this->transform[1];
		return array($x,$y);
	}
	/**
	 * 座標回転
	 * @param number $x
	 * @param number $y
	 * @return string
	 */
	protected function coordinate_rotate($x,$y){
		$theta = $this->rotate * M_PI / 180;
		$cos = cos($theta);
		$sin = sin($theta);
		return sprintf("%.3f %.3f %.3f %.3f %.3f %.3f cm 1 0 0 1 %.3f %.3f cm ",$cos,$sin,-$sin,$cos,$x,$y,-$x,-$y);
	}
	/**
	 * PDFオブジェクトを登録する
	 * @param PdfObj $obj
	 * @return PdfObj
	 */
	final protected function add_obj(PdfObj $obj){
		$id = count($this->_obj_) + 1;
		if(isset($this->_obj_[$id]) && $this->_obj_[$id] instanceof PdfObj) throw new PdfException("object id already exists [{$id}]");
		$obj->id($id);
		$this->_obj_[$id] = $obj;
		foreach($obj->ref() as $o){
			if($o instanceof PdfRef) continue;
			$this->add_obj($o);
		}
		return $obj;
	}
	/**
	 * PDFオブジェクトを取得
	 * @param integer $id
	 * @return PdfObj
	 */
	final protected function get_obj($id){
		if(!isset($this->_obj_[$id])) throw new PdfException("object id not found [{$id}]");
		return $this->_obj_[$id];
	}
	/**
	 * ページ追加
	 * @param PdfPage $page
	 */
	public function add_page(PdfObj $page){
		$this->_cur_page_ = $page instanceof PdfPage ? $this->add_obj($page) : $this->import_page($page);
		$this->_cur_page_->Resources($this->_resources_);
		$this->_catalog_->Pages()->Kids($this->_cur_page_);
		$this->_page_[count($this->_page_)+1] = $this->_cur_page_->id();
	}
	/**
	 * ページ移動
	 * @param integer $page_no
	 */
	public function page($page_no){
		if(!isset($this->_page_[$page_no])) throw new PdfException("page not found [{$page_no}]");
		$this->_cur_page_ = $this->_page_[$page_no];
	}
	/**
	 * ページ数
	 * @return integer
	 */
	public function total_page(){
		return count($this->_page_);
	}
	/**
	 * フォント追加
	 * @param PdfFont $font
	 */
	final public function add_font(PdfFont $font){
		$name = "RF-".get_class($font);
		if(!$this->_resources_->Font()->is_dictionary($name)){
			$this->_resources_->Font()->dictionary($name,$this->add_obj($font));
		}
		return $this->_resources_->Font()->in_dictionary($name);
	}
	/**
	 * XObject を追加する
	 * @param string $name
	 * @param PdfObj $xobj
	 * @return boolean
	 */
	final protected function add_xobject($name,PdfObj $xobj){
		if($this->_resources_->XObject()->is_dictionary($name)) return;
		$this->_resources_->XObject()->dictionary($name,$this->add_obj($xobj));
	}
	/**
	 * PNG画像を追加
	 * @param string $name
	 * @param binary $src
	 */
	public function add_png($name,$src){
		if($this->_resources_->XObject()->is_dictionary("RI-".$name)) throw new PdfException(sprintf("Image already exists [%s]",$name));
		$image = new PdfImage("Filter=/FlateDecode");
		$stream = new TextStream($src);
		$stream->seek(8);
		while(true){
			$len = $stream->read_uint32_be();
			switch($stream->read(4)){
				case "IHDR":
					$image->Width($stream->read_uint32_be());
					$image->Height($stream->read_uint32_be());
					$image->BitsPerComponent($stream->read_uint8());
					//color space
					$cs = $stream->read_uint8();
					switch($cs){
						case 0: //grayscale
							$image->ColorSpace("/DeviceGray");
							break;
						case 2: //rgb
							$image->ColorSpace("/DeviceRGB");
							break;
						case 3: //palette
							break;
						default:
							//TODO
							throw new PdfException("alpha channel is not supported");
					}
					//compression,filter,interlace
					$stream->seek(3);
					
					$decodeParms = new PdfObj();
					$decodeParms->dictionary("Predictor",15);
					$decodeParms->dictionary("Colors",in_array($cs,array(2,6)) ? 3 : 1);
					$decodeParms->dictionary("BitsPerComponent",$image->BitsPerComponent());
					$decodeParms->dictionary("Columns",$image->Width());
					$image->DecodeParms($decodeParms);
					break;
				case "PLTE": //palette
					$palette = new PdfObj("stream=true");
					$palette->value($stream->read($len));
					$image->ColorSpace(array("/Indexed","/DeviceRGB",($len/3)-1,$image->ref($palette)));
					break;
				case "tRNS": // transparency
					$trns = $stream->read($len);
					if($image->ColorSpace() == "/DeviceGray"){
						$image->Mask(array(ord(substr($trns,1,1)),ord(substr($trns,1,1))));
					}else if($image->ColorSpace() == "/DeviceRGB"){
						$image->Mask(array(ord(substr($t,1,1)),ord(substr($t,1,1)),ord(substr($t,3,1)),ord(substr($t,3,1)),ord(substr($t,5,1)),ord(substr($t,5,1))));
					}else{
						$pos = strpos($trns,chr(0));
						if($pos !== false){
							$image->Mask(array($pos,$pos));
						}
					}
					break;
				case "IDAT": //image data
					$image->value($stream->read($len));
					break;
				case "IEND":
					break(2);
				default:
					$stream->seek($len);
					break;
			}
			//crc
			$stream->seek(4);
		}
		unset($stream);
		$this->add_xobject("RI-".$name,$image);
	}
	/**
	 * JPEG画像を追加
	 * @param string $name
	 * @param binary $src
	 */
	public function add_jpeg($name,$src){
		if($this->_resources_->XObject()->is_dictionary("RI-".$name)) throw new PdfException(sprintf("Image already exists [%s]",$name));
		$image = new PdfImage("Filter=/DCTDecode");
		$stream = new TextStream($src);
		//SOI
		$stream->seek(2);
		while(true){
			if($stream->read(1)!="\xFF") throw new PdfException(sprintf("not a jpeg image [%s]",$name));
			if($stream->read(1) == "\xC0"){
				//Lf
				$stream->seek(2);
				//bits
				$image->BitsPerComponent(hexdec(bin2hex($stream->read(1))));
				//height
				$image->Height(hexdec(bin2hex($stream->read(2))));
				//width
				$image->Width(hexdec(bin2hex($stream->read(2))));
				//Nif
				switch(hexdec(bin2hex($stream->read(1)))){
					case 3:
						$image->ColorSpace("/DeviceRGB");
						break;
					case 4:
						$image->ColorSpace("/DeviceCMYK");
						$image->Decode(array(1,0,1,0,1,0,1,0));
						break;
					default:
						$image->ColorSpace("/DeviceGray");
						break;
				}
				break;
			}
			$stream->seek(hexdec(bin2hex($stream->read(2)))-2);
		}
		unset($stream);
		$image->value($src);
		$this->add_xobject("RI-".$name,$image);
	}
	/**
	 * 画像描画
	 * @param number $x
	 * @param number $y
	 * @param string $name_or_filename
	 * @param string $style
	 */
	public function image($x,$y,$name_or_filename,$style=null){
		list($x,$y) = $this->coordinate_transform($x,$y);
		$name = $name_or_filename;
		if(File::exist($name_or_filename)){
			$name = md5($name_or_filename);
			$file = new File($name_or_filename);
			switch(strtolower($file->ext())){
				case ".png":
					$this->add_png($name,$file->get());
					break;
				case ".jpg":
				case ".jpeg":
					$this->add_jpeg($name,$file->get());
					break;
				default: throw new PdfException(sprintf("unsupported image file [%s]",$name_or_filename));
			}
		}
		if(!$this->_resources_->XObject()->is_dictionary("RI-".$name)) throw new PdfException(sprintf("Image not found [%s]",$name));
		$image = $this->_resources_->XObject()->in_dictionary("RI-".$name);

		if(!is_null($style)) $this->push_style($style);
		//scale
		$width = $this->is_width() ? $this->width() : $image->Width();
		$height = $this->is_height() ? $this->height() : $image->Height();
		if($this->is_scale()){
			$width = $width * $this->scale() / 100;
			$height = $height * $this->scale() / 100;
		}
		$this->write_contents(sprintf("q %.2F 0 0 %.2F %.2F %.2F cm /%s Do Q\n",$width,$height,$x,$y,"RI-".$name));
		if(!is_null($style)) $this->pop_style();
	}
	/**
	 * 文字列描画
	 * @param number $x
	 * @param number $y
	 * @param string $str
	 * @param dict $style
	 */
	public function text($x,$y,$str,$style=null){
		list($x,$y) = $this->coordinate_transform($x,$y);
		$current_style = is_null($style) ? "" : $this->current_style();
		if(!is_null($style)) $this->style($style);
		$str = str_replace(array("\r","\n"),"",$str);
		if(strlen($str)==0) return;
		
		switch($this->align()){
			case "normal":
			case "justify":
			case "left":
				break;
			case "center":
				$x -= $this->_cur_font_->calc_width($str) * $this->font_size() / 2;
				break;
			case "right":
				$x -= $this->_cur_font_->calc_width($str) * $this->font_size();
				break;
		}
		if($this->is_width()){
			$len = mb_strlen($str,"UTF-8");
			$width = $this->_cur_font_->calc_width($str) * $this->font_size();
			if($len==1){
				$x += $width / 2;
			}else{
				$this->char_space(($this->width() - $width) / ($len - 1));
			}
		}
		
		$buf = array();
		$buf[] = "BT q ";
		if($this->is_rotate()) $buf[] = $this->coordinate_rotate($x,$y);
		if($this->is_char_space()) $buf[] = sprintf("%s Tc ",$this->char_space());
		if($this->is_leading()) $buf[] = sprintf("%s Tl ",$this->leading());
		if($this->is_render()) $buf[] = sprintf("%s Tr ",$this->render());
		if($this->is_rise()) $buf[] = sprintf("%s Ts ",$this->rise());
		if($this->is_scale()) $buf[] = sprintf("%s Tz ",$this->scale());
		if($this->is_word_space()) $buf[] = sprintf("%s Tw ",$this->word_space());
		$buf[] = sprintf("/RF-%s %s Tf ",$this->font,$this->font_size);
		if($this->is_color()){
			list($r,$g,$b) = $this->rgb($this->color());
			$buf[] = sprintf("%.3f %.3f %.3f rg ",$r/255,$g/255,$b/255);
		}
		$buf[] = sprintf("%s %s Td ",$x, $y);
		$buf[] = sprintf("(%s) Tj ",str_replace(array("\\","(",")","\r"),array("\\\\","\\(","\\)","\\r"),$this->_cur_font_->encode($str)));
		$buf[] = "Q ET\n";
		$this->write_contents(implode("\n",$buf));
		
		if(!is_null($style)) $this->style($current_style);
	}
	/**
	 * 直線描画
	 * @param number $x1
	 * @param number $y1
	 * @param number $x2
	 * @param number $y2
	 * @param dict $style
	 */
	public function line($x1,$y1,$x2,$y2,$style=null){
		$this->begin_path($x1,$y1);
		$this->add_line_path($x2,$y2);
		$this->draw_path($style);
	}
	/**
	 * 矩形描画
	 * @param number $x
	 * @param number $y
	 * @param number $width
	 * @param number $height
	 * @param dict $style
	 */
	public function rectangle($x,$y,$width,$height,$style=null){
		if($style !== null) $this->push_style($style);
		if($this->is_border_color()){
			if($this->is_background_color()){
				$this->rectangle($x,$y,$width,$height,"border_color=,line_color=");
			}
			list($t,$r,$b,$l) = $this->ar_border_color();
			$this->rm_background_color();
			//TODO 角の処理
			$this->line($x,$y,$x+$width,$y,sprintf("line_color=%s",$b));
			$this->line($x+$width,$y,$x+$width,$y+$height,sprintf("line_color=%s",$r));
			$this->line($x+$width,$y+$height,$x,$y+$height,sprintf("line_color=%s",$t));
			$this->line($x,$y+$height,$x,$y,sprintf("line_color=%s",$l));
		}else{
			$this->begin_path($x,$y);
			$this->add_line_path($x+$width,$y);
			$this->add_line_path($x+$width,$y+$height);
			$this->add_line_path($x,$y+$height);
			$this->add_line_path($x,$y);
			$this->draw_path($style);
		}
		if($style !== null) $this->pop_style();
	}
	/**
	 * 楕円描画
	 * @param number $x
	 * @param number $y
	 * @param number $rx
	 * @param number $ry
	 * @param dict $style
	 */
	public function ellipse($x,$y,$rx,$ry,$style=null){
		$a = 4/3*(M_SQRT2-1);
		$this->begin_path($x+$rx,$y);
		$this->add_bezier_path($x+$rx,$y+$a*$ry,$x+$a*$rx,$y+$ry,$x,$y+$ry);
		$this->add_bezier_path($x-$a*$rx,$y+$ry,$x-$rx,$y+$a*$ry,$x-$rx,$y);
		$this->add_bezier_path($x-$rx,$y-$a*$ry,$x-$a*$rx,$y-$ry,$x,$y-$ry);
		$this->add_bezier_path($x+$a*$rx,$y-$ry,$x+$rx,$y-$a*$ry,$x+$rx,$y);
		$this->draw_path($style);
	}
	/**
	 * 円描画
	 * @param number $x
	 * @param number $y
	 * @param number $r
	 * @param dict $style
	 */
	public function circle($x,$y,$r,$style=null){
		$this->ellipse($x,$y,$r,$r,$style);
	}
	/**
	 * パスの始点を追加
	 * @param number $x
	 * @param number $y
	 */
	public function begin_path($x,$y){
		list($x,$y) = $this->coordinate_transform($x,$y);
		$this->_path_ = array(sprintf("%.3f %.3f m",$x,$y));
		//TODO エレガントに
		$this->_path_origin_ = array($x,$y);
	}
	/**
	 * ベジエ曲線パスを追加
	 * @param number $x1
	 * @param number $y1
	 * @param number $x2
	 * @param number $y2
	 * @param number $x3
	 * @param number $y3
	 */
	public function add_bezier_path($x1,$y1,$x2,$y2,$x3,$y3){
		if(!$this->_path_) throw new PdfException("path does not begin");
		if($x1 === null && $y1 === null){
			list($x2,$y2) = $this->coordinate_transform($x2,$y2);
			list($x3,$y3) = $this->coordinate_transform($x3,$y3);
			$this->_path_[] = sprintf("%.3f %.3f %.3f %.3f v",$x2,$y2,$x3,$y3);
		}else if($x2 === null && $y2 === null){
			list($x1,$y1) = $this->coordinate_transform($x1,$y1);
			list($x3,$y3) = $this->coordinate_transform($x3,$y3);
			$this->_path_[] = sprintf("%.3f %.3f %.3f %.3f y",$x1,$y1,$x3,$y3);
		}else{
			list($x1,$y1) = $this->coordinate_transform($x1,$y1);
			list($x2,$y2) = $this->coordinate_transform($x2,$y2);
			list($x3,$y3) = $this->coordinate_transform($x3,$y3);
			$this->_path_[] = sprintf("%.3f %.3f %.3f %.3f %.3f %.3f c",$x1,$y1,$x2,$y2,$x3,$y3);
		}
	}
	/**
	 * 直線パスを追加
	 * @param number $x
	 * @param number $y
	 */
	public function add_line_path($x,$y){
		list($x,$y) = $this->coordinate_transform($x,$y);
		if(!$this->_path_) throw new PdfException("path does not begin");
		$this->_path_[] = sprintf("%.3f %.3f l",$x,$y);
	}
	/**
	 * パスを閉じる
	 * @param dict $style
	 */
	public function end_path($style=null){
		if(!$this->_path_) throw new PdfException("path does not begin");
		if($style !== null) $this->push_style($style);
		if($this->is_rotate()){
			list($x,$y) = $this->_path_origin_;
			$this->_path_[] = $this->coordinate_rotate($x,$y);
		}
		if($this->is_line_color()){
			list($r,$g,$b) = $this->rgb($this->line_color());
			$this->_path_[] = sprintf("%.3f %.3f %.3f RG",$r/255,$g/255,$b/255);
		}
		if($this->is_background_color()){
			list($r,$g,$b) = $this->rgb($this->background_color());
			$this->_path_[] = sprintf("%.3f %.3f %.3f rg",$r/255,$g/255,$b/255);
		}
		if($this->is_line_width()) $this->_path_[] = sprintf("%.3f w",$this->line_width);
		if($this->is_line_cap()) $this->_path_[] = sprintf("%d J",$this->line_cap);
		if($this->is_line_join()) $this->_path_[] = sprintf("%d j",$this->line_join);
		if($this->is_miter_limit()) $this->_path_[] = sprintf("%.3f M",$this->miter_limit);
		if($this->is_dash()){
			list($pattern,$phase) = $this->ar_dash();
			$this->_path_[] = sprintf("[%s] %d d",implode(" ",$pattern),$phase);
		}
		if($this->is_flatness()) $this->_path_[] = sprintf("%.3f i",$this->flatness);
		$this->_paths_[] = implode(" ",$this->_path_);
		if($style !== null) $this->pop_style();
	}
	public function draw_path($style=null){
		if($this->_path_) $this->end_path($style);
		//TODO クリップパスの扱い
		$this->write_contents(sprintf("q\nn\n%s\n%s\nQ\n",implode("\n",$this->_paths_),$this->is_background_color() ? "b" : "s"));
	}
	/**
	 * スタイルを適用
	 * @param dict $style
	 */
	public function style($style){
		/***
			$pdf = new Pdf();
			$pdf->style("color=#0f0f0f,font_size=24");
			eq("#0f0f0f",$pdf->color());
			eq(24,$pdf->font_size());
			$pdf->style("color=,font_size=");
			eq(10.5,$pdf->font_size());
			try{
				$pdf->style("hoge=>fuga");
				eq("invalid style property is assigned","false");
			}catch(Exception $e){
				eq("ok","ok");
			}
		 */
		foreach(Text::dict($style) as $name=>$value){
			if(!in_array($name,$this->_style_props_)) throw new PdfException("invalid style property [{$name}]");
			if($value == $this->{$name}) continue;
			if(empty($value)){
				$this->{"rm_".$name}();
			}else{
				$this->$name($value);
			}
		}
	}
	/**
	 * 現在スタイルを取得
	 * @return dict
	 */
	protected function current_style(){
		$style = array();
		foreach($this->_style_props_ as $name){
			$style[] = $name."=".$this->{$name};
		}
		return implode(",",$style);
	}
	/**
	 * スタイルをスタックに積んで新しいスタイルを適用
	 * @param dict $style
	 */
	protected function push_style($style=null){
		array_push($this->_style_,$this->current_style());
		$this->style($style);
	}
	/**
	 * スタイルをリストア
	 */
	protected function pop_style(){
		$this->style(array_pop($this->_style_));
	}
	/**
	 * RGB要素に分割
	 * @param string $color
	 */
	protected function rgb($color){
		if(!preg_match("/^#[0-9a-f]{6}$/i",$color)) throw new PdfException("invalid color");
		return array(hexdec(substr($color,1,2)),hexdec(substr($color,3,2)),hexdec(substr($color,5,2)));
	}
	//settings
	protected function __set_transform__($x,$y){
		$this->trasform = array($x,$y);
	}
	protected function __rm_transform__(){
		$this->transform = array(0,0);
	}
	//style
	protected function __set_font__($value){
		/***
			$pdf = new Pdf();
			$fname1 = create_class('','PdfFont');
			$pdf->font($fname1);
			eq($fname1,$pdf->font());
		 */
		if(empty($value)) return;
		$class = ($value instanceof PdfFont) ? get_class($value) : $value;
		if(!class_exists($class)) throw new PdfException("font not found [{$class}]");
		$this->_cur_font_ = $this->add_font($value instanceof PdfFont ? $value : new $class());
		$this->font = $class;
		return $this->font;
	}
	protected function __rm_font_size__(){
		/***
			$pdf = new Pdf();
			$pdf->font_size(12);
			$pdf->rm_font_size();
			eq(10.5,$pdf->font_size());
		 */
		$this->font_size(10.5);
	}
	protected function __rm_align__(){
		/***
			$pdf = new Pdf();
			$pdf->align("center");
			$pdf->rm_align();
			eq("normal",$pdf->align());
		 */
		$this->align("normal");
	}
	protected function __set_dash__(){
		/***
			$pdf = new Pdf();
			$pdf->dash(1,2,3);
			eq(array(array(1,2),3),$pdf->ar_dash());
			$pdf->dash("1 2 3");
			eq(array(array(1,2),3),$pdf->ar_dash());
			$pdf->dash(1,2);
			eq(array(array(1,2),0),$pdf->ar_dash());
			$pdf->dash("1 2");
			eq(array(array(1,2),0),$pdf->ar_dash());
			$pdf->dash(1,0);
			eq(array(array(1),0),$pdf->ar_dash());
			$pdf->dash("1 0");
			eq(array(array(1),0),$pdf->ar_dash());
			$pdf->dash(1,0,3);
			eq(array(array(1),3),$pdf->ar_dash());
			$pdf->dash("1 0 3");
			eq(array(array(1),3),$pdf->ar_dash());
			$pdf->dash(1);
			eq(array(array(1),0),$pdf->ar_dash());
			$pdf->dash("1");
			eq(array(array(1),0),$pdf->ar_dash());
		 */
		$even = $odd = null;
		$phase = 0;
		switch(func_num_args()){
			case 1:
				$args = func_get_arg(0);
				if(is_numeric($args)){
					$even = $args;
				}else{
					$args = explode(" ",$args);
					call_user_func_array(array($this,"dash"),$args);
					return;
				}
				break;
			case 2:
				list($even,$odd) = func_get_args();
				break;
			case 3:
				list($even,$odd,$phase) = func_get_args();
				break;
			default: throw new PdfException("invalid arguments for dash style property");
		}
		if(intval($odd) == 0) $odd = null;
		$this->dash = sprintf("%s %s %s",$even,$odd,$phase);
	}
	protected function __ar_dash__(){
		if(!$this->dash) return;
		list($even,$odd,$phase) = explode(" ",$this->dash);
		return is_numeric($odd) ? array(array(intval($even),intval($odd)),intval($phase)) : array(array(intval($even)),intval($phase));
	}
	protected function __set_border_color__(){
		$t = $r = $b = $l = null;
		$args = func_get_args();
		switch(count($args)){
			case 4:
				list($t,$r,$b,$l) = $args;
				break;
			case 3:
				$t = $args[0];
				$r = $l = $args[1];
				$b = $args[2];
				break;
			case 2:
				$t = $b = $args[0];
				$r = $l = $args[1];
				break;
			case 1:
				$args = explode(" ",$args[0]);
				if(count($args)==1){
					$t = $r = $b = $l = $args[0];
				}else{
					call_user_func_array(array($this,"border_color"),$args);
					return;
				}
				break;
			default: throw new PdfException("invalid arguments for border color");
		}
		$preg = "/^#[0-9a-f]{6}$/i";
		if(!preg_match($preg,$t) || !preg_match($preg,$l) || !preg_match($preg,$b) || !preg_match($preg,$r)){
			throw new PdfException("invalid arguments for border color");
		}
		$this->border_color = "{$t} {$l} {$b} {$r}";
	}
	protected function __ar_border_color__(){
		return $this->border_color ? explode(" ",$this->border_color) : array();
	}
	// Parser 
	/**
	 * PDFファイル解析
	 * @param string $filename_or_id
	 * @return PdfParser
	 */
	final static public function parser($filename_or_id){
		if(isset(self::$_parser_[$filename_or_id])) return self::$_parser_[$filename_or_id];
		$id = md5($filename_or_id);
		if(!isset(self::$_parser_[$id])){
			if(!File::exist($filename_or_id)) throw new PdfException("file not found [{$filename_or_id}]");
			self::$_parser_[$id] = new PdfParser($filename_or_id);
		}
		return self::$_parser_[$id];
	}
	/**
	 * ページをテンプレートとして取り込む
	 * @param PdfTplObj $page
	 */
	final protected function import_page(PdfTplObj $page){
		if($page->in_dictionary("Type") != "/Page") throw new PdfException("template importion failed");
		if(!isset($this->_template_[$page->uid()])){
			$tid = count($this->_template_) + 1;
			$this->_template_[$page->uid()] = $tid;
			$tpl = new PdfForm();
			$parser = self::$_parser_[$page->parser_id()];
			
			//merge contents
			$contents = $page->in_dictionary("Contents");
			if(is_array($contents)){
				$buf = array();
				foreach($contents as $ref){
					$buf[] = $parser->in_obj($ref->id())->value();
				}
				$tpl->value(implode("\n",$buf));
			}else{
				$tpl->value($parser->in_obj($contents->id())->value());
			}
			$tpl->dictionary("Resources",$this->import_resources($page));
			$tpl->dictionary("BBox",$page->in_dictionary("MediaBox"));
			$this->add_xobject("RT-".$tid,$tpl);
		}
		$p = new PdfPage();
		$p->MediaBox($page->in_dictionary("MediaBox"));
		$p->in_dictionary("Contents")->value(sprintf("q /RT-%d Do Q\n",$this->_template_[$page->uid()]));
		return $this->add_obj($p);
	}
	private function import_resources(PdfTplObj $page){
		$resources = $page->in_dictionary("Resources");
		if($resources instanceof PdfRef){
			return $this->import_ref($this->import_obj($page->in_dictionary("Resources")));
		}else{
			$search = $replace = array();
			foreach($resources->dictionary() as $obj){
				if($obj instanceof PdfTplObj){
					foreach($obj->ref() as $ref){
						$o = $this->import_obj($ref);
						$search[] = sprintf("%d 0 R",$ref->id());
						$replace[] = sprintf("%d 0 R",$o->id());
						$this->import_ref($o);
					}
				}else if($obj instanceof PdfRef){
					$o = $this->import_obj($obj);
					$search[] = sprintf("%d 0 R",$obj->id());
					$replace[] = sprintf("%d 0 R",$o->id());
					$this->import_ref($o);
				}
			}
			$result = new PdfObj("rawdata=true");
			$result->value($this->replace_refs($search,$replace,$resources->str()));
			return $result;
		}
	}
	private function import_ref(PdfTplObj $obj){
		$search = $replace = array();
		foreach($obj->ref() as $ref){
			$o = $this->import_obj($ref);
			$search[] = sprintf("%d 0 R",$ref->id());
			$replace[] = sprintf("%d 0 R",$o->id());
			$this->import_ref($o);
		}
		$obj->value($this->replace_refs($search,$replace,$obj->value()));
		return $obj;
	}
	private function replace_refs(array $search,array $replace,$subject){
		//チェックは甘々
		if(!$search || !$replace) return $subject;
		$u = array_map("md5",$search);
		return str_replace(array_merge($search,$u),array_merge($u,$replace),$subject);
	}
	private function import_obj(PdfRef $ref){
		return $this->add_obj(self::$_parser_[$ref->parser_id()]->export_obj($ref->id()));
	}
}