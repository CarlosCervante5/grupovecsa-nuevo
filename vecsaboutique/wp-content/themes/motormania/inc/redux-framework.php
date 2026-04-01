<?php

    if ( ! class_exists( 'Redux' ) ) {
        return;
    }

    $opt_name = "motormania_opt";
    $theme = wp_get_theme();

    $args = array(
        'opt_name'             => $opt_name,
        'display_name'         => $theme->get( 'Name' ),
        'display_version'      => $theme->get( 'Version' ),
        'menu_type'            => 'menu',
        'allow_sub_menu'       => true,
        'menu_title'           => esc_html__( 'MotorMania Opt', 'motormania' ),
        'page_title'           => esc_html__( 'MotorMania Opt', 'motormania' ),
        'google_api_key'       => '',
        'google_update_weekly' => false,
        'async_typography'     => false,
        'admin_bar'            => true,
        'admin_bar_icon'       => 'dashicons-portfolio',
        'admin_bar_priority'   => 50,
        'global_variable'      => '',
        'dev_mode'             => false,
        'update_notice'        => true,
        'customizer'           => true,
        'page_priority'        => null,
        'page_parent'          => 'themes.php',
        'page_permissions'     => 'manage_options',
        'menu_icon'            => '',
        'last_tab'             => '',
        'page_icon'            => 'icon-themes',
        'page_slug'            => '_options',
        'save_defaults'        => true,
        'default_show'         => false,
        'default_mark'         => '',
        'show_import_export'   => true,
        'transient_time'       => 60 * MINUTE_IN_SECONDS,
        'output'               => true,
        'output_tag'           => true,
        'database'             => '',
        'use_cdn'              => true,

        // HINTS
        'hints'                => array(
            'icon'          => 'el el-question-sign',
            'icon_position' => 'right',
            'icon_color'    => 'lightgray',
            'icon_size'     => 'normal',
            'tip_style'     => array(
                'color'   => 'light',
                'shadow'  => true,
                'rounded' => false,
                'style'   => '',
            ),
            'tip_position'  => array(
                'my' => 'top left',
                'at' => 'bottom right',
            ),
            'tip_effect'    => array(
                'show' => array(
                    'effect'   => 'slide',
                    'duration' => '500',
                    'event'    => 'mouseover',
                ),
                'hide' => array(
                    'effect'   => 'slide',
                    'duration' => '500',
                    'event'    => 'click mouseleave',
                ),
            ),
        )
    );

    Redux::setArgs( $opt_name, $args );
    

    $elementor_library = new WP_Query( array( 
        'post_type' => 'elementor_library'
    ));

    $template_lists = [];

    /* Start the Loop */
    if ( $elementor_library->have_posts()) {
        while ( $elementor_library->have_posts()) : $elementor_library->the_post();
            $template_lists[ get_the_ID() ] = get_the_title();
        endwhile;
    }


    // General
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'General', 'motormania' ),
        'id'     => 'general',
        'desc'   => esc_html__( 'General theme options.', 'motormania' ),
        'icon'   => 'el el-home',
        'fields' => array(
            array(
                'id'       => 'site_preloader',
                'type'     => 'switch',
                'title'    => esc_html__( 'Preloader', 'motormania' ),
                'default'  => true,
            )
        )
    ));

    // Style
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Style', 'motormania' ),
        'id'     => 'style',
        'desc'   => esc_html__( 'Header menu options.', 'motormania' ),
        'icon'   => 'el el-edit',
        'fields' => array(
            array(
                'id'       => 'primary_color',
                'type'     => 'color_gradient',
                'title'    => esc_html__('Primary Color', 'motormania'), 
                'subtitle' => esc_html__('Pick a color for the theme (default: #e52727 and #e52727).', 'motormania'),
                'validate' => 'color',
                'default'  => array(
                    'from' => '#e52727',
                    'to'   => '#e52727',
                ),

            ),  
        )
    ));

    // Typography
    Redux::setSection( $opt_name, array(
        'title'            => esc_html__( 'Typography', 'motormania' ),
        'id'               => 'page_title_typography',  
        'icon'   => 'el el-pencil',
        'fields'           => array(
            array(
                'id'          => 'motormania_heading_typography',
                'type'        => 'typography',
                'title'       => esc_html__( 'Heading Typography', 'motormania' ),
                'subtitle'    => esc_html__('H1, H2, H3,H4, H5, H6  Tags', 'motormania'),
                'google'      => true, 
                'font-backup' => true,
                'output'      => array('h1,h2,h3,h4,h5,h6'),
                'units'       =>'px',
                'default'     => array(
                    'color'       => '#333',
                    'font-weight' => '500', 
                    'font-family' => 'Oswald', 
                    'google'      => true,
                ),
            ),
            array(
                'id'          => 'motormania_typography',
                'type'        => 'typography',
                'title'       => esc_html__( 'Typography', 'motormania' ),
                'subtitle'    => esc_html__('body, p Tags', 'motormania'),
                'google'      => true, 
                'font-backup' => true,
                'output'      => array('body,p'),
                'units'       =>'px',
                'default'     => array(
                    'color'       => '#808080', 
                    'font-weight'  => 'normal', 
                    'line-height' => '26px',
                    'font-family' => 'Rubik', 
                    'google'      => true,
                    'font-size'   => '16px',
                ),
            )
        )
    ) );

    // Header
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Header', 'motormania' ),
        'id'     => 'header',
        'desc'   => esc_html__( 'Header menu options.', 'motormania' ),
        'icon'   => 'el el-heart-empty',
        'fields' => array(
            array(
                'id'       => 'header_style',
                'type'     => 'select',
                'title'    => esc_html__( 'Header Style', 'motormania' ),
                'options'  => array(
                    'style1' => esc_html__( 'Style one','motormania' ), 
                    'style2' => esc_html__( 'Style two','motormania' )
                ),
                'default'  => 'style2',
            ),
            array(
                'id'       => 'motormania_top_header',
                'type'     => 'switch',
                'title'    => esc_html__( 'Top Header', 'motormania' ),
                'default'  => true,
                'required' => array( 'header_style','equals', 'style2' )
            ),
            array(
                'id'       => 'motormania_header_full_width',
                'type'     => 'switch',
                'title'    => esc_html__( 'Full Width Header', 'motormania' ),
                'subtitle' => esc_html__( 'Controls the width of the header area. ', 'motormania' ),
                'default'  => false
            ),
            array(
                'id'       => 'motormania_header_sticky',
                'type'     => 'switch',
                'title'    => esc_html__( 'Sticky Header', 'motormania' ),
                'subtitle' => esc_html__( 'Turn on to activate the sticky header.', 'motormania' ),
                'default'  => false
            ),
            array(
                'id'       => 'motormania_navbar_button',
                'type'     => 'switch',
                'title'    => esc_html__( 'Navbar button', 'motormania' ),
                'default'  => true,
                'required' => array( 'header_style','equals', 'style2' )
            ),
            array(         
                'id'       => 'logo_width',
                'type'     => 'dimensions',                
                'height'   => false,                
                'units'    => array('em','px','%'),
                'output'   => array('.custom-logo-link img'),
                'title'    => esc_html__('Logo Dimensions', 'motormania'),
            ),
            array(
                'id'       => 'motormania_navbar_button_text',
                'type'     => 'text',
                'title'    => esc_html__( 'Navbar button text', 'motormania' ),
                'default'  => esc_html__( 'Buy This theme', 'motormania' ),
                'required' => array('motormania_navbar_button','equals', true)

            ),
            array(
                'id'       => 'motormania_navbar_button_url',
                'type'     => 'text',
                'title'    => esc_html__('Navbar button URL', 'motormania'),
                'default'   => '#',
                'required' => array('motormania_navbar_button','equals', true)
            ),
            array(         
                'id'       => 'breadcrumbs',
                'type'     => 'background',
                'output'     => array('.breadcrumbs'),
                'title'    => esc_html__('Breadcrumbs', 'motormania'),
                'subtitle' => esc_html__('Set breadcrumbs background with image, color', 'motormania'),
                'default'  => array(
                    'background-color' => '#000',
                )
            )
        )
    ) );

    // Blog Page
    Redux::setSection( $opt_name, array(
        'title' => esc_html__( 'Blog Page', 'motormania' ),
        'id'    => 'blog_page',
        'icon'  => 'el el-wordpress',
        'fields'     => array(         
            array(
                'id'       => 'blog_breadcrumb_title',
                'type'     => 'text',
                'title'    => esc_html__( 'Breadcrumb Title', 'motormania' ),
                'default'  => esc_html__( 'Latest Blog', 'motormania' ),
            ),   
            array(
                'id'               => 'motormania_excerpt_length',
                'type'             => 'slider',
                'title'            => esc_html__('Excerpt Length', 'motormania'),
                'subtitle'         => esc_html__('Controls the excerpt length on blog page','motormania'),
                "default"          => 55,
                "min"              => 10,
                "step"             => 2,
                "max"              => 130,
                'display_value'    => 'text'
            )
            
        )
    ) );

    // Single Blog
    Redux::setSection( $opt_name, array(
        'title' => esc_html__( 'Single Blog Page', 'motormania' ),
        'id'    => 'single_blog_page',
        'icon'  => 'el el-wordpress',
        'subsection' => true,
        'fields'     => array(              
            array(
                'id'       => 'social_share',
                'type'     => 'switch',
                'title'    => esc_html__( 'Social Share', 'motormania' ),
                'default'  => true,
            ),
            array(
                'id'       => 'motormania_blog_details_post_navigation',
                'type'     => 'switch',
                'title'    => esc_html__( 'Post Navigation (Next/Previous)', 'motormania' ),
                'default'  => true,
            ),
            array(
                'id'       => 'related_posts',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show Related Post', 'motormania' ),
                'default'  => true,
            ),
            array(
                'id'       => 'related_post_title',
                'type'     => 'text',
                'title'    => esc_html__( 'Related Post Title', 'motormania' ),
                'required' => array( 'related_posts','equals', true ),
                'default'  => esc_html__( 'Related Post', 'motormania' ),
            ),
            array(
                'id' => 'posts_per_page',
                'type' => 'slider',
                'title' => esc_html__( 'Related Posts', 'motormania' ),
                'subtitle' => esc_html__( 'Related posts per page', 'motormania' ),
                'desc' => esc_html__('Number of related posts to display. Min: 1, max: Unlimited, step: 1, default value: 4', 'motormania'),
                "default" => 3,
                "min" => 1,
                "step" => 1,
                "max" => 10000,
                'required' => array( 'related_posts','equals', true ),
                'display_value' => 'text'
            ),
            array(
                'id'       => 'related_posts_columns',
                'type'     => 'select',
                'title'    => esc_html__( 'Posts Column', 'motormania' ), 
                'subtitle' => esc_html__( 'Number of column', 'motormania' ),
                'desc'     => esc_html__( 'Specify the number of related posts column.', 'motormania' ),
                'required' => array( 'related_posts','equals', true ),
                'options'  => array(
                    '12' => esc_html__( 'One Column','motormania' ), 
                     '6' => esc_html__( 'Two Columns','motormania' ), 
                     '4' => esc_html__( 'Three Columns','motormania' ), 
                     '3' => esc_html__( 'Four Columns','motormania' ), 
                     '2' => esc_html__( 'Six Columns','motormania' ),
                ),
                'default'  => '4',
            )
        )
    ) );


    // WooCommerce
    Redux::setSection( $opt_name, array(
        'title' => esc_html__( 'WooCommerce', 'motormania' ),
        'id'    => 'woocommerce',
        'icon'  => 'el el-shopping-cart',
        'fields'     => array(
            array(
                'id' => 'products_per_page',
                'type' => 'slider',
                'title' => esc_html__( 'Products Per Page', 'motormania' ),
                'subtitle' => esc_html__( 'Product per page', 'motormania' ),
                'desc' => esc_html__('Number of products to display. Min: 1, max: Unlimited, step: 1, default value: 4', 'motormania'),
                "default" => 9,
                "min" => 1,
                "step" => 1,
                "max" => 10000,
                'display_value' => 'text'
            ),
            array(
                'id'       => 'product_style',
                'type'     => 'select',
                'title'    => esc_html__( 'Product Style', 'motormania' ),
                'options'  => array(
                    'grid' => esc_html__( 'Grid','motormania' ), 
                    'list' => esc_html__( 'List','motormania' )
                ),
                'default'  => 'grid',
            ),
            array(
                'id'       => 'shop_layout',
                'type'     => 'select',
                'title'    => esc_html__( 'Store layout', 'motormania' ),
                'desc'     => esc_html__( 'Specify the number of related products column.', 'motormania' ),
                'options'  => array(
                    'full_width' => esc_html__( 'Full width','motormania' ), 
                    'left_sidebar' => esc_html__( 'Left sidebar','motormania' ), 
                    'right_sidebar' => esc_html__( 'Right sidebar','motormania' )
                ),
                'default'  => 'full_width',
            ),
            array(
                'id'       => 'shop_columns',
                'type'     => 'select',
                'title'    => esc_html__( 'Products Column', 'motormania' ), 
                'subtitle' => esc_html__( 'Number of column', 'motormania' ),
                'desc'     => esc_html__( 'Specify the number of related products column.', 'motormania' ),
                'required' => array( 'product_style','equals', 'grid' ),
                'options'  => array(
                    '12' => esc_html__( 'One Column','motormania' ), 
                     '6' => esc_html__( 'Two Columns','motormania' ), 
                     '4' => esc_html__( 'Three Columns','motormania' ), 
                     '3' => esc_html__( 'Four Columns','motormania' ), 
                     '2' => esc_html__( 'Six Columns','motormania' ),
                ),
                'default'  => '3',
            ),
            array(
                'id'       => 'product_title_length',
                'type'     => 'slider',
                'title'    => esc_html__( 'Product title length', 'motormania' ),
                "default" => 25,
                "min" => 1,
                "step" => 1,
                "max" => 100
            ),
            array(
                'id'       => 'product_title_trimmarker',
                'type'     => 'text',
                'title'    => esc_html__( 'Product title trimmarker', 'motormania' ),
                'desc'    => esc_html__( 'End character of the title', 'motormania' ),
                "default" => '...'
            )
        )
    ) );

    // WooCommerce Single
    Redux::setSection( $opt_name, array(
        'title' => esc_html__( 'WooCommerce Single', 'motormania' ),
        'id'    => 'woocommerce_single',
        'icon'  => 'el el-shopping-cart',
        'subsection' => true,
        'fields'     => array(
            array(
                'id'       => 'woocommerce_social_share',
                'type'     => 'switch',
                'title'    => esc_html__( 'Social Share', 'motormania' ),
                'default'  => true,
            ),
            array(
                'id'       => 'related_products',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show Related Products', 'motormania' ),
                'default'  => true,
            ),
            array(
                'id'       => 'related_products_title',
                'type'     => 'text',
                'title'    => esc_html__( 'Related Product Title', 'motormania' ),
                'required' => array( 'related_products','equals', true ),
                'default'  => esc_html__( 'Related products', 'motormania' ),
            ),
            array(
                'id' => 'related_products_per_page',
                'type' => 'slider',
                'title' => esc_html__( 'Related Products', 'motormania' ),
                'subtitle' => esc_html__( 'Related product per page', 'motormania' ),
                'desc' => esc_html__('Number of related products to display. Min: 1, max: Unlimited, step: 1, default value: 4', 'motormania'),
                "default" => 4,
                "min" => 1,
                "step" => 1,
                "max" => 12,
                'required' => array( 'related_products','equals', true ),
                'display_value' => 'text'
            ),
            array(
                'id'       => 'related_products_columns',
                'type'     => 'select',
                'title'    => esc_html__( 'Products Column', 'motormania' ), 
                'subtitle' => esc_html__( 'Number of column', 'motormania' ),
                'desc'     => esc_html__( 'Specify the number of related products column.', 'motormania' ),
                'required' => array( 'related_products','equals', true ),
                'options'  => array(
                    '12' => esc_html__( 'One Column','motormania' ), 
                     '6' => esc_html__( 'Two Columns','motormania' ), 
                     '4' => esc_html__( 'Three Columns','motormania' ), 
                     '3' => esc_html__( 'Four Columns','motormania' ), 
                     '2' => esc_html__( 'Six Columns','motormania' ),
                ),
                'default'  => '3',
            )
        )
    ) );

    // Newsletter Modal
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Newsletter Modal', 'motormania' ),
        'id'     => 'newsletter_modal',
        'icon'   => 'el el-envelope',
        'fields' => array(
            array(
                'id'          => 'newsletter_modal_switch',
                'type'        => 'switch',
                'title'       => esc_html__( 'Newsletter Modal', 'motormania' ),
                'default'  => true,
            ),
            array(
                'id'          => 'modal_image',
                'type'        => 'media',
                'title'       => esc_html__( 'Modal image', 'motormania' ),
                'default'  => '#',
                'required' => array( 'newsletter_modal_switch','equals', true ),
            ),
            array(
                'id'          => 'modal_title',
                'type'        => 'text',
                'title'       => esc_html__( 'Modal title', 'motormania' ),
                'default'     => esc_html__( 'Subscribe And Get 30% Discount!', 'motormania' ),
                'required'    => array( 'newsletter_modal_switch','equals', true ),
            ),
            array(
                'id'          => 'modal_description',
                'type'        => 'textarea',
                'title'       => esc_html__( 'Modal description', 'motormania' ),
                'default'     => esc_html__( 'Subscribe to our newsletter to get updates and big discount offer!.', 'motormania' ),
                'required'    => array( 'newsletter_modal_switch','equals', true ),
            ),
            array(
                'id'          => 'modal_shortcode',
                'type'        => 'text',
                'title'       => esc_html__( 'Modal shortcode', 'motormania' ),
                'default'  => '[mc4wp_form id="302"]',
                'required' => array( 'newsletter_modal_switch','equals', true ),
            ),
            array(
                'id'          => 'modal_timeout',
                'type'        => 'text',
                'title'       => esc_html__( 'Modal timeout', 'motormania' ),
                'default'  => 5000,
                'required' => array( 'newsletter_modal_switch','equals', true ),
            )
        )
    ) );


    // Footer
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Footer', 'motormania' ),
        'id'     => 'footer',
        'icon'   => 'el el-arrow-down',
        'fields' => array(
            array(
                'id'          => 'footer_widget_display',
                'type'        => 'switch',
                'title'       => esc_html__( 'Footer widget display', 'motormania' ),
                'default'  => true,
            ),
            array(
                'id'         => 'motormania_footer_template',
                'type'       => 'select',
                'title'      => esc_html__( 'Footer template', 'motormania' ), 
                'required'   => array( 'footer_widget_display','equals', true ),
                'desc'       => esc_html__('To set a footer template click ', 'motormania').' <a href="edit.php?post_type=elementor_library&tabs_group=library&elementor_library_type=page">'.esc_html__('Here', 'motormania').'</a>',
                'options'  => $template_lists
            ),
            array(
                'id'          => 'backtotop',
                'type'        => 'switch',
                'title'       => esc_html__( 'Back to top', 'motormania' ),
                'default'  => true,
            ),
            array(
                'id'              => 'motormania_copyright_info',
                'type'            => 'editor',
                'title'           => esc_html__( 'Copyright text', 'motormania' ),
                'subtitle'        => esc_html__( 'Enter your company information here. HTML tags allowed: a, br, em, strong', 'motormania' ),
                'default'         => esc_html__( 'Copyright © 2021 motormania All Rights Reserved.', 'motormania' ),
                'args'            => array(
                'wpautop'         => false,
                'teeny'           => true,
                'textarea_rows'   => 5
                )
            ),
            array(
                'id'          => 'supported_currency',
                'type'        => 'slides',
                'title'       => esc_html__('Supported currency', 'motormania'),
                'subtitle'    => esc_html__('Unlimited currency with drag and drop sortings.', 'motormania')
            )
        )
    ) );

    // 404 
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( '404 Error', 'motormania' ),
        'id'     => 'error-page',
        'icon'   => 'el el-error-alt',
        'fields' => array(
            array(
                'id'          => 'motormania_error_title',
                'type'        => 'text',
                'title'       => esc_html__( 'Error title', 'motormania' ),
                'default'     => esc_html__( 'Oops! That page can’t be found.', 'motormania' ),
                ),
            array(
                'id'          => 'motormania_error_text',
                'type'        => 'textarea',
                'title'       => esc_html__('Error message', 'motormania'),
                'subtitle'    => esc_html__('Enter "not found" error message.', 'motormania'),
                'default'     => esc_html__('It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'motormania'),
                )
            ),
    ) );