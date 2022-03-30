<?php
	
	/* Styles
	=============================================================== */
	
	function nm_child_theme_styles() {
        // Enqueue child theme styles
        wp_enqueue_style( 'nm-child-theme', get_stylesheet_directory_uri() . '/style.css' );
	}
	add_action( 'wp_enqueue_scripts', 'nm_child_theme_styles', 1000 );
	// Note: Use priority "1000" to include the stylesheet after the parent theme stylesheets

	function mob_redirect(){
	    if( wp_is_mobile() && is_front_page() ){
	        $redirect_url = 'https://materiel.discount/home-mobile/';
	        header('Location: ' . $redirect_url ); // Redirect the user
	         
	    }
	}
	add_action( 'template_redirect', 'mob_redirect' );


	function woocommerce_after_shop_loop_item_title_short_description() {
		global $product;

		if ( ! $product->post->post_excerpt ) return;
		?>
		<div itemprop="description">
			<?php echo apply_filters( 'woocommerce_short_description', $product->post->post_excerpt ) ?>
		</div>
		<?php
	}
	add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_after_shop_loop_item_title_short_description', 5);

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

	function widgetAtTop() {

		register_sidebar( array(
			'name'          => 'Top',
			'id'            => 'top-zone',
			'description'   => 'This is the widget at top',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) );
		
	}

	add_action( 'widgets_init', 'widgetAtTop' );

	function navigationAtBottom() {

		register_sidebar( array(
			'name'          => 'Navigation at footer',
			'id'            => 'footer-navi-zone',
			'description'   => 'This is the widget at top',
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		) ); 
		
	}

	add_action( 'widgets_init', 'navigationAtBottom' );

	//woocommerce get lowest price in category
		function wpq_get_min_price_per_product_cat( $term_id ) {
		//var_dump($term_id);
		  //echo $term_id;
		  
		  
		$category = array( $term_id );

		$args = array(
			'posts_per_page' => -1,
			'post_type' => 'product',
			'orderby' => 'meta_value_num',
			'order' => 'ASC',
			'tax_query' => array(
				array(
					'taxonomy' => 'product_cat',
					'field' => 'id',
					'terms' => $category,
					'operator' => 'IN'
				)
			),
			'meta_query' => array(
				array(
					'key' => '_price',
				)
			)       
		);


		$loop = new WP_Query($args);
		
		return get_post_meta($loop->posts[0]->ID, '_price', true);
		  
		}

		add_filter('custom_lowest_pricing_category', 'wpq_get_min_price_per_product_cat');
		//end get lowest price in category


		//action hooks to make the related products after single product 
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

		add_action( 'woocommerce_after_single_product', 'woocommerce_upsell_display', 15 );
		add_action( 'woocommerce_after_single_product', 'woocommerce_output_related_products', 20 );

		add_action( 'admin_post_nopriv_contact_form', 'prefix_send_email_to_admin' );
		add_action( 'admin_post_contact_form', 'prefix_send_email_to_admin' );

		add_action( 'woocommerce_after_add_to_cart_button', 'add_content_after_addtocart_button_func' );

		/*
		 * Content below "Add to cart" Button.
		 */

