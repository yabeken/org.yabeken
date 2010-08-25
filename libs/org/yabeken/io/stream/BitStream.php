<?php
import('org.yabeken.io.stream.Stream');
/**
 * Bit単位のバイナリファイルの入出力ライブラリ
 * @author yabeken
 * @license New BSD License
 */
class BitStream extends Stream{
	protected $_buf_;
	/**
	 * Constructor
	 * @param Stream $resource
	 */
	final protected function __new__(Stream $resource){
		$this->_resource_ = $resource;
	}
	/**
	 * Destructor
	 */
	final protected function __del__(){
		$this->close();
	}
	/**
	 * get offset
	 * @return array(byte-offset, bit-offset)
	 */
	protected function __get_offset__(){
		return array($this->_resource_->offset(),$this->offset);
	}
	/**
	 * set offset
	 * @param integer $byte
	 * @param integer $bit
	 * @throws StreamException
	 */
	protected function __set_offset__($byte,$bit=0){
		if($bit < 0 || $bit > 8) throw new StreamException('invalid bit offset {1}',$bit);
		$this->_resource_->offset($byte);
		$this->offset = $bit;
	}
	protected function __get_length__(){
		return $this->_resource_->length();
	}
	public function open($resource,$mode=null){
		return $this->_resource_->open($resource,$mode);
	}
	public public function close(){
		return $this->_resource_->close();
	}
	public function seek($len,$mode=null){
		$this->offset = 0;
		$this->_buf_ = null;
		return $this->_resource_->seek($len,$mode);
	}
	public function read($len=2048){
		$this->offset = 0;
		$this->_buf_ = null;
		return $this->_resource_->read($len);
	}
	public function read_line($strict=false){
		throw new StreamException('not implemented');
	}
	public function search($needle,$invert=false,$limit=null){
		throw new StreamException('not implemented');
	}
	public function eof(){
		return $this->_resource_->eof();
	}
	public function write($str){
		return $this->_resource_->write($str);
	}
	public function is_opened(){
		return $this->_resource_->is_opened();
	}
	public function is_closed(){
		return $this->_resource_->is_closed();
	}
	/**
	 * 1 bit
	 * @return binary
	 */
	public function get_bit(){
		return $this->get_bits(1);
	}
	/**
	 * read bits
	 * @param integer $length
	 * @return binary
	 * @throws InvalidArgumentException
	 */
	public function get_bits($length){
		if(!is_int($length) || $length < 0) throw new InvalidArgumentException('invalid argument');
		if($this->_buf_ === null){
			$this->_buf_ = $this->_resource_->read(1);
		}
		$r = null;
		$mask = ((0xFF << $this->_pos_) & 0xFF) >> $this->_pos_;
		return $r;
	}
	/**
	 * put a bit
	 * @param binary $bit
	 */
	public function put_bit($bit){
		$this->put_bits(1,$bit);
	}
	/**
	 * put bits
	 * @param integer $num
	 * @param binary $bits
	 */
	public function put_bits($num,$bits){
		if($this->_pos_ + $num <= 8){
//			$mask = 
		}else{
			
		}
	}
}