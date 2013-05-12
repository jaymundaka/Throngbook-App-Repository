<?php
/**
 * @package     cleanlogic
 * @author      Robin Jungermann
 * @link        http://www.crosstec.de
 * @license     GNU/GPL
*/
defined('_JEXEC') or die;
JHTML::_('behavior.modal');
JHTML::_('behavior.framework', true);
require_once(JPATH_SITE . DS . 'templates' . DS . $this->template . DS . 'system' . DS . 'recolor.php');
$app = JFactory::getApplication();

$moduleWidthHighlightsRow1 = "ct_moduleWidth_".$this->countModules('highlights_1_1 + highlights_1_2 + highlights_1_3 + highlights_1_4');
$moduleWidthMaincontentRow1 = "ct_moduleWidth_".$this->countModules('maincontent_1_1 + maincontent_1_2 + maincontent_1_3 + maincontent_1_4');
$moduleWidthMaincontentRow2 = "ct_moduleWidth_".$this->countModules('maincontent_2_1 + maincontent_2_2 + maincontent_2_3 + maincontent_2_4');

$contentLeft = 0;
$contentRight = 0;

if($this->countModules('left') > 0) {
	$contentLeft =	1;
}

if($this->countModules('right') > 0) {
	$contentRight =	1;
}
 
$moduleWidthcomponentContent = "ct_componentWidth_".(4 - ($contentLeft + $contentRight));

$moduleWidthFooterRow1 = "ct_moduleWidth_".$this->countModules('footer_1_1 + footer_1_2 + footer_1_3 + footer_1_4');

$templateURL = $this->baseurl."/templates/".$this->template;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" >

<head>
	<jdoc:include type="head" />
	
    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/system/css/system.css" type="text/css" />

    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/print.css" type="text/css" media="Print" />

    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/layout.css.php?sitewidth=<?php echo $this->params->get('sitewidth'); ?>" type="text/css" media="screen, projection" />
    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/colors.css.php?color=<?php echo $this->params->get('color', '2e571a'); ?>&amp;content_color=<?php echo $this->params->get('content_color', 'e6e6e6'); ?>&amp;bg_style=<?php echo $this->params->get('bg_style','04'); ?>&amp;templateurl=<?php echo $templateURL; ?>" type="text/css" media="screen, projection" />
    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/template.css.php?font=<?php echo $this->params->get('font', 'arvo'); ?>" type="text/css" media="screen, projection" />

    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/formelements.css" type="text/css" media="screen, projection" />
    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/contentbuilder_support.css" type="text/css" media="screen, projection" />
    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/content_types.css" type="text/css" media="screen, projection" />
    <link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/cssmenu.css" type="text/css" media="screen, projection" /> 

<!--[if lt IE 9]>
    <style>
    ul.menu {
    	-webkit-border-radius: 0px;
		-moz-border-radius: 0px;
		border-radius: 0px; 
   	}

    ul.menu, ul.menu ul, .moduletable_ct_darkBox, .moduletable_ct_lightBox, ul.pagenav li a,
    input.button, button, #login-form.compact .button, #ct_headerLogin input.button, .tip  {
        behavior:url(<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/pie/PIE.php);
    }
    
    </style>
<![endif]-->

<!--[if lte IE 8]>
    <style>
    ul.menu {
    	-webkit-border-radius: 0px;
		-moz-border-radius: 0px;
		border-radius: 0px; 
   	}

    ul.menu, .moduletable_ct_darkBox, .moduletable_ct_lightBox, ul.pagenav li a,
    input.button, button, #login-form.compact .button, #ct_headerLogin input.button, .tip  {
        behavior:url(<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/pie/PIE.php);
    }
    
    </style>
<![endif]-->
   
</head>

<body>

