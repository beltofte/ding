<?php
/*
	dsm($variables['template_files']);
  dsm($node);
  dsm($node->content);
  print_r(get_defined_vars());
  print $FIELD_NAME_rendered;
*/
/*
ad a class="" if we have anything in the $classes var
this is so we can have a cleaner output - no reason to have an empty <div class="" id=""> 
*/
if($classes){
   $classes = ' class="' . $classes . ' clearfix"';
}

if($id_node){
  $id_node = ' id="' . $id_node . '"';  
}

// figure out if it's an event that has already occurred
$alertbox = null;
$event_end = format_date(strtotime($node->field_datetime[0]['value2']), 'custom', 'U');
if($event_end < format_date(time(), 'custom', 'U')) {
	$alertbox = '<div class="alert">' . t('NB! This event occurred in the past.') . '</div>';
}
?>

<!-- node-event.tpl-->
<?php if ($page == 0){ ?>
<div<?php print $id_node . $classes; ?>>

  <div class="picture">
    <?php print $field_image_rendered; ?>
  </div>

  <div class="content">

  	<?php if($node->title){	?>	
      <h3><?php print l($node->title, 'node/'.$node->nid); ?></h3>
  	<?php } ?>

  	<div class="meta">
      <?php print $field_datetime_rendered ?>
      <?php print $field_library_ref_rendered ?>          
        
  		<?php /* if (count($taxonomy)){ ?>
  		  <div class="taxonomy">
  	   	  <?php print $terms ?> 
  		  </div>  
  		<?php } */ ?>

      <?php print $field_entry_price_rendered ?>
  	</div>
	
    <?php print $node->content['body']['#value'];?>

	<?php
	// adding warning for event that has already occurred
	print $alertbox;
 	?>
    
  </div>

</div>
<?php }else{ 
//Content
?>

<div<?php print $id_node . $classes; ?>>

	<?php if($node->title){	?>	
	  <h2><?php print $title;?></h2>
	<?php } ?>

	<?php
	// adding warning for event that has already occurred
	print $alertbox;
	?>

	<div class="content">
		<p class="event-info">
			<?php print filter_xss($node->field_datetime[0]['view']); ?>	
			<?php print filter_xss($node->field_library_ref[0]['view']); ?>	

			<?php 
				if($node->field_entry_price[0]['value'] == "0"){
					print t('free');
				}else{
					print filter_xss($node->field_entry_price[0]['view']);
				}
			?>
		</p>
				
		<p>
			<?php print filter_xss($node->field_teaser[0]['view']); ?>					
		</p>

		<?php print $node->content['body']['#value']; ?>



		<?php // print $content ?>
	</div>
		
	<div class="meta">
		<span class="time">
			<?php print t('This event was created'); ?>
			<?php print format_date($node->created, 'custom', "j. F Y") ?> 
		</span>	
		<span class="author">
			<?php print t('by'); ?> <?php print theme('username', $node); ?>
		</span>	

		<?php if (count($taxonomy)){ ?>
		  <div class="taxonomy">
	   	  <?php print t("Tags:") . " " .  $terms ?> 
		  </div>  
		<?php } ?>
	</div>		
		
	<?php if ($links){ ?>
    <?php  print $links; ?>
	<?php } ?>
</div>
<?php } ?>