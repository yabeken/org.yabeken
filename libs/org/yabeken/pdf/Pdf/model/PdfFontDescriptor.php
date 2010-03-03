<?php
/**
 * Font Descriptor
 * @author yabeken
 */
class PdfFontDescriptor extends PdfObj {
	protected $Type = "/FontDescriptor";
	protected $FontName;
	protected $FontFamily;
	protected $FontStretch;
	protected $FontWeight;
	protected $Flags;
	protected $FontBBox;
	protected $ItalicAngle;
	protected $Ascent;
	protected $Descent;
	protected $Leading;
	protected $CapHeight;
	protected $XHeight;
	protected $StemV;
	protected $StemH;
	protected $AvgWidth;
	protected $MaxWidth;
	protected $MissingWidth;
	protected $FontFile;
	protected $FontFile2;
	protected $FontFile3;
	protected $CharSet;
}