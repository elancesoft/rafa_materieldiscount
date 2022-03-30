<?php
    global $nm_globals, $nm_theme_options;
    
    // Header classes
    $header_classes = nm_header_get_classes();
?>
<div id="nm-header-placeholder" class="nm-header-placeholder"></div>

<header id="nm-header" class="nm-header <?php echo esc_attr( $header_classes ); ?> clear">
        <div class="nm-header-inner">
        <?php
            // Include header layout
            if ( $nm_theme_options['header_layout'] == 'centered' ) {
                get_template_part( 'template-parts/header/header', 'layout-centered' );
            } else {
                get_template_part( 'template-parts/header/header', 'layout' );
            }
        ?>
    </div>
</header>
<nav class="nm-main-menu"> 
            <ul id="nm-main-menu-ul" class="nm-menu">
                 <li class="close-submenu">X</li> 
                <?php
                    wp_nav_menu( array(
                        'theme_location'    => 'main-menu',
                        'container'         => false,
                        'fallback_cb'       => false,
                        'walker'            => new NM_Sublevel_Walker,
                        'items_wrap'        => '%3$s'
                    ) );
                ?>
            </ul>
</nav>

<?php
    // Shop search
    //if ( $nm_globals['shop_search_header'] ) {
      //  get_template_part( 'template-parts/woocommerce/searchform' );
    //}
?>