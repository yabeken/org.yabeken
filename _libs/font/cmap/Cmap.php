<?php
class Cmap extends Object{
	protected $short_cmap;
	protected $long_cmap;
	
	protected function __str__(){
		$buf = array();
		$buf[] = "/CIDInit/ProcSet findresource begin";
		$buf[] = "12 dict begin";
		$buf[] = "begincmap";
		$buf[] = "/CIDSystemInfo<<";
		$buf[] = "/Registory (Adobe)";
		$buf[] = "/Ordering (UCS)";
		$buf[] = "/Supplement 0";
		$buf[] = ">> def";
		$buf[] = "/CMapName/Adobe-Identity-UCS def";
		$buf[] = "/CMapType 2 def";
		$buf[] = "1 begeincodespacerange";//TODO
		$buf[] = "<00> <FF>";//TODO
		$buf[] = "endcodespacerange";
		$buf[] = "0 beginbfchar";
		$buf[] = "endbfchar";
		$buf[] = "endcmap";
		$buf[] = "CMapName currentdict /CMap defineresource pop";
		$buf[] = "end";
		$buf[] = "end";
		return implode("\n",$buf);
	}
}
?>