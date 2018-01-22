<?php 
/*Plugin Name: Custom Masonry Gallery Post - Like Instagram
Plugin URI: http://pembenidesigns.com
Description: This plugin provides a shortcode to list posts, with parameters. It also registers a couple of post types and tacxonomies to work with.
Version: 1.0
Author: Steven Kirika
Author URI: http://pembenidesigns.com
License: GPLv2
*/

// register custom post type to work with
add_action( 'init', 'rmcc_create_post_type' );
function rmcc_create_post_type() {
	// clothes custom post type
	// set up labels
	$labels = array(
 		'name' => 'CM Gallery Post',
    	'singular_name' => 'Gallery Post',
    	'add_new' => 'Add New',
    	'add_new_item' => 'Add New Gallery Post',
    	'edit_item' => 'Edit Gallery Post Item',
    	'new_item' => 'New Gallery Post',
    	'all_items' => 'All Gallery Post',
    	'view_item' => 'View Gallery Post Item',
    	'search_items' => 'Search Gallery Post',
    	'not_found' =>  'No Gallery Post Found',
    	'not_found_in_trash' => 'No Gallery Post found in Trash', 
    	'parent_item_colon' => '',
    	'menu_name' => 'CMGalleryPost',
    );
	register_post_type( 'cmgallerypost', array(
		'labels' => $labels,
		'has_archive' => true,
 		'public' => true,
		'hierarchical' => true,
		'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		'taxonomies' => array( 'post_tag', 'category' ),	
		'exclude_from_search' => true,
		'capability_type' => 'post',
		'menu_icon'   => 'dashicons-camera',
		)
	);
	
}
//register scripts & styles
wp_register_script( 'cmgallerypost-js', plugins_url( 'main.js', __FILE__ ), array(jquery), true );
wp_register_script( 'masonry-pkgd-min-js', plugins_url( 'masonry.pkgd.min.js', __FILE__ ), array(jquery), true );
//wp_register_script( 'fontawesome-js', '//use.fontawesome.com/132025f8f9.js', true );

wp_register_style( 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', array(), '3.3.7' );


//add thumbnails to admin post lis view


add_image_size('custom_featured_preview', 80, 80, true);
function add_img_column($columns) {
    $columns['img'] = 'Featured Image';
    return $columns;
}

function manage_img_column($column_name, $post_id) {
    if( $column_name == 'img' ) {
        echo get_the_post_thumbnail($post_id, 'custom_featured_preview');
    }
    return $column_name;
}
add_filter('manage_edit-cmgallerypost_columns', 'add_img_column');
add_filter('manage_cmgallerypost_posts_custom_column', 'manage_img_column', 10, 2);

//enque scripts
function CMGalleryPost_enqueue_scripts() {
    wp_enqueue_style('bootstrap'); 
    wp_enqueue_style('cmgallerypost-style', plugins_url( 'style.css', __FILE__ )); 
    wp_enqueue_script( 'cmgallerypost-js');
    wp_enqueue_script('masonry-pkgd-min-js');
   // wp_enqueue_script('fontawesome-js');
}
add_action('wp_enqueue_scripts', 'cmgallerypost_enqueue_scripts');


//masonry layout
function cmgallerypost_short_shortcode( $atts ) {
	ob_start();
 	extract(shortcode_atts( array ( 
		'order' => 'date',
		'orderby' => 'title',
		'posts' => -1, 
		'category' => '',
	), $atts ) );
	$options = array(
		'post_type' => 'cmgallerypost',
		'order' => $order,
		'orderby' => $orderby,
		'posts_per_page' => $posts, 
		'category_name' => $category,
	);
	$query = new WP_Query( $options );
	if ( $query->have_posts() ) {?>
		<div id="masonry" class="row">
		    <?php
			$i=0;
			while ( $query->have_posts() ) : $query->the_post(); 
			 //check whether the post has a featured image
		       global $featured_img_url;
			
			   $featured_img_url ='';
		    	/* grab the url for the full size featured image */ 
			   $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
			
			   if($featured_img_url !=''){
			   ?>
		         <div class="pic_holder carouselGallery-col-1 col-md-4 carouselGallery-carousel" data-index="<?php echo $i ?>" data-username="<?php the_title(); ?>" 
		         data-imagetext="<?php 
			   $excerpt = '';
              if (has_excerpt()) {
              echo $excerpt = wp_strip_all_tags(get_the_excerpt());
              echo '<br>';
              }
              $post_tags = get_the_tags();
 
                    if ( $post_tags ) {
                        foreach( $post_tags as $tag ) {
                        echo $tag->name . ', '; 
                        }
                    }
                    
			   ?>" data-location="" data-likes="3144" data-posturl="<?php the_permalink(); ?>"
                data-imagepath="<?php echo esc_url($featured_img_url); ?>">
		             <img src="<?php echo esc_url($featured_img_url); ?>" />
                </div>
          <?php
			   
			       $i++;
			   }
			endwhile;
			wp_reset_postdata(); ?>
		</div><!-- .masonry -->
		  <script>
          var container = document.querySelector('#masonry');
          var masonry = new Masonry(container, {
            //columnWidth: 50,
            itemSelector: '.pic_holder'
            });
           </script>
	 
	 
		 
	<?php $myvariable = ob_get_clean();
	return $myvariable;
	}	
}
// create shortcode with parameters so that the user can define what's queried - default is to list all blog posts
add_shortcode( 'cmgallerypost_masonry_shortcode', 'cmgallerypost_short_shortcode' );


//3 coloumn layout
function cmgallerypost_column_short_shortcode( $atts ) {
	ob_start();
 	extract(shortcode_atts( array ( 
		'order' => 'date',
		'orderby' => 'title',
		'posts' => 3, 
		'category' => '',
	), $atts ) );
	$options = array(
		'post_type' => 'cmgallerypost',
		'order' => $order,
		'orderby' => $orderby,
		'posts_per_page' => $posts, 
		'category_name' => $category,
	);
	$query = new WP_Query( $options );
	if ( $query->have_posts() ) {?>
		<div class="row">
		    <?php
			$i=0;
			while ( $query->have_posts() ) : $query->the_post(); 
			 //check whether the post has a featured image
		       global $featured_img_url;
			
			   $featured_img_url ='';
		    	/* grab the url for the full size featured image */ 
			   $featured_img_url = get_the_post_thumbnail_url(get_the_ID(), 'full');
			
			   if($featured_img_url !=''){
			   ?>
		         <div class="pic_holder carouselGallery-col-1 col-md-4 carouselGallery-carousel" data-index="<?php echo $i ?>" data-username="<?php the_title(); ?>" 
		         data-imagetext="<?php 
			   $excerpt = '';
              if (has_excerpt()) {
              echo $excerpt = wp_strip_all_tags(get_the_excerpt());
              echo '<br>';
              }
              $post_tags = get_the_tags();
 
                    if ( $post_tags ) {
                        foreach( $post_tags as $tag ) {
                        echo $tag->name . ', '; 
                        }
                    }
                    
			   ?>" data-location="" data-likes="3144" 
                data-imagepath="<?php echo esc_url($featured_img_url); ?>">
		             <img src="<?php echo esc_url($featured_img_url); ?>" />
                </div>
          <?php
			   
			       $i++;
			   }
			endwhile;
			wp_reset_postdata(); ?>
		</div><!-- .row -->
		 
	<?php $myvariable = ob_get_clean();
	return $myvariable;
	}	
}
// create shortcode with parameters so that the user can define what's queried - default is to list all blog posts
add_shortcode( 'cmgallerypost_column_shortcode', 'cmgallerypost_column_short_shortcode' );


 
?>
