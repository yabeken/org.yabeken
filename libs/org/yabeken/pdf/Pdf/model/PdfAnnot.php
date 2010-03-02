<?php
/**
 * Pdf Annotation
 * @author yabeken
 *
 */
class PdfAnnot extends PdfObj{
	static protected $__F__ = "type=integer";
	protected $Type = "/Annot";
	protected $Subtype;
	protected $Rect;
	protected $Contents;
	protected $P;
	protected $NM;
	protected $M;
	protected $AP;
	protected $AS;
	protected $Border;
	protected $C;
	protected $StructParent;
	protected $OC;
	protected function __choices_F__(){
		return array(1,2,3,4,5,6,7,8,9,10);
	}
}