<?php
/*************************************************
 * MasterHash
 * Class that deals with hashes that are not ment
 * to be decoded, and generates the keys used in
 * the app for verification
 *
 * @author			Tyler J Barnes, tbarnes@arbsol.com
 ****************************************************/
class MasterHash {

	protected $XStr;

	public function MasterHash($mixer = null) {
		if(!isset($mixer) || empty($mixer) || $mixer == null) {
			$alph = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz!@#$&?";
			$str = "";
			for($i = 0; $i < 12; $i++) {
				$str .= $alph[mt_rand(0, 67)];
			}
			$mixer = $str;
		}
		$this->XStr = $mixer;
	}

	public static function getXStr() { return $this->XStr; }

	public function _Hash($str, $salt = null) {
		if(strlen($str) < 1) return null;
		if(!isset($salt) || empty($salt) || $salt == null) {
			for($i = 0; $i < strlen($str); $i++) {
				$rnd = mt_rand(0, strlen($str) - 1);
				$tmp = $str[$i];
				$str[$i] = $str[$rnd];
				$str[$rnd] = $tmp;
			}
		} else {
			$str = crypt(md5($str), $salt);
		}
		return $str;
	}

	public function TieString($str = null) {
		if(!isset($str) || empty($str) || $str == null) return null;
		$len = strlen($str);
		$twin = array("", "");
		for($i = 0; $i < $len; $i++) {
			if($i % 2 == 1)
				$twin[0] .= $str[$i];
			else
				$twin[1] .= $str[$i];
		}
		return implode($twin);
	}

	public function UnTieString($str) {
		if(!isset($str) || empty($str) || $str == null) return null;
		$len = strlen($str);
		if($len % 2 == 0) {
			$tmp[0] = substr($str, 0, $len / 2);
			$tmp[1] = substr($str, $len / 2);
		} else {
			$tmp[0] = substr($str, 0, (($len - 1) / 2));
			$tmp[1] = substr($str, (($len - 1) / 2));
		}
		$tmp[0] = str_split($tmp[0]);
		$tmp[1] = str_split($tmp[1]);
		$twin = "";
		for($i = 0; $i < $len; $i++) {
			if($i % 2 == 0) {
				$twin .= $tmp[1][0];
				array_shift($tmp[1]);
			} else {
				$twin .= $tmp[0][0];
				array_shift($tmp[0]);
			}
		}
		return $twin;
	}

}
?>
