<?php
/**
 * @package	JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class CommunityDeveloperController extends CommunityBaseController
{
	var $task;


	public function display($cacheable=false, $urlparams=false)
	{
		$view = $this->getView('developer');
		echo $view->get('display');
	}

}
