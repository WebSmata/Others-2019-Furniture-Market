<?php

	require( "config.php" );
	
	session_start();
	$thispage = isset( $_GET['thispage'] ) ? $_GET['thispage'] : "";

	$content = array();
	$content['sitename'] = strlen(as_option('sitename')) ? as_option('sitename') : SITENAME;
	$userid = isset( $_SESSION['furniture_user'] ) ? $_SESSION['furniture_user'] : "";
	$level = isset( $_SESSION['furniture_level'] ) ? $_SESSION['furniture_level'] : "";
	$fullname = isset( $_SESSION['furniture_name'] ) ? $_SESSION['furniture_name'] : "There";
	
	switch ( $thispage ) {
		case 'signin':
			$content['user'] = new user;
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?thispage='.$thispage,
					'fields' => array( 
						'handle' => array('label' => 'Username:', 'type' => 'text'),				
						'password' => array('label' => 'Password:', 'type' => 'password'),
					),
			
					'buttons' => array('signin' => array('label' => 'Login')),			
				);
			
			$content['title'] = "Login to Your Account";
			if ( isset( $_POST['signin'] ) ) {
				$userid = user::signinuser($_POST['handle'], md5($_POST['password']));
				if ($userid) {
					header( "Location: index.php?status=welcome" );
				}	else {
					header( "Location: index.php?thispage=signin&&error=loginFailed" );
				}
			}
			break;

		case 'register':
			$content['user'] = new user;			
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?thispage='.$thispage,
					'fields' => array( 
						'fullname' => array('label' => 'Full Name:', 'type' => 'text', 'tags' => 'required '),
						'level' => array('label' => 'Signing up as:', 'type' => 'radio', 
							'options' => array(
								'buyer' => array('name' => 'Buyer (Customer)', 'value' => 3),
								'seller' => array('name' => 'Seller (Workshop)', 'value' => 4),
								), 'value' => 3, 'tags' => 'required '),
						'mobile' => array('label' => 'Mobile:', 'type' => 'text', 'tags' => 'required '),
						'email' => array('label' => 'Email:', 'type' => 'email', 'tags' => 'required '),
						'handle' => array('label' => 'Username:', 'type' => 'text', 'tags' => 'required '),
						'password' => array('label' => 'Password:', 'type' => 'password', 'tags' => 'required '),
					),
							
					'buttons' => array('register' => array('label' => 'Register')),
				);
			
			$content['title'] = "Register your account";
			if ( isset( $_POST['register'] ) ) {
				$user = new user;
				$user->storeFormValues( $_POST );
				$userid = $user->insert();
				if ($userid) {
					$_SESSION['furniture_level'] = $_POST['level'];
					$_SESSION['furniture_name'] = $_POST['fullname'];
					$_SESSION['furniture_user'] = $userid;
					header( "Location: index.php" );
				} else {
					$content['errorMessage'] = "Unable to register you at the moment. Please try again later.";
				}
			}
			break;
		
		case 'orders_all':
			if ( !$userid ) header( "Location: index.php?thispage=signin" );
			switch ($level) {
				case 3:
					$content['title'] = "Orders you made";
					break;
					
				case 4:
					$content['title'] = "Orders you received";		
					break;
					
				case 5:
					$content['title'] = "Orders received";			
					break;
			}
			$orders = order::getList('ordered', 0, $userid, $level);					
			$listitems = array();
			foreach ( $orders as $order ) {
				$listitems[$order->orderid] = array($order->furniture, $order->quantity, $order->price.'/=', 
				($order->quantity * $order->price).'/=');
				if ($level === 3) $listitems[$order->orderid][] = $order->customer;
			}
			
			$content['page'] = array(
				'type' => 'table',
				'headers' => array( 'furniture', 'qty', 'price', 'amount' ),
				'items' => $listitems,
			);
			if ($level === 3) $content['page']['headers'][] = 'customer';
			
			break;
		
		case 'account':
			if ( !$userid ) header( "Location: index.php?thispage=signin" );
			$content['user'] = user::getById( (int)$_SESSION["furniture_user"] );
			$content['title'] = $content['user']->firstname . ' ' .$content['user']->lastname.
			' '.($content['user']->sex == 1 ? '(M)' : '(F)' );
			break;
			
		case 'signout';
			session_destroy();
			header( "Location: index.php?thispage=signin" );
			break;
				
		case 'database';
			errMissingTables();
			break;
		  			
		case 'customers':
			if ( !$userid ) header( "Location: index.php?thispage=signin" );
			$users = user::getList(1);
			$listitems = array();
			foreach ( $users['results'] as $user ) {
				$listitems[] = array($user->firstname. ' ' . $user->lastname, $user->handle, ($user->sex ==1) ? 'M' : 'F', $user->mobile, $user->email);
			}
			
			$content['title'] = "Customers";
			$content['page'] = array(
					'type' => 'table',
					'headers' => array( 'Name', 'username', 'sex', 'mobile phone', 'email'), 
					'items' => $listitems,
				);
			break;
		
		case 'settings':
			if ( !$userid ) header( "Location: index.php?thispage=signin" );
			$content['title'] = "Your Site Preferences";
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?thispage='.$thispage,
					'fields' => array( 
						'sitename' => array('label' => 'Site Name:', 'type' => 'text', 'tags' => 'required ', 'value' => $content['sitename']),
					),
					
					'hidden' => array('level' => 1),		
					'buttons' => array(
						'saveChanges' => array('label' => 'Save Changes'),
					),
				);
			
			if ( isset( $_POST['saveChanges'] ) ) {
				$sitename = $_POST['sitename'];
				as_update_option('sitename', $sitename);
				
				$filename = "config.php";
				$lines = file($filename, FILE_IGNORE_NEW_LINES );
				$lines[12] = '	define( "SITENAME", "'.$sitename.'"  );';
				file_put_contents($filename, implode("\n", $lines));
		
				header( "Location: index.php?pg=settings&&status=changesSaved" );
			} 
			break;
		 			
		case 'workshops':
			$users = user::getList(5);
			$listitems = array();
			foreach ( $users['results'] as $user ) {
				$listitems[$user->userid] = array($user->fullname.' @'.$user->handle, $user->mobile, $user->email);
			}
			
			$content['title'] = "Workshops";
			$content['page'] = array(
					'type' => 'table',
					'headers' => array( 'FullName', 'mobile phone', 'email'), 
					'items' => $listitems,
					'onclick' => 'thispage=furnitures_all&&workshop=',
				);
			break;
				
		case 'furniture_view':
			$furnitureid = $_GET["furnitureid"];
			$furniture = furniture::getById( (int)$furnitureid );
			
			$content['title'] = $furniture->title;
			if ($userid == $furniture->workshop || $level == 5)
			$content['link'] = '<a href="index.php?thispage=furniture_edit&&furnitureid='.$furnitureid.'" style="float:right;">EDIT POST</a>';		
			$content['page'] = array(
					'type' => 'viewer',
					'items' => array($furniture->furnitureid, $furniture->image, $furniture->title, $furniture->price, $furniture->content, $furniture->place, $furniture->workshop),
					
					'form' => array(
						'action' => 'index.php?thispage=furniture_view&&furnitureid='.$furnitureid,
						'fields' => array(
							'quantity' => array('label' => 'Quantity:', 'type' => 'number', 'value' => 1, 'tags' => 'required min="1" '),
						),
						
						'hidden' => array(
							'furniture' => $furnitureid, 
							'customer' => $userid
						),		
						'buttons' => array(
							'ordernow' => array('label' => 'Place an Order'),
						),
					),
				);
				
				switch ($level) {
					case 3:
						$content['page']['subtitle'] = 'Order for this Furniture';
						break;
						
					case 4:
						$content['title'] = count($listitems)." posts";	
						$content['page']['form'] = null;
						//$content['link'] = '<a href="index.php?thispage=furniture_new" style="float:right">New Post</a>';
						break;
						
					case 5:
						$content['page']['form'] = null;
						//$content['link'] = '<a href="index.php?thispage=furniture_new" style="float:right">New Post</a>';
						break;
					
					default:
						$content['page']['subtitle'] = 'Login to Order for this Furniture';
						$content['page']['form'] = null;
						break;				
				}
				
				if ( isset( $_POST['ordernow'] ) ) {
					$order = new order;
					$order->storeFormValues( $_POST );
					$orderid = $order->insert();
					if ($orderid) {
						header( "Location: index.php?thispage=orders_all&&status=orderreceived" );
					} else {
						$content['errorMessage'] = "Unable to order a furniture at the moment Please try again later.";
					}
				} elseif ( isset( $_POST['cancel'] ) ) {
					header( "Location: index.php?thispage=furniture_view&&furnitureid=".$furnitureid );
				}
			break;
			
		case 'furniture_edit':
			if ( !$userid ) header( "Location: index.php?thispage=signin" );
			$furnitureid = $_GET["furnitureid"];
			$furniture = furniture::getById( (int)$furnitureid );
			$content['title'] = "Edit Furniture";
			$content['link'] = '<a href="index.php?thispage=furniture_delete&&furnitureid='.$furnitureid.'" onclick="return confirm(\'Delete This Furniture? This action is irrevesible!\')" style="float:right;">DELETE JOB</a>';	
			$content['page'] = array(
					'type' => 'form',
					'action' => 'index.php?thispage='.$thispage.'&&furnitureid='.$furnitureid,
					'fields' => array(
						'title' => array('label' => 'Title:', 'type' => 'text', 'tags' => 'required ', 'value' => $furniture->title),
						'price' => array('label' => 'Salary:', 'type' => 'text', 'tags' => 'required ', 'value' => $furniture->price),
						'image' => array('label' => 'Company:', 'type' => 'text', 'tags' => 'required ', 'value' => $furniture->image),
						'content' => array('label' => 'Requirements:', 'type' => 'textarea', 'tags' => 'required ', 'value' => $furniture->content),
						'workshop' => array('label' => 'Skills:', 'type' => 'textarea', 'tags' => 'required ', 'value' => $furniture->workshop),
						'place' => array('label' => 'Description:', 'type' => 'textarea', 'tags' => 'required ', 'value' => $furniture->place),
					),
					
					'hidden' => array('level' => 1),		
					'buttons' => array(
						'saveChanges' => array('label' => 'Save Changes'),
						'cancel' => array('label' => 'Cancel Changes'),
					),
				);
			
			if ( isset( $_POST['saveChanges'] ) ) {
				$furniture->storeFormValues( $_POST );
				$furniture->update();
				header( "Location: index.php?thispage=furniture_view&&furnitureid=".$furnitureid."status=changesSaved" );
			} elseif ( isset( $_POST['cancel'] ) ) {
				header( "Location: index.php?thispage=furniture_view&&furnitureid=".$furnitureid );
			} 
			break;
		
		case 'furniture_new':
			if ( !$userid ) header( "Location: index.php?thispage=signin" );
			$content['furniture'] = new furniture;			
			$content['page'] = array(
					'type' => 'form',
					'tags' => ' enctype="multipart/form-data"',
					'action' => 'index.php?thispage='.$thispage,
					'fields' => array(
						'title' => array('label' => 'Descriptive Title:', 'type' => 'text', 'tags' => 'required '),
						'price' => array('label' => 'Price (Kshs):', 'type' => 'number', 'tags' => 'required '),
						'image' => array('label' => 'Furniture Image:', 'type' => 'file', 'tags' => 'required '),
						'content' => array('label' => 'Description:', 'type' => 'textarea', 'tags' => 'required '),
						'place' => array('label' => 'Location:', 'type' => 'text', 'tags' => 'required '),
					),
					
					'hidden' => array('workshop' => $userid),		
					'buttons' => array(
						'saveadd' => array('label' => 'Save & Add Furniture'),
						'saveclose' => array('label' => 'Save & Close'),
					),
				);
			
			$content['title'] = "Add a Post";
			if ( isset( $_POST['saveadd'] ) ) {
				$furniture = new furniture;
				$furniture->storeFormValues( $_POST );
				$furnitureid = $furniture->insert();
				if ($furnitureid) header( "Location: index.php?thispage=".$thispage );
				else $content['errorMessage'] = "Unable to add a furniture at the moment. Please try again later.";
			} else if ( isset( $_POST['saveclose'] ) ) {
				$furniture = new furniture;
				$furniture->storeFormValues( $_POST );
				$furnitureid = $furniture->insert();
				if ($furnitureid) header( "Location: index.php?thispage=furnitures_all" );
				else $content['errorMessage'] = "Unable to add a furniture at the moment. Please try again later.";
			}
			break;
					
		case 'furnitures_all':		
			if ( $userid ) $loggedinuser = isset( $_GET['workshop'] ) ? $_GET['workshop'] : $_SESSION['furniture_user'];
			else $loggedinuser = isset( $_GET['workshop'] ) ? $_GET['workshop'] : 0;
			
			$furnitures = furniture::getList('created', 0, $loggedinuser);
			$listitems = array();
			foreach ( $furnitures as $furniture ) {
				$image = $furniture->image ? '<img src="'.$furniture->image.'" width="40" height="40" style="border-radius:20px"/>' : '';
				$listitems[$furniture->furnitureid] = array($image, $furniture->title, $furniture->place, $furniture->price.' /=', $furniture->views);
			}
			switch ($level) {
				case 3:
					$content['title'] = count($listitems)." posts";	
					break;
					
				case 4:
					$content['title'] = count($listitems)." posts";	
					$content['link'] = '<a href="index.php?thispage=furniture_new" style="float:right">New Post</a>';
					break;
					
				case 5:
					$content['title'] = count($listitems)." posts";	
					$content['link'] = '<a href="index.php?thispage=furniture_new" style="float:right">New Post</a>';
					break;
				
				default:
					$content['title'] = count($listitems)." posts";	
					break;				
			}
			
			$content['page'] = array(
				'type' => 'table',
				'headers' => array( 'image', 'title', 'place', 'price', 'orders' ), 
				'items' => $listitems,
				'onclick' => 'thispage=furniture_view&&furnitureid=',
			);
			
			break;
				 
		default: 
			$search = isset( $_GET['q'] ) ? $_GET['q'] : "";
			$furnitures = furniture::searchThis($search);
			$listitems = array();
			foreach ( $furnitures as $furniture ) {
				$listitems[] = array($furniture->furnitureid, $furniture->image, $furniture->title, $furniture->price, $furniture->content, $furniture->place, $furniture->workshop);
			}
			
			if ($search) $content['title'] = count($furnitures). ' results found for "'.$search.'"';
			else $content['title'] = SITENAME;
			
			$content['page'] = array(
					'type' => 'search', 
					'items' => $listitems,
				);
				
			if ( isset( $_POST['searchNow'] ) ) {
				$searchthis = $_POST['searchText'];
				header( "Location: index.php?thispage=search&&q=" . $searchthis );
			} 
			break;
	}
	
	require ( CORE . "page.php" );