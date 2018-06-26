<?php
/**
 * Plugin Name: Simple Testimonials
 * Plugin URI: https://github.com/Pressed-Solutions/testimonials
 * Description: A plugin to display testimonials with a shortcode
 * Author: AndrewRMinion Design
 * Author URI: http://andrewrminion.com/
 * Version: 2.4
 * Tested up to: 4.9.6
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Simple_Testimonials
 */

if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

new Simple_Testimonials();

/**
 * Simple Testimonials class
 */
class Simple_Testimonials {

	/**
	 * Kick things off.
	 */
	public function __construct() {
		// Activation.
		register_activation_hook( __FILE__, array( $this, 'flush_rewrite_rules' ) );
		register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );

		// CPT.
		add_action( 'init', array( $this, 'register_cpt' ), 0 );

		// Shortcode.
		add_shortcode( 'testimonial', array( $this, 'shortcode_testimonial' ) );

		// Metabox.
		add_action( 'add_meta_boxes', array( $this, 'testimonial_author_metabox' ) );
		add_action( 'save_post', array( $this, 'save_metabox' ) );
	}

	/**
	 * Handle plugin activation
	 *
	 * @return void Registers CPT and flushes rewrite rules.
	 */
	public function flush_rewrite_rules() {
		$this->register_cpt();
		flush_rewrite_rules();
	}

	/**
	 * Register CPT
	 *
	 * @return  void Registers CPT.
	 */
	public function register_cpt() {

		$labels  = array(
			'name'               => 'Testimonials',
			'singular_name'      => 'Testimonial',
			'menu_name'          => 'Testimonials',
			'name_admin_bar'     => 'Testimonial',
			'parent_item_colon'  => 'Parent Testimonial:',
			'all_items'          => 'All Testimonials',
			'add_new_item'       => 'Add New Testimonial',
			'add_new'            => 'Add New',
			'new_item'           => 'New Testimonial',
			'edit_item'          => 'Edit Testimonial',
			'update_item'        => 'Update Testimonial',
			'view_item'          => 'View Testimonial',
			'search_items'       => 'Search Testimonial',
			'not_found'          => 'Not found',
			'not_found_in_trash' => 'Not found in Trash',
		);
		$rewrite = array(
			'slug'       => 'testimonials',
			'with_front' => true,
			'pages'      => true,
			'feeds'      => true,
		);
		$args    = array(
			'label'               => 'testimonial',
			'description'         => 'Testimonials',
			'labels'              => $labels,
			'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'revisions', 'page-attributes' ),
			'taxonomies'          => array( 'category', 'post_tag', 'testimonial_rating' ),
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

		$rating_labels = array(
			'name'                       => 'Ratings',
			'singular_name'              => 'Rating',
			'menu_name'                  => 'Ratings',
			'all_items'                  => 'All Ratings',
			'parent_item'                => 'Parent Rating',
			'parent_item_colon'          => 'Parent Rating:',
			'new_item_name'              => 'New Rating Name',
			'add_new_item'               => 'Add New Rating',
			'edit_item'                  => 'Edit Rating',
			'update_item'                => 'Update Rating',
			'view_item'                  => 'View Rating',
			'separate_items_with_commas' => 'Separate ratings with commas',
			'add_or_remove_items'        => 'Add or remove ratings',
			'choose_from_most_used'      => 'Choose from the most used',
			'popular_items'              => 'Popular Ratings',
			'search_items'               => 'Search Ratings',
			'not_found'                  => 'Not Found',
		);
		$rating_args   = array(
			'labels'            => $rating_labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'rewrite'           => false,
		);
		register_taxonomy( 'testimonial_rating', array( 'testimonial' ), $rating_args );
	}

	/**
	 * Add testimonials shortcode
	 *
	 * @param  array $atts Shortcode parameters.
	 *
	 * @return string HTML output.
	 */
	public function shortcode_testimonial( $atts ) {
		global $wp_query;
		// Attributes.
		$shortcode_atts = shortcode_atts(
			array(
				'postid'         => null,
				'posts_per_page' => 10,
				'order'          => 'DESC',
				'orderby'        => 'date',
				'tax_taxonomy'   => 'category',
				'tax_field'      => 'term_id',
				'tax_terms'      => null,
				'tax_operator'   => 'IN',
				'show_content'   => false,
				'show_rating'    => false,
				'show_paging'    => false,
			), $atts
		);

		// WP_Query arguments.
		$args = array(
			'post_type'      => array( 'testimonial' ),
			'posts_per_page' => $shortcode_atts['posts_per_page'],
		);

		if ( ! empty( $wp_query->query['page'] ) ) {
			$page           = $wp_query->query['page'];
			$args['offset'] = $page * 10;
		}

		if ( isset( $shortcode_atts['postid'] ) ) {
			$args['p'] = $shortcode_atts['post_id'];
		}

		if ( isset( $shortcode_atts['tax_taxonomy'] ) && isset( $shortcode_atts['tax_terms'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => $shortcode_atts['tax_taxonomy'],
					'field'    => $shortcode_atts['tax_field'],
					'terms'    => $shortcode_atts['tax_terms'],
					'operator' => $shortcode_atts['tax_operator'],
				),
			);
		}

		$testimonial_query = new WP_Query( $args );

		$shortcode_output = '';
		if ( $testimonial_query->have_posts() ) {
			echo '<div class="testimonials shortcode">';
			while ( $testimonial_query->have_posts() ) {
				$testimonial_query->the_post();
				$testimonial_author = get_post_meta( get_the_ID(), 'testimonial_author', true );

				$shortcode_output .= '<article class="' . implode( ' ', get_post_class( 'shortcode' ) ) . '"><h3><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
				if ( $shortcode_atts['show_rating'] ) {
					$shortcode_output .= '<p class="rating">' . get_the_term_list( get_the_ID(), 'testimonial_rating' ) . '</p>';
				}

				if ( $shortcode_atts['show_content'] ) {
					$shortcode_output .= apply_filters( 'the_content', get_the_content() );
				} else {
					$shortcode_output .= get_the_excerpt();
				}

				if ( $shortcode_atts['show_rating'] ) {
					$shortcode_output .= '<p class="rating">' . get_the_term_list( get_the_ID(), 'testimonial_rating' ) . '</p>';
				}

				if ( ! empty( $testimonial_author ) ) {
					$shortcode_output .= '<em>&mdash;' . esc_attr( $testimonial_author ) . '</em>'; }
				$shortcode_output .= '</article>';
			}

			// Paging.
			if ( $shortcode_atts['show_paging'] ) {
				$shortcode_output .= '<div class="paging">';
				if ( $page > 1 ) {
					$shortcode_output .= '<a class="btn btn-primary button" href="' . home_url( $wp_query->query['pagename'] ) . '/' . ( $page - 1 ) . '">Previous</a> ';
				}
				if ( ( $page < $testimonial_query->max_num_pages ) && ( 10 === $testimonial_query->post_count ) ) {
					if ( '' === $page ) {
						$page = 1;
					}
					$shortcode_output .= '<a class="btn btn-primary button" href="' . home_url( $wp_query->query['pagename'] ) . '/' . ( $page + 1 ) . '">Next</a>';
					$shortcode_output .= '</div>';
				}
			}
			echo '</div>';
		}
		wp_reset_postdata();

		return $shortcode_output;
	}

	/**
	 * Add custom metaboxes
	 *
	 * @return void Registers metabox.
	 */
	public function testimonial_author_metabox() {
		add_meta_box( 'testimonial-author', 'Testimonial Author', array( $this, 'testimonial_callback' ), 'testimonial' );
	}

	/**
	 * Print metabox content
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return  void Prints HTML content.
	 */
	public function testimonial_callback( WP_Post $post ) {
		// Add nonce field to check for later.
		wp_nonce_field( 'testimonial_author_meta', 'testimonial_author_meta_nonce' );

		// Get meta from database.
		$testimonial_author = get_post_meta( $post->ID, 'testimonial_author', true );

		echo '<label for="testimonial_author">Testimonial Author:</label>
        <input type="text" name="testimonial_author" placeholder="John Doe"';
		if ( isset( $testimonial_author ) ) {
			echo ' value="' . esc_attr( $testimonial_author ) . '" '; }
		echo '/>';
	}

	/**
	 * Save metabox metadata
	 *
	 * @param int $post_id WP post ID.
	 *
	 * @return  void Updates post meta.
	 */
	public function save_metabox( int $post_id ) {
		// Bail if not a testimonial post.
		if ( 'testimonial' !== get_post_type() ) {
			return;
		}

		// Bail if autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check for valid nonces.
		if ( ! isset( $_POST['testimonial_author_meta_nonce'] ) || ! wp_verify_nonce( $_POST['testimonial_author_meta_nonce'], 'testimonial_author_meta' ) ) {
			return;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_posts', $post_id ) ) {
			return;
		}

		// Sanitize user input.
		$testimonial_author_sanitized = sanitize_text_field( $_POST['testimonial_author'] );

		// Update the meta fields in database.
		if ( isset( $_POST['testimonial_author'] ) ) {
			update_post_meta( $post_id, 'testimonial_author', $testimonial_author_sanitized );
		}
	}
}
