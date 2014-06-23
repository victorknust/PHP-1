<?php
//namespace Services\Users;
require("UserServiceAdapterUI.php");
/*************************************************
 * UserServiceAdapter
 * Using the Namespace's interface, it provides
 * the logic to the outlined methods, ultimately
 * defining the way an object will be handled
 * from database entity to applicaiton model
 *
 * @author			Arbor Solutions, Inc.
 * @website			http://www.arbsol.com
 * @copyright		2014 (C) Arbor Solutions, Inc.
 * @developer		Tyler J Barnes, tbarnes@arbsol.com
 ****************************************************/
class UserServiceAdapter implements UserServiceAdapterUI {

	private $_dbAdapt;


	function UserServiceAdapter(DBConn $dbcon) {
		$this->_dbAdapt = $dbcon;
	}

	function GetAllUsers() {
		$this->_dbAdapt->SStatement(array(0 => "*"), DB_TBL_USER, null);
		$this->_dbAdapt->QQuery();
		return $this->_dbAdapt->gAll();
	}

	function GetAllEmployees() {
		$this->_dbAdapt->SStatement(array(0 => "*"), DB_TBL_USER, null,  array("Department !=" => " 'NA'"));
		$this->_dbAdapt->QQuery();
		return $this->_dbAdapt->gAll();
	}
	function GetAllManagers() {
		$this->_dbAdapt->SStatement(array(0 => "*"), DB_TBL_USER, null,  array("IsManager = " => "1"));
		$this->_dbAdapt->QQuery();
		return $this->_dbAdapt->gAll();
	}
	function GetAllActiveUsers() {
		$this->_dbAdapt->SStatement(array(0 => "*"), DB_TBL_USER, null,  array("Online = " => "1"));
		$this->_dbAdapt->QQuery();
		return $this->_dbAdapt->gAll();
	}
	function GetUsersByDepartment($dept) {
		$this->_dbAdapt->SStatement(array(0 => "*"), DB_TBL_USER, null, array("Department = " => "?"));
		$tmp = $this->_dbAdapt->getLnk();
		$tmpStmt = $tmp->prepare($this->_dbAdapt->getQry());
		$tmpStmt->bind_param("s", $dept);
		$tmpStmt->execute();
		$this->_dbAdapt->sRslt($tmpStmt->get_result());
		$tmpStmt->close();
		return $this->_dbAdapt->gAll();
	}
	function GetUsersByAccountType($type) {
		$this->_dbAdapt->SStatement(array(0 => "*"), DB_TBL_USER, null, array("AccType = " => "?"));
		$tmp = $this->_dbAdapt->getLnk();
		$tmpStmt = $tmp->prepare($this->_dbAdapt->getQry());
		$tmpStmt->bind_param("s", $type);
		$tmpStmt->execute();
		$this->_dbAdapt->sRslt($tmpStmt->get_result());
		$tmpStmt->close();
		return $this->_dbAdapt->gAll();
	}
	function GetUsersByUserManager($managerID = null) {

		if($managerID == null) return $this->GetAllUsers();

		$this->_dbAdapt->SStatement(array(0 => "*"), DB_TBL_USER, null, array("ManagerID = " => "?"));
		$tmp = $this->_dbAdapt->getLnk();
		$tmpStmt = $tmp->prepare($this->_dbAdapt->getQry());
		$tmpStmt->bind_param("i", $managerID);
		$tmpStmt->execute();
		$this->_dbAdapt->sRslt($tmpStmt->get_result());
		$tmpStmt->close();
		return $this->_dbAdapt->gAll();
	}
	function GetUserByName($ln = null) {
		if($ln == null) return $this->GetAllUsers();

		$this->_dbAdapt->SStatement(array(0 => "*"), DB_TBL_USER, null, array("LastName LIKE " => "?"));
		$tmp = $this->_dbAdapt->getLnk();
		$tmpStmt = $tmp->prepare($this->_dbAdapt->getQry());
		$tmpStmt->bind_param("s", $ln);
		$tmpStmt->execute();
		$this->_dbAdapt->sRslt($tmpStmt->get_result());
		$tmpStmt->close();
		return $this->_dbAdapt->gAll();
	}
	function GetUserByUserName($un = null) {
		if($un == null) return $this->GetAllUsers();

		$this->_dbAdapt->SStatement(array(0 => "*"), DB_TBL_USER, null, array("UName LIKE " => "?"));
		$tmp = $this->_dbAdapt->getLnk();
		$tmpStmt = $tmp->prepare($this->_dbAdapt->getQry());
		$tmpStmt->bind_param("s", $un);
		$tmpStmt->execute();
		$this->_dbAdapt->sRslt($tmpStmt->get_result());
		$tmpStmt->close();
		return $this->_dbAdapt->gAll();
	}
	function GetUserById($id = null) {
		if($id == null) return $this->GetAllUsers();
		$this->_dbAdapt->SStatement(array(0 => "*"), DB_TBL_USER, null, array("UID = " => "?"));
		$tmp = $this->_dbAdapt->getLnk();
		$tmpStmt = $tmp->prepare($this->_dbAdapt->getQry());
		$tmpStmt->bind_param("i", $id);
		$tmpStmt->execute();
		$this->_dbAdapt->sRslt($tmpStmt->get_result());
		$tmpStmt->close();
		return $this->_dbAdapt->gAll();
	}
	function InsertNewUser(User $user) {
		if($user == null) return false;
		$props = array(
			"UName"	=>	"'" . "$user->getUserName()," . "'",
			"UPass"	=>	"$user->getPassword()," . "'",
			"UEmail"	=>	"'" . "$user->getEmail()," . "'",
			"Session"	=>	"'" . "$user->getSession()," . "'",
			"FirstName"	=>	"'" . "$user->getFirstName()," . "'",
			"LastName"	=>	"'" . "$user->getLastName()," . "'",
			"Phone"	=>	"'" . "$user->getPhoneNumber()," . "'",
			"LastCheckInLocation"	=>	"'" . "$user->getLocation()," . "'",
			"Department"	=>	"'" . "$user->getDeparment()," . "'",
			"PayRate"	=>	"'" . "$user->getPayRate()," . "'",
			"Online"	=>	"'" . "$user->getActive()," . "'",
			"AccType"	=>	"'" . "$user->getAccountType()," . "'",
			"StatusID"	=>	"'" . "$user->getCurrentStatus()," . "'",
			"LastCheckIn"	=>	"'" . "$user->getLastActive()," . "'",
			"ManagerID"	=>	"'" . "$user->getManagerID()," . "'",
			"AccountVerified"	=>	"'" . "$user->getVerifStatus()," . "'",
			"IsManager"	=>	"'" . "$user->getIsManager()" . "'",
		);
		$this->_dbAdapt->IStatement(DB_TBL_USER, $props);
		$this->_dbAdapt->QQuery();
		return true;
	}
	function UpdateUser(User $user = null) {
		if($user == null || $user->getUID() == null) return false;
		$props = array(
			"UName"	=>	"'" . "$user->getUserName()," . "'",
			"UPass"	=>	"$user->getPassword()," . "'",
			"UEmail"	=>	"'" . "$user->getEmail()," . "'",
			"Session"	=>	"'" . "$user->getSession()," . "'",
			"FirstName"	=>	"'" . "$user->getFirstName()," . "'",
			"LastName"	=>	"'" . "$user->getLastName()," . "'",
			"Phone"	=>	"'" . "$user->getPhoneNumber()," . "'",
			"LastCheckInLocation"	=>	"'" . "$user->getLocation()," . "'",
			"Department"	=>	"'" . "$user->getDeparment()," . "'",
			"PayRate"	=>	"'" . "$user->getPayRate()," . "'",
			"Online"	=>	"'" . "$user->getActive()," . "'",
			"AccType"	=>	"'" . "$user->getAccountType()," . "'",
			"StatusID"	=>	"'" . "$user->getCurrentStatus()," . "'",
			"LastCheckIn"	=>	"'" . "$user->getLastActive()," . "'",
			"ManagerID"	=>	"'" . "$user->getManagerID()," . "'",
			"AccountVerified"	=>	"'" . "$user->getVerifStatus()," . "'",
			"IsManager"	=>	"'" . "$user->getIsManager()" . "'",
		);
		$cond = array("UID = " => "$user->getUID()");
		$this->_dbAdapt->UStatement(DB_TBL_USER, $props, $cond);
		$this->_dbAdapt->QQuery();
		return true;
	}
}
?>
