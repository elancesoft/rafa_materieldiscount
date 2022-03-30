<?php
/**
 * Checkout terms and conditions area.
 *
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

if ( apply_filters( 'woocommerce_checkout_show_terms', true ) && function_exists( 'wc_terms_and_conditions_checkbox_enabled' ) ) {
	do_action( 'woocommerce_checkout_before_terms_and_conditions' );

	?>
	<div class="woocommerce-terms-and-conditions-wrapper hereis"> 
		<?php
		/**
		 * Terms and conditions hook used to inject content.
		 *
		 * @since 3.4.0.
		 * @hooked wc_checkout_privacy_policy_text() Shows custom privacy policy text. Priority 20.
		 * @hooked wc_terms_and_conditions_page_content() Shows t&c page content. Priority 30.
		 */
			do_action( 'woocommerce_checkout_terms_and_conditions' );
		?>

		<br>

		<?php if ( wc_terms_and_conditions_checkbox_enabled() ) : ?>
		<p id="pogcheck" class="form-row wpgdprc-checkbox validate-required">
			<span class="woocommerce-input-wrapper">
		        <label class="checkbox">
		            <input id="wpgdprc" type="checkbox" value="1" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms"> 
		            <span class="gdpr-privacy">
		            	Je certifie être un professionnel
		            </span>
		        </label>

		        <label>
		            <input id="wpgdprc1" type="checkbox" value="1" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms"> 
		            <span class="gdpr-privacy">
						J’ai bien compris que pour que la garantie soit applicable, les produits doivent être installés par un installateur spécialisé.
		            </span>
		        </label>

		        <label>
		            <input id="wpgdprc2" type="checkbox" value="1" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms"> 
		            <span class="gdpr-privacy">
		            	J’ai bien compris que la garantie est valable uniquement sur les pièces.
		            </span>
		        </label>

		        <label>
		            <input id="wpgdprc3" type="checkbox" value="1" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms"> 
		            <span class="gdpr-privacy">
		            	J’ai bien compris que la livraison se faisait sur le trottoir et ne comprenait pas l’installation des produits.
		            </span>
		        </label>

		        <label>
		            <input type="checkbox" value="1" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms" <?php checked( apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['terms'] ) ), true ); // WPCS: input var ok, csrf ok. ?> id="terms" > 
		            <span class="gdpr-privacy"> 
		            	J’ai bien lu et j’accepte les Conditions Générales de Ventes du site <a target="_blank" href="https://materiel.discount">www.materiel.discount</a>
		            </span>
		        </label>
	        </span>
	    </p>

	    <?php endif; ?>

		<?php //if ( wc_terms_and_conditions_checkbox_enabled() ) : ?>
			<!--<p class="form-row validate-required">
				<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
				<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms" <?php //checked( apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['terms'] ) ), true ); // WPCS: input var ok, csrf ok. ?> id="terms" />
					<span class="woocommerce-terms-and-conditions-checkbox-text"><?php// wc_terms_and_conditions_checkbox_text(); ?></span>&nbsp;<span class="required">*</span>
				</label>
				<input type="hidden" name="terms-field" value="1" />
			</p>-->
		<?php //endif; ?>
	</div>
<style type="text/css">
	
	.payment_box.payment_method_payzenstd{
		margin: 14px 0;
		padding: 0!important;
	}

</style>

	<?php

	do_action( 'woocommerce_checkout_after_terms_and_conditions' );
}
