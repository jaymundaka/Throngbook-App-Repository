<?php
/**
 * @package     cleanlogic
 * @author      Robin Jungermann
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/

// no direct access
defined('_JEXEC') or die;
$lang = JFactory::getLanguage();
$upper_limit = $lang->getUpperLimitSearchWord();
?>

<form id="searchForm" action="<?php echo JRoute::_('index.php?option=com_search');?>" method="post">

	<fieldset class="word">
		

	</fieldset>


	<table class="filterTable">
    
    <tr>
        <td align="right"><label for="search-searchword"><?php echo JText::_('COM_SEARCH_SEARCH_KEYWORD'); ?></label><td>
        <td nowrap="nowrap">
            <span id="search-searchword-wrapper"><input type="text" name="searchword" id="search-searchword" size="30" maxlength="<?php echo $upper_limit; ?>" value="<?php echo $this->escape($this->origkeyword); ?>" class="inputbox" /></span>
            <span id="search-button-wrapper">
            <button name="Search" onclick="this.form.submit()" class="button"><?php echo JText::_('COM_SEARCH_SEARCH');?></button></span>
            <input type="hidden" name="task" value="search" />
        <td>
    </tr>
    
    <tr>
        <td align="right"><legend><?php echo JText::_('COM_SEARCH_FOR');?></legend><td>
        <td>
        	<div class="phrases-box">
				<?php echo $this->lists['searchphrase']; ?>
            </div>
        <td>
    </tr>
    
    <?php if ($this->params->get('search_areas', 1)) : ?>
    <tr>
        <td align="right"><legend><?php echo JText::_('COM_SEARCH_SEARCH_ONLY');?></legend><td>
        <td>
        <div class="only-box">
			<?php foreach ($this->searchareas['search'] as $val => $txt) :
                $checked = is_array($this->searchareas['active']) && in_array($val, $this->searchareas['active']) ? 'checked="checked"' : '';
            ?>
                <input type="checkbox" name="areas[]" value="<?php echo $val;?>" id="area-<?php echo $val;?>" <?php echo $checked;?> />
                <label for="area-<?php echo $val;?>"><?php echo JText::_($txt); ?></label>
            <?php endforeach; ?>
        </div>
        <td>
    </tr>
    <?php endif; ?>
    
    <tr>
        <td align="right"><label for="ordering" class="ordering"><?php echo JText::_('COM_SEARCH_ORDERING');?></label><td>
        <td nowrap="nowrap">
        <div class="ordering-box" style="float: left;">
		<?php echo $this->lists['ordering'];?>
        </div>
        
        <?php if ($this->total > 0) : ?>
		<div class="form-limit" style="float: left;">
		<label for="limit"><?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?></label>
		<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
<?php endif; ?>
        <td>
    </tr>
    </table>



	<div class="searchintro<?php echo $this->params->get('pageclass_sfx'); ?>">
		<?php if (!empty($this->searchword)):?>
		<p><?php echo JText::plural('COM_SEARCH_SEARCH_KEYWORD_N_RESULTS', $this->total);?></p>
		<?php endif;?>
	</div>

	<fieldset class="phrases"></fieldset>

	<?php if ($this->params->get('search_areas', 1)) : ?>
		<fieldset class="only"></fieldset>
	<?php endif; ?>

            


</form>
