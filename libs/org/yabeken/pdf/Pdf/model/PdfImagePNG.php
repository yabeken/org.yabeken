<?php
module("model.PdfImage");
class PdfImagePNG extends PdfImage {
	protected $palette;
	protected function __parse__(){
		$this->palette = new PdfObj();
		
		//signature
		$this->seek(8);
		while(true){
			$len = unpack('Ni',$this->read(4));
			$len = $len["i"];
			switch($this->read(4)){
				case "IHDR":
					//width
					$w = unpack('Ni',$this->read(4));
					$this->Width = $w["i"];
					
					//height
					$h = unpack('Ni',$this->read(4));
					$this->Height = $h["i"];
					
					//bit per component
					$this->BitsPerComponent = ord($this->read(1));
					
					//color space
					$cs = ord($this->read(1));
					switch($cs){
						case 0: //grayscale
							$this->ColorSpace = "/DeviceGray";
							break;
						case 2: //rgb
							$this->ColorSpace = "/DeviceRGB";
							break;
						case 3: //palette
							//TODO palette
							$this->ColorSpace = array("/Indexed","/DeviceRGB",$this->ref($this->palette));
							break;
						default:
							throw new Exception("alpha channel is not supported");
					}
					
					//compression
					$this->seek(1);
					//filter
					$this->seek(1);
					//interlace
					$this->seek(1);
					
					$this->DecodeParms = new PdfObj();
					$this->DecodeParms->dictionary("Predictor",15);
					$this->DecodeParms->dictionary("Colors",(in_array($cs,array(2,6)) ? 3 : 1));
					$this->DecodeParms->dictionary("BitsPerComponent",$this->BitsPerComponent);
					$this->DecodeParms->dictionary("Columns",$this->Width);
					
					break;
				case "PLTE": //palette
					$this->palette->stream($this->read($len));
					break;
				case "tRNS": // transparency
					$trns = $this->read($len);
					if($this->ColorSpace == "/DeviceGray"){
						$this->Mask = array(ord(substr($trns,1,1)),ord(substr($trns,1,1)));
					}else if($this->ColorSpace == "/DeviceRGB"){
						$this->Mask = array(ord(substr($t,1,1)),ord(substr($t,1,1)),ord(substr($t,3,1)),ord(substr($t,3,1)),ord(substr($t,5,1)),ord(substr($t,5,1)));
					}else{
						$pos = strpos(chr(0));
						if($pos !== false){
							$this->Mask = array($pos,$pos);
						}
					}
					break;
				case "IDAT": //image data
					$this->stream = $this->read($len);
					return;
				case "IEND":
					break(2);
				default:
					$this->seek($len);
					break;
			}
			//crc
			$this->seek(4);
		}
	}
}
?>