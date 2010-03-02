<?php
import("org.yabeken.pdf.Pdf");
module("CourierBold");
module("CourierItalic");
module("CourierBoldItalic");
/**
 * Courier
 * @author yabeken
 */
class Courier extends PdfFont{
	protected $Subtype = "/Type1";
	protected $BaseFont = "/Courier";
	protected $Encoding = "/WinAnsiEncoding";
	protected $DW = 600;
}