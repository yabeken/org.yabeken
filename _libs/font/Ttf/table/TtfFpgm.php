<?php
class TtfFpgm extends Object{
	static protected $__instruction__ = "type=integer[]";
	protected $instruction;
	static public function load(TtfParser $ttf){
		$cur_offset = $ttf->offset();
		list($offset,$length) = $ttf->inHeader("fpgm");
		$ttf->offset($offset);
		$table = new self();
		for($i=0;$i<$length;$i++){
			$table->instruction($ttf->read_uint8());
		}
		$ttf->offset($cur_offset);
		return $table;
	}
	
	protected function __str__(){
		$result = "";
		foreach($this->instruction as $value){
			$result .= pack("C",$value);
		}
		return $result;
	}
}
?>