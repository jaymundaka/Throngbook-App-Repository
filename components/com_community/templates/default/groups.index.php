<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @param	author		string
 * @param	categories	An array of category objects.
 * @param	category	An integer value of the selected category id if 0, not selected. 
 * @params	groups		An array of group objects.
 * @params	pagination	A JPagination object.  
 * @params	isJoined	boolean	determines if the current browser is a member of the group 
 * @params	isMine		boolean is this wall entry belong to me ?
 * @params	config		A CConfig object which holds the configurations for Jom Social
 * @params	sorttype	A string of the sort type. 
 */
defined('_JEXEC') or die();
?>

<div class="cLayout clrfix">
	<?php
	if( $featuredList )
	{
	?>
	<div class="cRow">
	<div class="ctitle"><?php echo JText::_('COM_COMMUNITY_FEATURED_GROUPS');?></div>
		<div id="cFeatured" class="forGroup">
			<?php
				foreach($featuredList as $group)
				{
			?>
			<div class="cFeaturedItem">
				<div class="cBoxPad clrfix">
					<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid=' . $group->id );?>" class="cFeaturedThumb jomTips" 
					title="
						<?php echo $this->escape($group->name);?> 
						::
						<small>
							<?php echo JText::sprintf('COM_COMMUNITY_GROUPS_CREATE_TIME_ON' , JHTML::_('date', $group->created, JText::_('DATE_FORMAT_LC')) );
						?>
						</small>
						<hr />
						<small>
							<?php echo JText::sprintf((CStringHelper::isPlural($group->membercount)) ? 'COM_COMMUNITY_GROUPS_MEMBER_COUNT_MANY':'COM_COMMUNITY_GROUPS_MEMBER_COUNT', $group->membercount);?>
						</small>
					">
		            	<img class="avatar" src="<?php echo $group->getAvatar();?>" alt="<?php echo $this->escape($group->name);?>" />
		            	<span class="cFeaturedOverlay">star</span>
		            </a>
					<?php
					if( $isCommunityAdmin )
					{
					?>
					<div class="album-actions small" style="display: none;">	        
						<a onclick="joms.featured.remove('<?php echo $group->id;?>','groups');" href="javascript:void(0);" title="<?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?>" class="album-action remove-featured"><?php echo JText::_('COM_COMMUNITY_REMOVE_FEATURED'); ?></a>
					</div>
					<?php
					}
					?>
				</div>
			</div>
			<?php
				}
			?>
		</div>
		<div class="clr"></div>
	</div>
	<?php
	}
	?>

	<?php if ( $index ) : ?>
	<div class="cRow">
		<div class="ctitle"><?php echo JText::_('COM_COMMUNITY_CATEGORIES');?></div>
		<ul class="cResetList c3colList">
			<li>
			<?php if( $category->parent == COMMUNITY_NO_PARENT && $category->id == COMMUNITY_NO_PARENT ){ ?>
				<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups');?>"><?php echo JText::_( 'COM_COMMUNITY_GROUPS_ALL_GROUPS' ); ?></a>
			<?php }else{ ?>
				<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&categoryid=' . $category->parent ); ?>"><?php echo JText::_('COM_COMMUNITY_BACK_TO_PARENT'); ?></a>
			<?php }  ?>
			</li>
			<?php if( $categories ): ?>
				<?php foreach( $categories as $row ): ?>
					<li>
						<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&categoryid=' . $row->id ); ?>"><?php echo JText::_( $this->escape($row->name) ); ?></a> <?php if( $row->count > 0 ){ ?>( <?php echo $row->count; ?> )<?php } ?>
					</li>
				<?php endforeach; ?>
			<?php else: ?>
				<?php if( $category->parent == COMMUNITY_NO_PARENT && $category->id == COMMUNITY_NO_PARENT ){ ?>
					<li>
						<?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY_NOITEM'); ?>
					</li>
				<?php } ?>
			<?php endif; ?>
		</ul>
		<div class="clr"></div>
	</div>
	<?php endif; ?>

	
	<?php echo $sortings; ?>
    
	<?php echo $discussionsHTML;?>
	
	<!-- ALL GROUP LIST -->
	<div class="cMain clrfix">
		<?php echo $groupsHTML;?>
	</div>
    <!-- ALL GROUP LIST -->
    
    <div class="clr"></div>
</div>