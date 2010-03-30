<?php
/**
 * PEAR ライブラリ制御
 * @author yabeken
 * @license New BSD License
 */
class Pea extends Http{
	protected $agent = "Pea (PEAR Client) powered by rhaco2";
	
	static private $pear_path;
	static private $download_path;
	static private $imported = array();
	static private $channel = array();
	static private $install = array();
	static private $preffered_state = 0;
	static private $dependency = false;
	static private $optional = false;
	
	static private $states = array("stable"=>0,"beta"=>1,"alpha"=>2,"devel"=>3);
	static private $prepared = false;
	
	const STATE_STABLE = "stable";
	const STATE_BETA = "beta";
	const STATE_ALPHA = "alpha";
	const STATE_DEVELOP = "devel";
	
	static private function r(){
		//Pea::r なんちって
		if(self::$prepared) return;
		self::$pear_path = module_const("pear_path",File::path(dirname(Lib::vendors_path()),"pear"));
		self::$download_path = module_const("download_path",App::work("pear_src"));
		if(!File::exist(File::path(self::$pear_path,"PEAR.php"))){
			self::install("pear.php.net/PEAR");
		}
		self::$dependency = module_const("dependency",true);
		self::$optional = module_const("optional",false);
		$state = module_const("state",self::STATE_STABLE);
		self::$preffered_state = isset(self::$states[$state]) ? self::$states[$state] : self::STATE_STABLE;
		set_include_path(self::$pear_path.PATH_SEPARATOR.get_include_path());
		require(File::path(self::$pear_path,"PEAR.php"));
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
		if(isset(self::$imported[$package_key])) return self::$imported[$package_key];
		$path = File::path(self::$pear_path,strtr($package_name,"_","/").".php");
		if(!File::exist($path)) self::install($package_path);
		include_once($path);
		self::$imported[$package_key] = class_exists($package_name) ? $package_name : null;
		return self::$imported[$package_key];
	}
	/**
	 * インストール
	 * @param string $package_path
	 * @return boolean
	 */
	static public function install($package_path){
		list($domain,$package_name,$package_version) = self::parse_package($package_path);
		if(strtolower($package_name) != "pear") self::r();
		if(isset(self::$install[strtolower($domain."/".$package_name)])) return true;
		if(!isset(self::$channel[$domain])) self::channel_discover($domain);
		
		$allreleases_xml = self::$channel[$domain]."/r/".strtolower($package_name)."/allreleases.xml";
		if(!Tag::setof($a,R(new self())->do_get($allreleases_xml)->body(),"a")) throw new RuntimeException($package_path." not found");
		$target_package = $package_name;
		$target_version = null;
		$target_state = self::$preffered_state;
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
		
		$download_path = File::path(self::$download_path,str_replace(array(".","-"),"_",$domain)."_".$target_package."_".strtr($target_version,".","_"));
		$download_url = "http://".$domain."/get/".$target_package."-".$target_version.".tgz";
		if(!File::exist($download_path)){
			self::download($download_url,$download_path);
		}
		$package_xml = File::exist(File::path($download_path,"package.xml")) ? File::path($download_path,"package.xml") : File::path($download_path,"package2.xml");
		self::$install[strtolower($domain."/".$target_package)] = $package_xml;
		if(Tag::setof($package,File::read($package_xml),"package")){
			switch($package->in_param("version")){
				case "1.0":
					if(self::$dependency){
						foreach($package->f("deps.in(dep)") as $dep){
							if($dep->in_param("type")=="pkg"){
								if(self::$optional || $dep->in_param("optional") == "no"){
									self::install($dep->value());
								}
							}
						}
					}
					foreach($package->f("release.filelist.in(file)") as $file){
						if($file->in_param("role") != "php") continue;
						$baseinstalldir = File::path(self::$pear_path,$file->in_param("baseinstalldir"));
						$name = $file->in_param("name");
						$src = File::path($download_path,File::path($target_package."-".$target_version,$name));
						$dst = File::path($baseinstalldir,$name);
						File::copy($src,$dst);
					}
					break;
				case "2.0":
					if(self::$dependency){
						foreach($package->f("dependencies.required.in(package)") as $dep){
							self::install($dep->f("channel.value()")."/".$dep->f("name.value()"));
						}
						if(self::$optional){
							foreach($package->f("dependencies.optional.in(package)") as $dep){
								self::install($dep->f("channel.value()")."/".$dep->f("name.value()"));
							}
						}
					}
					foreach($package->f("contents.in(dir)") as $dir){
						$default_baseinstalldir = $dir->in_param("baseinstalldir","/");
						foreach($dir->in("file") as $file){
							if($file->in_param("role") != "php") continue;
							$baseinstalldir = File::path(self::$pear_path,$file->in_param("baseinstalldir",$default_baseinstalldir));
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
		unset(self::$install[strtolower($domain."/".$target_package)]);
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
				self::$channel[$domain] = (substr($url,-1)=="/") ? $url = substr($url,0,-1) : $url;
				return self::$channel[$domain];
			}
		}
		throw new Exception("channel [{$domain}] not found");
	}
	/**
	 * PEAR パッケージをインストール
	 * @param Request $req
	 * @param string $value
	 */
	static public function __setup_pear_install__(Request $req,$value){
		if($req->is_vars("path")) def("org.yabeken.service.Pea@pear_path",$req->in_vars("path"));
		if($req->is_vars("nodeps")) def("org.yabeken.service.Pea@dependency",false);
		if($req->is_vars("optional")) def("org.yabeken.service.Pea@optional",true);
		if($req->is_vars("state")) def("org.yabeken.service.Pea@state",$req->in_vars("state"));
		self::r();
		if(self::install($value)){
			println("installed ".$value);
		}
	}
	/**
	 * PEAR パッケージ一覧
	 * @param Request $req
	 * @param string $value
	 */
	static public function __setup_pear_package__(Request $req,$value){
		if($value == "") $value = "pear.php.net";
		$url = trim(self::channel_discover($value),"/") . ($req->is_vars("category") ? "/c/".$req->in_vars("category")."/packages.xml" : "/p/packages.xml");
		if(Tag::setof($package,R(new self())->do_get($url)->body())){
			foreach($package->in("p") as $p){
				println($p->value());
			}
		}
	}
	/**
	 * PEAR カテゴリ一覧
	 * @param Request $req
	 * @param string $value
	 */
	static public function __setup_pear_category__(Request $req,$value){
		if($value == "") $value = "pear.php.net";
		$url = trim(self::channel_discover($value),"/")."/c/categories.xml";
		if(Tag::setof($package,R(new self())->do_get($url)->body())){
			foreach($package->in("c") as $c){
				println($c->value());
			}
		}
	}
	/**
	 * PEAR パッケージを再インストール
	 * @param Request $req
	 * @param string $value
	 */
	static public function __setup_pear_reinstall__(Request $req,$value){
		if($req->is_vars("path")) def("org.yabeken.service.Pea@pear_path",$req->in_vars("path"));
		if($req->is_vars("download_path")) def("org.yabeken.service.Pea@download_path",$req->in_vars("download_path"));
		self::r();
		
		if(is_dir(self::$pear_path)){
			foreach(File::ls(self::$pear_path) as $file) File::rm($file);
			foreach(File::dir(self::$pear_path) as $dir) File::rm($dir);
		}
		
		$package = array();
		foreach(File::ls(App::path(),true) as $file){
			if($file->is_ext("php")) $package = array_merge($package,self::find_package($file));
		}
		foreach(File::ls(Lib::path(),true) as $file){
			if($file->is_ext("php")) $package = array_merge($package,self::find_package($file));
		}
		foreach(File::ls(Lib::vendors_path(),true) as $file){
			if($file->is_ext("php")) $package = array_merge($package,self::find_package($file));
		}
		
		$package = array_unique($package);
		sort($package);
		foreach($package as $name){
			if(self::install($name)){
				println("installed ".$name);
			}
		}
	}
	static protected function find_package(File $file){
		if($file->oname() == __CLASS__) return array();
		$src = File::read($file);
		$list = array();
		if(preg_match_all("/[^\w]pear(?:_install)?\(([\"\'])(.+?)\\1\)/",$src,$matches)){
			$list = array_merge($list,$matches[2]);
		}
		if(preg_match_all("/[^\w]Pea::(?:import|install)\(([\"\'])(.+?)\\1\)/",$src,$matches)){
			$list = array_merge($list,$matches[2]);
		}
		return $list;
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