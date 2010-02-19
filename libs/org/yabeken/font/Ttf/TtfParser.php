<?php
module("TtfStream");
module("table.TtfHead");
module("table.TtfHhea");
module("table.TtfMaxp");
module("table.TtfPost");
module("table.TtfLoca");
module("table.TtfCmap");
module("table.TtfCmapEncoding");
//module("table.TtfGlyf");
//module("table.TtfPrep");
//module("table.TtfFpgm");
//module("table.TtfCvt");
//module("table.TtfHmtx");
//module("table.TtfName");

/**
 * TrueType フォントを解析する
 *
 */
class TtfParser extends Object{
	protected $_filename_;
	/**
	 * @var TtfStream
	 */
	protected $_file_;
	
	protected $version;
	protected $number_of_tables;
	protected $search_range;
	protected $entry_selector;
	protected $range_shift;
	
	protected $_header_;
	protected $_encoding_map_;
	
	//table
	/**
	 * @var TtfHead
	 */
	protected $_head_;
	/**
	 * @var TtfHhea
	 */
	protected $_hhea_;
	/**
	 * @var TtfMaxp
	 */
	protected $_maxp_;
	/**
	 * @var TtfPost
	 */
	protected $_post_;
	/**
	 * @var TtfLoca
	 */
	protected $_loca_;
	/**
	 * @var TtfCmap
	 */
	protected $_cmap_;
	