function add_content_after_addtocart_button_func() {
			global $product;
			$price = $product->get_sale_price();
			if(empty($price)) {
				$price = $product->get_price();
			}
			$two_product_price = number_format(($price/2), 2, ",", ",");
			$three_product_price = number_format(($price/3), 2, ",", ",");
			$four_product_price = number_format(($price/4), 2, ",", ",");
        // Echo content.
        echo '<div class="product_extra_features_addon_container">
        <p>Facilités de paiement CB</p>
        <div class="col-md-12">
        <div class="col-md-4">
        	<span class="product_extra_features_addon_star">*</span>
        	<h4 style="color: #f9cd2c;">2X</h4>
        	<span class="product_extra_features_addon">01/05/20: '.$two_product_price.' HT</span>
        	<span class="product_extra_features_addon">01/06/20: '.$two_product_price.' HT</span>
        </div>
        <div class="col-md-4" style="margin-left: 7px;">
        <span class="product_extra_features_addon_star">*</span>
        	<h4 style="color: #ff9b35;">3X</h4>
        	<span class="product_extra_features_addon small">01/05/20: '.$three_product_price.' HT</span>
        	<span class="product_extra_features_addon small">01/06/20: '.$three_product_price.' HT</span>
        	<span class="product_extra_features_addon small">01/07/20: '.$three_product_price.' HT</span>
        </div>
        <div class="col-md-4" style="margin-left: 7px;">
        <span class="product_extra_features_addon_star">*</span>
        	<h4 style="color: #f90202;">4X</h4>
        	<span class="product_extra_features_addon">01/05/20: '.$four_product_price.' HT</span>
        	<span class="product_extra_features_addon">01/06/20: '.$four_product_price.' HT</span>
        	<span class="product_extra_features_addon">01/07/20: '.$four_product_price.' HT</span>
        	<span class="product_extra_features_addon">01/08/20: '.$four_product_price.' HT</span>
        </div>
        </div>
        <p class="product_extra_features_addon_tag_line">*(+ Frais de dossier + TVA)</p>

        </div>';

}

remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

add_action( 'woo_custom_catalog_ordering', 'woocommerce_catalog_ordering', 30 );

function woostify_custom_sku_product_page( $template )  {
	global $product;
	$sku = $product->get_sku();
	echo "SKU: <span class='rsku'>$sku<span class='ram'>M</span></span>";
}
add_action( 'woocommerce_after_shop_loop_item_title', 'woostify_custom_sku_product_page', 1, 1);

function ts_hide_empty_categories ( $hide_empty ){
    $hide_empty = TRUE; 
}
add_filter( 'woocommerce_product_subcategories_hide_empty', 'ts_hide_empty_categories', 10, 1 );




// 1 - Display custom checkout radio buttons fields
add_action( 'woocommerce_cart_totals_before_order_total', 'display_custom_checkout_radio_buttons' );

function display_custom_checkout_radio_buttons() {
    $custom_subtotal = WC()->cart->get_subtotal() ; 

    if ( $custom_subtotal > 0 ) {
        $value = WC()->session->get( 'warranty' );
        $value = empty( $value ) ? WC()->checkout->get_value( 'warranty' ) : $value;
        $value = empty( $value ) ? '0' : $value;

        echo '<div id="checkout-radio">
            <h5>' . __("Choix de garantie") .'</h5>';
     
        ?>
			 <p class="form-row form-row-wide update_totals_on_change warranty-input" id="warranty_field" data-priority="">
				<span class="woocommerce-input-wrapper">
					<select name="warranty" id="warranty" class="select " data-placeholder="">
						<option value="0" <?php echo $value == '0' ? 'selected' : '' ?>>Garantie pièce(s) de rechange 1 an  (supplément de 0%)</option>
						<option value="5" <?php echo $value == '5' ? 'selected' : '' ?>>Garantie pièce(s) de rechange 2 ans  (supplément de 5%)</option>
						<option value="10" <?php echo $value == '10' ? 'selected' : '' ?>>Garantie pièce(s) de rechange 3 ans  (supplément de 10%)</option>
						<option value="15" <?php echo $value == '15' ? 'selected' : '' ?>>Garantie pièce(s) de rechange 5 ans  (supplément de 15%)</option>
					</select>
				</span>
			</p>
		<?php

        echo '</div>';
    }

}

// 2 - Customizing Woocommerce checkout radio form field
function custom_form_field_radio( $field, $key, $args, $value ) {
    if ( ! empty( $args['options'] ) && 'warranty' === $key && is_cart) {
        $field = str_replace( '</label><input ', '</label><br><input ', $field );
        $field = str_replace( '<label ', '<label style="display:inline;margin-left:8px;" ', $field );
    }
    return $field;
}

// 3 - Add a percentage Fee based on radio buttons
add_action( 'woocommerce_cart_calculate_fees', 'percentage_fee_based_on_radio_buttons', 20, 1 );

