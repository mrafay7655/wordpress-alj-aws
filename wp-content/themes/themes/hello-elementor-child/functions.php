<?php
// Enqueue parent and child theme styles
function hello_elementor_child_enqueue_styles()
{
    // Get the last modified time of the child theme's style.css
    $child_style_version = filemtime(get_stylesheet_directory() . '/style.css');
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array('parent-style'),
        $child_style_version
    );
    wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/js/custom.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles');

// Register Sidebars
if (function_exists('register_sidebar')) {
    // Register the first sidebar
    register_sidebar(array(
        'name' => 'Komatsu Filter Sidebar',
        'id' => 'sidebar-1',
        'description' => 'The first sidebar on the site',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

    // Register the second sidebar
    register_sidebar(array(
        'name' => 'Events Sidebar',
        'id' => 'sidebar-2',
        'description' => 'The second sidebar on the site',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

    // Register additional sidebars as needed
    register_sidebar(array(
        'name' => 'Case Studies',
        'id' => 'sidebar-3',
        'description' => 'The third sidebar on the site',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));

    // Register Posts sidebar
    register_sidebar(array(
        'name' => 'Posts Sidebar',
        'id' => 'sidebar-4',
        'description' => 'This Sidebar appear on single posts',
        'before_widget' => '<div id="posts-sidebar-widget-%1$s" class="widget posts-sidebar %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));
}
;

// Enable SVG Uploads to media 
function restrict_svg_uploads($mimes)
{
    if (current_user_can('administrator')) {
        $mimes['svg'] = 'image/svg+xml';
    }
    return $mimes;
}
add_filter('upload_mimes', 'restrict_svg_uploads');


/**
 * Disable all automatic WordPress image sizes.
 */
add_action('init', function () {
    // Disable core WP default sizes
    update_option('thumbnail_size_w', 0);
    update_option('thumbnail_size_h', 0);
    update_option('medium_size_w', 0);
    update_option('medium_size_h', 0);
    update_option('medium_large_size_w', 0);
    update_option('medium_large_size_h', 0);
    update_option('large_size_w', 0);
    update_option('large_size_h', 0);

    // Ensure WP does not generate them
    add_filter('intermediate_image_sizes_advanced', function ($sizes) {
        return [];
    });

    // Remove theme-registered sizes
    $registered = get_intermediate_image_sizes();
    foreach ($registered as $size) {
        remove_image_size($size);
    }

    // Remove plugin-added sizes
    global $_wp_additional_image_sizes;
    $_wp_additional_image_sizes = [];
});



// Show Machinery, Power Products and Commercial Vehicles first level subcategories with logo on category page 
function ah_product_category_cards_with_logo()
{

    // Original parent category slug (main language)
    $parent_term = get_term_by(
        'slug',
        'heavy-machinery-and-power-products',
        'product_cat'
    );

    if (!$parent_term) {
        return '';
    }

    // WPML translated parent
    if (function_exists('icl_object_id')) {
        $translated_parent_id = icl_object_id(
            $parent_term->term_id,
            'product_cat',
            true
        );
    } else {
        $translated_parent_id = $parent_term->term_id;
    }

    // Get first-level subcategories
    $terms = get_terms([
        'taxonomy' => 'product_cat',
        'parent' => $translated_parent_id,
        'hide_empty' => false,
    ]);

    if (empty($terms) || is_wp_error($terms)) {
        return '';
    }

    /**
     * Sort terms:
     * 1. Categories WITH valid ACF order (order > 0) → sorted by order
     * 2. Categories WITHOUT order or order = 0 → default WP ordering
     */
    usort($terms, function ($a, $b) {

        $order_a = function_exists('get_field')
            ? (int) get_field('order', 'product_cat_' . $a->term_id)
            : 0;

        $order_b = function_exists('get_field')
            ? (int) get_field('order', 'product_cat_' . $b->term_id)
            : 0;

        // If both have order > 0 → sort by order
        if ($order_a > 0 && $order_b > 0) {
            return $order_a - $order_b;
        }

        // If only A has order → A comes first
        if ($order_a > 0) {
            return -1;
        }

        // If only B has order → B comes first
        if ($order_b > 0) {
            return 1;
        }

        // Neither has order → default fallback (term_id ASC)
        return $a->term_id - $b->term_id;
    });

    ob_start();
    ?>
    <div class="product-category-cards">
        <?php foreach ($terms as $term):

            // WooCommerce category thumbnail
            $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
            $thumbnail = $thumb_id ? wp_get_attachment_url($thumb_id) : '';

            // ACF logo field
            $logo = function_exists('get_field')
                ? get_field('logo', 'product_cat_' . $term->term_id)
                : '';

            $logo_url = '';
            if (is_array($logo) && isset($logo['url'])) {
                $logo_url = $logo['url'];
            } elseif (is_string($logo) && !empty($logo)) {
                $logo_url = $logo;
            }
            ?>
            <a href="<?php echo esc_url(get_term_link($term)); ?>" class="product-category-card">

                <?php if ($thumbnail): ?>
                    <div class="category-thumbnail">
                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($term->name); ?>">
                    </div>
                <?php endif; ?>

                <?php if ($logo_url): ?>
                    <div class="category-logo">
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($term->name); ?> logo">
                    </div>
                <?php endif; ?>

            </a>
        <?php endforeach; ?>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode('product_category_cards', 'ah_product_category_cards_with_logo');






// Add body classes based on product categories
add_filter('body_class', function ($classes) {

    // ---------- CATEGORY ARCHIVE ----------
    if (is_product_category()) {

        $term = get_queried_object();

        if ($term && !is_wp_error($term)) {

            // Always add current category
            $classes[] = sanitize_html_class($term->slug);

            // Get ancestors (ordered from closest → top)
            $ancestors = get_ancestors($term->term_id, 'product_cat');

            if (!empty($ancestors)) {

                // Top-level category
                $top_level_id = end($ancestors);
                $top_term = get_term($top_level_id, 'product_cat');
                if ($top_term && !is_wp_error($top_term)) {
                    $classes[] = sanitize_html_class($top_term->slug);
                }

                // Second-level category (only if exists)
                if (count($ancestors) >= 2) {
                    $second_level_id = $ancestors[count($ancestors) - 2];
                    $second_term = get_term($second_level_id, 'product_cat');
                    if ($second_term && !is_wp_error($second_term)) {
                        $classes[] = sanitize_html_class($second_term->slug);
                    }
                }
            }
        }
    }

    // ---------- SINGLE PRODUCT ----------
    elseif (is_product()) {

        global $post;
        $terms = wp_get_post_terms($post->ID, 'product_cat');

        if (!empty($terms) && !is_wp_error($terms)) {

            foreach ($terms as $term) {

                // Add current product category
                $classes[] = sanitize_html_class($term->slug);

                $ancestors = get_ancestors($term->term_id, 'product_cat');

                if (!empty($ancestors)) {

                    // Top-level
                    $top_level_id = end($ancestors);
                    $top_term = get_term($top_level_id, 'product_cat');
                    if ($top_term && !is_wp_error($top_term)) {
                        $classes[] = sanitize_html_class($top_term->slug);
                    }

                    // Second-level
                    if (count($ancestors) >= 2) {
                        $second_level_id = $ancestors[count($ancestors) - 2];
                        $second_term = get_term($second_level_id, 'product_cat');
                        if ($second_term && !is_wp_error($second_term)) {
                            $classes[] = sanitize_html_class($second_term->slug);
                        }
                    }
                }
            }
        }
    }

    return array_unique($classes);
});




//Show Related Top Level Categories
add_shortcode('other_main_categories', 'other_main_categories_shortcode');

function other_main_categories_shortcode()
{

    if (!is_product_category() && !is_product()) {
        return '';
    }

    // 🔥 MAIN CATEGORY PRIORITY (top wins)
    $priority_slugs = [
        'rental-solutions-and-commercial-vehicles',
        'komatsu',
        'powerscreen',
        'material-handling-warehousing-and-industrial-products',
        'teksan',
        'mds',
        'terex',
        'man',
    ];

    $excluded_slugs = ['uncategorized'];
    $active_top_parent_id = null;

    /* -----------------------------
     * CATEGORY PAGE
     * ----------------------------- */
    if (is_product_category()) {

        $current_term = get_queried_object();
        if (!$current_term || is_wp_error($current_term)) {
            return '';
        }

        $ancestors = get_ancestors($current_term->term_id, 'product_cat');
        $active_top_parent_id = !empty($ancestors)
            ? end($ancestors)
            : $current_term->term_id;
    }

    /* -----------------------------
     * PRODUCT PAGE (FIXED)
     * ----------------------------- */
    if (is_product()) {

        $terms = wp_get_post_terms(get_the_ID(), 'product_cat');
        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        // Resolve active main category via PRIORITY
        foreach ($priority_slugs as $slug) {

            foreach ($terms as $term) {

                if (
                    $term->slug === $slug || term_is_ancestor_of(
                        get_term_by('slug', $slug, 'product_cat')->term_id,
                        $term->term_id,
                        'product_cat'
                    )
                ) {
                    $ancestors = get_ancestors($term->term_id, 'product_cat');
                    $active_top_parent_id = !empty($ancestors)
                        ? end($ancestors)
                        : $term->term_id;
                    break 2;
                }
            }
        }
    }

    if (!$active_top_parent_id) {
        return '';
    }

    /* -----------------------------
     * OUTPUT OTHER MAIN CATEGORIES
     * ----------------------------- */

    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'parent' => 0,
        'hide_empty' => false,
    ]);

    if (empty($categories) || is_wp_error($categories)) {
        return '';
    }

    ob_start();

    echo '<div class="main-category-grid">';

    foreach ($categories as $category) {

        // ❌ Hide active main category
        if ($category->term_id === $active_top_parent_id) {
            continue;
        }

        // ❌ Hide excluded
        if (in_array($category->slug, $excluded_slugs, true)) {
            continue;
        }

        $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
        $image_url = $thumbnail_id
            ? wp_get_attachment_url($thumbnail_id)
            : wc_placeholder_img_src();

        echo '<a href="' . esc_url(get_term_link($category)) . '" class="category-card">';
        echo '<div class="category-card-image">';
        echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($category->name) . '">';
        echo '</div>';
        echo '<div class="category-card-content">';
        echo '<h3>' . esc_html($category->name) . '</h3>';
        if (!empty($category->description)) {
            echo '<p>' . esc_html(wp_strip_all_tags($category->description)) . '</p>';
        }
        echo '</div>';
        echo '</a>';
    }

    echo '</div>';

    return ob_get_clean();
}



// Shortcode to display ACF 'specs' fields on product page with titles on Komatsu
function komatsu_products_shortcode()
{

    global $product;

    if (!$product || !is_a($product, 'WC_Product')) {
        $product_id = get_queried_object_id();
        $product = wc_get_product($product_id);
    } else {
        $product_id = $product->get_id();
    }

    if (!$product_id) {
        return '';
    }

    /* ----------------------------------
     * WPML LANGUAGE HANDLING
     * ---------------------------------- */
    $lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'en';

    $specs_label = ($lang === 'ar') ? 'المواصفات الفنية' : 'Technical Specification';
    $desc_label = ($lang === 'ar') ? 'الوصف' : 'Description';

    $output = '<div class="custom-product-tabs">';

    /* ----------------------------------
     * TABS NAV
     * ---------------------------------- */
    $output .= '<ul class="tabs-nav">';
    $output .= '<li class="active" data-tab="tab-specs">' . esc_html($specs_label) . '</li>';

    $description = $product->get_description();
    if ($description) {
        $output .= '<li class="description" data-tab="tab-description">' . esc_html($desc_label) . '</li>';
    }

    $output .= '</ul>';

    /* ----------------------------------
     * TABS CONTENT
     * ---------------------------------- */
    $output .= '<div class="tabs-content">';

    /* ---------- Technical Specifications ---------- */
    $output .= '<div id="tab-specs" class="tab active product-specs">';

    if (have_rows('specs', $product_id)) {
        while (have_rows('specs', $product_id)) {
            the_row();

            $title = get_sub_field('title');
            $value = get_sub_field('description');

            if (empty($title) && empty($value)) {
                continue;
            }

            $output .= '<div class="spec-item">';
            if ($title) {
                $output .= '<p class="spec-title">' . esc_html($title) . '</p>';
            }
            if ($value) {
                $output .= '<p class="spec-value">' . esc_html($value) . '</p>';
            }
            $output .= '</div>';
        }
    } else {
        $output .= '<p>' . esc_html__('No technical specifications available.', 'textdomain') . '</p>';
    }

    $output .= '</div>';

    /* ---------- Description Tab ---------- */
    if ($description) {
        $output .= '<div id="tab-description" class="tab ">';
        $output .= wp_kses_post(wpautop($description));
        $output .= '</div>';
    }

    $output .= '</div>'; // tabs-content
    $output .= '</div>'; // custom-product-tabs

    /* ----------------------------------
     * TAB SWITCH JS
     * ---------------------------------- */
    $output .= "
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.custom-product-tabs .tabs-nav li');
        const contents = document.querySelectorAll('.custom-product-tabs .tab');

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));

                this.classList.add('active');
                const target = document.getElementById(this.getAttribute('data-tab'));
                if (target) target.classList.add('active');
            });
        });
    });
    </script>
    ";

    return $output;
}
add_shortcode('komatsu_products_fields', 'komatsu_products_shortcode');




