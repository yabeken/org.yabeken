<?php
/**
 * PEAR ライブラリ制御
 * @author yabeken
 * @license New BSD License
 */
class Pea extends Http{
	protected $agent = "Pea (PEAR Client) powered by rhaco2";
	
	static private $PEAR_PATH;
	static private $IMPORTED = array();
	static private $CHANNEL = array();
	static private $INSTALL = array();
	static private $PREFFERED_STATE = 0;
	static private $DEPENDENCY = false;
	static private $OPTIONAL = false;
	
	static private $states = array("stable"=>0,"beta"=>1,"alpha"=>2,"devel"=>3);
	static private $prepared = false;
	
	const STATE_STABLE = "stable";
	const STATE_BETA = "beta";
	const STATE_ALPHA = "alpha";
	const STATE_DEVELOP = "devel";
	
	static private function r(){
		//Pea::r なんちって
		if(self::$prepared) return;
		self::$PEAR_PATH = module_const("pear_path",File::path(dirname(Lib::vendors_path()),"pear"));
		if(!File::exist(File::path(self::$PEAR_PATH,"PEAR.php"))){
			self::install("pear.php.net/PEAR");
		}
		self::$DEPENDENCY = module_const("dependency",true);
		self::$OPTIONAL = module_const("optional",false);
		$state = module_const("state",self::STATE_STABLE);
		self::$PREFFERED_STATE = isset(self::$states[$state]) ? self::$states[$state] : self::STATE_STABLE;
		set_include_path(self::$PEAR_PATH.PATH_SEPARATOR.get_include_path());
		require(File::path(self::$PEAR_PATH,"PEAR.php"));
		self::$prepared = true;
	}
	/**
	 * PEAR ライブラリを読み込む
	 * @param string $package_path
	 * @return string
	 */
	static public function import($package_path){
		self::r();
		list($domain,$package_name,$package_version) = self::parse_package($package_path);
		$package_key = strtolower($domain."/".$package_name);
		if(isset(self::$IMPORTED[$package_key])) return self::$IMPORTED[$package_key];
		$path = File::path(self::$PEAR_PATH,strtr($package_name,"_","/").".php");
		if(!File::exist($path)) self::install($package_path);
		require($path);
		self::$IMPORTED[$package_key] = class_exists($package_name) ? $package_name : null;
		return self::$IMPORTED[$package_key];
	}
	/**
	 * インストール
	 * @param string $package_path
	 * @return boolean
	 */
	static public function install($package_path){
		list($domain,$package_name,$package_version) = self::parse_package($package_path);
		if(strtolower($package_name) != "pear") self::r();
		if(isset(self::$INSTALL[strtolower($domain."/".$package_name)])) return true;
		if(!isset(self::$CHANNEL[$domain])) self::channel_discover($domain);
		
		$allreleases_xml = self::$CHANNEL[$domain]."/r/".strtolower($package_name)."/allreleases.xml";
		if(!Tag::setof($a,R(new self())->do_get($allreleases_xml)->body(),"a")) throw new RuntimeException($package_path." not found");
		$target_package = $package_name;
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
			}else{
				$s = $r->f("s.value()");
				if(isset(self::$states[$s]) && self::$states[$s] <= $target_state){
					$target_version = $v;
				}
			}
			if(!empty($target_version)) break;
		}
		if(empty($target_version)) throw new RuntimeException($package_path." not found");
		
		$download_path = File::path(App::work("pear"),str_replace(array(".","-"),"_",$domain)."_".$target_package."_".strtr($target_version,".","_"));
		$download_url = "http://".$domain."/get/".$target_package."-".$target_version.".tgz";
		if(!File::exist($download_path)){
			self::download($download_url,$download_path);
		}
		$package_xml = File::exist(File::path($download_path,"package.xml")) ? File::path($download_path,"package.xml") : File::path($download_path,"package2.xml");
		self::$INSTALL[strtolower($domain."/".$target_package)] = $package_xml;
		if(Tag::setof($package,File::read($package_xml),"package")){
			switch($package->in_param("version")){
				case "1.0":
					if(self::$DEPENDENCY){
						foreach($package->f("deps.in(dep)") as $dep){
							if($dep->in_param("type")=="pkg"){
								if(self::$OPTIONAL || $dep->in_param("optional") == "no"){
									self::install($dep->value());
								}
							}
						}
					}
					foreach($package->f("release.filelist.in(file)") as $file){
						if($file->in_param("role") != "php") continue;
						$baseinstalldir = File::path(self::$PEAR_PATH,$file->in_param("baseinstalldir"));
						$name = $file->in_param("name");
						$src = File::path($download_path,File::path($target_package."-".$target_version,$name));
						$dst = File::path($baseinstalldir,$name);
						File::copy($src,$dst);
					}
					break;
				case "2.0":
					if(self::$DEPENDENCY){
						foreach($package->f("dependencies.required.in(package)") as $dep){
							self::install($dep->f("channel.value()")."/".$dep->f("name.value()"));
						}
						if(self::$OPTIONAL){
							foreach($package->f("dependencies.optional.in(package)") as $dep){
								self::install($dep->f("channel.value()")."/".$dep->f("name.value()"));
							}
						}
					}
					foreach($package->f("contents.in(dir)") as $dir){
						$default_baseinstalldir = $dir->in_param("baseinstalldir","/");
						foreach($dir->in("file") as $file){
							if($file->in_param("role") != "php") continue;
							$baseinstalldir = File::path(self::$PEAR_PATH,$file->in_param("baseinstalldir",$default_baseinstalldir));
							$name = $file->in_param("name");
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
		return true;
	}
	static protected function download($url,$outpath){
		$tmpname = File::absolute($outpath,File::temp_path($outpath));
		if(R(new self())->do_download($url,$tmpname)->status() != 200){
			File::rm($tmpname);
			throw new ErrorException("download failed [{$url}]");
		}
		File::untgz($tmpname,$outpath);
		File::rm($tmpname);
	}
	static protected function parse_package($package_path){
		list($domain,$name) = (strpos($package_path,"/")===false) ? array("pear.php.net",$package_path) : explode("/",$package_path,2);
		list($name,$version) = (strpos($name,"-")===false) ? array($name,null) : explode("-",$name,2);
		return array($domain,$name,$version);
	}
	static protected function channel_discover($domain){
		if(Tag::setof($channel,R(new self())->do_get("http://{$domain}/channel.xml")->body())){
			$url = $channel->f("rest.baseurl[0].value()");
			if(!empty($url)){
				self::$CHANNEL[$domain] = (substr($url,-1)=="/") ? $url = substr($url,0,-1) : $url;
				return self::$CHANNEL[$domain];
			}
		}
		throw new Exception("channel [{$domain}] not found");
	}
	/**
	 * pear package install
	 * @param Request $req
	 * @param string $value
	 */
	static public function __setup_pear_install__(Request $req,$value){
		$package = $req->in_vars("pear_install");
		if($req->is_vars("nodeps")) self::$DEPENDENCY = false;
		if($req->is_vars("optional")) self::$OPTIONAL = true;
		if($req->is_vars("state")) self::$PREFFERED_STATE = $req->in_vars("state");
		if(self::install($package)){
			println($package." installed");
		}
	}
}
?>
<?php
/**
 * PEAR ライブラリの読み込み
 * @param string $package
 * @return string インポートしたクラス名
 */
function pear($package){
	return Pea::import($package);
}
/**
 * PEAR ライブラリのインストール
 * @param string $package
 */
function pear_install($package){
	return Pea::install($package);
}
?>