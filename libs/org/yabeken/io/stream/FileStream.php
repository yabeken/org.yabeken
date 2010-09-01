<?php
import('org.yabeken.io.stream.Stream');
/**
 * File Stream
 * @author yabeken
 * @license New BSD License
 */
class FileStream extends Stream{
	/**
	 * Constructor
	 * @param string $filename
	 * @param string $mode
	 */
	final protected function __new__($filename=null,$mode='rb+'){
		if($filename !== null) $this->open($filename,$mode);
	}
	/**
	 * Destructor
	 */
	final protected function __del__(){
		$this->close();
	}
	/**
	 * set offset
	 * @param integer $offset
	 * @return $this
	 */
	protected function __set_offset__($offset){
		fseek($this->_resource_,$offset,self::SEEK_SET);
		$this->offset = ftell($this->_resource_);
		return $this;
	}
	/**
	 * open file stream
	 * @param string $filename
	 * @param string $mode
	 * @return $this
	 */
	public function open($filename,$mode=null){
		if(!is_file($filename)) throw new StreamException(sprintf('file not found [%s]',$filename));
		$this->close();
		$this->_resource_ = fopen($filename,$mode === null ? 'rb+' : $mode);
		$this->seek(0,self::SEEK_SET);
		$this->length = filesize($filename);
		return $this;
	}
	/**
	 * close file stream
	 * @return $this
	 */
	public function close(){
		if($this->is_opened()) fclose($this->_resource_);
	}
	/**
	 * seek pointer
	 * @param integer $len
	 * @param integer $mode
	 * @return $this
	 */
	public function seek($len,$mode=null){
		switch($mode===null ? self::SEEK_CUR : $mode){
			case self::SEEK_SET:
				return $this->__set_offset__($len);
			case self::SEEK_END:
				return $this->__set_offset__($this->length + $len);
			default:
			case self::SEEK_CUR:
				return $this->__set_offset__($this->offset + $len);
		}
		return $this;
		/***
			$fname = File::temp_path(work_path());
			File::write($fname,"hogehoge");
			$s = new FileStream($fname);
			eq(1,$s->seek(1)->offset());
			eq(2,$s->seek(1)->offset());
			eq(8,$s->seek(0,SEEK_END)->offset());
			eq(7,$s->seek(-1,SEEK_END)->offset());
			eq(5,$s->seek(5,SEEK_SET)->offset());
			$s->close();
			File::rm($fname);
		 */
	}
	/**
	 * バッファーを指定バイト分読み込む
	 *
	 * @param integer $len
	 * @return string
	 */
	public function read($len=2048){
		if($len == 0) return;
		if($len < 0){
			$this->seek($this->offset + $len < 0 ? -1 * $offset : $len,self::SEEK_CUR);
			$buf = fread($this->_resource_,-1 * $len);
		}else{
			$buf = fread($this->_resource_,$this->offset + $len > $this->length ? $this->length - $this->offset : $len);
			$this->offset = ftell($this->_resource_);
		}
		return $buf;
		/***
			$fname = File::temp_path(work_path());
			File::write($fname,"hogehoge");
			$s = new FileStream($fname);
			eq("h",$s->read(1));
			eq("ogehoge",$s->read());
			eq("oge",$s->read(-3));
			eq(5,$s->offset());
			eq(null,$s->read(0));
			$s->close();
			File::rm($fname);
		 */
	}
	/**
	 * 一行読み込む
	 * @param boolean $strict 厳密に行末をチェックするかどうか
	 * @return string
	 */
	public function read_line($strict=false){
		$buf = '';
		if(!$strict){
			$buf = fgets($this->_resource_);
		}else{
			while(true){
				if(feof($this->_resource_)) break;
				$c = fread($this->_resource_,1);
				$buf .= $c;
				if($c == "\r"){
					$c = fread($this->_resource_,1);
					if($c != "\n"){
						$this->seek(ftell($this->_resource_)-1,self::SEEK_SET);
						break;
					}
					$buf .= $c;
				}
				if($c == "\n") break;
			}
		}
		$this->seek(ftell($this->_resource_),self::SEEK_SET);
		return $buf;
		/***
			$fname = File::temp_path(work_path());
			File::write($fname,"hogehoge\rfugafuga\nfoobar\r\nkonokodokonoko");
			$s = new FileStream($fname);
			eq("hogehoge\r",$s->read_line(true));
			eq("fugafuga\n",$s->read_line(true));
			eq("foobar\r\n",$s->read_line(true));
			$s->offset(0);
			eq("hogehoge\rfugafuga\n",$s->read_line());
			eq("foobar\r\n",$s->read_line());
			eq("konokodokonoko",$s->read_line());
			$s->close();
			File::rm($fname);
		 */
	}
	/**
	 * 指定した文字が出現するオフセットを取得
	 * @param string $needle
	 * @param boolean $invert true の際は指定した文字が出現しない最後のオフセット
	 * @param integer $limit 検索時のサイズ制限
	 * @return integer
	 */
	public function search($needle,$invert=false,$limit=null){
		$cur_offset = $this->offset;
		$buf = '';
		$offset = null;
		while(true){
			$buf .= $this->read();
			$offset = $invert ? strcspn($buf,$needle) : strpos($buf,$needle);
			if($offset !== false && ($limit == null || $offset <= $limit)) break;
			if($this->offset >= $this->length){
				$this->seek($cur_offset,self::SEEK_SET);
				return false;
			}
		}
		$this->seek($cur_offset,self::SEEK_SET);
		return $this->offset + $offset;
		/***
			$fname = File::temp_path(work_path());
			File::write($fname,"abcdefghijklmnopqrstuvwxyz");
			$s = new FileStream($fname);
			eq(false,$s->search("hoge"));
			eq(4,$s->search("e"));
			eq(false,$s->search("e",false,2));
			eq(4,$s->search("gfe",true));
			$s->close();
			File::rm($fname);
		 */
	}
	/**
	 * 文字列を追記
	 * @param string $str
	 */
	public function write($str){
		/***
			$fname = File::temp_path(work_path());
			File::write($fname,"hoge");
			$s = new FileStream($fname);
			eq(8,$s->write("fuga"));
			eq(8,$s->length());
			$s->offset(0);
			eq("hogefuga",$s->read());
			$s->close();
			File::rm($fname);
		 */
		$this->seek(0,self::SEEK_END);
		fwrite($this->_resource_,$str);
		$this->offset = ftell($this->_resource_);
		$this->length += strlen($str);
		return $this;
	}
	/**
	 * 空にする
	 */
	public function truncate(){
		ftruncate($this->_resource_, 0);
		$this->offset = 0;
		$this->length = 0;
		return $this;
	}
	/**
	 * 終端か
	 * @return boolean
	 */
	public function is_eof(){
		return feof($this->_resource_) || $this->offset >= $this->length;
		/***
			$fname = File::temp_path(work_path());
			File::write($fname,"hogehoge\rfugafuga\nfoobar\r\nkonokodokonoko");
			$s = new FileStream($fname);
			eq(false,$s->eof());
			$s->read();
			eq(true,$s->eof());
			$s->close();
			File::rm($fname);
		 */
	}
	/**
	 * ハンドルが開かれているか
	 * @return boolean
	 */
	public function is_opened(){
		return is_resource($this->_resource_);
	}
	/**
	 * ハンドルが閉じられているか
	 * @return boolean
	 */
	public function is_closed(){
		return !$this->is_opened();
	}
}