// Shortcode to display ACF 'specs' fields on product page with titles on Komatsu Related Products Grid
// function komatsu_related_products_shortcode() {
//     global $product;

//     if ( ! $product ) return '';

//     $product_id = $product->get_id();

//     // Check repeater rows
//     if ( ! have_rows('specs_on_product_card', $product_id) ) {
//         return '';
//     }

//     $output = '<div class="product-specs-related">';

//     while ( have_rows('specs_on_product_card', $product_id) ) {
//         the_row();

//         $title       = get_sub_field('title');
//         $description = get_sub_field('description');

//         if ( empty($title) && empty($description) ) {
//             continue;
//         }

//         $output .= '<div class="spec-item">';

//         if ( $title ) {
//             $output .= '<p class="spec-title">' . esc_html($title) . '</p>';
//         }

//         if ( $description ) {
//             $output .= '<p class="spec-value">' . esc_html($description) . '</p>';
//         }

//         $output .= '</div>';
//     }

//     $output .= '</div>';

//     return $output;
// }
// add_shortcode('komatsu_related_products_fields', 'komatsu_related_products_shortcode');


// Show Products Categories Subcategories Grid on Product Page with ACF Image and Title with Link 
add_shortcode('conditional_subcategories_grid', 'conditional_subcategories_grid');

