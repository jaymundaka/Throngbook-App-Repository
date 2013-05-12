<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 **/
defined('_JEXEC') or die();
?>


<div id="cHeading">

<div class="cProfile">	
	<table cellpadding="0" cellspacing="0">
	<tr>
		<td class="profile-avatar">
		<?php if( $isMine ): ?><a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&task=uploadAvatar'); ?>"><?php endif; ?><img src="<?php echo $profile->largeAvatar; ?>" alt="<?php echo $user->getDisplayName(); ?>" /><?php if( $isMine ): ?></a><?php endif; ?>

		<?php if( $config->get('enablevideos') ){ ?>
			<?php if( $config->get('enableprofilevideo') && ($videoid != 0) ){ ?>
				<div style="padding-top: 5px; text-align: center;"><a class="icon-videos" style="margin: 0;" onclick="joms.videos.playProfileVideo( <?php echo $profile->profilevideo; ?> , <?php echo $user->id; ?> )" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_VIDEOS_MY_PROFILE');?></a></div>
			<?php } ?>
		<?php } ?>
		
		
		</td>
		<td class="profile-summary">
			<span id="like-container"><?php echo $likesHTML; ?></span>
			<h2 class="profileName"><?php echo $user->getDisplayName(); ?></h2>
			
			<div class="clr"></div>
			
			<?php echo $adminControlHTML; ?>
			<div id="profileStatus">
				<div id="profileInner">
					<?php echo $user->getStatus(); ?>
					<div class="small"><?php echo $profile->posted_on; ?></div>
				</div>
			</div>
				
				<?php if( $isMine ) { ?>
					<div class="status-mine">
						<?php $userstatus->render(); ?>
					</div>
				<?php } ?>
			
			
				<table cellpadding="0" cellspacing="0">
					<?php if($config->get('enablekarma')){ ?>
					<tr class="profile-detail">
						<td class="profile-detail-title"><?php echo JText::_('COM_COMMUNITY_KARMA'); ?></td>
						<td><img src="<?php echo $karmaImgUrl; ?>" alt="" /></td>
					</tr>
					<?php } ?>

					<tr class="profile-detail">
						<td class="profile-detail-title"><?php echo JText::_('COM_COMMUNITY_PROFILE_VIEW'); ?></td>
						<td><?php echo JText::sprintf('COM_COMMUNITY_PROFILE_VIEW_RESULT', $user->getViewCount() ) ;?></td>
					</tr>

					<tr class="profile-detail">
					    <td class="profile-detail-title"><?php echo JText::_('COM_COMMUNITY_MEMBER_SINCE'); ?></td>
					    <td><?php echo JHTML::_('date', $registerDate , JText::_('DATE_FORMAT_LC2')); ?></td>
				    </tr>

				    <tr class="profile-detail">
						<td class="profile-detail-title"><?php echo JText::_('COM_COMMUNITY_LAST_LOGIN'); ?></td>
						<td><?php echo $lastLogin; ?></td>
				    </tr>
				</table>
		</td>
		</tr>
	<?php if( !$isMine ) { ?>
		<tr>
			<td colspan="2" class="profile-statusbox">
				<?php $userstatus->render(); ?>
			</td>
		</tr>
	<?php } ?>
	</table>
</div>

</div>
