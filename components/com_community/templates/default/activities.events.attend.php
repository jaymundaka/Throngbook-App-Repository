<?php
$user = CFactory::getUser($this->act->actor); 
$users = explode(',', $this->actors); 
$actorsHTML = array();
?>
<div class="cStream-Content">
<i class="cStream-Icon com-icon-groups"></i>
	<?php foreach($users as $actor) 
	{ 
		$user = CFactory::getUser($actor);
		$actorsHTML[] = '<a class="cStream-Author" href="'. CUrlHelper::userLink($user->id).'">'. $user->getDisplayName().'</a>'; 
	} 

	echo implode(', ', $actorsHTML); ?> 
	- 
	<?php echo JText::sprintf('COM_COMMUNITY_ACTIVITIES_EVENT_ATTEND' , $this->event->getLink(), $this->event->title);
	?>
</div>