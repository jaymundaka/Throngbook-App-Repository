<?php
//CFactory::load('libraries', 'groups');
$user = CFactory::getUser($this->act->actor);
$config = CFactory::getConfig();

// Setup group table
$group = $this->group;

// Setup Discussion Table
$discussion = JTable::getInstance('Discussion' , 'CTable' );
$discussion->load($act->cid);
$discussionLink = CRoute::_('index.php?option=com_community&view=groups&task=viewdiscussion&groupid=' . $group->id . '&topicid=' . $discussion->id ); 

// Load params
$param = new JRegistry($this->act->params); 
$action = $param->get('action');
$actors = $param->get('actors');
$this->set('actors', $actors);
?>

<a class="cStream-Avatar cFloat-L" href="<?php echo CUrlHelper::userLink($user->id); ?>">
	<img class="cAvatar" data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>">
</a>

<div class="cStream-Content">
	<div class="cStream-Headline">
		<span class="cStream-Reference">
			<a class="cStream-Reference" href="<?php echo $group->getLink(); ?>"><?php echo $group->name; ?></a> - 
		</span>

		<a class="cStream-Author" href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
		<?php echo JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_DISCUSSION' , $discussionLink, $discussion->title ); ?>	
	</div>
	
	<div class="cStream-Attachment">
		<div class="cStream-Quote">
			<?php
			echo $this->escape(JHTML::_('string.truncate', $discussion->message, $config->getInt('streamcontentlength'), true, false ));
			?>		
		</div>		
	</div>
	<?php $this->load('activities.actions'); ?>
</div>