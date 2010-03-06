<?php
/**
 * IPA Gothic CID Font
 * @author yabeken
 */
class IPAGothicCID extends PdfFont{
	protected $Subtype = "/CIDFontType2";
	protected $BaseFont = "/IPAGothic";
	protected $DW = 1000;
	protected $W = array(1,126,500,231,389,500,631,631,500);//TODO
	
	protected function __init__(){
		$this->FontDescriptor = $this->ref(new IPAGothicDescriptor());
		$this->CIDSystemInfo = new PdfObj();
		$this->CIDSystemInfo->dictionary("Ordering","Japan1");
		$this->CIDSystemInfo->dictionary("Registry","Adobe");
		$this->CIDSystemInfo->dictionary("Supplement",4);
	}
}