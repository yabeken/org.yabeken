<?php
import('org.yabeken.io.stream.Stream');
/**
 * 文字列をストリームっぽく扱う
 * @author yabeken
 * @license New BSD License
 */
class TextStream extends Stream{
	final protected function __new__($resource=null){
		$this->open($resource);
	}
	protected function __set_offset__($offset){
		if($offset < 0 || $offset > $this->length) throw new StreamException("invalid offset");
		$this->offset = $offset;
		return $this->offset;
	}
	/**
	 * ファイルハンドルが開かれているか
	 * @return boolean
	 */
	public function is_opened(){
		return true;
	}
	/**
	 * ファイルハンドルが閉じられているか
	 * @return boolean
	 */
	public function is_closed(){
		return !$this->is_opened();
	}
	/**
	 * 開く
	 * @param string $resource
	 */
	public function open($resource,$mode=null){
		$this->_resource_ = $resource;
		$this->offset = 0;
		$this->length = strlen($this->_resource_);
	}
	/**
	 * 閉じる
	 */
	public function close(){
		$this->_resource_ = null;
	}
	/**
	 * ポインタを変更する
	 * @param integer $len
	 * @param integer $mode
	 */
	public function seek($len,$mode=null){
		/***
			$s = new TextStream("hogehoge");
			eq(1,$s->seek(1));
			eq(2,$s->seek(1));
			eq(8,$s->seek(0,SEEK_END));
			eq(7,$s->seek(-1,SEEK_END));
			eq(5,$s->seek(5,SEEK_SET));
		 */
		switch($mode===null ? SEEK_CUR : $mode){
			case self::SEEK_SET:
				return $this->__set_offset__($len);
			case self::SEEK_END:
				return $this->__set_offset__($this->length + $len);
			default:
			case self::SEEK_CUR:
				return $this->__set_offset__($this->offset + $len);
		}
	}
	/**
	 * 現在位置から指定バイト分読み込む
	 * @param integer $len
	 * @return string
	 */
	public function read($len=2048){
		/***
			$s = new TextStream("hogehoge");
			eq("h",$s->read(1));
			eq("ogehoge",$s->read());
			eq("oge",$s->read(-3));
			eq(5,$s->offset());
		 */
		if($this->offset + $len < 0) $len = -1 * $this->offset;
		if($this->offset + $len > $this->length) $len = $this->length - $this->offset;
		$buf = ($len < 0) ? substr($this->_resource_,$this->offset + $len,-1 * $len) : substr($this->_resource_,$this->offset,$len);
		$this->offset = $this->offset + $len;
		return $buf;
	}
	/**
	 * 一行読み込む
	 * @return string
	 */
	public function read_line($strict=false){
		/***
			$s = new TextStream("hogehoge\rfugafuga\nfoobar\r\nkonokodokonoko");
			eq("hogehoge\r",$s->read_line());
			eq("fugafuga\n",$s->read_line());
			eq("foobar\r\n",$s->read_line());
		 */
		$buf = "";
		while(true){
			if($this->offset >= $this->length) break;
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
	/**
	 * 指定した文字が出現するオフセットを取得
	 * @param string $needle
	 * @param boolean $invert true の際は指定した文字が出現しない最後のオフセット
	 * @param integer $limit
	 */
	public function search($needle,$invert=false,$limit=null){
		/***
			$s = new TextStream("abcdefghijklmnopqrstuvwxyz");
			eq(false,$s->search("hoge"));
			eq(4,$s->search("e"));
			eq(false,$s->search("e",false,2));
			eq(4,$s->search("gfe",true));
		 */
		$offset = $invert ? strcspn($this->_resource_,$needle,$this->offset) : strpos($this->_resource_,$needle,$this->offset);
		if($offset === false || (is_numeric($limit) && $offset > $limit)) return false;
		return $invert ? $this->offset + $offset : $offset;
	}
	/**
	 * 終端かどうか
	 * @return boolean
	 */
	public function eof(){
		/***
			$s = new TextStream("hogehoge\rfugafuga\nfoobar\r\nkonokodokonoko");
			eq(false,$s->eof());
			$s->read();
			eq(true,$s->eof());
		 */
		return $this->offset == $this->length;
	}
	/**
	 * 文字列を書き込む
	 * @param string $str
	 */
	public function write($str){
		/***
			$s = new TextStream("hoge");
			eq(8,$s->write("fuga"));
			eq(8,$s->length());
			$s->offset(0);
			eq("hogefuga",$s->read());
		 */
		$this->_resource_ .= $str;
		$this->length += strlen($str);
		$this->offset = $this->length;
		return $this->offset;
	}
}