<div id="ct_mainWrapper">
		
		<?php if($this->params->get('mainnav_position') == "left") { echo('<div id="ct_leftWrapper">');}; ?>
		<div id="ct_headerWrapper_<?php echo $this->params->get('mainnav_position','left'); ?>">
			<div id="ct_headerContent"> 
				
				<div id="ct_headerMain_<?php echo $this->params->get('mainnav_position','left'); ?>">
					
				   <?php 
				   if($this->params->get('mainnav_position') == "top") {
					   echo('
				   <div id="ct_headerTools">
						<div id="ct_headerSearch">
							<jdoc:include type="modules" name="searchHeader" style="xhtml" />
						</div>
						<div id="ct_headerLogin">
							<jdoc:include type="modules" name="loginHeader" style="xhtml" />
						</div>
				   </div>
				   ');}
				   ?>
					
					<?php if ($this->params->get('logo')) : ?>
						<div id="ct_logo">
							<a href="<?php echo $this->baseurl ?>">
								<img src="<?php echo $this->baseurl.'/'.$this->params->get('logo'); ?>" />
							</a> 
						</div>
					 <?php endif; ?>   
					 
				   <?php if($this->params->get('mainnav_position') == "top") {
						echo('
							<div class="ct_clearFloat"></div>
						');}
				   ?>
								  
				   <div id="ct_mainNavWrapper">
						<jdoc:include type="modules" name="mainNav" style="xhtml" />
				   </div>
				   
			   </div>
			   
			   <?php 
			   if($this->params->get('mainnav_position') == "left" OR $this->params->get('mainnav_position') == "right") {
				   echo('
			   <div id="ct_headerTools">
					<div id="ct_headerSearch">
						<jdoc:include type="modules" name="searchHeader" style="xhtml" />
					</div>
					<div id="ct_headerLogin">
						<jdoc:include type="modules" name="loginHeader" style="xhtml" />
					</div>
			   </div>
			   ');}
			   ?>
			   
			</div>          
		</div>

		<?php if($this->params->get('mainnav_position') == "left") { echo('</div><div id="ct_rightWrapper">');}; ?>

			<div id="ct_siteWrapper">
				
				<?php if ($this->countModules( 'slider' ) or $this->countModules('highlights_1_1 + highlights_1_2 + highlights_1_3 + highlights_1_4') == 0) : ?>
				<?php endif; ?>
						
				<jdoc:include type="message" />
				
				<?php if ($this->countModules( 'slider' )) : ?>
					<div id="ct_sliderWrapper">
						<div id="ct_sliderContent">
							<jdoc:include type="modules" name="slider" style="xhtml" />
						</div>
					<div id="ct_sliderShadow"></div>
					</div>
				<?php endif; ?>
				
				<div id="ct_contentWrapper">
				
					<?php if ($this->countModules( 'highlights_1_1 or highlights_1_2 or highlights_1_3 or highlights_1_4' )) : ?>
					<div id="ct_highlightsWrapper">
						<div id="ct_highlightsContent">
						
							<?php if ($this->countModules( 'highlights_1_1' )) : ?>
								<div class="ct_module <?php echo $moduleWidthHighlightsRow1?>"><jdoc:include type="modules" name="highlights_1_1" style="xhtml" /></div>
							<?php endif; ?>
							<?php if ($this->countModules( 'highlights_1_2' )) : ?>
								<div class="ct_module <?php echo $moduleWidthHighlightsRow1?>"><jdoc:include type="modules" name="highlights_1_2" style="xhtml" /></div>
							<?php endif; ?>
							<?php if ($this->countModules( 'highlights_1_3' )) : ?>
								<div class="ct_module <?php echo $moduleWidthHighlightsRow1?>"><jdoc:include type="modules" name="highlights_1_3" style="xhtml" /></div>
							<?php endif; ?>
							<?php if ($this->countModules( 'highlights_1_4' )) : ?>
								<div class="ct_module <?php echo $moduleWidthHighlightsRow1?>"><jdoc:include type="modules" name="highlights_1_4" style="xhtml" /></div>
							<?php endif; ?>
							
									  
						</div>
						<div class="ct_clearFloat"></div> 
					</div>
					<?php endif; ?>
				

					
					<div id="ct_mainContent">
					
							<?php if ($this->countModules( 'breadcrumbs' )) : ?>
								<div class="ct_breadcrumbs"><jdoc:include type="modules" name="breadcrumbs" style="xhtml" /></div>
							<?php endif; ?>
					
							<?php if ($this->countModules( 'maincontent_1_1' )) : ?>
								<div class="ct_module <?php echo $moduleWidthMaincontentRow1?>"><jdoc:include type="modules" name="maincontent_1_1" style="xhtml" /></div>
							<?php endif; ?>	
							<?php if ($this->countModules( 'maincontent_1_2' )) : ?>    
								<div class="ct_module <?php echo $moduleWidthMaincontentRow1?>"><jdoc:include type="modules" name="maincontent_1_2" style="xhtml" /></div>
							<?php endif; ?>
							<?php if ($this->countModules( 'maincontent_1_3' )) : ?>	
								<div class="ct_module <?php echo $moduleWidthMaincontentRow1?>"><jdoc:include type="modules" name="maincontent_1_3" style="xhtml" /></div>
							<?php endif; ?>
							<?php if ($this->countModules( 'maincontent_1_4' )) : ?>	
								<div class="ct_module <?php echo $moduleWidthMaincontentRow1?>"><jdoc:include type="modules" name="maincontent_1_4" style="xhtml" /></div>
							<?php endif; ?>
							<div class="ct_clearFloat"></div>
							
							
							<?php if ($this->countModules( 'left' )) : ?>
								<div class="ct_left"><jdoc:include type="modules" name="left" style="xhtml" /></div>
							<?php endif; ?>
							
							<div class="ct_componentContent <?php echo $moduleWidthcomponentContent?>">
								
								<jdoc:include type="component" />
							</div>
									
							<?php if ($this->countModules( 'right' )) : ?>
								<div class="ct_right"><jdoc:include type="modules" name="right" style="xhtml" /></div>
							<?php endif; ?>
							<div class="ct_clearFloat"></div>
							
							
							
							<?php if ($this->countModules( 'maincontent_2_1' )) : ?>
								<div class="ct_module <?php echo $moduleWidthMaincontentRow2?>"><jdoc:include type="modules" name="maincontent_2_1" style="xhtml" /></div>
							<?php endif; ?>
							<?php if ($this->countModules( 'maincontent_2_2' )) : ?>
								<div class="ct_module <?php echo $moduleWidthMaincontentRow2?>"><jdoc:include type="modules" name="maincontent_2_2" style="xhtml" /></div>
							<?php endif; ?>
							<?php if ($this->countModules( 'maincontent_2_3' )) : ?>
								<div class="ct_module <?php echo $moduleWidthMaincontentRow2?>"><jdoc:include type="modules" name="maincontent_2_3" style="xhtml" /></div>
							<?php endif; ?>
							<?php if ($this->countModules( 'maincontent_2_4' )) : ?>
								<div class="ct_module <?php echo $moduleWidthMaincontentRow2?>"><jdoc:include type="modules" name="maincontent_2_4" style="xhtml" /></div>
							<?php endif; ?>
							<div class="ct_clearFloat"></div>
					</div>
					
				</div>
				
				<?php if ($this->countModules( 'footer_1_1 or footer_1_2 or footer_1_3 or footer_1_4' )) : ?>
				<div id="ct_footerWrapper">
					<div id="ct_footerContent">  
					
						<?php if ($this->countModules( 'footer_1_1' )) : ?> 
                            <div class="ct_module <?php echo $moduleWidthFooterRow1?>"><jdoc:include type="modules" name="footer_1_1" style="xhtml" /></div>
                        <?php endif; ?>    
                        <?php if ($this->countModules( 'footer_1_2' )) : ?>
                            <div class="ct_module <?php echo $moduleWidthFooterRow1?>"><jdoc:include type="modules" name="footer_1_2" style="xhtml" /></div>
                        <?php endif; ?>    
                        <?php if ($this->countModules( 'footer_1_3' )) : ?>
                            <div class="ct_module <?php echo $moduleWidthFooterRow1?>"><jdoc:include type="modules" name="footer_1_3" style="xhtml" /></div>
                         <?php endif; ?>
                         <?php if ($this->countModules( 'footer_1_4' )) : ?>
                            <div class="ct_module <?php echo $moduleWidthFooterRow1?>"><jdoc:include type="modules" name="footer_1_4" style="xhtml" /></div>
                         <?php endif; ?>             
					</div>
					<div class="ct_clearFloat"></div>   
				</div>
				<?php endif; ?>
			</div>
            <br />
		
    <div style="display: block; text-align: center;">Get more <a href="http://crosstec.de/en/joomla-templates.html">Joomla!&reg; Templates</a> and <a href="http://crosstec.de/en/extensions/joomla-forms-download.html">Joomla!&reg; Forms</a> From <a href="http://crosstec.de/">Crosstec</a></div>

</div>

<?php if($this->params->get('mainnav_position') == "left") { echo('</div>');}; ?>

<div id="ct_bgImage"></div>

</body>
</html>
