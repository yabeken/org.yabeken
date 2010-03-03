<?php
/**
 * Catalog
 * @author yabeken
 */
class PdfCatalog extends PdfObj{
	static protected $__Type__ = "type=name";
	static protected $__Pages__ = "type=PdfPages";
	
	protected $Type = "/Catalog";
	protected $Version;
	protected $Pages;
	protected $PageLabels;
	protected $Names;
	protected $Dests;
	protected $ViewerPreferences;
	protected $PageLayout;
	protected $PageMode;
	protected $Outlines;
	protected $Threads;
	protected $OpenAction;
	protected $AA;
	protected $URI;
	protected $AcroForm;
	protected $Metadata;
	protected $StructTreeRoot;
	protected $MarkInfo;
	protected $Lang;
	protected $SpiderInfo;
	protected $OutputIntents;
	protected $PieceInfo;
	protected $OCProperies;
	protected $Perms;
	protected $Legal;
	
	protected function __init__(){
		$this->ref($this->Pages(new PdfPages()));
	}
}