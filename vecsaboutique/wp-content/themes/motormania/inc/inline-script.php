<?php 
//inline style
function motormania_inline_style() {
    ob_start();
    global $motormania_opt;

    $primary_color_from = !empty($motormania_opt['primary_color']['from']) ? $motormania_opt['primary_color']['from'] : '#e52727';
    $primary_color_to = !empty($motormania_opt['primary_color']['to']) ? $motormania_opt['primary_color']['to'] : '#e52727'; ?>
	
	.my-account-widget i, .shopping-cart-widget i,.countdown-date span,.preview-btn li a:hover,#backtotop i,.blog-thumb .date,.slick-arrow,.product-item-content .add_to_cart_button:hover,.product-item-content .added_to_cart:hover,.comment-navigation .nav-links a,.header-btn a,.select-items div:hover,.same-as-selected,.mean-container .mean-nav ul li a.mean-expand:hover,button,input[type="button"],.widget_price_filter .ui-slider .ui-slider-range,.widget_price_filter .ui-slider .ui-slider-handle,input[type="reset"],.off-canvas-menu .navigation li>a:hover,.off-canvas-menu .navigation .dropdown-btn:hover,.off-canvas-menu .navigation li .cart-contents,input[type="submit"],.motormania-search-btn,.video-item .view-detail,.widget-product-details .widget-add-to-cart .variations .value .variation-radios [type="radio"]:checked+label:after,.single-product .product_meta .tagged_as a:hover,.single-product .product_meta .posted_in a:hover,.widget-product-details .widget-add-to-cart .variations .value .variation-radios [type="radio"]:not(:checked)+label:after,.widget_shopping_cart_content .button,.banner2 .banner-cat .cat-count,ul.banner-button li:first-child a,ul.banner-button li a:hover,.motormania-pricing-table.recommended,.motormania-pricing-table a:hover,.wedocs-single-wrap .wedocs-sidebar ul.doc-nav-list>li.current_page_parent>a,.wedocs-single-wrap .wedocs-sidebar ul.doc-nav-list>li.current_page_item>a,.wedocs-single-wrap .wedocs-sidebar ul.doc-nav-list>li.current_page_ancestor>a,.primary-menu ul li .children li.current-menu-item>a,.primary-menu ul li .sub-menu li.current-menu-item>a,.header-btn .sub-menu li.is-active a,.product-item-button a:hover,.recent-themes-widget,.newest-filter ul li.select-cat,.download-filter ul li.select-cat,.woocommerce .onsale,input[type="button"],input[type="reset"],input[type="submit"],.checkout-button,.woocommerce-tabs ul.tabs li.active a:after,.tagcloud a:hover,.motormania-btn,.motormania-btn.bordered:hover,.widget-woocommerce .single_add_to_cart_button,.post-navigation .nav-previous a,.post-navigation .nav-next a,.blog-btn .btn:hover,.mean-container .mean-nav,.recent-theme-item .permalink,.banner-item-btn a,.meta-attributes li span a:hover,.theme-item-price span,.error-404 a,.mini-cart .widget_shopping_cart .woocommerce-mini-cart__buttons a,.product-item-image .onsale,.theme-item-btn a:hover,.theme-banner-btn a,.comment-list .comment-reply-link,.comment-form input[type=submit],.pagination .nav-links .page-numbers.current,.pagination .nav-links .page-numbers:hover,.excerpt-date,.woocommerce-account .woocommerce-MyAccount-navigation li.is-active,.primary-menu ul li .children li a:hover,.primary-menu ul li .sub-menu li a:hover,.header-btn .sub-menu li a:hover,a.product_type_variable,a.product_type_simple,a.product_type_external,a.product_type_grouped,a.add_to_cart_button,a.added_to_cart,.tags>a:hover,.single-post .post-share ul li a:hover,.playerContainer .seekBar .outer .inner,.playerContainer .volumeControl .outer .inner,.excerpt-readmore a {
		background: <?php echo esc_attr( $primary_color_from ) ?>;
		background: -webkit-linear-gradient(to right, <?php echo esc_attr( $primary_color_from ) ?>, <?php echo esc_attr( $primary_color_to ) ?>);
		background: linear-gradient(to right, <?php echo esc_attr( $primary_color_from ) ?>, <?php echo esc_attr( $primary_color_to ) ?>);
	}

	.testimonials i.fa-quote-right,.banner.style-2 .banner-content h1 span,.client-info h4,.product-filter ul li a.active,a,a:hover,.current_page_item a,.tags a:hover,blockquote:before,.cart_item .product-name a:hover,.widget_recent_comments ul li .comment-author-link a,.mini-cart .cart-contents:hover span,ul.banner-button li a,.testimonial-content>i,.motormania-btn.bordered,.primary-menu ul li.current-menu-item>a,.cat-links a,.plyr--full-ui input[type=range],.motormania-team-social li a,.preview-btn li a,.related-post-title a:hover,.comment-author-link,.entry-meta ul li a:hover,.widget-product-details table td span a:hover,.woocommerce-message a,.woocommerce-info a,.iconbox-item i,.footer-widget ul li a:hover,.woocommerce-noreviews a,.widget li a:hover,p.no-comments a,.woocommerce-notices-wrapper a,.woocommerce table td a,.blog-meta span,.blog-content h4:hover a,.tags-links a,.tags a,.navbar-logo-text,.docs-single h4 a:hover,.docs-single ul li a:hover,.navbar .menu-item>.active,blockquote::before,.woocommerce-tabs ul.tabs li.active a,.woocommerce-tabs ul.tabs li a:hover,.primary-menu ul li>a:hover,.tags a,a.button,.the_excerpt .entry-title a:hover {
		color: <?php echo esc_attr( $primary_color_from ) ?>;
	}

	
	.category-item h5,.uil-ripple-css div,.testimonial-img img,.product-item.style-3:hover,.woocommerce-info,.motormania-btn.bordered,ul.banner-button li a,.preview-btn li a,.woocommerce-MyAccount-navigation,.woocommerce-info,.motormania-pricing-table a,.woocommerce-MyAccount-navigation .is-active a,blockquote,.loader,.related-themes .single-related-theme:hover,.theme-author span,.tags a,.playerContainer,.sticky .the_excerpt_content {
		border-color: <?php echo esc_attr( $primary_color_from ) ?>!important;
	}

	
	.navbar-toggler-icon {
	  background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 32 32' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='<?php echo esc_attr( $primary_color_from ) ?>' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 8h24M4 16h24M4 24h24'/%3E%3C/svg%3E");
	}

	/*----------------------------------------
	IF SCREEN SIZE LESS THAN 769px WIDE
	------------------------------------------*/

	@media screen and (max-width: 768px) {
		.navbar .menu-item>.active {
	 		background: <?php echo esc_attr( $primary_color_from ) ?>;
		}
	}
<?php
return ob_get_clean();
}