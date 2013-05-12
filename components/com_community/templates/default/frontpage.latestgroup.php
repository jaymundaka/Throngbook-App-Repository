<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 */
defined('_JEXEC') or die();

if ( !empty( $groups ) ) {
	$count = 1;
?>	
		<h3><span><?php echo JText::_('COM_COMMUNITY_GROUPS_LATEST'); ?></span></h3>
		<ul class="cResetList cThumbList clrfix">

		<?php foreach ( $groups as $group ) { ?>
			<li>
				<a href="<?php echo CRoute::_('index.php?option=com_community&view=groups&task=viewgroup&groupid='.$group->id); ?>"><img src="<?php echo $group->getAvatar(); ?>" alt="<?php echo $this->escape($group->name); ?>" class="avatar jomTips" width="45" title="<?php echo htmlspecialchars( JText::_( $this->escape($group->name) )); ?>::<?php echo JText::_( $this->escape($group->description) ); ?>" /></a>
			</li>
		
		<?php } ?>
		
		</ul>
<?php
}
?>