	protected function __new__($filename){
		$this->_filename_ = $filename;
		$this->_file_ = new TtfStream($filename,"rb");
		$this->_parse_();
		$this->_load_cache_();
	}
	protected function __del__(){
		$this->_save_cache_();
	}
	private function _load_cache_(){
//		if(Cache::exsist($filename)){
//			$this->_encoding_ = Cache::read($filename);
//		}
	}
	private function _save_cache_(){
//		Cache::write($this->_filename_,$this->_encoding_);
	}
	private function _parse_(){
		//parse headers
		$this->version = $this->_file_->read(4);
		$this->number_of_tables = $this->_file_->read_uint16_be();
		$this->search_range = $this->_file_->read_uint16_be();
		$this->entry_selector = $this->_file_->read_uint16_be();
		$this->range_shift = $this->_file_->read_uint16_be();
		
		//parse table offsets
		for($i=0;$i<$this->number_of_tables;$i++){
			$tag = trim($this->_file_->read(4));
			//skip check sum
			$this->_file_->seek(4);
			$this->_header_[$tag] = array($this->_file_->read_uint32_be(),$this->_file_->read_uint32_be());
		}
		$this->_parse_head_();
		$this->_parse_hhea_();
		$this->_parse_maxp_();
		$this->_parse_post_();
		$this->_parse_loca_();
		$this->_parse_cmap_();

//		$parser->table("cmap",$this->_file_->cmap(TtfCmap::load($parser)));
//		$parser->table("glyf",$this->_file_->glyf(TtfGlyf::load($parser)));
//		$parser->table("prep",$this->_file_->prep(TtfPrep::load($parser)));
//		$parser->table("fpgm",$this->_file_->fpgm(TtfFpgm::load($parser)));
//		$parser->table("cvt",$this->_file_->cvt(TtfCvt::load($parser)));
//		$parser->table("hmtx",$this->_file_->hmtx(TtfHmtx::load($parser)));
//		$parser->table("name",$this->_file_->name(TtfName::load($parser)));
//		return $this->_file_;
		return;
	}
	private function _parse_head_(){
		list($offset,) = $this->_header_["head"];
		$this->_file_->offset($offset);
		$this->_head_ = new TtfHead();
		$this->_head_->version(floatval($this->_file_->read_fixed_be()));
		$this->_head_->font_revision($this->_file_->read_fixed_be());
		$this->_head_->check_sum_adjustment($this->_file_->read_uint32_be());
		$this->_head_->magic_number($this->_file_->read_uint32_be());
		$this->_head_->flags($this->_file_->read_uint16_be());
		$this->_head_->units_per_em($this->_file_->read_uint16_be());
		$this->_head_->created($this->_file_->read_long_datetime());
		$this->_head_->modified($this->_file_->read_long_datetime());
		$this->_head_->x_min($this->_file_->read_int16_be());
		$this->_head_->y_min($this->_file_->read_int16_be());
		$this->_head_->x_max($this->_file_->read_int16_be());
		$this->_head_->y_max($this->_file_->read_int16_be());
		$this->_head_->mac_style($this->_file_->read_uint16_be());
		$this->_head_->lowest_rec_ppem($this->_file_->read_uint16_be());
		$this->_head_->font_direction_hint($this->_file_->read_uint16_be());
		$this->_head_->index_to_loc_format($this->_file_->read_uint16_be());
		$this->_head_->glyph_data_format($this->_file_->read_uint16_be());
	}
	private function _parse_hhea_(){
		list($offset,) = $this->_header_["hhea"];
		$this->_file_->offset($offset);
		$this->_hhea_ = new TtfHhea();
		$this->_hhea_->version($this->_file_->read_fixed_be());
		$this->_hhea_->ascent($this->_file_->read_int16_be());
		$this->_hhea_->descent($this->_file_->read_int16_be());
		$this->_hhea_->line_gap($this->_file_->read_int16_be());
		$this->_hhea_->advance_width_max($this->_file_->read_uint16_be());
		$this->_hhea_->min_left_side_bearing($this->_file_->read_int16_be());
		$this->_hhea_->min_right_side_bearing($this->_file_->read_int16_be());
		$this->_hhea_->x_max_extent($this->_file_->read_int16_be());
		$this->_hhea_->caret_slope_rise($this->_file_->read_int16_be());
		$this->_hhea_->caret_slope_run($this->_file_->read_int16_be());
		$this->_hhea_->caret_offset($this->_file_->read_int16_be());
		$this->_hhea_->reserved1($this->_file_->read_int16_be());
		$this->_hhea_->reserved2($this->_file_->read_int16_be());
		$this->_hhea_->reserved3($this->_file_->read_int16_be());
		$this->_hhea_->reserved4($this->_file_->read_int16_be());
		$this->_hhea_->metric_data_format($this->_file_->read_int16_be());
		$this->_hhea_->num_of_long_hor_metrics($this->_file_->read_uint16_be());
	}
	private function _parse_maxp_(){
		list($offset,) = $this->_header_["maxp"];
		$this->_file_->offset($offset);
		$this->_maxp_ = new TtfMaxp();
		$this->_maxp_->version($this->_file_->read_fixed_be());
		$this->_maxp_->num_glyphs($this->_file_->read_uint16_be());
		$this->_maxp_->max_points($this->_file_->read_uint16_be());
		$this->_maxp_->max_contours($this->_file_->read_uint16_be());
		$this->_maxp_->max_component_point($this->_file_->read_uint16_be());
		$this->_maxp_->max_component_contours($this->_file_->read_uint16_be());
		$this->_maxp_->max_zones($this->_file_->read_uint16_be());
		$this->_maxp_->max_twilight_points($this->_file_->read_uint16_be());
		$this->_maxp_->max_storage($this->_file_->read_uint16_be());
		$this->_maxp_->max_function_defs($this->_file_->read_uint16_be());
		$this->_maxp_->max_instruction_defs($this->_file_->read_uint16_be());
		$this->_maxp_->max_stack_elements($this->_file_->read_uint16_be());
		$this->_maxp_->max_size_of_instructions($this->_file_->read_uint16_be());
		$this->_maxp_->max_component_elements($this->_file_->read_uint16_be());
		$this->_maxp_->max_component_depth($this->_file_->read_uint16_be());
	}
	private function _parse_post_(){
		list($offset,) = $this->_header_["post"];
		$this->_file_->offset($offset);
		$this->_post_ = new TtfPost();

		$this->_post_->format($this->_file_->read_fixed_be());
		$this->_post_->italic_angle($this->_file_->read_fixed_be());
		$this->_post_->underline_position($this->_file_->read_int16_be());
		$this->_post_->underline_thickness($this->_file_->read_int16_be());
		$this->_post_->is_fixed_pitch($this->_file_->read_uint32_be());
		$this->_post_->min_mem_type42($this->_file_->read_uint32_be());
		$this->_post_->max_mem_type42($this->_file_->read_uint32_be());
		$this->_post_->min_mem_type1($this->_file_->read_uint32_be());
		$this->_post_->max_mem_type1($this->_file_->read_uint32_be());
	}
	private function _parse_loca_(){
		list($offset,) = $this->_header_["loca"];
		$this->_file_->offset($offset);
		$this->_loca_ = new TtfLoca();
		$this->get_location(0);
	}
	private function _parse_cmap_(){
		list($offset,) = $this->_header_["cmap"];
		$this->_file_->offset($offset);
		$this->_cmap_ = new TtfCmap();
		$this->_cmap_->version($this->_file_->read_uint16_be());
		$this->_cmap_->num_of_subtables($this->_file_->read_uint16_be());
		for($i=0;$i<$this->_cmap_->num_of_subtables();$i++){
			$this->_cmap_->encoding($this->_parse_cmap_subtables_($i));
		}
	}
	private function _parse_cmap_subtables_($table_no){
		list($offset,) = $this->_header_["cmap"];
		$this->_file_->offset($offset + 4 + $table_no * 8);
		$table = new TtfCmapEncoding();
		$table->platform_id($this->_file_->read_uint16_be());
		$table->platform_specific_id($this->_file_->read_uint16_be());
		$offset = $offset + $this->_file_->read_uint32_be();
		$this->_file_->offset($offset);
		$table->offset($offset);
		$table->format($this->_file_->read_uint16_be());
		$table->length($this->_file_->read_uint16_be());
		$table->language($this->_file_->read_uint16_be());
		return $table;
	}
	/**
	 * 字形IDに対応するオフセットを取得する
	 * @param integer $glyph_id
	 * @return integer
	 */
	public function get_location($glyph_id){
		if(!$this->_loca_->is_location($glyph_id)){
			list($offset,) = $this->_header_["loca"];
			if($this->_head_->index_to_loc_format() == TtfHead::SHORT_OFFSET){
				$this->_file_->offset($offset + $glyph_id * 2);
				$this->_loca_->location($glyph_id,$this->_file_->read_uint16_be() * 2);
			}else{
				$this->_file_->offset($offset + $glyph_id * 4);
				$this->_loca_->location($glyph_id,$this->_file_->read_uint32_be());
			}
		}
		return $this->_loca_->in_location($glyph_id);
	}
	/**
	 * 文字コードから字形IDを取得する
	 * @param integer $char_code
	 * @return integer
	 */
	public function get_glyph_id($char_code){
		if(!isset($this->_encoding_map_[$char_code])){
			foreach($this->_cmap_->encoding() as $encoding){
				$glyph_id = $this->_get_encoding_mapping_($encoding,$char_code);
				if($glyph_id != 0) break;
			}
			$this->_encoding_map_[$char_code] = $glyph_id;
		}
		return $this->_encoding_map_[$char_code];
	}
	private function _get_encoding_mapping_(TtfCmapEncoding $encoding,$char_code){
		$this->_file_->offset($encoding->offset()+6);
		if(strlen($char_code) == 1 && $encoding->format() == 0){
			$this->_file_->seek(ord($char_code));
			return $this->_file_->read_uint8();
		}else if($encoding->format() == 2){
			//TODO
		}else if($encoding->format() == 4){
//			$segCount = $ttf->read_uint16() / 2;
//			//search range
//			$ttf->seek(2);
//			//entry selector
//			$ttf->seek(2);
//			//range shift
//			$ttf->seek(2);
//			//end code
//			$endCode = array();
//			for($i=0;$i<$segCount;$i++){
//				$endCode[$i] = $ttf->read_uint16(); 
//			}
//			//reserved pad
//			$ttf->seek(2);
//			//start code
//			$startCode = array();
//			for($i=0;$i<$segCount;$i++){
//				$startCode[$i] = $ttf->read_uint16(); 
//			}
//			//id delta
//			$idDelta = array();
//			for($i=0;$i<$segCount;$i++){
//				$idDelta[$i] = $ttf->read_uint16();
//			}
//			//id range offset
//			$idRangeOffset = array();
//			for($i=0;$i<$segCount;$i++){
//				$idRangeOffset[$i] = $ttf->read_uint16(); 
//			}
//			for($i=0;$i<$segCount;$i++){
//				$start = $startCode[$i];
//				$end = $endCode[$i];
//				$delta = $idDelta[$i];
//				$rangeOffset = $idRangeOffset[$i];
//				for($j=$start;$j<=$end;$j++){
//					if($rangeOffset == 0){
//						$table->mapping($j,($j + $delta) % 0x10000);
//					}else{
//						//TODO
//					}
//				}
//			}
		}
	}
}