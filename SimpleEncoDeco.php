<?php
/*************************************************
 * SimpleEncoDeco
 * Class built as a tool for the application to
 * be able to 'encrypt' strings, msgs, file names,
 * etc. etc.  Really, it helps avoid so much plain
 * text - but also allows the plain text to be
 * recovered
 *
 * @author			Tyler J Barnes, tbarnes@arbsol.com
 ****************************************************/
class SimpleEncodeDecode {

	/**		STATIC VALUES		**/
						//	TODO:
						//	PUT YOUR OWN RANDOM, CRAZY LOOKING KEY HERE
						//	using all the letters as upper and lower case
						//	and all numbers 0-9
	public static $KEY = 'KEYkeyKEYkeyKEY00000';
	public static $spc  = array(0 => "{/}", 1 => "<;>", 2 => "(%)");

	public function SimpleEncodeDecode() {
	}


	/********************************************************
	 * QuickHash
	 * Quickly generates a masked string string using the key
	 * and spc (space, meaning takes up space, padding), above
	 * in a way that can also by decoded.  The generated strings
	 * should NOT be considered 'secure', nor should this class
	 * replace any crypto methods you use.  This class was just
	 * created to prevent so much plain text.
	 *
	 * @param	str: The string to encode
	 * @return	the encoded string
	 *******************************************************/
	public static function QuickHash($str) {

		if(!isset($str) || empty($str) || strlen($str) < 1) return null;

		//	immediately replace tabs and newlines - they mess things up
		//	a bit - and make sure characters can be easily recognized and will not change
		$str = str_replace("\n", '|', $str);

		$str = str_replace("\t", "[*]", $str);

		$slen = strlen($str);
		$klen = strlen(self::$KEY);

		//	string to encode is longer than the KEY - must make sure the KEY is cycled through
		if($slen > $klen) {

			//	a buffer... this determines the character positions for the new endcoded string
			$buff = $slen % $klen;

			//	iterate through all chars in str
			for($i = 0; $i < $slen; $i++) {

				//	like the tabs and newlines, replace space with something recognizable later
				if($str[$i] == " ") $str[$i] = "^";
				//	char does not exist in the KEY, remains same - go to next char
				elseif(strpos(self::$KEY, $str[$i]) === false) continue;
				//	otherwise - mess the string up
				else {

					//		making sure the changes with the character position stay within the length of the key
					//		the 'cycle' mentioned earlier.  Find the position of the character in the KEY, then add
					//		the value of the buffer to it, and take new character.
					if(strpos(self::$KEY, $str[$i]) + $buff >= $klen) $indx = ((strpos(self::$KEY, $str[$i]) + $buff) - $klen);
					else $indx = strpos(self::$KEY, $str[$i]) + $buff;

					//		replace character in str
					$str[$i] = self::$KEY[$indx];
				}
			}
		//		If the str is NOT longer than the KEY, make sure stay in
		//		bounds of str and KEY with buffer index change
		} else {

			$buff = $klen % $slen;

			//	all chars - spaces - special chars
			for($int = 0; $int < $slen; $int++) {

				if($str[$int] == " ") $str[$int] = "^";

				elseif(strpos(self::$KEY, $str[$int]) === false) continue;

				else {

					//		stay in bounds
					if(strpos(self::$KEY, $str[$int]) + $buff >= $klen) $xndx = ((strpos(self::$KEY, $str[$int]) + $buff) - $klen);
					else $xndx = strpos(self::$KEY, $str[$int]) + $buff;

					//		replace
					$str[$int] = self::$KEY[$xndx];
				}
			}

			//		NOW, if the str was shorter than KEY, it could be a pretty short string
			//		so add a bit of randomness to the string by inserting nothing, really
			for($i = 0; $i < $buff; $i++) {
				//	random #
				$rnd = mt_rand(0, $slen - 1);
				//	insert the spc chars in somewhere
				$str = substr($str, 0, $rnd) . self::$spc[array_rand(self::$spc)] . substr($str, $rnd);
			}
		}
		return $str;
	}


	/**********************************************************
	 * QuickDecode
	 * This undoes what QuickHash did
	 *
	 * @param	str: the string to decode
	 * @returns	the decoded string
	 ***********************************************************/
	public static function QuickDecode($str) {

		if(!isset($str) || empty($str) || strlen($str) < 1) return null;

		//	for loop to replace any 'spc' nothingness - one or two times
		//	will not clear all the spaces because their chars get tangled
		//	so I do this a lot of times to ensure theyre most likely gone
		for($_x = 0; $_x < 7; $_x++) $str = str_replace(self::$spc, "", $str);

		$slen = strlen($str);
		$klen = strlen(self::$KEY);

		if($slen <= $klen) {
			//	was buffer - now excape value to unbuff
			$esc = $klen % $slen;
			//	all chars
			for($int = 0; $int < $slen; $int++) {

				if($str[$int] == "^") $str[$int] = " ";

				elseif(strpos(self::$KEY, $str[$int]) === false) continue;

				else {

					//	stay in bounds
					if(strpos(self::$KEY, $str[$int]) - $esc < 0) $xndx = ($klen + (strpos(self::$KEY, $str[$int]) - $esc));
					else $xndx = strpos(self::$KEY, $str[$int]) - $esc;

					$str[$int] = self::$KEY[$xndx];
				}
			}
		//		str is longer than KEY after sps chars removed
		} else {

			$esc = $slen % $klen;

			for($i = 0; $i < $slen; $i++) {

				if($str[$i] == "^") $str[$i] = " ";
				elseif(strpos(self::$KEY, $str[$i]) === false) continue;

				else {

					//	escape to pad the string and find character pos value by doing the opposite of buffer
					if(strpos(self::$KEY, $str[$i]) - $esc < 0) $indx = ($klen + (strpos(self::$KEY, $str[$i]) - $esc));
					else $indx = strpos(self::$KEY, $str[$i]) - $esc;

					$str[$i] = self::$KEY[$indx];
				}
			}
		}
		//	put the tabs and new lines back
		$str = str_replace('|', "\n", $str);
		$str = str_replace("[*]", "\t", $str);

		return $str;
	}
}
?>
