<?php
module("PdfObj");
module("model.PdfPages");
class PdfCatalog extends PdfObj {
	static protected $__Pages__ = "type=PdfObj";
	static protected $__OpenAction__ = "type=mixed";
	
	public $Type = "/Catalog";
	public $Version;
	public $Pages;
	public $PageLabels;
	public $Names;
	public $Dests;
	public $ViewerPreferences;
	public $PageLayout;
	public $PageMode;
	public $Outlines;
	public $Threads;
	public $OpenAction;
	public $AA;
	public $URI;
	public $AcroForm;
	public $Metadata;
	public $StructTreeRoot;
	public $MarkInfo;
	public $Lang;
	public $SpiderInfo;
	public $OutputIntents;
	public $PieceInfo;
	public $OCProperies;
	public $Perms;
	public $Legal;
	
	protected function __init__(){
		$this->ref($this->Pages(new PdfPages()));
	}
}
?>