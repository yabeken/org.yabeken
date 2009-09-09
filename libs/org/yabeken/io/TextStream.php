<?php
/**
 * 文字列をストリームっぽく読み出す
 *
 * @author Kentaro YABE
 * @license New BSD License
 */
class TextStream extends Object {
	static protected $__resource__ = "type=string";
	static protected $__offset__ = "type=number";
	static protected $__length__ = "type=number,set=false";
	protected $resource;
	protected $offset;
	protected $length;
	
	const SEEK_CUR = SEEK_CUR;
	const SEEK_END = SEEK_END;
	const SEEK_SET = SEEK_SET;
	
	protected function __new__($resource=null){
		$this->setResource($resource);
	}
	protected function setResource($resource){
		$this->resource = $resource;
		$this->offset = 0;
		$this->length = strlen($this->resource);
	}
	protected function setOffset($offset){
		if($offset < 0 || $offset > $this->length) throw new Exception("invalid offset");
		$this->offset = $offset;
		return $this->offset;
	}
	public function seek($len,$mode=null){
		switch($mode===null?SEEK_CUR:$mode){
			case self::SEEK_SET:
				return $this->offset($len);
			case self::SEEK_END:
				return $this->offset($this->length+$len);
			default:
			case self::SEEK_CUR:
				return $this->offset($this->offset+$len);
		}
	}
	
	/**
	 * バッファーを指定バイト分読み込む
	 *
	 * @param integer $len
	 * @return string
	 */
	public function read($len){
		if($this->offset + $len < 0) $len = -1 * $offset;
		if($this->offset + $len > $this->length) $len = $this->length - $this->offset;
		$buf = substr($this->resource,$this->offset,$len);
		$this->offset($this->offset + $len);
		return $buf;
	}

	/**
	 * 一行読み込む
	 *
	 * @return string
	 */
	public function read_line(){
		$buf = "";
		while(true){
			if($this->offset >= $this->length) break;
			$c = $this->read(1);
			$buf .= $c;
			if($c == "\r"){
				$c = $this->read(1);
				if($c != "\n"){
					$this->offset(-1,SEEK_CUR);
					break;
				}
				$buf .= $c;
			}
			if($c == "\n") break;
		}
		return $buf;
	}
	
	public function search($needle,$invert=false){
		$offset = $invert ? strcspn($this->resource,$needle,$this->offset) : strpos($this->resource,$needle,$this->offset);
		if($offset===false) return false;
		return $invert ? $this->offset + $offset : $offset;
	}
}
?>