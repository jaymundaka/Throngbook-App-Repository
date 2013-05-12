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

if( $events )
{
	for( $i = 0; $i < count( $events ); $i++ )
	{
		$event				=& $events[$i];
?>
	<div class="community-events-results-item">
		<div class="community-events-results-left">
			<a href="<?php echo $event->getLink();?>"><img class="avatar" src="<?php echo $event->getThumbAvatar();?>" border="0" alt="<?php echo $this->escape($event->title); ?>"/></a>
			<div class="eventDate"><?php echo CEventHelper::formatStartDate($event, $config->get('eventdateformat') ); ?></div>
		</div>
		<div class="community-events-results-right">
			<h3 class="eventName">
				<a href="<?php echo $event->getLink();?>"><strong><?php echo $this->escape($event->title); ?></strong></a>
			</h3>
			<div class="eventLocation"><?php echo $this->escape($event->location);?></div>
            <div class="eventTime"><?php echo JText::sprintf('COM_COMMUNITY_EVENTS_DURATION', $event->startdate, $event->enddate); ?></div>
			<div class="eventActions">
				<span class="jsIcon1 icon-group" style="margin-right: 5px;">
					<?php if( $isExpired || CEventHelper::isPast($event) ) { ?>
					<a href="<?php echo $event->getGuestLink( COMMUNITY_EVENT_STATUS_ATTEND );?>"><?php echo JText::sprintf((cIsPlural($event->confirmedcount)) ? 'COM_COMMUNITY_EVENTS_COUNT_MANY_PAST':'COM_COMMUNITY_EVENTS_COUNT_PAST', $event->confirmedcount);?></a>
					<?php } else { ?>
					<a href="<?php echo $event->getGuestLink( COMMUNITY_EVENT_STATUS_ATTEND );?>"><?php echo JText::sprintf((cIsPlural($event->confirmedcount)) ? 'COM_COMMUNITY_EVENTS_MANY_GUEST_COUNT':'COM_COMMUNITY_EVENTS_GUEST_COUNT', $event->confirmedcount);?></a>
					<?php } ?>
				</span>
			</div>
		</div>
		<?php if( $isExpired || CEventHelper::isPast($event) ) { ?>
		    <span class="icon-offline-overlay">&nbsp;<?php echo JText::_('COM_COMMUNITY_EVENTS_PAST'); ?>&nbsp;</span>
		<?php } else if(CEventHelper::isToday($event)) { ?>
			<span class="icon-online-overlay">&nbsp;<?php echo JText::_('COM_COMMUNITY_EVENTS_ONGOING'); ?>&nbsp;</span>
		<?php } ?>
		<div style="clear: both;"></div>
	</div>
<?php
	}
} else {
?>
	<div class="event-not-found"><?php echo JText::_('COM_COMMUNITY_EVENTS_NO_EVENTS_ERROR'); ?></div>
<?php } ?>

<?php if (!is_null($pagination)) {?>
<div class="pagination-container">
	<?php echo $pagination->getPagesLinks(); ?>
</div>
<?php }?>