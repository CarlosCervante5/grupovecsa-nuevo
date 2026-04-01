<?php 
namespace Elementor;
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Products
class motormania_Widget_products extends Widget_Base {
 
   public function get_name() {
      return 'Products';
   }
 
   public function get_title() {
      return esc_html__( 'Products', 'motormania' );
   }
 
   public function get_icon() { 
        return 'eicon-form-vertical';
   }
 
   public function get_categories() {
      return [ 'motormania-elements' ];
   }

   protected function register_controls() {

      $this->start_controls_section(
         'products_section',
         [
            'label' => esc_html__( 'Products', 'motormania' ),
            'type' => Controls_Manager::SECTION,
         ]
      );

      $this->add_control(
         'style',
         [
            'label' => __( 'Style', 'motormania' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'style-1',
            'options' => [
               'style-1'  => __( 'Style 1', 'motormania' ),
               'style-2' => __( 'Style 2', 'motormania' )
            ],
         ]
      );

      $this->add_control(
         'product_by',
         [
            'label' => __( 'Product By', 'motormania' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
               'top-rated'  => __( 'Top Rated', 'motormania' ),
               'on-sale' => __( 'On Sale', 'motormania' ),
               'best-selling' => __( 'Best Selling', 'motormania' )
            ],
         ]
      );

      $this->add_control(
         'columns',
         [
            'label' => __( 'Columns', 'motormania' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'col-md-4',
            'options' => [
               'col-xl-12'  => __( 'Column 1', 'motormania' ),
               'col-xl-6' => __( 'Column 2', 'motormania' ),
               'col-xl-4 col-md-6' => __( 'Column 3', 'motormania' ),
               'col-xl-3 col-lg-4 col-md-6' => __( 'Column 4', 'motormania' ),
               'col-xl-2' => __( 'Column 6', 'motormania' ),
               'col-xl-1' => __( 'Column 12', 'motormania' ),
            ],
         ]
      );

      $this->add_control(
         'category',
         [
            'label' => esc_html__( 'Category', 'motormania' ),
            'type' => Controls_Manager::SELECT2, 
            'title' => esc_html__( 'Select a category', 'motormania' ),
            'multiple' => true,
            'options' => motormania_get_terms_dropdown_array([
               'taxonomy' => 'product_cat',
               'hide_empty' => false,
               'parent' => 0
            ]),
         ]
      );

      $this->add_control(
         'ppp',
         [
            'label' => __( 'Number of Items', 'motormania' ),
            'type' => Controls_Manager::SLIDER,
            'range' => [
               'no' => [
                  'min' => 0,
                  'max' => 100,
                  'step' => 1,
               ],
            ],
            'default' => [
               'size' => 3,
            ]
         ]
      );

      $this->add_control(
         'order',
         [
            'label' => __( 'order', 'motormania' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'DESC',
            'options' => [
               'ASC'  => __( 'Ascending', 'motormania' ),
               'DESC' => __( 'Descending', 'motormania' )
            ],
         ]
      );

      $this->end_controls_section();

   }

   protected function render( $instance = [] ) {
 
      // get our input from the widget settings.
       
      $settings = $this->get_settings_for_display(); ?>

      <div class="row justify-content-center">
         <?php
         
         // meta_key condition
         if ( $settings['product_by'] == 'best-selling' ) {
            $product_by = 'total_sales';
         } elseif ( $settings['product_by'] == 'top-rated' ) {
            $product_by = '_wc_average_rating';
         } else {
            $product_by = '';
         }

         $category = !empty( $settings['category'] ) ? $settings['category'] : motormania_get_product_category_ids();

         $products = new \WP_Query( array( 
            'post_type' => 'product',
            'meta_key' => $product_by,
            'orderby' => $settings['product_by'] == 'best-selling' || $settings['product_by'] == 'top-rated' ? 'meta_value_num' : "",
            'posts_per_page' => $settings['ppp']['size'],
            'order' => $settings['order'],
            'tax_query'     => array(
                 array(
                     'taxonomy'  => 'product_cat',
                     'field'     => 'id', 
                     'terms'     => $category
                 )
            ),
            'meta_query'     => $settings['product_by'] == 'on-sale' ? array(
               'relation' => 'OR',
                  array( // Simple products type
                     'key'           => '_sale_price',
                     'value'         => 0,
                     'compare'       => '>',
                     'type'          => 'numeric'
                  ),
                  array( // Variable products type
                     'key'           => '_min_variation_sale_price',
                     'value'         => 0,
                     'compare'       => '>',
                     'type'          => 'numeric'
                  )
            ) : "",
         ));

         /* Start the Loop */
         while ( $products->have_posts() ) : $products->the_post(); ?>
            <!-- Item -->
            <div class="<?php echo esc_attr($settings['columns']) ?>">
               <?php if ($settings['style'] == 'style-1') { ?>
                  <?php do_action( 'get_motormania_product_item' ) ?>
               <?php } elseif($settings['style'] == 'style-2') { ?>
                  <?php do_action( 'get_motormania_product_item_left_image' ) ?>
               <?php } ?>
            </div>

         <?php 
         endwhile; 
      wp_reset_postdata();
      ?>
      </div>
      
   <?php
   }
 
}

Plugin::instance()->widgets_manager->register_widget_type( new motormania_Widget_products );