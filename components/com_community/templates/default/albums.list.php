<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 */
defined('_JEXEC') or die();
?>

<div class="albums">
	<?php
	if( $albums )
	{   
	  $i = 0;
	  foreach($albums as $album)
		{
	?>
			<div class="album">
		  	<div class="album">
		      <div class="album-cover">
		      	<a class="album-cover-link" href="<?php echo $album->link; ?>">
		      	   <img class="avatar" src="<?php echo $album->thumbnail; ?>" alt="<?php echo $this->escape($album->name);?>" />
		      	</a>
						<div class="album-actions small">
			        <?php if( $album->isOwner ) { ?>
			          <a class="album-action edit" title="<?php echo JText::_('COM_COMMUNITY_PHOTOS_EDIT');?>" href="<?php echo $albums[$i]->editLink; ?>"><?php echo JText::_('COM_COMMUNITY_PHOTOS_EDIT');?></a>
			          <a class="album-action upload" title="<?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD');?>" href="<?php echo $albums[$i]->uploadLink; ?>"><?php echo JText::_('COM_COMMUNITY_PHOTOS_UPLOAD');?></a>
			          <a class="album-action delete" title="<?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DELETE');?>" href="javascript:void(0);" onclick="cWindowShow('jax.call(\'community\',\'photos,ajaxRemoveAlbum\',\'<?php echo $albums[$i]->id;?>\',\'<?php echo $currentTask; ?>\');' , '<?php echo JText::_('COM_COMMUNITY_REMOVE');?>' , 450 , 150 );"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DELETE');?></a>
			        <?php } elseif( $isSuperAdmin ) { ?>
			          <a class="album-action edit" title="<?php echo JText::_('COM_COMMUNITY_PHOTOS_EDIT');?>" href="<?php echo $albums[$i]->editLink; ?>"><?php echo JText::_('COM_COMMUNITY_PHOTOS_EDIT');?></a>
			          <a class="album-action delete" title="<?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DELETE');?>" href="javascript:void(0);" onclick="cWindowShow('jax.call(\'community\',\'photos,ajaxRemoveAlbum\',\'<?php echo $album->id;?>\',\'<?php echo $currentTask; ?>\');' , '<?php echo JText::_('COM_COMMUNITY_REMOVE');?>' , 450 , 150 );"><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DELETE');?></a>
			        <?php } ?>

				<?php if( $isCommunityAdmin && $type == PHOTOS_USER_TYPE )	{ ?>
				<?php if( !in_array($album->id, $featuredList) ){ ?>
				<a class="album-action featured" title="<?php echo JText::_('COM_COMMUNITY_MAKE_FEATURED'); ?>" onclick="joms.featured.add('<?php echo $album->id;?>','photos');" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_MAKE_FEATURED'); ?></a>
				<?php	} ?>
				<?php	} ?>
			  		  <div class="clr"></div>
			      </div>
		      </div>
		  
				<div class="album-summary">
					<div class="album-name"><a href="<?php echo $album->link; ?>"><?php echo $this->escape($album->name); ?></a></div>
					<div class="album-count">
						<?php if(CStringHelper::isPlural($album->count)) {
							echo JText::sprintf('COM_COMMUNITY_PHOTOS_COUNT', $album->count );
						} else {
							echo JText::sprintf('COM_COMMUNITY_PHOTOS_COUNT_SINGULAR', $album->count );
						} ?>
						
					</div>
					<div class="album-lastupdated small"><?php echo $album->lastupdated; ?> <?php echo JText::_('COM_COMMUNITY_PHOTOS_BY'); ?> <a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid='.$album->creator); ?>"><?php echo $album->user->getDisplayName(); ?></a></div>
				</div>
		  
		      
		  	</div> 
		  </div>  
			
			<?php
			 // count every 4 albums, and insert clearing div so there's no wrapping bug
			 $i++;
		   if( $i % 4 == 0 ) { ?>
		    <div class="clr"></div>
		   <?php }
		} // end: foreach($albums as $album)
		
	}
	else
	{
	?>
	<div class="community-empty-list">
		<?php echo JText::_('COM_COMMUNITY_PHOTOS_NO_ALBUM_CREATED'); ?>
	</div>
	
	<?php
	} // end: if( $albums )
	?>
	<div class="clr"></div>
</div>
<!-- end .albums -->

<div class="clr"></div>

<div class="pagination-container">
	<?php echo $pagination->getPagesLinks(); ?>
</div>