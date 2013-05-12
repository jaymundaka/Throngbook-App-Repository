<?php
/**
 * @package		JomSocial
 * @subpackage 	Template
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
?>
<ul class="cResetList cThumbList clrfix">
	<?php
	foreach( $data as $video )
	{
	?>
	<li class="jomTips tipFullWide" id="<?php echo "video-" . $video->getId() ?>" title="<?php echo $this->escape($video->title) . '::' . $this->escape( CStringHelper::truncate($video->description , VIDEO_TIPS_LENGTH ) ); ?>">
		<a class="video-thumb-url" href="<?php echo $video->getURL(); ?>">
			<img src="<?php echo $video->getThumbNail(); ?>" style="width:97px; height:72px;" alt="<?php echo $video->getTitle(); ?>" class="avatar" />
			<span class="video-durationHMS"><?php echo $video->getDurationInHMS(); ?></span>
		</a>
	</li>
	<?php } ?>
</ul>