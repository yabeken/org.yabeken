<?php
class TtfHmtx extends Object{
	static protected $__advance_width__ = "type=integer[]";
	static protected $__left_side_bearing__ = "type=integer[]";
	static protected $__monospace_left_side_bearing__ = "type=integer[]";
	protected $advance_width;
	protected $left_side_bearing;
	protected $monospace_left_side_bearing;
	
	static public function load(TtfParser $ttf){
		$cur_offset = $ttf->offset();
		list($offset,) = $ttf->inHeader("hmtx");
		$ttf->offset($offset);
		$table = new self();
		for($i=0;$i<$ttf->inTable("hhea")->num_of_long_hor_metrics();$i++){
			$table->advance_width($ttf->read_uint16());
			$table->left_side_bearing($ttf->read_int16());
		}
		for($i=0;$i<$ttf->inTable("maxp")->num_glyphs() - $ttf->inTable("hhea")->num_of_long_hor_metrics();$i++){
			$table->monospace_left_side_bearing($ttf->read_fword());
		}
		$ttf->offset($cur_offset);
		return $table;
	}
}
?>