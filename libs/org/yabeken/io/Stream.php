<?php
/**
 * Stream
 * @author yabe
 */
abstract class Stream extends Object{
	static protected $__offset__ = "type=integer";
	static protected $__length__ = "type=integer,set=false";
	protected $offset;
	protected $length;
	protected $_resource_;
	const SEEK_CUR = SEEK_CUR;
	const SEEK_END = SEEK_END;
	const SEEK_SET = SEEK_SET;
	abstract public function open($resource,$mode=null);
	abstract public function close();
	abstract public function seek($len,$mode=null);
	abstract public function read($len=2048);
	abstract public function read_line($strict=false);
	abstract public function search($needle,$invert=false,$limit=null);
	abstract public function eof();
	abstract public function write($str);
	
	final public function read_int8(){
		list(,$r) = unpack("c",$this->read(1));
		return $r;
	}
	final public function read_uint8(){
		list(,$r) = unpack("C",$this->read(1));
		return $r;
	}
	final public function read_int16(){
		list(,$r) = unpack("s",$this->read(2));
		return $r;
	}
	final public function read_int16_be(){
		$r = $this->read_uint16_be();
		return $r < 0x8000 ? $r : $r - 0x10000;
	}
	final public function read_int16_le(){
		$r = $this->read_uint16_le();
		return $r < 0x8000 ? $r : $r - 0x10000;
	}
	final public function read_uint16(){
		list(,$r) = unpack("S",$this->read(2));
		return $r;
	}
	final public function read_uint16_be(){
		list(,$r) = unpack("n",$this->read(2));
		return $r;
	}
	final public function read_uint16_le(){
		list(,$r) = unpack("v",$this->read(2));
		return $r;
	}
	final public function read_int32(){
		list(,$r) = unpack("l",$this->read(4));
		return $r;
	}
	final public function read_uint32(){
		list(,$r) = unpack("L",$this->read(4));
		return $r;
	}
	final public function read_uint32_be(){
		list(,$r) = unpack("N",$this->read(4));
		return $r;
	}
	final public function read_uint32_le(){
		list(,$r) = unpack("V",$this->read(4));
		return $r;
	}
}