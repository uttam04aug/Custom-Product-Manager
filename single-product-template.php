<?php get_header(); ?>

<style>
.whatsapp-share-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: #25D366;
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-size: 16px;
    font-weight: 500;
    transition: background 0.3s ease;
}

.whatsapp-share-btn:hover {
    background: #1ebe5d;
    color: #fff;
}

.whatsapp-share-btn i {
    font-size: 22px;
}

    .product-container { display: flex; flex-wrap: wrap; gap: 30px; padding: 40px 1.2%; max-width: 1200px; margin: auto; padding-bottom:20%; margin-top:-25px; }
    .product-left { flex: 1; min-width: 400px;}
    .product-right { flex: 1; min-width: 300px;}
    

    /* Carousel Styling */
    .main-img-slider img { width: 100%; height: 500px; object-fit: contain; background: #f9f9f9; border: 1px solid #eee; }
    .thumb-nav img { width: 110px; height: 110px; object-fit: cover; margin-top: 10px;  cursor: pointer; border: 1px solid #ddd; }
    .thumb-nav .slick-current img { border-color: #2ecc71; } /* Active thumbnail green border */

    /* Right Side Text */
    .p-title { font-size: 28px; margin-bottom: 10px; font-weight: bold; color: #333; }
    .p-code { color: #888; font-size: 14px; margin-bottom: 20px; }
    .p-desc { line-height: 1.6; color: #555; border-top: 1px solid #eee; padding-top: 20px; }
    .breadcrumb {
    margin-top: 4%;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
}

.breadcrumb a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb a:hover {
    color: #ff9900; /* theme / water blue */
}

.breadcrumb i {
    font-size: 12px;
    color: #aaa;
}

.breadcrumb .current {
    color: #ff9900;
    font-weight: 600;
}

/* Mobile friendly */
@media (max-width: 576px) {
    .breadcrumb {
        font-size: 13px;
        flex-wrap: wrap;
    }
}

</style>



<div class="container">
  <div class="breadcrumb">
    <a href="/">Home</a>
    <i class="fa-solid fa-angles-right"></i>
    <a href="/products">Products</a>
    <i class="fa-solid fa-angles-right"></i>
    <span class="current"><?php the_title(); ?></span>
  </div>
</div>

<div class="product-container">

    <div class="product-left">
        <div class="main-img-slider">
            <?php 
            $gallery_ids = explode(',', get_post_meta(get_the_ID(), '_product_gallery', true));
            if(!empty($gallery_ids[0])) {
                foreach($gallery_ids as $id) {
                    echo '<div>' . wp_get_attachment_image($id, 'full') . '</div>';
                }
            } else {
                echo '<div>' . get_the_post_thumbnail(get_the_ID(), 'full') . '</div>';
            }
            ?>
        </div>
        <div class="thumb-nav">
            <?php 
            if(!empty($gallery_ids[0])) {
                foreach($gallery_ids as $id) {
                    echo '<div>' . wp_get_attachment_image($id, 'thumbnail') . '</div>';
                }
            }
            ?>
        </div>
    </div>

    <div class="product-right">
        <h1 class="p-title"><?php the_title(); ?></h1>
        <div class="p-code">Product Code: <strong><?php echo get_post_meta(get_the_ID(), '_product_code', true); ?></strong></div>
      <?php
global $post;

// Phone number with country code (no + sign)
$owner_phone = '919936402144';

// Permalink ko fetch karein aur variable assign karein
$share_url = get_permalink($post->ID);
$share_text = urlencode("Hello, I am interested in this: " . $share_url);
?>

<a style="margin-bottom:10px;" 
   href="https://wa.me/<?php echo $owner_phone; ?>?text=<?php echo $share_text; ?>"
   target="_blank"
   class="whatsapp-share-btn">
   <i class="fa-brands fa-whatsapp"></i> Contact Owner
</a>


        <div class="p-desc">
            <?php the_content(); ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($){
    $('.main-img-slider').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        fade: true,
        asNavFor: '.thumb-nav'
    });
    $('.thumb-nav').slick({
        slidesToShow: 5,
        slidesToScroll: 1,
        asNavFor: '.main-img-slider',
        dots: false,
        centerMode: false,
        focusOnSelect: true,
        infinite: false
    });
});
</script>


<?php get_footer(); ?>