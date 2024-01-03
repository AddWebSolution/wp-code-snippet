<?php
/**
 * Plugin Name:       Advanced Gutenberg Block Master Library pl
 * Plugin URI:
 * Description:       Advanced Gutenberg Block Master Library
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Version:           0.1.0
 * Author:            AddWeb Solution
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       addweb-blocks
 * Domain Path:       addweb-blocks
 *
 * @package           aws-blocks
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

// Define constants for the plugin.
define('AWSGB_VERSION', '0.1.0');
define('AWSGB_PLUGIN_FILE', __FILE__);
define('AWSGB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AWSGB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AWSGB_PLUGIN_AUTHOR', 'Addweb');

// Function to add SVG mime type.
function awgb_mime_types( $mimes ){
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter( 'upload_mimes', 'awgb_mime_types' );

// Function to fix SVG display in the admin head.
function awgb_fix_svg() {
    echo '<style type="text/css">
            .attachment-266x266, .thumbnail img {
                width: 100% !important;
                height: auto !important;
            }
        </style>';
}
add_action( 'admin_head', 'awgb_fix_svg' );

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/writing-your-first-block-type/
 */
function awsgb_register_blocks()
{
    // Register blocks in the format $dir => $render_callback.
    $blocks = array(
        'accordion-item' => array(),
        'accordion-group' => array('script' => 'awsgb-accordion-group'),
        'advanced-tabs' => '',
        'breadcrumbs' => 'render_block_core_breadcrumbs',
        'button' => '',
        'button-group' => '',
        'button-modal' => '',
        'blog' => 'render_block_core_blog_post_html',
        'call-to-action' => '',
        'faq-item' => '', 
        'faqs' => '',
        'feature' => '',
        'hero-banner' => '',
        'icon' => '',
        'map-iframe' => '',
        'our-team' => '',
        'pricing' => '',
        'stat' => '',
        'tab' => '',
        'tagline' => '',
        'testimonial' => '',
        'gallery-slider' => ''
    );

    // Register each block.
    foreach ($blocks as $dir => $render_callback) {
        $args = array();
        if (!empty($render_callback)) {
            $args['render_callback'] = $render_callback;
        }
        register_block_type(__DIR__ . '/blocks/build/' . $dir, $args);
    }

    // Register block patterns.
    if (class_exists('WP_Block_Patterns_Registry')) {
        $patterns = array(
            'stats' => '',
            'faqs'=>'',
            'call-to-action'=>'',
            'our-team'=>'',
            'header'=>'',
            'footer'=>'',
        );
        register_block_pattern_category(
            'stats',
            array('label' => _x('Stats', 'Block pattern category', 'textdomain'))
        );
        register_block_pattern_category(
            'faqs',
            array('label' => _x('FAQs', 'Block pattern category', 'textdomain'))
        );
        register_block_pattern_category(
            'cta',
            array('label' => _x('Call To Actions', 'Block pattern category', 'textdomain'))
        );
        register_block_pattern_category(
            'our-team',
            array('label' => _x('Our Team', 'Block pattern category', 'textdomain'))
        );

        // Register block pattern categories.
        foreach ($patterns as $dirP => $render_callback) {
            $pattern_content = file_get_contents(AWSGB_PLUGIN_DIR . 'patterns/' . $dirP . '/index.php'); // Adjust the path as needed
            $json = json_decode(file_get_contents(AWSGB_PLUGIN_DIR . 'patterns/' . $dirP . '/pattern.json'),true);
            if(is_array($json)){
                $json['content'] = $pattern_content;
            }
            
            register_block_pattern(
                'aws-blocks/' . $dirP,
                $json
            );
        }
    }
}

add_action('init', 'awsgb_register_blocks');

/**
 * Renders the `core/blog` block on the server.
 *
 * @param array $attributes Block attributes.
 * @param string $content Block default content.
 * @param WP_Block $block Block instance.
 *
 * @return string Returns the latest blog html code
 */
