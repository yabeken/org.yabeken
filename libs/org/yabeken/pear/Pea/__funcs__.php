<?php
/**
 * PEAR ライブラリの読み込み
 * pear("net.php/DB");
 * pear("org.openpear/Wozozo_Unko");
 * @param string $package
 * @return string インポートしたクラス名
 */
function pear($package){
	return Pea::import($package);
}