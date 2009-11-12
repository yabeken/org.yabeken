<?php
/**
 * PEAR ライブラリの読み込み
 * pear("pear.php.net/DB");
 * pear("openpear.org/Wozozo_Unko");
 * @param string $package
 * @return string インポートしたクラス名
 */
function pear($package){
	return Pea::import($package);
}
/**
 * PEAR パスを取得
 * @return string
 */
function pear_path(){
	return Pea::pear_path();
}