function conditional_subcategories_grid($atts = [])
{

    $atts = shortcode_atts([
        'category' => '',
        'category_id' => '',
    ], $atts);

    // Categories to exclude (by name)
    $excluded_names = ['Solutions', 'Warehouse'];
    $excluded_slugs = array_map('sanitize_title', $excluded_names);

    $current_term = null;

    /* ----------------------------------
     * 1. MANUAL OVERRIDE
     * ---------------------------------- */
    if ($atts['category_id']) {
        $current_term = get_term((int) $atts['category_id'], 'product_cat');
    } elseif ($atts['category']) {
        $current_term = get_term_by(
            'slug',
            sanitize_title($atts['category']),
            'product_cat'
        );
    }

    /* ----------------------------------
     * 2. CATEGORY ARCHIVE
     * ---------------------------------- */
    if (!$current_term && is_tax('product_cat')) {
        $current_term = get_queried_object();
    }

    /* ----------------------------------
     * 3. SINGLE PRODUCT PAGE
     * ---------------------------------- */
    if (!$current_term && is_singular('product')) {

        $product_id = get_queried_object_id();
        if (!$product_id)
            return '';

        if (function_exists('icl_object_id')) {
            $product_id = icl_object_id($product_id, 'product', false);
        }

        $terms = wp_get_post_terms($product_id, 'product_cat');
        if (empty($terms) || is_wp_error($terms))
            return '';

        // Remove excluded categories
        $terms = array_filter($terms, function ($term) use ($excluded_slugs) {
            return !in_array($term->slug, $excluded_slugs, true);
        });

        if (empty($terms))
            return '';

        // Deepest category first
        usort($terms, function ($a, $b) {
            return count(get_ancestors($b->term_id, 'product_cat'))
                - count(get_ancestors($a->term_id, 'product_cat'));
        });

        $current_term = $terms[0];
    }

    if (!$current_term || is_wp_error($current_term)) {
        return '';
    }

    // Stop if current term itself is excluded
    if (in_array($current_term->slug, $excluded_slugs, true)) {
        return '';
    }

    /* ----------------------------------
     * 4. GET CATEGORIES (NO EMPTY)
     * ---------------------------------- */

    $subcategories = get_terms([
        'taxonomy' => 'product_cat',
        'parent' => $current_term->term_id,
        'hide_empty' => true,
        'exclude' => array_map(function ($slug) {
            $term = get_term_by('slug', $slug, 'product_cat');
            return $term ? $term->term_id : 0;
        }, $excluded_slugs),
    ]);

    // If no children → siblings
    if (empty($subcategories)) {
        $subcategories = get_terms([
            'taxonomy' => 'product_cat',
            'parent' => $current_term->parent,
            'hide_empty' => true,
            'exclude' => array_map(function ($slug) {
                $term = get_term_by('slug', $slug, 'product_cat');
                return $term ? $term->term_id : 0;
            }, $excluded_slugs),
        ]);
    }

    if (empty($subcategories) || is_wp_error($subcategories)) {
        return '';
    }

    /* ----------------------------------
     * 5. SORT BY ACF ORDER (WITH FALLBACK)
     * ---------------------------------- */
    usort($subcategories, function ($a, $b) {

        $order_a = function_exists('get_field')
            ? (int) get_field('order', 'product_cat_' . $a->term_id)
            : 0;

        $order_b = function_exists('get_field')
            ? (int) get_field('order', 'product_cat_' . $b->term_id)
            : 0;

        if ($order_a > 0 && $order_b > 0)
            return $order_a - $order_b;
        if ($order_a > 0)
            return -1;
        if ($order_b > 0)
            return 1;

        return $a->term_id - $b->term_id;
    });

    ob_start();

    echo '<div class="subcategory-grid">';

    foreach ($subcategories as $category) {

        if ($category->slug === 'uncategorized')
            continue;

        $image = function_exists('get_field')
            ? get_field(
                'category_image_single_product_page',
                'product_cat_' . $category->term_id
            )
            : '';

        $image_url = '';
        if ($image) {
            $image_url = is_array($image) ? $image['url'] : $image;
        }

        echo '<a href="' . esc_url(get_term_link($category)) . '" class="subcategory-card">';

        if ($image_url) {
            echo '<div class="subcategory-image">';
            echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($category->name) . '">';
            echo '</div>';
        }

        echo '<h3 class="subcategory-title">' . esc_html($category->name) . '</h3>';

        echo '</a>';
    }

    echo '</div>';

    return ob_get_clean();
}


