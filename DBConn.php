<?php
include("Library/Configuration/base.config.php");
/*************************************************
 * core.config.view.php
 * This configures the common HTML tags, elements
 * and attributes that will be used when creating
 * the view (mostly because I hate templating).
 *
 * @author			Arbor Solutions, Inc.
 * @website			http://www.arbsol.com
 * @copyright		2014 (C) Arbor Solutions, Inc.
 * @developer		Tyler J Barnes, tbarnes@arbsol.com
 ****************************************************/

class DBConn {

	/**		INSTANCE VARS		**/

	// Database type
	protected $DB_TYPE;

	// Holds the host, port, username, password
	private $AuthData = array();

	// Database name and table name
	protected $Targets = array();

	// Link to the connection's reference
	protected $DbLinkRef;

	// SQL to execute
	protected $Query;

	// Results of the Query statement
	protected $SQLResults;

	// Connection error | authentication error | databse, tabel, or data location error | query error
	protected $IErrors = array();


	/**************************************************
	 * Constructor
	 * Constructs the database connection object and
	 * assigns empty property values if none are given;
	 * allowing two methods of instantiation.  Cannot
	 * construct using the reference link, query, type
	 * of query, connection status, errors, or the sql
	 * results - they are controlled elsewhere...
	 *
	 * @param		dbtyp: type of database to work with
	 * @param		host: host to use for the connection
	 * @param		user: username for the database
	 * @param		pass: password for the database
	 * @param		dbname: database name
	 * @param		tblname: data table name
	 ***************************************************/

	function DBConn() {

		$this->DB_TYPE = DB_DFLT_TYP;
		$this->AuthData["host"] = DB_HOST;
		$this->AuthData["user"] = DB_UNAME;
		$this->AuthData["pass"] = DB_PASS;
		$this->Targets["db"] = DB_NAME;
		$this->Targets["tbl"] = null;
		$this->DbLinkRef = null;
		$this->Query = "";
		$this->SQLResults = null;
		$this->IErrors["connect"] = "";
		$this->IErrors["auth"] = "";
		$this->IErrors["target"] = "";
		$this->IErrors["query"] = "";
	}



	/**************************************
	 * Link
	 * Attempts to connect to the database
	 * and sets the link to reference the
	 * reference if successful.
	 ************************************/
	function Link() {

		// remove existing link
		$this->DbLinkRef = null;

		// create new link with other object data
		$this->DbLinkRef = new mysqli($this->AuthData["host"], $this->AuthData["user"], $this->AuthData["pass"], $this->Targets["db"]);

		// error number means there was an error connecting
		if($this->DbLinkRef->connect_errno) {

			$this->IErrors["connect"] .= "Initial connection failed when instantiating mysql object. Details: " . $this->DbLinkRef->connect_error . TXT_NL;

			$this->DbLinkRef = null;
			die(DBCON_FAIL);
		}
	}


	/***********************************************
	 * Kill
	 * Either resets the database reference or
	 * destroys the current instance / object.
	 *
	 * @param	bool: false = reset | true = destroy
	 **********************************************/
	function Kill($bool = false) {

		$this->DbLinkRef = null;

		if($bool) {
			unset($this);
		}
	}



	/*************************************************
	 * get methods
	 * Use these methods to retrieve data about an
	 * object because most properties are protected
	 * or private.
	 ***********************************************/
	 // database name
	function getDb() { return $this->Targets["db"]; }
	// table name
	function getTbl() { return $this->Targets["tbl"]; }
	// reference link
	function getLnk() { return $this->DbLinkRef; }
	// current query stmt or string
	function getQry() { return $this->Query; }
	// result reference
	function getResRef() { return $this->SQLResults; }


	/*************************************
	 * gAll
	 * Gets all data from a query result
	 * set -> like fetch_all()
	 *************************************/
	function gAll() {
		if(!isset($this->SQLResults) || empty($this->SQLResults) || $this->SQLResults == null) {
			$this->IErrors["query"] .= "Could not fetch all data with no reselts." . TXT_NL;
			return null;
		}
		return $this->SQLResults->fetch_all(MYSQLI_ASSOC);
	}


	/*************************************
	 * gRow
	 * Gets the data in the next row of a
	 * query result set.
	 *************************************/
	function gRow() {
		if($row = $this->SQLResults->fetch_assoc()) {
			return $row;
		}
		return null;
	}