function percentage_fee_based_on_radio_buttons() {
	global $woocommerce; 
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    $percentage = (float) WC()->session->get( 'warranty' );

    if ( $percentage ) {
		$custom_subtotal = WC()->cart->get_subtotal();
		$custom_subtotal_tax = WC()->cart->get_subtotal_tax();
		
        if ( $custom_subtotal > 0 ) {
        	if ($percentage == "0") {
				$label = "Garantie pièce(s) de rechange 1 ans  (supplément de 1s%)";
				$labelTax = "TVA 1 an de garantie ";
			} else if ($percentage == "5") {
				$label = "Garantie pièce(s) de rechange 2 ans  (supplément de 5%)";
				$labelTax = "TVA 2 an de garantie ";
			} else if ($percentage == "10") {
				$label = "Garantie pièce(s) de rechange 3 ans  (supplément de 10%)";
				$labelTax = "TVA 3 ans de garantie";
			}
			else if ($percentage == "15") {
				$label = "Garantie pièce(s) de rechange 5 ans  (supplément de 15%)";
				$labelTax = "TVA 5 ans de garantie";
			}
            $label_text = __($label, 'woocommerce');
            $labelTax = __($labelTax, 'woocommerce');
            $woocommerce->cart->add_fee( $label_text, $custom_subtotal * $percentage / 100, false, 'standard' );
            $woocommerce->cart->add_fee( $labelTax, $custom_subtotal_tax * $percentage / 100, false, 'standard' ); 
        }
    }
}

// 4 - Set chosen radio button value to a WC Session variable
add_action( 'woocommerce_cart_updated', 'chosen_input_radio_button_value_to_wc_session' );

function chosen_input_radio_button_value_to_wc_session( $posted_data ) {
    parse_str( $posted_data, $fields );

    if ( isset( $fields['warranty'] ) ){
        WC()->session->set( 'warranty', $fields['warranty'] );
    }
}

add_action('wp_ajax_update_warranty', 'set_warranty_session');

add_action('wp_ajax_nopriv_update_warranty', 'set_warranty_session');

function set_warranty_session() {
	
    if ( isset( $_POST['warranty'] ) ){
        WC()->session->set( 'warranty', $_POST['warranty'] );
    }
	
	echo wp_send_json_success(array('success' => true));
}

add_action('wp_footer', function() {
	if ( is_cart() ) {
	?>
	<script>
		jQuery(document).ready(function($) {

			$(document).on('change', 'select[name="warranty"]', function() {
				const select = $(this);
				const warranty = select.val();
				
				$.ajax({
					url: '<?php echo admin_url('admin-ajax.php') ?>',
					data: {
						action: 'update_warranty',
						warranty: warranty 
					},
					method: 'POST',
					dataType: 'JSON',
					beforeSend: function() {
						select.attr('disabled', 'disabled');
					},
					complete: function() {
						select.removeAttr('disabled');
					},
					success: function(res) {
						if (res.success) {
							jQuery( document.body ).trigger( 'added_to_cart', [  ] );
						}
					},
				});
			});
		});
	</script>
	<?php
	}
});

function top_cat_or_page_body_class( $class ) {
    $prefix = 'topic-'; // Editable class name prefix.
    global $wp_query;
    $object_id = $wp_query->get_queried_object_id();
    $top_slug  = ( is_home() ) ? 'home' : 'default';

    if ( is_single() ) {
        $cats    = get_the_category( $object_id ); // Get post categories.
        $parents = get_ancestors( $cats[0]->term_id, 'category', 'taxonomy' );
        $top_id  = ( $parents ) ? end( $parents ) : $object_id;

        // If term has parents, get ID of top page.
        $top_cat  = get_category( $top_id ); // Get top cat object.
        $top_slug = $top_cat->slug; // Get top cat slug.
    }

    if ( is_category() ) {
        $parents = get_ancestors( $object_id, 'category', 'taxonomy' );
        $top_id  = ( $parents ) ? end( $parents ) : $object_id;

        // If cat has parents, get ID of top cat.
        $top_cat  = get_category( $top_id ); // Get top cat object.
        $top_slug = $top_cat->slug; // Get top cat slug.
    }

    if ( is_page() ) {
        $parents = get_ancestors( $object_id, 'page', 'post_type' );
        $top_id  = ( $parents ) ? end( $parents ) : $object_id;

        // If page has parents, get ID of top page.
        $top_page = get_post( $top_id ); // Get top page object.
        $top_slug = $top_page->post_name; // Get top page slug.
    }

    $class[] = $prefix . $top_slug;

    return $class;
}
add_filter( 'body_class', 'top_cat_or_page_body_class' );
 