// Render Headers Conditionally Functionality
add_filter('elementor/frontend/the_content', function ($content) {

    if (is_admin())
        return $content;

    $global_header_id = 232; // Default global header (English)

    // WPML: If Arabic, change the global header to the Arabic one
    if (function_exists('apply_filters')) {
        $current_lang = apply_filters('wpml_current_language', null);
        if ($current_lang === 'ar') {
            $global_header_id = 3619;
        }
    }

    $category_headers = [
        'rental-solutions-and-commercial-vehicles' => 1155,
        'komatsu' => 637,
        'powerscreen' => 915,
        'material-handling-warehousing-and-industrial-products' => 1023,
        'teksan' => 1198,
        'mds' => 1267,
        'terex' => 1338,
        'man' => 1413,
    ];

    // WPML: Map Arabic translated headers if needed
    if (!empty($current_lang) && $current_lang === 'ar') {
        $category_headers = [
            'rental-solutions-and-commercial-vehicles' => 1155, // Replace with Arabic template IDs if available
            'komatsu' => 637,
            'powerscreen' => 915,
            'material-handling-warehousing-and-industrial-products' => 1023,
            'teksan' => 1198,
            'mds' => 1267,
            'terex' => 1338,
            'man' => 1413,
        ];
    }

    foreach ($category_headers as $slug => $header_id) {

        $is_category = false;

        if (is_product_category()) {
            $term = get_queried_object();
            $cat_term = get_term_by('slug', $slug, 'product_cat');

            // WPML: Get translated term
            if (function_exists('icl_object_id') && $term && $cat_term) {
                $translated_term_id = icl_object_id($cat_term->term_id, 'product_cat', false, $current_lang);
            } else {
                $translated_term_id = $cat_term ? $cat_term->term_id : 0;
            }

            if (
                $term && $translated_term_id && (
                    $term->term_id === $translated_term_id ||
                    term_is_ancestor_of($translated_term_id, $term->term_id, 'product_cat')
                )
            ) {
                $is_category = true;
            }
        }

        if (is_product() && has_term($slug, 'product_cat')) {
            $is_category = true;
        }

        if ($is_category && strpos($content, 'data-elementor-id="' . $global_header_id . '"') !== false) {
            return '';
        }
    }

    return $content;

}, 5);


add_action('wp_body_open', function () {

    if (is_admin())
        return;

    $global_header_id = 232; // Default English header

    // WPML: Arabic global header
    if (function_exists('apply_filters')) {
        $current_lang = apply_filters('wpml_current_language', null);
        if ($current_lang === 'ar') {
            $global_header_id = 3619;
        }
    }

    $category_headers = [
        'rental-solutions-and-commercial-vehicles' => 1155,
        'komatsu' => 637,
        'powerscreen' => 915,
        'material-handling-warehousing-and-industrial-products' => 1023,
        'teksan' => 1198,
        'mds' => 1267,
        'terex' => 1338,
        'man' => 1413,
    ];

    // WPML: Map Arabic translated category templates if needed
    if (!empty($current_lang) && $current_lang === 'ar') {
        $category_headers = [
            'rental-solutions-and-commercial-vehicles' => 1155, // Replace with Arabic template IDs if available
            'komatsu' => 637,
            'powerscreen' => 915,
            'material-handling-warehousing-and-industrial-products' => 1023,
            'teksan' => 1198,
            'mds' => 1267,
            'terex' => 1338,
            'man' => 1413,
        ];
    }

    foreach ($category_headers as $slug => $header_id) {

        $is_category = false;

        if (is_product_category()) {
            $term = get_queried_object();
            $cat_term = get_term_by('slug', $slug, 'product_cat');

            // WPML: Get translated term
            if (function_exists('icl_object_id') && $term && $cat_term) {
                $translated_term_id = icl_object_id($cat_term->term_id, 'product_cat', false, $current_lang);
            } else {
                $translated_term_id = $cat_term ? $cat_term->term_id : 0;
            }

            if (
                $term && $translated_term_id && (
                    $term->term_id === $translated_term_id ||
                    term_is_ancestor_of($translated_term_id, $term->term_id, 'product_cat')
                )
            ) {
                $is_category = true;
            }
        }

        if (is_product() && has_term($slug, 'product_cat')) {
            $is_category = true;
        }

        if ($is_category) {
            echo do_shortcode('[elementor-template id="' . $header_id . '"]');
            break;
        }
    }

}, 3);




// Pagination Count 
add_action('pre_get_posts', function ($query) {
    if (!is_admin() && $query->is_main_query() && is_product_category() && !wp_doing_ajax()) {
        $query->set('posts_per_page', 12);
    }
});



/**
 * Register a hierarchical taxonomy for Pages.
 *
 * Acts like Categories but works on the `page` post type.
 */

function register_page_category_taxonomy(): void
{
    $labels = [
        'name' => 'Page Categories',
        'singular_name' => 'Page Category',
        'search_items' => 'Search Page Categories',
        'all_items' => 'All Page Categories',
        'parent_item' => 'Parent Page Category',
        'parent_item_colon' => 'Parent Page Category:',
        'edit_item' => 'Edit Page Category',
        'update_item' => 'Update Page Category',
        'add_new_item' => 'Add New Page Category',
        'new_item_name' => 'New Page Category Name',
        'menu_name' => 'Page Categories',
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'hierarchical' => true, // Category behavior
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true, // Gutenberg + API
        'rewrite' => [
            'slug' => 'page-category',
            'hierarchical' => true,
        ],
        'query_var' => true,
    ];

    register_taxonomy(
        'page_category',
        ['page'],
        $args
    );
}

add_action('init', 'register_page_category_taxonomy');



/**
 * Add custom body class when a Page is assigned
 * to "Materials Handling, Warehousing and Industrial Products" category.
 */

