<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @params	categories Array	An array of categories
 */
defined('_JEXEC') or die();
?>
<form method="post" action="<?php echo CRoute::getURI(); ?>" id="createGroup" name="jsform-groups-create" class="community-form-validate">
<div id="community-groups-wrap">
<?php if($isNew) { ?>
	<p>
		<?php echo JText::_('COM_COMMUNITY_GROUPS_CREATE_DESC'); ?>
	</p>
	<?php
	if( $groupCreationLimit != 0 )
	{
	?>
	<div class="hints">
		<?php echo JText::sprintf('COM_COMMUNITY_GROUPS_LIMIT_STATUS', $groupCreated, $groupCreationLimit );?>
	</div>
	<?php
	}
	?>
<?php } ?>

	<table class="formtable" cellspacing="1" cellpadding="0">
	<?php echo $beforeFormDisplay;?>
	<!-- group name -->
	<tr>
		<td class="key">
			<label for="name" class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_TITLE');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_TITLE_TIPS'); ?>">
				*<?php echo JText::_('COM_COMMUNITY_GROUPS_TITLE'); ?>
			</label>
		</td>
		<td class="value">
			<input name="name" id="name" maxlength="255" type="text" size="45" class="required inputbox" value="<?php echo $this->escape($group->name); ?>" />
		</td>
	</tr>
	<!-- group description -->
	<tr>
		<td class="key">
			<label for="description" class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_DESCRIPTION');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_BODY_TIPS');?>">
				*<?php echo JText::_('COM_COMMUNITY_GROUPS_DESCRIPTION');?>
			</label>
		</td>
		<td class="value">
			<?php if( $config->get( 'htmleditor' ) == 'none' && $config->getBool('allowhtml') ) { ?>
   				<div class="htmlTag"><?php echo JText::_('COM_COMMUNITY_HTML_TAGS_ALLOWED');?></div>
			<?php } ?>

			<?php
			if( !CStringHelper::isHTML($group->description)
				&& $config->get('htmleditor') != 'none'
				&& $config->getBool('allowhtml') )
			{
				$event->description = CStringHelper::nl2br($group->description);
			}
			?>

			<?php echo $editor->displayEditor( 'description',  $group->description , '95%', '350', '10', '20' , false ); ?>
		</td>
	</tr>
	<!-- group category -->
	<tr>
		<td class="key">
			<label for="categoryid" class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY_TIPS');?>">
				*<?php echo JText::_('COM_COMMUNITY_GROUPS_CATEGORY');?>
			</label>
		</td>
		<td class="value">
			<?php echo $lists['categoryid']; ?>
		</td>
	</tr>
	<!-- group type -->
	<tr>
		<td class="key">
			<label class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_TYPE');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_APPROVAL_TIPS');?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_TYPE'); ?>
			</label>
		</td>
		<td class="value">
			<div>
				<input type="radio" name="approvals" id="approve-open" value="0"<?php echo ($group->approvals == COMMUNITY_PUBLIC_GROUP ) ? ' checked="checked"' : '';?> />
				<label for="approve-open" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_GROUPS_OPEN');?></label>
			</div>
			<div style="margin-bottom: 10px;" class="small">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_OPEN_DESCRITPION');?>
			</div>
			
			<div>
				<input type="radio" name="approvals" id="approve-private" value="1"<?php echo ($group->approvals == COMMUNITY_PRIVATE_GROUP ) ? ' checked="checked"' : '';?> />
				<label for="approve-private" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_GROUPS_PRIVATE');?></label>
			</div>
			<div class="small">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_PRIVATE_DESCRIPTION');?>
			</div>
		</td>
	</tr>
	
	
	<!-- group ordering -->
	<tr class="toggle" style="display:none">
		<td class="key">
			<label class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSS_ORDER');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_ORDERING_TIPS');?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSS_ORDER'); ?>
			</label>
		</td>
		<td class="value">
			<div>
				<input type="radio" name="discussordering" id="discussordering-lastreplied" value="0"<?php echo ($params->get('discussordering') == 0 ) ? ' checked="checked"' : '';?> />
				<label for="discussordering-lastreplied" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSS_ORDER_LAST_REPLIED');?></label>
			</div>
			<div>
				<input type="radio" name="discussordering" id="discussordering-creation" value="1"<?php echo ($params->get('discussordering') == 1 ) ? ' checked="checked"' : '';?> />
				<label for="discussordering-creation" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_GROUPS_DISCUSS_ORDER_CREATION_DATE');?></label>
			</div>
		</td>
	</tr>	
	
	<?php if($config->get('enablephotos') && $config->get('groupphotos')): ?>
	<!-- group photos -->
	<tr class="toggle" style="display:none">
		<td class="key">
			<label class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_PHOTOS');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_PERMISSION_TIPS');?>">
				<?php echo JText::_('COM_COMMUNITY_PHOTOS'); ?>
			</label>
		</td>
		<td class="value">
			<div>
				<input type="radio" name="photopermission" id="photopermission-disabled" value="-1"<?php echo ($params->get('photopermission') == GROUP_PHOTO_PERMISSION_DISABLE ) ? ' checked="checked"' : '';?> />
				<label for="photopermission-disabled" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_DISABLED');?></label>
			</div>
			<div>
				<input type="radio" name="photopermission" id="photopermission-admin" value="1"<?php echo ($params->get('photopermission') == GROUP_PHOTO_PERMISSION_ADMINS ) ? ' checked="checked"' : '';?> />
				<label for="photopermission-admin" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_UPLOAD_ALOW_ADMIN');?></label>
			</div>
			<div>
				<input type="radio" name="photopermission" id="photopermission-members" value="0"<?php echo ($params->get('photopermission') == GROUP_PHOTO_PERMISSION_MEMBERS ) ? ' checked="checked"' : '';?> />
				<label for="photopermission-members" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_GROUPS_PHOTO_UPLOAD_ALLOW_MEMBER');?></label>
			</div>
		</td>
	</tr>
	<tr class="toggle" style="display:none">
		<td class="key">
			<label for="grouprecentphotos-admin" class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_PHOTO');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_PHOTOS_TIPS');?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_PHOTO');?>
			</label>
		</td>
		<td class="value">
			<input type="text" name="grouprecentphotos" id="grouprecentphotos-admin" size="1" value="<?php echo $params->get('grouprecentphotos', GROUP_PHOTO_RECENT_LIMIT);?>" />
		</td>
	</tr>
	<?php endif;?>
	<?php if($config->get('enablevideos') && $config->get('groupvideos')): ?>
	<!-- group videos -->
	<tr class="toggle" style="display:none">
		<td class="key">
			<label for="discussordering" class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_VIDEOS');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEOS_PERMISSION_TIPS');?>">
				<?php echo JText::_('COM_COMMUNITY_VIDEOS'); ?>
			</label>
		</td>
		<td class="value">
			<div>
				<input type="radio" name="videopermission" id="videopermission-disabled" value="-1"<?php echo ($params->get('videopermission') == GROUP_VIDEO_PERMISSION_DISABLE ) ? ' checked="checked"' : '';?> />
				<label for="videopermission-disabled" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_DISABLED');?></label>
			</div>
			<div>
				<input type="radio" name="videopermission" id="videopermission-admin" value="1"<?php echo ($params->get('videopermission') == GROUP_VIDEO_PERMISSION_ADMINS ) ? ' checked="checked"' : '';?> />
				<label for="videopermission-admin" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_UPLOAD_ALLOW_ADMIN');?></label>
			</div>
			<div>
				<input type="radio" name="videopermission" id="videopermission-members" value="0"<?php echo ($params->get('videopermission') == GROUP_VIDEO_PERMISSION_MEMBERS ) ? ' checked="checked"' : '';?> />
				<label for="videopermission-members" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_GROUPS_VIDEO_UPLOAD_ALLOW_MEMBER');?></label>
			</div>
		</td>
	</tr>
	<tr class="toggle" style="display:none">
		<td class="key">
			<label for="grouprecentvideos-admin" class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_VIDEO');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_VIDEO_TIPS');?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_RECENT_VIDEO');?>
			</label>
		</td>
		<td class="value">
			<input type="text" name="grouprecentvideos" id="grouprecentvideos-admin" size="1" value="<?php echo $params->get('grouprecentvideos', GROUP_VIDEO_RECENT_LIMIT);?>" />
		</td>
	</tr>
	<?php endif;?>
	<tr class="toggle" style="display:none">
		<td class="key">
			<label class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_EVENTS');?>::<?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_PERMISSIONS');?>"><?php echo JText::_('COM_COMMUNITY_EVENTS');?></label>
		</td>
		<td class="value">
			<div>
				<input type="radio" name="eventpermission" id="eventpermission-disabled" value="-1"<?php echo ($params->get('eventpermission') == GROUP_EVENT_PERMISSION_DISABLE ) ? ' checked="checked"' : '';?> />
				<label for="eventpermission-disabled" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_DISABLE');?></label>
			</div>
			<div>
				<input type="radio" name="eventpermission" id="eventpermission-admin" value="1"<?php echo ($params->get('eventpermission') == GROUP_EVENT_PERMISSION_ADMINS ) ? ' checked="checked"' : '';?> />
				<label for="eventpermission-admin" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_ADMIN_CREATION');?></label>
			</div>
			<div>
				<input type="radio" name="eventpermission" id="eventpermission-members" value="0"<?php echo ($params->get('eventpermission') == GROUP_EVENT_PERMISSION_MEMBERS ) ? ' checked="checked"' : '';?> />
				<label for="eventpermission-members" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS_MEMBERS_CREATION');?></label>
			</div>
		</td>
	</tr>
	<tr class="toggle" style="display:none">
		<td class="key">
			<label for="grouprecentvideos-admin" class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_EVENT_TIPS');?>">
				<?php echo JText::_('COM_COMMUNITY_GROUP_EVENTS');?>
			</label>
		</td>
		<td class="value">
			<input type="text" name="grouprecentevents" id="grouprecentevents-admin" size="1" value="<?php echo $params->get('grouprecentevents', GROUP_EVENT_RECENT_LIMIT);?>" />
		</td>
	</tr>
	<tr class="toggle" style="display:none">
		<td class="key">
			<label class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_NEW_MEMBER_NOTIFICATION');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_NEW_MEMBER_NOTIFICATION_TIPS');?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_NEW_MEMBER_NOTIFICATION'); ?>
			</label>
		</td>
		<td class="value">
			<div>
				<input type="radio" name="newmembernotification" id="newmembernotification-enable" value="1"<?php echo ($params->get('newmembernotification', '1') == true ) ? ' checked="checked"' : '';?> />
				<label for="newmembernotification-enable" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_ENABLE');?></label>
			</div>
			<div>
				<input type="radio" name="newmembernotification" id="newmembernotification-disable" value="0"<?php echo ($params->get('newmembernotification', '1') == false ) ? ' checked="checked"' : '';?> />
				<label for="newmembernotification-disable" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_DISABLE');?></label>
			</div>			
		</td>
	</tr>
	<tr class="toggle" style="display:none">
		<td class="key">
			<label class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_REQUEST_NOTIFICATION');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_REQUEST_NOTIFICATION_TIPS');?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_JOIN_REQUEST_NOTIFICATION'); ?>
			</label>
		</td>
		<td class="value">
			<div>
				<input type="radio" name="joinrequestnotification" id="joinrequestnotification-enable" value="1"<?php echo ($params->get('joinrequestnotification', '1') == true ) ? ' checked="checked"' : '';?> />
				<label for="joinrequestnotification-enable" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_ENABLE');?></label>
			</div>
			<div>
				<input type="radio" name="joinrequestnotification" id="joinrequestnotification-disable" value="0"<?php echo ($params->get('joinrequestnotification', '1') == false ) ? ' checked="checked"' : '';?> />
				<label for="joinrequestnotification-disable" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_DISABLE');?></label>
			</div>			
		</td>
	</tr>
	<tr class="toggle" style="display:none">
		<td class="key">
			<label class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_WALL_NOTIFICATION');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_WALL_NOTIFICATION_TIPS');?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_WALL_NOTIFICATION'); ?>
			</label>
		</td>
		<td class="value">
			<div>
				<input type="radio" name="wallnotification" id="wallnotification-enable" value="1"<?php echo ($params->get('wallnotification', '1') == true ) ? ' checked="checked"' : '';?> />
				<label for="wallnotification-enable" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_ENABLE');?></label>
			</div>
			<div>
				<input type="radio" name="wallnotification" id="wallnotification-disable" value="0"<?php echo ($params->get('wallnotification', '1') == false ) ? ' checked="checked"' : '';?> />
				<label for="wallnotification-disable" class="label lblradio"><?php echo JText::_('COM_COMMUNITY_DISABLE');?></label>
			</div>			
		</td>
	</tr>
	<?php if(! $isNew): ?>
	<tr class="toggle" style="display:none">
		<td class="key">
			<label for="removeactivities" class="label title jomTips" title="<?php echo JText::_('COM_COMMUNITY_GROUPS_REMOVE_ACTIVITIES');?>::<?php echo JText::_('COM_COMMUNITY_GROUPS_REMOVE_ACTIVITIES_TIPS');?>">
				<?php echo JText::_('COM_COMMUNITY_GROUPS_REMOVE_ACTIVITIES');?>
			</label>
		</td>
		<td class="value">
			<input type="checkbox" name="removeactivities" id="removeactivities" value="1" />
			<div class="small"><?php echo JText::_('COM_COMMUNITY_GROUPS_REMOVE_ACTIVITIES_TIPS');?></div>
		</td>
	</tr>
	<?php endif;?>
	<!-- group hint -->
	<tr>
		<td class="key"></td>
		<td class="value"><span class="hints"><?php echo JText::_( 'COM_COMMUNITY_REGISTER_REQUIRED_FILEDS' ); ?></span></td>
	</tr>
	<?php echo $afterFormDisplay;?>

	<!-- Toggle buttons -->
	<tr class="toggleBtn">
	    <td class="key"></td>
	    <td class="value">
		    <a id="js_Group-expand" class="js_Group-expandLink" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_GROUPS_ADVANCED_OPTIONS'); ?></a>
	    </td>
	</tr>
	
	<!-- group buttons -->
	<tr>
		<td class="key"></td>
		<td class="value">
			<?php if($isNew): ?>
			<input name="action" type="hidden" value="save" />
			<?php endif;?>
			<input type="hidden" name="groupid" value="<?php echo $group->id;?>" />
			<input type="submit" value="<?php echo ($isNew) ? JText::_('COM_COMMUNITY_GROUPS_CREATE_GROUP') : JText::_('COM_COMMUNITY_SAVE_BUTTON');?>" class="button validateSubmit" />
			<input type="button" class="button" onclick="history.go(-1);return false;" value="<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON');?>" /> 
			<?php echo JHTML::_( 'form.token' ); ?> 
		</td>
	</tr>
	</table>

</div>

</form>
<script type="text/javascript">
	cvalidate.init();
	cvalidate.setSystemText('REM','<?php echo addslashes(JText::_("COM_COMMUNITY_ENTRY_MISSING")); ?>');
	cvalidate.setMaxLength('#createGroup #description', 65000);

	joms.jQuery('#js_Group-expand').click(function() {
		joms.jQuery('.toggle').toggle('slow');
		joms.jQuery('.toggleBtn').remove();
	});
</script>