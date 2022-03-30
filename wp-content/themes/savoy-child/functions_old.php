<?php
	
	/* Styles
	=============================================================== */
	
	function nm_child_theme_styles() {
        // Enqueue child theme styles
        wp_enqueue_style( 'nm-child-theme', get_stylesheet_directory_uri() . '/style.css' );
	}
	add_action( 'wp_enqueue_scripts', 'nm_child_theme_styles', 1000 ); // Note: Use priority "1000" to include the stylesheet after the parent theme stylesheets


	 
function newshopwidget() {

	register_sidebar( array(
		'name'          => 'Shop Right',
		'id'            => 'shop-right-zone',
		'description'   => 'This is the widget at right in the product page',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	
}

add_action( 'widgets_init', 'newshopwidget' );


//woocommerce get lowest price in category
function wpq_get_min_price_per_product_cat( $term_id ) {
  global $wpdb;
  $sql = "
    SELECT  MIN( meta_value+0 ) as minprice
    FROM {$wpdb->posts} 
    INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)
    INNER JOIN {$wpdb->postmeta} ON ({$wpdb->posts}.ID = {$wpdb->postmeta}.post_id) 
    WHERE  
      ( {$wpdb->term_relationships}.term_taxonomy_id IN (%d) ) 
    AND {$wpdb->posts}.post_type = 'product' 
    AND {$wpdb->posts}.post_status = 'publish' 
    AND {$wpdb->postmeta}.meta_key = '_price'
  ";
  return $wpdb->get_var( $wpdb->prepare( $sql, $term_id ) );
}
//end woocommerece
