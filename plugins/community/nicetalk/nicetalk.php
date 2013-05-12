<?php
/**
 * @category	Plugins
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php' );

if(!class_exists('plgCommunityNiceTalk'))
{
	class plgCommunityNiceTalk extends CApplications
	{
		var $name 		= 'Recent topics from Nice Talk';
		var $_name		= 'Nicetalk';
	
		function ajaxSubmit( $response , $title , $message )
		{
			JPlugin::loadLanguage( 'plg_community_nicetalk', JPATH_ADMINISTRATOR );
		
			if( empty( $title ) )
			{
				$response->addScriptCall( 'joms.jQuery("#title-error").html("' . JText::_('PLG_NICETALK_TITLE_IS_REQUIRED') .'");');
				$response->addScriptCall( 'joms.jQuery("#title-error").css("display","block");');
				return $response;
			}
			
			if( empty( $message ) )
			{
				$response->addScriptCall( 'joms.jQuery("#message-error").html("' . JText::_('PLG_NICETALK_MESSAGE_IS_REQUIRED') .'");');
				$response->addScriptCall( 'joms.jQuery("#message-error").css("display","block");');
				return $response;
			}
	
			// Include the helpers
			require_once( JPATH_ROOT . DS . 'components' . DS . 'com_nicetalk' . DS . 'helpers' . DS . 'functions.nicetalk.php' );
	
			$my			= CFactory::getUser();		
			$date		=& JFactory::getDate();
			$db			=& JFactory::getDBO();
					
			$created	= $date->toFormat();
			$ip			= getenv('REMOTE_ADDR');
			$title		= $db->getEscaped( strip_tags( $title ) );
			$message	= $db->getEscaped( ntNl2brStrict( strip_tags( $message ) ) );
			$fullname	= $db->getEscaped( strip_tags( $my->getDisplayName() ) );
			$topicid	= '0';
	
	
			// auto hyperlink
			$message = ntAutoHyperlink($message);
			
			if(get_magic_quotes_gpc())
			{
				$message	= stripslashes($message);
				$title		= stripslashes($title);
			}
				
			$query	= 'INSERT INTO ' . $db->nameQuote( '#__nicetalk_content' ) . ' '
					. 'SET '.$db->nameQuote('date').'=' . $db->Quote( $created ) . ' , '
					. $db->nameQuote('content').'=' . $db->Quote( $message ) . ' , '
					. $db->nameQuote('user_id').'=' . $db->Quote( $my->id ) . ' , '
					. $db->nameQuote('parentid').'=' . $db->Quote( $topicid ) . ' , '
					. $db->nameQuote('username').'=' . $db->Quote( $fullname ) . ' , '
					. $db->nameQuote('email').'=' . $db->Quote( $my->email ) . ' , '
					. $db->nameQuote('title').'=' . $db->Quote( ucwords( $title ) ) . ' , '
					. $db->nameQuote('published').'=' . $db->Quote( '1' ) . ' , '
					. $db->nameQuote('istopic').'=' . $db->Quote( '1' );
			
			$db->setQuery( $query );
			$db->query();
	
			$topicId	= $db->insertId();
			
			$response->addAssign( 'cWindowContent' , 'innerHTML' , JText::_('PLG_NICETALK_TOPIC_ADDED') );
			$action		= '<input type="button" class="button" onclick="javascript:cWindowHide();" value="' . JText::_('COM_COMMUNITY_CLOSE_BUTTON') . '">';
			$response->addScriptCall('cWindowActions', $action );
			$response->addScriptCall('cWindowResize(150);');
			$response->addScriptCall('window.location.reload();');
	
			//$response->addScriptCall('joms.jQuery("#nicetalk-entries").prepend("' . $data . '");');
			return $response;
		}
		
		function ajaxShowForm( $response )
		{
			JPlugin::loadLanguage( 'plg_community_nicetalk', JPATH_ADMINISTRATOR );
			
			$my			= CFactory::getUser();
			ob_start();
		?>
			<div>
				<label for="nicetalk-title">
					<?php echo JText::_('PLG_NICETALK_TITLE');?>
					<span class="nicetalk-error" id="title-error"><?php echo JText::_('PLG_NICETALK_TITLE_IS_REQUIRED');?></span>
				</label>
				<input id="nicetalk-title" type="text" style="width: 400px;" />
				<label for="nicetalk-message">
					<?php echo JText::_('PLG_NICETALK_MESSAGE');?>
					<span class="nicetalk-error" id="message-error"></span>
				</label>
				<textarea style="width: 400px; height: 120px;" id="nicetalk-message" name="nicetalk-message"></textarea>
			</div>
		<?php
			$html		= ob_get_contents();
			ob_end_clean();
			
	
			$response->addAssign( 'cWindowContent' , 'innerHTML' , $html );
	
			$action		= '<input type="button" class="button" onclick="jax.call( \'community\' , \'plugins,nicetalk,ajaxSubmit\' , joms.jQuery(\'#nicetalk-title\').val() , joms.jQuery(\'#nicetalk-message\').val() );" name="submit" value="' . JText::_('COM_COMMUNITY_SUBMIT_BUTTON') . '" />&nbsp;&nbsp;';
			$action		.= '<input type="button" class="button" onclick="javascript:cWindowHide();" value="' . JText::_('COM_COMMUNITY_CLOSE_BUTTON') . '">';
			$response->addScriptCall('cWindowActions', $action );
			return $response->sendResponse();
		}
		
		function onProfileDisplay()
		{
			// Load language
			JPlugin::loadLanguage( 'plg_community_nicetalk', JPATH_ADMINISTRATOR );
		
			$id		= JRequest::getVar( 'userid' , '' , 'GET' );
			
			$user	= CFactory::getUser( $id );
			$my		= CFactory::getUser();
			
			// Test if Nice Talk really exists
			$config	= JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_nicetalk' . DS . 'config.nicetalk.php';
			$helper	= JPATH_ROOT . DS . 'components' . DS . 'com_nicetalk' . DS . 'helpers' . DS . 'functions.nicetalk.php';
			
			jimport( 'joomla.filesystem.file' );
			
			if( JFile::exists( $config ) && JFile::exists( $helper ) )
			{
				// Include helper and config
				require_once( $config );
				require_once( $helper );
			}
			else
			{
				$content	= '<div style="text-align: center;">' . JText::_('PLG_NICETALK_NOT_AVAILABLE' ) . '</div>';
				return $content;
			}
	
			// Get topics
			$topics	= $this->_getTopics( $user->id );
			
			// Attach CSS into headers.
			$document	=& JFactory::getDocument();
			$document->addStyleSheet( rtrim( JURI::root() , '/' ) . '/plugins/community/nicetalk/style.css' );
			
			//
			require_once( JPATH_ROOT . DS . 'components' . DS . 'com_nicetalk' . DS . 'libraries' . DS . 'tagcloud.php' );
			
			ob_start();
			
			if( $my->id == $user->id )
			{
		?>
			<div style="text-align: right;" class="nicetalk-see-all">
				[ <a href="javascript:void(0);" onclick="cWindowShow('jax.call(\'community\',\'plugins,nicetalk,ajaxShowForm\');','Add new topic',450, 300);"><?php echo JText::_('PLG_NICETALK_ADD_TOPIC');?></a> ]
			</div>
		<?php
			}
			
			if( $topics )
			{
		?>
	
			<ul class="nicetalk-entries" id="nicetalk-entries">
				<?php
				foreach( $topics as $topic )
				{
					$lastReply	= $this->_getRepliesData( $topic->id );
					
					$userId		= (!$lastReply) ? $my->id : $lastReply->user_id;
					$userName	= ( !$lastReply ) ? $user->getDisplayName() : $lastReply->user->getDisplayName();
				?>
				<li>
				    <div class="ctitle">
						<div>
							<span class="nicetalk-title">
								<a href="<?php echo JRoute::_('index.php?option=com_nicetalk&view=topics&topicid=' . $topic->id );?>"><?php echo $topic->title;?></a>
							</span>
							<span class="nicetalk-replies"><?php echo JText::sprintf('PLG_NICETALK_TOTAL_REPLIES' , $this->_getRepliesCount($topic->id) );?></span>
						</div>
						<div>
							<span style="margin-right: 3px;">
							<?php echo JText::sprintf( 'PLG_NICETALK_LAST_COMMENTED' );?>
								<a href="<?php echo CRoute::_('index.php?option=com_community&view=profile&userid=' . $userId );?>">
								<?php echo $userName; ?>
								</a>
							</span>
						</div>
						<div>
							<span class="nicetalk-tags"><?php echo JText::_('PLG_NICETALK_TAGS');?></span>
							<span class="nicetalk-tags-container">
							<?php
							$tags	= $this->_getTags( $topic->id );
							if( $tags )
							{
								foreach( $tags as $tag )
								{
							?>
								<a href="<?php echo JRoute::_('index.php?option=com_nicetalk&tagid=' . $tag->id );?>" class="nicetalk-tag"><?php echo $tag->name;?></a>
							<?php
								}
							}
							else
							{
							?>
								<span><?php echo JText::_('PLG_NICETALK_NO_TAGS');?></span>
							<?php
							}
							?>
							</span>
						</div>
					</div>
				</li>
				<?php
				}
				?>
			</ul>
		<?php
			}
			else
			{
		?>
			<div style="text-align: center;"><?php echo JText::_('PLG_NICETALK_NO_TOPIC');?></div>
		<?php
			}
			
			$html	= ob_get_contents();
			ob_end_clean();
			
			return $html;
		}
		
		function _getRepliesCount( $topicId )
		{
			$db		=& JFactory::getDBO();
			
			$query	= 'SELECT COUNT(*) FROM ' . $db->nameQuote( '#__nicetalk_content' ) . ' '
					. 'WHERE ' . $db->nameQuote( 'published' ) . '=' . $db->Quote( '1' ) . ' '
					. 'AND ' . $db->nameQuote( 'parentid' ) . '=' . $db->Quote( $topicId );
			
			$db->setQuery( $query );
			
			$replyCount	= $db->loadResult();
	
			return $replyCount;
		}
		
		function _getRepliesData( $topicId )
		{
			$db		=& JFactory::getDBO();
			
			$replyCount	= $this->_getRepliesCount( $topicId );
	
			if( $replyCount < 1 )
			{
				return false;
			}
	
			$query	= 'SELECT  '.$db->nameQuote('username').', '.$db->nameQuote('user_id').' FROM ' . $db->nameQuote( '#__nicetalk_content' )
					. ' WHERE ' . $db->nameQuote( 'published' ) . '=' . $db->Quote( 1 )
					. ' AND ' . $db->nameQuote( 'parentid' ) . '=' . $db->Quote( $topicId )
					. 'ORDER BY '.$db->nameQuote('date').' DESC';
			
			$db->setQuery( $query );
			$reply	= $db->loadObject();
			
			$reply->user	= CFactory::getUser( $reply->user_id );
			
			return $reply;
		}
		
		function _getTags( $topicId )
		{
			$db		=& JFactory::getDBO();
			
			$query	= 'SELECT * FROM ' . $db->nameQuote( '#__nicetalk_content_categories' ) . ' AS a '
					. ' INNER JOIN ' . $db->nameQuote( '#__nicetalk_categories' ) . ' AS b '
					. ' ON a.'.$db->nameQuote('category').'=b.'.$db->nameQuote('id')
					. ' AND a.'.$db->nameQuote('contentid').'=' . $db->Quote( $topicId );
			$db->setQuery( $query );
			
			$tags	= $db->loadObjectList();
			
			return $tags;
		}
		
		function _getTopics( $userId )
		{
			$this->loadUserParams();
			$db		=& JFactory::getDBO();
			
			$query	= 'SELECT * FROM ' . $db->nameQuote( '#__nicetalk_content' ) . ' '
					. 'WHERE ' . $db->nameQuote( 'user_id' ) . '=' . $db->Quote( $userId ) . ' '
					. 'AND ' . $db->nameQuote( 'parentid' ) . '=' . $db->Quote( 0 );
					
			// Set limit
			$query	.= ' LIMIT 0,' . $this->userparams->get('count' , 5 );
			
			$db->setQuery( $query );
			
			$topics	= $db->loadObjectList();
			
			return $topics;
		}
		
		/**
		 * Return itemid for NiceTalk
		 */	 	
		function getItemid()
		{
			$db =& JFactory::getDBO();
			$Itemid = 0;
			if (!defined("FB_FB_ITEMID")) {
		    	if ($Itemid < 1) {
		        	$db->setQuery('SELECT '.$db->nameQuote('id')
		        				.' FROM '.$db->nameQuote('#__menu')
		        				.' WHERE '.$db->nameQuote('link').' = '.$db->Quote('index.php?option=com_fireboard')
		        				.' AND '.$db->nameQuote('published').' = '.$db->Quote('1'));
		        	$Itemid = $db->loadResult();
		
		        	if ($Itemid < 1) {
		         	   $Itemid = 0;
		        	}
		    	}
		    } else {
		    	$Itemid = FB_FB_ITEMID;
			}
		    
		    return $Itemid;
		}
	}	
}

