<?php
class TtfGlyf extends Object{
	static protected $__number_of_contours__ = "type=integer";
	static protected $__x_min__ = "type=integer";
	static protected $__y_min__ = "type=integer";
	static protected $__x_max__ = "type=integer";
	static protected $__y_max__ = "type=integer";
	static protected $__glyph__ = "type=mixed{}";
	
	protected $number_of_contours;
	protected $x_min;
	protected $y_min;
	protected $x_max;
	protected $y_max;
	protected $glyph;
	
	static public function load(TtfParser $ttf){
		$cur_offset = $ttf->offset();
		list($offset,) = $ttf->inHeader("glyf");
		$ttf->offset($offset);
		$table = new self();
		$table->number_of_contours($ttf->read_int16());
		$table->x_min($ttf->read_fword());
		$table->y_min($ttf->read_fword());
		$table->x_max($ttf->read_fword());
		$table->y_max($ttf->read_fword());
		
		for($i=0;$i<$ttf->inTable("maxp")->num_glyphs()-1;$i++){
			$glyph_offset = $ttf->inTable("loca")->inLocation($i);
			$ttf->offset($offset+$glyph_offset);
			$length = $ttf->inTable("loca")->inLocation($i+1,0) - $glyph_offset;
			$table->glyph($i,($length <= 0) ? $table->inGlyph(0) : $ttf->read($length));
		}
		$ttf->offset($cur_offset);
		return $table;
	}
}
?>