function render_block_core_blog_post_html( $attributes, $content, $block ){

    $orderby = 'id'; $order = 'DESC';
    if( isset( $attributes['orderby'] ) && !empty( $attributes['orderby'] ) ){
 
        $order_by = explode( '/', $attributes['orderby'] );
        $orderby = ( isset( $order_by[0] ) ? $order_by[0] : $orderby ); 
        $order = ( isset( $order_by[1] ) ? $order_by[1] : $order );
    }


    $post_type = ( isset( $attributes['selectedPostType'] ) && !empty( $attributes['selectedPostType'] ) ? $attributes['selectedPostType'] : 'post');    
    $lastposts = get_posts( array(
                     'post_status'    => 'publish',
                     'post_type'      => $post_type,
                     'orderby'        => $orderby,
                     'order'          => $order,
                     'posts_per_page' => 3
                ));

    $str = '';
    if( !empty( $lastposts ) ){
        $str .= '<section class="addweb-blog-style1 align'.( isset( $attributes['align'] ) ? $attributes['align'] : '' ).'"><div class="blog-card-container row mt-3 mt-lg-5">';

            foreach ( $lastposts as $key => $lastpost ) {
                
                $url = get_the_post_thumbnail_url( $lastpost->ID );
                $cats = get_the_category( $lastpost->ID );

                $str .= '<div class="col-md-6 col-lg-4 blog-card-wrap mb-4 mb-lg-0"><div class="card blog-card position-relative h-100 ">';                    
                $str .= '<div class="card-blog-img position-relative h-100 ">';

                if( isset( $attributes['displaytag'] ) && !empty( $attributes['displaytag'] ) ){
                $str .= '<div class="position-absolute d-flex card-badge mt-3 ms-3">
                            <p class="badge bg-primary m-0 text-capitalize">
                                <img src="'.AWSGB_PLUGIN_URL.'blocks/src/blog/img/flash.svg'.'" class="img-fluid">'.( !empty( $cats ) ? join( ', ', wp_list_pluck( $cats, 'name' ) ) : '').'
                            </p>
                        </div>';
                }
                if( isset( $attributes['postimage'] ) && !empty( $attributes['postimage'] ) && !empty( $url ) ){
                    $str .= '<img src="'.$url.'" class="card-img-top img-fluid" alt="Blog image">';
                }

                $str .= '</div>';

                $author_id = get_post_field( 'post_author', $lastpost->ID );
                $author_name = get_the_author_meta( 'display_name', $author_id );

                $str .= '<div class="card-body p-2 p-lg-4">';

                if( isset( $attributes['posttitle'] ) && !empty( $attributes['posttitle'] ) && !empty( $url ) ){
                    $str .= '<h4 class="card-title line-clamp">'.$lastpost->post_title.'</h4>';
                }

                $str .= apply_filters( 'the_content', wp_trim_words($lastpost->post_content, ( isset( $attributes['NumWords'] ) && !empty( $attributes['NumWords'] ) ? $attributes['NumWords'] : 30 ), '...') );
                $str .= '<div class="blog-content-wrap d-flex justify-content-between">';
                
                if( isset( $attributes['author'] ) && !empty( $attributes['author'] )){
                    $str .= '<p class="card-text font-mini m-0 text-capitalize">'.$author_name.'</p>';
                }
                
                if( isset( $attributes['postdate'] ) && !empty( $attributes['postdate'] )){
                    $str .= '<p class="font-12 m-0">'.date("d M • Y", strtotime( $lastpost->post_date )).'</p>';
                }

                $str .= '</div>
                        </div>';
                $str .= '<a href="'.get_permalink( $lastpost->ID ).'" class="position-absolute w-100 h-100 top-0 start-0"></a>';
                $str .= '</div></div>';
            } 
        $str .= '</div></section>';
    }
    return $str;
}
/**
 * Adding a new (custom) block category.
 *
 * @param array $block_categories Array of categories for block types.
 * @param WP_Block_Editor_Context $block_editor_context The current block editor context.
 */
