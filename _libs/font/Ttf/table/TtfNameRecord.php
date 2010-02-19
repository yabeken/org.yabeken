<?php
class TtfNameRecord extends Object{
	static protected $__platform_id__ = "type=integer";
	static protected $__platform_specific_id__ = "type=integer";
	static protected $__language_id__ = "type=integer";
	static protected $__name_id__ = "type=integer";
	static protected $__length__ = "type=integer";
	static protected $__offset__ = "type=integer";
	static protected $__name__ = "type=string";
	protected $platform_id;
	protected $platform_specific_id;
	protected $language_id;
	protected $name_id;
	protected $length;
	protected $offset;
	protected $name;
	
	protected function __str__(){
		$result = "";
		$result.= pack("n",$this->platform_id());
		$result.= pack("n",$this->platform_specific_id());
		$result.= pack("n",$this->language_id());
		$result.= pack("n",$this->name_id());
		$result.= pack("n",$this->length());
		$result.= pack("n",$this->offset());
		$result.= $this->name();
		return $result;
	}
}
?>