<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @param	albums	An array of album objects.
 * @param	user	Current browser's CUser object. 
 * @params	isOwner		boolean Determines if the current photos view belongs to the browser
 */
defined('_JEXEC') or die();

if( $featuredList )
{
?>
<div class="ctitle"><?php echo JText::_('COM_COMMUNITY_FEATURED_ALBUMS');?></div>
<div id="cFeatured" class="listBy4">
<?php
  $x = 1;
	foreach($featuredList as $album)
	{
?>
<div class="cFeaturedItem">
  <div class="cBoxPad cBoxBorder">
    
		<div class="cFeaturedImg">
			<a href="<?php echo CRoute::_($album->getURI()); ?>">
				<img class="avatar" src="<?php echo $album->getCoverThumbPath();?>" alt="<?php echo $this->escape($album->name); ?>" />
				<span class="cFeaturedOverlay"><?php echo JText::_('COM_COMMUNITY_STAR'); ?></span>
			</a>
			<div class="cFeaturedTitle"><a href="<?php echo CRoute::_($album->getURI()); ?>"><?php echo $this->escape($album->name);?></a></div>
			<?php
			if( $isCommunityAdmin )
			{
			?>
			<div class="album-actions">
				<a class="album-action remove-featured" title="<?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?>" onclick="joms.featured.remove('<?php echo $album->id;?>','photos');" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?></a>
			</div>
			<?php
			}
			?>
    </div>
		
	</div>
</div>
  <?php
    if( $x % 4 == 0 )
		{
		echo '<div class="clr"></div>';
		}
  	$x++;
	} // end foreach
?>
	<div class="clr"></div>
</div>
<!-- end #cFeatured -->
	
<div class="ctitle"><?php echo JText::_('COM_COMMUNITY_PHOTOS_PHOTO_ALBUMS');?></div>
<?php
} // end if featured
?>


<div>
	<?php echo $albumsHTML; ?>
</div>