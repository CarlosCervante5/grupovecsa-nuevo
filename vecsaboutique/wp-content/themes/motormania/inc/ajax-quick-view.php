<?php

function motormania_ajax_quick_view(){ 

  // $product = wc_get_product( $_POST['productid'] );

  $product = new WP_Query( array( 
    'post_type' => 'product',
    'posts_per_page' => 1,
    'p' => $_POST['productid'],
  ));
  /* Start the Loop */
  while ( $product->have_posts() ) : $product->the_post(); ?>

  <div class="ajax-text-and-image white-popup-block">
    <style>
    .ajax-text-and-image {
      max-width:800px; margin: 20px auto; background: #FFF; padding: 0; line-height: 0;
    }
    .ajcol {
      width: 50%;
      float:left;
      height: 400px;
      overflow: auto;
    }
    .ajcol img {
      width: 100%; height: auto;
    }
    .ajcol h3 {
      font-weight: bold;
    }
    @media all and (max-width:30em) {
      .ajcol { 
        width: 100%;
        float:none;
      }
    }
    </style>
    <div class="ajcol">
      <?php if ( has_post_thumbnail() ){
        the_post_thumbnail();
      } else { ?>
        <img src="<?php echo get_template_directory_uri().'/assets/images/placeholder.png' ?>" alt="<?php the_title_attribute() ?>">
      <?php } ?>
    </div>
    <div class="ajcol" style="line-height: 1.231;">
      <div style="padding: 50px 15px">
          <h3><?php the_title(); ?></h3>
          <?php woocommerce_template_single_rating(); ?>
          <?php woocommerce_template_single_price(); ?>
          <?php woocommerce_template_single_excerpt(); ?>
          <?php woocommerce_template_single_add_to_cart(); ?>
          <?php woocommerce_template_single_meta(); ?>
      </div>
    </div>
    <div style="clear:both; line-height: 0;"></div>
  </div>
  <?php endwhile; wp_reset_postdata(); ?>
  <?php }

add_action( 'wp_ajax_motormania_ajax_quick_view',  'motormania_ajax_quick_view' );
add_action( 'wp_ajax_nopriv_motormania_ajax_quick_view',  'motormania_ajax_quick_view' );











