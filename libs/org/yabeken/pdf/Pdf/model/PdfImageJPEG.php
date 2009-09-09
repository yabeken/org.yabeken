<?php
module("model.PdfImage");
class PdfImageJPEG extends PdfImage {
	protected function __parse__(){
		//SOI
		$this->seek(2);
		while(true){
			if($this->read(1)!="\xFF"){
				throw new Exception("parse failed");
			}
			if($this->read(1) == "\xC0"){
				//Lf
				$this->seek(2);
				//bits
				$this->BitsPerComponent = hexdec(bin2hex($this->read(1)));
				//width
				$this->Width = hexdec(bin2hex($this->read(2)));
				//height
				$this->Height = hexdec(bin2hex($this->read(2)));
				//Nif
				switch(hexdec(bin2hex($this->read(1)))){
					case 3:
						$this->ColorSpace = "/DeviceRGB";
						break;
					case 4:
						$this->ColorSpace = "/DeviceCMYK";
						$this->Decode = array(1,0,1,0,1,0,1,0);
						break;
					default:
						$this->ColorSpace = "/DeviceGray";
						break;
				}
				return;
			}
			//seek
			$this->seek(hexdec(bin2hex($this->read(2)))-2);
		}
	}
	protected function __filter__($str){
		$this->Filter = "/DCTDecode";
		return $str;
	}
}
?>