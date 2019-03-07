			<div class="tooplate_sidebar float_r">
				<div class="content_box lb">
                	<h3 class="gallery_title">Popular Items</h3>
                    <div class="box01"><span class="top"></span><span class="bottom"></span>
					<?php
					$furnitures = furniture::getList('views', 5);
					$listitems = array();
					foreach ( $furnitures as $furniture ) {
						echo '<a href="index.php?thispage=furniture_view&&furnitureid='.$furniture->furnitureid.'">';
						echo '<div class="box01_content">';
                        echo '<div class="image_wrapper"><span></span><img src="'.$furniture->image.'" width="40" height="40" style="border-radius:20px"/></div>';
                        echo '<h6>'.$furniture->title.' - '.$furniture->price.' /=</h6>';
                        echo '</div>';
                        echo '</a>';
					}
					?>
			
                    	
                    </div>
                </div>
            </div>
            
            <div class="cleaner h20"></div>
            
        	<div class="cleaner"></div>
            
        </div>
        
        <div id="tooplate_main_bottom"></div>
        
               
    </div> <!-- end of tooplate_wrapper -->
    
    <div id="tooplate_footer_wrapper">
        <div id="tooplate_footer">
            <center><?php echo $content['sitename'].' &copy; '.date('Y') ?>. All rights reserved. <?php echo (isset($_SESSION['furniture_name']) ? ' | You are logged in as <a href="index.php?action=account">' . $_SESSION['furniture_name'].'</a>' : '') ?></center>
        </div> <!-- end of tooplate_footer -->
    </div> <!-- end of tooplate_footer_wrapper -->
</div> <!-- end of tooplate_body_wrapper -->

</body>
</html>