<?php 
//dsm($collection);
?>
<div id="ting-collection">
	<div class="content-left">

		<div class="tab-navigation">

			<ul>
				<li class="active"><a href="#">Materialer</a></li>
				<li><a href="#">Forfatterportræt </a></li>
				<li  class="active"><a href="#">Anmeldelser </a></li>				
				<li class="active"><a href="#">Materialer</a></li>
				<li><a href="#">Forfatterportræt </a></li>
				<li  class="active"><a href="#">Anmeldelser </a></li>				
				<li class="active"><a href="#">Materialer</a></li>
				<li><a href="#">Forfatterportræt </a></li>
				<li  class="active"><a href="#">Anmeldelser </a></li>				
			</ul>

		</div>

		<div class="tab-navigation-main">
			<div class="tab-navigation-main-inner">

				<?php
				foreach ($collection->objects as $key => $value) {
				//	dsm($key);
				?>
				
				<?php if($key == "0"){ ?>


					<div class="ting-overview clearfix">

						<div class="left-column left">
				  		<div class="picture">
								<?php // if($object->data->additionalInformation->thumbnailUrl){ ?>
						    	<?php 
										// 	TODO set false to true ?
								print theme('image', $collection->objects[$key]->additionalInformation->detailUrl, '', '', null, false);
						 			?>
								<?php // } ?>
							</div>


						</div>

						<div class="right-column left">
							<h2><?php print $collection->objects[$key]->data->title['0'];?></h2>
							<?php print theme('item_list', $collection->objects[$key]->data->creator, t('by'), 'span', array('class' => 'creator'));?> 
							(<?php print theme('item_list', $collection->objects[$key]->data->date, NULL, 'span', array('class' => 'date'));?>	)
							<p><?php print $collection->objects[$key]->data->description[0];?></p>
						</div>

					</div>


					<div class="collection-info">
						
						<div class="object-information clearfix">
							<?php print theme('item_list', $collection->objects[$key]->data->subject, t('Terms'), 'span', array('class' => 'subject'));?>

							<?php print theme('item_list', $collection->objects[$key]->data->type, t('Type'), 'span', array('class' => 'type'));?>

							<?php print theme('item_list', $collection->objects[$key]->data->format, t('Format'), 'span', array('class' => 'format'));?>

							<?php print theme('item_list', $collection->objects[$key]->data->source, t('Source'), 'span', array('class' => 'source'));?>

							<?php print theme('item_list', $collection->objects[$key]->data->publisher, t('Publisher'), 'span', array('class' => 'publisher'));?>

							<?php print theme('item_list', $collection->objects[$key]->data->language, t('Language'), 'span', array('class' => 'language'));?>

							<?php print l(t('More information'), $collection->objects[$key]->url, array('attributes' => array('class' => 'more-link')) ); ?>
						</div>

					</div>
				<?php }
				else{ ?>

				<div class="collection clearfix">

		  		<div class="picture">
						<?php if($collection->objects[$key]->additionalInformation->thumbnailUrl){ ?>
				    	<?php 
								// 	TODO set false to true ?
								$image = theme('image', $collection->objects[$key]->additionalInformation->thumbnailUrl, '', '', null, false);
								print l($image, $collection->objects[$key]->url, $options= array('html'=>TRUE ) );
				 			?>
					    <?php // print theme('imagecache', '120_120', $collection->objects[$key]->additionalInformation->thumbnailUrl); ?>      
						<?php } ?>
					</div>

				  <div class="content">
						<h5><?php print l($collection->objects[$key]->data->title['0'], $collection->objects[$key]->url ); ?></h5>
						<?php print theme('item_list', $collection->objects[$key]->data->date, NULL, 'div-span', array('class' => 'date'));?>	
						<?php print theme('item_list', $collection->objects[$key]->data->creator, t('by'), 'span', array('class' => 'creator'));?>
						<?php print $collection->objects[$key]->data->description[0]; ?>
						<?php //print theme('item_list', format_danmarc2($collection->objects[$key]->data->description), t('Description'), 'span', array('class' => 'description'));?>



						<?php print theme('item_list', $collection->objects[$key]->data->subject, t('Terms'), 'span', array('class' => 'subject'));?>

						<?php print theme('item_list', $collection->objects[$key]->data->type, t('Type'), 'span', array('class' => 'type'));?>

						<?php print theme('item_list', $collection->objects[$key]->data->format, t('Format'), 'span', array('class' => 'format'));?>

						<?php print theme('item_list', $collection->objects[$key]->data->source, t('Source'), 'span', array('class' => 'source'));?>

						<?php print theme('item_list', $collection->objects[$key]->data->publisher, t('Publisher'), 'span', array('class' => 'publisher'));?>

						<?php print theme('item_list', $collection->objects[$key]->data->language, t('Language'), 'span', array('class' => 'language'));?>

						<?php print l(t('More information'), $collection->objects[$key]->url, array('Attributes' => array('class' => 'more-link')) ); ?>

						<ul class="types">
							<li class="out">out</li>
							<li class="reserved">reserved</li>					
							<li class="available">available</li>										
						</ul>

					</div>
					<div class="cart">

						<ul>
							<li class="button button-orange"><a href="">Reserver nu</a></li>
							<li class="button button-green"><a href="">Læg i kurv</a></li>
						</ul>

					</div>

				</div>

				<?php 
					}//else
				} //foreach collection
				?>

			
			</div>	
		</div>
	</div>

	<div class="content-right">
	 KAmpagner
	</div>
	
</div>	