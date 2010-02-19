<?php
class TtfPrep extends Object{
	static protected $__control_value_program__ = "type=integer[]";
	protected $control_value_program;
	static public function load(TtfParser $ttf){
		$cur_offset = $ttf->offset();
		list($offset,$length) = $ttf->inHeader("prep");
		$ttf->offset($offset);
		$table = new self();
		for($i=0;$i<$length;$i++){
			$table->control_value_program($ttf->read_uint8());
		}
		$ttf->offset($cur_offset);
		return $table;
	}
	
	protected function __str__(){
		$result = "";
		foreach($this->control_value_program as $val){
			$result .= pack("C",$val);
		}
		return $result;
	}
}
?>