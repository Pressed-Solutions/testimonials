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
-----

By default, the `show_star` parameter loads the `dashicons` stylesheet. Use the filter `testimonials_enqueue_dashicons` to return `false` to disable it from loading.

The filter `testimonials_star_html` can be used to change the star HTML content (e.g., using an image, a different class, etc.). This string will be printed once for each star.

Changelog
---------

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
 - Add rating taxonomy and `show_rating` parameter
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
