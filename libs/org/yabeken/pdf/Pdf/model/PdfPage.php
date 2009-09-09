<?php
module("PdfObj");
class PdfPage extends PdfObj {
	static protected $__Kids__ = "type=PdfPage[]";
	static protected $__Resources__ = "type=PdfResources";
	static protected $__Contents__ = "type=PdfObj";
	static protected $__MediaBox__ = "type=numeric[]";
	static protected $__Parent__ = "type=PdfPages";
	
	/* Common */
	protected $Type = "/Page";
	protected $Parent;
	
	/* Page */
	protected $LastModified;
	protected $Resources;
	protected $MediaBox = array(0,0,595.28,841.89);
	protected $CropBox;
	protected $BleedBox;
	protected $TrimBox;
	protected $ArtBox;
	protected $BoxColorInfo;
	
	/**
	 * Contents
	 *
	 * @var PdfContents
	 */
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
		$this->Contents = $this->ref(new PdfObj());
	}
}
?>