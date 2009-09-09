<?php
/**
 * PDFの基本要素
 *
 * @author Kentaro YABE
 */
class PdfObj extends Object {
	static protected $__id__ = "type=integer";
	static protected $__dictionary__ = "type=mixed{}";
	static protected $__stream__ = "type=string";
	static protected $__value__ = "type=mixed";
	static protected $__ref__ = "type=PdfObj[]";
	
	protected $id;
	protected $dictionary = array();
	protected $stream;
	protected $value;
	protected $ref = array();
	
	protected $Length;
	protected $Filter;
	protected $DecodeParams;
	protected $F;
	protected $FFilter;
	protected $FDecodeParams;
	protected $DL;
	
	final protected function __str__(){
		$buf = "";
		$stream = null;
		if($this->isId()){
			if(!is_null($this->stream)){
				$stream = $this->__filter__($this->stream);
				$this->dictionary("Length",strlen($stream));
			}
			$buf .= self::line(sprintf("%d 0 obj",$this->id));
		}
		if(!$this->value){
			$buf .= self::line("<<");
			foreach($this->hash() as $key => $value){
				if(!is_null($value)){
					$buf .= self::line(sprintf("/%s %s",$key,self::format($value)));
				}
			}
			$buf .= (is_null($this->id) ? ">>" : self::line(">>"));
			if(!is_null($stream)){
				$buf .= self::line("stream");
				$buf .= in_array(substr($stream,-1),array("\r","\n")) ? $stream : self::line($stream);
				$buf .= self::line("endstream");
			}
		}else{
			$buf .= self::line(self::format($this->value));
		}
		if($this->id){
			$buf .= self::line("endobj");
		}
		return $buf;
	}
	protected function __filter__($str){
		$this->dictionary("Filter","/FlateDecode");
		return gzcompress($str);
	}
	protected function __hash__(){
		$result = array();
		foreach($this->get_access_vars() as $name => $value){
			if(ctype_upper($name[0])){
				$result[$name] = $value;
			}
		}
		return array_merge($result,$this->dictionary);
	}
	protected function setDictionary($key,$value=null){
		if(is_array($key)){
			foreach($key as $k=>$v){
				$this->setDictionary($k,$v);
			}
			return $key;
		}else{
			if(isset($this->{$key})){
				$this->{$key}($value);
			}else{
				$this->dictionary[$key] = $value;
			}
			return $value;
		}
	}
	public function inDictionary($key){
		if(!$key || !ctype_upper($key[0])) return null;
		if(isset($this->{$key})) return $this->{$key}();
		if(isset($this->dictionary[$key])) return $this->dictionary[$key];
	}
	public function verifyDictionary($key){
		if(!$key || !ctype_upper($key[0])) return false;
		if(isset($this->{$key})) return true;
		if(isset($this->dictionary[$key])) return true;
		return false;
	}
	final protected function setRef($obj){
		$this->ref[] = $obj;
		return $obj;
	}
	final protected function formatId(){
		if(!$this->isId()) throw new Exception("not a pdf object");
		return sprintf("%d 0 R",$this->id);
	}
	final protected function verifyId(){
		return is_int($this->id);
	}
	final protected function setStream($str,$append=true){
		if($append){
			$this->stream .= self::line($str);
		}else{
			$this->stream = $str;
		}
		return $this->stream;
	}
	final static public function format($var){
		if($var instanceof PdfObj){
			return $var->isId() ? $var->fmId() : (string)$var;
		}else if(is_array($var)){
			foreach($var as $k=>$v){
				$var[$k] = self::format($v);
			}
			return sprintf("[ %s ]", implode(" ", $var));
		}else if(is_numeric($var)){
			return sprintf("%s",$var);
		}else if(is_bool($var)){
			return $var ? "true" : "false";
		}else if(is_string($var)){
			if($var == "") return "null";
			if($var[0] == "/") return $var;
			if(preg_match("/^\<(?:[0-9a-f]{2})+\>$/i",$var)) return $var;
			return sprintf("(%s)",self::escape($var));
		}else if(is_null($var)){
			return "null";
		}
		throw new Exception("can not format variable");
	}
	final static public function line($str){
		return $str."\n";
	}
	final static public function escape($str){
		return str_replace(array("\\","(",")","\r"),array("\\\\","\\(","\\)","\\r"),$str);
	}
}
?>