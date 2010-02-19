<?php
class TtfCvt extends Object{
	static protected $__control_value__ = "type=integer[]";
	protected $control_value;
	static public function load(TtfParser $ttf){
		$cur_offset = $ttf->offset();
		list($offset,$length) = $ttf->inHeader("cvt");
		$ttf->offset($offset);
		$table = new self();
		for($i=0;$i<$length;$i+=2){
			$table->control_value($ttf->read_fword());
		}
		$ttf->offset($cur_offset);
		return $table;
	}
	
	protected function __str__(){
		$result = "";
		foreach($this->control_value as $value){
			$result .= pack("s",$value);
		}
		return $result;
	}
}
?>