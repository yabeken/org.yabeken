<?php
import('org.yabeken.io.stream.Stream');
/**
 * Bit Stream
 * @author yabeken
 * @license New BSD License
 */
class BitStream extends Stream{
	protected $_buf_;
	/**
	 * Constructor
	 * @param Stream $resource
	 * @throws InvalidArgumentException
	 */
	final protected function __new__(Stream $resource){
		if(!($resource instanceof Stream)) throw new InvalidArgumentException('Stream is required');
		$this->_resource_ = $resource;
		$this->clear_buffer();
		/***
			try{
				new BitStream();
				fail();
			}catch(Exception $e){
				success();
			}
		 */
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
		$offset = $this->_resource_->offset();
		return array($offset == 0 ? 0 : $offset - 1,$this->offset);
	}
	/**
	 * set offset
	 * @param integer $byte
	 * @param integer $bit
	 * @return $this
	 * @throws StreamException
	 */
	protected function __set_offset__($byte,$bit=0){
		//TODO
		if($bit < 0 || $bit >= 8) throw new StreamException('invalid bit offset {1}',$bit);
		$this->_resource_->offset($byte);
		$this->offset = $bit;
		return $this;
	}
	protected function __get_length__(){
		return $this->_resource_->length() + ($this->offset == 0 ? 0 : 1);
	}
	/**
	 * 開く
	 * @param mixed $resource
	 * @param string $mode
	 * @return $this
	 */
	public function open($resource,$mode=null){
		$this->_resource_->open($resource,$mode);
		$this->clear_buffer();
		return $this;
	}
	/**
	 * 閉じる
	 */
	public function close(){
		$this->truncate();
		if($this->_resource_ instanceof Stream) $this->_resource_->close();
	}
	public function seek($len,$mode=null){
		$this->clear_buffer();
		//TODO
		return $this->_resource_->seek($len,$mode);
	}
	public function search($needle,$invert=false,$limit=null){
		throw new StreamException('not implemented');
	}
	public function read($len=2048){
		//TODO
		$this->clear_buffer();
		return $this->_resource_->read($len);
	}
	public function read_line($strict=false){
		throw new StreamException('not implemented');
	}
	public function write($str){
		//TODO
		return $this->_resource_->write($str);
	}
	/**
	 * trucate
	 */
	public function truncate(){
		if($this->_resource_ instanceof Stream) $this->_resource_->truncate();
		$this->clear_buffer();
		return $this;
	}
	/**
	 * 終端か(non-PHPdoc)
	 * @return boolean
	 */
	public function is_eof(){
		return $this->_resource_->is_eof();
	}
	/**
	 * ハンドルが開かれているか
	 * @return boolean
	 */
	public function is_opened(){
		return $this->_resource_->is_opened();
	}
	/**
	 * ハンドルが閉じられているか
	 * @return boolean
	 */
	public function is_closed(){
		return $this->_resource_->is_closed();
	}
	/**
	 * 1 bit
	 * @return binary
	 */
	public function get_bit(){
		if($this->_buf_ === null) $this->_buf_ = ord($this->_resource_->read(1));
		if($this->_buf_ === null) return;
		$r = ($this->_buf_ >> (8 - ++$this->offset)) & 1;
		if($this->offset == 8) $this->clear_buffer();
		return (int)$r;
		/***
			$s = new BitStream(R('org.yabeken.io.stream.TextStream'));
			
			$s->put_int8(1 << 7 | 1 << 5 | 1 << 3 | 1 << 1)->offset(0);
			eq(1,$s->get_bit());
			eq(0,$s->get_bit());
			eq(1,$s->get_bit());
			eq(0,$s->get_bit());
			eq(1,$s->get_bit());
			eq(0,$s->get_bit());
			eq(1,$s->get_bit());
			eq(0,$s->get_bit());
		 */
	}
	/**
	 * read bits
	 * @param integer $length
	 * @return binary
	 */
	public function get_bits($length){
		$r = 0;
		for($i=0;$i<$length;$i++){
			$r = ($r << 1) | $this->get_bit();
		}
		return $r;
		/***
			$s = new BitStream(R('org.yabeken.io.stream.TextStream'));
			
			$s->put_int8(1 << 7)->offset(0);
			eq(1,$s->get_bit());
			eq(0,$s->get_bits(7));
			
			$s->truncate()->put_int8(1 << 7 | 1 << 5)->offset(0);
			eq(2,$s->get_bits(2));
			eq(8,$s->get_bits(4));
		 */
	}
	/**
	 * put a bit
	 * @param binary $value
	 */
	public function put_bit($value){
		$this->_buf_ = ($this->_buf_ === null ? 0 : $this->_buf_) | (($value & 1) << (8 - ++$this->offset));
		if($this->offset == 8){
			$this->write($this->_buf_);
			$this->clear_buffer();
		}
		return $this;
	}
	/**
	 * put bits
	 * @param integer $num
	 * @param binary $bits
	 */
	public function put_bits($num,$bits){
		for($i=$num-1;$i>=0;$i--){
			$this->put_bit(($bits >> $i) & 1);
		}
		return $this;
	}
	protected function clear_buffer(){
		$this->_buf_ = null;
		$this->offset = 0;
	}
}