function add_materials_handling_body_class(array $classes): array
{
    if (!is_page()) {
        return $classes;
    }

    $taxonomy = 'page_category';
    $term_slug = 'material-handling-warehousing-and-industrial-products';

    if (has_term($term_slug, $taxonomy)) {
        $classes[] = $term_slug;
    }

    return $classes;
}

add_filter('body_class', 'add_materials_handling_body_class');



// function add_custom_class_based_on_arabic_category( $classes ) {
//     // Define the Arabic category slug exactly as it appears in your dashboard
//     $target_category = 'مناولة-المواد-والتخزين-والمنتجات-الص';

//     // Check if the current post has this category assigned
//     if ( has_category( $target_category ) ) {
//         $classes[] = 'material-handling-warehousing-and-industrial-products';
//     }

//     return $classes;
// }
// add_filter( 'body_class', 'add_custom_class_based_on_arabic_category' );










add_filter('wpsl_meta_box_fields', 'custom_meta_box_fields');

function custom_meta_box_fields($meta_fields)
{

    $meta_fields[__('Additional Information', 'wpsl')] = array(
        'phone' => array(
            'label' => __('Tel', 'wpsl')
        ),
        'fax' => array(
            'label' => __('Fax', 'wpsl')
        ),
        'email' => array(
            'label' => __('Email', 'wpsl')
        ),
        'url' => array(
            'label' => __('Url', 'wpsl')
        ),
        'alternate_marker_url' => array(
            'label' => __('Marker Url', 'wpsl')
        )
    );

    return $meta_fields;
}


add_filter('wpsl_cpt_info_window_meta_fields', 'custom_cpt_info_window_meta_fields', 10, 2);

function custom_cpt_info_window_meta_fields($meta_fields, $store_id)
{

    // Your existing line for markers
    $meta_fields['alternateMarkerUrl'] = get_post_meta($store_id, 'wpsl_alternate_marker_url', true);

    // Add these two lines to fetch phone and email
    $meta_fields['phone'] = get_post_meta($store_id, 'wpsl_phone', true);
    $meta_fields['email'] = get_post_meta($store_id, 'wpsl_email', true);

    return $meta_fields;
}






// Updated shortcode to accept a product ID
function komatsu_related_products_shortcode($atts = array())
{
    global $product;

    $product_id = !empty($atts['id']) ? intval($atts['id']) : ($product ? $product->get_id() : 0);

    if (!$product_id)
        return '';

    if (!have_rows('specs_on_product_card', $product_id))
        return '';

    $output = '<div class="product-specs-related">';

    while (have_rows('specs_on_product_card', $product_id)) {
        the_row();

        $title = get_sub_field('title');
        $description = get_sub_field('description');

        if (empty($title) && empty($description))
            continue;

        $output .= '<div class="spec-item">';

        if ($title) {
            $output .= '<p class="spec-title">' . esc_html($title) . '</p>';
        }

        if ($description) {
            $output .= '<p class="spec-value">' . esc_html($description) . '</p>';
        }

        $output .= '</div>';
    }

    $output .= '</div>';

    return $output;
}
add_shortcode('komatsu_related_products_fields', 'komatsu_related_products_shortcode');


// Related products carousel – deepest subcategory only
function related_subcategory_carousel_shortcode()
{
    global $product;

    if (!$product)
        return '';

    $product_id = $product->get_id();
    $unique_id = 'related-products-' . $product_id;

    // Get all product categories
    $terms = wp_get_post_terms($product_id, 'product_cat');
    if (empty($terms) || is_wp_error($terms))
        return '';

    // Find deepest category
    $deepest = null;
    $max_depth = -1;

    foreach ($terms as $term) {
        $depth = 0;
        $parent = $term->parent;

        while ($parent != 0) {
            $depth++;
            $parent_term = get_term($parent, 'product_cat');
            if (!$parent_term || is_wp_error($parent_term))
                break;
            $parent = $parent_term->parent;
        }

        if ($depth > $max_depth) {
            $max_depth = $depth;
            $deepest = $term;
        }
    }

    if (!$deepest)
        return '';

    $subcategory_id = $deepest->term_id;

    // WPML translate category
    if (function_exists('icl_object_id')) {
        $subcategory_id = icl_object_id($subcategory_id, 'product_cat', true);
    }

    // Get related products (exclude current)
    $product_ids = get_posts(array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post__not_in' => array($product_id),
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $subcategory_id,
                'include_children' => false,
            ),
        ),
        'orderby' => array(
            'menu_order' => 'ASC',
            'date' => 'ASC',
        ),
        'order' => 'ASC',
        'fields' => 'ids',
        'suppress_filters' => false, // WPML safe
    ));

    $has_related_products = !empty($product_ids);

    // Enqueue Swiper only if there are products
    if ($has_related_products) {
        wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css');
        wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', array(), null, true);
    }

    ob_start();

    // If no related products, output JS to hide parent .related section
    if (!$has_related_products): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var relatedSection = document.querySelector(".related");
                if (relatedSection) {
                    relatedSection.classList.add("hidden");
                }
            });
        </script>
        <?php
        return ob_get_clean();
    endif;
    ?>

    <!-- Related products carousel -->
    <div class="productsGridGlobalStyled related-products swiper <?php echo esc_attr($unique_id); ?>" <?php echo (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'ar') ? 'dir="rtl"' : ''; ?>>
        <div class="swiper-wrapper">

            <?php foreach ($product_ids as $prod_id):
                $wc_product = wc_get_product($prod_id);
                if (!$wc_product)
                    continue;
                ?>
                <div class="swiper-slide">
                    <div class="product-card card">

                        <!-- Product Image -->
                        <div class="elementor-widget-theme-post-featured-image">
                            <a href="<?php echo esc_url(get_permalink($prod_id)); ?>">
                                <?php echo get_the_post_thumbnail($prod_id, 'medium'); ?>
                            </a>
                        </div>

                        <!-- Product Title -->
                        <h3 class="product-title">
                            <a href="<?php echo esc_url(get_permalink($prod_id)); ?>">
                                <?php echo esc_html(get_the_title($prod_id)); ?>
                            </a>
                        </h3>

                        <!-- Specs shortcode -->
                        <?php echo do_shortcode('[komatsu_related_products_fields id="' . $prod_id . '"]'); ?>

                        <!-- Elementor Button -->
                        <div class="elementor-element elementor-widget elementor-widget-button">
                            <a class="elementor-button elementor-button-link elementor-size-sm"
                                href="<?php echo get_permalink($prod_id); ?>">
                                <span class="elementor-button-content-wrapper">
                                    <span class="elementor-button-text">
                                        <?php
                                        if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'ar') {
                                            echo 'عرض المنتج'; // Arabic 
                                        } else {
                                            echo 'View Product'; // English (default) 
                                        }
                                        ?>
                                    </span>
                                    <span class="elementor-button-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16"
                                            fill="none">
                                            <path d="M15 8L1 8" stroke="white" stroke-linecap="round" />
                                            <path d="M8 15L15 8L8 1" stroke="white" stroke-linecap="round" />
                                        </svg>
                                    </span>
                                </span>
                            </a>
                        </div>


                    </div>
                </div>
            <?php endforeach; ?>

        </div>

        <!-- Navigation -->
        <div class="related-products-nav">
            <div class="elementor-swiper-button-prev <?php echo esc_attr(
                $unique_id
            ); ?>-prev">
                <img src="/wp-content/uploads/2025/12/nav-next-prev.svg" alt="Arrow Navigation">
            </div>
            <div class="elementor-swiper-button-next <?php echo esc_attr(
                $unique_id
            ); ?>-next">
                <img src="/wp-content/uploads/2025/12/nav-next-prev.svg" alt="Arrow Navigation">
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Swiper('.<?php echo esc_js($unique_id); ?>', {
                spaceBetween: 24,
                speed: 1500,
                loop: false,
                slidesPerView: 4,
                navigation: {
                    nextEl: '.<?php echo esc_js($unique_id); ?>-next',
                    prevEl: '.<?php echo esc_js($unique_id); ?>-prev',
                },
                breakpoints: {
                    0: { slidesPerView: 1 },
                    768: { slidesPerView: 2 },
                    1024: { slidesPerView: 4 }
                },
                <?php if (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'ar'): ?>
                rtl: true,
                <?php endif; ?>
            });
        });
    </script>



    <?php
    return ob_get_clean();
}
add_shortcode('related_subcategory_carousel', 'related_subcategory_carousel_shortcode');


