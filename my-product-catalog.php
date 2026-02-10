<?php
/*
Plugin Name: My Custom Product Catalog
Description: Product management with Gallery Carousel, Grid View, Pagination, and Product Slider.
Version: 1.2
Author: Uttam Singh
*/

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Register Custom Post Type
function mpc_register_product_post_type() {
    register_post_type('my_product', array(
        'labels'      => array('name' => 'Products', 'singular_name' => 'Product'),
        'public'      => true,
        'has_archive' => true,
        'menu_icon'   => 'dashicons-cart',
        'supports'    => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'mpc_register_product_post_type');

// 2. Admin Assets
function mpc_admin_assets($hook) {
    global $post_type;
    if ( 'my_product' !== $post_type ) return;
    wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'mpc_admin_assets' );

// 3. Metabox for Product Code and Gallery
function mpc_add_metabox() {
    add_meta_box('mpc_details', 'Product Details', 'mpc_render_metabox', 'my_product', 'normal', 'high');
}
add_action('add_meta_boxes', 'mpc_add_metabox');

function mpc_render_metabox($post) {
    $code = get_post_meta($post->ID, '_product_code', true);
    $gallery = get_post_meta($post->ID, '_product_gallery', true);
    wp_nonce_field('mpc_save', 'mpc_nonce');
    ?>
    <p>
        <label><strong>Product Code:</strong></label><br>
        <input type="text" name="product_code" value="<?php echo esc_attr($code); ?>" style="width:100%;">
    </p>
    <p>
        <label><strong>Gallery Images:</strong></label><br>
        <input type="text" id="mpc_gal_input" name="product_gallery" value="<?php echo esc_attr($gallery); ?>" style="width:80%;" readonly>
        <button type="button" class="button mpc_upload_btn">Select Images</button>
    </p>
    <script>
        jQuery(document).ready(function($){
            $('.mpc_upload_btn').click(function(e) {
                var frame = wp.media({ title: 'Select Images', multiple: true }).open().on('select', function(){
                    var ids = frame.state().get('selection').map(function(a){ return a.id; }).join(',');
                    $('#mpc_gal_input').val(ids);
                });
            });
        });
    </script>
    <?php
}

function mpc_save_data($post_id) {
    if (!isset($_POST['mpc_nonce']) || !wp_verify_nonce($_POST['mpc_nonce'], 'mpc_save')) return;
    update_post_meta($post_id, '_product_code', sanitize_text_field($_POST['product_code']));
    update_post_meta($post_id, '_product_gallery', sanitize_text_field($_POST['product_gallery']));
}
add_action('save_post', 'mpc_save_data');

// 4. Frontend Assets (CSS & Slick Slider)
add_action('wp_enqueue_scripts', function(){
    wp_enqueue_style('slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
    wp_enqueue_style('slick-theme', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css');
    wp_enqueue_script('slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), null, true);
});

// 5. Shortcode for List [my_products]
add_shortcode('my_products', 'mpc_list_view');
function mpc_list_view() {
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $q = new WP_Query(array('post_type'=>'my_product', 'posts_per_page'=>3, 'paged'=>$paged));
    
    $out = '<style>
        .p-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px; margin-top:3%; }
        .p-card { background: white; margin:10px !important;
            border-radius: 10px; padding-bottom:30px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition); padding:10px; text-decoration:none; padding-bottom:30px !important;   }
                    .p-card img { width: 100%; height: 200px; object-fit: cover; }
                   .pagination {
    margin: 40px 0;
    display: flex;
    justify-content: flex-end;
}


.pagination .page-numbers {
    display: inline-block;
    margin: 0 4px;
    padding: 8px 14px;
    border: 1px solid #ddd;
    color: #333;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.pagination .page-numbers:hover {
    background: #703c89;
    color: #fff;
    border-color: #703c89;
}

.pagination .page-numbers.current {
    background: #703c89;
    color: #fff;
    border-color: #703c89;
    font-weight: 600;
    cursor: default;
}

.pagination .page-numbers.prev,
.pagination .page-numbers.next {
    font-weight: 500;
}
@media (max-width: 576px) {
    .pagination .page-numbers {
        padding: 6px 10px;
        font-size: 13px;
    }
    .p-grid { display: grid; grid-template-columns: repeat(1, 1fr); gap: 5px; margin-top:3%; }
}
    </style><div class="p-grid">';
    
    if($q->have_posts()): while($q->have_posts()): $q->the_post();
        $code = get_post_meta(get_the_ID(), '_product_code', true);
        $out .= '<a href="'.get_permalink().'" class="p-card" style="color:#fff;" >
            '.get_the_post_thumbnail(get_the_ID(), 'medium').'
            <h3 style="font-size:18px !important; font-weight:600; padding-top:10px;">'.get_the_title().'</h3>
            <p>Code: '.$code.'</p>
            <button  class="btn">View Details</button>
        </a>'; 
    endwhile; endif;
    
    $out .= '</div><div class="pagination">' . paginate_links(array('total' => $q->max_num_pages)) . '</div>';
    wp_reset_postdata();
    return $out;
}

// 6. Template Loader
add_filter('template_include', function($template){
    if (is_singular('my_product')) {
        $custom_template = plugin_dir_path(__FILE__) . 'single-product-template.php';
        if (file_exists($custom_template)) return $custom_template;
    }
    return $template;
});



// 7. Shortcode for Scrolling Slider [product_slider]
add_shortcode('product_slider', 'mpc_slider_view');
function mpc_slider_view() {
    $q = new WP_Query(array('post_type'=>'my_product', 'posts_per_page'=> 20)); // Jitne products slider mein chahiye
    
    $out = '<style>
        .mpc-slick-slider {  max-width:1200px; margin:auto; }
        .slide-item {background: white; margin:10px !important;
            border-radius: 10px; padding-bottom:30px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition); }
        .slide-item a { text-decoration: none; color: #fff;  display: block; padding: 10px; }
        .slide-item img { width: 100%; height: 280px; object-fit: cover; margin-bottom: 10px; }
    </style>';
    
    $out .= '<div class="mpc-slick-slider">';
    
    if($q->have_posts()): while($q->have_posts()): $q->the_post();
        $code = get_post_meta(get_the_ID(), '_product_code', true);
        $out .= '<div class="slide-item">
            <a href="'.get_permalink().'">
                '.get_the_post_thumbnail(get_the_ID(), 'medium').'
                <h4 style="font-size:18px; font-weight:bold;">'.get_the_title().'</h4>
                <p style="margin-bottm:0px !important;">Code: '.$code.'</p>
                 <a href="'.get_permalink().'" class="btn" style="display:inline-block; color:#fff; width:auto; padding:10px 20px; margin-top:-10px; margin-left:10px;">
    View Details
</a>

        </div>';
    endwhile; endif;
    
    $out .= '</div>';
    
    $out .= '<script>
        jQuery(document).ready(function($){
            $(".mpc-slick-slider").slick({
                slidesToShow: 3,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 2000,
                dots: true,
                arrows: true,
                responsive: [
                    { breakpoint: 1024, settings: { slidesToShow: 3 } },
                    { breakpoint: 768, settings: { slidesToShow: 2 } },
                    { breakpoint: 480, settings: { slidesToShow: 1 } }
                ]
            });
        });
    </script>';
    
    wp_reset_postdata();
    return $out;
}