<?php
//import("org.yabeken.pdf.Pdf.model.PdfFont");
import("org.yabeken.pdf.Pdf");
module("KozMinProDescriptor");
class KozMinProCID extends PdfFont {
	protected $Subtype = "/CIDFontType0";
	protected $BaseFont = "/KozMinPro-Regular-Acro";
	protected $DW = 1000;
	protected $W = array(
					1,array(
						' '=>278,'!'=>299,'"'=>353,'#'=>614,'$'=>614,'%'=>721,'&'=>735,'\''=>216,
						'('=>323,')'=>323,'*'=>449,'+'=>529,','=>219,'-'=>306,'.'=>219,'/'=>453,
						'0'=>614,'1'=>614,'2'=>614,'3'=>614,'4'=>614,'5'=>614,'6'=>614,'7'=>614,
						'8'=>614,'9'=>614,':'=>219,';'=>219,'<'=>529,'='=>529,'>'=>529,'?'=>486,
						'@'=>744,'A'=>646,'B'=>604,'C'=>617,'D'=>681,'E'=>567,'F'=>537,'G'=>647,
						'H'=>738,'I'=>320,'J'=>433,'K'=>637,'L'=>566,'M'=>904,'N'=>710,'O'=>716,
						'P'=>605,'Q'=>716,'R'=>623,'S'=>517,'T'=>601,'U'=>690,'V'=>668,'W'=>990,
						'X'=>681,'Y'=>634,'Z'=>578,'['=>316,'\\'=>614,']'=>316,'^'=>529,'_'=>500,
						'`'=>387,'a'=>509,'b'=>566,'c'=>478,'d'=>565,'e'=>503,'f'=>337,'g'=>549,
						'h'=>580,'i'=>275,'j'=>266,'k'=>544,'l'=>276,'m'=>854,'n'=>579,'o'=>550,
						'p'=>578,'q'=>566,'r'=>410,'s'=>444,'t'=>340,'u'=>575,'v'=>512,'w'=>760,
						'x'=>503,'y'=>529,'z'=>453,'{'=>326,'|'=>380,'}'=>326,'~'=>387
					),
					231,325,500,
					631,array(500),
					326,389,500
				);
	
	protected function __init__(){
		$this->CIDSystemInfo = new PdfObj();
		$this->CIDSystemInfo->dictionary(array("Registry"=>"Adobe","Ordering"=>"Japan1","Supplement"=>5));
		$this->FontDescriptor = $this->ref(new KozMinProDescriptor());
	}
	
	protected function __str_width__($str){
		if(isset($this->W[1][$str])){
			return $this->W[1][$str] / 1000;
		}
		if(strlen($str) === 1){
			$ord = ord($str);
			if(($ord >= 231 && $ord <= 325) || $ord == 631 || ($ord >= 326 && $ord < 389)){
				return 0.5;
			}
		}
		return parent::__str_width__($str);
	}
}

?>