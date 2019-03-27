<?php
	function tableCreate( $table,  $variables = array() ) 
	{
		try {
			$fields = array();
			$values = array();
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$sql = "CREATE TABLE IF NOT EXISTS ". $table;
			foreach( $variables as $field ) $fields[] = $field;
			$fields = ' (' . implode(', ', $fields) . ')';      
			$sql .= $fields;
			$conn->exec( $sql );
		} catch(PDOException $exception) {
			$as_err['errno'] = 3;
			$as_err['errtitle'] = 'Database action failed';
			$as_err['errsumm'] = 'Creating the table '. $table . ' failed';
			$as_err['errfull'] = $exception->getMessage();
		}
		$conn = null;
	}
	
	function createTables()
	{
		tableCreate( 'furnitures',  
			array(//title, price, image, content, place, workshop, created, updated
				'furnitureid int(11) NOT NULL AUTO_INCREMENT',
				'title varchar(100) NOT NULL',
				'price int(11) DEFAULT 0',
				'image varchar(2000) NOT NULL',
				'content varchar(1000) NOT NULL',
				'place varchar(100) NOT NULL',
				'workshop int(11) DEFAULT 0',
				'views int(11) DEFAULT 0',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (furnitureid)',
			)
		); 
		
		tableCreate( 'orders',
			array(//furnitureid, customer, quantity, created, updated
				'orderid int(11) NOT NULL AUTO_INCREMENT',
				'furniture int(11) DEFAULT 0',
				'customer int(11) DEFAULT 0',
				'quantity int(11) DEFAULT 0',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (orderid)',
			)
		); 
		
		tableCreate( 'options',
			array(
				'optionid int(11) NOT NULL AUTO_INCREMENT',
				'title varchar(100) NOT NULL',
				'content varchar(2000) NOT NULL',
				'created datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (optionid)',
			)
		); 
		
		tableCreate( 'users', 
			array(
				'userid int(11) NOT NULL AUTO_INCREMENT',
				'handle varchar(50) NOT NULL',
				'fullname varchar(50) NOT NULL',
				'place varchar(50) NOT NULL',
				'mobile varchar(50) NOT NULL',
				'password text NOT NULL',
				'email varchar(200) NOT NULL',
				'level int(10) NOT NULL DEFAULT 0',
				'joined datetime DEFAULT NULL',
				'updated datetime DEFAULT NULL',
				'PRIMARY KEY (userid)',
			)
		);
		
	}
	createTables();
	
	function checkTables( $table ) 
	{
		$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
		$sql = "SELECT * FROM " . $table . " LIMIT 1";
		$st = $conn->prepare( $sql );
		$st->execute();
		$row = $st->fetch();
		$conn = null;
		if ( $row ) return 0;
		else return 1;
	}