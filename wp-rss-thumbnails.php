<?php
/*
Plugin Name: WP RSS Thumbnails
Plugin URI: http://github.com/n0nick/WP-RSS-Thumbnails 
Description: Include the post thumbnail in your RSS feed.
             Based on WPP RSS Images by Alain Gonzalez
             http://web-argument.com/wp-rss-images-wordpress-plugin/
Version: 1.2
Author: Sagie Maoz
Author URI: http://sagie.maoz.info/
License: GPL
*/

define( 'WP_RSS_THUMBNAILS_NONCE', 'wp-rss-nonce' );

// add support for thumbnails
if ( function_exists( 'add_theme_support' ) ) { 
  add_theme_support( 'post-thumbnails' );
}

// action hooks
if ( get_option( 'rss_img_ch_op' ) == 1 ) // RSS
{
  add_action( 'rss_item', 'wp_rss_include' );
}
if ( get_option( 'rss2_img_ch_op') == 1 ) // RSS2
{
  add_action( 'rss2_item', 'wp_rss_include' );
	add_action( 'rss2_ns', 'wp_rss2_mrss' );
}
add_action( 'admin_menu', 'wp_rss_img_menu' ); // settings menu

function wp_rss2_mrss()
{
  echo "xmlns:media=\"http://search.yahoo.com/mrss/\"\n\t";
}

function rss_image_url( $size = 'medium' )
{	
  global $post;
  
  if ( has_post_thumbnail( $post->ID ) )
  {
    $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), $size);
    if ( isset( $thumbnail[0] ) )
    {
      return $thumbnail[0];
    }
  }
  return null;
}

function wp_rss_include()
{
  $image_size = get_option( 'rss_image_size_op' );
  if ( empty( $image_size ) )
  {
    $image_size = 'medium';
  }
  
  $image_url = rss_image_url( $image_size );
  
  if ( !empty( $image_url ) )
  {
    // find local path (thx @Michael Kröll)  
    $uploads = wp_upload_dir();
    $url = parse_url( $image_url );
    $path = $uploads['basedir'] . preg_replace( '/.*uploads(.*)/', '${1}', $url['path'] );
    
    if ( file_exists( $path ) )
    {
      $filesize = filesize( $path );
      echo '<enclosure url="' . $image_url . '" length="' . $filesize . '" type="image/jpg" />';
    }
  }
}

function wp_rss_img_menu()
{
    add_options_page( 'WP RSS Thumbnails', 'WP RSS Thumbnails', 10, 'wp-rss-image', 'wp_rss_image_setting' );	 
}

function wp_rss_image_setting()
{
  $image_size = get_option( 'rss_image_size_op' );
  if ( empty( $image_size ) ) {
    $image_size = 'medium';
  }

  $rss_img_ch   = get_option( 'rss_img_ch_op' );
  $rss2_img_ch  = get_option( 'rss2_img_ch_op' );	 
   
  if ( !empty( $_POST ) && check_admin_referer( WP_RSS_THUMBNAILS_NONCE ) )
  {
    $image_size = in_array( $_POST['image_size'], array( 'thumbnail', 'medium', 'full' ) ) ? $_POST['image_size'] : 'medium';
    $rss_img_ch = $_POST['rss_img_ch']  == 1 ? 1 : 0;
    $rss2_img_ch= $_POST['rss2_img_ch'] == 1 ? 1 : 0;

    update_option( 'rss_image_size_op', $image_size );
    update_option( 'rss_img_ch_op',     $rss_img_ch );
    update_option( 'rss2_img_ch_op',    $rss2_img_ch );
    
    echo '<div class="updated"><p><strong>' . _( 'Options saved.', 'mt_trans_domain' ) . '</strong></p></div>  ';
  }
?>

<div class="wrap">   

<form method="post" name="options" target="_self">
<?php wp_nonce_field( WP_RSS_THUMBNAILS_NONCE ); ?>

<h2><?php _e('WP RSS Thumbnails Settings') ?></h2>
<h3><?php _e('Select the size of the images') ?></h3>
<p><?php _e('You can change the dimension of this sizes under Miscellaneous Settings.') ?></p>
<table width="100%" cellpadding="10" class="form-table">

  <tr valign="top">
  	<td width="200" align="right">
  	  <input type="radio" name="image_size" id="radio" value="thumbnail" <?= ($image_size == 'thumbnail') ? "checked=\"checked\"" : '' ?> />
  	</td>
  	<td align="left" scope="row"><?php _e('Thumbnail') ?></td>
  </tr>
  <tr valign="top">
  	<td width="200" align="right">
	 <input name="image_size" type="radio" id="radio" value="medium" <?= ($image_size == 'medium') ? "checked=\"checked\"" : '' ?> />
     </td> 
  	<td align="left" scope="row"><?php _e('Medium') ?></td>
  </tr>
  <tr valign="top">
  	<td width="200" align="right">
	 <input type="radio" name="image_size" id="radio" value="full" <?= ($image_size == 'full') ? "checked=\"checked\"" : '' ?> />
     </td> 
  	<td align="left" scope="row"><?php _e('Full Size') ?></td>
  </tr>
</table>

<h3><?php _e('Apply to:') ?></h3>
<table width="100%" cellpadding="10" class="form-table">  
  <tr valign="top">
  	<td width="200" align="right"><input name="rss_img_ch" type="checkbox" value="1" 
	<?php if ( $rss_img_ch == 1 ) echo "checked" ?> /></td>
  	<td align="left" scope="row">
  	  <?php _e('RSS') ?>
  	  <a href="<?php echo get_bloginfo( 'rss_url' ); ?> " title="<?php bloginfo( 'name' ); ?> - rss" target="_blank"><?= get_bloginfo( 'rss_url' ); ?></a>
  	</td>
  </tr>
  <tr valign="top">
  	<td width="200" align="right"><input name="rss2_img_ch" type="checkbox" value="1"
	<?php if ( $rss2_img_ch == 1 )  echo "checked" ?> /></td>
  	<td align="left" scope="row">
  	  <?php _e('RSS 2') ?>
  	  <a href="<?php echo get_bloginfo( 'rss2_url' ); ?> " title="<?php bloginfo( 'name' ); ?> - rss2" target="_blank"><?= get_bloginfo( 'rss2_url' ); ?></a>
  	</td>
  </tr>    
</table>
<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update') ?>" />
</p>

</form>
</div>

<?php
}
?>
