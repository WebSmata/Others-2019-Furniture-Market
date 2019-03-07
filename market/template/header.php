<?php
			if ( isset( $_GET['error'] ) ) {
				switch ( $_GET['error'] ) {
					case "postNotFound":
						$content['errorMessage'] = "Error: post not found.";
						break;
					case "userNotFound":
						$content['errorMessage'] = "Error: user not found.";
						break;
					case "loginFailed":
						$content['errorMessage'] = "Incorrect username or password. Please try again.";
						break;
				}
			}

			if ( isset( $_GET['status'] ) ) {
				switch ( $_GET['status'] ) {
					case "welcome":
						$content['statusMessage'] = $_SESSION['furniture_name']." welcome online.";
						break;
						
					case "postsaved":
						$content['statusMessage'] = "A new Vacancy has been saved.";
						break;
					case "changesSaved":
						$content['statusMessage'] = "Your changes have been saved.";
						break;
					case "orderreceived":
						$content['statusMessage'] = "Your furniture order was successful.";
						break;
					
					case "postDeleted":
						$content['statusMessage'] = "That Vacancy has been deleted successfully!.";
						break;
					case "usersaved":
						$content['statusMessage'] = "A new user has been saved.";
						break;
					case "changesSaved":
						$content['statusMessage'] = "Your changes have been saved.";
						break;
					case "userDeleted":
						$content['statusMessage'] = "That user has been deleted successfully!.";
						break;
				}
			}
		?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title><?php echo (isset($content['title']) ? $content['title'].' - ' : '').$content['sitename'] ?></title>
		<link rel="stylesheet" type="text/css" href="template/style.css" />
	<?php if ( isset( $content['calendarScript'] ) ) { ?>
	<link rel="stylesheet" type="text/css" media="all" href="calendar/jsDatePick.css" />
        <script type="text/javascript" src="calendar/jsDatePick.full.1.1.js"></script>
		<?php echo $content['calendarScript']; ?>
	<?php } ?>	
	
		<script language="javascript" type="text/javascript">
		function clearText(field){

			if (field.defaultValue == field.value) field.value = '';
			else if (field.value == '') field.value = field.defaultValue;

		}
		</script>

	</head>
	<body>
	
<div id="tooplate_body_wrapper">
	<div id="tooplate_wrapper">
    	
        <div id="tooplate_top">
		<?php if ( isset( $content['errorMessage'] ) ) { ?>
					<div class="errorMessage"><?php echo $content['errorMessage'] ?></div>
				<?php } 
				if ( isset( $content['statusMessage'] ) ) { ?>
					<div class="statusMessage"><?php echo $content['statusMessage'] ?></div>
				<?php } ?>
		</div> <!-- end of tooplate_top -->    
        
        <div id="tooplate_header">
            <div id="site_title">
                <h1><a href="."><?php echo $content['sitename'] ?></a></h1>
            </div>
            <div id="header_right">Hello <?php echo $fullname ?><?php if ($userid) { ?> | <b>			
			<?php
			switch ($level) {
				case 3: echo 'CUSTOMER DASHBOARD';
					break;
				
				case 4: echo 'WORKSHOP DASHBOARD';	
					break;
								
				case 5: echo 'ADMIN DASHBOARD';	
					break;
				
			}
			?>			
			</b>
			<a href="index.php?thispage=signout" style="color:#fff"><b>Sign Out?</b></a><?php } ?>
                <div id="social_links">
                    <a href="#"><img src="template/images/mail.png" alt="Contact" /></a>
                    <a href="#"><img src="template/images/rss.png" alt="RSS" /></a>
                    <a href="#"><img src="template/images/twitter_01.png" alt="Twitter" /></a>            
                </div>
                <div id="tooplate_menu">
                   <ul>
						<?php echo as_navigation($thispage) ?>
					</ul>      	
                </div>
            </div>
            <div class="cleaner"></div>
        </div>
        <div id="tooplate_main">
        	<div class="cleaner h20"></div>
            