// Show Subcategories wrt to order number acf field
function ah_current_category_subcategories_shortcode()
{

    if (!is_product_category()) {
        return '';
    }

    // Categories to exclude (by name)
    $excluded_names = ['Solutions', 'Warehouse'];
    $excluded_slugs = array_map('sanitize_title', $excluded_names);

    $current_term = get_queried_object();
    if (!$current_term || empty($current_term->term_id)) {
        return '';
    }

    // Stop completely if current category is excluded
    if (in_array($current_term->slug, $excluded_slugs, true)) {
        return '';
    }

    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : '';

    // Resolve excluded term IDs (language-aware)
    $excluded_ids = [];
    foreach ($excluded_slugs as $slug) {
        $term = get_term_by('slug', $slug, 'product_cat');
        if ($term) {
            if (function_exists('icl_object_id')) {
                $translated_id = icl_object_id($term->term_id, 'product_cat', true, $current_lang);
                if ($translated_id) {
                    $excluded_ids[] = (int) $translated_id;
                    continue;
                }
            }
            $excluded_ids[] = (int) $term->term_id;
        }
    }

    // Get direct subcategories
    $subcategories = get_terms([
        'taxonomy' => 'product_cat',
        'parent' => $current_term->term_id,
        'hide_empty' => true,
        'lang' => $current_lang, // WPML important
        'exclude' => $excluded_ids,
    ]);

    if (empty($subcategories) || is_wp_error($subcategories)) {
        return '';
    }

    // Attach ACF order field (language-specific)
    foreach ($subcategories as &$term) {

        $term_id = $term->term_id;

        if (function_exists('icl_object_id')) {
            $translated_id = icl_object_id($term_id, 'product_cat', true, $current_lang);
            if ($translated_id) {
                $term_id = $translated_id;
            }
        }

        $order = get_field('order', 'product_cat_' . $term_id);
        $term->acf_order = is_numeric($order) ? (int) $order : 9999;
    }
    unset($term);

    // Sort by ACF order
    usort($subcategories, function ($a, $b) {
        return $a->acf_order <=> $b->acf_order;
    });

    ob_start();
    ?>
    <ul class="products elementor-grid columns-3">
        <?php
        $count = 0;
        foreach ($subcategories as $category):

            if (in_array($category->slug, $excluded_slugs, true)) {
                continue;
            }

            $count++;
            $position_class = '';

            if (($count - 1) % 3 === 0) {
                $position_class = ' first';
            } elseif ($count % 3 === 0) {
                $position_class = ' last';
            }

            $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
            $image = $thumbnail_id
                ? wp_get_attachment_image($thumbnail_id, 'woocommerce_thumbnail')
                : wc_placeholder_img('woocommerce_thumbnail');

            $link = get_term_link($category);
            ?>
            <li class="product-category product<?php echo esc_attr($position_class); ?>">
                <a aria-label="Visit product category <?php echo esc_attr($category->name); ?>"
                    href="<?php echo esc_url($link); ?>">
                    <?php echo $image; ?>
                    <h2 class="woocommerce-loop-category__title">
                        <?php echo esc_html($category->name); ?>
                        <mark class="count">(<?php echo esc_html($category->count); ?>)</mark>
                    </h2>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php

    return ob_get_clean();
}

add_shortcode('current_category_subcategories', 'ah_current_category_subcategories_shortcode');






/**
 * Sort WooCommerce products by ACF field `sort_order`.
 */
// add_action('woocommerce_product_query', 'cc_sort_products_by_acf_order');

// function cc_sort_products_by_acf_order($query)
// {
//     if (is_admin() || ! $query->is_main_query()) {
//         return;
//     }

//     $query->set('meta_key', 'sort_order');
//     $query->set('orderby', 'meta_value_num');
//     $query->set('order', 'ASC');

//     // Ensure products without the field appear last
//     $meta_query = [
//         'relation' => 'OR',
//         [
//             'key'     => 'sort_order',
//             'compare' => 'EXISTS',
//         ],
//         [
//             'key'     => 'sort_order',
//             'compare' => 'NOT EXISTS',
//         ],
//     ];

