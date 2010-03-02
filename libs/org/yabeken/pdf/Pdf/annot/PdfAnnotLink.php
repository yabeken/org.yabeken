<?php
/**
 * Link Annotation
 * @author Kentaro YABE
 */
class PdfAnnotLink extends PdfAnnot{
	protected $Subtype = "/Link";
	protected $A;
	protected $Dest;
	protected $H;
	protected $PA;
//	protected $QuadPoints;
}