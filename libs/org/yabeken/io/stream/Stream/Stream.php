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
	abstract public function read_line($strict=false);
	abstract public function search($needle,$invert=false,$limit=null);
	abstract public function eof();
	abstract public function write($str);
	abstract public function truncate();
	abstract public function is_opened();
	abstract public function is_closed();
	/**
	 * get 8bit signed integer
	 * @return integer
	 */
	final public function get_int8(){
		return $this->read_unpack(1, 'c');
	}
	/**
	 * get 8bit unsigned integer
	 * @return integer
	 */
	final public function get_uint8(){
		return $this->read_unpack(1, 'C');
	}
	/**
	 * get 16bit signed integer by big endian order
	 * @return integer
	 */
	final public function get_int16_be(){
		$r = $this->get_uint16_be();
		return $r < 0x8000 ? $r : $r - 0x10000;
	}
	/**
	 * get 16bit signed integer by little endian order
	 * @return integer
	 */
	final public function get_int16_le(){
		$r = $this->get_uint16_le();
		return $r < 0x8000 ? $r : $r - 0x10000;
	}
	/**
	 * get 16bit unsigned integer by big endian order
	 * @return integer
	 */
	final public function get_uint16_be(){
		return $this->read_unpack(2, 'n');
	}
	/**
	 * get 16bit unsigned integer by little endian order
	 * @return integer
	 */
	final public function get_uint16_le(){
		return $this->read_unpack(2, 'v');
	}
	/**
	 * get 32bit signed integer by big endian order
	 * @return integer
	 */
	final public function get_int32_be(){
		$r = $this->get_uint32_be();
		return $r < 0x80000000 ? $r : ~($r-1);
	}
	/**
	 * get 32bit signed integer by big little order
	 * @return integer
	 */
	final public function get_int32_le(){
		
	}
	/**
	 * get 32bit unsigned integer by big endian order
	 * @return integer
	 */
	final public function get_uint32_be(){
		return $this->read_unpack(4, 'N');
	}
	/**
	 * get 32bit unsigned integer by little endian order
	 * @return integer
	 */
	final public function get_uint32_le(){
		return $this->read_unpack(4, 'V');
	}
	/**
	 * put value as 8bit signed integer
	 * @param integer $value
	 */
	final public function put_int8($value){
		$this->write_pack($value, 'c');
	}
	/**
	 * put value as 8bit unsigned integer
	 * @param integer $value
	 */
	final public function put_uint8($value){
		$this->write_pack($value, 'C');
	}
	/**
	 * put value as 16bit signed integer by big endian order
	 * @param integer $value
	 */
	final public function put_int16_be($value){
		$value &= 0xFFFF;
		$this->put_uint16_be(($value < 0) ? $value + 0x10000 : $value);
		
	}
	/**
	 * put value as 16bit signed integer by little endian order
	 * @param integer $value
	 */
	final public function put_int16_le($value){
		$value &= 0xFFFF;
		$this->put_uint16_le(($value < 0) ? $value + 0x10000 : $value);
	}
	/**
	 * put value as 16bit unsigned integer by big endian order
	 * @param integer $value
	 */
	final public function put_uint16_be($value){
		$this->write_pack($value, 'n');
	}
	/**
	 * put value as 16bit unsigned integer by little endian order
	 * @param integer $value
	 */
	final public function put_uint16_le($value){
		$this->write_pack($value, 'v');
	}
	/**
	 * put value as 32bit signed integer by big endian order
	 * @param integer $value
	 */
	final public function put_int32_be($value){
		$value &= 0xFFFFFFFF;
		$this->put_uint32_be(($value < 0) ? (~$value) + 1 : $value);
	}
	/**
	 * put value as 32bit signed integer by little endian order
	 * @param integer $value
	 */
	final public function put_int32_le($value){
		
	}
	/**
	 * put value as 32bit unsigned integer by big endian order
	 * @param integer $value
	 */
	final public function put_uint32_be($value){
		$this->write_pack($value, 'N');
	}
	/**
	 * put value as 32bit unsigned integer by little endian order
	 * @param integer $value
	 */
	final public function put_uint32_le($value){
		$this->write_pack($value, 'V');
	}
	/**
	 * read and unpack
	 * @param integer $length
	 * @param string $format
	 */
	final protected function read_unpack($length,$format){
		list(,$r) = unpack($format,$this->read($length));
		return $r;
	}
	/**
	 * pack and write
	 * @param mixed $value
	 * @param string $format
	 */
	final protected function write_pack($value,$format){
		$this->write(pack($format,$value));
	}
}