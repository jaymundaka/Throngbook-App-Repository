<?php
defined('_JEXEC') or die('Restricted access');
?>
<html xmlns:fb="http://www.facebook.com/2008/fbml" xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" dir="ltr" >
<head>
<?php
$siteRoot = JURI::root();
$siteRoot = rtrim($siteRoot, '/');
jimport('joomla.filesystem.file');
if(JFile::exists(JPATH_ROOT .DS.'media'.DS.'system'.DS.'js'.DS.'mootools-core.js') )
{
	// New style media path. (Joomla! 1.6 onwards)	
?>
	<script src="<?php echo $siteRoot; ?>/media/system/js/mootools-core.js" type="text/javascript"></script>
	<script src="<?php echo $siteRoot; ?>/media/system/js/mootools-more.js" type="text/javascript"></script>
<?php
}
else 
{
	// Old style media path. ( Joomla! 1.5 )
?>
	<script src="<?php echo $siteRoot; ?>/media/system/js/mootools.js" type="text/javascript"></script>
<?php
} 
?>

<script src="<?php echo $siteRoot; ?>/components/com_community/assets/joms.jquery.js" type="text/javascript"></script>
<script type="text/javascript" src="http://static.ak.connect.facebook.com/js/api_lib/v0.4/FeatureLoader.js.php"></script>
<script type="text/javascript">
	window.addEvent('load', function()
	{
		FB_RequireFeatures(["XFBML"], function() {
			FB.Facebook.init( "<?php echo $config->get('fbconnectkey');?>" , "index.php?option=com_community&view=connect&task=receiver");
		});
	});
</script>
<?php
$content	= JText::sprintf( 'COM_COMMUNITY_FBCONNECT_MESSAGE' , '<fb:name uid="' . $facebook->getUser() . '" useyou="false" />', JURI::root() , '<fb:req-choice url="' . CRoute::getExternalUrl( 'index.php?option=com_community&view=register' ) . '" label="Register" />');
?>
</head>
<body style="width:610px; height: 400px; margin:0px; padding:0px; overflow:hidden;background: url(<?php echo $siteRoot; ?>/components/com_community/assets/wait.gif) 50% 50% no-repeat ;">
<fb:serverfbml>
	<script type="text/fbml">
		<fb:fbml>
			<fb:request-form target="_top" action="<?php echo CRoute::getExternalUrl('index.php?option=com_community&view=connect&task=inviteend');?>" method="post" type="invite" content="<?php echo JText::_('COM_COMMUNITY_FBCONNECT_CHECKOUT_SITE');?> <?php echo htmlentities($content,ENT_COMPAT,'UTF-8');?>">
				<fb:multi-friend-selector import_external_friends="false" condensed="false" rows="3" email_invite="false" cols="5" showborder="false" actiontext="<?php echo JText::_('COM_COMMUNITY_FBCONNECT_INVITE_FACEBOOK_FRIENDS');?>">
			</fb:request-form>
		</fb:fbml>
	</script>
</fb:serverfbml>
</body>
</html>