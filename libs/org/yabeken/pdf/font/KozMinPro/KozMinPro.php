<?php
import("org.yabeken.pdf.Pdf");
module("KozMinProCID");
class KozMinPro extends PdfFont {
	protected $Subtype = "/Type0";
	protected $BaseFont = "/KozMinPro-Regular-Acro-UniJIS-UTF16";
	protected $Encoding = "/UniJIS-UTF16-H";

	function __init__(){
		$this->DescendantFonts = array($this->ref(new KozMinProCID()));
	}
}
?>