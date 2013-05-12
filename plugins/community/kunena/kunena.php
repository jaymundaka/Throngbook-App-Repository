<?php
/**
* @category	Plugins
* @package		JomSocial
* @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
* @license		GNU/GPL, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once( JPATH_ROOT .DS.'components' .DS.'com_community' .DS.'libraries' .DS.'core.php');
if(JFile::exists( JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_kunena' .DS. 'libraries' .DS. 'api.php' ))
{
    require_once(JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_kunena' .DS. 'libraries' .DS. 'api.php');
}
if(!class_exists('plgCommunityKunena'))
{
    class plgCommunityKunena extends CApplications
    {
            var $name 		= "My on Kunena";
            var $_name		= 'Kunena';
            var $_path		= '';

            var $db_prefix = "fb";

            function plgCommunityKunena(& $subject, $config)
        {
                    //from Kunena v1.6.3, table prefix has been changed
                    $db =& JFactory::getDBO();
                    $sql = 'show tables like ' . $db->Quote('%_kunena_users');

                    $db->setQuery($sql);
                    $userTable = $db->loadObject();

                    if($userTable){
                            $this->db_prefix = "kunena";
                    }

                    parent::__construct($subject, $config);
        }

            function onProfileDisplay()
            {
                    JPlugin::loadLanguage( 'plg_community_kunena', JPATH_ADMINISTRATOR );

                    $files	= JPATH_ROOT . DS . 'components' . DS. 'com_kunena' . DS . 'class.kunena.php';

                    if(JFile::exists( $files ))
                    {
                            $config	= CFactory::getConfig();

                            if( !$config->get('enablegroups') )
                            {
                                    return JText::_('PLG_KUNENA_GROUP_DISABLED');
                            }


                            $uri	= JURI::base();

                            $document	=& JFactory::getDocument();
                            // Attach CSS
                            //$css		= JURI::base() . 'plugins/community/kunena/style.css';
                            //$document->addStyleSheet($css);
                            $css	= ( C_JOOMLA_15 ) ? 'plugins/community/kunena/' : 'plugins/community/kunena/kunena/';
                            CAssets::attach( 'style.css' , 'css' , $css );
                            CFactory::load('helpers', 'time');

                            $groupsModel		=& CFactory::getModel( 'groups' );
                            $avatarModel		=& CFactory::getModel( 'avatar' );

                            $user		= CFactory::getRequestUser();
                            $userName	= $user->getDisplayName();

                            $groups		= $groupsModel->getGroups( $user->id );

                            $my	=& JFactory::getUser();

                            $username = $this->params->get('username');
                            $password = $this->params->get('password');

                            $db =& JFactory::getDBO();
                            // Get forum user info:
                            $sql = 'SELECT a.*, b.* FROM '.$db->nameQuote('#__'.$this->db_prefix.'_users').' as a '
                             . 'LEFT JOIN '.$db->nameQuote('#__users').' AS b on b.'.$db->nameQuote('id').'=a.'.$db->nameQuote('userid')
                             . 'WHERE a.'.$db->nameQuote('userid').'='.$db->Quote($user->id);

                            $db->setQuery($sql);

                    $userinfo = $db->loadObject();

                            if( $userinfo ){
                                    $usr_info = 1;
                                    //print_r($userinfo);
                                    $maxPost = intval($userinfo->posts);

                                    // Get latest forum topics

                                    // Search only within allowed group
                                    if (C_JOOMLA_15) {
                                            $query = 'select '.$db->nameQuote('gid')
                                                            .' from '.$db->nameQuote('#__users')
                                                            .' where '.$db->nameQuote('id').'='.$db->Quote($my->id);
                                    } else {
                                            $query = 'SELECT b.' . $db->nameQuote( 'group_id' ) . ' as gid'
                                                            . ' FROM ' . $db->nameQuote('#__users') . ' as a, '
                                                            . $db->nameQuote('#__user_usergroup_map') . ' as b'
                                                            . ' WHERE a.' . $db->nameQuote('id') . '= b.' . $db->nameQuote('user_id')
                                                            . ' AND a.' . $db->nameQuote( 'id' ) . '=' .$db->Quote($my->id);
                                    }

                                    $db->setQuery($query);
                            $db->query();

                            $dse_groupid = $db->loadObjectList();

                            if (count($dse_groupid)) {
                                $group_id = $dse_groupid[0]->gid;
                            }
                            else {
                                $group_id = 0;
                            }

                            $maxCount = $this->params->get('count', 5);
                                    $query = 'SELECT a.* , b.'.$db->nameQuote('id').' as category, b.'.$db->nameQuote('name').' as catname, c.'.$db->nameQuote('hits').' AS threadhits'
                                                     .' FROM '.$db->nameQuote('#__'.$this->db_prefix.'_messages').' AS a, '.$db->nameQuote('#__'.$this->db_prefix.'_categories').' AS b, '.$db->nameQuote('#__'.$this->db_prefix.'_messages').' AS c, '.$db->nameQuote('#__'.$this->db_prefix.'_messages_text').' AS d'
                                                     .' WHERE a.'.$db->nameQuote('catid').' = b.'.$db->nameQuote('id')
                                     .' AND a.'.$db->nameQuote('thread').' = c.'.$db->nameQuote('id')
                                     .' AND a.'.$db->nameQuote('id').' = d.'.$db->nameQuote('mesid')
                                     .' AND a.'.$db->nameQuote('hold').' = '.$db->Quote('0')
                                     .' AND b.'.$db->nameQuote('published').' = '.$db->Quote('1')
                                     .' AND a.'.$db->nameQuote('userid').'='.$db->Quote($user->id)
                                     .' AND (b.'.$db->nameQuote('pub_access').'<='.$db->Quote($group_id).')'
                                     .' ORDER BY '.$db->nameQuote('time').' DESC'
                                     .' LIMIT 0, '.$maxCount;
                            $db->setQuery($query);

                            $items = $db->loadObjectList();
                            }else{
                                    $usr_info = 0;
                                    $userId = "";
                                    $userName = "";
                                    $items = "";
                            }

                            $fbItemid = '&amp;Itemid='.$this->getItemid();

                            $mainframe =& JFactory::getApplication();
                            $caching = $this->params->get('cache', 1);
                            if($caching)
                            {
                                    $caching = $mainframe->getCfg('caching');
                            }

                    $cache =& JFactory::getCache('plgCommunityKunena');
                    $cache->setCaching($caching);
                            $callback = array('plgCommunityKunena', '_getKunenaHTML');
                            $content = $cache->call($callback, $usr_info, $user->id, $userName, $items, $fbItemid);
                    }
                    else
                    {
                            //$content = "<div class=\"icon-nopost\"><img src='".JURI::base()."components/com_community/assets/error.gif' alt=\"\" /></div>";
                            //$content .= "<div class=\"content-nopost\" style=\"height:100%;\">".JText::_('PLG_KUNENA_NOT_INSTALLED')."</div>";

                            $content = "<table>
                                                    <tr>
                                                            <td style=\"vertical-align: top;padding:4px\">
                                                <img src='".JURI::base()."components/com_community/assets/error.gif' alt='' />
                                                    </td>
                                                    <td style=\"vertical-align: top;padding:4px\">
                                                             " .JText::_('PLG_KUNENA_NOT_INSTALLED') . "
                                                            </td>
                                                    </tr>
                                                    </table>";
                    }

                    return $content;
            }

            function _getKunenaHTML($usr_info, $userId, $userName, $items, $fbItemid){
                    ob_start();

                    if($usr_info){
                            if( !empty($items) ) {
                                    ?>
                                    <div id="community-kunena-wrap">
                                        <ul class="list">
                                    <?php
                                            foreach ($items as $item ){
                                                    $fbURL 		= JRoute::_("index.php?option=com_kunena&amp;func=view".$fbItemid."&amp;catid=" . $item->catid . "&amp;id=" . $item->id . "#" . $item->id);
                                                    $fbCatURL 	= JRoute::_("index.php?option=com_kunena".$fbItemid."&amp;func=showcat&amp;catid=" . $item->catid);
                                        $postDate	= new CDate($item->time);
                                        ?>


                                                    <?php
                                                    /*
                                                    echo '<div style="border-bottom: 1px solid rgb(204, 204, 204); margin: 0pt 0pt 5px; padding: 4px;">';
                                                    echo '<img src="' . $this->getTopicImoticon($item) . '" alt="emo" style="vertical-align: middle; margin: 0 5px 0 0;" />';
                                                    echo '<a href="'. $fbURL .'">' . stripslashes ($item->subject) . '</a> ';
                                                    echo 'in ' . '<a href="'. $fbCatURL .'">'. $item->catname .'</a> on '.  $postDate->toFormat(JText::_('DATE_FORMAT_LC2')) ;
                                                    echo '</div>';
                                                    */
                                                    ?>
                                                <li>
                                                    <div class="icon">
                                                        <img src="<?php echo plgCommunityKunena::getTopicImoticon($item); ?>" alt="" />
                                                    </div>
                                                    <div class="content">
                                                        <a href="<?php echo $fbURL;?>">
                                                                            <?php echo stripslashes ($item->subject); ?>
                                                                    </a> in
                                                                    <a href="<?php echo $fbCatURL; ?>"><?php echo $item->catname; ?></a>
                                                                    on <?php echo $postDate->toFormat(JText::_('DATE_FORMAT_LC2')); ?>
                                                    </div>
                                                    <div style="clear: both;"></div>
                                                </li>

                                            <?php
                                            }
                                    ?>
                                        </ul>
                                        <div style="clear: both;"></div>
                                    </div>
                                    <?php
                            } else {
                                    ?>
                                    <div class="icon-nopost">
                                <?php if( C_JOOMLA_15 ): ?>
                    <img src="<?php echo JURI::base(); ?>plugins/community/kunena/no-post.gif" alt="" />
                    <?php else: ?>
                    <img src="<?php echo JURI::base(); ?>plugins/community/kunena/kunena/no-post.gif" alt="" />
                    <?php endif; ?>
                            </div>
                            <div class="content-nopost">
                                <?php echo $userName . ' ' . JText::_('PLG_KUNENA_NO_DISCUSSION_JOIN'); ?>
                            </div>
                                    <?php
                            }
                    }else{
                            ?>
                    <div class="icon-nopost">
                        <?php if( C_JOOMLA_15 ): ?>
                <img src="<?php echo JURI::base(); ?>plugins/community/kunena/no-post.gif" alt="" />
                <?php else: ?>
                <img src="<?php echo JURI::base(); ?>plugins/community/kunena/kunena/no-post.gif" alt="" />
                <?php endif; ?>
                    </div>
                    <div class="content-nopost">
                        <?php echo JText::_('PLG_KUNENA_NO_FORUM_POST'); ?>
                    </div>
                            <?php
                     }

                    $contents	= ob_get_contents();
                    ob_end_clean();
                    return $contents;
            }

            /**
             * Return itemid for Kunena
             */
            function getItemid(){
                    $db =& JFactory::getDBO();
                    $Itemid = 0;
                    if (!defined("FB_FB_ITEMID")) {
                    if ($Itemid < 1) {
                            $db->setQuery('SELECT '.$db->nameQuote('id')
                                                    .' FROM '.$db->nameQuote('#__menu')
                                                    .' WHERE '.$db->nameQuote('link').' = '.$db->Quote('index.php?option=com_kunena')
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

            /**
             * Return path to topic emoticons
             * Sadly, for now, we will only return default, emoticons
             */
            function getTopicImoticon(&$item) {
                $version = 1.5;
                if(class_exists('Kunena'))
                {
                    $version = Kunena::version();
                }
                
                $emoticonPath = '';
                if( !defined('JB_URLEMOTIONSPATH' )) {
                        if($version <'1.6.0'){
                            $emoticonPath = JURI::base() . 'components/com_kunena/template/default/images/english/emoticons/';
                        } else {
                            $emoticonPath = JURI::base() . 'components/com_kunena/template/default/images/icons/';
                        }
                } else {
                        $emoticonPath = JB_URLEMOTIONSPATH;
                }

                 // Emotions
                $topic_emoticons = array ();
                if($version <'1.6.0'){

                    $topic_emoticons[0] = $emoticonPath . 'default.gif';
                    $topic_emoticons[1] = $emoticonPath . 'exclam.gif';
                    $topic_emoticons[2] = $emoticonPath . 'question.gif';
                    $topic_emoticons[3] = $emoticonPath . 'arrow.gif';
                    $topic_emoticons[4] = $emoticonPath . 'love.gif';
                    $topic_emoticons[5] = $emoticonPath . 'grin.gif';
                    $topic_emoticons[6] = $emoticonPath . 'shock.gif';
                    $topic_emoticons[7] = $emoticonPath . 'smile.gif';

                }else{
                    $topic_emoticons[0] = $emoticonPath . 'topic-default.png';
                    $topic_emoticons[1] = $emoticonPath . 'topic-exclam.png';
                    $topic_emoticons[2] = $emoticonPath . 'topic-question.png';
                    $topic_emoticons[3] = $emoticonPath . 'topic-arrow.png';
                    $topic_emoticons[4] = $emoticonPath . 'topic-love.png';
                    $topic_emoticons[5] = $emoticonPath . 'topic-grin.png';
                    $topic_emoticons[6] = $emoticonPath . 'topic-shock.png';
                    $topic_emoticons[7] = $emoticonPath . 'topic-smile.png';
                }
                return $topic_emoticons[$item->topic_emoticon];
            }
    }
}