	/**************************
	 * gErr
	 * Gets a specified
	 * error log.
	 *
	 * @param type: log to get
	 **************************/
	function gErr($type = null) {

		if($type == null) {

			// combine all logs if type is null - concatenate
			$str = "All DBConn Errors:" . TXT_NL;

			foreach($this->IErrors as $title => $data) {
				$str .= "[$title]: $data" . TXT_NL;
			}

			$str .= "-----END-----" . TXT_NL;
			return $str;
		}

		return "For DBConn [$type]: " . $this->IErrors[$type] . TXT_NL;
	}




	/******************************
	 * resetQ
	 * Resets the Query to nothing
	 ******************************/
	function resetQ() {
		$this->Query = null;
		$this->Query = "";
	}



	/******************************
	 * resetRslt
	 * Resets the SQL reslts to nothing
	 ******************************/
	function resetRslt() {
		$this->SQLResults = null;
	}


	/*******************************************
	 * sDb
	 * Sets a new database, and tests it with a
	 * select call.
	 *
	 * @param	db: database name
	 *******************************************/
	function sDb($db = null) {
		if($db == null) return null;
		$this->Targets["db"] = $db;
		$this->DbLinkRef->select_db($db);
	}


	/*******************************************
	 * sTbl
	 * Sets the table name
	 *
	 * @param	tbl: table name to set
	 *******************************************/
	function sTbl($tbl = null) {
		$this->Targets["tbl"] = $tbl;
	}



	/*******************************************
	 * sLnk
	 * Injection of a new refernce link to query
	 * from - useful for dependency injection
	 *
	 * @param	ref: the reference to the link
	 *******************************************/
	function sLnk(mysqli &$ref) {
		$this->DbLinkRef = $ref;
		if($test = $this->DbLinkRef->query("SELECT DATABASE()")) {

			return $this;
		} else {
			$this->Kill(false);
		}
	}



	/*******************************************
	 * sQry
	 * Sets the Quer
	 *
	 * @param stmt: the statement to set
	 *******************************************/
	function sQry($stmt) {
		$this->Query = $stmt;
	}



	/*******************************************
	 * sRslt
	 * Sets the SQL results
	 *
	 * @param data: the data set of the result
	 *******************************************/
	function sRslt(mysqli_result $data) {
		$this->SQLResults = $data;
	}



	/*****************************************
	 * QQuery
	 * Pretty much just ->query() just formed
	 * to fit the class better
	 ****************************************/
	function QQuery() {

		if($sent = $this->DbLinkRef->query($this->Query)) {
			$this->sRslt($sent);
			return $sent;
		}

		$this->IErrors["query"] .= "Statement is not ready to send.  Something went wrong." . TXT_NL;
		return null;
	}



	/***************************************
	 * Statement builders
	 * SStatement = SELECT
	 * IStatement = INSERT
	 * UStatement = UPDATE
	 ***************************************/
	function SStatement(array $s, $f = null, $j = null, array $w, array $p) {

		if($s == null || count($s) < 1 || $f == null || empty($f)) {
			$this->IErrors["query"] .= "Invalid values passed to select builder." . TXT_NL;
			return null;
		}

		$sql = "SELECT ";

		foreach($s as $index => $string) { $sql .= $string; }

		$sql .= " FROM $f ";

		if($j != null) $sql .= " $j ";

		$sql .= "WHERE ";
		if(isset($w) && !empty($w) && $w != null) {

			foreach($w as $column => $toBind) {
				$sql .= " $column " . "$toBind";
			}
		}

		$this->sQry($sql);
		return $this->Query;
	}


	function IStatement($tbl = null, array $data = null) {

		if($tbl == null || empty($tbl) || $data == null || !isset($data) || empty($data)) return null;

		$sql = "INSERT INTO $tbl (";

		foreach($data as $property => $val) {
			$sql .= $property . TXT_SPC;
		}

		$sql .= ") VALUES (";

		foreach($data as $property => $val) {
			$sql .= $val . TXT_SPC;
		}

		$sql .= ")";

		$this->sQry($sql);
		return $this->Query;
	}



	function UStatement($tbl = null, array $setdata = null, array $condition = null) {

		if($tbl == null || $setdata == null || $condition == null || empty($tbl)) return null;

		$sql = "UPDATE $tbl SET ";

		foreach($setdata as $column => $value) {
			$sql .= $column . " = " . $value . TXT_SPC;
		}


		$sql .= "WHERE ";

		foreach($condition as $varWithSign => $right) {
			$sql .= "$varWithSign $right ";
		}

		$this->sQry($sql);
		return $this->Query;
	}

}
?>
