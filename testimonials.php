<?php
/**
 * Plugin Name: Testimonials
 * Plugin URI: https://github.com/Pressed-Solutions/testimonials
 * Description: A plugin to display testimonials with a shortcode
 * Version: 1.0
 * Author: AndrewRMinion Design
 * Author URI: http://andrewrminion.com/
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( ! function_exists('testimonials') ) {

// Register custom post type
function testimonials() {

	$labels = array(
		'name'                => 'Testimonials',
		'singular_name'       => 'Testimonial',
		'menu_name'           => 'Testimonials',
		'name_admin_bar'      => 'Testimonials',
		'parent_item_colon'   => 'Parent Testimonial:',
		'all_items'           => 'All Testimonials',
		'add_new_item'        => 'Add New Testimonials',
		'add_new'             => 'Add New',
		'new_item'            => 'New Testimonial',
		'edit_item'           => 'Edit Testimonial',
		'update_item'         => 'Update Testimonial',
		'view_item'           => 'View Testimonial',
		'search_items'        => 'Search Testimonial',
		'not_found'           => 'Not found',
		'not_found_in_trash'  => 'Not found in Trash',
	);
	$args = array(
		'label'               => 'testimonial',
		'description'         => 'Testimonials',
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields', ),
		'taxonomies'          => array( 'category', 'post_tag' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-format-chat',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'testimonial', $args );

}

// Hook into the 'init' action
add_action( 'init', 'testimonials', 0 );

}

// Add shortcode
function testimonial_shortcode( $atts ) {

	// attributes
	extract( shortcode_atts(
		array(
			'id' => '1',
		), $atts )
	);
}
add_shortcode( 'testimwonial', 'testimonial_shortcode' );

// Add custom metaboxes
add_action( 'add_meta_boxes', 'testimonial_author_metabox' );
function testimonial_author_metabox() {
    add_meta_box( 'testimonial-author', 'Testimonial Name', 'testimonial_callback', 'testimonial' );
}

// Print metabox content
function testimonial_callback( $post ) {
    // add nonce field to check for later
    wp_nonce_field( 'testimonial_author_meta', 'testimonial_author_meta_nonce' );

    // get meta from database
    $custom_values = get_post_custom( $post->ID );
    $testimonial_author = isset( $custom_values['testimonial_author'] ? esc_attr( $custom_values['testimonial_author'] ) : '' );

    echo '<label for="testimonial_author">Testimonial Author:</label>
    <input type="text" name="testimonial_author" placeholder="John Doe" />';
}

// Save custom metadata
add_action( 'save_post', 'save_metabox' );
function save_metabox( $post_id ) {
    // bail if autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    // check for valid nonces
    if ( ! isset( $_POST['testimonial_meta_nonce'] ) || ! wp_verify_nonce( $_POST['testimonial_meta_nonce'], 'testimonial_author_meta' ) ) return;

    // check the user's permissions
    if ( ! current_user_can( 'edit_posts', $post_id ) ) return;

    // sanitize user input
    $testimonial_author_sanitized = sanitize_text_field( $_POST['testimonial_author'] );

    // update the meta fields in database
    if ( isset( $_POST['testimonial_author'] ) ) update_post_meta( $post_id, 'testimonial_author', $testimonial_author_sanitized );
}
