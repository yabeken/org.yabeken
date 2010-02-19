<?php
/**
 * TrueType Font head table
 * @author Kentaro YABE
 */
class TtfHead extends Object{
	const SHORT_OFFSET = 0;
	const LONG_OFFSET = 1;
	
	static protected $__version__ = "type=number";
	static protected $__font_revision__ = "type=number";
	static protected $__check_sum_adjustment__ = "type=integer";
	static protected $__magic_number__ = "type=integer";
	static protected $__flags__ = "type=integer";
	static protected $__units_per_em__ = "type=integer";
	static protected $__created__ = "type=integer";
	static protected $__modified__ = "type=integer";
	static protected $__x_min__ = "type=integer";
	static protected $__x_max__ = "type=integer";
	static protected $__y_min__ = "type=integer";
	static protected $__y_max__ = "type=integer";
	static protected $__mac_style__ = "type=integer";
	static protected $__lowest_rec_ppem__ = "type=integer";
	static protected $__font_direction_hint__ = "type=integer";
	static protected $__index_to_loc_format__ = "type=integer";
	static protected $__glyph_data_format__ = "type=integer";
	protected $version;
	protected $font_revision;
	protected $check_sum_adjustment;
	protected $magic_number;
	protected $flags;
	protected $units_per_em;
	protected $created;
	protected $modified;
	protected $x_min;
	protected $y_min;
	protected $x_max;
	protected $y_max;
	protected $mac_style;
	protected $lowest_rec_ppem;
	protected $font_direction_hint;
	protected $index_to_loc_format;
	protected $glyph_data_format;
}