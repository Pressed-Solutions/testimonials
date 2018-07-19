Introduction
============

A basic plugin to display testimonials. See shortcode examples below.

Options
-------

- Show one specific testimonial: `[testimonial id="1"]`; use the post ID as the `id` parameter
- Show only 5 testimonials: `[testimonial posts_per_page="5"]`
- Order by date: `[testimonial orderby="date" order="desc"]`; supports all the [standard WordPress order parameters](https://developer.wordpress.org/reference/classes/wp_query/#order-orderby-parameters)
- Show only testimonials from a given category: `[testimonial tax_taxonomy="post_tag" tax_field="slug" tax_terms="home-page"]`; supports these [standard WordPress taxonomy parameters](https://developer.wordpress.org/reference/classes/wp_query/#taxonomy-parameters), prefixed by `tax_`:
    - `taxonomy` (defaults to “category”)
    - `field`
    - `terms`
    - `operator`
- Show testimonials with full post content: `[testimonial show_content="true"]`
- Show nav buttons: `[testimonial show_paging="true"]`
- Show stars: `[testimonial show_stars="true"]`
- Show ratings: `[testimonial show_rating="true"]`

Filters
-------

By default, the `show_star` parameter loads the `dashicons` stylesheet. Add this to your theme’s `functions.php` to prevent dashicons from loading:

```
add_filter( 'simple_testimonials_enqueue_dashicons', '__return_false' );
```

The filter `simple_testimonials_star_html` can be used to change the star HTML content (e.g., using an image, a different class, etc.). This string will be printed once for each star. For example:

```
/**
 * Modifies testimonial star HTML.
 *
 * @param  {string} $content Default star HTML.
 *
 * @return {string}          Modified star HTML.
 */
function my_custom_star_html( $content ) {
	$content = '<img src="star.png" alt="star" />';
	return $content;
}
add_filter( 'simple_testimonials_star_html', 'my_custom_star_html' );
```

The filter `simple_testimonials_author_html` can be used to modify the author line. For example:

```
/**
 * Modifies testimonial author HTML.
 *
 * @param  {string} $content Default author HTML.
 * @param  {string} $author  Author name.
 *
 * @return {string}          Modified author HTML.
 */
function my_custom_author_html( $content, $author ) {
	$content = '<p class="author modified">Testimonial by ' . esc_attr( $author ) . '</p>';
	return $content;
}
add_filter( 'simple_testimonials_author_html', 'my_custom_author_html', 10, 2 );
```

Actions
-------

Several action hooks are available:

- `simple_testimonials_before_posts`: runs inside the wrapper before any testimonials are displayed
- `simple_testimonials_after_posts`: runs inside the wrapper after all testimonials are displayed
- `simple_testimonials_no_posts`: runs if no testimonials were found; passes the `$shortcode_atts` array

Changelog
---------

### 2.7.1
 - Add action hooks.

### 2.7.0
 - Add `simple_testimonials_author_html` filter.

### 2.6.1
 - Fix some bugs with the `offset` parameter.

### 2.6
 - Add length taxonomy
 - Add `wrapper_class` shortcode parameter

### 2.5
 - Add star rating

### 2.4.3
 - Fix content being output too early

### 2.4.2
 - Remove duplicate rating section

### 2.4.1
 - Correctly parse all input arguments

### 2.4
 - Convert to class-based plugin
 - Add rating taxonomy and `show_rating` shortcode parameter
 - Add `.testimonials.shortcode` wrapper to the entire shortcode block

### 2.3
 - Add `show_paging` parameter

### 2.2
- Add wrapping `<article>` to each testimonial

### 2.1
- Add support for `show_content` parameter

### 2.0
- Add support for `order`, `orderby`, `posts_per_page`, and `taxonomy` parameters

### 1.1
- Improve output display

### 1.0
- Initial plugin
