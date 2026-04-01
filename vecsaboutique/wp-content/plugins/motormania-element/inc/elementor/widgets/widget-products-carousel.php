<?php 
namespace Elementor;
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// product
class motormania_Widget_Products_Carousel extends Widget_Base {
 
   public function get_name() {
      return 'products_carousel';
   }
 
   public function get_title() {
      return esc_html__( 'Products Carousel', 'motormania' );
   }
 
   public function get_icon() { 
        return 'eicon-gallery-masonry';
   }
 
   public function get_categories() {
      return [ 'motormania-elements' ];
   }

   protected function register_controls() {

      $this->start_controls_section(
         'products_carousel_section',
         [
            'label' => esc_html__( 'product', 'motormania' ),
            'type' => Controls_Manager::SECTION,
         ]
      );
      
      $this->add_control(
         'title_show',
         [
            'label' => __( 'Title Show', 'motormania' ),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __( 'Yes', 'motormania' ),
            'label_off' => __( 'No', 'motormania' ),
            'return_value' => 'yes',
            'default' => 'yes'
         ]
      );

      $this->add_control(
         'title',
         [
            'label' => __( 'Title', 'motormania' ),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => __('Featured Products','motormania'),            
            'condition' => [
               'title_show' => 'yes'
            ]
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
         'filter',
         [
            'label' => __( 'Filter', 'motormania' ),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => __( 'Yes', 'motormania' ),
            'label_off' => __( 'No', 'motormania' ),
            'return_value' => 'yes',
            'default' => 'yes'
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
               'size' => 12,
            ]
         ]
      );

      $this->add_control(
         'slidestoshow',
         [
            'label' => __('Slides To Show', 'motormania' ),
            'type' => Controls_Manager::NUMBER,
            'min' => 1,
            'max' => 100,
            'step' => 1,
            'default' => 5
         ]
      );

      $this->add_control(
         'slidestoscroll',
         [
            'label' => __( 'Slides To Scroll', 'motormania' ),
            'type' => Controls_Manager::NUMBER,
            'min' => 1,
            'max' => 10,
            'step' => 1,
            'default' => 5
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
      $settings = $this->get_settings_for_display();

      $uniqueId = wp_rand( 0, 1000 ); ?>
      
      <div class="container">
         <!-- Nav tabs -->
         <div class="product-filter" id="product-filter-<?php echo esc_attr( $uniqueId ) ?>">
            <?php if ( 'yes' == $settings['title_show'] ) {?>
               <h4><?php echo esc_html( $settings['title'] ) ?></h4>
            <?php } ?>
            <?php if ( 'yes' == $settings['filter'] ) {?>
            <div class="<?php if ( 'yes' == $settings['title_show'] ){ echo 'float-r'; } else {echo 'float-l'; } ?>">
               <ul class="nav" role="tablist">
                  <?php foreach ( $settings['category'] as $key => $single_category ) { ?>
                  <li class="nav-item">
                     <a class="nav-link <?php if( $key == 0 ){ echo'active'; } ?>" data-bs-toggle="tab" href="#<?php echo esc_attr( get_term_by('id', $single_category , 'product_cat')->slug ).$uniqueId ?>"><?php echo esc_attr( get_term_by('id', $single_category , 'product_cat')->name ) ?></a>
                  </li>
                  <?php } ?>
               </ul>
            </div>
            <?php } ?>
         </div>

         <!-- Tab panes -->
         <div class="tab-content">
            <?php foreach ( $settings['category'] as $key => $single_category ) { ?>
            <div id="<?php echo esc_attr( get_term_by('id', $single_category , 'product_cat')->slug.$uniqueId ) ?>" class="container tab-pane p-0 <?php if( $key == 0 ){ echo'active'; } ?>">
               <div class="product-items row" data-slick='{"slidesToShow": <?php echo esc_attr( $settings['slidestoshow'] ) ?>, "slidesToScroll": <?php echo esc_attr( $settings['slidestoscroll'] ) ?>}'>
                  <?php
                  $product = new \WP_Query( array( 
                     'post_type' => 'product',
                     'posts_per_page' => $settings['ppp']['size'],
                     'order' => $settings['order'],
                     'tax_query'     => array(
                          array(
                              'taxonomy'  => 'product_cat',
                              'field'     => 'id', 
                              'terms'     =>  $single_category
                          )
                      )
                  ));

                  /* Start the Loop */
                  while ( $product->have_posts() ) : $product->the_post(); ?>
                     <div>
                        <?php do_action( 'get_motormania_product_item' ) ?>
                     </div>
                  <?php endwhile; wp_reset_postdata(); ?>
               </div>
            </div>
         <?php } ?>
         </div>
      </div>
   <?php
   }
 
}

Plugin::instance()->widgets_manager->register_widget_type( new motormania_Widget_Products_Carousel );