<?php
import('org.rhaco.service.GoogleAuth');
/**
 * Google Code 上のファイルを扱うライブラリ
 *
 * @author Kentaro YABE
 * @license New BSD License
 */
class GoogleCode extends GoogleAuth{
	const SEARCH_ALL = 1;
	const SEARCH_CURRENT = 2;
	const SEARCH_FEATURED = 3;
	const SEARCH_DEPRECTED = 4;
	
	const LABEL_FEATURED = "Featured";
	const LABEL_DEPRECATED = "Deprecated";
	const LABEL_EXECUTABLE = "Type-Executable";
	const LABEL_INSTALLER = "Type-Installer";
	const LABEL_PACKAGE = "Type-Package";
	const LABEL_ARCHIVE = "Type-Archive";
	const LABEL_SOURCE = "Type-Source";
	const LABEL_DOCUMENT = "Type-Docs";
	const LABEL_OS_ALL = "OpSys-All";
	const LABEL_OS_WINDOWS = "OpSys-Windows";
	const LABEL_OS_LINUX = "OpSys-Linux";
	const LABEL_OS_OSX = "OpSys-OSX";
	
	static protected $__fileinfo__ = "type=boolean";
	static protected $__project__ = "type=string";
	protected $project;
	protected $fileinfo = false;
	protected $query_array = false;
	private $logged_in = false;
	
	protected function __new__($project){
		$this->project = $project;
	}
	/**
	 * ファイルをアップロードする
	 * @param string $summary
	 * @param File $file
	 * @param string $label
	 */
	public function upload(File $file,array $labels,$summary=null){
		if(!$this->is_login()) throw new GoogleAuthException("not logged in");
		$this->do_get(sprintf("http://code.google.com/p/%s/downloads/entry",$this->project));
		$this->vars("file",$file);
		$summary = empty($summary) ? $file->name() : $summary;
		$this->vars("summary",$this->fileinfo ? sprintf("update=%d,size=%d,%s",(int)$file->update(),(int)$file->size(),$summary) : $summary);
		$this->vars("label",$labels);
		$this->submit(2);
	}
	/**
	 * ファイルを削除する
	 *
	 * @param string $filename
	 */
	public function delete($filename){
		if(!$this->is_login()) throw new GoogleAuthException("not logged in");
		$this->do_get(sprintf("http://code.google.com/p/%s/downloads/delete?name=%s",$this->project,$filename));
		if($this->status != 404) $this->submit(2,"delete");
	}
	/**
	 * ファイルに deprecated タグを付与する
	 *
	 * @param string $filename
	 */
	public function deprecate($filename){
		if(!$this->logged_in) throw new Exception("not logged in");
		$this->do_get(sprintf("http://code.google.com/p/%s/downloads/delete?name=%s",$this->project,$filename));
		if($this->status != 404) $this->submit(2,"deprecate");
	}
	/**
	 * ファイル検索
	 *
	 * @param string $q
	 * @param integer $within
	 * @return File[]
	 */
	public function search($q="",$within=2){
		$this->vars("q",$q);
		$this->vars("can",$within);
		$offset = 0;
		$files = array();
		while(true){
			$cnt = 0;
			$this->do_get(sprintf("http://code.google.com/p/%s/downloads/list?start=%d",$this->project,$offset));
			if(Tag::setof($body,$this->body,"body")){
				foreach($body->f("table[3].in(tr)") as $tr){
					if($tr->in_param("id") == "headingrow") continue;
					if(preg_match(sprintf("@\"(http://%s\.googlecode\.com/files/.+?)\"@i",$this->project),$tr->plain(),$matches)){
						$file = new File($matches[1]);
						$info = array();
						$info["summary"] = trim($tr->f("td[2].a[0].value()"));
						if(preg_match("@^update=(\d+),size=(\d+),(.*)@",$info["summary"],$matches)){
							$info["update"] = $matches[1];
							$info["size"] = $matches[2];
							$info["summary"] = $matches[3];
						}
						$info["download_count"] = (int)trim($tr->f("td[5].a[0].value()"));
						$file->merge($info);
						$files[] = $file;
					}
					$cnt++;
				}
			}
			if($cnt!=100) break;
			$offset += 100;
		}
		return $files;
	}
	/**
	 * ファイルが存在するか
	 *
	 * @param string $filename
	 * @return boolean
	 */
	public function exists($filename){
		$this->do_get(sprintf("http://%s.googlecode.com/files/%s",$this->project,$filename));
		return $this->status != 404;
	}
}
?>