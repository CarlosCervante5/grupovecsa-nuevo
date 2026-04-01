<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 100.4.0
 */

defined( 'ABSPATH' ) || exit;

global $motormania_opt; 

$shop_layout = !empty( $motormania_opt['shop_layout'] ) ? $motormania_opt['shop_layout'] : 'full_width';

if(!empty($_GET['shop_layout'])){
    $shop_layout = $_GET['shop_layout'];
}
// http://localhost/motormania/?shop_layout=left_sidebar&shop_columns=4

get_header( 'shop' ); ?>

<section class="section-padding bg-gray">
	<div class="container">
	<?php 
	    if ( $shop_layout == 'full_width' ) {
	        get_template_part( 'template-parts/shop-layout/shop', 'fullwidth' );
	    } elseif ( $shop_layout == 'left_sidebar' ) {
	        get_template_part( 'template-parts/shop-layout/shop', 'left' );
	    } elseif ( $shop_layout == 'right_sidebar' ) {
	        get_template_part( 'template-parts/shop-layout/shop', 'right' );
	    } else {
	        get_template_part( 'template-parts/shop-layout/shop', 'fullwidth' );
	    }
    ?>
	</div>
</section>
<?php get_footer( 'shop' );
