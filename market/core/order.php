<?php

	class order
	{
		public $orderid = null;
		public $furniture = null;
		public $customer = null;
		public $quantity = null;
		public $price = null;
		public $amount = null;
		public $created = null;
		public $updated = null;

		public function __construct( $data=array() ) 
		{ 
			if ( isset( $data['orderid'] ) ) $this->orderid = (int) $data['orderid'];
			if ( isset( $data['furniture'] ) ) $this->furniture = $data['furniture'];
			if ( isset( $data['customer'] ) ) $this->customer = $data['customer'];
			if ( isset( $data['quantity'] ) ) $this->quantity = $data['quantity'];
			if ( isset( $data['price'] ) ) $this->price = $data['price'];
			if ( isset( $data['amount'] ) ) $this->amount = $data['amount'];
			if ( isset( $data['created'] ) ) $this->created = $data['created'];
			if ( isset( $data['updated'] ) ) $this->updated = $data['updated'];
		}

		public function storeFormValues ( $params ) 
		{
			$this->__construct( $params );

			if ( isset($params['created']) ) {
				$created = explode ( '-', $params['created'] );

				if ( count($created) == 3 ) {
					list ( $y, $m, $d ) = $created;
					$this->created = mktime ( 0, 0, 0, $m, $d, $y );
				}
			}
		}

		public static function getById( $orderid ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT *, UNIX_TIMESTAMP(created) AS created FROM orders WHERE orderid = :orderid";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":orderid", $orderid, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) return new order( $row );
		}

		public static function getList( $sortby, $limit, $userid, $level)
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			switch ($level) {
				case 3:
					$wheresql = 'WHERE customer='.$userid.' ';
					break;
					
				case 4:
					$wheresql = 'WHERE workshop='.$userid.' ';	
					break;
					
				case 5:
					$wheresql = '';
					break;
			}
			$sql = 'SELECT *, UNIX_TIMESTAMP(orders.created) AS ordered, 
			CONCAT(fullname, " ", mobile) AS customer, title AS furniture 
			FROM orders  
			INNER JOIN users ON userid = customer 
			INNER JOIN furnitures ON furnitureid = furniture '.
			$wheresql.'ORDER BY '.$sortby.' DESC'.($limit != 0 ? ' LIMIT '.$limit : '');
			
			$st = $conn->prepare( $sql );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$order = new order( $row );
				$list[] = $order;
			}

			$conn = null;
			return $list;
		}

		public function insert() 
		{
			if ( !is_null( $this->orderid ) ) trigger_error ( "order::insert(): Attempt to insert an order object that already has its ID property set (to $this->orderid).", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "INSERT INTO orders ( furniture, customer, quantity, created ) VALUES ( :furniture, :customer, :quantity, :created )";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":furniture", $this->furniture, PDO::PARAM_STR );
			$st->bindValue( ":customer", $this->customer, PDO::PARAM_STR );
			$st->bindValue( ":quantity", $this->quantity, PDO::PARAM_STR );
			$st->bindValue( ":created", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->execute();
			$this->orderid = $conn->lastInsertId();
			$conn = null;
			return $this->orderid;
		}

		public function update() 
		{
			if ( is_null( $this->orderid ) ) trigger_error ( "order::update(): Attempt to update an order object that does not have its ID property set.", E_USER_ERROR );
		   
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "UPDATE orders SET furniture=:furniture, customer=:customer, quantity=:quantity, updated=:updated WHERE orderid =:orderid";
			
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":furniture", $this->furniture, PDO::PARAM_STR );
			$st->bindValue( ":customer", $this->customer, PDO::PARAM_STR );
			$st->bindValue( ":quantity", $this->quantity, PDO::PARAM_STR );
			$st->bindValue( ":updated", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

		public function delete() 
		{
			if ( is_null( $this->orderid ) ) trigger_error ( "order::delete(): Attempt to delete an order object that does not have its ID property set.", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$st = $conn->prepare ( "DELETE FROM orders WHERE orderid = :orderid LIMIT 1" );
			$st->bindValue( ":orderid", $this->orderid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

	}
