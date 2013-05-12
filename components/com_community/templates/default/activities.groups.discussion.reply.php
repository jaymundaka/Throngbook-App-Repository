<?php
//CFactory::load('libraries', 'groups');
$user = CFactory::getUser($this->act->actor);

// Setup event table
$group = $this->group;

// Load params
$param = new JRegistry($this->act->params); 
$action = $param->get('action');
$actors = $param->get('actors');
$this->set('actors', $actors);

$discussion = JTable::getInstance('Discussion' , 'CTable' );
$discussion->load($act->cid);

?>

<a class="cStream-Avatar cFloat-L" href="<?php echo CUrlHelper::userLink($user->id); ?>">
	<img class="cAvatar" data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>">
</a>

<div class="cStream-Content">
	<div class="cStream-Headline">
		<?php
		if($act->groupid){
			$group = $this->group;
			?>
			<span class="cStream-Reference">
				<a class="cStream-Reference" href="<?php echo $group->getLink(); ?>"><?php echo $group->name; ?></a> - 
			</span>
			<?php
		}
		?>
		<a class="cStream-Author" href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
		<?php echo JText::sprintf('COM_COMMUNITY_GROUPS_REPLY_DISCUSSION' , CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid='.$discussion->groupid.'&topicid='.$discussion->id), $discussion->title ); ?>
	</div>
	
	<div class="cStream-Attachment">
		<div class="cStream-Quote">
			<?php echo $this->escape($this->act->content); ?>
		</div>
	</div>
	
	<?php 
	// No actions for discussion replies
	/*$this->load('activities.actions');*/ 
	?>
</div>