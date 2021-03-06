<?php
    global $nm_theme_options;
    
    // Copyright text
    $copyright_text = ( isset( $nm_theme_options['footer_bar_text'] ) && strlen( $nm_theme_options['footer_bar_text'] ) > 0 ) ? $nm_theme_options['footer_bar_text'] : '';
    if ( $nm_theme_options['footer_bar_text_cr_year'] ) {
        $copyright_text = sprintf( '&copy; %s %s', date( 'Y' ), $copyright_text );
    }
    
    // Right/bottom column content
    $display_social_icons = ( strpos( $nm_theme_options['footer_bar_content'], 'social' ) !== false ) ? true : false;
    $display_copyright_text = ( strpos( $nm_theme_options['footer_bar_content'], 'copyright' ) !== false ) ? true : false;
    $display_custom_content = ( $nm_theme_options['footer_bar_content'] == 'custom' ) ? true : false;
?>
<div class="nm-footer-bar layout-<?php echo esc_attr( $nm_theme_options['footer_bar_layout'] ); ?>">
    <div class="nm-footer-bar-inner">
        <div class="nm-row">
            <div class="nm-footer-bar-left col-md-8 col-xs-12">
                <?php 
                    if ( isset( $nm_theme_options['footer_bar_logo'] ) && strlen( $nm_theme_options['footer_bar_logo']['url'] ) > 0 ) :
                
                    $logo_src = ( is_ssl() ) ? str_replace( 'http://', 'https://', $nm_theme_options['footer_bar_logo']['url'] ) : $nm_theme_options['footer_bar_logo']['url'];
                    $logo_alt_attr_escaped = ( strlen( $nm_theme_options['footer_bar_logo']['title'] ) > 0 ) ? 'alt="' . esc_attr( $nm_theme_options['footer_bar_logo']['title'] ) . '"' : '';
                ?>
                <div class="nm-footer-bar-logo">
                    <img src="<?php echo esc_url( $logo_src ); ?>"<?php echo $logo_alt_attr_escaped; ?> />
                </div>
                <?php endif; ?>

                <ul id="nm-footer-bar-menu" class="menu">
                    <?php
                        // Footer menu
                        wp_nav_menu( array(
                            'theme_location'    => 'footer-menu',
                            'container'         => false,
                            'fallback_cb'       => false,
                            'items_wrap'        => '%3$s'
                        ) );
                    ?>
                    <?php if ( ! $display_copyright_text ) : ?>
                    <li class="nm-menu-item-copyright menu-item"><span><?php echo wp_kses_post( $copyright_text ); ?></span></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="nm-footer-bar-right col-md-4 col-xs-12">
                <?php if ( $display_social_icons ) : ?>
                    <?php echo nm_get_social_profiles( 'nm-footer-bar-social' ); // Args: $wrapper_class ?>
                <?php endif; ?>
                <?php if ( $display_copyright_text ) : ?>
                <div class="nm-footer-bar-copyright"><?php echo wp_kses_post( $copyright_text ); ?></div>
                <?php endif; ?>
                <?php if ( $display_custom_content ) : ?>
                <div class="nm-footer-bar-custom"><?php echo wp_kses_post( do_shortcode( $nm_theme_options['footer_bar_custom_content'] ) ); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/v4-font-face.min.css"/>



<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-143827258-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-143827258-1');
</script>

