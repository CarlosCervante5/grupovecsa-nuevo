<?php
/**
 * WooCommerce Compatibility File
 *
 * @link https://woocommerce.com/
 *
 * @package motormania
 */

/**
 * WooCommerce setup function.
 *
 * @link https://docs.woocommerce.com/document/third-party-custom-theme-compatibility/
 * @link https://github.com/woocommerce/woocommerce/wiki/Enabling-product-gallery-features-(zoom,-swipe,-lightbox)-in-3.0.0
 *
 * @return void
 */
function motormania_woocommerce_setup() {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'motormania_woocommerce_setup' );

/**
 * WooCommerce specific scripts & stylesheets.
 *
 * @return void
 */
function motormania_woocommerce_scripts() {
	wp_enqueue_style( 'motormania-woocommerce-style', get_template_directory_uri() . '/assets/css/woocommerce.css' );
	
	$font_path   = WC()->plugin_url() . '/assets/fonts/';
	$inline_font = '@font-face {
			font-family: "star";
			src: url("' . $font_path . 'star.eot");
			src: url("' . $font_path . 'star.eot?#iefix") format("embedded-opentype"),
				url("' . $font_path . 'star.woff") format("woff"),
				url("' . $font_path . 'star.ttf") format("truetype"),
				url("' . $font_path . 'star.svg#star") format("svg");
			font-weight: normal;
			font-style: normal;
		}';

	wp_add_inline_style( 'motormania-woocommerce-style', $inline_font );
}
add_action( 'wp_enqueue_scripts', 'motormania_woocommerce_scripts' );

/**
 * Disable the default WooCommerce stylesheet.
 *
 * Removing the default WooCommerce stylesheet and enqueing your own will
 * protect you during WooCommerce core updates.
 *
 * @link https://docs.woocommerce.com/document/disable-the-default-stylesheet/
 */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

/**
 * Add 'woocommerce-active' class to the body tag.
 *
 * @param  array $classes CSS classes applied to the body tag.
 * @return array $classes modified to include 'woocommerce-active' class.
 */
function motormania_woocommerce_active_body_class( $classes ) {
	$classes[] = 'woocommerce-active';

	return $classes;
}
add_filter( 'body_class', 'motormania_woocommerce_active_body_class' );

// Products per page.
function motormania_woocommerce_products_per_page() {
	global $motormania_opt; 
	$products_per_page = !empty( $motormania_opt['products_per_page'] ) ? $motormania_opt['products_per_page'] : '';
	return $products_per_page;
}
add_filter( 'loop_shop_per_page', 'motormania_woocommerce_products_per_page' );

// Product gallery thumnbail columns.
function motormania_woocommerce_thumbnail_columns() {
	global $motormania_opt; 
	$products_per_page = !empty( $motormania_opt['products_per_page'] ) ? $motormania_opt['products_per_page'] : '';
	return 4;
}
add_filter( 'woocommerce_product_thumbnails_columns', 'motormania_woocommerce_thumbnail_columns' );


// Default loop columns on product archives.
function motormania_woocommerce_loop_columns() {
	global $motormania_opt;

	if(!empty($_GET['shop_columns'])){
	    $shop_columns = $_GET['shop_columns'];
	} else {
		$shop_columns = !empty( $motormania_opt['shop_columns'] ) ? $motormania_opt['shop_columns'] : 3;
	}

	return $shop_columns;
}
add_filter( 'loop_shop_columns', 'motormania_woocommerce_loop_columns' );

/**
 * Related Products Args.
 *
 * @param array $args related products args.
 * @return array $args related products args.
 */
function motormania_woocommerce_related_products_args( $args ) {
	global $motormania_opt; 
	$related_products_per_page = !empty( $motormania_opt['related_products_per_page'] ) ? $motormania_opt['related_products_per_page'] : 4;
	$related_products_columns = !empty( $motormania_opt['related_products_columns'] ) ? $motormania_opt['related_products_columns'] : 3;
	$defaults = array(
		'posts_per_page' => $related_products_per_page,
		'columns'        => $related_products_columns,
	);

	$args = wp_parse_args( $defaults, $args );

	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'motormania_woocommerce_related_products_args' );


/**
 * Remove "Description" Title @ WooCommerce Single Product Tabs
 */
 
add_filter( 'woocommerce_product_description_heading', '__return_null' );
add_filter( 'woocommerce_product_additional_information_heading', '__return_false' );
add_filter( 'woocommerce_product_reviews_heading', '__return_null' );


