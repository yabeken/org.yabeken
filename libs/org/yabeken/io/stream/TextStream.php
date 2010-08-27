<?php
import('org.yabeken.io.stream.Stream');
/**
 * Text Stream
 * @author yabeken
 * @license New BSD License
 */
class TextStream extends Stream{
	/**
	 * Constructor
	 * @param string $resource
	 */
	final protected function __new__($resource=null){
		$this->open($resource);
	}
	/**
	 * ポインタオフセットを設定する
	 * @param integer $offset
	 * @throws StreamException
	 */
	protected function __set_offset__($offset){
		if($offset < 0 || $offset > $this->length) throw new StreamException("invalid offset");
		$this->offset = $offset;
		return $this->offset;
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
		$this->truncate();
		/***
			$s = new TextStream("hogehoge");
			eq(8,$s->length());
			$s->truncate();
			eq(null,$s->read());
		 */
	}
	/**
	 * ポインタを現在位置から変更する
	 * @param integer $len
	 * @param integer $mode SEEK_CUR,SEEK_END,SEEK_SET
	 */
	public function seek($len,$mode=null){
		switch($mode === null ? SEEK_CUR : $mode){
			case self::SEEK_SET:
				return $this->__set_offset__($len);
			case self::SEEK_END:
				return $this->__set_offset__($this->length + $len);
			default:
			case self::SEEK_CUR:
				return $this->__set_offset__($this->offset + $len);
		}
		/***
			$s = new TextStream("hogehoge");
			eq(1,$s->seek(1));
			eq(2,$s->seek(1));
			eq(8,$s->seek(0,SEEK_END));
			eq(7,$s->seek(-1,SEEK_END));
			eq(5,$s->seek(5,SEEK_SET));
		 */
	}
	/**
	 * 指定した文字が出現するオフセットを取得
	 * @param string $needle
	 * @param boolean $invert true の際は指定した文字が出現しない最後のオフセット
	 * @param integer $limit
	 */
	public function search($needle,$invert=false,$limit=null){
		//TODO $invert -> $mode
		$offset = $invert ? strcspn($this->_resource_,$needle,$this->offset) : strpos($this->_resource_,$needle,$this->offset);
		if($offset === false || (is_numeric($limit) && $offset > $limit)) return false;
		return $invert ? $this->offset + $offset : $offset;
		/***
			$s = new TextStream("abcdefghijklmnopqrstuvwxyz");
			eq(false,$s->search("hoge"));
			eq(4,$s->search("e"));
			eq(false,$s->search("e",false,2));
			eq(4,$s->search("gfe",true));
		 */
	}
	/**
	 * 現在位置から指定バイト分読み込む．終端に達した場合にはその位置まで読み込む．
	 * @param integer $len
	 * @return string
	 */
	public function read($len=2048){
		if($this->offset + $len < 0) $len = -1 * $this->offset;
		if($this->offset + $len > $this->length) $len = $this->length - $this->offset;
		$buf = ($len < 0) ? substr($this->_resource_,$this->offset + $len,-1 * $len) : substr($this->_resource_,$this->offset,$len);
		$this->offset = $this->offset + $len;
		return $buf === false ? null : $buf;
		/***
			$s = new TextStream("hogehoge");
			eq("h",$s->read(1));
			eq("ogehoge",$s->read());
			eq("oge",$s->read(-3));
			eq(5,$s->offset());
		 */
	}
	/**
	 * 一行読み込む
	 * @param boolean $strict 改行が混在している場合には，
	 * @return string
	 */
	public function read_line($strict=false){
		$buf = '';
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
		/***
			$s = new TextStream("hogehoge\rfugafuga\nfoobar\r\nkonokodokonoko");
			eq("hogehoge\r",$s->read_line());
			eq("fugafuga\n",$s->read_line());
			eq("foobar\r\n",$s->read_line());
		 */
	}
	/**
	 * append value
	 * @param string $value
	 */
	public function write($value){
		$this->_resource_ .= $value;
		$this->length += strlen($value);
		$this->offset = $this->length;
		return $this->offset;
		/***
			$s = new TextStream("hoge");
			eq(8,$s->write("fuga"));
			eq(8,$s->length());
			$s->offset(0);
			eq("hogefuga",$s->read());
		 */
	}
	/**
	 * 空にする
	 */
	public function truncate(){
		$this->_resource_ = null;
		$this->offset = 0;
		$this->length = 0;
	}
	/**
	 * ポインタが終端かどうか
	 * @return boolean
	 */
	public function is_eof(){
		return $this->offset == $this->length;
		/***
			$s = new TextStream("hogehoge\rfugafuga\nfoobar\r\nkonokodokonoko");
			eq(false,$s->is_eof());
			$s->read();
			eq(true,$s->is_eof());
		 */
	}
	/**
	 * ハンドルが開かれているか
	 * @return boolean
	 */
	public function is_opened(){
		return true;
	}
	/**
	 * ハンドルが閉じられているか
	 * @return boolean
	 */
	public function is_closed(){
		return !$this->is_opened();
	}
	/***
		# Stream Test with TextStream
		# 8bit and 16bit integer is treated as 32bit integer in PHP
		# 32bit integer has PHP_INT_MAX problem
		
		$s = new TextStream();
		
		# int8 
		# 1000 0000 = -128
		$s->put_int8(1 << 7);
		$s->offset(0);
		eq(-128,$s->get_int8());
		$s->truncate();
		
		# 0111 1111 = 127
		$s->put_int8(~(1 << 7));
		$s->offset(0);
		eq(127,$s->get_int8());
		$s->truncate();
		
		# uint8
		# 1000 0000 = 128
		$s->put_uint8(1 << 7);
		$s->offset(0);
		eq(128,$s->get_uint8());
		$s->truncate();
		
		# 0111 1111 = 127
		$s->put_int8(~(1 << 7));
		$s->offset(0);
		eq(127,$s->get_uint8());
		$s->truncate();
		
		# int16 be
		# 1000 0000 0000 0000 = -32768
		$s->put_int16_be(1 << 15);
		$s->offset(0);
		eq(-32768,$s->get_int16_be());
		$s->truncate();
		
		# 0111 1111 1111 1111 = 32767
		$s->put_int16_be(~(1 << 15));
		$s->offset(0);
		eq(32767,$s->get_int16_be());
		$s->truncate();
		
		# int16 le
		# 0000 0000 1000 0000 = -32768
		$s->put_int16_le(1 << 15);
		$s->offset(0);
		eq(-32768,$s->get_int16_le());
		$s->truncate();
		
		# 1111 1111 01111 1111 = 32767
		$s->put_int16_le(~(1 << 15));
		$s->offset(0);
		eq(32767,$s->get_uint16_le());
		$s->truncate();
		
		# uint16 be
		# 1000 0000 0000 0000 = 32768
		$s->put_uint16_be(1 << 15);
		$s->offset(0);
		eq(32768,$s->get_uint16_be());
		$s->truncate();
		
		# 0111 1111 1111 1111 = 32767
		$s->put_uint16_be(~(1 << 15));
		$s->offset(0);
		eq(32767,$s->get_uint16_be());
		$s->truncate();
		
		# uint16 le
		# 0000 0000 1000 0000 = 32768
		$s->put_uint16_le(1 << 15);
		$s->offset(0);
		eq(32768,$s->get_uint16_le());
		$s->truncate();
		
		# 1111 1111 0111 1111 = 32767
		$s->put_uint16_le(~(1 << 15));
		$s->offset(0);
		eq(32767,$s->get_uint16_le());
		$s->truncate();
		
		# int32 be
		# 1000 0000 0000 0000 0000 0000 0000 0000 = -2147483648
		$s->put_int32_be(1 << 31);
		$s->offset(0);
		eq(1 << 31,$s->get_int32_be());
		$s->truncate();
		
		# 0111 1111 1111 1111 1111 1111 1111 1111 = 2147483647
		$s->put_int32_be(~(1 << 31));
		$s->offset(0);
		eq(~(1 << 31),$s->get_int32_be());
		$s->truncate();
		
		# int32 le
		# 0000 0000 0000 0000 1000 0000 0000 0000 = -2147483648
		$s->put_int32_le(1 << 31);
		$s->offset(0);
		eq(1 << 31,$s->get_int32_le());
		$s->truncate();
		
		# uint32 be
		# 1000 0000 0000 0000 0000 0000 0000 0000 = 2147483648 = -2147483648 (int32)
		$s->put_uint32_be(1 << 31);
		$s->offset(0);
		eq(1 << 31,$s->get_uint32_be());
		$s->truncate();
		
		# 0111 1111 1111 1111 1111 1111 1111 1111 = 2147483647
		$s->put_uint32_be(~(1 << 31));
		$s->offset(0);
		eq(~(1 << 31),$s->get_uint32_be());
		$s->truncate();
		
		# uint32 le
		# 0000 0000 0000 0000 1000 0000 0000 0000 = 2147483648
		$s->put_uint32_le(1 << 31);
		$s->offset(0);
		eq(1 << 31,$s->get_uint32_le());
		$s->truncate();
		
		# 1111 1111 1111 1111 0111 1111 1111 1111 = 2147483647
		$s->put_uint32_le(~(1 << 31));
		$s->offset(0);
		eq(~(1 << 31),$s->get_uint32_le());
		$s->truncate();
	 */
}