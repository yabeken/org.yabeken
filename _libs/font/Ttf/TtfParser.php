<?php
import("org.yabeken.io.FileStream");
module("table.TtfHead");
module("table.TtfHhea");
module("table.TtfMaxp");
module("table.TtfPost");
module("table.TtfLoca");
module("table.TtfCmap");
module("table.TtfGlyf");
module("table.TtfPrep");
module("table.TtfFpgm");
module("table.TtfCvt");
module("table.TtfHmtx");
module("table.TtfName");

/**
 * TrueType フォントを解析する
 *
 */
class TtfParser extends FileStream{
	static protected $__header__ = "type=mixed{}";
	static protected $__table__ = "type=mixed{}";
	protected $header = array();
	protected $table = array();
	/**
	 * 現在位置から unsigned int (8bits) を読み込む
	 *
	 * @return integer
	 */
	final public function read_uint8(){
		return hexdec(bin2hex($this->read(1)));
	}
	/**
	 * 現在位置から int (8bits) を読み込む
	 *
	 * @return integer
	 */
	final public function read_int8(){
		$uint8 = $this->read_uint8();
		return $uint8 < 0x80 ? $uint8 : $uint8 - 0x100;
	}
	/**
	 * 現在位置から unsigned int (16bits) を読み込む
	 *
	 * @return integer
	 */
	final public function read_uint16(){
		return hexdec(bin2hex($this->read(2)));
	}
	/**
	 * 現在位置から int (16bits) を読み込む
	 *
	 * @return integer
	 */
	final public function read_int16(){
		$uint16 = $this->read_uint16();
		return $uint16 < 0x8000 ? $uint16 : $uint16 - 0x10000;
	}
	/**
	 * 現在位置から unsigned int (32bits) を読み込む
	 *
	 * @return integer
	 */
	final public function read_uint32(){
		return hexdec(bin2hex($this->read(4)));
	}
	/**
	 * 現在位置から int (32bits) を読み込む
	 *
	 * @return integer
	 */
	final public function read_int32(){
		$uint32 = $this->read_uint32();
		return $uint32 < 0x80000000 ? $uint32 : $uint32 - 0x100000000;
	}
	/**
	 * 現在位置から分数 (16bits) を読み込む
	 *
	 * @return float
	 */
	final public function read_short_frac(){
		return $this->read_uint16() / 0x10000;
	}
	/**
	 * 現在位置から分数 (16bits.16bits) を読み込む
	 *
	 * @return float
	 */
	final public function read_fixed(){
		return floatval($this->read_int16() + $this->read_short_frac());
	}
	/**
	 * 現在位置から unsigned fword (16bits) を読み込む
	 *
	 * @return integer
	 */
	final public function read_ufword(){
		return $this->read_uint16();
	}
	/**
	 * 現在位置から fword (16bits) を読み込む
	 *
	 * @return integer
	 */
	final public function read_fword(){
		return $this->read_int16();
	}
	/**
	 * 現在位置から分数 (2bits.14bits) を読み込む
	 *
	 * @return float
	 */
	final public function read_f2dot14(){
		//TODO
//		$uint16 = $this->read(2);
	}
	/**
	 * 現在位置から long datetime (64bits) を読み込む
	 *
	 * @return integer
	 */
	final public function read_long_datetime(){
		return hexdec(bin2hex($this->read(8)));
	}
	/**
	 * 解析する
	 *
	 * @param string $filename
	 * @return Ttf
	 */
	static public function parse($filename){
		$parser = new self();
		$parser->resource($filename);
		
		$ttf = new Ttf();
		$ttf->version($parser->read(4));
		$ttf->number_of_tables($parser->read_uint16());
		$ttf->search_range($parser->read_uint16());
		$ttf->entry_selector($parser->read_uint16());
		$ttf->range_shift($parser->read_uint16());
		
		//table offsets
		for($i=0;$i<$ttf->number_of_tables();$i++){
			$tag = trim($parser->read(4));
			//check sum
			$parser->seek(4);
			$offset = hexdec(bin2hex($parser->read(4)));
			$length = hexdec(bin2hex($parser->read(4)));
			$parser->header($tag,array($offset,$length));
		}
		
		$parser->table("head",$ttf->head(TtfHead::load($parser)));
		$parser->table("hhea",$ttf->hhea(TtfHhea::load($parser)));
		$parser->table("maxp",$ttf->maxp(TtfMaxp::load($parser)));
		$parser->table("post",$ttf->post(TtfPost::load($parser)));
		$parser->table("loca",$ttf->loca(TtfLoca::load($parser)));
		$parser->table("cmap",$ttf->cmap(TtfCmap::load($parser)));
		$parser->table("glyf",$ttf->glyf(TtfGlyf::load($parser)));
		$parser->table("prep",$ttf->prep(TtfPrep::load($parser)));
		$parser->table("fpgm",$ttf->fpgm(TtfFpgm::load($parser)));
		$parser->table("cvt",$ttf->cvt(TtfCvt::load($parser)));
		$parser->table("hmtx",$ttf->hmtx(TtfHmtx::load($parser)));
		$parser->table("name",$ttf->name(TtfName::load($parser)));
		return $ttf;
	}
}
?>