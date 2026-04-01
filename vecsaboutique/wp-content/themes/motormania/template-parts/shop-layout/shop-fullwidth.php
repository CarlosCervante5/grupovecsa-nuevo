<?php

global $motormania_opt;
$product_style = isset($motormania_opt['product_style']) ? $motormania_opt['product_style'] : '';

if(!empty($_GET['product_style'])){
    $product_style = $_GET['product_style'];
}

if ( woocommerce_product_loop() ) { ?>
	<div class="products-filter-area">
		<div class="row">
			<div class="col-md-9 my-auto"><?php woocommerce_result_count(); ?></div>
			<div class="col-md-3 my-auto text-right"><?php woocommerce_catalog_ordering(); ?></div>
		</div>
	</div>

	<?php 
	woocommerce_output_all_notices();
	woocommerce_product_loop_start();

	if ( have_posts() ) { while ( have_posts() ) { the_post();

			/**
			 * Hook: woocommerce_shop_loop.
			 */
			do_action( 'woocommerce_shop_loop' ); ?>

			<div class="<?php if ( $product_style == 'grid' ) {echo 'col-xl-'.esc_attr( wc_get_loop_prop( 'columns' ) ).' col-md-6';}else{echo 'col-md-3';} ?>">
				<?php if ( $product_style == 'grid' ) {
				    do_action( 'get_motormania_product_item' );
				} elseif ( $product_style == 'list' ) {
				    do_action( 'get_motormania_product_item_horizontal' );
				} else {
				    do_action( 'get_motormania_product_item' );
				} ?>
			</div>
		<?php 
		}
	}

	woocommerce_product_loop_end(); ?>

	<div class="text-center mt-5">
	<?php
	the_posts_pagination( array(
	    'mid_size'  => 2,
	    'prev_text' => esc_html__( '&#10094; Prev', 'motormania' ),
	    'next_text' => esc_html__( 'Next &#10095;', 'motormania' ),
	) ); ?>
	</div>
	
<?php 
} else {
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	do_action( 'woocommerce_no_products_found' );
} ?>