//     $query->set('meta_query', $meta_query);
// }



/**
 * Sort WooCommerce products by ACF field `sort_order`
 * while keeping products without the field visible.
 */

// add_action('woocommerce_product_query', 'cc_sort_products_by_acf_order');

// function cc_sort_products_by_acf_order($query)
// {
//     if (is_admin() || ! $query->is_main_query()) {
//         return;
//     }

//     $query->set('meta_query', [
//         'relation' => 'OR',
//         [
//             'key'     => 'sort_order',
//             'compare' => 'EXISTS',
//         ],
//         [
//             'key'     => 'sort_order',
//             'compare' => 'NOT EXISTS',
//         ],
//     ]);

//     $query->set('orderby', [
//         'meta_value_num' => 'ASC',
//         'date'           => 'DESC', // fallback for products without sort_order
//     ]);

//     $query->set('order', 'ASC');
// }




add_filter('wpseo_breadcrumb_links', function ($links) {

    if (!is_singular('product') || !function_exists('icl_object_id')) {
        return $links;
    }

    global $post;

    // Get all product categories
    $terms = wp_get_post_terms($post->ID, 'product_cat');
    if (empty($terms) || is_wp_error($terms)) {
        return $links;
    }

    /**
     * STEP 1: Find the deepest category
     */
    $deepest_term = null;
    $max_depth = -1;

    foreach ($terms as $term) {
        $depth = count(get_ancestors($term->term_id, 'product_cat'));
        if ($depth > $max_depth) {
            $max_depth = $depth;
            $deepest_term = $term;
        }
    }

    if (!$deepest_term) {
        return $links;
    }

    /**
     * STEP 2: Translate deepest category
     */
    $translated_term_id = icl_object_id($deepest_term->term_id, 'product_cat', true);
    $term = get_term($translated_term_id, 'product_cat');

    if (!$term || is_wp_error($term)) {
        return $links;
    }

    /**
     * STEP 3: Build full hierarchy (parent → child)
     */
    $breadcrumb_terms = [];

    $ancestors = get_ancestors($term->term_id, 'product_cat');
    $ancestors = array_reverse($ancestors);

    foreach ($ancestors as $ancestor_id) {
        $translated_parent_id = icl_object_id($ancestor_id, 'product_cat', true);
        $parent = get_term($translated_parent_id, 'product_cat');

        if ($parent && !is_wp_error($parent)) {
            $breadcrumb_terms[] = [
                'url' => get_term_link($parent),
                'text' => $parent->name,
                'term_id' => $parent->term_id,
            ];
        }
    }

    // Add current category
    $breadcrumb_terms[] = [
        'url' => get_term_link($term),
        'text' => $term->name,
        'term_id' => $term->term_id,
    ];

    /**
     * STEP 4: Remove Yoast’s default category crumbs
     */
    $new_links = [];
    foreach ($links as $link) {
        if (!isset($link['term_id'])) {
            $new_links[] = $link; // Home + Product
        }
    }

    /**
     * STEP 5: Insert our hierarchy after Home
     */
    array_splice($new_links, 1, 0, $breadcrumb_terms);

    /**
     * STEP 6: Fix URLs for current language
     */
    foreach ($new_links as &$link) {
        if (!empty($link['url'])) {
            $link['url'] = apply_filters(
                'wpml_permalink',
                $link['url'],
                apply_filters('wpml_current_language', null)
            );
        }
    }

    return $new_links;
});


// Numeric URL Fix
add_action('init', function () {

    if (!current_user_can('administrator'))
        return;

    $products = get_posts([
        'post_type' => 'product',
        'numberposts' => -1,
        'post_status' => 'any',
    ]);

    foreach ($products as $product) {
        if (is_numeric($product->post_name)) {
            wp_update_post([
                'ID' => $product->ID,
                'post_name' => 'p-' . $product->post_name,
            ]);
        }
    }
});

// Remove aria role attribute for accesiblity score on lighthouse 
add_action('wp_footer', function () {
    ?>
    <script>
        document.querySelectorAll('.elementor-loop-container[role="list"]').forEach(el => {
            el.removeAttribute('role');
        });

        document.querySelectorAll('.topBar .col-1 ul li a').forEach(el => {
            el.removeAttribute('role');
        });
    </script>
    <?php
});


add_action('wp_footer', function () {
    ?>
    <script>
        (function () {

            function fixAria() {

                // 1️⃣ Search popup
                document.querySelectorAll('.searchHeader a').forEach(el => {
                    if (!el.getAttribute('aria-label')) {
                        el.setAttribute('aria-label', 'Open search');
                    }
                });

                // 2️⃣ LinkedIn icon
                document.querySelectorAll('.topBar .col-2 ul li a').forEach(el => {
                    if (!el.getAttribute('aria-label')) {
                        el.setAttribute('aria-label', 'Visit LinkedIn page');
                    }
                });

                // 3️⃣ Category boxes
                document.querySelectorAll('.productsGrid .elementor-container .box').forEach(box => {

                    // If box itself is link
                    if (box.tagName === 'A' && !box.getAttribute('aria-label')) {
                        let title = box.innerText.trim();
                        if (!title) title = 'View category';
                        box.setAttribute('aria-label', title);
                    }

                    // If link is inside box
                    const link = box.querySelector('a');
                    if (link && !link.getAttribute('aria-label')) {

                        let title = box.querySelector('h1,h2,h3,h4')?.innerText.trim();
                        if (!title) title = link.innerText.trim();
                        if (!title) title = 'View category';

                        link.setAttribute('aria-label', 'View ' + title);
                    }

                });

            }

            // Initial run
            fixAria();

            // Elementor dynamic content observer
            const observer = new MutationObserver(fixAria);
            observer.observe(document.body, { childList: true, subtree: true });

        })();
    </script>
    <?php
});

// product link unlikable 
add_filter('wpseo_breadcrumb_links', 'fix_only_products_breadcrumb');
function fix_only_products_breadcrumb($links)
{

    // Check if Products exists at position 1
    if (isset($links[1])) {
        unset($links[1]['url']); // make only "Products" non-clickable
    }

    return $links;
}

