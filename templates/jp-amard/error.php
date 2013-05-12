<?php
/**
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
if (!isset($this->error)) {
	$this->error = JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
	$this->debug = false;
}
//get language and direction
$doc = JFactory::getDocument();
$this->language = $doc->language;
$this->direction = $doc->direction;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
	<title><?php echo $this->error->getCode(); ?> - <?php echo $this->title; ?></title>
	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/jp-amard/css/error.css" type="text/css" />
	<?php if($this->direction == 'rtl') : ?>
   	<link rel="stylesheet" href="<?php echo $this->baseurl; ?>/templates/jp-amard/css/error-rtl.css" type="text/css" />
<?php endif; ?>
</head>
<body>
<div class="headerk">
<div class="logok"></div><div class="home"> <a href="<?php echo $this->baseurl; ?>/index.php" title="<?php echo JText::_('ERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>"><?php echo JText::_('ERROR_LAYOUT_HOME_PAGE'); ?> </a> </div>
<div id="errorboxheader-des"><?php echo $this->error->getMessage(); ?></div>
</div>
	<div class="error">
		<div id="outline">
		<div id="errorboxoutline">
			<div id="errorboxheader"><?php echo $this->error->getCode(); ?></div>

			<div class="errormenu"><?php echo JText::_('ERROR_LAYOUT_PLEASE_TRY_ONE_OF_THE_FOLLOWING_PAGES'); ?> <a href="<?php echo $this->baseurl; ?>/index.php" title="<?php echo JText::_('ERROR_LAYOUT_GO_TO_THE_HOME_PAGE'); ?>"><?php echo JText::_('ERROR_LAYOUT_HOME_PAGE'); ?> </a> <?php echo JText::_('ERROR_LAYOUT_PLEASE_CONTACT_THE_SYSTEM_ADMINISTRATOR'); ?>
			</div>
			<div id="techinfo">
			<p>
				<?php if ($this->debug) :
					echo $this->renderBacktrace();
				endif; ?>
			</p>
			</div>
			</div>
		</div>
        </div>
</body>
</html>
