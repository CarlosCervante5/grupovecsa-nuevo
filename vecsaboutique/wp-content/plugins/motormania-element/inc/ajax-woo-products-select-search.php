<?php

// AJAX Select Search One
function motormania_ajax_products_select_one_search( $taxonomy ) { ?>
  <option value="-1" disabled selected><?php echo esc_html__( 'Select', 'motormania' ) ?></option>
  <?php
  if (isset($_REQUEST['product_id'])){
    $product_id = $_REQUEST['product_id'];
  };
  $product_taxonomy = get_terms(array('taxonomy' => 'product_cat',
    'hide_empty' => true,
    'parent' => $product_id
  ));

  foreach ($product_taxonomy as $key => $taxonomy) { ?>
    <option value="<?php echo esc_attr( $taxonomy->slug ) ?>" data-id="<?php echo $taxonomy->term_id ?>">
      <?php echo esc_attr( $taxonomy->name ) ?>
      <?php if ($taxonomy->count > 0){
        echo ' (' . esc_attr($taxonomy->count) . ')';
      } ?>
    </option>
  <?php }
}
 
add_action('wp_ajax_motormania_products_select_one_search', 'motormania_ajax_products_select_one_search');
add_action('wp_ajax_nopriv_motormania_products_select_one_search', 'motormania_ajax_products_select_one_search');


// AJAX Select Search One
function motormania_ajax_products_select_two_search( $taxonomy ) { ?>
  <option value="-1" disabled selected><?php echo esc_html__( 'Select', 'motormania' ) ?></option>
  <?php
  if (isset($_REQUEST['product_id'])){
    $product_id = $_REQUEST['product_id'];
  };
  $product_taxonomy = get_terms(array('taxonomy' => 'product_cat',
    'hide_empty' => true,
    'parent' => $product_id
  ));

  foreach ($product_taxonomy as $key => $taxonomy) { ?>
    <option value="<?php echo esc_attr( $taxonomy->slug ) ?>" data-id="<?php echo $taxonomy->term_id ?>">
      <?php echo esc_attr( $taxonomy->name ) ?>
        <?php if ($taxonomy->count > 0){
        echo ' (' . esc_attr($taxonomy->count) . ')';
      } ?>
    </option>
  <?php }
}
 
add_action('wp_ajax_motormania_products_select_two_search', 'motormania_ajax_products_select_two_search');
add_action('wp_ajax_nopriv_motormania_products_select_two_search', 'motormania_ajax_products_select_two_search');