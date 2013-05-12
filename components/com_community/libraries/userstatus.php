<?php
/**
 * @category	Library
 * @package		JomSocial
 * @subpackage	user
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die('Restricted access');


class CUserStatus {

	private $creators = null;

	public $target = '';

	public function __construct($target='')
	{
		$my = CFactory::getUser();

		$this->target = (empty($target)) ? $my->id : $target;
	}

	public function addCreator($creator)
	{
		$this->creators[] =& $creator;

		return $creator;
	}

	public function render()
	{
		CFactory::load('libraries', 'privacy');

		$my = CFactory::getUser();

		if ($my->id && is_array($this->creators)) {

			$tmpl = new CTemplate();
			$tmpl->set('my', $my);
			$tmpl->set('target', $this->target);
			$tmpl->set('creators', $this->creators);

			$html = $tmpl->fetch('status.form');

			echo $html;
		}
	}
}

class CUserStatusCreator {

	public $type='';
	public $class='';
	public $title='';
	public $html='';

	public function __construct($type=null)
	{
		$this->type = $type;
		$this->class = 'type-' . $type;
	}

}


?>