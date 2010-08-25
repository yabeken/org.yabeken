<?php
module('StreamException');
/**
 * Stream
 * @author yabeken
 * @license New BSD License
 */
abstract class Stream extends Object{
	static protected $__offset__ = 'type=integer';
	static protected $__length__ = 'type=integer,set=false';
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
	abstract public function get_line($strict=false);
	abstract public function search($needle,$invert=false,$limit=null);
	abstract public function eof();
	abstract public function write($str);
	abstract public function is_opened();
	abstract public function is_closed();
	/**
	 * 8bit signed integer
	 * @return integer
	 */
	final public function get_int8(){
		return $this->read_unpack(1, 'c');
	}
	/**
	 * 8bit unsigned integer
	 * @return integer
	 */
	final public function get_uint8(){
		return $this->read_unpack(1, 'C');
	}
	/**
	 * 16bit signed integer by machine order
	 * @return integer
	 */
	final public function get_int16(){
		return $this->read_unpack(2, 's');
	}
	/**
	 * 16bit signed integer by big endian order
	 * @return integer
	 */
	final public function get_int16_be(){
		$r = $this->get_uint16_be();
		return $r < 0x8000 ? $r : $r - 0x10000;
	}
	/**
	 * 16bit signed integer by little endian order
	 * @return integer
	 */
	final public function get_int16_le(){
		$r = $this->get_uint16_le();
		return $r < 0x8000 ? $r : $r - 0x10000;
	}
	/**
	 * 16bit unsigned integer by machine order
	 * @return integer
	 */
	final public function get_uint16(){
		return $this->read_unpack(2, 'S');
	}
	/**
	 * 16bit unsigned integer by big endian order
	 * @return integer
	 */
	final public function get_uint16_be(){
		return $this->read_unpack(2, 'n');
	}
	/**
	 * 16bit unsigned integer by little endian order
	 * @return integer
	 */
	final public function get_uint16_le(){
		return $this->read_unpack(2, 'v');
	}
	/**
	 * 32bit signed integer by machine order
	 * @return integer
	 */
	final public function get_int32(){
		return $this->read_unpack(4, 'l');
	}
	/**
	 * 32bit unsigned integer by machine order
	 * @return integer
	 */
	final public function get_uint32(){
		return $this->read_unpack(4, 'L');
	}
	/**
	 * 32bit unsigned integer by big endian order
	 * @return integer
	 */
	final public function get_uint32_be(){
		return $this->read_unpack(4, 'N');
	}
	/**
	 * 32bit unsigned integer by little endian order
	 * @return integer
	 */
	final public function get_uint32_le(){
		return $this->read_unpack(4, 'V');
	}
	/**
	 * unpack
	 * @param integer $length
	 * @param string $format
	 */
	final protected function read_unpack($length,$format){
		list(,$r) = unpack($format,$this->read($length));
		return $r;
	}
}