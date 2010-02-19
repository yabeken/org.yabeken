<?php
class TtfPost extends Object{
	static protected $__format__ = "type=number";
	static protected $__italic_angle__ = "type=number";
	static protected $__underline_position__ = "type=integer";
	static protected $__underline_thickness__ = "type=integer";
	static protected $__is_fixed_pitch__ = "type=integer";
	static protected $__min_mem_type42__ = "type=integer";
	static protected $__max_mem_type42__ = "type=integer";
	static protected $__min_mem_type1__ = "type=integer";
	static protected $__max_mem_type1__ = "type=integer";

	protected $format = 0;
	protected $italic_angle = 0;
	protected $underline_position = 0;
	protected $underline_thickness = 0;
	protected $is_fixed_pitch = 0;
	protected $min_mem_type42 = 0;
	protected $max_mem_type42 = 0;
	protected $min_mem_type1 = 0;
	protected $max_mem_type1 = 0;
	
	protected function __str__(){
		$result = "";
		$result .= pack("n",intval($this->format())).pack("n",intval(($this->format() % 1)*0x10000));
		$result .= pack("n",intval($this->italic_angle())).pack("n",intval(($this->italic_angle() % 1)*0x10000));
		$result .= pack("n",intval($this->underline_position()));
		$result .= pack("n",intval($this->underline_thickness()));
		$result .= pack("n",intval($this->is_fixed_pitch()));
		$result .= pack("n",intval($this->min_mem_type42()));
		$result .= pack("n",intval($this->max_mem_type42()));
		$result .= pack("n",intval($this->min_mem_type1()));
		$result .= pack("n",intval($this->max_mem_type1()));
		return $result;
	}
}
?>