<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * 
 */
defined('_JEXEC') or die();
?>

<table class="video-addTypes" cellpadding="5">
<tr>
    <td class="video-addType link">
        <h2 class="video-addType-name"><?php echo JText::_('COM_COMMUNITY_VIDEOS_LINK'); ?></h2>
        <p class="video-addType-description"><?php echo JText::_('COM_COMMUNITY_VIDEOS_LINK_ADDTYPE_DESC'); ?></p>
        
        <ul class="video-providers">          
            <li class="video-provider">YouTube</li>
            <li class="video-provider">Yahoo Video</li>
            <li class="video-provider">MySpace Video</li>
            <li class="video-provider">Flickr</li>
            <li class="video-provider">Vimeo</li>
            <li class="video-provider">Metacafe</li>
            <li class="video-provider">Blip.tv</li>
            <li class="video-provider">Dailymotion</li>
            <li class="video-provider">Break</li>
            <li class="video-provider">Live Leak</li>
            <li class="video-provider">Viddler</li> 
        </ul>
    </td>
<?php
if( $enableVideoUpload )
{
?>
	<td class="video-addType upload">
        <div class="upload-video-field">
            <h2 class="video-addType-name"><?php echo JText::_('COM_COMMUNITY_VIDEOS_UPLOAD'); ?></h2>
            <p class="video-addType-description"><?php echo JText::_('COM_COMMUNITY_VIDEOS_FILE_ADDTYPE_DESC'); ?></p>
            <ul class="video-uploadRules">
                <li class="video-uploadRule"><?php echo JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SIZE_RULE', $uploadLimit); ?></li>
                <li class="video-uploadRule"><?php echo JText::_('COM_COMMUNITY_VIDEOS_UPLOAD_LENGTH_RULE'); ?></li>
                <li class="video-uploadRule"><?php echo JText::_('COM_COMMUNITY_VIDEOS_RULE_FORMAT'); ?></li>
            </ul> 
            
        </div>
	</td>
<?php
}
?>
</tr>
<tr>
	<td style="text-align: center;">
		<input class="video-action button" type="button" onclick="joms.videos.linkVideo('<?php echo $creatorType; ?>', '<?php echo $groupid; ?>');" value="<?php echo JText::_('COM_COMMUNITY_NEXT'); ?>"/>
	</td>
<?php
if( $enableVideoUpload )
{
?>
	<td style="text-align: center;">
		<input class="video-action button" type="button" onclick="joms.videos.uploadVideo('<?php echo $creatorType; ?>', '<?php echo $groupid; ?>');" value="<?php echo JText::_('COM_COMMUNITY_NEXT'); ?>"/>
	</td>
<?php
}
?>
</tr>
