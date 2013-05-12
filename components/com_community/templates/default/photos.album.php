<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @param	applications	An array of applications object
 * @param	pagination		JPagination object 
 */
defined('_JEXEC') or die();
?>
<?php
if( $photos && $isOwner )
{
?>
<script type="text/javascript" src="<?php echo rtrim(JURI::root(),'/'); ?>/components/com_community/assets/ui.core.js"></script>
<script type="text/javascript" src="<?php echo rtrim(JURI::root(),'/'); ?>/components/com_community/assets/ui.sortable.js"></script>
<script type='text/javascript'>
joms.jQuery(document).ready(function(){
	joms.jQuery('#community-photo-items').sortable({
		cursor: 'move',
		start: function(event, ui) {
			ui.item.addClass('onDrag');
		},
		stop: function(event, ui) {
			//@rule: Reset the ordering so the next drag will not mess up
			var i = 0;
			joms.jQuery( '#community-photo-items' ).children().each( function(){
				joms.jQuery( this ).attr('id' , 'photo-' + i);
				i++;
			});
			ui.item.removeClass('onDrag');

			// Update all existing ordering.
			var items	= [];
			joms.jQuery( '#community-photo-items img' ).each( function(){
				var photoid	= joms.jQuery(this).attr('id').split('-');
				items.push('app-list[]=' + photoid[1] );
				i++;
			});
			
			// Hide action
			jax.call('community', 'photos,ajaxSaveOrdering', items.join('&') , joms.jQuery('#albumid').val() );
		}
	});
});
</script>
<?php
}
?>

<?php
if( !empty($album->description) )
{
?>
<div class="community-photo-desc">
	<strong><?php echo JText::_('COM_COMMUNITY_PHOTOS_ALBUM_DESC');?></strong><br />
	<?php echo $this->escape($album->description); ?>
</div>
<?php
}
?>

<div class="page-actions">
  <?php echo $bookmarksHTML;?>
  <div class="clr"></div>
</div>


<input type="hidden" name="albumid" value="<?php echo $album->id;?>" id="albumid" />
<div id="community-photo-items" class="photo-list-item">
	<?php
	if($photos)
	{	
		for( $i=0; $i<count($photos); $i++ )
		{
			$row =& $photos[$i];
	?>
		<div class="photo-item jomTips" id="photo-<?php echo $i;?>" title="<?php echo $this->escape($row->caption);?>">
			<a href="<?php echo $row->link;?>"><img class="jomTips" src="<?php echo $row->getThumbURI();?>" alt="<?php echo $this->escape($row->caption);?>" id="photoid-<?php echo $row->id;?>" /></a>
			<?php
			if( $isOwner )
			{
			?>
			<div class="photo-action">
				<a href="javascript:void(0);" title="<?php echo JText::_('COM_COMMUNITY_REMOVE');?>" onclick="joms.gallery.confirmRemovePhoto('<?php echo $row->id;?>');" class="remove"><?php echo JText::_('COM_COMMUNITY_REMOVE');?></a>
			</div>
			<?php
			}
			?>
		</div>
	<?php
		}
	}
	else
	{
	?>
		<div class="community-empty-list"><?php echo JText::_('COM_COMMUNITY_PHOTOS_NO_PHOTOS_UPLOADED');?></div>
	<?php
	}
	?>
</div>

<div class="community-album-location clrfix">
	<em><?php echo $album->location;?></em>
</div>

<?php if ($people): ?>
<div class="community-album-people clrfix">
<?php echo JText::_('COM_COMMUNITY_PHOTOS_IN_THIS_ALBUM'); ?>
	<div>
	<?php foreach($people as $peep): ?>
		<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $peep->id); ?>" rel="nofollow"><img src="<?php echo $peep->getThumbAvatar(); ?>" height="40" class="avatar jomTips" title="<?php echo $peep->getDisplayName(); ?>" /></a>
	<?php endforeach; ?>
	</div>
</div>

<div class="community-album-viewcount">
	<?php echo JText::_('COM_COMMUNITY_VIEWS');?>: <strong class="photoHitsText"><?php echo $album->hits;?></strong>
</div>
<?php endif; ?>

<div id="like-container" class="community-album-likes"><?php echo $likesHTML; ?></div>

<div class="clr"></div>

<div class="ctitle"><?php echo JText::_('COM_COMMUNITY_COMMENTS') ?></div>

    <div class="album-wall">
		<div id="wallForm"><?php echo $wallForm; ?></div>
		<div id="wallContent"><?php echo $wallContent; ?></div>
    </div>