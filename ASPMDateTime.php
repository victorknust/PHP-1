<?php
/**************************************************
 * ASPMController.php
 * The main controller for the application. It
 * acts as a service model for the controllers
 * being created throughout the app.
 *
 * @author			Arbor Solutions, Inc.
 * @website			http://www.arbsol.com
 * @copyright		2014 (C) Arbor Solutions, Inc.
 * @developer		Tyler J Barnes
 * @contact			tbarnes@arbsol.com
 ************************************************/
class ASPMDateTime {

	/**	STATIC PROPERTY	**/
	public static $Months = array(
			0	=>	"JAN",
			1	=>	"FEB",
			2	=>	"MAR",
			3	=>	"APR",
			4	=>	"MAY",
			5	=>	"JUN",
			6	=>	"JULY",
			7	=>	"AUG",
			8	=>	"SEPT",
			9	=>	"OCT",
			10	=>	"NOV",
			11	=>	"DEC"
		);

	/**	Class Properties	**/
	protected $DbDate;
	protected $DbTime;
	protected $AppDate;
	protected $AppTime;

	/******************************************************
	 * Constructs an ASPMDateTime object.
	 *
	 * @param	dbdate: date in db format
	 * @param	dbtime: time in db format
	 * @param	seconds: show seconds in app time summary
	 *****************************************************/
	public function ASPMDateTime($dbdate = null, $dbtime = null, $seconds = true) {
		//	value specified in correct format
		if(isset($dbdate) && isset($dbtime) && $dbdate != null && $dbtime != null && strpos($dbdate, "-") !== false) {

			$this->DbDate = $dbdate;

			$this->DbTime = $dbtime;

		//	not specified or incorrect format
		} else {
			//	set to now
			$this->DbDate = date("Y-m-d");
			$this->DbTime = date("H:i:s");
		}

		$this->AppDate = self::Db2ASPM($this->DbDate);
		$this->AppTime = self::Time2ASPM($this->DbTime, $seconds);
	}


	/*********************************
	 * Sets both database and app date
	 *
	 * @param	date: date to set to
	 * @return	true if completed
	 **********************************/
	public function _date($date) {
		if(strpos($date, "-") === false || strpos($date, "-") < 4 || strlen($date) != 10) return false;

		$this->DbDate = $date;
		$this->AppDate = self::Db2ASPM($this->DbDate);

		return true;
	}


	/*********************************
	 * Sets both database and app time
	 *
	 * @param	time: time to set to
	 * @return	true if completed
	 **********************************/
	public function _time($time, $seconds = true) {
		if(strpos($time, ":") === false || strlen($time) < 7) return false;

		$this->DbTime = $time;
		$this->AppTime = self::Time2ASPM($this->DbTime, $seconds);

		return true;
	}


	/*************************************************
	 * Returns a formatted string of a timestamp in
	 * database format.
	 *
	 * @param	includetime: include time in string
	 * @return	timestamp as string
	 ************************************************/
	public function getDbDate($includetime = true) {
		return $includetime ? $this->DbDate . " " . $this->DbTime : $this->DbDate;
	}


	/*************************************************
	 * Returns a formatted string of a timestamp in
	 * application - user friendly -  format.
	 *
	 * @param	includetime: include time in string
	 * @return	timestamp as string
	 ************************************************/
	public function getAppDate($includetime = true) {
		return $includetime ? $this->AppDate . " " . $this->AppTime : $this->AppDate;
	}

/**********************************************
 *******	STATIC FUNCTIONS	***************
 **********************************************/

	/*******************************************
	 * Convert database date format to application
	 * date format string.
	 *
	 * @param	date: date to convert
	 * @return string as new date
	 *******************************************/
	public static function Db2ASPM($date) {
		if(strpos($date, "-") === false || strpos($date, "-") < 4 || strlen($date) != 10) return $date;

		$arr = explode("-", $date);

		$y = (int)$arr[0];
		$m = (int)$arr[1] - 1;
		$d = (int)$arr[2];

		$date = self::$Months[$m] . " " . ($d < 10 ? "0$d" : (string)$d) . ", " . (string)$y;

		return $date;
	}


