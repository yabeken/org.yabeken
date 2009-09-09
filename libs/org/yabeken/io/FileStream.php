<?php
class FileStream extends Object{
	static protected $__resource__ = "type=mixed,set=false";
	static protected $__offset__ = "type=number";
	static protected $__length__ = "type=number,set=false";
	protected $resource;
	protected $offset;
	protected $length;

	const SEEK_CUR = SEEK_CUR;
	const SEEK_END = SEEK_END;
	const SEEK_SET = SEEK_SET;
	
	protected function __new__($filename,$mode="rb"){
		$this->setResource($filename,$mode);
	}
	protected function __del__(){
		if(is_resource($this->resource)) fclose($this->resource);
	}
	protected function setResource($filename,$mode){
		$this->__del__();
		if(!is_file($filename)) throw new Exception(sprintf("%s is not a file",$filename));
		$this->resource = fopen($filename,$mode);
		$this->offset = 0;
		$this->length = filesize($filename);
	}
	protected function setOffset($offset){
		fseek($this->resource,$offset,self::SEEK_SET);
		$this->offset = ftell($this->resource);
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
		$buf = "";
		if($len < 0){
			if($this->offset + $len < 0) $len = -1 * $this->offset;
			$this->seek($len,self::SEEK_CUR);
			$buf = fread($this->resource,abs($len));
			$this->seek($len,self::SEEK_CUR);
		}else{
			$buf = fread($this->resource,$len);
			$this->offset = ftell($this->resource);
		}
		return $buf;
	}
	
	/**
	 * 一行読み込む
	 *
	 * @param boolean $strict 厳密に行末をチェックするかどうか
	 * @return string
	 */
	public function read_line($strict=false){
		if(!$strict) return fgets($this->resource);
		$buf = "";
		while(true){
			if(feof($this->resource)) break;
			$c = $this->read(1);
			$buf .= $c;
			if($c == "\r"){
				$c = $this->read(1);
				if($c != "\n"){
					$this->seek(-1);
					break;
				}
				$buf .= $c;
			}
			if($c == "\n") break;
		}
		return $buf;
	}
}
?>