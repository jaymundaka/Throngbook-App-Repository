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


<form method="post" action="<?php echo CRoute::getURI();?>" name="jsform-profile-privacy">

<div class="ctitle"><h2><?php echo JText::_('COM_COMMUNITY_EDIT_YOUR_PRIVACY');?></h2></div>
<p><?php echo JText::_('COM_COMMUNITY_EDIT_PRIVACY_DESCRIPTION');?></p>

<table class="formtable" cellspacing="1" cellpadding="0">
<?php echo $beforeFormDisplay;?>
<!-- profile privacy -->
<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_PROFILE_FIELD');?></label>
	</td>
	<td class="privacyc"><?php echo CPrivacy::getHTML( 'privacyProfileView' , $params->get( 'privacyProfileView' ) , COMMUNITY_PRIVACY_BUTTON_LARGE , array( 'public' => true , 'members' => true , 'friends' => true , 'self' => false ) ); ?></td>
	<td></td>
</tr>


<!-- friends privacy -->
<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_FRIENDS_FIELD'); ?></label>
	</td>
	<td class="privacy"><?php echo CPrivacy::getHTML( 'privacyFriendsView' , $params->get( 'privacyFriendsView' ) , COMMUNITY_PRIVACY_BUTTON_LARGE ); ?></td>
	<td></td>
</tr>


<!-- photos privacy -->
<?php if($config->get('enablephotos')): ?>
<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_PHOTOS_FIELD'); ?></label>
	</td>
	<td class="privacy"><?php echo CPrivacy::getHTML( 'privacyPhotoView' , $params->get( 'privacyPhotoView' ) , COMMUNITY_PRIVACY_BUTTON_LARGE ); ?></td>
	<td class="value"><input type="checkbox" name="resetPrivacyPhotoView" /> <?php echo JText::_('COM_COMMUNITY_PHOTOS_PRIVACY_APPLY_TO_ALL'); ?></td>
</tr>
<?php endif;?>

<!-- videos privacy -->
<?php if($config->get('enablevideos')): ?>
<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_VIDEOS_FIELD'); ?></label>
	</td>
	<td class="privacy"><?php echo CPrivacy::getHTML( 'privacyVideoView' , $params->get( 'privacyVideoView' ) , COMMUNITY_PRIVACY_BUTTON_LARGE ); ?></td>
	<td class="value"><input type="checkbox" name="resetPrivacyVideoView" /> <?php echo JText::_('COM_COMMUNITY_VIDEOS_PRIVACY_RESET_ALL'); ?></td>
</tr>
<?php endif; ?>


<?php if( $config->get( 'enablegroups' ) ){ ?>
<!-- groups privacy -->
<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_GROUPS_FIELD'); ?></label>
	</td>
	<td class="privacy"><?php echo CPrivacy::getHTML( 'privacyGroupsView' , $params->get( 'privacyGroupsView' ) , COMMUNITY_PRIVACY_BUTTON_LARGE ); ?></td>
	<td></td>
</tr>
<?php } ?>
</table>


<div class="ctitle"><h2><?php echo JText::_('COM_COMMUNITY_EDIT_EMAIL_PRIVACY'); ?></h2></div>

<table class="formtable" cellspacing="1" cellpadding="0">

<!-- system email -->
<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_RECEIVE_SYSTEM_MAIL'); ?></label>
	</td>
	<td class="value">
    	<input name="notifyEmailSystem" id="email-privacy-yes" type="radio" value="1" <?php if($params->get('notifyEmailSystem') == 1) { ?>checked="checked" <?php } ?> />
        <label for="email-privacy-yes" class="lblradio"><?php echo JText::_('COM_COMMUNITY_PRIVACY_YES'); ?></label>
        
        <input type="radio" value="0" id="email-privacy-no" name="notifyEmailSystem" <?php if($params->get('notifyEmailSystem') == 0) { ?>checked="checked" <?php } ?> />
        <label for="email-privacy-no" class="lblradio"><?php echo JText::_('COM_COMMUNITY_PRIVACY_NO'); ?></label>
	</td>
</tr>

