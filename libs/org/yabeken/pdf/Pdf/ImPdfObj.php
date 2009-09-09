<?php
import("org.yabeken.io.TextStream");
module("PdfObj");
/**
 * import 用の PDF オブジェクト
 *
 * @author Kentaro YABE
 * @license New BSD License
 */
class ImPdfObj extends PdfObj {
	static protected $whitespace = "\x00\x09\x0a\x0c\x0d\x20";
	static protected $delimiter = "<>[]()/%";
	static protected $__oid__ = "type=integer,set=false";
	static protected $__parser__ = "type=PdfParser,set=false";
	
	protected $_mixin_ = "TextStream";
	protected $oid;
	protected $parser;

	protected function __new__($parser){
		$this->parser = $parser;
	}
	protected function __mixin_resource__(){
		$this->parse();
	}
	protected function __filter__($str){
		if($this->inDictionary("Filter") == "/FlateDecode"){
			return parent::__filter__($str);
		}else{
			return $str;
		}
	}
	
	/**
	 * PDF オブジェクトを解析する
	 */
	protected function parse(){
		$this->oid = intval($this->read_token());
		$this->offset($this->search("obj")+3);
		$this->parse_value($this);
		if(($offset = $this->search("stream"))!==false){
			$this->offset($offset+6);//strlen("stream") = 6
			$this->skip_whitespace();
			$length = $this->inDictionary("Length");
			$length = ($length instanceof self) ? $length->value() : $length;
			$stream = $this->read($length);
			if($this->inDictionary("Filter") == "/FlateDecode"){
				$this->stream(gzuncompress($stream),true);
			}else{
				$this->stream($stream);
			}
		}
	}
	
	/**
	 * 現在位置から値を解析する
	 *
	 * @param ImPdfObj $result pdfobj
	 * @return mixed
	 */
	protected function parse_value($result=null){
		$token = $this->read_token();
		if($token instanceof self) return $token;
		switch($token){
			case "<<":
				if($result === null) $result = new self($this->parser);
				while(true){
					$tk = $this->read_token();
					if(is_string($tk) && $tk == ">>") break;
					$result->dictionary($this->parse_value(),$this->parse_value());
				}
				return $result;
			case "[":
				$result = array();
				while(true){
					$value = $this->read_token();
					if(is_string($value) && $value == "]") break;
					if(!($value instanceof self) && strpos(self::$delimiter,$value)!==false){
						$this->seek(-1*strlen($value));
						$value = $this->parse_value();
					}
					$result[] = $this->normalize($value);
				}
				return $result;
			case "(":
				$result = $this->read($this->search(")",true) - $this->offset());
				$this->seek(1);
				return $result;
			case "<":
				return $token.$this->read($this->search(">",true) - $this->offset() + 1);
			case "/":
				return $token.$this->read($this->search(self::$whitespace.self::$delimiter,true) - $this->offset());
			default:
				$value = $this->normalize($token);
				if($result instanceof self){
					$result->value($value);
				}
				return $value;
		}
	}
	
	/**
	 * 文字列を正規化する
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	protected function normalize($value){
		if($value instanceof ImPdfObj) return $value;
		if($value[0]=="/") return $value;
		if($value==="true") return true;
		if($value==="false") return false;
		if($value==="null") return null;
		//TODO float
		if(is_numeric($value)) return (strpos($value,".")===false) ? intval($value) : floatval($value);
		return $value;
	}
	
	/**
	 * 現在のオフセットからトークンを読み込む
	 *
	 * @return mixed
	 */
	protected function read_token(){
		$this->skip_whitespace();
		$token  = $this->read(1);
		switch($token){
			case "<":
			case ">":
				if($this->read(1) == $token){
					return $token.$token;
				}
				$this->seek(-1);
				return $token;
			case "[":
			case "]":
			case "(":
			case ")":
			case "/":
				return $token;
			case "%":
				$this->read_line();
				return $this->read_token();
			default:
				$token .= $this->read($this->search(self::$whitespace.self::$delimiter,true) - $this->offset());
				$offset = $this->offset();
				if(is_numeric($token) && $this->read_token() === "0" && $this->read_token() === "R"){
					return $this->ref($this->parser->get_obj(intval($token)));
				}
				$this->offset($offset);
				return $token;
		}
	}
	
	/**
	 * 空白をスキップする
	 */
	protected function skip_whitespace(){
		while(true){
			if(strpos(self::$whitespace,$this->read(1))===false){
				$this->seek(-1);
				return;
			}
		}
	}
	
	/**
	 * ページ取り込みのためオブジェクトを入れ替える
	 *
	 * @param string $key
	 * @param PdfObj $obj
	 */
	public function replace($key,$obj){
		if($this->inDictionary($key)){
			$old = $this->inDictionary($key);
			$this->dictionary($key,$obj);
			foreach($this->ref as $key => $ref){
				if($ref->oid() == $old->oid()){
					unset($this->ref[$key]);
					return;
				}
			}
		}
	}
}
?>