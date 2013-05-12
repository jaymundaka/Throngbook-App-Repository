<?php 

// Load params
$param = new JRegistry($act->params);
$action = $param->get('action');

$user = CFactory::getUser($this->act->actor);
$wall = JTable::getInstance('Wall', 'CTable');
$wall->load($param->get('wallid'));
$photo = JTable::getInstance('Photo','CTable');
$photo->load($act->cid);

?>
<a class="cStream-Avatar cFloat-L" href="<?php echo CUrlHelper::userLink($user->id); ?>">
	<img class="cAvatar" data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>">
</a>

<div class="cStream-Content">
	<div class="cStream-Headline" style="display:block">
		<a class="cStream-Author" href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
		<?php echo JText::sprintf('COM_COMMUNITY_ACTIVITIES_WALL_POST_PHOTO', '#', "photo" ); ?>
		</br>			
	</div>
	
	<div class="cStream-Attachment">
		<?php
		// Load some album photos. I'd says 4 is enough
		?>
		<div class="cStream-PhotoRow row-fluid">
			<div class="span12" style="height:200px;background-image:url(<?php echo $photo->getImageURI(); ?>);background-size:cover;">
				<a class="cPhoto-Thumb">
					<img src="" />
				</a>
			</div>
		</div>
		<div class="cStream-Quote">
			<?php $comment = JHTML::_('string.truncate', $wall->comment, $config->getInt('streamcontentlength') );?>
			<?php echo CActivities::format($comment); ?>
		</div>
	</div>
	
	<?php 
	// No comment on photo comment
	//$this->load('activities.actions'); 
	?>
</div>
