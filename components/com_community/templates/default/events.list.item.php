<?php
/**
 * @package		JomSocial
 * @subpackage 	Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 * 
 * @params	groups		An array of events objects.
 */
defined('_JEXEC') or die();
?>
<li>
	<div class="cIndex-Box clearfix">
		<?php if( $isExpired || CEventHelper::isPast($event) ) { ?>
			<span class="icon-offline-overlay">&nbsp;<?php echo JText::_('COM_COMMUNITY_EVENTS_PAST'); ?>&nbsp;</span>
		<?php } else if(CEventHelper::isToday($event)) { ?>
			<span class="icon-online-overlay">&nbsp;<?php echo JText::_('COM_COMMUNITY_EVENTS_ONGOING'); ?>&nbsp;</span>
		<?php } ?>

		
		<a href="<?php echo $event->getLink();?>" class="cIndex-Avatar cFloat-L">
			<img src="<?php echo $event->getThumbAvatar();?>" alt="<?php echo $this->escape($event->title); ?>" class="cAvatar" />
		</a>


		<div class="cIndex-content">
			<h3 class="cIndex-Name reset-h">
				<a href="<?php echo $event->getLink();?>"><strong><?php echo $this->escape($event->title); ?></strong></a>
			</h3>
			<div class="cIndex-Status">
				<div class="cIndex-Date"><?php echo CEventHelper::formatStartDate($event, $config->get('eventdateformat') ); ?></div>
				<div class="cIndex-Location"><?php echo $this->escape($event->location);?></div>
				<div class="cIndex-Time"><?php echo JText::sprintf('COM_COMMUNITY_EVENTS_DURATION', CTimeHelper::getFormattedTime($event->startdate, $timeFormat), CTimeHelper::getFormattedTime($event->enddate, $timeFormat)); ?></div>
			</div>
			<div class="cIndex-Actions">
				<div class="action">
					<?php if( $isExpired || CEventHelper::isPast($event) ) { ?>
					<a href="<?php echo $event->getGuestLink( COMMUNITY_EVENT_STATUS_ATTEND );?>"><?php echo JText::sprintf((cIsPlural($event->confirmedcount)) ? 'COM_COMMUNITY_EVENTS_COUNT_MANY_PAST':'COM_COMMUNITY_EVENTS_COUNT_PAST', $event->confirmedcount);?></a>
					<?php } else { ?>
					<a href="<?php echo $event->getGuestLink( COMMUNITY_EVENT_STATUS_ATTEND );?>"><?php echo JText::sprintf((cIsPlural($event->confirmedcount)) ? 'COM_COMMUNITY_EVENTS_MANY_GUEST_COUNT':'COM_COMMUNITY_EVENTS_GUEST_COUNT', $event->confirmedcount);?></a>
					<?php } ?>
				</div>
				<?php
				if( $isCommunityAdmin && $showFeatured ) {
					if( !in_array($event->id, $featuredList) )
					{
				?>
				<div class="action">
					<a onclick="joms.featured.add('<?php echo $event->id;?>','events');" href="javascript:void(0);"><?php echo JText::_('COM_COMMUNITY_MAKE_FEATURED'); ?></a>
				</div>
				<?php			
					}
				}
				?>
			</div>
		</div>
	</div>
</li>