// Product Item
function motormania_product_item() { 
	
	global $product;
	global $motormania_opt; 
	$product_title_length = isset( $motormania_opt['product_title_length'] ) ? $motormania_opt['product_title_length'] : 25;
	$product_title_trimmarker = isset( $motormania_opt['product_title_trimmarker'] ) ? $motormania_opt['product_title_trimmarker'] : '...';
	$image_id = isset( $product->get_gallery_image_ids()[0] ) ? $product->get_gallery_image_ids()[0] : '';?>

	<div class="product-item">
	    <div class="product-item-image">
			<a href="<?php the_permalink(); ?>">
				<div class="flip-box">
				  <div class="<?php if ($image_id){ echo'flip-box-inner'; } ?>">
				    <div class="flip-box-front">
					    <?php if ( has_post_thumbnail() ){
							the_post_thumbnail('motormania-400-400');
						} else { ?>
							<img src="<?php echo get_template_directory_uri().'/assets/images/placeholder.png' ?>" alt="<?php the_title_attribute() ?>">
						<?php } ?>
				    </div>
				    <div class="flip-box-back">
						<?php if ($image_id){ ?>
							<img src="<?php echo wp_get_attachment_image_src( $image_id, 'motormania-400-400' )[0]; ?>" alt="<?php the_title_attribute() ?>">
						<?php } ?>
				    </div>
				  </div>
				</div>		
			</a>
	      <?php woocommerce_show_product_loop_sale_flash() ?>
	    </div>
	    <div class="product-item-content">

			<a href="<?php the_permalink(); ?>">
				<h5><?php echo mb_strimwidth( get_the_title(), 0, $product_title_length, $product_title_trimmarker );?></h5>
			</a>

			<ul class="list-inline">
				<li class="list-inline-item">
					<?php woocommerce_template_single_price(); ?>
				</li>
				<li class="list-inline-item float-right"><?php woocommerce_template_loop_rating(); ?></li>
			</ul>
			
			<?php woocommerce_template_loop_add_to_cart() ?>
	    </div>
		<a class="ajax-quick-view-popup" href="#" data-product-id="<?php echo get_the_ID() ?>">
			<i class="fas fa-search-plus"></i>
		</a>
		<?php
		if(function_exists( 'yith_wishlist_install' )) {
			echo do_shortcode( '[yith_wcwl_add_to_wishlist label="" product_added_text="" browse_wishlist_text="" already_in_wishslist_text=""  icon="fa fa-heart"]' );
		} ?>
	</div>
<?php }

add_action( 'get_motormania_product_item', 'motormania_product_item' );

// Product Item Left Image
function motormania_product_item_left_image() { 
	
	global $motormania_opt; 
	$product_title_length = isset( $motormania_opt['product_title_length'] ) ? $motormania_opt['product_title_length'] : 25;
	$product_title_trimmarker = isset( $motormania_opt['product_title_trimmarker'] ) ? $motormania_opt['product_title_trimmarker'] : '...'; ?>

  <div class="product-item style-2">
    <div class="product-item-image">
      <a href="<?php the_permalink(); ?>">
      	<?php if ( has_post_thumbnail() ){
      		the_post_thumbnail('motormania-200-200');
      	} else { ?>
			<img src="<?php echo get_template_directory_uri().'/assets/images/placeholder.png' ?>" alt="<?php the_title_attribute() ?>">
      	<?php } ?>
      </a>
      <?php woocommerce_show_product_loop_sale_flash() ?>
    </div>
    <div class="product-item-content">
    	<a href="<?php the_permalink(); ?>">
			<h5><?php echo mb_strimwidth(get_the_title(), 0, $product_title_length, $product_title_trimmarker); ?></h5>
		</a>
		<div class="d-flex">
			
		<?php woocommerce_template_single_price(); ?>
		<?php woocommerce_template_single_rating(); ?>
		</div>
    </div>
	<a class="ajax-quick-view-popup" href="#" data-product-id="<?php echo get_the_ID() ?>">
		<i class="fas fa-search-plus"></i>
	</a>
  </div>
<?php }

add_action( 'get_motormania_product_item_left_image', 'motormania_product_item_left_image' );

// Product Item Horizontal
function motormania_product_item_horizontal() {
	
	global $motormania_opt; 
	$product_title_length = isset( $motormania_opt['product_title_length'] ) ? $motormania_opt['product_title_length'] : 25;
	$product_title_trimmarker = isset( $motormania_opt['product_title_trimmarker'] ) ? $motormania_opt['product_title_trimmarker'] : '...'; ?>
	
  <div class="product-item style-3">
    <div class="product-item-image my-auto">
      <a href="<?php the_permalink(); ?>">
      	<?php if ( has_post_thumbnail() ){
      		the_post_thumbnail('motormania-400-400');
      	} else { ?>
			<img src="<?php echo get_template_directory_uri().'/assets/images/placeholder.png' ?>" alt="<?php the_title_attribute() ?>">
      	<?php } ?>
      </a>
      <?php woocommerce_show_product_loop_sale_flash() ?>
    </div>
    <div class="product-item-content my-auto">
    	<a href="<?php the_permalink(); ?>">
			<h3><?php echo mb_strimwidth(get_the_title(), 0, $product_title_length, $product_title_trimmarker); ?></h3>
		</a>
		<?php woocommerce_template_single_rating(); ?>
		<p><?php echo wp_trim_words( get_the_content(), 25, '...' );?></p>
		<div class="d-flex">
			<?php woocommerce_template_loop_add_to_cart() ?>
			<?php woocommerce_template_single_price(); ?>
		</div>
    </div>
	<a class="ajax-quick-view-popup" href="#" data-product-id="<?php echo get_the_ID() ?>">
		<i class="fas fa-search-plus"></i>
	</a>
  </div>
<?php }

add_action( 'get_motormania_product_item_horizontal', 'motormania_product_item_horizontal' );

// AJAX cart content update
function motormania_subtotal_count( $fragments ) {
 
    ob_start(); ?>
    <h5 class="subtotal">
    	<?php echo WC()->cart->get_cart_subtotal(); ?>
	</h5>
    <?php
 
    $fragments['.subtotal'] = ob_get_clean();
     
    return $fragments;
}

add_filter( 'woocommerce_add_to_cart_fragments', 'motormania_subtotal_count' );

// All product category ids
function motormania_get_product_category_ids(){
  $all_categories = get_terms( array('taxonomy' => 'product_cat','hide_empty' => false, 'parent' => 0));
  if (is_wp_error($all_categories)) {
      return [];
  }
  foreach ($all_categories as $key => $term) {
      $options[$key] = $term->term_id;
  }
  return $options;
}