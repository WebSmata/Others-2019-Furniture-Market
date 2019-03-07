<?php 

	function as_navigation($request, $html = '')
	{
		$userid = isset( $_SESSION['furniture_user'] ) ? $_SESSION['furniture_user'] : "";
		$level = isset( $_SESSION['furniture_level'] ) ? $_SESSION['furniture_level'] : "";
		$navigation = array();
		$navigation['furnitures'] = array('label' => 'Furnitures', 'url' => 'index.php?thispage=furnitures_all');
		$navigation['workshops'] = array('label' => 'Workshops', 'url' => 'index.php?thispage=workshops');
		if ( $userid ) {			
			switch ($level) {
				case 3:
					$navigation['orders'] = array('label' => 'Orders', 'url' => 'index.php?thispage=orders_all');
				
					break;
					
				case 4:
					$navigation['orders'] = array('label' => 'Orders', 'url' => 'index.php?thispage=orders_all');				
					break;
					
				case 5:
					$navigation['customers'] = array('label' => 'Customers', 'url' => 'index.php?thispage=customers');
					$navigation['settings'] = array('label' => 'Settings', 'url' => 'index.php?thispage=settings');				
					break;
			}
		} else {
			$navigation['signin'] = array('label' => 'Sign In', 'url' => 'index.php?thispage=signin');
			$navigation['register'] = array('label' => 'SignUp', 'url' => 'index.php?thispage=register');
		}			
		if (isset($navigation[$request])) $navigation[$request]['selected']=true;
		foreach ($navigation as $k => $a){
			if ( $k != 'home'){
				$html .= '<li><a '.(($request==$k) ? 'class="current" ': '').'href="'.$a['url'].'">'.$a['label'].'</a></li>'."\n\t\t";
			} else {
				$html .= '<li><a '.(empty($request) ? 'class="current" ': '').'href=".">Home</a></li>'."\n\t\t";
			}
		}
		return $html;
	}
	 
	include TEMPLATE . "header.php";
	$page = $content['page'];