//unComitted by Pratyush on support with Hassan Line : 1737-1857
//rental sub parent category
function rental_solutions_categories_shortcode()
{
     // 1. Setup Configuration
     $allowed_slugs = [
         'lift-trucks',
         'industrial-products',
         'power-products',
         'construction-machinery',
		 'telehandlers',
		 'air-compressors'
     ];

     $exclude_ids = [];
     $is_allowed_context = false;
     $target_main_term = null;

     // Convert slugs to IDs for easier comparison
     $allowed_ids = [];
     foreach ($allowed_slugs as $slug) {
         $term = get_term_by('slug', $slug, 'product_cat');
         if ($term) $allowed_ids[] = $term->term_id;
     }

     // 2. Identify the Context (Category Page or Product Page)
     if (is_product_category()) {
         $current_term = get_queried_object();
         $current_id = $current_term->term_id;

         // Check if current cat or any ancestor is in our allowed list
         $ancestors = get_ancestors($current_id, 'product_cat');
         $hierarchy = array_merge([$current_id], $ancestors);

         foreach ($hierarchy as $term_id) {
             if (in_array($term_id, $allowed_ids)) {
                 $is_allowed_context = true;
                 $exclude_ids[] = $term_id; // Exclude the main top-level cat from the grid
                 break;
             }
         }
     }
     elseif (is_product()) {
             $product_id = get_queried_object_id();
         $product_cats = wp_get_post_terms($product_id, 'product_cat');

         if (!is_wp_error($product_cats) && !empty($product_cats)) {
             foreach ($product_cats as $term) {
                 $ancestors = get_ancestors($term->term_id, 'product_cat');
                 $hierarchy = array_merge([$term->term_id], $ancestors);

                 foreach ($hierarchy as $term_id) {
                     if (in_array($term_id, $allowed_ids)) {
                         $is_allowed_context = true;
                         $exclude_ids[] = $term_id;
                         break;
                     }
                 }
             }
         }
     }

     // Stop if we aren't in the Rental Solutions branch
     if (!$is_allowed_context) return '';

     // 3. Get the Parent for the Grid
     $parent = get_term_by('slug', 'rental-solutions-and-commercial-vehicles', 'product_cat');
     if (!$parent) return '';

     $terms = get_terms([
         'taxonomy'   => 'product_cat',
         'parent'     => $parent->term_id,
         'hide_empty' => true
     ]);

     if (empty($terms) || is_wp_error($terms)) return '';

     ob_start();
     ?>

     <div class="rental-other-cats">
         <ul class="products elementor-grid columns-3">
             <?php
             $count = 0;
             foreach ($terms as $term):
                 // Show ONLY categories in our allowed list
                 if (!in_array($term->slug, $allowed_slugs)) continue;

                 // EXCLUDE the one currently active in the hierarchy
                 if (in_array($term->term_id, $exclude_ids)) continue;

                 $count++;
                 $position_class = '';
                 if (($count - 1) % 3 === 0) {
                     $position_class = ' first';
                 } elseif ($count % 3 === 0) {
                     $position_class = ' last';
                 }

                 $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                 $image = $thumbnail_id
                     ? wp_get_attachment_image($thumbnail_id, 'woocommerce_thumbnail')
                     : wc_placeholder_img('woocommerce_thumbnail');

                 $link = get_term_link($term);
                 ?>

                 <li class="product-category product<?php echo esc_attr($position_class); ?>">
                     <a href="<?php echo esc_url($link); ?>">
                         <?php echo $image; ?>
                         <h2 class="woocommerce-loop-category__title">
                             <?php echo esc_html($term->name); ?>
                         </h2>
                     </a>
                 </li>

             <?php endforeach; ?>
         </ul>
     </div>

     <?php
     return ob_get_clean();
}
add_shortcode('rental_cats', 'rental_solutions_categories_shortcode');

// Show email and phone in homepage map info window 
add_filter('wpsl_cpt_info_window_template', 'custom_wpsl_cpt_info_window');

function custom_wpsl_cpt_info_window()
{

    $template = '<div class="wpsl-info-window">' . "\r\n";
    $template .= "\t" . '<p class="wpsl-no-margin">' . "\r\n";
    $template .= "\t\t" . '<strong><%= store %></strong>' . "\r\n";
    $template .= "\t\t" . '<span><%= address %></span>' . "\r\n";
    $template .= "\t\t" . '<span><%= city %> <%= state %> <%= zip %></span>' . "\r\n";
    $template .= "\t" . '</p>' . "\r\n";

    // Linked Phone - No <br>
    $template .= "\t" . '<% if ( typeof phone !== "undefined" && phone ) { %>' . "\r\n";
    $template .= "\t" . '<span><strong>Phone</strong>: <a href="tel:<%= phone %>"><%= phone %></a></span>' . "\r\n";
    $template .= "\t" . '<% } %>' . "\r\n";

    // Linked Email - No <br>
    $template .= "\t" . '<% if ( typeof email !== "undefined" && email ) { %>' . "\r\n";
    $template .= "\t" . '<span><strong>Email</strong>: <a href="mailto:<%= email %>"><%= email %></a></span>' . "\r\n";
    $template .= "\t" . '<% } %>' . "\r\n";

    $template .= '</div>';

    return $template;
}


// Show different markers on main our locations map 
add_filter('wpsl_frontend_meta_fields', 'custom_frontend_meta_fields');

function custom_frontend_meta_fields($store_fields)
{
    $store_fields['wpsl_alternate_marker_url'] = array('name' => 'alternateMarkerUrl');
    return $store_fields;
}

add_filter('wpsl_js_settings', 'fix_wpsl_js_marker_error');

function fix_wpsl_js_marker_error($settings)
{
    // Ensure the markers have a string-based size to satisfy the JS split function
    if (!isset($settings['markerSize']) || !is_string($settings['markerSize'])) {
        $settings['markerSize'] = '24,35';
    }

    return $settings;
}

function custom_start_marker_props($marker_props)
{

    // 1. Use the absolute URL (better for Google Maps API)
    $marker_props['location']['url'] = site_url('/wp-content/uploads/2026/04/machinery-blue.png');

    // 2. Explicitly define size/anchor as strings to prevent the .split error
    // Standard marker size is usually 24x35 or 32x32. Adjust numbers if needed.
    $marker_props['location']['size'] = '24,35';
    $marker_props['location']['scaledSize'] = '24,35';
    $marker_props['location']['anchor'] = '16,32'; // Points to the bottom center

    return $marker_props;
}
?>
