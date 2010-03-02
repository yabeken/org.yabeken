<?php
import("org.yabeken.pdf.Pdf");
module("IPAGothicCID");
module("IPAGothicDescriptor");
/**
 * IPA Gothic Font
 * @author yabeken
 */
class IPAGothic extends PdfFont{
	protected $Subtype = "/Type0";
	protected $BaseFont = "/IPAGothic";
	protected $Encoding = "/UniJIS-UTF16-H";
	protected function __init__(){
		$this->DescendantFonts = array($this->ref(new IPAGothicCID()));
	}
}