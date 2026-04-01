<?php

if( !class_exists( 'Walker_Nav_Menu_Edit') ){
    require_once ABSPATH . 'wp-admin/includes/class-walker-nav-menu-edit.php';
}

class motormania_Walker_Nav_Menu_Mega extends Walker_Nav_Menu_Edit  {

    public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        $item_id = esc_attr( $item->ID );
        $walker = new Walker_Nav_Menu_Edit();
        $walker->start_el( $item_output, $item, $depth, $args, $id);
        $args = array(
            'post_type' => 'elementor_library',
            'posts_per_page' => -1,
        );
        $temp = $active = '';
        $query = new WP_Query($args);
        if ( $query->have_posts() ) {
            while ($query->have_posts()) {
                $query->the_post();
                if ($item->mega_menu == get_the_ID()) $active = 'selected';
                else $active = '';
                $temp .= '<option ' . $active . ' value="' . get_the_ID() . '">' . get_the_title() . '</option>';
            }

            $custom_html = '';

            $custom_html = '<p class="description description-wide">
                                <label>
                                    ' . esc_html__( 'Select Mega Menu', 'motormania') . '<br>
                                    <select id="edit-menu-item-[' . $item_id . ']" name="edit-menu-item-' . $item_id . '">
                                        <option value="0">' . esc_html__( 'None', 'motormania') . '</option>
                                        ' . $temp . '
                                    </select>
                                </label>
                            </p>';

            if( $depth == 0 ) {
                $output .= str_replace( '<div class="menu-item-actions description-wide submitbox">', $custom_html . '<div class="menu-item-actions description-wide submitbox">', $item_output);
            }
            if( $depth != 0 ) {
                $output .= $item_output;
            }
        }
    }
}



class motormania_Mega_Menu{

    function __construct(){
        add_filter( 'wp_edit_nav_menu_walker', array($this, 'edit_nav_menu_walker'), 10, 2);

        add_filter( 'wp_setup_nav_menu_item', array($this, 'setup_nav_menu_item'));
        add_action( 'wp_update_nav_menu_item', array($this, 'update_nav_menu_item'), 10, 3);

        add_filter( 'walker_nav_menu_start_el', array($this, 'custom_walker_nav_menu_start_el'), 10, 4);
    }

    public function edit_nav_menu_walker($walker, $menu_id) {
        return 'motormania_Walker_Nav_Menu_Mega';
    }

    public function custom_walker_nav_menu_start_el( $item_output, $item, $depth, $args ){
        
        $attributes = ! empty( $item->attr_title ) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= ! empty( $item->target ) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= ! empty( $item->url ) ? ' href="' . esc_attr($item->url) . '"' : '';

        $item_output = '';
        $has_children = isset($args->has_children) ? $args->has_children : '';

        if ( $item->mega_menu != 0 || (is_page_template( 'page-split.php') && $args->has_children) ) {
            $item_output .= sprintf( '<a %s class="dropdown-mega-menu-toggle">%s</a>', $attributes, $item->title);
        } else {
            $item_output .= sprintf( '<a %s>%s</a>', $attributes, $item->title);
        }

        if ( $item->mega_menu != 0 ) {
            $content = get_post( $item->mega_menu );
            $item_output .= sprintf( '<div class="mega-menu-content">%s</div>', do_shortcode('[megamenu_shortcode id='.$content->ID.']'));
            wp_reset_postdata();
        }

        return $item_output;
    }


    public function setup_nav_menu_item($menu_item) {
        $menu_item->mega_menu = get_post_meta( $menu_item->ID, "megamenu_page_id", true );
        return $menu_item;
    }
    public function update_nav_menu_item($menu_id, $menu_item_db_id, $args ) {
        $custom_value = isset($_REQUEST["edit-menu-item-".$menu_item_db_id]) ? $_REQUEST["edit-menu-item-".$menu_item_db_id] : '';
        update_post_meta( $menu_item_db_id, "megamenu_page_id", $custom_value );
    }

}

if( class_exists( 'Walker_Nav_Menu') ){
    new motormania_Mega_Menu();
}