function awsgb_categories($block_categories, $post)
{

    array_unshift($block_categories, array(
        'slug' => 'addweb-blocks',
        'title' => AWSGB_PLUGIN_AUTHOR.__(' Blocks', 'addweb-blocks'),
        'icon' => AWSGB_PLUGIN_URL . 'assets/img/addweb-logo.png'
    ));

    return $block_categories;
}

add_action('block_categories_all', 'awsgb_categories', 10, 2);

// Function to enqueue scripts and styles.
function awsgb_enqueue_scripts()
{
    if(is_admin()){
        wp_enqueue_style('awsgb-bootstrap-css', AWSGB_PLUGIN_URL . 'dist/assets/css/app.min.css', '', time());
        wp_enqueue_style('awsgb-backend-css', AWSGB_PLUGIN_URL . 'assets/css/backend-style.css', '', time());
    }
    wp_enqueue_style('dashicons');
    wp_enqueue_style('awsgb-swiper', AWSGB_PLUGIN_URL . 'assets/vendor/swiper/css/swiper-bundle.min.css', '', time());
    wp_enqueue_style('awsgb-font-awesome5', AWSGB_PLUGIN_URL . 'assets/css/font-awesome5.css', '', AWSGB_VERSION);
    wp_enqueue_style('awsgb-fontpicker.base', AWSGB_PLUGIN_URL . 'assets/css/fonticonpicker.base-theme.react.css', '', AWSGB_VERSION);
    wp_enqueue_style('awsgb-fontpicker.material', AWSGB_PLUGIN_URL . 'assets/css/fonticonpicker.material-theme.react.css', '', AWSGB_VERSION);
    wp_register_style('awsgb-font-awesome-all', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css', '', AWSGB_VERSION);

    wp_register_script('awsgb-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js', array('jquery'), true, true);
    wp_enqueue_script('awsgb-bootstrap-js', AWSGB_PLUGIN_URL . 'assets/vendor/BS5/js/bootstrap.bundle.min.js', array('jquery'), AWSGB_VERSION, true);
    wp_enqueue_script('awsgb-swiper', AWSGB_PLUGIN_URL . 'assets/vendor/swiper/js/swiper-bundle.min.js', array('jquery'), time(), true);
    wp_enqueue_script('awsgb-tab-js', AWSGB_PLUGIN_URL . 'assets/js/tab.js', array('jquery'), time(), true);
    wp_enqueue_script('awsgb-glide', 'https://cdnjs.cloudflare.com/ajax/libs/Glide.js/3.0.1/glide.js', array('jquery'), AWSGB_VERSION, true);
    
    wp_enqueue_script('awsgb-accordion-group', AWSGB_PLUGIN_URL . 'blocks/src/accordion-group/accordion-group.js', array('jquery'), time(), true);
    wp_enqueue_script('awsgb-our-team-slider', AWSGB_PLUGIN_URL . 'blocks/src/our-team/slider.js', array('jquery','awsgb-swiper'), time(), true);
    wp_enqueue_script('awsgb-pricing-toggle', AWSGB_PLUGIN_URL . 'blocks/src/pricing/toggle.js', array('jquery'), time(), true);
   
    wp_enqueue_script('awsgb-custom-js', AWSGB_PLUGIN_URL . 'dist/assets/js/custom.min.js', array('jquery'), time(), true);
    wp_enqueue_script('awsgb-gallery-slider', AWSGB_PLUGIN_URL . 'blocks/src/gallery-slider/gallery-slider.js', array('jquery','awsgb-swiper'), time(), true);
}

add_action('enqueue_block_editor_assets', 'awsgb_enqueue_scripts');
add_action('wp_enqueue_scripts', 'awsgb_enqueue_scripts');

/**
 * Renders the dynamic block on server.
 *
 * @param array $attributes The block attributes.
 *
 * @return string Returns the post content with latest posts added.
 */
function wz_tutorial_dynamic_block_recent_posts($attributes)
{

    $args = array(
        'posts_per_page' => $attributes['postsToShow'],
        'post_status' => 'publish',
        'order' => $attributes['order'],
        'orderby' => $attributes['orderBy'],
        'ignore_sticky_posts' => true,
        'no_found_rows' => true,
    );

    $query = new WP_Query();
    $latest_posts = $query->query($args);

    $li_html = '';

    foreach ($latest_posts as $post) {
        $post_link = esc_url(get_permalink($post));
        $title = get_the_title($post);

        if (!$title) {
            $title = __('(no title)', 'addweb-blocks');
        }

        $li_html .= '<li>';

        $li_html .= sprintf(
            '<a class="addweb-blocks-recent-posts__post-title" href="%1$s">%2$s</a>',
            esc_url($post_link),
            $title
        );

        $li_html .= '</li>';

    }

    $classes = array('addweb-blocks-recent-posts');

    $wrapper_attributes = get_block_wrapper_attributes(array('class' => implode(' ', $classes)));

    $heading = $attributes['showSubHeading'] ? '<h3>' . $attributes['heading'] . '</h3>' : '';

    return sprintf(
        '<div %2$s>%1$s<ul>%3$s</ul></div>',
        $heading,
        $wrapper_attributes,
        $li_html
    );
}

/**
 * Server-side rendering of the `core/breadcrumbs` block.
 *
 * @package WordPress
 */

/**
 * Renders the `core/breadcrumbs` block on the server.
 *
 * @param array $attributes Block attributes.
 * @param string $content Block default content.
 * @param WP_Block $block Block instance.
 *
 * @return string Returns the filtered breadcrumbs for the current post.
 */
function render_block_core_breadcrumbs($attributes, $content, $block)
{
    if (!isset($block->context['postId'])) {
        return '';
    }

    $post_id = $block->context['postId'];
    $post_type = get_post_type($post_id);

    if (false === $post_type) {
        return '';
    }

    $ancestor_ids = array();
    $has_post_hierarchy = is_post_type_hierarchical($post_type);
    $show_site_title = !empty($attributes['showSiteTitle']);
    $show_current_page = !empty($attributes['showCurrentPageTitle']);
    $backgroundImage = !empty($attributes['backgroundImage']);


    if ($has_post_hierarchy) {
        $ancestor_ids = get_post_ancestors($post_id);

        if (
            empty($ancestor_ids) &&
            !($show_site_title && $show_current_page)
        ) {
            return '';
        }
    } else {
        $terms = get_the_terms($post_id, 'category');

        if (empty($terms) || is_wp_error($terms)) {
            return '';
        }

        $term = get_term($terms[0], 'category');

        $ancestor_ids[] = $term->term_id;
        $ancestor_ids = array_merge($ancestor_ids, get_ancestors($term->term_id, 'category'));
    }

    $breadcrumbs = array();

    // Prepend site title breadcrumb if available and set to show.
    $site_title = get_bloginfo('name');
    if ($site_title && $show_site_title) {
        $site_title = !empty($attributes['siteTitleOverride']) ?
            $attributes['siteTitleOverride'] :
            $site_title;

        $breadcrumbs[] = array(
            'url' => get_bloginfo('url'),
            'title' => $site_title,
        );
    }

    if ($has_post_hierarchy) {
        // Construct remaining breadcrumbs from ancestor ids.
        foreach (array_reverse($ancestor_ids) as $ancestor_id) {
            $breadcrumbs[] = array(
                'url' => get_the_permalink($ancestor_id),
                'title' => get_the_title($ancestor_id),
            );
        }
    } else {
        foreach (array_reverse($ancestor_ids) as $ancestor_id) {
            $breadcrumbs[] = array(
                'url' => get_category_link($ancestor_id),
                'title' => get_cat_name($ancestor_id),
            );
        }
    }

    // Append current page title if set to show.
    if ($show_current_page) {
        $breadcrumbs[] = array(
            'url' => get_the_permalink($post_id),
            'title' => get_the_title($post_id),
        );
    }

    $inner_markup = '';

    /**
     * Filters the list of breadcrumb links within the Breadcrumbs block render callback.
     *
     * @param array[] An array of Breadcrumb arrays with `url` and `title` keys.
     * @since 6.3.0
     *
     */
    $breadcrumbs = apply_filters('block_core_breadcrumbs_links', $breadcrumbs);

    foreach ($breadcrumbs as $index => $breadcrumb) {
        $show_separator = $index < count($breadcrumbs) - 1;
        $inner_markup .= build_block_core_breadcrumbs_inner_markup_item(
            $breadcrumb['url'],
            $breadcrumb['title'],
            $attributes,
            $index,
            $show_separator,
            ($show_current_page && count($breadcrumbs) - 1 === $index)
        );
    }
    $classnames = '';

    if (!empty($attributes['contentJustification'])) {
        if ('left' === $attributes['contentJustification']) {
            $classnames .= ' is-content-justification-left';
        }

        if ('center' === $attributes['contentJustification']) {
            $classnames .= ' is-content-justification-center';
        }

        if ('right' === $attributes['contentJustification']) {
            $classnames .= ' is-content-justification-right';
        }
    }

    $wrapper_attributes = get_block_wrapper_attributes(
        array(
            'class' => $classnames,
            'aria-label' => __('Breadcrumb'),
        )
    );


    if (isset($attributes['backgroundImage']) && $attributes['backgroundImage'] != '') {
        $sectionMarkup = sprintf(
            '<section class="addweb-breadcrumb-style1" style="background-image: url(%s); background-size: cover; background-position: center center; background-repeat: no-repeat;">',
            esc_url($attributes['backgroundImage'])
        );
        $sectionMarkup .= '<div class="breadcrumbs-wrap">';
        if ($attributes['showPageTitle']) {
            $sectionMarkup .= '<h2 class="page-title">' . esc_html($breadcrumb['title']) . '</h2>';
        }
    
        $sectionMarkup .= sprintf(
            '<nav %1$s><ol class="breadcrumb justify-content-center mb-0">%2$s</ol></nav>
            </div>
            </section>',
            $wrapper_attributes,
            $inner_markup
        );
    } else {
        $sectionMarkup = sprintf(
            '<section class="addweb-breadcrumb-style1">
                <div class="breadcrumbs-wrap">
                    <h2 class="page-title">%s</h2>
                    <nav %s><ol class="breadcrumb justify-content-center mb-0">%s</ol></nav>
                </div>
            </section>',
            esc_html($breadcrumb['title']),
            $wrapper_attributes,
            $inner_markup
        );
    }
    
    return $sectionMarkup;
    
}

/**
 * Builds the markup for a single Breadcrumb item.
 *
 * Used when iterating over a list of breadcrumb urls and titles.
 *
 * @param string $url The url for the link in the breadcrumb.
 * @param string $title The label/title for the breadcrumb item.
 * @param array $attributes Block attributes.
 * @param int $index The position in a list of ids.
 * @param bool $show_separator Whether to show the separator character where available.
 * @param bool $is_current_page Whether to mark the breadcrumb item as the current page.
 *
 * @return string The markup for a single breadcrumb item wrapped in an `li` element.
 */
function build_block_core_breadcrumbs_inner_markup_item($url, $title, $attributes, $index, $show_separator = true, $is_current_page = false)
{
    $li_class = 'breadcrumb-item';
    $markup = '';
    // Render leading separator if specified.
    if (
        !empty($attributes['showLeadingSeparator']) &&
        !empty($attributes['separator']) &&
        0 === $index
    ) {
        $markup .= sprintf(
            '%2$s',
            wp_kses_post($attributes['separator'])
        );
    }

    $markup .= sprintf(
        '<a class="breadcrumb-link" href="%s"%s>%s</a>',
        esc_url($url),
        $is_current_page ? ' aria-current="page"' : '',
        wp_kses_post($title)
    );

    return '<li class="'. $li_class.'">'.$markup.'</li>';
}