?>
<div id="tooplate_content">   	
	<h2>
		<?php echo (isset($content['title']) ? $content['title'] : '') ?>
		<?php echo (isset($content['link']) ? $content['link'] : '') ?>
	</h2>
		<?php switch ($page['type']){
			case 'form':
			?>
		<div id="general_form"> 
					<form action="<?php echo $page['action']?>" method="post"<?php echo @$page['tags']?>>      
				<?php foreach($page['fields'] as $name => $field) { ?>
		<label class="label" for="<?php echo $name ?>"><?php echo $field['label'] ?></label>
				<?php 
					switch ($field['type']) {
						case 'radio':
							if (isset($field['options'])) {
								foreach ($field['options'] as $option) { ?>
		<label><input type="radio" <?php echo 'name="'.$name.'" value="'.@$option['value'].'" '. ($field['value'] == $option['value'] ? 'checked ' : '') ?>  <?php echo @$field['tags'] ?>/> <?php echo @$option['name'] ?> </label>
				<?php } ?>
		<?php }
							break;
						case 'select':
							if (isset($field['options'])) { ?>
						<select class="input_field" name="<?php echo $name  ?>" >
							<?php foreach ($field['options'] as $key => $value ) { ?>
					<option value="<?php echo $key ?>"><?php echo $value ?></option>
				<?php } ?>
				</select>
		<?php }
						break;
					case 'textarea': ?>
					<textarea id="text" name="<?php echo $name  ?>" rows="<?php echo $field['rows'] ?>" cols="<?php echo @$field['cols'] ?>" class="input_field"><?php echo @$field['value'] ?></textarea>
				<?php 
						break;
						
					default: ?>
		<input type="<?php echo $field['type'] ?>" name="<?php echo $name  ?>" value="<?php echo @$field['value'] ?>" autocomplete="off" class="input_field" <?php echo @$field['tags'] ?>/>
						<?php } ?><div class="cleaner h10"></div>
						
				<?php } ?>
		<?php 
				if (isset($page['hidden']))
					foreach ($page['hidden'] as $name => $hidden)
						echo '<input type="hidden" name="'.$name.'" value="'.$hidden.'" />';
							?>	
						<?php 
					foreach ($page['buttons'] as $name => $button)
						echo '<input type="submit" name="'.$name.'" value="'.$button['label'].'" class="submit_btn float_l"'.@$button['tags'].'/>'; ?>
			
					</form>
				</div>
		<?php break;
			case 'table': ?>
				<table class="tt_tb">
					<thead>
						<tr>
					<?php foreach($page['headers'] as $header) { ?>
				<th><?php echo $header ?></th>
					<?php } ?>
						</tr>
					</thead>
					<tbody>
					<?php foreach($page['items'] as $rid => $trow) { ?>
						<tr onclick="location='index.php?<?php echo $page['onclick'].$rid ?>'">
						<?php foreach($trow as $tdata) { ?>
							<td valign="top"><?php echo $tdata ?></td>
						<?php } ?>
						</tr>
					<?php } ?>
					</tbody>
				</table>			
		<?php break;
			case 'search': ?>
            <div id="search_box">
                <form action="index.php?thispage=search" method="post">
                    <input type="text" value="Enter keyword here..." name="searchText" size="10" id="searchfield" title="searchfield" onfocus="clearText(this)" onblur="clearText(this)" />
                    <input type="submit" name="searchNow" value="" id="searchbutton" title="Search" />
                </form>
            </div>
            <div class="cleaner"></div><br>
			<div class="col_large">
			<?php foreach($page['items'] as $item) { ?>
				<div><a href="index.php?thispage=furniture_view&&furnitureid=<?php echo $item[0] ?>">
				<table><tr><td><img src="<?php echo $item[1] ?>" width="100" height="100" style="border: 2px solid #000"/></td><td><h3><?php echo $item[2] ?></h3>
				<p>
					<?php echo $item[4] ?><br>Price: Kshs. <b><?php echo $item[3] ?></b> <br>Posted in <b><?php echo $item[5].'</b> by <b>'.$item[6] ?></b>
				</p></td></tr></table>
				</a></div>
			<?php } ?>
			</div>
            <div class="cleaner"></div>
		<?php break;
			case 'viewer': ?>
			<div class="col_large">
				<img src="<?php echo $page['items'][1] ?>" style="width:90%;border: 1px solid #000; border-radius:20px;"/><br><br>
				<h3>Price: Kshs <?php echo $page['items'][3] ?></h3>
				<h5>Description: <?php echo $page['items'][4] ?></h5>
				<?php if (isset($page['subtitle'])) { ?>
				<hr><h3><?php echo $page['subtitle'] ?></h3>
				<?php } ?>
			<div id="general_form"> 
				<?php if (isset($page['form'])) { ?>
					<form action="<?php echo $page['form']['action']?>" method="post">      
				<?php foreach($page['form']['fields'] as $name => $field) { ?>
		<label class="label" for="<?php echo $name ?>"><?php echo $field['label'] ?></label>
				<?php 
					switch ($field['type']) {
						case 'radio':
							if (isset($field['options'])) {
								foreach ($field['options'] as $option) { ?>
		<label><input type="radio" <?php echo 'name="'.$name.'" value="'.@$option['value'].'" '. ($field['value'] == $option['value'] ? 'checked ' : '') ?>  <?php echo @$field['tags'] ?>/> <?php echo @$option['name'] ?> </label>
				<?php } ?>
		<?php }
							break;
					case 'textarea': ?>
					<textarea name="<?php echo $name  ?>" id="<?php echo $name  ?>" class="input_field"><?php echo @$field['value'] ?></textarea>
				<?php 
						break;
						case 'select':
							if (isset($field['options'])) { ?>
						<select class="input_field" name="<?php echo $name  ?>" >
							<?php foreach ($field['options'] as $key => $value ) { ?>
					<option value="<?php echo $key ?>"><?php echo $value ?></option>
				<?php } ?>
				</select>
		<?php }
					break;
					case 'textarea': ?>
					<textarea id="text" name="<?php echo $name  ?>" rows="<?php echo $field['rows'] ?>" cols="<?php echo @$field['cols'] ?>" class="input_field"><?php echo @$field['value'] ?></textarea>
				<?php 
						default: ?>
		<input type="<?php echo $field['type'] ?>" name="<?php echo $name  ?>" id="<?php echo $name  ?>" value="<?php echo @$field['value'] ?>" class="input_field" <?php echo @$field['tags'] ?> autocomplete="off"/>
						<?php } ?><div class="cleaner h10"></div>
				<?php } ?>
		<?php 
				if (isset($page['form']['hidden']))
					foreach ($page['form']['hidden'] as $name => $hidden)
						echo '<input type="hidden" name="'.$name.'" value="'.$hidden.'" />';
							?>	
						<?php 
					foreach ($page['form']['buttons'] as $name => $button)
						echo '<input type="submit" name="'.$name.'" value="'.$button['label'].'" class="submit_btn float_l"'.@$button['tags'].'/>'; ?>
			
					</form>
			<?php } ?>
				</div> 
			</div>
		<?php default: ?>
		
		<?php } ?>
	</div>
<?php include TEMPLATE . "footer.php" ?>