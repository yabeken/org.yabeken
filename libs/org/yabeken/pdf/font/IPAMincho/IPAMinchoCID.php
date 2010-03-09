<?php
/**
 * IPA Mincho CID Font
 * @author yabeken
 */
class IPAMinchoCID extends PdfFont{
	protected $Subtype = "/CIDFontType2";
	protected $BaseFont = "/IPAMincho";
	protected $DW = 1000;
	protected $W = array(1,127,500,231,389,500,631,631,500);//TODO
	
	protected function __init__(){
		$this->FontDescriptor = $this->ref(new IPAMinchoDescriptor());
		$this->CIDSystemInfo = new PdfObj();
		$this->CIDSystemInfo->dictionary("Registry","Adobe");
		$this->CIDSystemInfo->dictionary("Ordering","Japan1");
		$this->CIDSystemInfo->dictionary("Supplement",5);
	}
}