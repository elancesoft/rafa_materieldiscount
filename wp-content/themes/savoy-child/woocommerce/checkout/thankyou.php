<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.7.0
 NM: Modified:
 - "nm-checkout-ty" and "nm-shop-notice" classes
 - Font-icon elements (three)
 - "<div class="nm-checkout-ty-order-details-top">" element
 - Commented out email from order-details */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="woocommerce-order nm-checkout-ty">
    <div class="retour-after-checkout">
        <p>
            <a href="https://materiel.discount/">< Retour</a> 
        </p>
    </div>
    <hr>
        <h1>Merci beaucoup pour votre confiance!</h1>
    <hr>

    <div class="num-de-commande">
        <h2>
            <?php esc_html_e( 'Order number:', 'woocommerce' ); ?>
            <strong><?php echo $order->get_order_number(); ?></strong>
        </h2>
    </div>
    <hr>

	<?php
    if ( $order ) :
    
        do_action( 'woocommerce_before_thankyou', $order->get_id() );
        ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed nm-shop-notice"><i class="nm-font nm-font-close"></i><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'woocommerce' ) ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>
			<div class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received nm-shop-notice">
                <i class="nm-font nm-font-check ok"></i>
               
                <span class="ok-right">
                    Nous vous avons envoyé une confirmation de commande avec toutes les informations sur la commande par e-mail. Veuillez vérifier votre boîte de réception e-mail, avec vous spécifié lors de votre commande
                </span>
                
            </div>
        <?php endif; ?>

            <div class="information-importante">
                <h2>
                    Information importante en cas de paiement anticipé/facture : 
                </h2>
                <p>
                    Votre commande sera traitée directement en recevant le paiement sur notre compte. Veuillez transférer le montant total en indiquant la référence (nº de commande) sur le compte indiquez ci-dessous. 
                </p>

                <?php if ( $order->get_payment_method_title() ) : ?>
                        <div class="woocommerce-order-overview__payment-method method">
                            <?php esc_html_e( 'Payment method:', 'woocommerce' ); ?>
                            <strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
                        </div>
                <?php endif; ?>
                <br>
                <?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>

                <hr>

                <div>   
                    <h2 class="black">Montant de la facture</h2>
                    <div class="woocommerce-order-overview__total total">
                        <strong><?php echo $order->get_formatted_order_total(); ?></strong>
                    </div>
                    <br>
                    <hr>
                    <h2 class="black">Utilisation préveu:</h2>
                    <div class="woocommerce-order-overview__total total">
                        <strong><?php echo $order->get_order_number(); ?></strong>
                    </div> 
                </div>

                
            </div>

            <?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php else : ?>

		

	<?php endif; ?>

</div>

<style type="text/css">
    .woocommerce-order-received .page > .woocommerce{
        padding-top: 0;
    }

    .retour-after-checkout{
        margin-bottom: 21px;
    }

    h1{
        text-align: center;
        font-weight: bold;
        padding: 21px 0;
    }

    h2{
        padding: 21px 0;
        font-size: 21px;
        color: #585858;
    }

    .woocommerce-notice.woocommerce-notice--success.woocommerce-thankyou-order-received.nm-shop-notice{
        background: #298401;
        margin-top: 21px;
        font-size: 12px;
        text-align: left;
        border: 1px solid #298401;
    }

    i.nm-font.nm-font-check.ok{
        top: 0;
        padding: 15px;
        margin: 0;
        color: #fff;
        background: #298401;
        vertical-align: top;
        width: 6%;
    }

    .ok-right{
        background: #e5f2dc;
        display: inline-block;
        width: 93.63%;
        vertical-align: text-top;
        padding: 5px 10px;
    }

    .information-importante{
        margin-top: 21px;
        border: 2px solid red;
        background: white;
        padding: 28px;
    }

    .information-importante h2{
        color: red;
        font-size: 16px;
        padding: 0;
    }

    .information-importante p{
        padding: 10px 0;
        margin: 0;
    }

    .information-importante .wc-bacs-bank-details-heading{
        color: #000;
    }

    .black{
        margin: 21px 0;
        color: #000!important;
    }

    .woocommerce-bacs-bank-details ul li{
        width: 100%;
    }

    .woocommerce-order-overview__total.total{
        background: #e2e2e2;
        padding: 0px 14px;
        font-size: 24px;
        width: auto;
        display: table;
    }
    @media (width: 856px){
        .ok-right{
            width: 100%;
        }
    }
</style>