	/*******************************************
	 * Convert application date format to database
	 * format date.
	 *
	 * @param	date: date to convert
	 * @return string as new date
	 *******************************************/
	public static function ASPM2Db($date) {
		if(strpos($date, "-") !== false) return $date;
		$arr = explode(" ", str_replace(",", "", $date));
		$m = (int)array_search($arr[0], self::$Months);
		$m++;
		$m = $m < 10 ? "0$m" : (string)$m;
		$d = $arr[1];
		$y = $arr[2];
		$date = "$y-$m-$d";
		return $date;
	}


	/***************************************************
	 * Determines which of two dates are later or more
	 * recent.
	 *
	 * @param	d1: first date
	 * @param	d2: second date
	 * @return string of later date
	 ***************************************************/
	public static function LaterDate($d1, $d2) {
		if($d1 == $d2) return false;

		$arr1 = explode("-", $d1);
		$arr2 = explode("-", $d2);

		//	if years equal
		if((int)$arr1[0] == (int)$arr2[0]) {

			//	if months equal
			if((int)$arr1[1] == (int)$arr2[1]) {
				//	bigger day
				return ((int)$arr1[2] > (int)$arr2[2]) ? $d1 : $d2;
			}
			//	months inequal, bigger month
			return ((int)$arr1[1] > (int)$arr2[1]) ? $d1 : $d2;
		}
		//	months and year different, bigger year
		return ((int)$arr1[0] > (int)$arr2[0]) ? $d1 : $d2;
	}


	/*********************************************
	 * Convers db formatted time to app / more user
	 * readable format.
	 *
	 * @param	time: time to convert
	 * @param	bool: show seconds
	 * @return new formatted time string
	 *********************************************/
	public static function Time2ASPM($time, $bool = true) {
		$arr = explode(":", $time);

		$apm = "am";

		if((int)$arr[0] > 11) {
			$apm = "pm";
		}

		if((int)$arr[0] > 12) $h = ((int)$arr[0]) - 12;

		elseif((int)$arr[0] == 0) $h = 12;

		else $h = (int)$arr[0];

		return $bool ? (($h > 9) ? (string)$h : "0$h") . ":$arr[1]$apm (seconds: $arr[2])" : (($h > 9) ? (string)$h : "0$h") . ":$arr[1]$apm";
	}


	/**********************************************
	 * Determines difference in two dates. Date
	 * formats for parameters must be in Y-m-d
	 * format ( YYYY-mm-dd).
	 *
	 * @param	d1: first date
	 * @param	d2: second date
	 * @return	array with yr, mth, week, day, hour, min, sec difference
	 *************************************************************/
	public static function DateDiff($d1, $d2) {
		if($d1 == $d2) return 0;
		//	unix integer time
		$d1 = (int)strtotime($d1);
		$d2 = (int)strtotime($d2);

		$timz = array(
			"year"	=>	(60 * 60 * 24 * 365),
			"mth"	=>	(60 * 60 * 24 * 30),
			"day"	=>	(60 * 60 * 24),
			"hour"	=>	(60 * 60),
			"min"	=>	(60)
		);

		$diff = abs($d1 - $d2);

		$y = floor($diff / $timz["year"]);
		$M = floor(($diff - ($y * $timz["year"])) / $timz["mth"]);
		$d = floor((($diff - ($y * $timz["year"]) - ($M * $timz["mth"])) / $timz["day"]));
		$w = ($d > 6) ? floor($d / 7) : 0;
		$h = floor((($diff - ($y * $timz["year"]) - ($M * $timz["mth"]) - ($d * $timz["day"])) / $timz["hour"]));
		$m = floor((($diff - ($y * $timz["year"]) - ($M * $timz["mth"]) - ($d * $timz["day"]) - ($h * $timz["hour"])) / $timz["min"]));
		$s = floor($diff - (($y * $timz["year"]) + ($M * $timz["mth"]) + ($d * $timz["day"]) + ($h * $timz["hour"]) + ($m * $timz["min"])));

		return array($y, $M, $w, $d, $h, $m, $s);
	}

/**********************************************
 ******* END STATIC FUNCTIONS	***************/
}
?>
