<?php

	class user
	{
		public $userid = null;
		public $fullname = null;
		public $level = null;
		public $mobile = null;
		public $email = null;
		public $handle = null;
		public $password = null;
		public $joined = null;
		public $updated = null;

		public function __construct( $data=array() ) 
		{ 
			if ( isset( $data['userid'] ) ) $this->userid = (int) $data['userid'];
			if ( isset( $data['fullname'] ) ) $this->fullname = $data['fullname'];
			if ( isset( $data['level'] ) ) $this->level = (int) $data['level'];
			if ( isset( $data['mobile'] ) ) $this->mobile = $data['mobile'];
			if ( isset( $data['email'] ) ) $this->email = $data['email'];
			if ( isset( $data['handle'] ) ) $this->handle = $data['handle'];
			if ( isset( $data['password'] ) ) $this->password = md5($data['password']);
			if ( isset( $data['joined'] ) ) $this->joined = $data['joined'];
			if ( isset( $data['updated'] ) ) $this->updated = $data['updated'];
		}

		public function storeFormValues ( $params ) 
		{
			$this->__construct( $params );

			if ( isset($params['joined']) ) {
				$joined = explode ( '-', $params['joined'] );

				if ( count($joined) == 3 ) {
					list ( $y, $m, $d ) = $joined;
					$this->joined = mktime ( 0, 0, 0, $m, $d, $y );
				}
			}
		}

		public static function getById( $userid ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT *, UNIX_TIMESTAMP(joined) AS joined FROM users WHERE userid = :userid";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":userid", $userid, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) return new user( $row );
		}

		public static function signinuser( $handle, $password ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT * FROM users WHERE handle = :handle AND password = :password";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":handle", $handle, PDO::PARAM_INT );
			$st->bindValue( ":password", $password, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) {
				$_SESSION['furniture_level'] = $row['level'];
				$_SESSION['furniture_name'] = $row['fullname'];
				$_SESSION['furniture_user'] = $row['userid'];
				return true;
			}	else return false;
		}

		public static function getList( $level ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM users WHERE level = :level ORDER BY joined DESC";

			$st = $conn->prepare( $sql );
			$st->bindValue( ":level", $level, PDO::PARAM_INT );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$user = new user( $row );
				$list[] = $user;
			}

			$sql = "SELECT FOUND_ROWS() AS totalRows";
			$totalRows = $conn->query( $sql )->fetch();
			$conn = null;
			return ( array ( "results" => $list, "totalRows" => $totalRows[0] ) );
		}

		public function insert() 
		{
			if ( !is_null( $this->userid ) ) trigger_error ( "user::insert(): Attempt to insert an user object that already has its ID property set (to $this->userid).", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "INSERT INTO users ( fullname, level, mobile, email, handle, password, joined ) VALUES ( :fullname, :level, :mobile, :email, :handle, :password, :joined )";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":fullname", $this->fullname, PDO::PARAM_STR );
			$st->bindValue( ":level", $this->level, PDO::PARAM_STR );
			$st->bindValue( ":mobile", $this->mobile, PDO::PARAM_STR );
			$st->bindValue( ":email", $this->email, PDO::PARAM_STR );
			$st->bindValue( ":handle", $this->handle, PDO::PARAM_STR );
			$st->bindValue( ":password", $this->password, PDO::PARAM_STR );
			$st->bindValue( ":joined", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->execute();
			$this->userid = $conn->lastInsertId();
			$conn = null;
			return $this->userid;
		}

		public function update() 
		{
			if ( is_null( $this->userid ) ) trigger_error ( "user::update(): Attempt to update an user object that does not have its ID property set.", E_USER_ERROR );
		   
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "UPDATE users SET handle=:handle, fullname=:fullname, mobile=:mobile, email=:email, level=:level, updated=:updated WHERE userid =:userid";
			
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":handle", $this->handle, PDO::PARAM_STR );
			$st->bindValue( ":fullname", $this->fullname, PDO::PARAM_STR );
			$st->bindValue( ":email", $this->email, PDO::PARAM_STR );
			$st->bindValue( ":level", $this->level, PDO::PARAM_STR );
			$st->bindValue( ":mobile", $this->mobile, PDO::PARAM_STR );
			$st->bindValue( ":updated", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

		public function delete() 
		{
			if ( is_null( $this->userid ) ) trigger_error ( "user::delete(): Attempt to delete an user object that does not have its ID property set.", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$st = $conn->prepare ( "DELETE FROM users WHERE userid = :userid LIMIT 1" );
			$st->bindValue( ":userid", $this->userid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

	}
