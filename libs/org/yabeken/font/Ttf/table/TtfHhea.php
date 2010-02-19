<?php
/**
 * TrueType Font hhea table
 * @author Kentaro YABE
 */
class TtfHhea extends Object{
	static protected $__version__ = "type=number";
	static protected $__ascent__ = "type=integer";
	static protected $__descent__ = "type=integer";
	static protected $__line_gap__ = "type=integer";
	static protected $__advance_width_max__ = "type=integer";
	static protected $__min_left_side_bearing__ = "type=integer";
	static protected $__min_right_side_bearing__ = "type=integer";
	static protected $__x_max_extent__ = "type=integer";
	static protected $__caret_slope_rise__ = "type=integer";
	static protected $__caret_slope_run__ = "type=integer";
	static protected $__caret_offset__ = "type=integer";
	static protected $__reserved1__ = "type=integer";
	static protected $__reserved2__ = "type=integer";
	static protected $__reserved3__ = "type=integer";
	static protected $__reserved4__ = "type=integer";
	static protected $__metric_data_format__ = "type=integer";
	static protected $__num_of_long_hor_metrics__ = "type=integer";
	
	protected $version;
	protected $ascent;
	protected $descent;
	protected $line_gap;
	protected $advance_width_max;
	protected $min_left_side_bearing;
	protected $min_right_side_bearing;
	protected $x_max_extent;
	protected $caret_slope_rise;
	protected $caret_slope_run;
	protected $caret_offset;
	protected $reserved1;
	protected $reserved2;
	protected $reserved3;
	protected $reserved4;
	protected $metric_data_format;
	protected $num_of_long_hor_metrics;
	
	static public function load(TtfParser $ttf){
		$cur_offset = $ttf->offset();
		list($offset,) = $ttf->inTable("hhea");
		$ttf->offset($offset);
		$table = new self();
		$table->version($ttf->read_fixed());
		$table->ascent($ttf->read_int16_be());
		$table->descent($ttf->read_int16_be());
		$table->line_gap($ttf->read_int16_be());
		$table->advance_width_max($ttf->read_uint16_be());
		$table->min_left_side_bearing($ttf->read_int16_be());
		$table->min_right_side_bearing($ttf->read_int16_be());
		$table->x_max_extent($ttf->read_int16_be());
		$table->caret_slope_rise($ttf->read_int16_be());
		$table->caret_slope_run($ttf->read_int16_be());
		$table->caret_offset($ttf->read_int16_be());
		$table->reserved1($ttf->read_int16_be());
		$table->reserved2($ttf->read_int16_be());
		$table->reserved3($ttf->read_int16_be());
		$table->reserved4($ttf->read_int16_be());
		$table->metric_data_format($ttf->read_int16());
		$table->num_of_long_hor_metrics($ttf->read_uint16());
		$ttf->offset($cur_offset);
		return $table;
	}
}
?>