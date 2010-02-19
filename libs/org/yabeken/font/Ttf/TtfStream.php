<?php
import("org.yabeken.io.FileStream");
/**
 * 
 * @author yabe
 *
 */
class TtfStream extends FileStream{
	/**
	 * 分数 (16bits) を読み込む
	 *
	 * @return float
	 */
	final public function read_short_frac(){
		return $this->read_uint16() / 0x10000;
	}
	/**
	 * 分数 (16bits) を読み込む
	 *
	 * @return float
	 */
	final public function read_short_frac_be(){
		return $this->read_uint16_be() / 0x10000;
	}
	/**
	 * 分数 (16bits) を読み込む
	 *
	 * @return float
	 */
	final public function read_short_frac_le(){
		return $this->read_uint16_le() / 0x10000;
	}
	/**
	 * 分数 (16bits.16bits) を読み込む
	 *
	 * @return float
	 */
	final public function read_fixed(){
		return floatval($this->read_int16() + $this->read_short_frac());
	}
	/**
	 * 分数 (16bits.16bits) を読み込む
	 *
	 * @return float
	 */
	final public function read_fixed_be(){
		return floatval($this->read_int16_be() + $this->read_short_frac_be());
	}
	/**
	 * 分数 (16bits.16bits) を読み込む
	 *
	 * @return float
	 */
	final public function read_fixed_le(){
		return floatval($this->read_int16_le() + $this->read_short_frac_le());
	}
	/**
	 * 現在位置から分数 (2bits.14bits) を読み込む
	 *
	 * @return float
	 */
	final public function read_f2dot14(){
		//TODO 実装
		throw new Exception("not implemented");
	}
	/**
	 * 現在位置から long datetime (64bits) を読み込む
	 *
	 * @return integer
	 */
	final public function read_long_datetime(){
		return hexdec(bin2hex($this->read(8)));
	}
}