<script> 
     if ( jQuery( window).width() < 980 ) {
        jQuery('#nm-main-menu').remove()
    }

    jQuery('.toggle-menu').click(function(){
        jQuery(' .nm-main-menu , .nm-page-default.nm-row , #nm-shop ').toggleClass('menuopen')
    })

    jQuery(' .nm-menu-wishlist , .nm-menu-account ').appendTo('#nm-top-bar ul')
   
    jQuery(window).scroll(function(){
        if(jQuery(this).scrollTop() > 100) {
            jQuery('.nm-menu .sub-menu').addClass('scrolled');
        }else{ jQuery('.nm-menu .sub-menu').removeClass('scrolled'); }
    });

    

    jQuery(document).ready(function() {

        jQuery('#dprice').click(function(){
              jQuery( "#nm-menu-cart-btn" ).trigger( "click" );
        }) 

        jQuery(window).resize(function() { 
            if ( jQuery(this).width() < 909 ) {
               jQuery('.nm-main-menu').insertBefore('.nm-mobile-menu-secondary').addClass('navmoved')
            }else{ jQuery('.nm-main-menu').insertAfter('#nm-header').removeClass('navmoved') }
        });

        if ( jQuery( window).width() < 980 ) {
            jQuery('.nm-main-menu').remove()
        }else{
            jQuery('#nm-mobile-menu-main-ul').remove()
        }

        jQuery('<li class="control-menu-moebel"><h5>Ameublement</h5></li><li class="menu-moebel"></li>').prependTo('#nm-main-menu-ul , #nm-mobile-menu-main-ul')
    
        jQuery('<li class="control-menu-gastro"><h5>Mat??riels alimentaire</h5></li><li class="menu-gastro"></li>').prependTo('#nm-main-menu-ul , #nm-mobile-menu-main-ul')

        jQuery( '#nm-main-menu-ul li.menu-item-has-children , #nm-mobile-menu-main-ul .menu-item-has-children' ).each( function(){
            jQuery(this).find('.nm-menu-item-image-title').each( function(){
                
                if (jQuery(this).text() === 'Petits ustensiles') {
                    jQuery(this).addClass('gadgets')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Ustensiles de cuisine') {
                    jQuery(this).addClass('gadgets')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Garder la chaleur') {
                    jQuery(this).addClass('garder')
                    jQuery(this).parent().parent().addClass('moebelg')
                } 

                if (jQuery(this).text() === 'Four ?? vapeur combin??') {
                    jQuery(this).addClass('four')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === '??clairage') {
                    jQuery(this).addClass('eclaire')
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                if (jQuery(this).text() === 'Rin??age et nettoyage') {
                    jQuery(this).addClass('rincage')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === '??tag??res des magasins') {
                    jQuery(this).addClass('etageres')
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                if (jQuery(this).text() === 'Chaises') {
                    jQuery(this).addClass('chaises')
                    jQuery(this).parent().parent().addClass('moebelg')
                };

                if (jQuery(this).text() === 'Meuble de s??paration') {
                    jQuery(this).addClass('meuble-de-separation')
                    jQuery(this).parent().parent().addClass('moebelg')
                };

                if (jQuery(this).text() === 'Tabourets de bar') {
                    jQuery(this).addClass('tabourets')
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                if (jQuery(this).text() === 'Transport de bagages') {
                    jQuery(this).addClass('transport-de-bagages') 
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                if (jQuery(this).text() === 'Mobilier de jardin') {
                    jQuery(this).addClass('mobilier-de-jardin')
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                if (jQuery(this).text() === 'Tables') {
                    jQuery(this).addClass('tables')
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                if (jQuery(this).text() === 'Meubles lounge') {
                    jQuery(this).addClass('meubles-lounge')
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                if (jQuery(this).text() === 'Panneaux de fibres min??rales') {
                    jQuery(this).addClass('panneaux-de-fibres-minerales')
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                if (jQuery(this).text() === 'Range-Couverts') {
                    jQuery(this).addClass('range-couverts')
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                if (jQuery(this).text() === 'Plateaux de table') {
                    jQuery(this).addClass('plateaux')
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                if (jQuery(this).text() === 'Mise en place de la table') {
                    jQuery(this).addClass('mise')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Ventilation') {
                    jQuery(this).addClass('ventilation')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Les v??tements') {
                    jQuery(this).addClass('vetements')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Habillement') {
                    jQuery(this).addClass('vetements')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Vitrines r??frig??r??es') {
                    jQuery(this).addClass('vitrines')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Appareils de cuisine') {
                    jQuery(this).addClass('appareils')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Cl. Ustensiles de cuisine') {
                    jQuery(this).addClass('cl')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Petits ??quipements') {
                    jQuery(this).addClass('cl')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Pizza et grill') {
                    jQuery(this).addClass('pizza')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Mobilier en acier inoxydable') {
                    jQuery(this).addClass('mobilier-acier')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Meubles inox') {
                    jQuery(this).addClass('mobilier-acier')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Caf?? et glaces') {
                    jQuery(this).addClass('cafe')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Bo??tes en treillis') {
                    jQuery(this).addClass('boites')
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                if (jQuery(this).text() === 'Refroidissement') {
                    jQuery(this).addClass('refroidissement')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Cadres de table') {
                    jQuery(this).addClass('cadres')
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                if (jQuery(this).text() === 'Bouchon client') {
                    jQuery(this).addClass('bouchon')
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                if (jQuery(this).text() === 'Pots & Casseroles') {
                    jQuery(this).addClass('pots')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === '??lectrom??nagers de cuisine') {
                    jQuery(this).addClass('electro')
                    jQuery(this).parent().parent().addClass('gastrog')
                }

                if (jQuery(this).text() === 'Barri??re') {
                    jQuery(this).addClass('barrirere')
                    jQuery(this).parent().parent().addClass('moebelg')
                }

                 if (jQuery(this).text() === 'Type d???entreprise') {
                    jQuery(this).addClass('type')
                    jQuery(this).parent().parent().addClass('gastrog last-item')
                } 

                if (jQuery(this).text() === 'Accessoires') {
                    jQuery(this).addClass('accessoires')
                    jQuery(this).parent().parent().addClass('moebelg')
                }
            })
        })
       
        if ( jQuery( window).width() < 980 ) {
            jQuery('#nm-mobile-menu-main-ul .gastrog').prependTo('.menu-gastro')
            jQuery('#nm-mobile-menu-main-ul > .moebelg , #nm-mobile-menu-main-ul .transport-de-bagages, #nm-mobile-menu-main-ul .accessoires-m').appendTo('.menu-moebel') ;
            jQuery('.menu-item-591946').remove() 
        }else{
            jQuery('#nm-main-menu-ul .gastrog').appendTo('.menu-gastro')
            jQuery('#nm-main-menu-ul > .moebelg , #nm-main-menu-ul .transport-de-bagages,  #nm-main-menu-ul .accessoires-m').appendTo('.menu-moebel')
           
        } 

        jQuery('.control-menu-gastro').click( function(){
            jQuery('.menu-gastro').slideToggle()
        })

        jQuery('.control-menu-moebel').click( function(){
            jQuery('.menu-moebel').slideToggle()
        })

                
        if ( jQuery(window).width() < 650 ) {
            jQuery('.aws-container').insertBefore('#nm-mobile-menu-main-ul')
            jQuery('.die-Beschreibung').insertBefore('.right-contenedeur')
        }

        jQuery( '#contact-us-button , #custom_html-6' ).on('click', function() {
            
            jQuery('.contact-us-area , #wpcf7-f472026-o1').fadeToggle()

            jQuery(document).mouseup(function(e) {
                    var container = jQuery("#wpcf7-f472026-o1");

                    // if the target of the click isn't the container nor a descendant of the container
                    if (!container.is(e.target) && container.has(e.target).length === 0) 
                    {
                        container.fadeOut();
                        jQuery('.contact-us-area').fadeOut()
                    }
                });
        });

       


        var tabs = [];
        jQuery.each(jQuery('.woocommerce-tabs').find('.wc-tab:first').find('.wc-tab'), function() {
            tabs.push(jQuery(this));
            jQuery(this).remove();
        });

        jQuery.each(tabs, function(k,v) {
            jQuery(v).insertAfter(jQuery('.wc-tab:last'));
        });

    })

    
    
</script>

 
 