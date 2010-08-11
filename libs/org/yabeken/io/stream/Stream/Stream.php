<?php
module('StreamException');
/**
 * Stream Abstract Class
 * @author yabeken
 * @license New BSD License
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
	abstract public function is_opened();
	abstract public function is_closed();
	/**
	 * read 8bit signed integer
	 * @return integer
	 */
	final public function read_int8(){
		return $this->read_binary(1, 'c');
	}
	/**
	 * read 8bit unsigned integer
	 * @return integer
	 */
	final public function read_uint8(){
		return $this->read_binary(1, 'C');
	}
	/**
	 * read 16bit signed integer by machine order
	 * @return integer
	 */
	final public function read_int16(){
		return $this->read_binary(2, 's');
	}
	/**
	 * read 16bit signed integer by big endian order
	 * @return integer
	 */
	final public function read_int16_be(){
		$r = $this->read_uint16_be();
		return $r < 0x8000 ? $r : $r - 0x10000;
	}
	/**
	 * read 16bit signed integer by little endian order
	 * @return integer
	 */
	final public function read_int16_le(){
		$r = $this->read_uint16_le();
		return $r < 0x8000 ? $r : $r - 0x10000;
	}
	/**
	 * read 16bit unsigned integer by machine order
	 * @return integer
	 */
	final public function read_uint16(){
		return $this->read_binary(2, 'S');
	}
	/**
	 * read 16bit unsigned integer by big endian order
	 * @return integer
	 */
	final public function read_uint16_be(){
		return $this->read_binary(2, 'n');
	}
	/**
	 * read 16bit unsigned integer by little endian order
	 * @return integer
	 */
	final public function read_uint16_le(){
		return $this->read_binary(2, 'v');
	}
	/**
	 * read 32bit signed integer by machine order
	 * @return integer
	 */
	final public function read_int32(){
		list(,$r) = unpack("l",$this->read(4));
		return $r;
	}
	/**
	 * read 32bit unsigned integer by machine order
	 * @return integer
	 */
	final public function read_uint32(){
		return $this->read_binary(4, 'L');
	}
	/**
	 * read 32bit unsigned integer by big endian order
	 * @return integer
	 */
	final public function read_uint32_be(){
		return $this->read_binary(4, 'N');
	}
	/**
	 * read 32bit unsigned integer by little endian order
	 * @return integer
	 */
	final public function read_uint32_le(){
		return $this->read_binary(4, 'V');
	}
	/**
	 * バイナリとして読み込む
	 * @param integer $length
	 * @param string $format
	 */
	final private function read_binary($length,$format){
		list(,$r) = unpack($format,$this->read($length));
		return $r;
	}
}