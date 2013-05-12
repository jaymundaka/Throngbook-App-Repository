<?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class CommunitySystemController extends CommunityBaseController
{
	public function ajaxShowInvitationForm( $friends , $callback , $cid , $displayFriends , $displayEmail )
	{
                // pending filter
		$objResponse	= new JAXResponse();
		$displayFriends	= (bool) $displayFriends;
		
		$invitation		=& JTable::getInstance( 'Invitation' , 'CTable' );
		$invitation->load( $callback , $cid );
		
		$friends		= empty( $friends ) ? array() : explode( ',' , $friends );
		$tmpl			= new CTemplate();
		$tmpl->set( 'friends'	, $friends );
		$tmpl->set( 'selected'	, $invitation->getInvitedUsers() );		
		$tmpl->set( 'displayFriends' , $displayFriends );
		$tmpl->set( 'displayEmail'	, $displayEmail );
		$html			= $tmpl->fetch( 'ajax.showinvitation' );

		$actions        = '<input type="button" class="button" onclick="joms.invitation.send(\'' . $callback . '\',\'' . $cid . '\');" value="' . JText::_('COM_COMMUNITY_SEND_INVITATIONS') . '"/>';
		
		$objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_FRIENDS'));

		$objResponse->addScriptCall('cWindowAddContent', $html, $actions);

		return $objResponse->sendResponse();
	}
	
	public function ajaxSubmitInvitation( $callback , $cid , $values )
	{
                $filter = JFilterInput::getInstance();
                $callback = $filter->clean($callback, 'string');
                $cid = $filter->clean($cid, 'int');
                $values = $filter->clean($values, 'array');

		$objResponse	= new JAXResponse();
		$my				= CFactory::getUser();
		$methods		= explode( ',' , $callback );
		$emails			= array();
		$recipients		= array();
		$users			= '';
		$message		= $values[ 'message' ];
		$values['friends']	= isset( $values['friends'] ) ? $values['friends'] : array();
		
		if( !is_array( $values['friends'] ) )
		{
			$values['friends']	= array( $values['friends'] );
		}

		// This is where we process external email addresses
		if( !empty( $values[ 'emails' ] ) )
		{
			$emails	= explode( ',' , $values[ 'emails' ] );
			foreach( $emails as $email )
			{
				$recipients[]	= $email;
			}
		}
		
		// This is where we process site members that are being invited
		if( !empty( $values[ 'friends' ] ) )
		{
			$users		= implode( ',' , $values['friends'] );
			
			foreach( $values['friends'] as $id )
			{
				$recipients[]	= $id;
			}
		}

		if( !empty( $recipients) )
		{
			$arguments		=  array( $cid , $values['friends'] , $emails , $message );
			
			if( is_array( $methods ) && $methods[0] != 'plugins' )
			{
				$controller	= JString::strtolower( basename($methods[0]) );
				$function	= $methods[1];
				require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'controllers' . DS . 'controller.php' );
				$file		= JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'controllers' . DS . $controller . '.php';
	 			
	 			
	 			if( JFile::exists( $file ) )
	 			{
	 				require_once( $file );

					$controller	= JString::ucfirst( $controller );
		 			$controller	= 'Community' . $controller . 'Controller';
		 			$controller	= new $controller();
		 			
		 			if( method_exists( $controller , $function ) )
					{ 
		 				$inviteMail	= call_user_func_array( array( $controller , $function ) , $arguments );
		 			}
		 			else
		 			{
		 				$objResponse->addAssign('invitation-error' , 'innerHTML' , JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR' ) );
						return $objResponse->sendResponse();
					}
				}
				else
				{
	 				$objResponse->addAssign('invitation-error' , 'innerHTML' , JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR' ) );
					return $objResponse->sendResponse();
				}
			}
			else if( is_array( $methods ) && $methods[0] == 'plugins' )
			{
				// Load 3rd party applications
				$element	= JString::strtolower( basename($methods[1]) );
				$function	= $methods[2];
				$file		= CPluginHelper::getPluginPath('community',$element) . DS . $element . '.php';

	 			if( JFile::exists( $file ) )
	 			{
	 				require_once( $file );
					$className	= 'plgCommunity' . JString::ucfirst( $element );
				
				
		 			if( method_exists( $controller , $function ) )
					{
						$inviteMail	= call_user_func_array( array( $className , $function ) , $arguments ); 
		 			}
		 			else
		 			{
		 				$objResponse->addAssign('invitation-error' , 'innerHTML' , JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR' ) );
						return $objResponse->sendResponse();
					}
				}
				else
				{
	 				$objResponse->addAssign('invitation-error' , 'innerHTML' , JText::_('COM_COMMUNITY_INVITE_EXTERNAL_METHOD_ERROR' ) );
					return $objResponse->sendResponse();
				}
			}
			
			CFactory::load( 'libraries' , 'invitation' );
			
			// If the responsible method returns a false value, we should know that they want to stop the invitation process.
			
			if( $inviteMail instanceof CInvitationMail )
			{
				if( $inviteMail->hasError() )
				{
					$objResponse->addAssign('invitation-error' , 'innerHTML' , $inviteMail->getError() );
			
					return $objResponse->sendResponse();
				}
				else
				{
					// Once stored, we need to store selected user so they wont be invited again
					$invitation		=& JTable::getInstance( 'Invitation' , 'CTable' );
					$invitation->load( $callback , $cid );
	
					if( !empty( $values['friends'] ) )
					{
						if( !$invitation->id )
						{
							// If the record doesn't exists, we need add them into the
							$invitation->cid		= $cid;
							$invitation->callback	= $callback;
						}
						$invitation->users	= empty( $invitation->users ) ? implode( ',' , $values[ 'friends' ] ) : $invitation->users . ',' . implode( ',' , $values[ 'friends' ] );
						$invitation->store();
					}
	
					// Add notification
					CFactory::load( 'libraries' , 'notification' );	
		 			CNotificationLibrary::add( 'groups.invite' , $my->id , $recipients , $inviteMail->getTitle() , $inviteMail->getContent() , '' , $inviteMail->getParams() );
				}
			}
			else
			{
				$objResponse->addScriptCall( JText::_('COM_COMMUNITY_INVITE_INVALID_RETURN_TYPE') );
				return $objResponse->sendResponse();
			}
		}
		else
		{
			$objResponse->addAssign('invitation-error' , 'innerHTML' , JText::_('COM_COMMUNITY_INVITE_NO_SELECTION') );
			
			return $objResponse->sendResponse();
		}

		$actions    = '<input type="button" class="button" onclick="cWindowHide();" value="' . JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON') . '"/>';
		$html		= JText::_( 'COM_COMMUNITY_INVITE_SENT' );		
		
		$objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_INVITE_FRIENDS'));
		$objResponse->addScriptCall('cWindowAddContent', $html, $actions);
		
		return $objResponse->sendResponse();
	}
	
	public function ajaxReport( $reportFunc , $pageLink )
	{
                $filter = JFilterInput::getInstance();
                $pageLink = $filter->clean($pageLink, 'string');
                $reportFunc = $filter->clean($reportFunc, 'string');

		$objResponse    = new JAXResponse();
		$config			= CFactory::getConfig();
		
		$reports		= JString::trim( $config->get( 'predefinedreports' ) );
		
		$reports		= empty( $reports ) ? false : explode( "\n" , $reports );

		$html = '';

		$argsCount		= func_num_args();

		$argsData		= '';
		
		if( $argsCount > 1 )
		{
			
			for( $i = 2; $i < $argsCount; $i++ )
			{
				$argsData	.= "\'" . func_get_arg( $i ) . "\'";
				$argsData	.= ( $i != ( $argsCount - 1) ) ? ',' : '';
			}
		}

		$tmpl			= new CTemplate();
		$tmpl->set( 'reports'	, $reports );
		$tmpl->set( 'reportFunc', $reportFunc );
		
		$html	= $tmpl->fetch( 'ajax.reporting' );
		ob_start();
?>
		<button class="button" onclick="joms.report.submit('<?php echo $reportFunc;?>','<?php echo $pageLink;?>','<?php echo $argsData;?>');" name="submit">
		<?php echo JText::_('COM_COMMUNITY_SEND_BUTTON');?>
		</button>
		<button class="button" onclick="javascript:cWindowHide();" name="cancel">
		<?php echo JText::_('COM_COMMUNITY_CANCEL_BUTTON');?>
		</button>
<?php
		$actions	= ob_get_contents();
		ob_end_clean();

		// Change cWindow title
		$objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_REPORT_THIS'));
		$objResponse->addScriptCall('cWindowAddContent', $html, $actions);
		
		return $objResponse->sendResponse();
	}
	
	public function ajaxSendReport()
	{
		$reportFunc		= func_get_arg( 0 );
		$pageLink		= func_get_arg( 1 );
		$message		= func_get_arg( 2 );

		$argsCount		= func_num_args();
		$method			= explode( ',' , $reportFunc );

		$args			= array();
		$args[]			= $pageLink;
		$args[]			= $message;
		
		for($i = 3; $i < $argsCount; $i++ )
		{
			$args[]		= func_get_arg( $i );
		}

		// Reporting should be session sensitive
		// Construct $output
		$uniqueString	= md5($reportFunc.$pageLink);
		$session = JFactory::getSession();

		
		if( $session->has('action-report-'. $uniqueString))
		{
			$output	= JText::_('COM_COMMUNITY_REPORT_ALREADY_SENT');
		}
		else
		{
			if( is_array( $method ) && $method[0] != 'plugins' )
			{
				$controller	= JString::strtolower( basename($method[0]) );
				
	 			require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'controllers' . DS . 'controller.php' );
	 			require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'controllers' . DS . $controller . '.php' );
	
				$controller	= JString::ucfirst( $controller );
	 			$controller	= 'Community' . $controller . 'Controller';
	 			$controller	= new $controller();
	 			
	 			
	 			$output		= call_user_func_array( array( &$controller , $method[1] ) , $args );
			}
			else if( is_array( $method ) && $method[0] == 'plugins' )
			{
				// Application method calls
				$element	= JString::strtolower( $method[1] );
				require_once( CPluginHelper::getPluginPath('community',$element) . DS . $element . '.php' );
				$className	= 'plgCommunity' . JString::ucfirst( $element );
				$output		= call_user_func_array( array( $className , $method[2] ) , $args );
			}
		}
		$session->set('action-report-'. $uniqueString, true);
		
		// Construct the action buttons $action
		ob_start();
?>
		<button class="button" onclick="javascript:cWindowHide();" name="cancel">
		<?php echo JText::_('COM_COMMUNITY_BUTTON_CLOSE_BUTTON');?>
		</button>
<?php
		$action	= ob_get_contents();
		ob_end_clean();
		
		// Construct the ajax response
		$objResponse	= new JAXResponse();

		$objResponse->addAssign('cwin_logo', 'innerHTML', JText::_('COM_COMMUNITY_REPORT_SENT'));
		$objResponse->addScriptCall('cWindowAddContent', $output, $action);
		
		return $objResponse->sendResponse();
	}
	
	public function ajaxEditWall( $wallId , $editableFunc )
	{
                $filter = JFilterInput::getInstance();
                $wallId = $filter->clean($wallId, 'int');
                $editableFunc = $filter->clean($editableFunc, 'string');

		$objResponse	= new JAXResponse();
		$wall			=& JTable::getInstance( 'Wall' , 'CTable' );
		$wall->load( $wallId );
		
		CFactory::load( 'libraries' , 'wall' );
		$isEditable		= CWall::isEditable( $editableFunc , $wall->id );
		
		if( !$isEditable )
		{
			$objResponse->addAlert(JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_EDIT') );
			return $objResponse->sendResponse();
		}

		CFactory::load( 'libraries' , 'comment' );
		$tmpl			= new CTemplate();
		$message		= CComment::stripCommentData( $wall->comment );
		$tmpl->set( 'message' , $message );
		$tmpl->set( 'editableFunc' , $editableFunc );
		$tmpl->set( 'id'	, $wall->id );
		
		$content		= $tmpl->fetch( 'wall.edit' );
		
		$objResponse->addScriptCall( 'joms.jQuery("#wall_' . $wallId . ' div.loading").hide();');
		$objResponse->addAssign( 'wall-edit-container-' . $wallId , 'innerHTML' , $content );
		
		return $objResponse->sendResponse();
	}
	
	public function ajaxUpdateWall( $wallId , $message , $editableFunc )
	{
                $filter = JFilterInput::getInstance();
                $wallId = $filter->clean($wallId, 'int');
                $element = $filter->clean($element, 'string');
                $editableFunc = $filter->clean($editableFunc, 'string');

		$wall			=& JTable::getInstance( 'Wall' , 'CTable' );
		$wall->load( $wallId );
		$objResponse	= new JAXresponse();
		
		if( empty($message) )
		{
			$objResponse->addScriptCall( 'alert' , JText::_('COM_COMMUNITY_EMPTY_MESSAGE') );
			return $objResponse->sendResponse();
		}
		

		CFactory::load( 'libraries' , 'wall' );
		$isEditable		= CWall::isEditable( $editableFunc , $wall->id );
		
		if( !$isEditable )
		{
			$response->addScriptCall('cWindowAddContent', JText::_('COM_COMMUNITY_NOT_ALLOWED_TO_EDIT'));
			return $objResponse->sendResponse();
		}
			
		CFactory::load( 'libraries' , 'comment' );
		
		// We don't want to touch the comments data.
		$comments		= CComment::getRawCommentsData( $wall->comment );
		$wall->comment	= $message;
		$wall->comment	.= $comments;
		$my				= CFactory::getUser();
		$data			= CWallLibrary::saveWall( $wall->contentid , $wall->comment , $wall->type , $my , false , $editableFunc , 'wall.content' , $wall->id );		
		
		$objResponse	= new JAXResponse();
		
		$objResponse->addScriptCall('joms.walls.update' , $wall->id , $data->content );

		return $objResponse->sendResponse();
	}
	
	public function ajaxGetOlderWalls($groupId, $discussionId, $limitStart)
	{
                $filter = JFilterInput::getInstance();
                $groupId = $filter->clean($groupId, 'int');
                $discussionId = $filter->clean($discussionId, 'int');
                $limitStart = $filter->clean($limitStart, 'int');

		$limitStart	= max(0, $limitStart);
		$response	= new JAXResponse();

		$my			= CFactory::getUser();
		$jconfig	= JFactory::getConfig();
		
		$groupModel		= CFactory::getModel( 'groups' );
		$isGroupAdmin	=   $groupModel->isAdmin( $my->id , $groupId );
		
		CFactory::load( 'libraries' , 'wall' );
		$html	= CWall::getWallContents( 'discussions' , $discussionId , $isGroupAdmin , $jconfig->get('list_limit') , $limitStart, 'wall.content','groups,discussion', $groupId);
		
		// parse the user avatar
		CFactory::load( 'helpers' , 'string' );
		$html = CStringHelper::replaceThumbnails($html);
		$html = CString::str_ireplace(array('{error}', '{warning}', '{info}'), '', $html);
		
		
		$config	= CFactory::getConfig();
		$order	= $config->get('group_discuss_order');
		
		if ($order == 'ASC')
		{
			// Append new data at Top.
			$response->addScriptCall('joms.walls.prepend' , $html );
		} else {
			// Append new data at bottom.
			$response->addScriptCall('joms.walls.append' , $html );
		}
		
		return $response->sendResponse();
	}
	
	/**
	 * Like an item. Update ajax count
	 * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
	 * @param mixed $itemId	    Unique id to identify object item
	 *
	 */
	public function ajaxLike( $element, $itemId )
	{
                $filter = JFilterInput::getInstance();
                $element = $filter->clean($element, 'string');
                $itemId = $filter->clean($itemId, 'int');

		if (!COwnerHelper::isRegisteredUser())
		{
			return $this->ajaxBlockUnregister();
		}

		// Load libraries
		CFactory::load( 'libraries' , 'like' );
		$like	=   new CLike();

		if( !$like->enabled($element) )
		{
			// @todo: return proper ajax error
			return;
		}

		
		$my		=   CFactory::getUser();
		$objResponse	=   new JAXResponse();

		
		$like->addLike( $element, $itemId );
		$html	=   $like->getHTML( $element, $itemId, $my->id );
		
		$objResponse->addScriptCall('__callback', $html);

		return $objResponse->sendResponse();
	}
	
	/**
	 * Dislike an item
	 * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
	 * @param mixed $itemId	    Unique id to identify object item
	 * 
	 */
	public function ajaxDislike( $element, $itemId )
	{
                $filter = JFilterInput::getInstance();
                $itemId = $filter->clean($itemId, 'int');
                $element = $filter->clean($element, 'string');

		if (!COwnerHelper::isRegisteredUser())
		{
			return $this->ajaxBlockUnregister();
		}

		// Load libraries
		CFactory::load( 'libraries' , 'like' );
		$dislike   =   new CLike();

		if( !$dislike->enabled($element) )
		{
			// @todo: return proper ajax error
			return;
		}
		
		$my		=   CFactory::getUser();
		$objResponse	=   new JAXResponse();

		
		$dislike->addDislike( $element, $itemId );
		$html = $dislike->getHTML( $element, $itemId, $my->id );

		$objResponse->addScriptCall('__callback', $html);
		
		return $objResponse->sendResponse();
	}

	/**
	 * Unlike an item
	 * @param string $element   Can either be core object (photos/videos) or a plugins (plugins,plugin_name)
	 * @param mixed $itemId	    Unique id to identify object item
	 *
	 */
	public function ajaxUnlike( $element, $itemId )
	{
                $filter = JFilterInput::getInstance();
                $itemId = $filter->clean($itemId, 'int');
                $element = $filter->clean($element, 'string');

		if (!COwnerHelper::isRegisteredUser())
		{
			return $this->ajaxBlockUnregister();
		}
		
		$my		=   CFactory::getUser();
		$objResponse	=   new JAXResponse();

		// Load libraries
		CFactory::load( 'libraries' , 'like' );
		$unlike	    =   new CLike();

		if( !$unlike->enabled($element) )
		{

		}
		else{
			$unlike->unlike( $element, $itemId );
			$html	    =	$unlike->getHTML( $element, $itemId, $my->id );

			$objResponse->addScriptCall('__callback', $html);
		}

		return $objResponse->sendResponse();
	}

	/**
	 *
	 * 
	 */
	public function ajaxAddTag($element, $id, $tagString)
	{
                $filter = JFilterInput::getInstance();
                $id = $filter->clean($id, 'int');
                $tagString = $filter->clean($tagString, 'string');
                $element = $filter->clean($element, 'string');

		$objResponse	=   new JAXResponse();

		// @todo: make sure user has the permission
		
		CFactory::load('libraries', 'tags');
		$tags = new CTags();
		$tagString = JString::trim($tagString);

		// If there is only 1 word, add a space so thet the next 'explode' call works
		$tagStrings = explode(',', $tagString);
		
		// @todo: limit string lenght
		
		foreach($tagStrings as $row)
		{
			// Need to trim unwanted char
			$row = JString::trim($row, " ,;:");

			// For each tag, we ucwords them for consistency
			$row = ucwords($row);
			
			// @todo: Send out warning or error message that the string is too short
			if(JString::strlen($row) >= CTags::MIN_LENGTH){
				// Only add to the tag list if add is successful
				if( $tags->add($element, $id, $row)){
					// @todo: get last tag id inserted
					$tagid = $tags->lastInsertId(); 
					
					// urlescape the string
					$row = CTemplate::escape($row);
					$objResponse->addScriptCall("joms.jQuery('#tag-list').append", "<li id=\"tag-".$tagid."\"><span class=\"tag-token\"><a href=\"javascript:void(0);\" onclick=\"joms.tag.list('".$row."')\">$row</a><a href=\"javascript:void(0);\" style=\"display:block\" class=\"tag-delete\" onclick=\"joms.tag.remove('".$tagid."')\">x</a></span></li>");
				}
			}
		}

		
		$objResponse->addScriptCall('joms.jQuery(\'#tag-addbox\').val', '');

		return $objResponse->sendResponse();
	}

	/**
	 * 
	 */
	public function ajaxRemoveTag($id)
	{
                $filter = JFilterInput::getInstance();
                $id = $filter->clean($id, 'int');

		$objResponse	=   new JAXResponse();
		$my		=   CFactory::getUser();
		
		// @todo: make sure user has the permission
		CFactory::load('libraries', 'tags');
		$tags = new CTags();
		
		$tag =&  JTable::getInstance( 'Tag' , 'CTable' );
		$tag->load($id);
		
		$table = $tags->getItemTable($tag);
		
		$allowEdit = $table->tagAllow($my->id);
		
		if($allowEdit)
		{
			$tags->delete($id);
			$objResponse->addScriptCall('joms.jQuery(\'#tag-'.$id.'\').remove');
		}
		
		return $objResponse->sendResponse();
	}

	/**
	 * Show a list of all recent items with the given tag
	 */
	public function ajaxShowTagged($tag)
	{
                $filter = JFilterInput::getInstance();
                $tag = $filter->clean($tag, 'string');

		$objResponse	=   new JAXResponse();
		
		CFactory::load('libraries', 'tags');
		$tags = new CTags();
		$html = $tags->getItemsHTML($tag);

		$objResponse->addScriptCall('cWindowAddContent', $html);

		return $objResponse->sendResponse();
	}

	public function ajaxStreamAdd($message, $attachment)
	{
                $filter = JFilterInput::getInstance();
                $message = $filter->clean($message, 'string');
                // $attachment pending filter

		$cache	= CFactory::getFastCache();
		$cache->clean(array('activities'));

		$my = CFactory::getUser();

		if (!COwnerHelper::isRegisteredUser())
		{
			return $this->ajaxBlockUnregister();
		}

		CFactory::load('libraries', 'activities');
		CFactory::load('libraries', 'userpoints');
		CFactory::load('helpers', 'linkgenerator');
		CFactory::load( 'libraries' , 'notification' );
                
		//@rule: In case someone bypasses the status in the html, we enforce the character limit.
		$config		= CFactory::getConfig();
		if( JString::strlen( $message ) > $config->get('statusmaxchar') )
		{
			$message	= JString::substr( $message , 0 , $config->get('statusmaxchar') );
		}

		$message	= JString::trim($message);
		$message	= CStringHelper::escape($message);

		$inputFilter = CFactory::getInputFilter(true);
		$message = $inputFilter->clean($message);

		$objResponse	= new JAXResponse();
		
		$rawMessage	= $message;
		
		// @rule: Autolink hyperlinks
		$message	= CLinkGeneratorHelper::replaceURL( $message );
		
		// @rule: Autolink to users profile when message contains @username
		$message	= CLinkGeneratorHelper::replaceAliasURL( $message );

		// @rule: Spam checks
		if( $config->get( 'antispam_akismet_status') )
		{
			CFactory::load( 'libraries' , 'spamfilter' );

			$filter	= CSpamFilter::getFilter();
			$filter->setAuthor( $my->getDisplayName() );
			$filter->setMessage( $message );
			$filter->setEmail( $my->email );
			$filter->setURL( CRoute::_('index.php?option=com_community&view=profile&userid=' . $my->id ) );
			$filter->setType( 'message' );
			$filter->setIP( $_SERVER['REMOTE_ADDR'] );

			if( $filter->isSpam() )
			{
				$objResponse->addAlert( JText::_('COM_COMMUNITY_STATUS_MARKED_SPAM') );
				return $objResponse->sendResponse();
			}
		}

		$attachment	= json_decode($attachment, true);

		//respect wall setting before adding activity
		CFactory::load( 'helpers' , 'friends' );
		CFactory::load('helper', 'owner');
		if (!COwnerHelper::isCommunityAdmin() && isset($attachment['target']) && 
				 $config->get('lockprofilewalls') && !CFriendsHelper::isConnected( $my->id , $attachment['target'] ) ) {
			return;
		}

		switch($attachment['type'])
		{
			case "message":

				if(!empty($message))
				{
					//only update user status if share messgage is on his profile
					if (COwnerHelper::isMine($my->id,$attachment['target'])){
						//save the message
						$status		=& $this->getModel('status');
						$status->update($my->id, $rawMessage, $attachment['privacy'] );
	
						//set user status for current session.
						$today		=& JFactory::getDate();
						$message2	= (empty($message)) ? ' ' : $message;
						$my->set( '_status' , $rawMessage );
						$my->set( '_posted_on' , $today->toMySQL());
						
						// Order of replacement
						$order   = array("\r\n", "\n", "\r");
						$replace = '<br />';

						// Processes \r\n's first so they aren't converted twice.
						$messageDisplay = str_replace($order, $replace, $message);
						
						//update user status
						$objResponse->addScriptCall("joms.jQuery('#profile-status span#profile-status-message').html('" . addslashes( $messageDisplay ) . "');");
					}

					//push to activity stream
					$privacyParams	= $my->getParams();

					$act = new stdClass();
					$act->cmd          = 'profile.status.update';
					$act->actor        = $my->id;
					$act->target       = $attachment['target'];
					// Experimental: special case to profile messages,
					// now trying to  make it viewer sensitive.
					// we keep the message and process the actor and
					// target in activity library.
					// Hope we don't break anything. *cross finger*
					//$act->title		   = '{actor} '.$message;
					$act->title			= $message;
					$act->content	   = '';
					$act->app		   = 'profile';
					$act->cid		   = $my->id;
					$act->access	   = $attachment['privacy'];
					$act->comment_id   = CActivities::COMMENT_SELF;
					$act->comment_type = 'profile.status';
					$act->like_id 	   = CActivities::LIKE_SELF;
					$act->like_type    = 'profile.status';

					CActivityStream::add($act);
					CUserPoints::assignPoint('profile.status.update');

                                        $recipient = CFactory::getUser($attachment['target']);
                                        $params			= new CParameter( '' );
                                        $params->set( 'actorName' , $my->getDisplayName() );
                                        $params->set( 'recipientName', $recipient->getDisplayName());

                                        CNotificationLibrary::add( 'profile.status.update' , $attachment['target'] , $my->id , JText::sprintf('COM_COMMUNITY_FRIEND_WALL_POST', $my->getDisplayName() ) , '' , 'wall.post' , $params);

                                        $objResponse->addScriptCall('__callback', '');
				}

				break;

			case "photo":

				$photoId = $attachment['id'];
				$privacy = $attachment['privacy'];

				$photo	= JTable::getInstance('Photo', 'CTable');
				$photo->load($photoId);

				$photo->caption = $message;				
				$photo->permissions = $privacy;
				$photo->published = 1;
				$photo->status = 'ready';
				$photo->store();

				// Trigger onPhotoCreate
				CFactory::load( 'libraries' , 'apps' );
				$apps =& CAppPlugins::getInstance();
				$apps->loadApplications();
				$params = array();
				$params[] = &$photo;
				$apps->triggerEvent( 'onPhotoCreate' , $params );

				$album	= JTable::getInstance('Album', 'CTable');
				$album->load($photo->albumid);

				$act = new stdClass();
				$act->cmd 		= 'photo.upload';
				$act->actor   	= $my->id;
				$act->access	= $attachment['privacy'];
				$act->target  	= ($attachment['target']==$my->id) ? 0 : $attachment['target'];
				$act->title	  	= JText::sprintf('COM_COMMUNITY_ACTIVITIES_UPLOAD_PHOTO' , '{photo_url}', $album->name );
				$act->content	= ''; // Generated automatically by stream. No need to add anything 
				$act->app		= 'photos';
				$act->cid		= $album->id;
				$act->location	= $album->location;
				
				$act->comment_id   = CActivities::COMMENT_SELF;
				$act->comment_type = 'photos.album';
				$act->like_id 	   = CActivities::LIKE_SELF;
				$act->like_type    = 'photos.album';

				$albumUrl	= 'index.php?option=com_community&view=photos&task=album&albumid=' . $album->id .  '&userid=' . $my->id;
				$albumUrl	= CRoute::_($albumUrl);
				
				$photoUrl	= 'index.php?option=com_community&view=photos&task=photo&albumid=' . $album->id .  '&userid=' . $photo->creator . '#photoid=' . $photo->id;
				$photoUrl	= CRoute::_($photoUrl);
				
				$params = new CParameter('');
			 	$params->set('multiUrl'	, $albumUrl );
				$params->set('photoid'	, $photo->id);
				$params->set('action'	, 'upload' );
				$params->set('photo_url', $photoUrl );

				// Add activity logging
				CFactory::load ( 'libraries', 'activities' );
				CActivityStream::add( $act , $params->toString() );

				// Add user points
				CFactory::load( 'libraries' , 'userpoints' );
				CUserPoints::assignPoint('photo.upload');
				
				$objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_PHOTO_UPLOADED_SUCCESSFULLY', $photo->caption));
				
				break;

			case "video":
				// attachment id
				$cid	 = $attachment['id'];
				$privacy = $attachment['privacy'];
				
				$video	= JTable::getInstance('Video', 'CTable');
				$video->load($cid);
				$video->status	= 'ready';
				$video->permissions = $privacy;
				$video->store();
                                
                                
				
				// Add activity logging
				$url	= $video->getViewUri(false);
                               
				
				$act			= new stdClass();
				$act->cmd 		= 'videos.upload';
				$act->actor		= $my->id;
				$act->target	= ($attachment['target']==$my->id) ? 0 : $attachment['target'];
				$act->access	= $privacy;
				//filter empty message
                                $act->title = (empty($message)) ? JText::sprintf('COM_COMMUNITY_VIDEOS_POST_VIDEO' , '{video_url}' , $video->getTitle()) : JText::sprintf('COM_COMMUNITY_VIDEOS_POST_VIDEO_STATUS' , $message);
				$act->app		= 'videos';
				$act->content	= '';
				$act->cid		= $video->id;
				$act->location	= $video->location;
				
				$act->comment_id 	= $video->id;
				$act->comment_type 	= 'videos';
		
				$act->like_id 	= $video->id;
				$act->like_type	= 'videos';				
				
				$params = new CParameter('');
				$params->set( 'video_url', $url );
				
				CFactory::load ( 'libraries', 'activities' );
				CActivityStream::add( $act , $params->toString() );
				
				// @rule: Add point when user adds a new video link
				CFactory::load( 'libraries' , 'userpoints' );
				CUserPoints::assignPoint('video.add', $video->creator);
				
				$this->cacheClean(array(COMMUNITY_CACHE_TAG_VIDEOS,COMMUNITY_CACHE_TAG_FRONTPAGE,COMMUNITY_CACHE_TAG_FEATURED,COMMUNITY_CACHE_TAG_VIDEOS_CAT,COMMUNITY_CACHE_TAG_ACTIVITIES));

				$objResponse->addScriptCall('__callback', JText::sprintf('COM_COMMUNITY_VIDEOS_UPLOAD_SUCCESS', $video->title));

				break;
				
			case "event":

				require_once(COMMUNITY_COM_PATH.DS.'controllers'.DS.'events.php');

				$eventController = new CommunityEventsController();

				// Assign default values where necessary
				$attachment['description'] = $message;
				$attachment['ticket']      = 0;
				$attachment['offset']      = 0;

				$event = $eventController->ajaxCreate($attachment, $objResponse);

				$objResponse->addScriptCall('__callback', '');
				
				break;

			case "link":
				break;
		}

		$activities = CActivities::getActivitiesByFilter($attachment['filter'], $attachment['target']);

		$objResponse->addAssign('activity-stream-container', 'innerHTML', $activities);

		return $objResponse->sendResponse();
	}
	
	
	/**
	 * Add comment to the stream
	 *
	 * @param int	$actid acitivity id
	 * @param string $comment
	 * @return obj
	 */
	public function ajaxStreamAddComment($actid, $comment)
	{
                $filter = JFilterInput::getInstance();
                $actid = $filter->clean($actid, 'int');
                $comment = $filter->clean($comment, 'string');

		$my = CFactory::getUser();
		$objResponse	=   new JAXResponse();
		$wallModel = CFactory::getModel( 'wall' );
		CFactory::load('libraries', 'wall');
		CFactory::load('libraries', 'activities');
		CFactory::load('helpers', 'friends');
		CFactory::load('helper', 'owner');

		// Pull the activity record and find out the actor
		// only allow comment if the actor is a friend of current user
		$act =& JTable::getInstance('Activity', 'CTable');
		$act->load($actid);

		if( CFriendsHelper::isConnected($act->actor, $my->id) ||
				COwnerHelper::isCommunityAdmin()){
				 
			$table =& JTable::getInstance('Wall', 'CTable');
			$table->type 		= $act->comment_type;
			$table->contentid 	= $act->comment_id;
			$table->post_by 	= $my->id;
			$table->comment 	= $comment;
			$table->store();

			$cache	= CFactory::getFastCache();
			$cache->clean(array('activities'));

			$comment = CWall::formatComment($table);
			$objResponse->addScriptCall('joms.miniwall.insert', $actid, $comment);
		}
		else
		{
			// Cannot comment on non-friend stream.
			$objResponse->addAlert('Permission denied');
		}
			
		return $objResponse->sendResponse();
	}

	/**
	 * Remove a wall comment
	 *
	 * @param int $actid
	 * @param int $wallid
	 */
	public function ajaxStreamRemoveComment($wallid){
                $filter = JFilterInput::getInstance();
                $wallid = $filter->clean($wallid, 'int');

		$my = CFactory::getUser();
		$objResponse	=   new JAXResponse();

		CFactory::load('helper', 'owner');

		//@todo: check permission. Find the activity id that
		// has this wall's data. Make sure actor is friend with
		// current user

		$table =& JTable::getInstance('Wall', 'CTable');
		$table->load($wallid);
		$table->delete();

		$objResponse->addScriptCall('joms.miniwall.delete', $wallid);
		return $objResponse->sendResponse();
	}
	
	/**
	 * Fill up the 'all comment fields with.. all comments
	 *
	 */
	public function ajaxStreamShowComments($actid)
	{
                $filter = JFilterInput::getInstance();
                $actid = $filter->clean($actid, 'int');

		$objResponse	=   new JAXResponse();
		$wallModel = CFactory::getModel( 'wall' );
		CFactory::load('libraries', 'wall');
		CFactory::load('libraries', 'activities');

		// Pull the activity record and find out the actor
		// only allow comment if the actor is a friend of current user
		$act =& JTable::getInstance('Activity', 'CTable');
		$act->load($actid);

		
		$comments = $wallModel->getAllPost($act->comment_type, $act->comment_id);
		$commentsHTML = '';
		foreach($comments as $row)
		{
			$commentsHTML .= CWall::formatComment($row);
		}
		
		$objResponse->addScriptCall('joms.miniwall.loadall', $actid, $commentsHTML);
		
		return $objResponse->sendResponse();
	}
	
	/**
	 *
	 */
	public function ajaxStreamAddLike($actid)
	{
                $filter = JFilterInput::getInstance();
                $actid = $filter->clean($actid, 'int');

		$objResponse	=   new JAXResponse();
		
		$wallModel = CFactory::getModel( 'wall' );
		CFactory::load('libraries', 'like');
		CFactory::load('libraries', 'wall');
		
		$like = new CLike();
		
		$act =& JTable::getInstance('Activity', 'CTable');
		$act->load($actid);
		
		// Count before the add
		$oldLikeCount = $like->getLikeCount($act->like_type, $act->like_id);

		$like->addLike($act->like_type, $act->like_id);

		$likeCount = $like->getLikeCount($act->like_type, $act->like_id);

		// If the like count is 1, then, the like bar most likely not there before
		// but, people might just click twice, hence the need to compare it before
		// the actual like
		if($likeCount == 1 && $oldLikeCount != $likeCount){
			// Clear old like status
			$objResponse->addScriptCall("joms.jQuery('#wall-cmt-{$actid} .wallicon-like').remove", '');
			$objResponse->addScriptCall("joms.jQuery('#wall-cmt-{$actid}').prepend", '<div class="wallcmt wallinfo wallicon-like"></div>');
		}
		
		$this->_streamShowLikes( $objResponse, $actid, $act->like_type, $act->like_id );
		$script = "joms.jQuery('#like_id".$actid."').replaceWith('<a id=like_id".$actid." href=#unlike onclick=\"jax.call(\'community\',\'system,ajaxStreamUnlike\',".$actid.");return false;\">". JText::_('COM_COMMUNITY_UNLIKE')."</a>');";
		$objResponse->addScriptCall($script);
		
		return $objResponse->sendResponse();
	}

	/**
	 *
	 */
	public function ajaxStreamUnlike($actid)
	{
                $filter = JFilterInput::getInstance();
                $actid = $filter->clean($actid, 'int');
                
		$objResponse	=   new JAXResponse();

		$wallModel = CFactory::getModel( 'wall' );
		CFactory::load('libraries', 'like');
		CFactory::load('libraries', 'wall');

		$like = new CLike();

		$act =& JTable::getInstance('Activity', 'CTable');
		$act->load($actid);

		$like->unlike($act->like_type, $act->like_id);

		$this->_streamShowLikes( $objResponse, $actid, $act->like_type, $act->like_id );
		$script = "joms.jQuery('#like_id".$actid."').replaceWith('<a id=like_id".$actid." href=#like onclick=\"jax.call(\'community\',\'system,ajaxStreamAddLike\',".$actid.");return false;\">". JText::_('COM_COMMUNITY_LIKE')."</a>');";
		$objResponse->addScriptCall($script);
		
		return $objResponse->sendResponse();
	}
	
	
	/**
	 * List down all people who like it
	 *
	 */
	public function ajaxStreamShowLikes($actid)
	{
                $filter = JFilterInput::getInstance();
                $actid = $filter->clean($actid, 'int');

		$objResponse	=   new JAXResponse();
		$wallModel = CFactory::getModel( 'wall' );
		CFactory::load('libraries', 'like');
		CFactory::load('libraries', 'wall');
		CFactory::load('libraries', 'activities');
	
		// Pull the activity record
		$act = JTable::getInstance('Activity', 'CTable');
		$act->load($actid);
		
		$this->_streamShowLikes( $objResponse, $actid, $act->like_type, $act->like_id );
		
		return $objResponse->sendResponse();		
	} 	
	
	/**
	 * Display the full list of people who likes this stream item
	 *
	 * @param <type> $objResponse
	 * @param <type> $actid
	 * @param <type> $like_type
	 * @param <type> $like_id
	 */
	private function _streamShowLikes($objResponse, $actid, $like_type, $like_id)
	{
		CFactory::load('libraries', 'like');
		CFactory::load('helpers', 'url');
		$my = CFactory::getUser();
		$like = new CLike();
		
		$likes = $like->getWhoLikes( $like_type, $like_id );

		$canUnlike = false;
		$likeHTML = '';
		$likeUsers = array();
		foreach($likes as $user)
		{
			$likeUsers[] = '<a href="'.CUrlHelper::userLink($user->id).'">'.$user->getDisplayName().'</a>';
			if($my->id == $user->id)
				$canUnlike = true;
		}

		if( count($likeUsers) == 0)
		{
			$likeHTML = JText::_('COM_COMMUNITY_NO_ONE_LIKE_THIS');
		}
		else
		{
			$likeHTML = implode(", ", $likeUsers);
			$likeHTML = CStringHelper::isPlural( count($likeUsers) ) ? JText::sprintf('COM_COMMUNITY_LIKE_THIS_MANY_LIST', $likeHTML) : JText::sprintf('COM_COMMUNITY_LIKE_THIS_LIST', $likeHTML);
		}
		// Append (Unlike) if necessary
		//if( $canUnlike )
		//{
		//	$likeHTML .= ' <a href="#unlike" onclick="jax.call(\'community\', \'system,ajaxStreamUnlike\', \''.$actid. '\' );return false;">Unlike</a>';
		//}
		
		// When we show all, we hide the count, the "3 people like this"
		$objResponse->addScriptCall("joms.jQuery('*[id$=profile-newsfeed-item{$actid}] .wallicon-like').html", "$likeHTML");
		
	}
}
