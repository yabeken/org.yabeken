<?php
class TtfCmap extends Object{
	static protected $__version__ = "type=integer";
	static protected $__number_of_subtables__ = "type=integer";
	static protected $__encoding__ = "type=TtfCmapEncoding[]";
	protected $version;
	protected $num_of_subtables;
	protected $encoding;
	/**
	 * 文字コードから字形IDを取得
	 *
	 * @param integer $char_code
	 * @return integer
	 */
	public function get_glyph_id($char_code){
		$glyph_id = 0;
		foreach($this->encoding as $encoding){
			if(strlen($char) == 1 && $encoding->format() == 0){
				$glyph_id = $encoding->inMapping($char);
				break;
			}
			$glyph_id = $encoding->inMapping($char);
			if($glyph_id != 0) break;
		}
		return $glyph_id;
	}
}