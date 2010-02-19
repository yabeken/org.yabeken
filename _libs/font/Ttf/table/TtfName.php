<?php
module("table.TtfNameRecord");
class TtfName extends Object{
	static protected $__format__ = "type=integer";
	static protected $__count__ = "type=integer";
	static protected $__string_offset__ = "type=integer";
	static protected $__name_record__ = "type=NameRecord[]";
	protected $format;
	protected $count;
	protected $string_offset;
	protected $name_record;
	protected $name;
	
	static public function load(TtfParser $ttf){
		$cur_offset = $ttf->offset();
		list($offset,) = $ttf->inHeader("name");
		$ttf->offset($offset);
		$table = new self();
		$table->format($ttf->read_uint16());
		$table->count($ttf->read_uint16());
		$table->string_offset($ttf->read_uint16());
		for($i=0;$i<$table->count();$i++){
			$rec = new TtfNameRecord();
			$rec->platform_id($ttf->read_uint16());
			$rec->platform_specific_id($ttf->read_uint16());
			$rec->language_id($ttf->read_uint16());
			$rec->name_id($ttf->read_uint16());
			$rec->length($ttf->read_uint16());
			$rec->offset($ttf->read_uint16());
			$rec_offset = $ttf->offset();
			$ttf->offset($offset+$table->string_offset()+$rec->offset());
			$rec->name($ttf->read($rec->length()));
			$ttf->offset($rec_offset);
			$table->name_record($rec);
		}
		$ttf->offset($cur_offset);
		return $table;
	}
	protected function __str__(){
		$result = "";
		$result.= pack("n",$this->format());
		$result.= pack("n",$this->count());
		$result.= pack("n",$this->string_offset());
		foreach($this->name_record as $rec){
			$result.= $rec->str();
		}
		return $result;
	}
}
?>