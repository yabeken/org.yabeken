<?php
import('org.yabeken.io.stream.Stream');
/**
 * Bit単位のバイナリファイルの入出力ライブラリ
 * @author yabeken
 * @license New BSD License
 */
class BitIO extends Object{
	protected $_resource_;
	protected $_mode_;
	protected $_buf_;
	protected $_pos_ = 0;
	const MODE_READ = 1;
	const MODE_WRITE = 2;
	final protected function __new__(Stream $resource){
		$this->_resource_ = $resource;
	}
	final protected function __del__(){
		if(!empty($this->_buf_) && $this->_resource_->is_closed()) throw new StreamException('stream is already closed');
		//TODO
	}
	/**
	 * 1 bit
	 * @return bit
	 */
	public function get_bit(){
		return $this->get_bits(1);
	}
	/**
	 * read bits
	 * @param integer $length
	 * @return binary
	 */
	public function get_bits($length){
		$this->_mode_ == self::MODE_READ;
		if(!is_int($length) || $length < 0) throw new RuntimeException("read_bits: invalid argument");
		if($this->_buf_ === null){
			$this->_buf_ = $this->_resource_read(1);
			$this->_pos_ = 0;
		}
		$r = null;
		$mask = ((0xFF << $this->_pos_) & 0xFF) >> $this->_pos_;
		if($this->_pos_ + $num <= 8){
			$r = ($this->_buf & $mask) >> $num;
			$this->_pos_ += $num;
		}else{
			$r = ($this->_buf & $mask) << ($num - $this->_pos_);
			$this->_buf_ = null;
			$this->_pos_ = 0;
			$r &= $this->get_bits($num - 8);
		}
		if($this->_pos_ == 8){
			$this->_buf_ = null;
			$this->_pos_ = 0;
		}
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
		if($this->_mode_ == self::MODE_READ) throw new RuntimeException("TODO");
		$this->_mode_ == self::MODE_WRITE;
		if($this->_pos_ + $num <= 8){
//			$mask = 
		}else{
			
		}
	}
}