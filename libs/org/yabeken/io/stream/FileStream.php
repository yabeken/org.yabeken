<?php
import('org.yabeken.io.stream.Stream');
/**
 * ファイルをストリームっぽく扱う
 * @author yabeken
 * @license New BSD License
 */
class FileStream extends Stream{
	final protected function __new__($filename=null,$mode="rb+"){
		if($filename) $this->open($filename,$mode);
	}
	final protected function __del__(){
		$this->close();
	}
	protected function __set_offset__($offset){
		fseek($this->_resource_,$offset,self::SEEK_SET);
		$this->offset = ftell($this->_resource_);
		return $this->offset;
	}
	/**
	 * ファイルハンドルが開かれているか
	 * @return boolean
	 */
	public function is_opened(){
		return is_resource($this->_resource_);
	}
	/**
	 * ファイルハンドルが閉じられているか
	 * @return boolean
	 */
	public function is_closed(){
		return !$this->is_opened();
	}
	/**
	 * ファイルを開く
	 * @param string $filename
	 * @param string $mode
	 */
	public function open($filename,$mode=null){
		if(!is_file($filename)) throw new StreamException(sprintf("%s is not a file",$filename));
		$this->close();
		$this->_resource_ = fopen($filename,$mode==null?"rb+":$mode);
		$this->seek(0,self::SEEK_SET);
		$this->length = filesize($filename);
	}
	/**
	 * ファイルを閉じる
	 */
	public function close(){
		if($this->is_opened()) fclose($this->_resource_);
	}
	/**
	 * ポインタを変更する
	 * @param integer $len
	 * @param integer $mode
	 */
	public function seek($len,$mode=null){
		/***
			$fname = File::temp_path(work_path());
			File::write($fname,"hogehoge");
			$s = new FileStream($fname);
			eq(1,$s->seek(1));
			eq(2,$s->seek(1));
			eq(8,$s->seek(0,SEEK_END));
			eq(7,$s->seek(-1,SEEK_END));
			eq(5,$s->seek(5,SEEK_SET));
			$s->close();
			File::rm($fname);
		 */
		switch($mode===null ? self::SEEK_CUR : $mode){
			case self::SEEK_SET:
				return $this->__set_offset__($len);
			case self::SEEK_END:
				return $this->__set_offset__($this->length+$len);
			default:
			case self::SEEK_CUR:
				return $this->__set_offset__($this->offset+$len);
		}
	}
	/**
	 * バッファーを指定バイト分読み込む
	 *
	 * @param integer $len
	 * @return string
	 */
	public function read($len=2048){
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
		if($len == 0) return;
		if($len < 0){
			$this->seek($this->offset + $len < 0 ? -1 * $offset : $len,self::SEEK_CUR);
			$buf = fread($this->_resource_,-1 * $len);
		}else{
			$buf = fread($this->_resource_,$this->offset + $len > $this->length ? $this->length - $this->offset : $len);
			$this->offset = ftell($this->_resource_);
		}
		return $buf;
	}
	/**
	 * 一行読み込む
	 * @param boolean $strict 厳密に行末をチェックするかどうか
	 * @return string
	 */
	public function read_line($strict=false){
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
		$buf = "";
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
	}
	/**
	 * 指定した文字が出現するオフセットを取得
	 * @param string $needle
	 * @param boolean $invert true の際は指定した文字が出現しない最後のオフセット
	 * @param integer $limit 検索時のサイズ制限
	 */
	public function search($needle,$invert=false,$limit=null){
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
		$cur_offset = $this->offset;
		$buf = "";
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
	}
	/**
	 * 終端かどうか
	 * @return boolean
	 */
	public function eof(){
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
		return feof($this->_resource_) || $this->offset >= $this->length;
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
		return $this->offset;
	}
	/**
	 * truncate
	 */
	public function truncate(){
		ftruncate($this->_resource_, 0);
		$this->offset = 0;
		$this->length = 0;
	}
}