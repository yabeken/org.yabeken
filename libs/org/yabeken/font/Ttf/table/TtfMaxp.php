<?php
class TtfMaxp extends Object{
	static protected $__version__ = "type=number";
	static protected $__num_glyphs__ = "type=integer";
	static protected $__max_points__ = "type=integer";
	static protected $__max_contours__ = "type=integer";
	static protected $__max_component_point__ = "type=integer";
	static protected $__max_component_contours__ = "type=integer";
	static protected $__max_zones__ = "type=integer";
	static protected $__max_twilight_points__ = "type=integer";
	static protected $__max_storage__ = "type=integer";
	static protected $__max_function_defs__ = "type=integer";
	static protected $__max_instruction_defs__ = "type=integer";
	static protected $__max_stack_elements__ = "type=integer";
	static protected $__max_size_of_instructions__ = "type=integer";
	static protected $__max_component_elements__ = "type=integer";
	static protected $__max_component_depth__ = "type=integer";
	protected $version = 1.0;
	protected $num_glyphs = 0;
	protected $max_points;
	protected $max_contours;
	protected $max_component_point;
	protected $max_component_contours;
	protected $max_zones = 2;
	protected $max_twilight_points;
	protected $max_storage;
	protected $max_function_defs;
	protected $max_instruction_defs;
	protected $max_stack_elements;
	protected $max_size_of_instructions;
	protected $max_component_elements;
	protected $max_component_depth;
}