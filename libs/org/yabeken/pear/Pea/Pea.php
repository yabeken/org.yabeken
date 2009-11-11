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
	static private $PREFFERED_STATE = "stable";
	static private $prepared = false;
	
	const STATE_STABLE = "stable";
	const STATE_BETA = "beta";
	const STATE_ALPHA = "alpha";
	const STATE_DEVELOP = "devel";
	
	
	/**
	 * PEA::R なんちって
	 */
	static private function r(){
		if(self::$prepared) return;
		if(!File::exist(File::path(self::pear_path(),"PEAR.php"))){
//			self::install("pear.php.net/PEAR");
		}
//		require_once(File::path(self::pear_path()));
//		require_once(path("vendors/pear/PEAR5.php"));
		set_include_path(self::pear_path());
		self::$prepared = true;
	}
	
	static public function config_path($pear_path){
		if(isset($pear_path)) self::$PEAR_PATH = $pear_path;
	}
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
		
		self::install($package_path);
	}
	
	/**
	 * 強制インストールを行う
	 * @param string $package
	 * @return boolean
	 */
	static public function install($package){
		$package = strtolower($package);
		list($domain,$package_name) = (strpos($package,"/")===false) ? array("pear.php.net",$package) : explode("/",$package,2);
		list($package_name,$package_version) = (strpos($package_name,"-")===false) ? array($package_name,null) : explode("-",$package_name,2);
		if(!isset(self::$CHANNEL[$domain])) self::channel_discover($domain);
		while(true){
			$allreleases_xml = self::$CHANNEL[$domain]."/r/".$package_name."/allreleases.xml";
			if(!Tag::setof($a,R(Http)->do_get($allreleases_xml)->body(),"a")) throw new RuntimeException($package." not found");
			$target_package = $a->f("p.value()");
			$target_version = null;
			$target_state = self::$PREFFERED_STATE;
			if(in_array($package_version,array(self::STATE_STABLE,self::STATE_BETA,self::STATE_ALPHA,self::STATE_DEVELOP))){
				$target_state = $package_version;
				$package_version = null;
			}
			foreach($a->in("r") as $r){
				$v = $r->f("v.value()");
				if($package_version==$v){
					$target_version = $v;
					break;
				}
				if($target_state == $r->f("s.value()")){
					$target_version = $v;
					break;
				}
			}
			if(empty($target_version)) throw new RuntimeException($package." not found");
			$download_path = File::absolute(App::work("pear"),strtr($domain,".","_")."_".$target_package."_".strtr($target_version,".","_"));
			$download_url = "http://".$domain."/get/".$target_package."-".$target_version.".tgz";
			if(!File::exist($download_path)){
				File::untgz($download_url,$download_path);
			}
			//TODO 依存関係
			$package_xml = self::$CHANNEL[$domain]."/r/package.".$target_version.".xml";
//			if(Tag::setof($dependencies,R(Http)->do_get($package_xml)->body(),"dependencies")){
//				
//			}
			
			//TODO ファイルのコピー
			
			break;
		}
	}
	static private function channel_discover($domain){
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