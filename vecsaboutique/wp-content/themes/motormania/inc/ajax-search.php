<?php

function motormania_ajax_search(){
    $the_query = new WP_Query( array( 'posts_per_page' => 10 , 's' => esc_attr( $_POST['keyword'] ), 'post_type' => 'product' ) );
      if( $the_query->have_posts() ) { ?>
      <ul class="ajax-search-results list-unstyled">
        <?php
        while( $the_query->have_posts() ){ $the_query->the_post(); ?>
        <li>
          <a href="<?php echo esc_url( get_permalink() ); ?>">
            <?php
              the_post_thumbnail( 'motormania-32x32');
              the_title();
            ?>
          </a>
        </li>
        <?php }; ?>
      </ul>
      <?php
      wp_reset_postdata();  
      }
  die();
}

add_action( 'wp_ajax_motormania_ajax_search',  'motormania_ajax_search' );
add_action( 'wp_ajax_nopriv_motormania_ajax_search',  'motormania_ajax_search' );











