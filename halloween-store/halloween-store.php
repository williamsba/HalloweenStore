<?php
/*
Plugin Name: Halloween Store
Plugin URI: http://webdevstudios.com/support/wordpress-plugins/
Description: Create a Halloween Store to display product information
Version: 1.0
Author: Brad Williams
Author URI: http://webdevstudios.com
License: GPLv2
*/

/*  Copyright 2013  Brad Williams  (email : brad@webdevstudios.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Call function when plugin is activated
register_activation_hook( __FILE__, 'halloween_store_install' );

function halloween_store_install() {
	
    //setup default option values
    $hween_options_arr = array(
        'currency_sign' => '$'
    );

    //save our default option values
    update_option( 'halloween_options', $hween_options_arr );
	
}


// Action hook to initialize the plugin
add_action( 'init', 'halloween_store_init' );

//Initialize the Halloween Store
function halloween_store_init() {

	//register the products custom post type
	$labels = array(
		'name' => __( 'Products', 'halloween-plugin' ),
		'singular_name' => __( 'Product', 'halloween-plugin' ),
		'add_new' => __( 'Add New', 'halloween-plugin' ),
		'add_new_item' => __( 'Add New Product', 'halloween-plugin' ),
		'edit_item' => __( 'Edit Product', 'halloween-plugin' ),
		'new_item' => __( 'New Product', 'halloween-plugin' ),
		'all_items' => __( 'All Products', 'halloween-plugin' ),
		'view_item' => __( 'View Product', 'halloween-plugin' ),
		'search_items' => __( 'Search Products', 'halloween-plugin' ),
		'not_found' =>  __( 'No products found', 'halloween-plugin' ),
		'not_found_in_trash' => __( 'No products found in Trash', 'halloween-plugin' ),
		'menu_name' => __( 'Products', 'halloween-plugin' )
	  );
	
	  $args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true, 
		'show_in_menu' => true, 
		'query_var' => true,
		'rewrite' => true,
		'capability_type' => 'post',
		'has_archive' => true, 
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' )
	  ); 
	  
	  register_post_type( 'halloween-products', $args );

}

// Action hook to add the post products menu item
add_action( 'admin_menu', 'halloween_store_menu' );

//create the Halloween Masks sub-menu
function halloween_store_menu() {
	
    add_options_page( __( 'Halloween Store Settings Page', 'halloween-plugin' ), __( 'Halloween Store Settings', 'halloween-plugin' ), 'manage_options', 'halloween-store-settings', 'halloween_store_settings_page' );
	
}

//build the plugin settings page
function halloween_store_settings_page() {
	
    //load the plugin options array
    $hween_options_arr = get_option( 'halloween_options' );
	
	//set the option array values to variables
	$hs_inventory = ( ! empty( $hween_options_arr['show_inventory'] ) ) ? $hween_options_arr['show_inventory'] : '';
	$hs_currency_sign = $hween_options_arr['currency_sign'];
    ?>
    <div class="wrap">
    <h2><?php _e( 'Halloween Store Options', 'halloween-plugin' ) ?></h2>

    <form method="post" action="options.php">
        <?php settings_fields( 'halloween-settings-group' ); ?>
        <table class="form-table">
            <tr valign="top">
            <th scope="row"><?php _e( 'Show Product Inventory', 'halloween-plugin' ) ?></th>
            <td><input type="checkbox" name="halloween_options[show_inventory]" <?php echo checked( $hs_inventory, 'on' ); ?> /></td>
            </tr>

            <tr valign="top">
            <th scope="row"><?php _e( 'Currency Sign', 'halloween-plugin' ) ?></th>
            <td><input type="text" name="halloween_options[currency_sign]" value="<?php echo esc_attr( $hs_currency_sign ); ?>" size="1" maxlength="1" /></td>
            </tr>
        </table>

        <p class="submit">
        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'halloween-plugin' ); ?>" />
        </p>

    </form>
    </div>
<?php
}

// Action hook to register the plugin option settings
add_action( 'admin_init', 'halloween_store_register_settings' );

function halloween_store_register_settings() {
	
    //register the array of settings
    register_setting( 'halloween-settings-group', 'halloween_options', 'halloween_sanitize_options' );
	
}

function halloween_sanitize_options( $options ) {
	
	$options['show_inventory'] = ( ! empty( $options['show_inventory'] ) ) ? sanitize_text_field( $options['show_inventory'] ) : '';
	$options['currency_sign'] = ( ! empty( $options['currency_sign'] ) ) ? sanitize_text_field( $options['currency_sign'] ) : '';
	
	return $options;
	
}

//Action hook to register the Products meta box
add_action( 'add_meta_boxes', 'halloween_store_register_meta_box' );

function halloween_store_register_meta_box() {
	
    // create our custom meta box 
	add_meta_box( 'halloween-product-meta', __( 'Product Information','halloween-plugin' ), 'halloween_meta_box', 'halloween-products', 'side', 'default' );
	
}

//build product meta box
function halloween_meta_box( $post ) {

    // retrieve our custom meta box values
    $hween_sku = get_post_meta( $post->ID, '_halloween_product_sku', true );
    $hween_price = get_post_meta( $post->ID, '_halloween_product_price', true );
    $hween_weight = get_post_meta( $post->ID, '_halloween_product_weight', true );
    $hween_color = get_post_meta( $post->ID, '_halloween_product_color', true );
    $hween_inventory = get_post_meta( $post->ID, '_halloween_product_inventory', true );

	//nonce field for security
	wp_nonce_field( 'meta-box-save', 'halloween-plugin' );
	
    // display meta box form
    echo '<table>';
    echo '<tr>';
    echo '<td>' .__('Sku', 'halloween-plugin').':</td><td><input type="text" name="halloween_product_sku" value="'.esc_attr( $hween_sku ).'" size="10"></td>';
    echo '</tr><tr>';
    echo '<td>' .__('Price', 'halloween-plugin').':</td><td><input type="text" name="halloween_product_price" value="'.esc_attr( $hween_price ).'" size="5"></td>';
    echo '</tr><tr>';
    echo '<td>' .__('Weight', 'halloween-plugin').':</td><td><input type="text" name="halloween_product_weight" value="'.esc_attr( $hween_weight ).'" size="5"></td>';
    echo '</tr><tr>';
    echo '<td>' .__('Color', 'halloween-plugin').':</td><td><input type="text" name="halloween_product_color" value="'.esc_attr( $hween_color ).'" size="5"></td>';
    echo '</tr><tr>';
    echo '<td>Inventory:</td><td><select name="halloween_product_inventory" id="halloween_product_inventory">
            <option value="In Stock"' .selected( $hween_inventory, 'In Stock', false ). '>' .__( 'In Stock', 'halloween-plugin' ). '</option>
            <option value="Backordered"' .selected( $hween_inventory, 'Backordered', false ). '>' .__( 'Backordered', 'halloween-plugin' ). '</option>
            <option value="Out of Stock"' .selected( $hween_inventory, 'Out of Stock', false ). '>' .__( 'Out of Stock', 'halloween-plugin' ). '</option>
            <option value="Discontinued"' .selected( $hween_inventory, 'Discontinued', false ). '>' .__( 'Discontinued', 'halloween-plugin' ). '</option>
        </select></td>';
    echo '</tr>';

    //display the meta box shortcode legend section
    echo '<tr><td colspan="2"><hr></td></tr>';
    echo '<tr><td colspan="2"><strong>' .__( 'Shortcode Legend', 'halloween-plugin' ).'</strong></td></tr>';
    echo '<tr><td>' .__( 'Sku', 'halloween-plugin' ) .':</td><td>[hs show=sku]</td></tr>';
    echo '<tr><td>' .__( 'Price', 'halloween-plugin' ).':</td><td>[hs show=price]</td></tr>';
    echo '<tr><td>' .__( 'Weight', 'halloween-plugin' ).':</td><td>[hs show=weight]</td></tr>';
    echo '<tr><td>' .__( 'Color', 'halloween-plugin' ).':</td><td>[hs show=color]</td></tr>';
    echo '<tr><td>' .__( 'Inventory', 'halloween-plugin' ).':</td><td>[hs show=inventory]</td></tr>';
    echo '</table>';
}

// Action hook to save the meta box data when the post is saved
add_action( 'save_post','halloween_store_save_meta_box' );

//save meta box data
function halloween_store_save_meta_box( $post_id ) {

	//verify the post type is for Halloween Products and metadata has been posted
	if ( get_post_type( $post_id ) == 'halloween-products' && isset( $_POST['halloween_product_sku'] ) ) {
		
		//if autosave skip saving data
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;

		//check nonce for security
		check_admin_referer( 'meta-box-save', 'halloween-plugin' );

		// save the meta box data as post metadata
		update_post_meta( $post_id, '_halloween_product_sku', sanitize_text_field( $_POST['halloween_product_sku'] ) );
		update_post_meta( $post_id, '_halloween_product_price', sanitize_text_field( $_POST['halloween_product_price'] ) );
		update_post_meta( $post_id, '_halloween_product_weight', sanitize_text_field( $_POST['halloween_product_weight'] ) );
		update_post_meta( $post_id, '_halloween_product_color', sanitize_text_field( $_POST['halloween_product_color'] ) );
		update_post_meta( $post_id, '_halloween_product_inventory',sanitize_text_field( $_POST['halloween_product_inventory'] ) );

	}
	
}

// Action hook to create the products shortcode
add_shortcode( 'hs', 'halloween_store_shortcode' );

//create shortcode
function halloween_store_shortcode( $atts, $content = null ) {
    global $post;
	
    extract( shortcode_atts( array(
        "show" => ''
    ), $atts ) );

    //load options array
    $hween_options_arr = get_option( 'halloween_options' );

    if ( $show == 'sku') {
		
        $hs_show = get_post_meta( $post->ID, '_halloween_product_sku', true );
		
    }elseif ( $show == 'price' ) {
		
        $hs_show = $hween_options_arr['currency_sign']. get_post_meta( $post->ID, '_halloween_product_price', true );
		
    }elseif ( $show == 'weight' ) {
		
        $hs_show = get_post_meta( $post->ID, '_halloween_product_weight', true );
		
    }elseif ( $show == 'color' ) {
		
        $hs_show = get_post_meta( $post->ID, '_halloween_product_color', true );
		
    }elseif ( $show == 'inventory' ) {
		
        $hs_show = get_post_meta( $post->ID, '_halloween_product_inventory', true );
		
    }

	//return the shortcode value to display
    return $hs_show;
}

// Action hook to create plugin widget
add_action( 'widgets_init', 'halloween_store_register_widgets' );

//register the widget
function halloween_store_register_widgets() {
	
    register_widget( 'hs_widget' );
	
}

//hs_widget class
class hs_widget extends WP_Widget {

    //process our new widget
    public function __construct() {
        $widget_ops = array(
            'classname' => 'hs-widget-class',
            'description' => __('Display Halloween Products','halloween-plugin'),
        );
        parent::__construct( 'hs_widget', __( 'Products Widget', 'halloween-plugin'), $widget_ops);
    }

    //build our widget settings form
    function form( $instance ) {
		
        $defaults = array( 
			'title'           => __( 'Products', 'halloween-plugin' ), 
			'number_products' => '3' );
		
        $instance = wp_parse_args( (array) $instance, $defaults );
        $title = $instance['title'];
        $number_products = $instance['number_products'];
        ?>
            <p><?php _e('Title', 'halloween-plugin') ?>: 
				<input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
            <p><?php _e( 'Number of Products', 'halloween-plugin' ) ?>: 
				<input name="<?php echo $this->get_field_name( 'number_products' ); ?>" type="text" value="<?php echo esc_attr( $number_products ); ?>" size="2" maxlength="2" />
			</p>
        <?php
    }

    //save our widget settings
    function update( $new_instance, $old_instance ) {
		
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        $instance['number_products'] = absint( $new_instance['number_products'] );

        return $instance;
		
    }

     //display our widget
    function widget( $args, $instance ) {
        global $post;
		
        extract( $args );

        echo $before_widget;
        $title = apply_filters( 'widget_title', $instance['title'] );
        $number_products = $instance['number_products'];

        if ( ! empty( $title ) ) { echo $before_title . esc_html( $title ) . $after_title; };

		//custom query to retrieve products
		$args = array(
			'post_type'			=>	'halloween-products',
			'posts_per_page'	=>	absint( $number_products )
		);
		
        $dispProducts = new WP_Query();
        $dispProducts->query( $args );
		
        while ( $dispProducts->have_posts() ) : $dispProducts->the_post();

            //load options array
            $hween_options_arr = get_option( 'halloween_options' );

            //load custom meta values
            $hs_price = get_post_meta( $post->ID, '_halloween_product_price', true );
            $hs_inventory = get_post_meta( $post->ID, '_halloween_product_inventory', true );
            ?>
			<p>
				<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?> Product Information">
				<?php the_title(); ?>
				</a>
			</p>
			<?php
			echo '<p>' .__( 'Price', 'halloween-plugin' ). ': '.$hween_options_arr['currency_sign'] .$hs_price .'</p>';

            //check if Show Inventory option is enabled
            if ( $hween_options_arr['show_inventory'] ) {
				
				//display the inventory metadata for this product
                echo '<p>' .__( 'Stock', 'halloween-plugin' ). ': ' .$hs_inventory .'</p>';
				
            }
            echo '<hr>';

        endwhile;
		
		wp_reset_postdata();

        echo $after_widget;
		
    }
	
}
