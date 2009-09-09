<?php
import("org.yabeken.io.FileStream");
module("ImPdfObj");
/**
 * PDF Parser
 * 
 * 厳密である必要が無いのでいろいろ手を抜く
 * 
 * @author Kentaro YABE
 * @license New BSD License
 */
class PdfParser extends FileStream {
	static protected $__xref__ = "type=int[],set=false";
	static protected $__pages__ = "type=int[],set=false";
	static protected $__obj__ = "type=ImPdfObj[],set=false";
	protected $xref = array();
	protected $pages = array();
	protected $obj = array();
	
	final protected function __init__(){
		$this->parse_xref();
	}
	
	/**
	 * クロスリファレンスを解析
	 */
	protected function parse_xref(){
		//startxref
		$this->seek(0,self::SEEK_END);
		$buf = $this->read(-64);
		if(!preg_match("/startxref(?:\r?\n|\r)(\d+)(?:\r?\n|\r)/",$buf,$matches)) throw new Exception("invalid start xref");
		$startxref = intval($matches[1]);
		
		//xref
		$this->offset($startxref);
		if(!preg_match("/^xref/",$this->read_line(true))) throw new Exception("invalid xref");
		list($start_obj,$num) = explode(" ",trim($this->read_line(true)));
		$offset = array();
		for($i=0;$i<$num;$i++){
			list($size,,$flg) = explode(" ",trim($this->read_line(true)));
			if($flg == "n"){
				$offset[] = intval($size);
			}
		}
		
		//trailer
		if(!preg_match("/^trailer/",$this->read_line(true))) throw new Exception("invalid trailer");
		$trailer = $this->read_line(true);
		while(true){
			$line = $this->read_line(true);
			if(preg_match("/^startxref/",$line)){
				break;
			}
			$trailer .= $line;
		}

		//extra xref
		if($start_obj != 0){
			$buf = "";
			$len = 64;
			$this->seek(0,SEEK_END);
			while(true){
				$buf = $this->read(-1 * $len).$buf;
				if(preg_match("/(.*(?:\r?\n|\r))xref/",substr($buf,2*$len),$matches)){
					$this->seek(strlen($matches[1])+$len);
					$this->read_line(true);
					break;
				}
			}
			list(,$num) = explode(" ",trim($this->read_line(true)));
			for($i=0;$i<$num;$i++){
				list($size,,$flg) = explode(" ",trim($this->read_line(true)));
				if($flg == "n"){
					$offset[] = intval($size);
				}
			}
		}

		//check obj
		foreach($offset as $v){
			$this->offset($v);
			$line = $this->read(16);
			if(!preg_match("/^(\d+) 0 obj/",$line,$matches)){
				throw new Exception("invalid pdf");
			}
			$this->xref[intval($matches[1])] = intval($v);
		}

		$this->parse_trailer($trailer);
	}
	/**
	 * トレーラを解析
	 *
	 * @param string $trailer
	 */
	protected function parse_trailer($trailer){
		//Root
		if(!preg_match("@/Root (\d+) 0 R@",$trailer,$matches)){
			throw new Exception("Root not found");
		}
		$root_obj = $this->read_obj(intval($matches[1]));
		//Pages
		if(!preg_match("@/Pages (\d+) 0 R@",$root_obj,$matches)){
			throw new Exception("PDF has no pages");
		}
		$pages_obj = $this->read_obj(intval($matches[1]));
		if(!preg_match("@/Kids ?\[(.+)\]@",$pages_obj,$matches)){
			throw new Exception("PDF has no pages");
		}
		$pages = $matches[1];
		if(!preg_match_all("/ ?(\d+) 0 R/",$pages,$matches)){
			throw new Exception("PDF has no pages");
		}
		$this->pages = array_map("intval",$matches[1]);
	}
	
	/**
	 * PDF オブジェクトの文字列を取得
	 *
	 * @param integer $obj_id
	 * @return string
	 */
	protected function read_obj($obj_id){
		if(!isset($this->xref[$obj_id])) throw new Exception("object [{$obj_id}] not found");
		$this->offset($this->xref[$obj_id]);
		$buf = "";
		while(true){
			$line = $this->read_line(true);
			$buf .= $line;
			if(preg_match("/^endobj/",$line)) break;
		}
		return $buf;
	}
	
	/**
	 * PDF オブジェクトを取得
	 *
	 * @param integer $obj_id
	 * @return ImPdfObj
	 */
	public function get_obj($obj_id){
		if(isset($this->obj[$obj_id])) return $this->obj[$obj_id];
		$this->obj[$obj_id] = new ImPdfObj($this);
		$this->obj[$obj_id]->resource($this->read_obj($obj_id));
		return $this->obj[$obj_id];
	}
	
	/**
	 * 指定ページの PDF オブジェクトを取得
	 *
	 * @param integer $page_no
	 * @return ImPdfObj
	 */
	public function page($page_no){
		if(!isset($this->pages[$page_no-1])) throw new Exception("page not found");
		return $this->get_obj($this->pages[$page_no-1]);
	}
}
?>