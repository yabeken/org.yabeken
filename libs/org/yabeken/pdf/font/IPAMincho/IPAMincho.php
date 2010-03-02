<?php
import("org.yabeken.pdf.Pdf");
module("IPAMinchoCID");
module("IPAMinchoDescriptor");
/**
 * IPA Mincho Font
 * @author yabeken
 */
class IPAMincho extends PdfFont{
	protected $Subtype = "/Type0";
	protected $BaseFont = "/IPAMincho";
	protected $Encoding = "/UniJIS-UTF16-H";
	protected function __init__(){
		$this->DescendantFonts = array($this->ref(new IPAMinchoCID()));
	}
}