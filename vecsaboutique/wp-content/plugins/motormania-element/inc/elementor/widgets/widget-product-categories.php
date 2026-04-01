<?php 
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
// Product categories
class motormania_Widget_Product_Cat extends Widget_Base {
 
   public function get_name() {
      return 'product_cat';
   }
 
   public function get_title() {
      return esc_html__( 'Product categories', 'motormania' );
   }
 
   public function get_icon() { 
        return 'eicon-posts-carousel';
   }
 
   public function get_categories() {
      return [ 'motormania-elements' ];
   }
   protected function register_controls() {

      $this->start_controls_section(
         'product_cat_section',
         [
            'label' => esc_html__( 'Product categories', 'motormania' ),
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
         'category',
         [
            'label' => esc_html__( 'Category', 'motormania' ),
            'type' => Controls_Manager::SELECT, 
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
         'category_count',
         [
            'label'   => esc_html__( 'Number of Sub Categories', 'motormania' ),
            'type'    => Controls_Manager::NUMBER,
            'default' => 4,
            'min'     => 0,
            'max'     => 100,
            'step'    => 1,
            'condition' => [
               'style' => 'style-2'
            ]
         ]
       );
      
      $this->end_controls_section();
   }

  protected function render( $instance = [] ) {

    // get our input from the widget settings.     
    $settings = $this->get_settings_for_display();

      $terms = get_terms( array (
        'taxonomy' => 'product_cat', 
        'orderby' => 'name',
        'hide_empty' => true,
        'include' => $settings['category']
      ));

      if ( $terms ) {

        foreach ( $terms as $term ) { ?>

         <?php if ($settings['style'] == 'style-1') { ?>

            <a class="category-item text-center" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
              <span class="category-item-img">
                <?php
                $image = wp_get_attachment_url( get_term_meta( $term->term_id, 'thumbnail_id', true ));
                if ( $image ) { ?>
                <img src="<?php echo esc_url( $image ) ?>" alt="<?php echo esc_attr( $term->name ) ?>">
                <?php } ?>
               </span>
               <h5><?php echo esc_html( $term->name ) ?></h5>
            </a>

         <?php } elseif($settings['style'] == 'style-2') { ?>

            <div class="category-item-2">
               <?php
               $image = wp_get_attachment_url( get_term_meta( $term->term_id, 'thumbnail_id', true ));
               if ( $image ) { ?>
                 <a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><img src="<?php echo esc_url( $image ) ?>" alt="<?php echo esc_attr( $term->name ) ?>"></a>
               <?php } ?>
               <div>
                  <h5><a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ) ?></a></h5>
                  <?php
                  $subcategories = get_term_children( $term->term_id, 'product_cat' );

                  if ($subcategories) { ?>
                     <ul class="list-unstyled">
                        <?php foreach ( array_slice($subcategories, 0, $settings['category_count']) as $subcategory ) { 
                        $term_cat = get_term_by( 'id', $subcategory, 'product_cat' ); ?>
                           <li><a href="<?php echo esc_url( get_term_link( $subcategory ) ); ?>"><?php echo esc_html( $term_cat->name ) ?></a></li>
                           <?php } ?>
                     </ul>
                  <?php } ?>

                  <a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html__( 'Show All', 'motormania' ) ?></a>
               </div>
            </div>
            
        <?php }
      }
    }
  }
 
}

Plugin::instance()->widgets_manager->register_widget_type( new motormania_Widget_Product_Cat );