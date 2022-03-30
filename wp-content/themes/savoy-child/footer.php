<?php 
	global $nm_theme_options, $nm_globals;
?>
                </div> <!-- .nm-page-wrap-inner -->
            </div> <!-- .nm-page-wrap -->
            
            <footer id="nm-footer" class="nm-footer">
                <div class="nm-row aligncenter bgblue">
                     <div class="col-xs-4">
                        <img src="/wp-content/uploads/2021/12/securepay.png">
                        <h3 style="color: #fff;">
                          PAIEMENT SÉCURISÉ <br>
                          Cryptage SSL
                        </h3>
                     </div>

                     <div class="col-xs-4">
                        <img src="/wp-content/uploads/2021/12/free-delivery.png">
                        <h3 style="color: #fff;">
                            LIVRAISON GRATUITE<br>
                            En France
                        </h3>
                     </div>

                     <!--<div class="col-xs-4">
                        <img src="/wp-content/themes/savoy-child/images/payments.png">
                        <br>
                     </div>-->

                     <div class="col-xs-4">
                        <img src="/wp-content/uploads/2021/12/help.png">
                        <h3 style="color: #fff;"> 
                            AIDE EN 24 HEURES<br>
                            Appelez pour obtenir de l’aide
                        </h3>                       
                     </div>
                </div>

                <div class="nm-row bgwhite">
                    <div class="col-xs-6">
                        <h3>METHODE DE PAIEMENT</h3>
                        <img src="/wp-content/themes/savoy-child/images/cardss.png">
                    </div>

                    <div class="col-xs-6">
                        <h3>NOS TRANSPORTEURS</h3> 
                        <img src="/wp-content/themes/savoy-child/images/deliveries.png">
                    </div>
                </div>

                <div class="nm-row bgred">
                    <h3>Pour information:</h3>
                    Nous vendons du matériel professionnel à des professionnels. Sauf certaines sous-sections, l’article L121-16-1 du code de la consommation ne s’applique pas.
                    Les photos, images, données techniques et tarifs sont sous réserve d’erreur et de modification et peuvent être soumis à modification sans préavis

                </div>

                <div class="nm-row bggray">
                    <a href="https://materiel.discount/">
                        <img src="https://materiel.discount/wp-content/themes/savoy-child/images/logo.svg" class="nm-logo" width="288" height="35" alt="Materiel design">
                    </a>
                </div>

                <div class="contact-navi-at-footer">
                    <?php dynamic_sidebar( 'footer-navi-zone' ); ?> 
                </div>
                
                <?php
                    // Footer widgets
                    if ( is_active_sidebar( 'footer' ) ) {
                        get_template_part( 'template-parts/footer/footer', 'widgets' );
                    }
                ?>
                
                <?php
                    // Footer bar (or Elementor Pro footer location)
                    if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
						get_template_part( 'template-parts/footer/footer', 'bar' );
					}
                ?>
            </footer>
            
            <?php 
                // Mobile menu
                get_template_part( 'template-parts/navigation/navigation', 'mobile' );
            ?>
            
            <?php
                // Cart panel
                if ( $nm_globals['cart_panel'] ) {
                    get_template_part( 'template-parts/woocommerce/cart-panel' );
                }
            ?>
            
            <?php
                // Login panel
                if ( $nm_globals['login_popup'] && ! is_user_logged_in() && ! is_account_page() ) {
                    get_template_part( 'template-parts/woocommerce/login' );
                }
			?>

            <div id="nm-page-overlay"></div>
            
            <div id="nm-quickview" class="clearfix"></div>
            
            <?php wp_footer(); // WordPress footer hook ?>
        
        </div> <!-- .nm-page-overflow -->
        <script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js"></script>

	<script>
        jQuery('#menu-dynamic-categories-menu-1').isotope({
          itemSelector: ' .menu-item-has-children',
          percentPosition: true,
          masonry: {
            columnWidth: ' .menu-item-has-children'
          }
        });
	</script>
 

	</body>
</html>