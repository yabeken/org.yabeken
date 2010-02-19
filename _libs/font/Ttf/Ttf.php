<?php
import("org.yabeken.io.TextStream");
module("TtfParser");
/**
 * @author Kentaro YABE
 * @license New BSD Lincese
 */
class Ttf extends Object{
	static protected $__version = "type=string";
	static protected $__number_of_tables__ = "type=integer";
	static protected $__search_range__ = "type=integer";
	static protected $__entry_selector__ = "type=integer";
	static protected $__range_shift__ = "type=integer";
	protected $version;
	protected $number_of_tables;
	protected $search_range;
	protected $entry_selector;
	protected $range_shift;
	
	static protected $__head__ = "type=TtfHead";
	static protected $__hhea__ = "type=TtfHhea";
	static protected $__maxp__ = "type=TtfMaxp";
	static protected $__post__ = "type=TtfPost";
	static protected $__loca__ = "type=TtfLoca";
	static protected $__cmap__ = "type=TtfCmap";
	static protected $__glyf__ = "type=TtfGlyf";
	static protected $__prep__ = "type=TtfPrep";
	static protected $__fpgm__ = "type=TtfFpgm";
	static protected $__cvt__ = "type=TtfCvt";
	static protected $__hmtx__ = "type=TtfHmtx";
	static protected $__name__ = "type=TtfName";
	protected $head;
	protected $hhea;
	protected $maxp;
	protected $post;
	protected $loca;
	protected $cmap;
	protected $glyf;
	protected $prep;
	protected $fpgm;
	protected $cvt;
	protected $hmtx;
	protected $name;
	
	protected $num_tables = 12;
	
	/**
	 * .ttf を解析する
	 *
	 * @param string $filename
	 * @return Ttf
	 */
	static public function parse($filename){
		if(Cache::exsist($filename,true)) return Cache::read($filename);
		$ttf = TtfParser::parse($filename);
		Cache::write($filename,$ttf);
		return $ttf;
	}
	
	protected function __str__(){
		$stream = new TextStream();
		//version
		$stream->write("true");
		//num talbes
		$stream->write(pack("n",$this->num_tables));
		//search range
		$search_range = pow(2,floor(log($this->num_tables,2)))*16;
		$stream->write(pack("n",$search_range));
		//entry selector
		$stream->write(pack("n",log($search_range,2)));
		//range shift
		$stream->write(pack("n",$this->num_tables*16 - $search_range));
		
		//TODO
		
		//prep
//		$stream->write($this->prep()->str());
		//cvt
//		$stream->write($this->cvt()->str());
		//fpgm
//		$stream->write($this->fpgm()->str());
		//post
//		$stream->write($this->post()->str());
		//name
//		$stream->write($this->name()->str());
		//hhea
		$stream->write($this->hhea()->str());
		return $stream->resource();
	}
}
?>