<?php

	class furniture
	{ 
		public $furnitureid = null;
		public $title = null;
		public $price = null;
		public $image = null;
		public $content = null;
		public $place = null;
		public $views = null;
		public $workshop = null;
		public $created = null;
		public $updated = null;

		public function __construct( $data=array() ) 
		{
			if ( isset( $data['furnitureid'] ) ) $this->furnitureid = (int) $data['furnitureid'];
			if ( isset( $data['title'] ) ) $this->title =  $data['title'];
			if ( isset( $data['price'] ) ) $this->price = $data['price'];
			if ( isset( $data['image'] ) ) $this->image = $data['image'];
			if ( isset( $data['content'] ) ) $this->content = $data['content'];
			if ( isset( $data['place'] ) ) $this->place = $data['place'];
			if ( isset( $data['workshop'] ) ) $this->workshop = $data['workshop'];
			if ( isset( $data['views'] ) ) $this->views = $data['views'];
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

		public static function getById( $furnitureid ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "SELECT *, UNIX_TIMESTAMP(created) AS created FROM furnitures WHERE furnitureid = :furnitureid";
			$st = $conn->prepare( $sql );
			$st->bindValue( ":furnitureid", $furnitureid, PDO::PARAM_INT );
			$st->execute();
			$row = $st->fetch();
			$conn = null;
			if ( $row ) return new furniture( $row );
		}

		public static function getList( $sortby, $limit, $workshop = 0)
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = 'SELECT *, UNIX_TIMESTAMP(created) AS created, fullname AS workshop 
			FROM furnitures  
			INNER JOIN users ON users.userid = furnitures.workshop 
			'.($workshop != 0 ? 'WHERE workshop='.$workshop : '').' 
			ORDER BY '.$sortby.' DESC'.($limit != 0 ? ' LIMIT '.$limit : '');

			$st = $conn->prepare( $sql );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$furniture = new furniture( $row );
				$list[] = $furniture;
			}

			$conn = null;
			return $list;
		}

		public static function searchThis( $search ) 
		{
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = 'SELECT *, UNIX_TIMESTAMP(created) AS created, fullname AS workshop  
			FROM furnitures 
			INNER JOIN users ON users.userid = furnitures.workshop 
			WHERE title LIKE "%'.$search.'%" 
			ORDER BY created DESC';

			$st = $conn->prepare( $sql );
			$st->execute();
			$list = array();

			while ( $row = $st->fetch() ) {
				$furniture = new furniture( $row );
				$list[] = $furniture;
			}

			$conn = null;
			return $list;
		}
		
		function as_image_upload()
		{	
			$new_image = $_FILES['image']['name'];
			$image_error = $_FILES['image']['error'];
			$image_type = $_FILES['image']['type'];
			
			$allowedExts = array("gif", "jpeg", "jpg", "png");
			$extension = (explode(".", $_FILES["image"]["name"]));

			$merge = end($extension);
				
			if($image_error > 0) $error = "Image should be uploaded.";
			else if(!(($image_type == "image/gif") || ($image_type == "image/jpeg") || ($image_type == "image/jpg") || ($image_type == "image/x-png") ||
				($image_type == "image/png") || ($image_type == "image/pjpeg")) && !(in_array($merge, $allowedExts))){
				$error = "Image type should be jpg, jpeg, gif, or png.";
			} 
			if (empty($error)) {
				$file = preg_replace("/\s+/", "_", $_FILES['image']['name']);
				$new_image = "image_".time().".".$merge;

				$upload = move_uploaded_file($_FILES['image']['tmp_name'], 'files/'.$new_image);		
				return array(1, 'files/'.$new_image );
			} else return array(0, $error);
		}
	
		public function insert() 
		{
			$image = $this->as_image_upload();
			if ($image[0] == 1) {
				if ( !is_null( $this->furnitureid ) ) trigger_error ( "Attempt to insert an object that already has its ID property set (to $this->furnitureid).", E_USER_ERROR );

				$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
				$sql = "INSERT INTO furnitures ( title, price, image, content, place, workshop, created ) VALUES ( :title, :price, :image, :content, :place, :workshop, :created)";
				$st = $conn->prepare ( $sql );
				$st->bindValue( ":title", $this->title, PDO::PARAM_STR );
				$st->bindValue( ":price", $this->price, PDO::PARAM_STR );
				$st->bindValue( ":image", $image[1], PDO::PARAM_STR );
				$st->bindValue( ":content", $this->content, PDO::PARAM_STR );
				$st->bindValue( ":place", $this->place, PDO::PARAM_STR );
				$st->bindValue( ":workshop", $this->workshop, PDO::PARAM_STR );
				$st->bindValue( ":created", date('Y-m-d H:i:s'), PDO::PARAM_INT );
				$st->execute();
				$this->furnitureid = $conn->lastInsertId();
				$conn = null;
				return $this->furnitureid;
			} else trigger_error ( $image[1] );
		}

		public function update() 
		{
			if ( is_null( $this->furnitureid ) ) trigger_error ( "furniture::update(): Attempt to update an furniture object that does not have its ID property set.", E_USER_ERROR );
		   
			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$sql = "UPDATE furnitures SET title=:title, price=:price, image=:image, content=:content, place=:place, workshop=:workshop, runsfrom=:runsfrom, runstill=:runstill, updated=:updated WHERE furnitureid = :furnitureid";
			$st = $conn->prepare ( $sql );
			$st->bindValue( ":title", $this->title, PDO::PARAM_STR );
			$st->bindValue( ":price", $this->price, PDO::PARAM_STR );
			$st->bindValue( ":image", $this->image, PDO::PARAM_STR );
			$st->bindValue( ":content", $this->content, PDO::PARAM_STR );
			$st->bindValue( ":place", $this->place, PDO::PARAM_STR );
			$st->bindValue( ":workshop", $this->workshop, PDO::PARAM_STR );
			$st->bindValue( ":runsfrom", $this->runsfrom, PDO::PARAM_INT );
			$st->bindValue( ":runstill", $this->runstill, PDO::PARAM_INT );
			$st->bindValue( ":updated", date('Y-m-d H:i:s'), PDO::PARAM_INT );
			$st->bindValue( ":furnitureid", $this->furnitureid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

		public function delete() 
		{

			if ( is_null( $this->furnitureid ) ) trigger_error ( "furniture::delete(): Attempt to delete an furniture object that does not have its ID property set.", E_USER_ERROR );

			$conn = new PDO( DB_DSN, DB_USER, DB_PASS );
			$st = $conn->prepare ( "DELETE FROM furnitures WHERE furnitureid = :furnitureid LIMIT 1" );
			$st->bindValue( ":furnitureid", $this->furnitureid, PDO::PARAM_INT );
			$st->execute();
			$conn = null;
		}

	}
