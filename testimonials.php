<?php
/**
 * Plugin Name: Simple Testimonials
 * Plugin URI: https://github.com/Pressed-Solutions/testimonials
 * Description: A plugin to display testimonials with a shortcode
 * Version: 2.3
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

/**
 * Flush rewrite rules on activation
 */
function pressed_flush_rewrite_rules() {
    pressed_testimonials();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'pressed_flush_rewrite_rules' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

/**
 * Register Testimonials CPT
 */
function pressed_testimonials() {

    $labels = array(
        'name'                => 'Testimonials',
        'singular_name'       => 'Testimonial',
        'menu_name'           => 'Testimonials',
        'name_admin_bar'      => 'Testimonial',
        'parent_item_colon'   => 'Parent Testimonial:',
        'all_items'           => 'All Testimonials',
        'add_new_item'        => 'Add New Testimonial',
        'add_new'             => 'Add New',
        'new_item'            => 'New Testimonial',
        'edit_item'           => 'Edit Testimonial',
        'update_item'         => 'Update Testimonial',
        'view_item'           => 'View Testimonial',
        'search_items'        => 'Search Testimonial',
        'not_found'           => 'Not found',
        'not_found_in_trash'  => 'Not found in Trash',
    );
    $rewrite = array(
        'slug'                => 'testimonials',
        'with_front'          => true,
        'pages'               => true,
        'feeds'               => true,
    );
    $args = array(
        'label'               => 'testimonial',
        'description'         => 'Testimonials',
        'labels'              => $labels,
        'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes', ),
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
        'rewrite'             => $rewrite,
        'capability_type'     => 'page',
    );
    register_post_type( 'testimonial', $args );

}
add_action( 'init', 'pressed_testimonials', 0 );

/**
 * Add [testimonials] shortcode
 * @param  array  $atts shortcode parameters
 * @return string HTML output
 */
function pressed_testimonial_shortcode( $atts ) {
    global $wp_query;
    // attributes
    extract( shortcode_atts(
        array(
            'postid'            => NULL,
            'posts_per_page'    => 10,
            'order'             => 'DESC',
            'orderby'           => 'date',
            'tax_taxonomy'      => 'category',
            'tax_field'         => 'term_id',
            'tax_terms'         => NULL,
            'tax_operator'      => 'IN',
            'show_content'      => false,
            'show_paging'       => false,
        ), $atts )
    );

    // WP_Query arguments
    $args = array (
        'post_type'         => array( 'testimonial' ),
        'posts_per_page'    => $posts_per_page,
    );
    if ( isset( $wp_query->query['page'] ) ) {
        $page = $wp_query->query['page'];
        $args['offset'] = $page * 10;
    }

    if ( $postid ) {
        $args['p'] = $post_id;
    }

    if ( $tax_taxonomy && $tax_terms ) {
        $args['tax_query'] = array( array(
            'taxonomy'      => $tax_taxonomy,
            'field'         => $tax_field,
            'terms'         => $tax_terms,
            'operator'      => $tax_operator,
        ));
    }

    // The Query
    $testimonial_query = new WP_Query( $args );

    // The Loop
    $shortcode_output = '';
    if ( $testimonial_query->have_posts() ) {
        while ( $testimonial_query->have_posts() ) {
            $testimonial_query->the_post();
            $shortcode_output .= '<article class="' . implode( ' ', get_post_class( 'shortcode' ) ) . '"><h3><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
            if ( $show_content ) {
                $shortcode_output .= apply_filters( 'the_content', get_the_content() );
            } else {
                $shortcode_output .= get_the_excerpt();
            }
            if ( get_post_meta( get_the_ID(), 'testimonial_author', true) ) { $shortcode_output .= '<em>&mdash;' . esc_attr( get_post_meta( get_the_ID(), 'testimonial_author', true ) ) . '</em>'; }
            $shortcode_output .= '</article>';
        }

        // paging
        if ( $show_paging ) {
            $shortcode_output .= '<div class="paging">';
            if ( $page > 1 ) {
                $shortcode_output .= '<a class="btn btn-primary" href="' . home_url( $wp_query->query['pagename'] ) . '/' . ( $page - 1 ) . '">Previous</a> ';
            }
            if ( ( $page < $testimonial_query->max_num_pages ) && ( $testimonial_query->post_count === 10 )  ) {
                if ( $page === '' ) {
                    $page = 1;
                }
                $shortcode_output .= '<a class="btn btn-primary" href="' . home_url( $wp_query->query['pagename'] ) . '/' . ( $page + 1 ) . '">Next</a>';
            $shortcode_output .= '</div>';
            }
        }
    } else {
        // no posts found
    }

    // Restore original Post Data
    wp_reset_postdata();

    // print data
    return $shortcode_output;
}
add_shortcode( 'testimonial', 'pressed_testimonial_shortcode' );

/**
 * Add custom metaboxes
 */
function pressed_testimonial_author_metabox() {
    add_meta_box( 'testimonial-author', 'Testimonial Author', 'pressed_testimonial_callback', 'testimonial' );
}
add_action( 'add_meta_boxes', 'pressed_testimonial_author_metabox' );

/**
 * Print metabox content
 * @param object $post WP_Post
 */
function pressed_testimonial_callback( $post ) {
    // add nonce field to check for later
    wp_nonce_field( 'testimonial_author_meta', 'testimonial_author_meta_nonce' );

    // get meta from database
    $custom_values = get_post_custom( $post->ID );
    $testimonial_author = esc_attr( $custom_values['testimonial_author'][0] );

    echo '<label for="testimonial_author">Testimonial Author:</label>
    <input type="text" name="testimonial_author" placeholder="John Doe "';
    if ( isset( $testimonial_author ) ) { echo 'value="' . $testimonial_author . '" '; }
    echo '/>';
}

/**
 * Save metabox metadata
 * @param integer $post_id WP post ID
 */
function pressed_save_metabox( $post_id ) {
    // bail if autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    // check for valid nonces
    if ( ! isset( $_POST['testimonial_author_meta_nonce'] ) || ! wp_verify_nonce( $_POST['testimonial_author_meta_nonce'], 'testimonial_author_meta' ) ) return;

    // check the user's permissions
    if ( ! current_user_can( 'edit_posts', $post_id ) ) return;

    // sanitize user input
    $testimonial_author_sanitized = sanitize_text_field( $_POST['testimonial_author'] );

    // update the meta fields in database
    if ( isset( $_POST['testimonial_author'] ) ) update_post_meta( $post_id, 'testimonial_author', $testimonial_author_sanitized );
}
add_action( 'save_post', 'pressed_save_metabox' );
