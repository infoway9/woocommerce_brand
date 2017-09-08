<?php

class pw_brands_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
                'pw_brands_Widget', // Base ID
                __('WooCommerce Brands', 'woocommerce-brands'), // Name
                array('description' => __('Display a list of your Brands on your site.', 'woocommerce-brands'),) // Args
        );
    }

    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        echo $args['before_widget'];
        if (!empty($title))
            echo $args['before_title'] . $title . $args['after_title'];

        $get_terms = "product_brand";

        $categories = get_terms('product_brand', 'orderby=name&hide_empty=0');

        if (!empty($categories)) {

            if ($instance['show'] == "dropdown") {

                wp_enqueue_script('woob-dropdown-script');
                ?>
                <script type='text/javascript'>
                    /* <![CDATA[ */
                    function onbrandsChange(value) {
                        if (value == "")
                            return false;
                        window.location = "<?php echo home_url(); ?>/?<?php echo $get_terms; ?>=" + value;
                    }

                    /*jQuery(document).ready(function() {
                     jQuery("#payments").msDropdown({visibleRows:4});
                     jQuery(".tech").msDropdown();	
                     //				jQuery( '#carouselhor' ).elastislide(
                     //					{
                     //					 minItems : parseInt(jQuery( '#carouselhor' ).attr('title')),
                     //					}
                     //				);
                     });
                     /* ]]> */
                </script>                    
                <?php
                echo '<select name="tech" class="tech" onchange="onbrandsChange(this.value)" >';
                echo '<option value="">' . __('Please Select', 'woocommerce-brands') . '</option>';
                foreach ((array) $categories as $term) {
                    $display_type = get_woocommerce_term_meta($term->term_id, 'featured', true);
                    $count = "";
                    if ($instance['post_counts'] == 1)
                        $count = '( ' . esc_html($term->count) . ' )  ';

                    if ($instance['featured'] == 1 && $display_type == 1) {

                        echo'<option value="' . esc_html($term->slug) . '" ' . selected(esc_html(get_query_var('product_brand')), esc_html($term->slug), 1) . '>' . esc_html($term->name) . $count . '</option>';
                    } elseif ($instance['featured'] == 0) {
                        echo '<option value="' . esc_html($term->slug) . '" ' . selected(esc_html(get_query_var('product_brand')), esc_html($term->slug), 1) . '>' . esc_html($term->name) . $count . '</option>';
                    }
                }
                echo '</select>';
            } elseif ($instance['show'] == 'a-z') {
                ?>

                <script>
                    jQuery(document).ready(function ($) {
                        $(".alpha").on('click', function (e) {
                            e.preventDefault();
                            $("#loder").show();
                            $("#dispat-div").hide();
                            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                            var search_string = $(this).text();
                            var count = '<?php echo ($instance['post_counts'] == 1) ? 1 : ''; ?>'
                            var data = {
                                action: 'alpha_search',
                                search_string: search_string,
                                count: count
                            };
                            $.post(ajaxurl, data, function (resp) {
                                $("#loder").hide();
                                $("#dispat-div").show();
                                if (resp.flag == true) {
                                    $("#dispat-div").html(resp.result);
                                } else {
                                    $("#dispat-div").html('No Brand.');
                                }
                            }, 'json');
                        });
                        $('#dispat-div');
                        $('#dispat-div').perfectScrollbar();
                    });

                </script>
                <div class="wb-alphabet-table">
                    <div class="wb-all-alphabet"><a class="alpha" href="#">All</a></div>
                    <div class="wb-other-brands">
                        <span><a class="alpha" href="#">A</a></span>
                        <span><a class="alpha" href="#">B</a></span>
                        <span><a class="alpha" href="#">C</a></span>
                        <span><a class="alpha" href="#">D</a></span>
                        <span><a class="alpha" href="#">E</a></span>
                        <span><a class="alpha" href="#">F</a></span>
                        <span><a class="alpha" href="#">G</a></span>
                        <span><a class="alpha" href="#">H</a></span>
                        <span><a class="alpha" href="#">I</a></span>
                        <span><a class="alpha" href="#">J</a></span>
                        <span><a class="alpha" href="#">K</a></span>
                        <span><a class="alpha" href="#">L</a></span>
                        <span><a class="alpha" href="#">M</a></span>
                        <span><a class="alpha" href="#">N</a></span>
                        <span><a class="alpha" href="#">O</a></span>
                        <span><a class="alpha" href="#">P</a></span>
                        <span><a class="alpha" href="#">Q</a></span>
                        <span><a class="alpha" href="#">R</a></span>
                        <span><a class="alpha" href="#">S</a></span>
                        <span><a class="alpha" href="#">T</a></span>
                        <span><a class="alpha" href="#">U</a></span>
                        <span><a class="alpha" href="#">V</a></span>
                        <span><a class="alpha" href="#">W</a></span>
                        <span><a class="alpha" href="#">X</a></span>
                        <span><a class="alpha" href="#">Y</a></span>
                        <span><a class="alpha" href="#">Z</a></span>
                        <span><a class="alpha" href="#">123</a></span>
                    </div>
                </div>
                <div id="loder" style="display: none">
                    <img src="<?php echo BRAND_PLUGIN_URL . '/asset/img/ajax-loader.gif'; ?>">
                </div>
                <div id="dispat-div" class="brand-list">

                    <?php
                    if (is_array($categories) && count($categories) > 0) {
                        foreach ($categories as $term) {
                            $count = "";
                            if ($instance['post_counts'] == 1)
                                $count = '( ' . esc_html($term->count) . ' )  ';
                            ?>
                            <div class="single-brand">
                                <a href="<?php echo get_term_link($term, 'product_brand'); ?>">
                                    <span><?php echo $term->name . $count; ?></span>
                                </a>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
                <?php
            } elseif ($instance['show'] == 'carousel') {
                if (is_array($categories) && count($categories) > 0) {
                    ?>
                    <script>
                        jQuery(document).ready(function ($) {
                            $('.slider1').bxSlider({
                                auto: true,
                                slideWidth: 200,
                                minSlides: 2,
                                maxSlides: 2,
                                slideMargin: 10
                            });
                        });
                    </script>
                    <div class="slider1">
                        <?php
                        foreach ($categories as $term) {
                            $count = "";
                            if ($instance['post_counts'] == 1)
                                $count = '( ' . esc_html($term->count) . ' )  ';
                            ?>
                            <div class="slide">
                                <a href="<?php echo get_term_link($term, 'product_brand'); ?>">
                                    <img src="<?php echo brand_taxonomy_image_url($term->term_id) ? brand_taxonomy_image_url($term->term_id) : BRAND_IMAGE_PLACEHOLDER; ?>">
                                    <div class="brand-carousel-caption">
                                        <?php echo $term->name . $count; ?>
                                    </div>
                                </a>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <?php
                }
            } elseif ($instance['show'] == 'thumbnail') {
                if (is_array($categories) && count($categories) > 0) {
                    ?>
                    <script>
                        jQuery(function ($) {
                            $('.logo').tooltip({
                                position: {
                                    my: "center bottom-5",
                                    at: "center top",
                                    using: function (position, feedback) {
                                        $(this).css(position);
                                        $("<div>")
                                                .addClass("arrow")
                                                .addClass(feedback.vertical)
                                                .addClass(feedback.horizontal)
                                                .appendTo(this);
                                    }
                                }
                            });
                        });
                    </script>

                    <ul class="brand-logo-holder">
                        <?php
                        foreach ($categories as $term) {
                            $count = "";
                            if ($instance['post_counts'] == 1)
                                $count = '( ' . esc_html($term->count) . ' )  ';
                            ?>
                            <li class="logo" title="<?php echo $term->name; ?>">
                                <a href="<?php echo get_term_link($term, 'product_brand'); ?>">
                                    <img src="<?php echo brand_taxonomy_image_url($term->term_id) ? brand_taxonomy_image_url($term->term_id) : BRAND_IMAGE_PLACEHOLDER; ?>">
                                </a>
                            </li>
                            <?php
                        }
                        ?> </ul>
                        <?php
                }
            }
        }
        echo $args['after_widget'];
    }

    public function form($instance) {
        $instance = wp_parse_args((array) $instance, array('title' => ''));
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'woocommerce-brands'); ?></label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php
        if (isset($instance['title'])) {
            echo esc_attr($instance['title']);
        }
        ?>" /></p>

        <p><label for="<?php echo $this->get_field_id('show'); ?>"><?php _e('Display Show:', 'woocommerce-brands'); ?></label>
            <select class='widefat' id="<?php echo $this->get_field_id('show'); ?>"
                    name="<?php echo $this->get_field_name('show'); ?>" type="text">
                <option value='dropdown' <?php selected(@$instance['show'], "dropdown", 1); ?>>
                    Display DropDown
                </option>
                <option value='a-z' <?php selected(@$instance['show'], "a-z", 1); ?>>
                    Display A-Z
                </option>
                <option value='carousel' <?php selected(@$instance['show'], "carousel", 1); ?>>
                    Display Carousel
                </option>
                <option value='thumbnail' <?php selected(@$instance['show'], "thumbnail", 1); ?>>
                    Display Thumbnail
                </option>
            </select>
        </p>

                                                                                                                                <!--        <p><input id="rss-show-summary" name="<?php echo $this->get_field_name('featured'); ?>" type="checkbox" value="1" <?php checked(@$instance['featured'], 1); ?> />
                                                                                                                                            <label for="rss-show-summary"><?php echo _e('Display Only featured?', 'woocommerce-brands'); ?></label></p>		-->
        <p><input id="rss-show-summary" name="<?php echo $this->get_field_name('post_counts'); ?>" type="checkbox" value="1" <?php checked(@$instance['post_counts'], 1); ?> />
            <label for="rss-show-summary"><?php echo _e('Show post counts', 'woocommerce-brands'); ?></label></p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : '';
        $instance['show'] = $new_instance['show'];
//        $instance['featured'] = isset($new_instance['featured']) ? (int) $new_instance['featured'] : 0;
        $instance['post_counts'] = isset($new_instance['post_counts']) ? (int) $new_instance['post_counts'] : 0;
        return $instance;
    }

}

register_widget('pw_brands_Widget');
