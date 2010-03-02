<?php
module("PdfObj");
/**
 * Page
 * @author Kentaro YABE
 */
class PdfPage extends PdfObj{
	static protected $__rotation__ = "type=choice(tate,yoko,portrait,landscape)";
	protected $rotation;
	
	static protected $__Type__ = "type=name";
	static protected $__Parent__ = "type=PdfPages";
	static protected $__Kids__ = "type=PdfPage[]";
	static protected $__Resources__ = "type=PdfResources";
	static protected $__Contents__ = "type=PdfObj";
	static protected $__MediaBox__ = "type=mixed";
	
	protected $Type = "/Page";
	protected $Parent;
	protected $LastModified;
	protected $Resources;
	protected $MediaBox;
	protected $CropBox;
	protected $BleedBox;
	protected $TrimBox;
	protected $ArtBox;
	protected $BoxColorInfo;
	protected $Contents;
	protected $Rotate;
	protected $Group;
	protected $Thumb;
	protected $B;
	protected $Dur;
	protected $Trans;
	protected $Annots;
	protected $AA;
	protected $Metadata;
	protected $PieceInfo;
	protected $StructParents;
	protected $ID;
	protected $PZ;
	protected $SeparationInfo;
	protected $Tabs;
	protected $TemplateInstantiated;
	
	protected function __init__(){
		$this->Contents = $this->ref(new PdfObj("stream=true"));
	}
	protected function __get_MediaBox__(){
		/***
			uc($pname,'
				protected $MediaBox = array(0,0,1,2);
			','PdfPage');
			$page = new $pname();
			eq(array(0,0,1,2),$page->MediaBox());
			$page->rotation("yoko");
			eq(array(0,0,2,1),$page->MediaBox());
		 */
		switch($this->rotation){
			default:
			case "tate":
			case "portrait":
				return $this->MediaBox;
			case "yoko":
			case "landscape":
				return array($this->MediaBox[0],$this->MediaBox[1],$this->MediaBox[3],$this->MediaBox[2]);
		}
	}
}