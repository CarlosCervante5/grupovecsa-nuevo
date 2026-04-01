<?php 
global $motormania_opt;

$motormania_top_header = !empty( $motormania_opt['motormania_top_header'] ) ? $motormania_opt['motormania_top_header'] : false;
$motormania_header_sticky = !empty( $motormania_opt['motormania_header_sticky'] ) ? $motormania_opt['motormania_header_sticky'] : '';
$motormania_header_full_width = !empty( $motormania_opt['motormania_header_full_width'] ) ? $motormania_opt['motormania_header_full_width'] : '';
$motormania_navbar_button =  !empty( $motormania_opt['motormania_navbar_button'] ) ? $motormania_opt['motormania_navbar_button'] : '';
$motormania_navbar_button_text =  !empty( $motormania_opt['motormania_navbar_button_text'] ) ? $motormania_opt['motormania_navbar_button_text'] : '';
$motormania_navbar_button_url =  !empty( $motormania_opt['motormania_navbar_button_url'] ) ? $motormania_opt['motormania_navbar_button_url'] : '';
?>
<header>
    <?php if ( $motormania_top_header == true ): ?>
    <div class="top-header"> 
        <div class="container<?php if( $motormania_header_full_width == true ){ echo'-fluid'; } ?>">
            <div class="row justify-content-center">
                <div class="col-xl-3 col-lg-2 my-auto d-none d-lg-block">
                    <div class="logo">
                    <?php if (has_custom_logo()) {
                        the_custom_logo();
                    } else { ?>
                        <a class="navbar-logo-text" rel="home" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
                    <?php } ?>
                    </div>
                </div>
                <div class="<?php if ( class_exists( 'WooCommerce' ) ){ echo 'col-xl-5 col-lg-4'; } else { echo'col-xl-7 col-lg-7'; } ?> my-auto d-none d-lg-block">
                    <form class="ajax-search-form" action="<?php echo esc_url(home_url( '/' )); ?>">
                        <input type="text" name="s" id="keyword" placeholder="<?php echo esc_attr__( 'Search', 'motormania' ); ?>">
                        <button type="submit"><i class="fal fa-search"></i></button>
                        <input type="hidden" name="post_type" value="product" />
                        <div id="datafetch"></div>
                    </form>
                </div>
                <div class="col-xl-2 col-lg-3 col my-auto">
                    <div class="top-header-action">
                        <div class="widget-header">
                            <div class="my-account-widget">
                                <i class="fal fa-fw fa-user"></i>
                                <div class="my-account-button">
                                    <small>
                                        <?php if (is_user_logged_in()) { echo wp_get_current_user()->display_name; }else{ echo esc_html__( 'Login here','motormania' ); } ?>
                                    </small>
                                    <h5><?php echo esc_html__( 'My Account','motormania' ) ?></h5>
                                </div>
                                <div class="my-account-content">
                                    <?php if (is_user_logged_in()) { ?>

                                        <div class="header-profile">                                            
                                            <?php if ( is_user_logged_in() ) { 
                                                $current_user = wp_get_current_user();
                                                if ( ($current_user instanceof WP_User) ) { 
                                                    echo get_avatar( $current_user->ID, 60 ); 
                                                }
                                            } else { ?>
                                                <img src="<?php echo get_template_directory_uri() ?>/assets/images/user.png" alt="<?php the_title_attribute() ?>">
                                            <?php } ?>
                                            <div class="header-profile-content">
                                                <h6><?php echo wp_get_current_user()->display_name; ?></h6>
                                                <p><?php echo wp_get_current_user()->user_email ?></p>
                                            </div>
                                        </div>

                                        <?php if ( class_exists( 'WooCommerce' ) ) { ?>
                                            <ul class="list-unstyled">
                                                <?php 
                                                foreach ( wc_get_account_menu_items() as $endpoint => $label ) { ?>
                                                    <li class="<?php echo wc_get_account_menu_item_classes( $endpoint ); ?>">
                                                        <a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"><?php echo esc_html( $label ); ?></a>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        <?php } ?>

                                    <?php } else { ?>

                                        <div class="header-profile-login">
                                            <h6 class="text-center"><?php echo esc_html__( 'Log In to Your Account', 'motormania' ) ?></h6>
                                            <?php wp_login_form(); ?>
                                            <a href="<?php echo esc_url( wp_registration_url() ); ?>"><?php esc_html_e( 'Register', 'motormania' ); ?></a>
                                            <span class="mr-2 ml-2">|</span>
                                            <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
                                                <?php esc_html_e( 'Lost Password', 'motormania' ); ?>
                                            </a>
                                        </div>


                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ( class_exists( 'WooCommerce' ) ){ ?>
                <div class="col-xl-2 col-lg-3 col my-auto">
                    <div class="top-header-action">
                        <div class="widget-header">
                            <div class="shopping-cart-widget">
                                <i class="fal fa-fw fa-cart-plus"></i>
                                <div class="shopping-cart-button">
                                    <small><?php echo esc_html__( 'Shopping Cart', 'motormania' ) ?></small>
                                    <h5 class="subtotal">
                                        <?php echo WC()->cart->get_cart_subtotal() ?>
                                    </h5>
                                </div>
                                <div class="widget_shopping_cart_content"><?php wc_get_template('cart/mini-cart.php'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php endif ?>

    <div class="site-header <?php if( true == $motormania_header_sticky ){ echo'sticky-header'; } ?>">
        <div class="container<?php if( $motormania_header_full_width == true ){ echo'-fluid'; } ?>">
            <div class="row justify-content-center">
                <div class="col-xl-10 col-md-10 my-auto">
                    <div class="primary-menu d-none d-lg-inline-block">
                        <nav class="desktop-menu">
                            <?php
                                wp_nav_menu( array(
                                'theme_location'    => 'primary',
                                'depth'             => 3,
                                'container'         => 'ul',
                            ) ); ?>
                        </nav>                      
                    </div>
                </div>
                <?php 
                if ( true == $motormania_navbar_button ) { ?>
                <div class="col-xl-2 col-md-2 p-0 my-auto">
                    <div class="header-btn d-none d-lg-block">
                        <a href="<?php echo esc_url( $motormania_navbar_button_url ) ?>">
                            <?php echo esc_html( $motormania_navbar_button_text ) ?>
                        </a>
                    </div>
                </div>
                <?php } ?>           
            </div>
        </div>
    </div>
</header><!-- #masthead -->

<!--Mobile Navigation Toggler-->
<div class="off-canvas-menu-bar">
    <div class="container">
        <div class="row">
            <div class="col-6 my-auto">
            <?php if (has_custom_logo()) {
                the_custom_logo();
            } else { ?>
                <a class="navbar-logo-text" rel="home" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
            <?php } ?>
            </div>
            <div class="col-6">
                <div class="mobile-nav-toggler"><span class="fal fa-bars"></span></div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Menu  -->
<div class="off-canvas-menu">
    <div class="menu-backdrop"></div>
    <i class="close-btn fa fa-close"></i>
    <nav class="mobile-nav">
        <div class="text-center pt-3 pb-3">
        <?php if (has_custom_logo()) {
            the_custom_logo();
        } else { ?>
            <a class="navbar-logo-text" rel="home" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
        <?php } ?>
        </div>
        
        <ul class="navigation"><!--Keep This Empty / Menu will come through Javascript--></ul>
    </nav>
</div>