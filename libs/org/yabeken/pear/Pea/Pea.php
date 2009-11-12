<?php
require_once(dirname(__FILE__)."/__funcs__.php");

/**
 * PEAR ライブラリ制御
 * @author Kentaro YABE
 * @license New BSD License
 */
class Pea{
	static private $PEAR_PATH;
	static private $IMPORTED = array();
	static private $CHANNEL = array();
	static private $INSTALL = array();
	static private $PREFFERED_STATE = 0;
	static private $DEPENDENCY = false;
	static private $OPTIONAL = false;
	static private $states = array("stable"=>0,"beta"=>1,"alpha"=>2,"devel"=>3);
	static private $prepared = false;
	
	/**
	 * PEARの準備準備
	 * Pea::r なんちって
	 */
	static private function r(){
		if(self::$prepared) return;
		if(!File::exist(File::path(self::pear_path(),"PEAR.php"))){
			self::install("pear.php.net/PEAR");
		}
		require_once(File::path(self::pear_path(),"PEAR.php"));
		require_once(File::path(self::pear_path(),"PEAR5.php"));
		set_include_path(self::pear_path());
		self::$prepared = true;
	}
	/**
	 * PEARパスを設定する
	 * @param string $pear_path
	 */
	static public function config_path($pear_path){
		if(isset($pear_path)) self::$PEAR_PATH = $pear_path;
	}
	/**
	 * PEARパスを返す
	 * @return string
	 */
	static public function pear_path(){
		if(!isset(self::$PEAR_PATH)) self::$PEAR_PATH = File::path(Lib::vendors_path(),"pear");
		return self::$PEAR_PATH;
	}
	/**
	 * PEAR ライブラリを読み込む
	 * @param string $package_path
	 * @return string
	 */
	static public function import($package_path){
		self::r();
		list($domain,$package_name,$package_version) = self::parse_package($package_path);
		if(isset(self::$IMPORTED[strtolower($domain."/".$package_name)])) return self::$IMPORTED[strtolower($domain."/".$package_name)];
		self::install($package_path);
	}
	/**
	 * インストール
	 * @param string $package_path
	 * @param boolean $force
	 * @return boolean
	 */
	static protected function install($package_path,$force=false){
		list($domain,$package_name,$package_version) = self::parse_package($package_path);
		if(isset(self::$INSTALL[strtolower($domain."/".$package_name)])) return;
		if(!isset(self::$CHANNEL[$domain])) self::channel_discover($domain);
		
		$allreleases_xml = self::$CHANNEL[$domain]."/r/".strtolower($package_name)."/allreleases.xml";
		if(!Tag::setof($a,R(Http)->do_get($allreleases_xml)->body(),"a")) throw new RuntimeException($package_path." not found");
		$target_package = $a->f("p.value()");
		$target_version = null;
		$target_state = self::$PREFFERED_STATE;
		if(isset(self::$states[$package_version]) && self::$states[$package_version] > $target_state){
			$target_state = self::$states[$package_version];
			$package_version = null;
		}
		foreach($a->in("r") as $r){
			$v = $r->f("v.value()");
			if(!empty($package_version)){
				if($package_version == $v) $target_version = $v;
			}else if($force){
				$target_version = $v;
			}else{
				$s = $r->f("s.value()");
				if(isset(self::$states[$s]) && self::$states[$s] <= $target_state){
					$target_version = $v;
				}
			}
			if(!empty($target_version)) break;
		}
		if(empty($target_version)) throw new RuntimeException($package_path." not found");
		
		$download_path = File::path(App::work("pear"),strtr($domain,".","_")."_".$target_package."_".strtr($target_version,".","_"));
		$download_url = "http://".$domain."/get/".$target_package."-".$target_version.".tgz";
		if(!File::exist($download_path)){
			File::untgz($download_url,$download_path);
		}
		$package_xml = File::exist(File::path($download_path,"package2.xml")) ? File::path($download_path,"package2.xml") : File::path($download_path,"package.xml");
		self::$INSTALL[strtolower($domain."/".$target_package)] = $package_xml;
		if(Tag::setof($package,File::read($package_xml),"package")){
			switch($package->inParam("version")){
				case "1.0":
					if(self::$DEPENDENCY){
						foreach($package->f("deps.in(dep)") as $dep){
							if($dep->inParam("type")=="pkg"){
								if(self::$OPTIONAL || $dep->inParam("optional") == "no") self::install($dep->value(),true);
							}
						}
					}
					foreach($package->f("release.filelist.in(file)") as $file){
						if($file->inParam("role") != "php") continue;
						$baseinstalldir = $file->inParam("baseinstalldir");
						$name = $file->inParam("name");
						$src = File::path($download_path,File::path($target_package."-".$target_version,$name));
						$dst = File::path($baseinstalldir,$name);
						File::copy($src,$dst);
					}
					break;
				case "2.0":
					if(self::$DEPENDENCY){
						foreach($package->f("dependencies.required.in(package)") as $dep){
							self::install($dep->f("channel.value()")."/".$dep->f("name.value()"),true);
						}
						if(self::$OPTIONAL){
							foreach($package->f("dependencies.optional.in(package)") as $dep){
								self::install($dep->f("channel.value()")."/".$dep->f("name.value()"),true);
							}
						}
					}
					foreach($package->f("contents.in(dir)") as $dir){
						$baseinstalldir = $dir->inParam("baseinstalldir","/");
						foreach($dir->in("file") as $file){
							if($file->inParam("role") != "php") continue;
							$baseinstalldir = File::path(self::pear_path(),$file->inParam("baseinstalldir",$baseinstalldir));
							$name = $file->inParam("name");
							$src = File::path($download_path,File::path($target_package."-".$target_version,$name));
							$dst = File::path($baseinstalldir,$name);
							File::copy($src,$dst);
						}
					}
					break;
				default:
					throw new Exception("unknown package version");
			}
		}
		unset(self::$INSTALL[strtolower($domain."/".$target_package)]);
	}
	static protected function parse_package($package_path){
		list($domain,$name) = (strpos($package_path,"/")===false) ? array("pear.php.net",$package_path) : explode("/",$package_path,2);
		list($name,$version) = (strpos($name,"-")===false) ? array($name,null) : explode("-",$name,2);
		return array($domain,$name,$version);
	}
	static protected function channel_discover($domain){
		if(Tag::setof($channel,R(Http)->do_get("http://{$domain}/channel.xml")->body())){
			$url = $channel->f("rest.baseurl[0].value()");
			if(!empty($url)){
				self::$CHANNEL[$domain] = (substr($url,-1)=="/") ? $url = substr($url,0,-1) : $url;
				return self::$CHANNEL[$domain];
			}
		}
		throw new Exception("channel [{$domain}] not found");
	}
}
?>