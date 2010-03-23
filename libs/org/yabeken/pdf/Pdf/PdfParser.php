<?php
module("parser.PdfRef");
module("parser.PdfTplObj");
/**
 * PDF Parser
 * @author Kentaro YABE
 * @license New BSD License
 */
class PdfParser extends Object{
	static protected $__id__ = "type=string,set=false";
	static protected $__obj__ = "type=PdfTplObj[],set=false";
	protected $id;
	protected $obj;
	
	private $_page_ = array();
	private $_file_;
	private $_xref_ = array();
	private $_catalog_;
	private $_parsing_obj_;
	
	static private $_whitespace_ = "\x00\x09\x0a\x0c\x0d\x20";
	static private $_delimiter_ = "<>[]()/%";
	
	protected function __new__($filename){
		$this->id = md5($filename);
		$this->_file_ = new FileStream($filename,"rb");
		$this->parse();
	}
	/**
	 * PDFオブジェクトをエクスポートする
	 * @param integer $id
	 * @return PdfTplObj
	 */
	public function export_obj($id){
		return $this->get_obj($id,true);
	}
	/**
	 * ページをエクスポートする
	 * @param integer $page_no
	 * @return PdfTplObj
	 */
	public function page($page_no){
		if(!isset($this->_page_[$page_no])) throw new PdfException("Page not found");
		$page = $this->get_obj($this->_page_[$page_no]);
		return $page;
	}
	protected function __in_obj__($id){
		return $this->get_obj($id);
	}
	private function parse(){
		//trailer
		$buf = "";
		$this->_file_->seek(0,FileStream::SEEK_END);
		while(strpos($buf,"trailer")===false){
			$buf = $this->_file_->read(-256).$buf;
		}
		//startxref
		if(!preg_match("/startxref(?:\r|\n|\r\n)(\d+)(?:\r|\n)/",$buf,$m)) throw new PdfException("startxref not found");
		$this->parse_xref(intval($m[1]));
		//pages
		$this->parse_pages();
	}
	private function parse_xref($startxref){
		$this->_file_->offset($startxref);
		if(!preg_match("/^xref/",$this->_file_->read_line(true))) throw new PdfException("invalid xref");
		list(,$num) = explode(" ",trim($this->_file_->read_line(true)));

		$offset_list = array();
		$search_offset = $this->_file_->search("trailer");
		if($search_offset === false) throw new PdfException("invalid pdf file");
		$offset_source = explode("\n",trim(str_replace(array("\r\n","\r"),"\n",$this->_file_->read($search_offset - $this->_file_->offset()))));
		foreach($offset_source as $line){
			$flg = substr(trim($line),-1);
			if($flg == "n"){
				list($offset,,) = explode(" ",$line);
				$offset_list[] = intval($offset);
			}
		}

		$search_offset = $this->_file_->search("startxref");
		if($search_offset === false) throw new PdfException("invalid pdf file");
		$trailer = $this->_file_->read($search_offset - $this->_file_->offset() + 9);

		//check obj
		foreach($offset_list as $offset){
			$this->_file_->offset($offset);
			$line = $this->_file_->read(16);
			if(!preg_match("/^(\d+) (\d+) obj/",$line,$matches)) throw new PdfException("invalid pdf");
			$id = intval($matches[1]);
			$this->_xref_[$id] = intval($offset);
			$this->obj[$id] = null;
		}
		//prev
		if(preg_match("@/Prev\s+(\d+)@",$trailer,$m)){
			$this->parse_xref(intval($m[1]));
		}
		//root
		if(is_null($this->_catalog_) && preg_match("@/Root\s+(\d+) 0 R@",$trailer,$m)){
			$this->_catalog_ = $this->get_obj(intval($m[1]));
		}
	}
	private function parse_pages(){
		$pages = $this->get_obj($this->_catalog_->in_dictionary("Pages")->id());
		$page_no = 1;
		foreach($pages->in_dictionary("Kids") as $ref){
			$this->_page_[$page_no++] = $ref->id();
		}
	}
	private function get_obj($id,$rawdata=false){
		if(isset($this->obj[$id])) return $this->obj[$id];
		return $this->obj[$id] = $this->parse_obj($id,$rawdata);
	}
	private function read_obj($id){
		if(!isset($this->_xref_[$id])) throw new PdfException(sprintf("object id not found [%s]",$id));
		$cur_offset = $this->_file_->offset();
		$this->_file_->offset($this->_xref_[$id]);
		$offset = $this->_file_->search("endobj");
		if($offset === false) throw new PdfException("invalid pdf file");
		$this->_file_->seek(strlen("{$id} 0 obj"));
		$buf = trim($this->_file_->read($offset - $this->_file_->offset()),self::$_whitespace_);
		$this->_file_->offset($cur_offset);
		return $buf;
	}
	private function parse_obj($id,$rawdata=false){
		if(!isset($this->_xref_[$id])) throw new PdfException(sprintf("object id not found [%s]",$id));
		
		$this->_parsing_obj_ = $obj = new PdfTplObj();
		$obj->parser_id($this->id);
		
		if($rawdata){
			//手抜き参照チェック
			$rawstr = $this->read_obj($id);
			$obj->rawdata(true);
			$obj->value($rawstr);
			if(preg_match_all("/(\d+)\s+0\s+R/",$rawstr,$matches)!==false){
				foreach($matches[1] as $id){
					$obj->ref(new PdfRef("id={$id},parser_id={$this->id}"));
				}
			}
		}else{
			if(!isset($this->_xref_[$id])) throw new PdfException(sprintf("object id not found [%s]",$id));
			$this->_file_->offset($this->_xref_[$id]+strlen("{$id} 0 obj"));
			$this->skip_whitespace();
			$this->parse_value($obj);
			if(($offset = $this->_file_->search("stream",false,32)) !== false){
				$obj->stream(true);
				$this->_file_->offset($offset + 6);
				$this->skip_whitespace();
				$length = $obj->in_dictionary("Length");
				$length = $length instanceof PdfRef ? intval(trim($this->read_obj($length->id()))) : $length;
				$obj->dictionary("Length",$length);
				//TODO 圧縮の種類
				$value = $this->_file_->read($length);
				if($obj->is_dictionary("Filter")){
					$value = gzuncompress($value);
				}
				$obj->value($value);
			}
		}
		
		$this->_parsing_obj_ = null;
		return $obj;
	}
	private function parse_value($result=null){
		$token = $this->read_token();
		if($token instanceof PdfObj) return $token;
		switch($token){
			case "<<":
				if($result === null) $result = new PdfTplObj();
				$pobj = $this->_parsing_obj_;
				$this->_parsing_obj_ = $result;
				while(true){
					$tk = $this->read_token();
					if(is_string($tk) && $tk == ">>") break;
					$result->dictionary($this->parse_value(),$this->parse_value());
				}
				$this->_parsing_obj_ = $pobj;
				return $result;
			case "[":
				$result = array();
				while(true){
					$tk = $this->read_token();
					if(is_string($tk) && $tk == "]") break;
					if(!($tk instanceof PdfObj) && strpos(self::$_delimiter_,$tk)!==false){
						$this->_file_->seek(-1 * strlen($tk));
						$tk = $this->parse_value();
					}
					$result[] = $this->normalize($tk);
				}
				return $result;
			case "(":
				$result = "";
				$escape = false;
				while(true){
					$c = $this->_file_->read(1);
					if(!$escape && $c == ")") break;
					$escape = ($c == "\\");
					$result .= $c;
				}
				return str_replace(array("\\\\","\\(","\\)","\\r"),array("\\","(",")","\r"),$result);
			case "<":
				return $token.$this->_file_->read($this->_file_->search(">",true) - $this->_file_->offset() + 1);
			case "/":
				$r = $token.$this->_file_->read($this->_file_->search(self::$_whitespace_.self::$_delimiter_,true) - $this->_file_->offset());
				return $r;
			default:
				$value = $this->normalize($token);
				if($result instanceof PdfObj){
					$result->value($value);
				}
				return $value;
		}
	}
	private function read_token(){
		$this->skip_whitespace();
		$token  = $this->_file_->read(1);
		switch($token){
			case "<":
			case ">":
				if($this->_file_->read(1) == $token){
					return $token.$token;
				}
				$this->_file_->seek(-1);
				return $token;
			case "[":
			case "]":
			case "(":
			case ")":
			case "/":
				return $token;
			case "%":
				$this->_file_->read_line(true);
				return $this->read_token();
			default:
				$token .= $this->_file_->read($this->_file_->search(self::$_whitespace_.self::$_delimiter_,true) - $this->_file_->offset());
				$offset = $this->_file_->offset();
				if(is_numeric($token) && $this->read_token() === "0" && $this->read_token() === "R"){
					$ref = new PdfRef("id={$token},parser_id={$this->id}");
					$this->_parsing_obj_->ref($ref);
					return $ref;
				}
				$this->_file_->offset($offset);
				return $token;
		}
	}
	private function normalize($var){
		if(is_numeric($var)) return ctype_digit($var) ? intval($var) : floatval($var);
		if(is_string($var)){
			if(strlen($var) == 0) return;
			if($var[0] == "/") return $var;
			if($var === "true") return true;
			if($var === "false") return false;
			if($var === "null") return null;
		}
		return $var;
	}
	private function skip_whitespace(){
		while(!$this->_file_->eof()){
			if(strpos(self::$_whitespace_,$this->_file_->read(1))===false){
				$this->_file_->seek(-1);
				break;
			}
		}
	}
}