add_action('woocommerce_checkout_before_terms_and_conditions', 'checkout_additional_checkboxes');
function checkout_additional_checkboxes( ){
    $checkbox1_text = __( "Je certifie être un professionnel", "woocommerce" );
    $checkbox2_text = __( "J’ai bien compris que pour que la garantie soit applicable, les produits doivent être installés par un installateur spécialisé.", "woocommerce" );
    $checkbox3_text = __( "J’ai bien compris que la garantie est valable uniquement sur les pièces.", "woocommerce" );
    $checkbox4_text = __( "J’ai bien compris que la livraison se faisait sur le trottoir et ne comprenait pas l’installation des produits.", "woocommerce" );
    $checkbox5_text = __( "J’ai bien lu et j’accepte les Conditions Générales de Ventes du site www.materiel.discount", "woocommerce" );
    ?>
    <p class="form-row wpgdprc-checkbox validate-required">
    	<br>
        <label class="woocommerce-form__label checkbox custom-one">
            <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="custom_one" > <span><?php echo  $checkbox1_text; ?></span> <span class="required">*</span>
        </label>
    </p>

    <p class="form-row wpgdprc-checkbox validate-required">
        <label class="woocommerce-form__label checkbox custom-two">
            <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="custom_two" > <span><?php echo  $checkbox2_text; ?></span> <span class="required">*</span>
        </label>
    </p>

    <p class="form-row wpgdprc-checkbox validate-required">
        <label class="woocommerce-form__label checkbox custom-two">
            <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="custom_three" > <span><?php echo  $checkbox3_text; ?></span> <span class="required">*</span>
        </label>
    </p>

    <p class="form-row wpgdprc-checkbox validate-required">
        <label class="woocommerce-form__label checkbox custom-two">
            <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="custom_four" > <span><?php echo  $checkbox4_text; ?></span> <span class="required">*</span>
        </label>
    </p>
    
    <p class="form-row wpgdprc-checkbox validate-required">
        <label class="woocommerce-form__label checkbox custom-two">
            <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="custom_five" > <span>J’ai bien lu et j’accepte les <a href="https://materiel.discount/mentions-legales/" target="blank">Conditions Générales de Ventes</a> du site <a href="https://materiel.discount/mentions-legales/" target="blank">www.materiel.discount</a></span> <span class="required">*</span>
        </label>
    </p>

    <?php
}

add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');

function my_custom_checkout_field_process() {
    // Check if set, if its not set add an error.
    if ( ! $_POST['custom_one'] )
        wc_add_notice( __( 'Vous devez accepter "Je certifie être un professionnel".' ), 'error' );
    if ( ! $_POST['custom_two'] )
        wc_add_notice( __( 'Vous devez accepter "J’ai bien compris que pour que la garantie...".' ), 'error' );
    if ( ! $_POST['custom_three'] )
        wc_add_notice( __( '’ai bien compris que la garantie est valable...".' ), 'error' );
    if ( ! $_POST['custom_four'] )
        wc_add_notice( __( 'Vous devez accepter "J’ai bien compris que la livraison...".' ), 'error' );
    if ( ! $_POST['custom_five'] )
        wc_add_notice( __( 'J’ai bien lu et j’accepte les Conditions Générales de Ventes du site www.materiel.discount".' ), 'error' );
}



function custom_checkout_required_fields_error_notice( $error_notice, $field_label ) {
    $error_notice = sprintf( __( '%s est un champ obligatoire.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' );

    return $error_notice; 
}

add_filter( 'woocommerce_checkout_required_field_notice', 'custom_checkout_required_fields_error_notice', 10, 2 );


 