<?php
/**
 * Plugin Name: Simple Testimonials
 * Plugin URI: https://github.com/Pressed-Solutions/testimonials
 * Description: A plugin to display testimonials with a shortcode
 * Author: AndrewRMinion Design
 * Author URI: http://andrewrminion.com/
 * Version: 2.7.1
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
		add_shortcode( 'testimonials', array( $this, 'shortcode_testimonial' ) );

		// Metabox.
		add_action( 'add_meta_boxes_testimonial', array( $this, 'testimonial_author_metabox' ), 5 );
		add_action( 'save_post_testimonial', array( $this, 'save_metabox' ) );
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
			'taxonomies'          => array( 'category', 'post_tag', 'testimonial_length', 'testimonial_rating' ),
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

		$length_labels = array(
			'name'                       => 'Lengths',
			'singular_name'              => 'Length',
			'menu_name'                  => 'Lengths',
			'all_items'                  => 'All Lengths',
			'parent_item'                => 'Parent Length',
			'parent_item_colon'          => 'Parent Length:',
			'new_item_name'              => 'New Length Name',
			'add_new_item'               => 'Add New Length',
			'edit_item'                  => 'Edit Length',
			'update_item'                => 'Update Length',
			'view_item'                  => 'View Length',
			'separate_items_with_commas' => 'Separate lengths with commas',
			'add_or_remove_items'        => 'Add or remove lengths',
			'choose_from_most_used'      => 'Choose from the most used',
			'popular_items'              => 'Popular Lengths',
			'search_items'               => 'Search Lengths',
			'not_found'                  => 'Not Found',
		);
		$length_args   = array(
			'labels'            => $length_labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'rewrite'           => false,
		);
		register_taxonomy( 'testimonial_length', array( 'testimonial' ), $length_args );
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
				'offset'         => 0,
				'order'          => 'DESC',
				'orderby'        => 'date',
				'tax_taxonomy'   => 'category',
				'tax_field'      => 'term_id',
				'tax_terms'      => null,
				'tax_operator'   => 'IN',
				'show_content'   => false,
				'show_rating'    => false,
				'show_stars'     => false,
				'show_paging'    => false,
				'wrapper_class'  => '',
			), $atts
		);

		$remove_from_query = array( 'show_content', 'show_rating', 'show_stars', 'show_paging', 'wrapper_class' );
		foreach ( $remove_from_query as $key ) {
			$$key = $shortcode_atts[ $key ];
			unset( $shortcode_atts[ $key ] );
		}

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

			// Remove keys from array so they’re not merged into the WP query.
			unset( $shortcode_atts['tax_taxonomy'] );
			unset( $shortcode_atts['tax_field'] );
			unset( $shortcode_atts['tax_terms'] );
			unset( $shortcode_atts['tax_operator'] );
		}

		$args = wp_parse_args( $shortcode_atts, $args );

		$testimonial_query = new WP_Query( $args );

		ob_start();
		if ( $testimonial_query->have_posts() ) {
			echo '<div class="testimonials shortcode ' . esc_attr( $wrapper_class ) . '">';

			/**
			 * Runs before posts are displayed.
			 *
			 * @since  2.7.1
			 */
			do_action( 'simple_testimonials_before_posts' );

			while ( $testimonial_query->have_posts() ) {
				$testimonial_query->the_post();
				$testimonial_author = get_post_meta( get_the_ID(), 'testimonial_author', true );

				echo '<article class="' . esc_attr( implode( ' ', get_post_class( 'shortcode' ) ) ) . '"><h3><a href="' . esc_url( get_permalink() ) . '">' . get_the_title() . '</a></h3>';

				if ( $show_content ) {
					echo wp_kses_post( apply_filters( 'the_content', get_the_content() ) );
				} else {
					the_excerpt();
				}

				if ( $show_rating ) {
					echo '<p class="rating">' . get_the_term_list( get_the_ID(), 'testimonial_rating' ) . '</p>';
				}

				if ( $show_stars ) {
					// By default, enqueue dashicons stylesheet.
					if ( apply_filters( 'simple_testimonials_enqueue_dashicons', true ) ) {
						wp_enqueue_style( 'dashicons' );
					}

					$stars = get_post_meta( get_the_ID(), 'star_rating', true );
					echo '<p class="stars" data-value="' . esc_attr( $stars ) . '">' . wp_kses_post( str_repeat( apply_filters( 'simple_testimonials_star_html', '<span class="dashicons-before dashicons-star-filled"></span>' ), (int) $stars ) ) . '</p>';
				}

				if ( ! empty( $testimonial_author ) ) {
					/**
					 * Modify the author HTML.
					 *
					 * @since 2.7.0
					 *
					 * @param  string $content            Full HTML content.
					 * @param  string $testimonial_author Author name.
					 */
					echo wp_kses_post( apply_filters( 'simple_testimonials_author_html', '<p class="author"><em>&mdash;' . $testimonial_author . '</em></p>', $testimonial_author ) );
				}
				echo '</article>';
			}

			// Paging.
			if ( $show_paging ) {
				echo '<div class="paging">';
				if ( $page > 1 ) {
					echo '<a class="btn btn-primary button" href="' . esc_url( home_url( $wp_query->query['pagename'] ) ) . '/' . esc_attr( $page - 1 ) . '">Previous</a> ';
				}
				if ( ( $page < $testimonial_query->max_num_pages ) && ( 10 === $testimonial_query->post_count ) ) {
					if ( '' === $page ) {
						$page = 1;
					}
					echo '<a class="btn btn-primary button" href="' . esc_url( home_url( $wp_query->query['pagename'] ) ) . '/' . esc_attr( $page + 1 ) . '">Next</a>';
					echo '</div>';
				}
			}

			/**
			 * Runs after posts are displayed.
			 *
			 * @since  2.7.1
			 */
			do_action( 'simple_testimonials_after_posts' );

			echo '</div>';
		} else {

			/**
			 * Runs if no posts were found.
			 *
			 * @since  2.7.1
			 *
			 * @param  array $shortcode_atts Shortcode attributes.
			 */
			do_action( 'simple_testimonials_no_posts', $shortcode_atts );

		}
		wp_reset_postdata();

		return ob_get_clean();
	}

	/**
	 * Add custom metaboxes
	 *
	 * @return void Registers metabox.
	 */
	public function testimonial_author_metabox() {
		add_meta_box( 'testimonial-author', 'Testimonial Information', array( $this, 'testimonial_metabox_content' ), 'testimonial', 'normal', 'high' );
	}

	/**
	 * Print metabox content
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return  void Prints HTML content.
	 */
	public function testimonial_metabox_content( WP_Post $post ) {
		// Add nonce field to check for later.
		wp_nonce_field( 'testimonial_author_meta', 'testimonial_author_meta_nonce' );

		// Get meta from database.
		$testimonial_author = get_post_meta( $post->ID, 'testimonial_author', true );
		$star_rating        = get_post_meta( $post->ID, 'star_rating', true );
		?>

		<p>
			<label for="testimonial_author">Testimonial Author:</label>
			<input type="text" name="testimonial_author" placeholder="John Doe" value="<?php echo esc_attr( $testimonial_author ); ?>" />
		</p>

		<p>
			<label for="star_rating">Rating:</label><br/>
			<label><input type="radio" name="star_rating" value="1" <?php checked( 1, $star_rating ); ?> /><span class="dashicons-before dashicons-star-filled"></span></label><br/>
			<label><input type="radio" name="star_rating" value="2" <?php checked( 2, $star_rating ); ?> /><span class="dashicons-before dashicons-star-filled"><span class="dashicons-before dashicons-star-filled"></span></label><br/>
			<label><input type="radio" name="star_rating" value="3" <?php checked( 3, $star_rating ); ?> /><span class="dashicons-before dashicons-star-filled"><span class="dashicons-before dashicons-star-filled"><span class="dashicons-before dashicons-star-filled"></span></label><br/>
			<label><input type="radio" name="star_rating" value="4" <?php checked( 4, $star_rating ); ?> /><span class="dashicons-before dashicons-star-filled"><span class="dashicons-before dashicons-star-filled"><span class="dashicons-before dashicons-star-filled"><span class="dashicons-before dashicons-star-filled"></span></label><br/>
			<label><input type="radio" name="star_rating" value="5" <?php checked( 5, $star_rating ); ?> /><span class="dashicons-before dashicons-star-filled"><span class="dashicons-before dashicons-star-filled"><span class="dashicons-before dashicons-star-filled"><span class="dashicons-before dashicons-star-filled"><span class="dashicons-before dashicons-star-filled"></span></label><br/>
		</p>
		<?php
	}

	/**
	 * Save metabox metadata
	 *
	 * @param int $post_id WP post ID.
	 *
	 * @return  void Updates post meta.
	 */
	public function save_metabox( int $post_id ) {
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

		if ( isset( $_POST['testimonial_author'] ) ) {
			// Sanitize user input.
			$testimonial_author_sanitized = sanitize_text_field( $_POST['testimonial_author'] );
			$star_rating_sanitized        = sanitize_text_field( $_POST['star_rating'] );

			// Update the meta fields in database.
			update_post_meta( $post_id, 'testimonial_author', $testimonial_author_sanitized );
			update_post_meta( $post_id, 'star_rating', $star_rating_sanitized );
		}
	}
}
