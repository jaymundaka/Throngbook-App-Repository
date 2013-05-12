<?php
/**
 * @packageJomSocial
 * @subpackage Template 
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
//CFactory::load('libraries', 'photos');
$album	= JTable::getInstance( 'Album' , 'CTable' );
$album->load( $act->cid );
$act->album = $album;
$this->set('album', $album);

$user = CFactory::getUser(
$this->act->actor);
$param = new CParameter($act->params);
$action = $param->get('action');

// Load saperate template for featured photo
if( $act->app == 'albums.featured'){
	$this->load('activities.photos.featured'); 
	return;
}

// Load saperate template for comment on a photo
if( $action == 'wall'){
	$this->load('activities.photos.comment'); 
	return;
}


?>

<a class="cStream-Avatar cFloat-L" href="<?php echo CUrlHelper::userLink($user->id); ?>">
	<img class="cAvatar" data-author="<?php echo $user->id; ?>" src="<?php echo $user->getThumbAvatar(); ?>">
</a>

<div class="cStream-Content">
	<div class="cStream-Headline">
		<a class="cStream-Author" href="<?php echo CUrlHelper::userLink($user->id); ?>"><?php echo $user->getDisplayName(); ?></a>
		<?php
		// If we're using new stream style or has old style data (which contains {multiple} )
		if($param->get('style') == COMMUNITY_STREAM_STYLE || strpos($act->title, '{multiple}') ){
			// New style
			$count = $param->get('count');
			if($count > 1)
			{
				echo JText::sprintf( 'COM_COMMUNITY_ACTIVITY_PHOTO_UPLOAD_TITLE_MANY' , $count, $album->getURI(), CStringHelper::escape($album->name) );
			}else 
			{
				echo JText::sprintf( 'COM_COMMUNITY_ACTIVITY_PHOTO_UPLOAD_TITLE' , $album->getURI(), CStringHelper::escape($album->name) );;
			}
		
		}

		?>				
	</div>

	<?php 
	// If custom message is there for single photo upload, display it
	// User for style=1 only (since 2.8)
	if( !empty($act->title) && $param->get('style') == 1) { ?>
		<div class="cStream-Quote">
			<?php echo CActivities::format($act->title); ?>
		</div>
	<?php } ?>
	
	<div class="cStream-Attachment">
		<?php
		$html = CPhotos::getActivityContentHTML($act);
		echo $html;
		?>
	</div>
	

	<?php 
	// No action for wall comment
	if( $action != 'wall'){
		$this->load('activities.actions'); 
	}
	?>
</div>