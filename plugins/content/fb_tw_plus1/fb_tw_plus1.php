<?php
/**
 * @version  2.6
 * @Project  Facebook Like, Twitter and google +1 buttons
 * @author   Compago TLC
 * @package
 * @copyright Copyright (C) 2012 Compago TLC. All rights reserved.
 * @license  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );
if(!defined('FOO')) {
  define( 'DS', DIRECTORY_SEPARATOR );
}
$document = JFactory::getDocument();
$docType = $document->getType();
// only in html
if ($docType != 'html'){
  return;
}
require_once( JPATH_SITE . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'route.php' );

if(!function_exists('json_decode')) {
  function json_decode($json) {
    $comment = false;
    $out = '$x=';
    for ($i=0; $i<strlen($json); $i++) {
      if (!$comment) {
        if (($json[$i] == '{') || ($json[$i] == '['))
          $out .= ' array(';
        else if (($json[$i] == '}') || ($json[$i] == ']'))
          $out .= ')';
        else if ($json[$i] == ':')
          $out .= '=>';
        else
          $out .= $json[$i];
      } else
        $out .= $json[$i];
      if ($json[$i] == '"' && $json[($i-1)]!="\\")
        $comment = !$comment;
    }
    eval($out . ';');
    return $x;
  }
}
if(!function_exists('json_encode')){
  function json_encode($a=false) {
    // Some basic debugging to ensure we have something returned
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a)) {
      if (is_float($a)) {
        // Always use '.' for floats.
        return floatval(str_replace(',', '.', strval($a)));
      }
      if (is_string($a)) {
        static $jsonReplaces = array(array('\\', '/', "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); true; $i++) {
      if (key($a) !== $i) {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList) {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    } else {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}

jimport('joomla.plugin.plugin');
jimport('joomla.environment.browser');

class plgContentfb_tw_plus1 extends JPlugin {
  var $_fb = 0;
  var $_google = 0;
  var $_tw = 0;
  var $_in = 0;
  var $_pint = 0;

  function plgContentfb_tw_plus1( &$subject,$params ) {
    parent::__construct( $subject,$params );
  }

  function onContentPrepare($context, &$article, &$params, $page=0){
    if ($context == 'com_media.file') return;
    $ignore_pagination = $this->params->get( 'ignore_pagination');
    $view = JRequest::getCmd('view');
    if ((($view == 'article')||($view == 'productdetails'))&&($ignore_pagination==1)) {
      $this->InjectCode($article, $params ,0,$view);
    }
  }
  function onContentBeforeDisplay($context,&$article,&$params,$page=0){
	if ($context == 'com_media.file') return;
    $ignore_pagination = $this->params->get( 'ignore_pagination');
    $view = JRequest::getCmd('view');
    if (($view != 'article')||((($view == 'article')||($view == 'productdetails'))&&($ignore_pagination==0))) {
      $this->InjectCode($article, $params ,1,$view);
    }
  }
  function onBeforeCompileHead(){
    $view = JRequest::getCmd('view');
    $this->InjectHeadCode($view);
  }

  public function onContentAfterSave($context, &$article, $isNew) {
    if ($_REQUEST[jform][state]!='1') return;
    if ($_REQUEST[jform][access]!='1') return;
    if ($context == 'com_media.file') return;
    $enable_fb_autopublish      = $this->params->get( 'enable_fb_autopublish');
    $enable_twitter_autopublish = $this->params->get( 'enable_twitter_autopublish');
    //Enable autopublish only on the articles or categories where are rendered the share buttons
    $category_tobe_excluded     = $this->params->get('category_tobe_excluded_buttons', '' );
    $content_tobe_excluded      = $this->params->get('content_tobe_excluded_buttons', '' );
    $excludedContentList        = @explode ( ",", $content_tobe_excluded );
    if ($article->id!=null) {
      if ( in_array ( $article->id, $excludedContentList )) {
        return;
      }
      if (is_array($category_tobe_excluded ) && in_array ( $article->catid, $category_tobe_excluded )) {
        return;
      }
    } else {
      if (is_array($category_tobe_excluded ) && in_array ( JRequest::getCmd('id'), $category_tobe_excluded )) return;
    }
    //Enable autopublish only on "apply" action
    if ($_REQUEST['task']!='apply') {
      return true;
    }
    if (($enable_fb_autopublish||$enable_twitter_autopublish)&&(!extension_loaded('curl'))) {
      JFactory::getApplication()->enqueueMessage( JText::_('Facebook or Twitter Autopublish is not possible because CURL extension is not loaded.'), 'error' );
      return true;
    }
    //Facebook autopublish
    if (($context == "com_content.article")&&($enable_fb_autopublish)) {
      if (!class_exists('Facebook', false)) {
        require_once('facebook'.DS.'facebook.php');
      }
      $app_id            = $this->params->get('app_id');
      $fb_secret_key     = $this->params->get('fb_secret_key');
      $fb_extra_params   = $this->params->get('fb_extra_params');
      $fb_ids            = $fb_extra_params->fb_ids;
      $token             = $fb_extra_params->fb_token;

      //if the configuration is complete proceeed with the post on FB walls
      if (($app_id!='')&&($fb_secret_key!='')&&(count($fb_ids)>0)&&($token!='')) {
        $title       = $this->getTitle($article);
        $caption     = '';
        $url         = JUri::root().ContentHelperRoute::getArticleRoute($article->id.':'.$article->alias, $article->catid);
        $router      = JSite::getInstance('site')->getRouter('site');
        $url         = $router->build($url);
        $url         = str_replace('administrator/', '', $url);
        $description = $this->getDescription($article,'article');
        if ($this->params->get('fb_autopublish_image','1')=='1') {
          $images      = $this->getPicture($article,'article');
          if (count($images)>0) { 
            $pic       = $images[0];
          } else { 
            $pic       = '';
          }
        } else {
          $pic       = '';
        }
        if ($isNew) {
          $msg         = $this->params->get('fb_text_new','');
        }  else {
          $msg         = $this->params->get('fb_text_old','Update');
        }
        $facebook = new Facebook(array(
           'appId'  => $app_id,
           'secret' => $fb_secret_key,
           'cookie' => true
        ));
        $ok = true;
        try {
          $info_accounts=$facebook->api('/me/accounts',array('access_token' => $token ));
          $info_groups=$facebook->api('/me/groups',array('access_token' => $token ));
        } catch(FacebookApiException $e) {
          JError::raiseWarning('1', 'Facebook error: ' . $e->getMessage());
          $ok = false;
        }

        if ($ok) {
          $accounts=$info_accounts['data'];
          foreach ($accounts as $account) {
            if (in_array($account['id'],$fb_ids)) {
              $ok = true;
              try {
                $token = $account['access_token'];
                $facebook->api('/'.$account['id'].'/feed','post',
                                         array('access_token' => $token,
                                               'message'      => $msg,
                                               'link'         => $url,
                                               'picture'      => $pic,
                                               'name'         => $title,
                                               'caption'      => $caption,
                                               'description'  => $description,
                                              )
                                        );
              } catch(FacebookApiException $e) {
                JError::raiseWarning('1', 'Facebook error: ' . $e->getMessage());
                $ok = false;
              }
              if ($ok) {
                $info=$facebook->api('/'.$account['id'].'/',array('access_token' => $token ));
                JFactory::getApplication()->enqueueMessage( JText::_('Content published on Facebook: ')."<a href='".$info['link']."'>".$info['name']."</a>", 'message' );
              }
            }
          }
        }
      } else {
        if ($app_id==''){JFactory::getApplication()->enqueueMessage( JText::_('App ID is missing'), 'error' ); }
        if ($fb_secret_key==''){JFactory::getApplication()->enqueueMessage( JText::_('App secret key is missing'), 'error' ); }
        if (count($fb_ids)==0){JFactory::getApplication()->enqueueMessage( JText::_('Must be specified on at least one Facebook account ID where to publish the article'), 'error' ); }
        if ($token==''){JFactory::getApplication()->enqueueMessage( JText::_('Valid access token missing'), 'error' ); }
      }
    }


    //Twitter autopublish
    if (($context == "com_content.article")&&($enable_twitter_autopublish)) {
      if (!class_exists('TwitterOAuth', false)) {
        require_once('twitteroauth'.DS.'twitteroauth.php');
      }
      $consumer_key       = $this->params->get( 'twitter_consumer_key','');
      $consumer_secret    = $this->params->get( 'twitter_consumer_secret','');
      $oauth_token        = $this->params->get( 'twitter_oauth_token','');
      $oauth_token_secret = $this->params->get( 'twitter_oauth_token_secret','');
      $use_tinyurl        = $this->params->get( 'twitter_use_tinyurl',0);
      if (($consumer_key!='')&&($consumer_secret!='')&&
          ($oauth_token!='')&&($oauth_token_secret!='')) {
        $conn = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
        if (!$conn) {
          JFactory::getApplication()->enqueueMessage( JText::_('Connection error occurred'), 'error' );
          die();
        }
        $title    = $this->getTitle($article);
        $url      = JURI::root().ContentHelperRoute::getArticleRoute($article->id, $article->catid);
        if ($use_tinyurl) {
          $url     = $this->getTinyurl($url);
        }
        if ($isNew) {
          $msg     = (substr($title, 0, 100)." ".$url);
        } else {
          $msg     = ("Update : " . substr($title, 0, 100)." ".$url);
        }
        $status    = $conn->post('statuses/update', array('status' => $msg));
        if (!isset($status->error)) {
          JFactory::getApplication()->enqueueMessage( JText::_('Content published on Twitter'), 'message' );
        } else {
          JFactory::getApplication()->enqueueMessage( JText::_('Content published on Twitter: '.$status->error), 'error' );
        }
      } else {
        if ($consumer_key==''){JFactory::getApplication()->enqueueMessage( JText::_('Consumer key is missing'), 'error' ); }
        if ($consumer_secret==''){JFactory::getApplication()->enqueueMessage( JText::_('Consumer secret key is missing'), 'error' ); }
        if ($oauth_token==''){JFactory::getApplication()->enqueueMessage( JText::_('Oauth token is missing'), 'error' ); }
        if ($oauth_token_secret==''){JFactory::getApplication()->enqueueMessage( JText::_('Oauth token secret key is missing'), 'error' ); }
      }
    }
    return true;
  }

  private function getTinyurl($url) {
    $data = (trim($this->get_url_contents('http://tinyurl.com/api-create.php?url=' . $url)));
    if (!$data)
      return $url;
    return $data;
  }

  private function getProtocol() {
    if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
      || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
    ) {
      $protocol = 'https://';
    }
    else {
      $protocol = 'http://';
    }
    return $protocol;
  }

  private function getCurrentUrl($mode=0) {
    $protocol = $this->getProtocol();
    $currentUrl = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $parts = parse_url($currentUrl);
    $query = '';
    if (!empty($parts['query'])) {
      // drop known fb params
      $params = explode('&', $parts['query']);
      $retained_params = array();
      foreach ($params as $param) {
          $retained_params[] = $param;
      }
      unset($retained_params['state']);
      unset($retained_params['code']);
      if($mode==1){
        if ($_REQUEST['task']=='apply') {
          $retained_params[] = 'view=article';
        } elseif ($_REQUEST['task']=='save2new') {
          unset($retained_params['id']);
        }
      } else {
        $retained_params[] = 'view=article';
      }
      if (!empty($retained_params)) {
        $query = '?'.implode($retained_params, '&');
      }
    }

    // use port if non default
    $port =
      isset($parts['port']) &&
      (($protocol === 'http://' && $parts['port'] !== 80) ||
       ($protocol === 'https://' && $parts['port'] !== 443))
      ? ':' . $parts['port'] : '';

    // rebuild
    return $protocol . $parts['host'] . $port . $parts['path'] . $query;
  }

  private function SetParams($key,$value) {
    $db=& JFactory::getDBO();
    $db->setQuery("SELECT `params` FROM `#__extensions` WHERE `name`= 'Content - Facebook-Twitter-Google+1';");
    $contents = $db->loadObject();
    $params = json_decode($contents->params);
    $params->{$key}=$value;
    $params = json_encode($params);
    $db->setQuery("UPDATE `#__extensions` SET ".$db->nameQuote('params')."= '".$db->getEscaped($params)."' WHERE `name`= 'Content - Facebook-Twitter-Google+1';");
    $result = $db->query();
    $db->freeResult($result);
  }


  private function InjectHeadCode($view){
    $document                 = & JFactory::getDocument();
    $enable_like              = $this->params->get( 'enable_like');
    $enable_share             = $this->params->get( 'enable_share');
    $enable_comments          = $this->params->get( 'enable_comments');

    if (($enable_share==1)||($enable_like==1)||($enable_comments==1)) {
      $config                   =& JFactory::getConfig();
      if (method_exists($config,getValue)) {
        $site_name                = $config->getValue('config.sitename');
      } else {
        $site_name                = $config->get('config.sitename');
      }
      $description              = $this->params->get('description');
      $enable_admin             = $this->params->get('enable_admin','0');
      $enable_app               = $this->params->get('enable_app','0');
      $admin_id                 = $this->params->get('admin_id');
      $app_id                   = $this->params->get('app_id');
      if ($this->params->get('auto_language')) {
        $language_fb            = str_replace('-', '_', JFactory::getLanguage()->getTag());
      } else {
        $language_fb            = $this->params->get('language_fb');
      }
      $meta                     = "";
      $head_data = array();
      foreach( $document->getHeadData() as $tmpkey=>$tmpval ){
        if(!is_array($tmpval)){
          $head_data[] = $tmpval;
        } else {
          foreach( $tmpval as $tmpval2 ){
            if(!is_array($tmpval2)){
              $head_data[] = $tmpval2;
            }
          }
        }
      }
      $head = implode(',',$head_data);
      if (($description==0)&&(preg_match('/<meta property="og:description"/i',$head)==0)&&($view!='productdetails')){
        $description = $document->getMetaData("description");
        $meta .= "<meta property=\"og:description\" content=\"$description\"/>".PHP_EOL;
      }
      if ($enable_admin==0) { $admin_id=""; }
      else {
        if (preg_match('/<meta property="fb:admins"/i',$head)==0){
          $meta .= "<meta property=\"fb:admins\" content=\"$admin_id\"/>".PHP_EOL;
        }
      }
      if ($enable_app==0) { $app_id=""; }
      else {
        if (preg_match('/<meta property="fb:app_id"/i',$head)==0){
          $meta .= "<meta property=\"fb:app_id\" content=\"$app_id\"/>".PHP_EOL;
        }
      }
      if (preg_match('/<meta property="og:locale"/i',$head)==0){
        $meta .= "<meta property=\"og:locale\" content=\"".$language_fb."\"/>".PHP_EOL;
      }
      if (preg_match('/<meta property="og:site_name"/i',$head)==0){
        $meta .= "<meta property=\"og:site_name\" content=\"$site_name\"/>".PHP_EOL;
      }
      $document->addCustomTag( PHP_EOL.$meta.PHP_EOL);
    }
  }

  private function InjectCode(&$article, &$params, $mode,$view){
    $format=JRequest::getCMD('format');
    if (($format=='pdf')||($format=='feed')) return;
    $document                 = & JFactory::getDocument();
    $position                 = $this->params->get( 'position',  '' );
    $enable_like              = $this->params->get( 'enable_like');
    $enable_share             = $this->params->get( 'enable_share');
    $enable_comments          = $this->params->get( 'enable_comments');
    $enable_twitter           = $this->params->get( 'enable_twitter');
    $enable_google            = $this->params->get( 'enable_google');
    $enable_in                = $this->params->get( 'enable_in');
    $enable_pint              = $this->params->get( 'enable_pint');
    $view_article_buttons     = $this->params->get( 'view_article_buttons');
    $view_frontpage_buttons   = $this->params->get( 'view_frontpage_buttons');
    $view_category_buttons    = $this->params->get( 'view_category_buttons');
    $view_article_comments    = $this->params->get( 'view_article_comments');
    $view_frontpage_comments  = $this->params->get( 'view_frontpage_comments');
    $view_category_comments   = $this->params->get( 'view_category_comments');
    $asynchronous_fb          = $this->params->get( 'asynchronous_fb',0);
    $asynchronous_twitter     = $this->params->get( 'asynchronous_twitter',0);
    $asynchronous_in          = $this->params->get( 'asynchronous_in',0);
    $asynchronous_pint        = $this->params->get( 'asynchronous_pint',0);
    $enable_view_comments     = 0;
    $enable_view_buttons      = 0;
    $enable_app               = $this->params->get('enable_app');
    $app_id                   = $this->params->get('app_id');
    $type                     = $this->params->get('type');
    $directyoutube            = $this->params->get('directyoutube',0);
    $meta                     = "";

    $title    = $this->getTitle($article);
    $url      = $this->getPageUrl($article);
    $basetitle= $document->getTitle();

    if ($view=='category'){
      $baseurl  = $this->getCatUrl($article);
    } else {
      $baseurl  = $document->getBase();
    }
    if (($enable_share==1)||($enable_like==1)||($enable_comments==1)||($enable_google==1)||($enable_twitter==1)||($enable_in==1)||($enable_pint==1)) {
      $head_data = array();
      foreach( $document->getHeadData() as $tmpkey=>$tmpval ){
        if(!is_array($tmpval)){
          $head_data[] = $tmpval;
        } else {
          foreach( $tmpval as $tmpval2 ){
            if(!is_array($tmpval2)){
              $head_data[] = $tmpval2;
            }
          }
        }
      }
      $head = implode(',',$head_data);
      if (($enable_share==1)||($enable_like==1)||($enable_comments==1)) {
        if ((preg_match('/<meta property="og:video"/i',$head)==0)){
          if (isset($article->text)) {
            $text=$article->text;
          } else {
            $text=$article->introtext;
          }
          if ($view == 'article'){
            if (preg_match('%<object.*(?:data|value)=[\\\\"\'](.*?\.(?:flv|swf))["\'].*?</object>%si', $text,$regsu)) {
              if ((preg_match('%<object.*width=["\'](.*?)["\'].*</object>%si', $text,$regsw))&&
                  (preg_match('%<object.*height=["\'](.*?)["\'].*</object>%si', $text,$regsh))) {
                if (preg_match('/^http/i',$regsu[1])) {
                  $video = $regsu[1];
                } else {
                  $video = JURI::root().preg_replace('#^/#','',$regsu[1]);
                }
                $type = "video";
              }
            } elseif (preg_match('%<iframe.*src=["\'](.*?(?:www\.(?:youtube|youtube-nocookie)\.com|vimeo.com)/(?:embed|v)/(?!videoseries).*?)["\'].*?</iframe>%si', $text,$regsu)) {
              if ((preg_match('%<iframe.*width=["\'](.*?)["\'].*</iframe>%si', $text,$regsw))&&
                  (preg_match('%<iframe.*height=["\'](.*?)["\'].*</iframe>%si', $text,$regsh))) {
                if ($directyoutube==0) {
                  $video = $url;
                } else {
                  $video = preg_replace('%embed/(?!videoseries)%i','v/',$regsu[1]);
                }
                $type = "video";
              }
            }
            if ($type == "video") {
              $meta .= "<meta property=\"og:video\" content=\"$video\"/>".PHP_EOL;
              $meta .= "<meta property=\"og:video:type\" content=\"application/x-shockwave-flash\"/>".PHP_EOL;
              $meta .= "<meta property=\"og:video:width\" content=\"$regsw[1]\">".PHP_EOL;
              $meta .= "<meta property=\"og:video:height\" content=\"$regsh[1]\">".PHP_EOL;
            }
          }         
        }
        if ((preg_match('/<meta property="og:type"/i',$head)==0)&&($enable_app==1)&&($app_id!="")) {
          if (($view == 'article')||($view == 'productdetails')) {
            $meta .= "<meta property=\"og:type\" content=\"$type\"/>".PHP_EOL;
          } else {
            $meta .= "<meta property=\"og:type\" content=\"website\"/>".PHP_EOL;
          }
        }
        $description  = $this->params->get('description');
        if (preg_match('/<meta property="og:description"/i',$head)==0){
          if (($description==1)||($description==1)) { //first paragraph
            if ($view == 'productdetails') {
              if (isset($article->product_s_desc)) {
                $description=htmlentities(strip_tags($article->product_s_desc),ENT_QUOTES, "UTF-8");
              } else {
                $description=htmlentities(strip_tags($article->product_desc),ENT_QUOTES, "UTF-8");
              }
            } elseif ($view == 'article') {
              if ($description==2) { //first 255 chars
                $description = htmlentities(mb_substr(strip_tags($obj->text), 0, 251)."... ",ENT_QUOTES, "UTF-8");
              } else { //first paragraph
                $content = htmlentities(strip_tags($obj->text),ENT_QUOTES, "UTF-8");
                $pos = strpos($content, '.');
                if ($pos === false) {
                  $description = $content;
                } else {
                  $description = substr($content, 0, $pos+1);
                }
              }             
            } else {
              $description = htmlentities(strip_tags($document->getMetaData("description")),ENT_QUOTES, "UTF-8");
            }
          } elseif ($view == 'productdetails') {
            if (isset($article->product_s_desc)) {
              $description=htmlentities(strip_tags($article->product_s_desc),ENT_QUOTES, "UTF-8");
            } else {
              $description=htmlentities(strip_tags($article->product_desc),ENT_QUOTES, "UTF-8");
            }
          } else {
            $description = htmlentities(strip_tags($document->getMetaData("description")),ENT_QUOTES, "UTF-8");
          }
          $meta .= "<meta property=\"og:description\" content=\"$description\"/>".PHP_EOL;
        }
        if (preg_match('/<meta property="og:image"/i',$head)==0){
          $images = $this->getPicture($article,$view);
          if (count($images) != 0) {
            foreach ($images as $value) {
              $meta .= "<meta property=\"og:image\" content=\"$value\"/>".PHP_EOL;
            }
          }
        }
        if (preg_match('/<meta property="og:url"/i',$head)==0) {
          if (($view == 'article')||($view == 'productdetails')) {
            $meta .= "<meta property=\"og:url\" content=\"$url\"/>".PHP_EOL;
          } else {
            $meta .= "<meta property=\"og:url\" content=\"$baseurl\"/>".PHP_EOL;
          }
        }
        if (preg_match('/<meta property="og:title"/i',$head)==0) {
          if (($view == 'article')||($view == 'productdetails')) {
            $meta .= "<meta property=\"og:title\" content=\"$title\"/>".PHP_EOL;
          } else {
            $meta .= "<meta property=\"og:title\" content=\"$basetitle\"/>".PHP_EOL;
          }
        }
        if (preg_match('/<meta property="my:fb"/i',$head)==0){
          $meta .= "<meta property=\"my:fb\" content=\"on\"/>".PHP_EOL;
          $this->_fb = 1;
        } else {
          $this->_fb = 2;
        }
      }
      if ($enable_google==1) {
        if (preg_match('/<meta property="my:google"/i',$head)==0){
          $meta .= "<meta property=\"my:google\" content=\"on\"/>".PHP_EOL;
          $this->_google = 1;
        } else {
          $this->_google = 2;
        }
      }
      if ($enable_twitter==1) {
        if (preg_match('/<meta property="my:tw"/i',$head)==0){
          $meta .= "<meta property=\"my:tw\" content=\"on\"/>".PHP_EOL;
          $this->_tw = 1;
        } else {
          $this->_tw = 2;
        }
      }
      if ($enable_in==1) {
        if (preg_match('/<meta property="my:in"/i',$head)==0){
          $meta .= "<meta property=\"my:in\" content=\"on\"/>".PHP_EOL;
          $this->_in = 1;
        } else {
          $this->_in = 2;
        }
      }
      if ($enable_pint==1) {
        if (preg_match('/<meta property="my:pint"/i',$head)==0){
          $meta .= "<meta property=\"my:pint\" content=\"on\"/>".PHP_EOL;
          $this->_pint = 1;
        } else {
          $this->_pint = 2;
        }
      }
      if ($meta!="") {
        $document->addCustomTag( PHP_EOL.$meta.PHP_EOL);
      }
    }

    if ((($view == 'article')||($view == 'productdetails'))&&($view_article_buttons)||
        ($view == 'featured')&&($view_frontpage_buttons)||
        ($view == 'category')&&($view_category_buttons)) {
      $enable_view_buttons = 1;
    }
    if ((($view == 'article')||($view == 'productdetails'))&&($view_article_comments)||
        ($view == 'featured')&&($view_frontpage_comments)||
        ($view == 'category')&&($view_category_comments)) {
      $enable_view_comments = 1;
    }

    $category_tobe_excluded_buttons     = $this->params->get('category_tobe_excluded_buttons', '' );
    $content_tobe_excluded_buttons      = $this->params->get('content_tobe_excluded_buttons', '' );
    $excludedContentList_buttons        = @explode ( ",", $content_tobe_excluded_buttons );
    if ($article->id!=null) {
      if ( in_array ( $article->id, $excludedContentList_buttons )) $enable_view_buttons = 0;
      if (is_array($category_tobe_excluded_buttons ) && in_array ( $article->catid, $category_tobe_excluded_buttons )) $enable_view_buttons = 0;
    } else {
      if (is_array($category_tobe_excluded_buttons ) && in_array ( JRequest::getCmd('id'), $category_tobe_excluded_buttons )) $enable_view_buttons = 0;
    }

    $category_tobe_excluded_comments     = $this->params->get('category_tobe_excluded_comments', '' );
    $content_tobe_excluded_comments      = $this->params->get('content_tobe_excluded_comments', '' );
    $excludedContentList_comments        = @explode ( ",", $content_tobe_excluded_comments );
    if ($article->id!=null) {
      if ( in_array ( $article->id, $excludedContentList_comments )) $enable_view_comments = 0;
      if (is_array($category_tobe_excluded_comments ) && in_array ( $article->catid, $category_tobe_excluded_comments )) $enable_view_comments = 0;
    } else {
      if (is_array($category_tobe_excluded_comments ) && in_array ( JRequest::getCmd('id'), $category_tobe_excluded_comments )) $enable_view_comments = 0;
    }

    if (JRequest::getCMD('print')==1) {
      $enable_view_buttons = 0;
      if ($this->params->get('enable_comments_print','0')==0) $enable_view_comments = 0;
    }
    
    if (($enable_view_buttons != 1)&&($enable_view_comments != 1)) return;

    
    if ((($enable_like==1)||($enable_share==1)||($enable_comments==1))&&(($enable_view_buttons == 1)||($enable_view_comments == 1))) {
      if ($this->_fb==1) {
        if ($this->params->get('auto_language')) {
          $language_fb = str_replace('-', '_', JFactory::getLanguage()->getTag());
        } else {
          $language_fb = $this->params->get('language_fb');
        }
        if ($asynchronous_fb) {
          $FbCode = "
            function AddFbScript(){
              var js,fjs=document.getElementsByTagName('script')[0];
              if (!document.getElementById('facebook-jssdk')) {
                js = document.createElement('script');
                js.id = 'facebook-jssdk';
                js.setAttribute('async', 'true');
                js.src = '//connect.facebook.net/".$language_fb."/all.js#xfbml=1';
                fjs.parentNode.insertBefore(js, fjs);
              }
            }
            window.addEvent('load', function() { AddFbScript() });
          ";
          $document->addScriptDeclaration($FbCode);
        } else {
          $document->addScript("//connect.facebook.net/".$language_fb."/all.js#xfbml=1");
        }
      }
    }
    if (($enable_twitter==1)&&($enable_view_buttons == 1)) {
      if ($this->_tw==1){
        if ($asynchronous_twitter) {
          $TwCode = "
            function AddTwitterScript(){
              var js,fjs=document.getElementsByTagName('script')[0];
              if(!document.getElementById('twitter-wjs')){
                js=document.createElement('script');
                js.id='twitter-wjs';
                js.setAttribute('async', 'true');
                js.src=\"//platform.twitter.com/widgets.js\";
                fjs.parentNode.insertBefore(js,fjs);
              }
            }
            window.addEvent('load', function() { AddTwitterScript() });
          ";
          $document->addScriptDeclaration($TwCode);
        } else {
          $document->addScript("//platform.twitter.com/widgets.js");
        }
      }
    }
    if (($enable_google==1)&&($enable_view_buttons == 1)) {
      if ($this->_google==1) {
        if ($this->params->get('auto_language')) {
          $language_google    = JFactory::getLanguage()->getTag();
        } else {
          $language_google  = $this->params->get('language_google','en-US');
        }
        $GoogleCode = "
          function AddGoogleScript(){
            var js,fjs=document.getElementsByTagName('script')[0];
            if(!document.getElementById('google-wjs')){
              js=document.createElement('script');
              js.id='google-wjs';
              js.setAttribute('async', 'true');
              js.src=\"//apis.google.com/js/plusone.js\";
              js.text=\"{lang: '".$language_google."'}\";
              fjs.parentNode.insertBefore(js,fjs);
            }
          }
          window.addEvent('load', function() { AddGoogleScript() });
        ";
        $document->addScriptDeclaration($GoogleCode);
      } 
    }
    if (($enable_in==1)&&($enable_view_buttons == 1)) {
      if ($this->_in==1) {
        $InCode = "
          function AddInScript(){
            var js,fjs=document.getElementsByTagName('script')[0];
            if(!document.getElementById('linkedin-js')){
              js=document.createElement('script');
              js.id='linkedin-js';
              js.setAttribute('async', 'true');
              js.src=\"//platform.linkedin.com/in.js\";
              fjs.parentNode.insertBefore(js,fjs);
            }
          }
          window.addEvent('load', function() { AddInScript() });
        ";
        $document->addScriptDeclaration($InCode);
      } 
    }
    if (($enable_pint==1)&&($enable_view_buttons == 1)) {
      $selection_pint = $this->params->get( 'selection_pint','0');
      if (($this->_pint==1)&&($selection_pint=='1')) { //not user select
        $PintCode = "
          function AddPintScript(){
            var js,fjs=document.getElementsByTagName('script')[0];
            if(!document.getElementById('pinterest-js')){
              js=document.createElement('script');
              js.id='pinterest-js';
              js.setAttribute('async', 'true');
              js.src=\"//assets.pinterest.com/js/pinit.js\";
              fjs.parentNode.insertBefore(js,fjs);
            }
          }
          window.addEvent('load', function() { AddPintScript() });
        ";
        $document->addScriptDeclaration($PintCode);
        $PintCss = "
          .cmp_pint_button {
            display: inline;
            position: absolute;
            -moz-opacity:.50; 
            filter:alpha(opacity=50); 
            opacity:.50; 
            z-index: 20;
          } 
          .cmp_pint_button:hover { 
            -moz-opacity:1; 
            filter:alpha(opacity=100); 
            opacity:1;
          } 
        "; 
        $document->addStyleDeclaration($PintCss);
      } 
    }
    if (($view=='article')||($view=='productdetails')){
      $tmp=$article->text;     
    } else {
      $tmp=$article->introtext;
    }

    if ((($enable_like==1)||($enable_share==1)||($enable_twitter==1)||($enable_google==1)||($enable_in==1)||($enable_pint==1))&&($enable_view_buttons==1)) {
      $htmlcode=$this->getPlugInButtonsHTML($params, $article, $url, $title, $view, $tmp);
      if ($position == '1'){
        $tmp = $htmlcode . $tmp;
      }
      if ($position == '2'){
        $tmp = $tmp . $htmlcode;
      }
      if ($position == '3'){
        $tmp = $htmlcode . $tmp . $htmlcode;
      }
    }

    if (($enable_comments==1)&&($enable_view_comments==1)) {
      $tmp = $tmp . $this->getPlugInCommentsHTML($params, $article, $url, $title);
    }

    if (($view=='article')||($view=='productdetails')){
      $article->text=$tmp;     
    } else {
      $article->introtext=$tmp;
    }
  }

  private function getPlugInCommentsHTML($params, $article, $url, $title) {
    $idrnd                       = 'fbcom'.rand();
    $document                    = & JFactory::getDocument();
    $category_tobe_excluded      = $this->params->get('category_tobe_excluded_comments');
    $content_tobe_excluded       = $this->params->get('content_tobe_excluded_comments', '' );
    $excludedContentList         = @explode ( ",", $content_tobe_excluded );
    if ($article->id!=null) {
      if ( in_array ( $article->id, $excludedContentList )) {
        return;
      }
      if (is_array($category_tobe_excluded ) && in_array ( $article->catid, $category_tobe_excluded )) {
        return;
      }
    } else {
      if (is_array($category_tobe_excluded ) && in_array ( JRequest::getCmd('id'), $category_tobe_excluded )) return;
    }
    $htmlCode                    = "";
    $number_comments             = $this->params->get('number_comments');
    $width                       = $this->params->get('width_comments');
    $box_color                   = $this->params->get('box_color');
    $container_comments          = $this->params->get('container_comments','1');
    $css_comments                = $this->params->get('css_comments','border-top-style:solid;border-top-width:1px;padding:10px;text-align:center;');
    if ($css_comments!="") { $css_comments="style=\"$css_comments\""; }
    $enable_comments_count       = $this->params->get('enable_comments_count');
    $container_comments_count    = $this->params->get('container_comments_count','1');
    $css_comments_count          = $this->params->get('css_comments_count');
    $asynchronous_fb             = $this->params->get('asynchronous_fb',0);
    $autofit                     = $this->params->get('autofit_comments',0);
    $htmlCode                    = "";

    if ($css_comments_count!="") { $css_comments_count="style=\"$css_comments_count\""; }
    if ($container_comments==1){
      $htmlCode .="<div id=\"".$idrnd."\" class=\"cmp_comments_container\" $css_comments>";
    } elseif ($container_comments==2) {
      $htmlCode .="<p id=\"".$idrnd."\" class=\"cmp_comments_container\" $css_comments>";
    }
    if ($enable_comments_count==1){
      if ($container_comments_count==1){
        $htmlCode .="<div $css_comments_count>";
      } elseif ($container_comments_count==2) {
        $htmlCode .="<p $css_comments_count>";
      }
      $htmlCode .= "<fb:comments-count href=\"$url\"></fb:comments-count> comments";
      if ($container_comments==1){
        $htmlCode .="</div>";
      } elseif ($container_comments==2) {
        $htmlCode .="</p>";
      }
    }
    if ($asynchronous_fb) {
      $tmp = "<script type=\"text/javascript\">".PHP_EOL."//<![CDATA[".PHP_EOL;
      if ($autofit){
        $tmp.= "function getwfbcom() {".PHP_EOL;
        $tmp.= "var efbcom = document.getElementById('".$idrnd."');".PHP_EOL;
        $tmp.= "if (efbcom.currentStyle){".PHP_EOL;
        $tmp.= " var pl=efbcom.currentStyle['paddingLeft'].replace(/px/,'');".PHP_EOL;
        $tmp.= " var pr=efbcom.currentStyle['paddingRight'].replace(/px/,'');".PHP_EOL;
        $tmp.= " return efbcom.offsetWidth-pl-pr;".PHP_EOL;
        $tmp.= "} else {".PHP_EOL;
        $tmp.= " var pl=window.getComputedStyle(efbcom,null).getPropertyValue('padding-left' ).replace(/px/,'');".PHP_EOL;
        $tmp.= " var pr=window.getComputedStyle(efbcom,null).getPropertyValue('padding-right').replace(/px/,'');".PHP_EOL;
        $tmp.= " return efbcom.offsetWidth-pl-pr;";
        $tmp.= "}}".PHP_EOL;
        $tmp.= "var tagfbcom = '<fb:comments href=\"$url\" num_posts=\"$number_comments\" width=\"'+getwfbcom()+'\" colorscheme=\"$box_color\"></fb:comments>';";
      } else {
        $tmp.= "var tagfbcom = '<fb:comments href=\"$url\" num_posts=\"$number_comments\" width=\"$width\" colorscheme=\"$box_color\"></fb:comments>';";
      }
      $tmp.= "document.write(tagfbcom); ".PHP_EOL."//]]> ".PHP_EOL."</script>";
    } else {
      $tmp = "<fb:comments href=\"$url\" num_posts=\"$number_comments\" width=\"$width\" colorscheme=\"$box_color\"></fb:comments>";
      if ($autofit){
        $tmps= "function autofitfbcom() {";
        $tmps.= "var efbcom = document.getElementById('".$idrnd."');";
        $tmps.= "if (efbcom.currentStyle){";
        $tmps.= "var pl=efbcom.currentStyle['paddingLeft'].replace(/px/,'');";
        $tmps.= "var pr=efbcom.currentStyle['paddingRight'].replace(/px/,'');";
        $tmps.= "var wfbcom=efbcom.offsetWidth-pl-pr;";
        $tmps.= "try {efbcom.firstChild.setAttribute('width',wfbcom);}";
        $tmps.= "catch(e) {efbcom.firstChild.width=wfbcom+'px';}";
        $tmps.= "} else {";
        $tmps.= "var pl=window.getComputedStyle(efbcom,null).getPropertyValue('padding-left' ).replace(/px/,'');";
        $tmps.= "var pr=window.getComputedStyle(efbcom,null).getPropertyValue('padding-right').replace(/px/,'');";
        $tmps.= "efbcom.childNodes[0].setAttribute('width',efbcom.offsetWidth-pl-pr);".PHP_EOL;
        $tmps.= "}}";
        $tmps.= "autofitfbcom();";
        $tmp .= "<script type=\"text/javascript\">".PHP_EOL."//<![CDATA[".PHP_EOL.$tmps.PHP_EOL."//]]> ".PHP_EOL."</script>".PHP_EOL;
      }
    }
    $htmlCode .= $tmp;
    if ($container_comments==1){
      $htmlCode .="</div>";
    } elseif ($container_comments==2) {
      $htmlCode .="</p>";
    }
    return $htmlCode;
  }

  private function getPlugInButtonsHTML($params, $article, $url, $title, $view, &$text) {
    $idrnd                       = rand();
    $document                    = & JFactory::getDocument();
    $category_tobe_excluded      = $this->params->get('category_tobe_excluded_buttons', '' );
    $content_tobe_excluded       = $this->params->get('content_tobe_excluded_buttons', '' );
    $excludedContentList         = @explode ( ",", $content_tobe_excluded );
    if ($article->id!=null) {
      if ( in_array ( $article->id, $excludedContentList )) {
        return;
      }
      if (is_array($category_tobe_excluded ) && in_array ( $article->catid, $category_tobe_excluded )) {
        return;
      }
    } else {
      if (is_array($category_tobe_excluded ) && in_array ( JRequest::getCmd('id'), $category_tobe_excluded )) return;
    }
    $enable_like                 = $this->params->get( 'enable_like');
    $enable_share                = $this->params->get( 'enable_share');
    $enable_twitter              = $this->params->get( 'enable_twitter');
    $enable_google               = $this->params->get( 'enable_google');
    $enable_in                   = $this->params->get( 'enable_in');
    $enable_pint                 = $this->params->get( 'enable_pint');
    $asynchronous_fb             = $this->params->get( 'asynchronous_fb',0);

    $weight = array(
      'like'    => $this->params->get( 'weight_like'),
      'share'   => $this->params->get( 'weight_share'),
      'twitter' => $this->params->get( 'weight_twitter'),
      'google'  => $this->params->get( 'weight_google'),
      'in'      => $this->params->get( 'weight_in'),
      'pint'    => $this->params->get( 'weight_pint')
    );
    asort($weight);
    $container_buttons           = $this->params->get( 'container_buttons','1');
    $css_buttons                 = $this->params->get( 'css_buttons','height:40px;');
    if ($css_buttons!="") { $css_buttons="style=\"$css_buttons\""; }
    $htmlCode     = '';
    $code_like    = '';
    $code_share   = '';
    $code_twitter = '';
    $code_google  = '';
    $code_in      = '';
    $code_pint    = '';
    if ($container_buttons==1){
      $htmlCode ="<div class=\"cmp_buttons_container\" $css_buttons>";
    } elseif ($container_buttons==2) {
      $htmlCode ="<p class=\"cmp_buttons_container\" $css_buttons>";
    }
    //FB like button
    if ($enable_like == 1) {
      $layout_style                = $this->params->get( 'layout_style','button_count');
      $show_faces                  = $this->params->get('show_faces');
      if ($show_faces == 1) {
        $show_faces = "true";
      } else {
        $show_faces = "false";
      }
      $width_like                  = $this->params->get( 'width_like');
      $css_like                    = $this->params->get( 'css_like','float:left;margin:10px;');
      if ($css_like!="") { $css_like="style=\"$css_like\""; }
      $container_like              = $this->params->get( 'container_like','1');
      $send                        = $this->params->get( 'send','1');
      if ($send == 2) {
        $standalone=1;
      } else {
        $standalone=0;
        if ($send == 1) {
          $send  = "true";
        } else {
          $send = "false";
        }
      }
      $verb_to_display             = $this->params->get( 'verb_to_display','1');
      if ($verb_to_display == 1) {
        $verb_to_display  = "like";
      } else {
        $verb_to_display = "recommend";
      }
      $font                        = $this->params->get( 'font');
      $color_scheme                = $this->params->get( 'color_scheme','light');
      if ($this->_fb == 1) {
        $code_like .= "<div id=\"fb-root\"></div>";
      }
      if ($standalone==1){
        $tmp = "<fb:send href=\"$url\" font=\"$font\" colorscheme=\"$color_scheme\"></fb:send>";
        if ($container_like==1){
          $code_like .="<div class=\"cmp_send_container\" $css_like>$tmp</div>";
        } elseif ($container_like==2) {
          $code_like .="<p class=\"cmp_send_container\" $css_like>$tmp</p>";
        } else {
          $code_like .=$tmp;
        }
      }
      $tmp = "<fb:like href=\"$url\" layout=\"$layout_style\" show_faces=\"$show_faces\" send=\"$send\" width=\"$width_like\" action=\"$verb_to_display\" font=\"$font\" colorscheme=\"$color_scheme\"></fb:like>";
      if ($asynchronous_fb) {
        $tmp = "<script type=\"text/javascript\">".PHP_EOL."//<![CDATA[".PHP_EOL."document.write('".$tmp."'); ".PHP_EOL."//]]> ".PHP_EOL."</script>";
      } else {
        $tmp = $tmp.PHP_EOL;
      }
      if ($container_like==1){
        $code_like .="<div class=\"cmp_like_container\" $css_like>$tmp</div>";
      } elseif ($container_like==2) {
        $code_like .="<p class=\"cmp_like_container\" $css_like>$tmp</p>";
      } else {
        $code_like .=$tmp;
      }
    }
    //Twitter button
    if ($enable_twitter == 1) {
      if ($this->params->get('auto_language')) {
        $language_twitter  = substr(JFactory::getLanguage()->getTag(), 0, 2);
      } else {
        $language_twitter  = $this->params->get('language_twitter','en');
      }
      $data_via_twitter    = $this->params->get( 'data_via_twitter');
      $data_related_twitter= $this->params->get( 'data_related_twitter');
      $show_count_twitter  = $this->params->get( 'show_count_twitter','horizontal');
      $hashtags_twitter    = $this->params->get( 'hashtags_twitter','');
      $asynchronous_twitter= $this->params->get( 'asynchronous_twitter','0');
      $datasize_twitter    = $this->params->get( 'datasize_twitter','medium');
      $container_twitter   = $this->params->get( 'container_twitter','1');
      $css_twitter         = $this->params->get( 'css_twitter','float:right;margin:10px;');
      $asynchronous_twitter= $this->params->get( 'asynchronous_twitter',0);
      if ($language_twitter!="en"){$language_twitter="data-lang=\"$language_twitter\"";} else {$language_twitter='';}
      if ($data_via_twitter!=""){$data_via_twitter="data-via=\"$data_via_twitter\"";} else {$data_via_twitter='';}
      if ($data_related_twitter!=""){$data_related_twitter="data-related=\"$data_related_twitter\"";} else {$data_related_twitter='';}
      if ($hashtags_twitter!="") { $hashtags_twitter="data-hashtags=\"$hashtags_twitter\""; }
      if ($datasize_twitter!="") { $datasize_twitter="data-size=\"$datasize_twitter\""; }
      if ($css_twitter!="") { $css_twitter="style=\"$css_twitter\""; }
      $tmp = "<a href=\"//twitter.com/share\" class=\"twitter-share-button\" ";
      $tmp.= "$language_twitter $data_via_twitter $hashtags_twitter $data_related_twitter ";
      $tmp.= "data-url=\"$url\" ";
      $tmp.= "data-text=\"$title\" ";
      $tmp.= "data-count=\"$show_count_twitter\">Tweet</a>";
      if ($asynchronous_twitter) {
        $tmp = "<script type=\"text/javascript\">".PHP_EOL."//<![CDATA[".PHP_EOL."document.write('".$tmp."'); ".PHP_EOL."//]]> ".PHP_EOL."</script>";
      } else {
        $tmp = $tmp.PHP_EOL;
      }
      if ($container_twitter==1){
        $code_twitter .="<div class=\"cmp_twitter_container\" $css_twitter>$tmp</div>";
      } elseif ($container_twitter==2) {
        $code_twitter .="<p class=\"cmp_twitter_container\" $css_twitter>$tmp</p>";
      } else {
        $code_twitter .=$tmp;
      };
    }
    //Google +1 button
    if ($enable_google == 1) {
      $html5_google       = $this->params->get( 'html5_google','0');
      $size_google        = $this->params->get( 'size_google','standard');
      $annotation_google  = $this->params->get( 'annotation_google','bubble');
      $asynchronous_google= $this->params->get( 'asynchronous_google','0');
      if ($this->params->get('auto_language')) {
        $language_google    = JFactory::getLanguage()->getTag();
      } else {
        $language_google  = $this->params->get('language_google','en-US');
      }
      $container_google   = $this->params->get( 'container_google','1');
      $css_google         = $this->params->get( 'css_google','float:right;margin:10px;');
      if ($css_google!="") { $css_google="style=\"$css_google\""; }
      if ($annotation_google!="bubble") {
        if ($html5_google) {
          $annotation_google="data-annotation=\"$annotation_google\"";
        } else {
          $annotation_google="annotation=\"$annotation_google\"";
        }
      } else {
        $annotation_google="";
      }
      $tmp="";
      if ($html5_google) {
        $tmp .= "<div class=\"g-plusone\" data-size=\"$size_google\" data-href=\"$url\" $annotation_google></div>";
      } else {
        $tmp .= "<g:plusone size=\"$size_google\" href=\"$url\" $annotation_google></g:plusone>";
      }
      $browser = &JBrowser::getInstance();
      $browserType = $browser->getBrowser();
      if ($asynchronous_google && ($browserType != 'msie')) {
        $tmp = "<script type=\"text/javascript\">".PHP_EOL."//<![CDATA[".PHP_EOL."document.write('".$tmp."'); ".PHP_EOL."//]]> ".PHP_EOL."</script>";
      } else {
        $tmp = $tmp.PHP_EOL;
      }
      if ($container_google==1){
        $code_google .="<div class=\"cmp_google_container\" $css_google>$tmp</div>";
      } elseif ($container_google==2) {
        $code_google .="<p class=\"cmp_google_container\" $css_google>$tmp</p>";
      } else {
        $code_google .=$tmp;
      };
    }
    //FB share button
    if ($enable_share == 1) {
      $share_button_text           = $this->params->get( 'text_share_button','Share');
      $share_button_style          = $this->params->get( 'share_button_style','icontext');
      $container_share             = $this->params->get( 'container_share','1');
      $css_share                   = $this->params->get( 'css_share','float:right;margin:10px;');
      if ($css_share!="") { $css_share="style=\"$css_share\""; }
      $script  = "<script>"; 
      $script .= "function fbs_click$idrnd() {";
      $script .= "FB.ui({";
      $script .= "    method: \"stream.share\",";
      $script .= "    u: \"".$url."\"";
      $script .= "  } ";
      $script .= "); return false; };";
      $script .= "</script>";
      $tmp  = $script;  
      switch ($share_button_style) {
        case "icontext":
          $tmp .= "<style>a.cmp_shareicontextlink { text-decoration: none; line-height: 20px;height: 20px; color: #3B5998; font-size: 11px; font-family: arial, sans-serif;  padding:2px 4px 2px 20px; border:1px solid #CAD4E7; cursor: pointer;  background:url(//static.ak.facebook.com/images/share/facebook_share_icon.gif?6:26981) no-repeat 1px 1px #ECEEF5; -webkit-border-radius: 3px; -moz-border-radius: 3px;} .cmp_shareicontextlink:hover {   background:url(//static.ak.facebook.com/images/share/facebook_share_icon.gif?6:26981) no-repeat 1px 1px #ECEEF5 !important;  border-color:#9dacce !important; color: #3B5998 !important;} </style><a class=\"cmp_shareicontextlink\" href=\"#\" onclick=\"return fbs_click$idrnd()\" target=\"_blank\">".$share_button_text."</a>";
          break;
        case "text":
          $tmp .= "<style>a.cmp_sharetextlink { text-decoration: none; line-height: 20px;height: 20px; color: #3B5998; font-size: 11px; font-family: arial, sans-serif;  padding:2px 4px 2px 4px; border:1px solid #CAD4E7; cursor: pointer;  background-color: #ECEEF5; -webkit-border-radius: 3px; -moz-border-radius: 3px;} .cmp_sharetextlink:hover {   background-color: #ECEEF5 !important;  border-color:#9dacce !important; color: #3B5998 !important;} </style><a class=\"cmp_sharetextlink\" rel=\"nofollow\" href=\"#\" onclick=\"return fbs_click$idrnd()\" target=\"_blank\">".$share_button_text."</a>";
          break;
        case "icon":
          $tmp  .= "<style>.cmp_shareiconlink { text-decoration: none; line-height: 20px;height: 20px; color: #3B5998; font-size: 11px; font-family: arial, sans-serif;  padding:2px 4px 2px 14px; border:1px solid #CAD4E7; cursor: pointer;width: 20px;  background:url(//static.ak.facebook.com/images/share/facebook_share_icon.gif?6:26981) no-repeat 1px 1px #ECEEF5; -webkit-border-radius: 3px; -moz-border-radius: 3px;} .cmp_shareiconlink:hover {   background:url(//static.ak.facebook.com/images/share/facebook_share_icon.gif?6:26981) no-repeat 1px 1px #ECEEF5 !important;  border-color:#9dacce !important; color: #3B5998 !important;} </style><a class=\"cmp_shareiconlink\" href=\"#\" onclick=\"return fbs_click$idrnd()\" target=\"_blank\"></a>";
          break;
      }
      if ($asynchronous_fb) {
        $tmp = "<script type=\"text/javascript\">".PHP_EOL."//<![CDATA[".PHP_EOL."document.write('".preg_replace('/<\/script>/i','<\/script>',$tmp)."'); ".PHP_EOL."//]]> ".PHP_EOL."</script>";
      } else {
        $tmp = $tmp.PHP_EOL;
      }
      if ($container_share==1){
        $code_share .="<div class=\"cmp_share_container\" $css_share>$tmp</div>";
      } elseif ($container_share==2) {
        $code_share .="<p class=\"cmp_share_container\" $css_share>$tmp</p>";
      } else {
        $code_share .=$tmp;
      };  
    }
    //LinkedIn button
    if ($enable_in == 1) {
      $data_counter_in    = $this->params->get( 'data-counter_in','none');
      $data_showzero_in   = $this->params->get( 'data-showzero_in','0');
      $asynchronous_in    = $this->params->get( 'asynchronous_in','0');
      $container_in       = $this->params->get( 'container_in','1');
      $css_in             = $this->params->get( 'css_in','float:right;margin:10px;');
      if ($css_in!="") { $css_in="style=\"$css_in\""; }
      if ($data_counter_in=="none") {
        $data_counter_in="";
        $data_showzero_in="";
      } else {
        $data_counter_in="data-counter=\"$data_counter_in\"";
        if ($data_showzero_in=="0") {
          $data_showzero_in="";
        } else {
          $data_showzero_in="data-showzero=\"true\"";
        }
      }

      $tmp  ="";
      $tmp .="<script type=\"IN/Share\" data-url=\"$url\" $data_counter_in $data_showzero_in></script>";
      if ($asynchronous_in) {
        $tmp = "<script type=\"text/javascript\">".PHP_EOL."//<![CDATA[".PHP_EOL."document.write('".preg_replace('/<\/script>/i','<\/script>',$tmp)."'); ".PHP_EOL."//]]> ".PHP_EOL."</script>";
      } else {
        $tmp = $tmp.PHP_EOL;
      }
      if ($container_in==1){
        $code_in .="<div class=\"cmp_in_container\" $css_in>$tmp</div>";
      } elseif ($container_in==2) {
        $code_in .="<p class=\"cmp_in_container\" $css_in>$tmp</p>";
      } else {
        $code_in .=$tmp;
      };
    }

    //Pinterest button
    if ($enable_pint == 1) {
      $count_layout_pint  = $this->params->get( 'count_layout_pint','none');
      $asynchronous_pint  = $this->params->get( 'asynchronous_pint','0');
      $selection_pint     = $this->params->get( 'selection_pint','0');
      $container_pint     = $this->params->get( 'container_pint','1');
      $css_pint           = $this->params->get( 'css_pint','float:right;margin:10px;');
      $tmp                = '';
      if ($css_pint!="") { $css_pint="style=\"$css_pint\""; }
      if ($selection_pint=='1') {
        preg_match_all('/(<\s*img)(\s+[^>]*?src\s*=\s*["\'])(.*?)(["\'][^>]*?>)/i', $text, $matches) ; 
        $r = array();
        $b = array();
        foreach ( $matches[3] as $key => &$img) {
          $n=mt_rand(1,10000);
          $r[]='pin'.$n;
          $b[]='but'.$n;
          if (!preg_match('%^(?://|http://|https://)%',$img)) {
            $img = JURI::root().preg_replace('#^/#','',$img);      
          }
          $matches[5][$key] = $matches[1][$key].' id="pin'.$n.'" '.$matches[2][$key].$img.$matches[4][$key];  
        }
        $text=str_replace($matches[0], $matches[5] , $text); 
        foreach ( $b as $key => $id) {
          $tmp.="<span id='".$id."' class='cmp_pint_button'><a href=\"http://pinterest.com/pin/create/button/?url=".$url."&media=".$matches[3][$key].
                "&description=".$title."\" class=\"pin-it-button\" count-layout=\"$count_layout_pint\">".
                "<img border=\"0\" src=\"//assets.pinterest.com/images/PinExt.png\" title=\"Pin It\" /></a></span>".PHP_EOL;       
        }
        $text.=$tmp;
        if (count($r)>0) {
          $tmp  = '';
          foreach ( $r as $key => $id) {
            $tmp .= "img = document.getElementById('".$id."');
                  e = document.getElementById('".$b[$key]."');
                  if (img.height > 80 && img.width > 80) {
                    pT = img.offsetTop+4;
                    pL = img.offsetLeft+4;
                    e.style.top=pT+'px';
                    e.style.left=pL+'px';
                  } else {
                    e.parentNode.removeChild(e);
                  }
                 ";
          }
          $n=mt_rand(1,10000);
          $tmp = "function SetButtons".$n."(){ ".PHP_EOL.$tmp.PHP_EOL." }
                  window.addEvent('load', function() { SetButtons".$n."() });
                 "; 
          $tmp = "<script type=\"text/javascript\">".PHP_EOL."//<![CDATA[".PHP_EOL.$tmp.PHP_EOL."//]]> ".PHP_EOL."</script>";
          $text.=$tmp;
        } 
      } else {
        $images = $this->getPicture($article,$view);
        if (!preg_match('%^(?://|http://|https://)%',$images[0])) {
            $images[0] = JURI::root().preg_replace('#^/#','',$images[0]);
        }
        if (count($images)>1) {
          //no counter for generic pin if there are many images
          $tmp ="<a href=\"\" class=\"pin-it-button2\" count-layout=\"none\" ";
          $tmp.="onclick=\"execPinmarklet();return false;\" >";
          $tmp.="<img border=\"0\" src=\"//assets.pinterest.com/images/PinExt.png\" title=\"Pin It 2\" /></a>";
          if ($asynchronous_pint) {
            $tmp = "<script type=\"text/javascript\">".PHP_EOL."//<![CDATA[".PHP_EOL."document.write('".$tmp."'); ".PHP_EOL."//]]> ".PHP_EOL."</script>";
          } else {
            $tmp = $tmp.PHP_EOL;
          }
          $tmp .="<script type='text/javascript'>".PHP_EOL."function execPinmarklet() {var e=document.createElement('script');	e.setAttribute('type','text/javascript');	e.setAttribute('charset','UTF-8');	e.setAttribute('src','//assets.pinterest.com/js/pinmarklet.js?r=' + Math.random()*99999999); document.body.appendChild(e);}".PHP_EOL."</script>";
        } else {
          $tmp = "<a href=\"http://pinterest.com/pin/create/button/?url=".$url."&media=".$images[0]."&description=".$title."\" class=\"pin-it-button\" count-layout=\"$count_layout_pint\">";
          $tmp.= "<img border=\"0\" src=\"//assets.pinterest.com/images/PinExt.png\" title=\"Pin It\" /></a>";
          $tmp .="<script type='text/javascript' src='//assets.pinterest.com/js/pinit.js'></script>";
        }
      }
      
      if ($selection_pint!='1') {
        if ($container_pint==1){
          $code_pint .="<div class=\"cmp_pint_container\" $css_pint>$tmp</div>";
        } elseif ($container_in==2) {
          $code_pint .="<p class=\"cmp_pint_container\" $css_pint>$tmp</p>";
        } else {
          $code_pint .=$tmp;
        };
      }
    }
    
    foreach ($weight as $key => $val) {
      switch ($key) {
        case "like":
          $htmlCode .= $code_like;
          break;
        case "share":
          $htmlCode .= $code_share;
          break;
        case "twitter":
          $htmlCode .= $code_twitter;
          break;
        case "google":
          $htmlCode .= $code_google;
          break;
        case "in":
          $htmlCode .= $code_in;
          break;
        case "pint":
          $htmlCode .= $code_pint;
          break;
      }
    }

    if ($container_buttons==1){
      $htmlCode .="</div>";
    } elseif ($container_buttons==2) {
      $htmlCode .="</p>";
    }

    return $htmlCode;
  }

  private function getTitle($obj){
    if (JRequest::getCmd('view') == 'productdetails'){
      return htmlentities( $obj->product_name, ENT_QUOTES, "UTF-8");
    } else {
      return htmlentities( $obj->title, ENT_QUOTES, "UTF-8");
    }
  }

  //get meta from editor form
  private function getDescription($obj,$view){
    if ($view == 'productdetails'){
      if (isset($obj->product_s_desc)){
        $description = $obj->product_s_desc;
      } else {
        $description = $obj->product_desc;
      }
      return htmlentities( $description, ENT_QUOTES, "UTF-8");
    }
    $description  = $this->params->get('description');

    if (($description=='1')||($description=='2')||($description=='3')) { //first paragraph or first 255 chars
      if ($view == 'article') {
        if ($description=='2') { //first 255 chars
          $description = htmlentities(mb_substr(strip_tags($obj->introtext.$obj->fulltext), 0, 251)."... ",ENT_QUOTES, "UTF-8");
        } elseif ($description=='3') { //only intro
          $description = htmlentities(strip_tags($obj->introtext),ENT_QUOTES, "UTF-8");
        } else { //first paragraph
          $content = htmlentities(strip_tags($obj->introtext.$obj->fulltext),ENT_QUOTES, "UTF-8");
          $pos = strpos($content, '.');
          if($pos === false) {
            $description = $content;
          } else {
            $description = substr($content, 0, $pos+1);
          }
        }
      } else {
        $description = stripslashes($_REQUEST['jform']['metadesc']);
      }
    } else {
      $description = stripslashes($_REQUEST['jform']['metadesc']);
    }
    return $description;
  }

  private function getPicture($obj,$view){
    $images = array();
    if (($view == 'productdetails')||($_REQUEST['option']=='com_virtuemart')){
      return $images;
    }
    $defaultimage = $this->params->get('defaultimage');
    $onlydefaultimage = $this->params->get('onlydefaultimage');
    if ($onlydefaultimage==1){
      if ($defaultimage=="") {
        $images[] = JURI::root().'plugins'.DS.'content'.DS.'linkcmp.png';
      } else {
        if (preg_match('/^http/i',$defaultimage)) {
          $images[] = $defaultimage;
        } else {
          $images[] = JURI::root().preg_replace('#^/#','',$defaultimage);
        }
      }
    } else {
      //joomla 2.5+ content images
      if(property_exists($obj,'images')){
        if ($img=json_decode($obj->images)){
          if ($img->{'image_intro'}!=null) {
            $images[] = JURI::root().$img->{'image_intro'};
          } elseif ($img->{'image_fulltext'}!=null) {
            $images[] = JURI::root().$img->{'image_fulltext'};
          }
        }
      }
      $defaultimage = $this->params->get('defaultimage');
      if (isset($obj->text)) {
        $text=$obj->text;
      } else {
        $text=$obj->introtext;
      }
      if ($view == 'article') {
        $this->find_youtube_images($text,$images);
        $this->find_images($text,$images);
      }
      if (($view != 'article')||(count($images)==0)) {
        if ($defaultimage=="") {
          $images[] = JURI::root().'plugins'.DS.'content'.DS.'fb_tw_plus1'.DS.'linkcmp.png';
        } else {
          if (preg_match('/^http/i',$defaultimage)) {
            $images[] = $defaultimage;
          } else {
            $images[] = JURI::root().preg_replace('#^/#','',$defaultimage);
          }
        }
      }
    }
    return $images;
  }

  private function extract_images($obj) {
    $images = array();
    $id = $obj->id;
    $db =& JFactory::getDBO();
    $sql = "SELECT `fulltext`,`introtext`,`images` FROM `#__content` WHERE `id` = ".intval($id);
    $db->setQuery($sql);
    $result=$db->loadObject();
 
    if ($imgs = json_decode($result->images)){
      $image_intro    =JURI::base().trim($imgs->image_intro);
      $image_fulltext =JURI::base().trim($imgs->image_fulltext);
      if (($image_intro)&&(!in_array($image_intro, $images))) { $images[] = $image_intro; };
      if (($image_fulltext)&&(!in_array($image_fulltext, $images))) { $images[] = $image_fulltext; };
    }

    $fulltext = trim($result->fulltext);
    $this->find_youtube_images($fulltext,$images);
    $this->find_images($fulltext,$images);
    $introtext = trim($result->introtext);
    $this->find_youtube_images($introtext,$images);
    $this->find_images($introtext,$images);

    $db->freeResult($result);
    return $images;
  }

  private function find_youtube_images($text,&$images) {
    if (preg_match_all('%(?:http|https)://www\.(?:youtube|youtube-nocookie)\.com/(?:v|embed)/(?!videoseries)(.*?)(?:\?|"|\')%i', $text, $regs)) {
      foreach ($regs[1] as $value) {
        $img = "http://img.youtube.com/vi/$value/0.jpg";
        if(!in_array($img, $images)) { $images[] = $img; };
      }
    }
  }
  private function find_images($text,&$images) {
    if (preg_match_all('/<img.*?src=["\'](.*?)["\'].*?>/i', $text, $regs_i)) {
      foreach ($regs_i[1] as $value) {
        if (preg_match('/^http/i',$value)) {
          $img = $value;
        } else {
          $img = JURI::root().preg_replace('#^/#','',$value);
        }
        if(!in_array($img, $images)) { $images[] = $img; };
      }
    }
  }

  private function getCatUrl($obj){
    if (!is_null($obj)&&(!empty($obj->catid))) {
      $url = JRoute::_(ContentHelperRoute::getCategoryRoute($obj->catid));
      $uri = JURI::getInstance();
      $base  = $uri->toString( array('scheme', 'host', 'port'));
      $url = $base . $url;
      $url = JRoute::_($url, true, 0);
      return $url;
    }
  }

  private function getPageUrl($obj){
    if ((!is_null($obj))&&(JRequest::getCmd('view') == 'productdetails')){
      $uri = JURI::getInstance();
      $base  = $uri->toString( array('scheme', 'host', 'port'));
      $url = $base . $obj->link;
      return $url;
    }
    if (!is_null($obj)&&(!empty($obj->catid))) {
      if (empty($obj->catslug)){
        $url = JRoute::_(ContentHelperRoute::getArticleRoute($obj->slug, $obj->catid));
      } else {
        $url = JRoute::_(ContentHelperRoute::getArticleRoute($obj->slug, $obj->catslug));
      }
      $uri = JURI::getInstance();
      $base  = $uri->toString( array('scheme', 'host', 'port'));
      $url = $base . $url;
      $url = JRoute::_($url, true, 0);
      return $url;
    }
  }
  
  private function get_url_contents($url){
    $ch = curl_init();
    $timeout = 5;
    curl_setopt ($ch, CURLOPT_URL,$url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $ret = curl_exec($ch);
    curl_close($ch);
    return $ret;
  }

}
?>