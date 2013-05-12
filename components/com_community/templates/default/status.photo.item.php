<li id="photo-<?php echo $photo->id; ?>">
	<img src="<?php echo JURI::base().$photo->thumbnail; ?>" alt="" />
	<div class="creator-photo-filename"><?php echo $filename; ?></div>
	<a class="creator-change-photo" href="javascript: void(0);"><?php echo JText::_('COM_COMMUNITY_PHOTOS_CHANGE'); ?></a>
</li>