<?php
//CFactory::load('libraries', 'groups');
$user = CFactory::getUser($this->act->actor);
$config = CFactory::getConfig();

// Setup group table
$group = $this->group;

// Setup Announcement Table
$bulletin = JTable::getInstance('Bulletin', 'CTable');
$bulletin->load($act->cid);

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
		<?php echo JText::sprintf('COM_COMMUNITY_GROUPS_NEW_GROUP_NEWS' , CRoute::_('index.php?option=com_community&view=groups&task=viewbulletin&groupid=' . $group->id . '&bulletinid=' . $bulletin->id ), $bulletin->title ); ?>
	</div>
	
	<div class="cStream-Attachment">
		<div class="cStream-Quote">
			<?php
			echo $this->escape(JHTML::_('string.truncate', $bulletin->message, $config->getInt('streamcontentlength'), true, false ));
			?>		
		</div>		
	</div>
	<?php $this->load('activities.actions'); ?>
</div>