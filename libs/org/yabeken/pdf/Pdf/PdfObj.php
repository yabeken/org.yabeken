<?php
/**
 * PDF Object
 * @author Kentaro YABE
 */
class PdfObj extends Object{
	static protected $__uid__ = "type=string,set=false";
	static protected $__id__ = "type=integer";
	static protected $__dictionary__ = "type=mixed{}";
	static protected $__stream__ = "type=boolean";
	static protected $__value__ = "type=mixed";
	static protected $__ref__ = "type=PdfObj[]";
	static protected $__rawdata__ = "type=boolean";

	protected $uid;
	protected $id;
	protected $dictionary = array();
	protected $stream = false;
	protected $value;
	protected $ref = array();
	protected $rawdata = false;
	
	private $_dictionary_ = array();
	
	//PDF
	protected $Length;
	protected $Filter;
	protected $DecodeParms;
	protected $F;
	protected $FFilter;
	protected $FDecodeParms;
	protected $DL;
	
	/**
	 * フォーマット
	 * @param mixed $var
	 */
	final static protected function format($var){
		if($var instanceof self){
			return $var->is_id() ? $var->fm_id() : (string)$var;
		}else if(is_object($var)){
			return self::to_string($var);
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
			return sprintf("(%s)",str_replace(array("\\","(",")","\r"),array("\\\\","\\(","\\)","\\r"),$var));
		}else if(is_null($var)){
			return "null";
		}
		return str($var);
	}
	final protected function __new__($dict=null){
		foreach($this->props() as $name){
			if(ctype_upper($name[0])) $this->_dictionary_[] = $name;
		}
		if(!empty($dict)){
			foreach(Text::dict($dict) as $key => $value){
				$this->$key($value);
			}
		}
		$this->uid = md5(uniqid(time(),true));
	}
	final protected function __set__($args,$param){
		/***
			uc($name1,'
				static protected $__Aaa__ = "type=name";
				static protected $__Bbb__ = "type=dictionary";
				protected $Aaa;
				protected $Bbb;
			',"PdfObj");
			
			$a = new $name1();
			eq("/aaa",$a->Aaa("/aaa"));
			try{
				$a->Aaa("aaa");
				eq("invalid type","Aaa");
			}catch(Exception $e){
				eq("Aaa","Aaa");
			}
			eq(true,$a->Bbb(new PdfObj) instanceof PdfObj);
			try{
				$a->Bbb("pdfobj");
				eq("invalid type","Bbb");
			}catch(Exception $e){
				eq("Bbb","Bbb");
			}
		 */
		if(!$param->set) throw new InvalidArgumentException('Processing not permitted [set]');
		if($args[0] === null || $args[0] === '') return null;
		$arg = $args[0];
		switch($param->type){
			case "name":
				if(!is_string($arg) || $arg[0] !== "/") $this->invalid_argument($param,$arg);
				return $arg;
			case "dictionary":
				if(!($arg instanceof PdfObj)) $this->invalid_argument($param,$arg);
				return $arg;
		}
		return parent::__set__($args,$param);
	}
	final protected function __str__(){
		/***
			//dictionary
			$obj = new PdfObj();
			eq("<< >>",$obj->str());
			$obj->dictionary("Hoge","Fuga");
			eq("<< /Hoge (Fuga) >>",$obj->str());
			$obj->dictionary("Foo",123);
			eq("<< /Hoge (Fuga) /Foo 123 >>",$obj->str());
			
			//object
			$obj = new PdfObj("id=1");
			eq("1 0 obj\nendobj",$obj->str());
			$obj->value(1234);
			eq("1 0 obj\n1234\nendobj",$obj->str());
			$obj->value("hogehoge");
			eq("1 0 obj\n(hogehoge)\nendobj",$obj->str());
			
			//dictionary object
			$obj = new PdfObj("id=1");
			$obj->dictionary("Hoge","Fuga");
			eq("1 0 obj\n<<\n/Hoge (Fuga)\n>>\nendobj",$obj->str());
			try{
				$obj->value("hoge");
				eq("pdf object can not have value and dictionary at once",$obj->str());
			}catch(Exception $e){
				eq("ok","ok");
			}
			
			//stream
			$obj = new PdfObj("stream=true,id=1");
			eq("1 0 obj\n<<\n/Length 0\n>>\nstream\nendstream\nendobj",$obj->str());
			$obj->value("fugafuga");
			eq("1 0 obj\n<<\n/Length 8\n>>\nstream\nfugafuga\nendstream\nendobj",$obj->str());
		 */
		if(!$this->__is_id__()) return $this->__fm_dictionary__(" ");
		$buf = sprintf("%d 0 obj\n",$this->id);
		if($this->rawdata){
			$buf .= $this->value."\n";
		}else if($this->stream){
			$stream = gzcompress($this->value);
			$this->dictionary("Length",$length = strlen($stream));
			$buf .= $this->__fm_dictionary__("\n")."\n";
			$buf .= "stream\n";
			$buf .= in_array(substr($stream,-1),array("\r","\n")) ? $stream : ($length == 0 ? "" : $stream."\n");
			$buf .= "endstream\n";
		}else{
			if($this->__is_dictionary__()){
				if($this->value) throw new PdfException("object value error");
				$buf .= $this->__fm_dictionary__("\n")."\n";
			}else{
				$buf .= $this->value ? self::format($this->value)."\n" : "";
			}
		}
		$buf .= "endobj";
		return $buf;
	}
	final protected function __hash__(){
		$result = array();
		foreach($this->_dictionary_ as $name){
			$result[$name] = $this->{$name}();
		}
		return array_merge($result,$this->dictionary);
	}
	final protected function __set_value__($value){
		/***
			$obj = new PdfObj("stream=true");
			eq("hoge",$obj->value("hoge"));
			eq("hogehoge",$obj->value("hoge"));
		 */
		if($this->stream){
			$this->value .= $value;
		}else{
			$this->value = $value;
		}
		return $this->value;
	}
	final protected function __fm_id__(){
		if(!$this->__is_id__()) throw new PdfException("id not set");
		return sprintf("%d 0 R",$this->id);
	}
	final protected function __is_id__(){
		return is_int($this->id);
	}
	final protected function __set_dictionary__($key,$value){
		if(in_array($key,$this->_dictionary_)){
			$this->{$key}($value);
		}else{
			$this->dictionary[$key] = $value;
		}
		return $value;
	}
	final protected function __in_dictionary__($key){
		if(empty($key)) return null;
		if(in_array($key,$this->_dictionary_)) return $this->{$key}();
		if(isset($this->dictionary[$key])) return $this->dictionary[$key];
	}
	final protected function __is_dictionary__($key=null){
		if(is_null($key)){
			if(count($this->dictionary) > 0) return true;
			foreach($this->_dictionary_ as $name){
				if($this->{$name}() !== null){
					return true;
				}
			}
			return false;
		}
		if(in_array($key,$this->_dictionary_) || isset($this->dictionary[$key])) return true;
		return false;
	}
	final protected function __fm_dictionary__($glue){
		if($this->rawdata) return $this->value;
		$props = "";
		foreach($this->__hash__() as $key => $value){
			if(!is_null($value)) $props .= sprintf("/%s %s%s",$key,self::format($value),$glue);
		}
		return "<<".$glue.$props.">>";
	}
	final protected function __set_ref__($obj){
		$this->ref[] = $obj;
		return $obj;
	}
}