<!-- apps email -->
<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_RECEIVE_APPLICATION_MAIL'); ?></label>
	</td>
	<td class="value">
    	<input type="radio" value="1" id="email-apps-yes" name="notifyEmailApps" <?php if($params->get('notifyEmailApps') == 1) { ?>checked="checked" <?php } ?>/>
        <label for="email-apps-yes" class="lblradio"><?php echo JText::_('COM_COMMUNITY_PRIVACY_YES'); ?></label>
		        
        <input type="radio" value="0" id="email-apps-no" name="notifyEmailApps" <?php if($params->get('notifyEmailApps') == 0) { ?>checked="checked" <?php } ?>/>
        <label for="email-apps-no" class="lblradio"><?php echo JText::_('COM_COMMUNITY_PRIVACY_NO'); ?></label>
	</td>
</tr>

<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_RECEIVE_COMMENT_MAIL'); ?></label>
	</td>
	<td class="value">
    	<input type="radio" value="1" id="email-wallcomment-yes" name="notifyWallComment" <?php if($params->get('notifyWallComment') == 1) { ?>checked="checked" <?php } ?>/>
        <label for="email-wallcomment-yes" class="lblradio"><?php echo JText::_('COM_COMMUNITY_PRIVACY_YES'); ?></label>
		        
        <input type="radio" value="0" id="email-wallcomment-no" name="notifyWallComment" <?php if($params->get('notifyWallComment') == 0) { ?>checked="checked" <?php } ?>/>
        <label for="email-wallcomment-no" class="lblradio"><?php echo JText::_('COM_COMMUNITY_PRIVACY_NO'); ?></label>
	</td>
</tr>
<?php
if( $config->get('privacy_search_email') == 1 )
{
?>
<tr>
	<td class="key" style="width: 200px;">
		<label class="label"><?php echo JText::_('COM_COMMUNITY_PRIVACY_EMAIL'); ?></label>
	</td>
	<td class="value">
    	<input type="radio" value="1" id="search-email-yes" name="search_email" <?php if($my->get('_search_email') == 1) { ?>checked="checked" <?php } ?>/>
        <label for="search-email-yes" class="lblradio"><?php echo JText::_('COM_COMMUNITY_PRIVACY_YES'); ?></label>

        <input type="radio" value="0" id="search-email-no" name="search_email" <?php if($my->get('_search_email') == 0) { ?>checked="checked" <?php } ?>/>
        <label for="search-email-no" class="lblradio"><?php echo JText::_('COM_COMMUNITY_PRIVACY_NO'); ?></label>
	</td>
</tr>
<?php
}
?>
<?php echo $afterFormDisplay;?>
<tr>
	<td class="key"></td>
	<td class="value">
		<input type="hidden" value="save" name="action" />
		<input type="submit" class="button" value="<?php echo JText::_('COM_COMMUNITY_SAVE_BUTTON'); ?>" />
	</td>
</tr>
</table>

</form>

<div id="community-banlists-wrap" style="padding-top: 20px;">
	
	<div id="community-banlists-news-items" class="app-box" style="width: 100%; float: left;margin-top: 0px;">
		<div class="ctitle"><h2><?php echo JText::_('COM_COMMUNITY_MY_BLOCKED_LIST');?></h2></div>
		<ul id="friends-list">
		<?php
			foreach( $blocklists as $row )
			{
				$user	= CFactory::getUser( $row->blocked_userid );
		?>
			<li id="friend-<?php echo $user->id;?>" class="friend-list">
				<span><img width="45" height="45" src="<?php echo $user->getThumbAvatar();?>" alt="" /></span>
				<span class="friend-name">
					<?php echo $user->getDisplayName(); ?>
					<a class="remove" href="javascript:void(0);" onclick="joms.users.unBlockUser('<?php echo $row->blocked_userid;  ?>','privacy');">
					   <?php echo JText::_('COM_COMMUNITY_BLOCK'); ?>
					</a>
				</span>
			</li>
		<?php
			}
		?>
		</ul>
	</div>
</div>
<script type="text/javascript">
joms.jQuery( document ).ready( function(){
  	joms.privacy.init();
});
</script>
