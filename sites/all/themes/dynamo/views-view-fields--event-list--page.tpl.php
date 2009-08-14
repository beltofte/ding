<!-- views-view-fields  event-list  page.tpl.php-->
<?php 
	//dsm($fields);
  //converts the date value to time
  $date = strtotime($fields['field_datetime_value']->raw);
?>
<div class="node-teaser clearfix">

  <div class="picture">
    <?php print $fields['field_image_fid']->content; ?>  
  </div>

  <div class="info">
  
		<h2><?php print $fields['title']->content; ?></h2>

		<div class="meta">
		  <?php print date("l j M h:i", $date); ?>
		  <span class="libary-tag"><?php print $fields['field_library_ref_nid']->content; ?></span>, 
		  <span class="price"><?php print $fields['field_entry_price_value']->content; ?></span>
		</div>    
    
		<p> 
		 <?php 
	 			if($fields['field_teaser_value']->content){
					print $fields['field_teaser_value']->content; 

				}else{
					print $fields['body']->content;       
	 			}
 			?>
		</p> 
		
    <?php print $fields['edit_node']->content; ?>  
  </div>

</div>