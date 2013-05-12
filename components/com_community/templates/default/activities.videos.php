<?php
//CFactory::load('libraries', 'videos'); 
$user = CFactory::getUser($this->act->actor);
$param = new CParameter($act->params);
$video	= JTable::getInstance( 'Video' , 'CTable' );
$video->load( $act->cid );
$this->set('video', $video);

// Attach to $act since it is used by the video library
$act->video = $video;

// Load saperate template for featured videos
if( $act->app == 'videos.featured'){
	$this->load('activities.videos.featured'); 
	return;
}

// Load saperate template for comment on videos
if ($param->get('action') == 'wall') {
	$this->load('activities.videos.comment'); 
	return;
}

?>
<a class="cStream-Avatar cFloat-L" href="<?php echo CUrlHelper::userLink($user->id); ?>">
	<img class="cAvatar" data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>">
</a>

<div class="cStream-Content">
	<div class="cStream-Headline">
		<?php
		if($act->groupid){
			$group = JTable::getInstance('Group', 'CTable');
			$group->load($act->groupid);
			?>
			<span class="cStream-Reference">
				<a class="cStream-Reference" href="<?php echo $group->getLink(); ?>"><?php echo $group->name; ?></a> - 
			</span>
			<?php
		}
		?>
		
		<?php
		$html = CVideos::getActivityTitleHTML($act);
		echo $html;
		?>				
	</div>

	<?php
	// If the param style = COMMUNITY_STREAM_STYLE, then use the title as content
	$quoteContent = CActivities::format($act->title);

	if(!empty($quoteContent) && $param->get('style') == COMMUNITY_STREAM_STYLE){
		echo '<div class="cStream-Quote">'. $quoteContent .'</div>';
	}
	?>
	
	<div class="cStream-Attachment">		
		<?php
		$html = CVideos::getActivityContentHTML($act);
		echo $html;
		?>				
	</div>
	
	<?php $this->load('activities.actions'); ?>
</div>