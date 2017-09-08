<?php
/*
  Plugin Name: Woocomerce Brands
  Plugin URI: http://www.itsl.net
  Description: Woocommerce Brands Plugin.
  Author: Infoway LLC
  Version: 1.0.0
  Author URI: http://www.infoway.us
 */

/**
 * Check if WooCommerce is active
 * */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!defined('BRAND_PLUGIN_URL'))
        define('BRAND_PLUGIN_URL', untrailingslashit(plugins_url('', __FILE__)));

    define('BRAND_IMAGE_PLACEHOLDER', BRAND_PLUGIN_URL . "/assets/img/no_image.jpg");

    add_action('woocommerce_init', 'woocommerce_loaded');

    function woocommerce_loaded() {
        create_taxonomies();
        add_action('product_brand_add_form_fields', 'brand_add_texonomy_field');
        add_action('product_brand_edit_form_fields', 'brand_edit_texonomy_field');
        add_filter('manage_edit-product_brand_columns', 'brand_taxonomy_columns');
        add_filter('manage_product_brand_custom_column', 'brand_taxonomy_column', 10, 3);
        add_action('woocommerce_after_shop_loop_item', 'display_brand', 9);
        add_action('wp_enqueue_scripts', 'brand_scripts');
        include_once( 'class/widget.php' );
    }

    function brand_scripts() {
        wp_enqueue_style('jquery-bxslider-style', BRAND_PLUGIN_URL . "/assets/css/jquery.bxslider.css");
        wp_enqueue_style('perfect-scrollbar-min-style', BRAND_PLUGIN_URL . "/assets/css/perfect-scrollbar.min.css");
        wp_enqueue_style('jquery-ui-css', BRAND_PLUGIN_URL . "/assets/css/jquery-ui.css");
        wp_enqueue_script('jquery-bxslider', BRAND_PLUGIN_URL . '/assets/js/jquery.bxslider.js', array(), '1.0.0', true);
        wp_enqueue_script('perfect-scrollbar-jquery', BRAND_PLUGIN_URL . '/assets/js/perfect-scrollbar.jquery.js', array(), '1.0.0', true);
        wp_enqueue_script('jquery-ui-tooltip');
        wp_enqueue_style('plugin-style', BRAND_PLUGIN_URL . "/assets/css/custom.css");
    }

    /* Start of Brand Image */

    function brand_add_style() {
        echo '<style type="text/css" media="screen">
		th.column-thumb {width:60px;}
		.form-field img.taxonomy-image {border:1px solid #eee;max-width:300px;max-height:300px;}
		.inline-edit-row fieldset .thumb label span.title {width:48px;height:48px;border:1px solid #eee;display:inline-block;}
		.column-thumb span {width:48px;height:48px;border:1px solid #eee;display:inline-block;}
		.inline-edit-row fieldset .thumb img,.column-thumb img {width:48px;height:48px;}
	</style>';
    }

// add image field in add form
    function brand_add_texonomy_field() {
        if (get_bloginfo('version') >= 3.5)
            wp_enqueue_media();
        else {
            wp_enqueue_style('thickbox');
            wp_enqueue_script('thickbox');
        }

        echo '<div class="form-field">
		<label for="taxonomy_image">' . __('Image', 'zci') . '</label>
		<input type="text" name="taxonomy_image" id="taxonomy_image" value="" />
		<br/>
		<button class="brand_upload_image_button button">' . __('Upload/Add image', 'zci') . '</button>
	</div>' . brand_script();
    }

// add image field in edit form
    function brand_edit_texonomy_field($taxonomy) {
        if (get_bloginfo('version') >= 3.5)
            wp_enqueue_media();
        else {
            wp_enqueue_style('thickbox');
            wp_enqueue_script('thickbox');
        }

        if (brand_taxonomy_image_url($taxonomy->term_id, NULL, TRUE) == BRAND_IMAGE_PLACEHOLDER)
            $image_text = "";
        else
            $image_text = brand_taxonomy_image_url($taxonomy->term_id, NULL, TRUE);
        echo '<tr class="form-field">
		<th scope="row" valign="top"><label for="taxonomy_image">' . __('Image', 'zci') . '</label></th>
		<td><img class="taxonomy-image" src="' . brand_taxonomy_image_url($taxonomy->term_id, NULL, TRUE) . '"/><br/><input type="text" name="taxonomy_image" id="taxonomy_image" value="' . $image_text . '" /><br />
		<button class="brand_upload_image_button button">' . __('Upload/Add image', 'zci') . '</button>
		<button class="brand_remove_image_button button">' . __('Remove image', 'zci') . '</button>
		</td>
	</tr>' . brand_script();
    }

// upload using wordpress upload
    function brand_script() {
        return '<script type="text/javascript">
	    jQuery(document).ready(function($) {
			var wordpress_ver = "' . get_bloginfo("version") . '", upload_button;
			$(".brand_upload_image_button").click(function(event) {
				upload_button = $(this);
				var frame;
				if (wordpress_ver >= "3.5") {
					event.preventDefault();
					if (frame) {
						frame.open();
						return;
					}
					frame = wp.media();
					frame.on( "select", function() {
						// Grab the selected attachment.
						var attachment = frame.state().get("selection").first();
						frame.close();
						if (upload_button.parent().prev().children().hasClass("tax_list")) {
							upload_button.parent().prev().children().val(attachment.attributes.url);
							upload_button.parent().prev().prev().children().attr("src", attachment.attributes.url);
						}
						else
							$("#taxonomy_image").val(attachment.attributes.url);
					});
					frame.open();
				}
				else {
					tb_show("", "media-upload.php?type=image&amp;TBRAND_iframe=true");
					return false;
				}
			});
			
			$(".brand_remove_image_button").click(function() {
				$("#taxonomy_image").val("");
				$(this).parent().siblings(".title").children("img").attr("src","' . BRAND_IMAGE_PLACEHOLDER . '");
				$(".inline-edit-col :input[name=\'taxonomy_image\']").val("");
				return false;
			});
			
			if (wordpress_ver < "3.5") {
				window.send_to_editor = function(html) {
					imgurl = $("img",html).attr("src");
					if (upload_button.parent().prev().children().hasClass("tax_list")) {
						upload_button.parent().prev().children().val(imgurl);
						upload_button.parent().prev().prev().children().attr("src", imgurl);
					}
					else
						$("#taxonomy_image").val(imgurl);
					tb_remove();
				}
			}
			
			$(".editinline").live("click", function(){  
			    var tax_id = $(this).parents("tr").attr("id").substr(4);
			    var thumb = $("#tag-"+tax_id+" .thumb img").attr("src");
				if (thumb != "' . BRAND_IMAGE_PLACEHOLDER . '") {
					$(".inline-edit-col :input[name=\'taxonomy_image\']").val(thumb);
				} else {
					$(".inline-edit-col :input[name=\'taxonomy_image\']").val("");
				}
				$(".inline-edit-col .title img").attr("src",thumb);
			    return false;  
			});  
	    });
	</script>';
    }

// save our taxonomy image while edit or save term
    add_action('edit_term', 'brand_save_taxonomy_image');
    add_action('create_term', 'brand_save_taxonomy_image');

    function brand_save_taxonomy_image($term_id) {
        if (isset($_POST['taxonomy_image']))
            update_option('brand_taxonomy_image' . $term_id, $_POST['taxonomy_image']);
    }

// get attachment ID by image url
    function brand_get_attachment_id_by_url($image_src) {
        global $wpdb;
        $query = "SELECT ID FROM {$wpdb->posts} WHERE guid = '$image_src'";
        $id = $wpdb->get_var($query);
        return (!empty($id)) ? $id : NULL;
    }

// get taxonomy image url for the given term_id (Place holder image by default)
    function brand_taxonomy_image_url($term_id = NULL, $size = NULL, $return_placeholder = FALSE) {
        if (!$term_id) {
            if (is_category())
                $term_id = get_query_var('cat');
            elseif (is_tax()) {
                $current_term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
                $term_id = $current_term->term_id;
            }
        }

        $taxonomy_image_url = get_option('brand_taxonomy_image' . $term_id);
        if (!empty($taxonomy_image_url)) {
            $attachment_id = brand_get_attachment_id_by_url($taxonomy_image_url);
            if (!empty($attachment_id)) {
                if (empty($size))
                    $size = 'full';
                $taxonomy_image_url = wp_get_attachment_image_src($attachment_id, $size);
                $taxonomy_image_url = $taxonomy_image_url[0];
            }
        }

        if ($return_placeholder)
            return ($taxonomy_image_url != '') ? $taxonomy_image_url : BRAND_IMAGE_PLACEHOLDER;
        else
            return $taxonomy_image_url;
    }

    function brand_quick_edit_custom_box($column_name, $screen, $name) {
        if ($column_name == 'thumb')
            echo '<fieldset>
		<div class="thumb inline-edit-col">
			<label>
				<span class="title"><img src="" alt="Thumbnail"/></span>
				<span class="input-text-wrap"><input type="text" name="taxonomy_image" value="" class="tax_list" /></span>
				<span class="input-text-wrap">
					<button class="brand_upload_image_button button">' . __('Upload/Add image', 'zci') . '</button>
					<button class="brand_remove_image_button button">' . __('Remove image', 'zci') . '</button>
				</span>
			</label>
		</div>
	</fieldset>';
    }

    /**
     * Thumbnail column added to category admin.
     *
     * @access public
     * @param mixed $columns
     * @return void
     */
    function brand_taxonomy_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['thumb'] = __('Image', 'zci');

        unset($columns['cb']);

        return array_merge($new_columns, $columns);
    }

    /**
     * Thumbnail column value added to category admin.
     *
     * @access public
     * @param mixed $columns
     * @param mixed $column
     * @param mixed $id
     * @return void
     */
    function brand_taxonomy_column($columns, $column, $id) {
        if ($column == 'thumb')
            $columns = '<span><img src="' . brand_taxonomy_image_url($id, NULL, TRUE) . '" alt="' . __('Thumbnail', 'zci') . '" class="wp-post-image" /></span>';

        return $columns;
    }

// change 'insert into post' to 'use this image'
    function brand_change_insert_button_text($safe_text, $text) {
        return str_replace("Insert into Post", "Use this image", $text);
    }

// style the image in category list
    if (strpos($_SERVER['SCRIPT_NAME'], 'edit-tags.php') > 0) {
        add_action('admin_head', 'brand_add_style');
        add_action('quick_edit_custom_box', 'brand_quick_edit_custom_box', 10, 3);
        add_filter("attribute_escape", "brand_change_insert_button_text", 10, 2);
    }

    /* End of Brand Image */

    function display_brand() {
        global $product;
        $term_list = wp_get_post_terms($product->id, 'product_brand', array("fields" => "all"));
        if (is_array($term_list) && count($term_list) > 0) {
            echo '<div class="wb-posted_in">Brand:
                    <a rel="tag" href="' . get_term_link($term_list[0]->term_id, 'product_brand') . '">' . $term_list[0]->name . '</a>
             </div>';
        }
    }

    function create_taxonomies() {
        // Add new taxonomy, make it hierarchical (like categories)
        $shop_page_id = woocommerce_get_page_id('shop');

        $base_slug = $shop_page_id > 0 && get_page($shop_page_id) ? get_page_uri($shop_page_id) : 'shop';

        $category_base = get_option('woocommerce_prepend_shop_page_to_urls') == "yes" ? trailingslashit($base_slug) : '';

        $cap = version_compare(WOOCOMMERCE_VERSION, '2.0', '<') ? 'manage_woocommerce_products' : 'edit_products';
        $labels = array(
            'name' => __('Brands', 'woocommerce-brands'),
            'singular_name' => __('Brands', 'woocommerce-brands'),
            'search_items' => __('Search Genres', 'woocommerce-brands'),
            'all_items' => __('All Brands', 'woocommerce-brands'),
            'parent_item' => __('Parent Brands', 'woocommerce-brands'),
            'parent_item_colon' => __('Parent Brands:', 'woocommerce-brands'),
            'edit_item' => __('Edit Brands', 'woocommerce-brands'),
            'update_item' => __('Update Brands', 'woocommerce-brands'),
            'add_new_item' => __('Add New Brands', 'woocommerce-brands'),
            'new_item_name' => __('New Brands Name', 'woocommerce-brands'),
            'menu_name' => 'Brand',
        );

        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_in_nav_menus' => true,
            'capabilities' => array(
                'manage_terms' => $cap,
                'edit_terms' => $cap,
                'delete_terms' => $cap,
                'assign_terms' => $cap
            ),
            'rewrite' => array('slug' => $category_base . __('brand', 'woocommerce-brands'), 'with_front' => false, 'hierarchical' => true)
        );
        register_taxonomy('product_brand', array('product'), apply_filters('register_taxonomy_product_brand', $args));
    }

    add_action('wp_ajax_alpha_search', 'ajaxAlphaSearch');
    add_action('wp_ajax_nopriv_alpha_search', 'ajaxAlphaSearch');

    if (!function_exists('ajaxAlphaSearch')) {

        function ajaxAlphaSearch() {
            $response_arr = ['flag' => FALSE, 'result' => NULL];
            $number_array = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0');
            $search_string = $_POST['search_string'];
            $search_count = $_POST['count'];
            $taxonomy = array('product_brand');
            $args = array(
                'orderby' => 'name',
                'order' => 'ASC',
                'hide_empty' => FALSE,
                'fields' => 'all',
                'hierarchical' => true,
                'child_of' => 0,
                'childless' => false,
                'name__like' => '',
                'description__like' => '',
                'pad_counts' => false,
                'offset' => '',
                'search' => '',
                'cache_domain' => 'core'
            );

            $terms = get_terms($taxonomy, $args);
            if (is_array($terms) && count($terms) > 0) {
                foreach ($terms as $term) {
                    if ($search_count == 1)
                        $count = '( ' . esc_html($term->count) . ' )  ';
                    if ($search_string == 'All') {
                        $response_arr['result'] .= '<div class="single-brand">
                                <a href="' . get_term_link($term, 'product_brand') . '">
                                    <span>' . $term->name . $count . '</span>
                                </a>
                            </div>';
                        $response_arr['flag'] = TRUE;
                    } elseif ($search_string == '123') {
                        if (in_array($term->name[0], $number_array)) {
                            $response_arr['result'] .= '<div class="single-brand">
                                <a href="' . get_term_link($term, 'product_brand') . '">
                                    <span>' . $term->name . $count . '</span>
                                </a>
                            </div>';
                            $response_arr['flag'] = TRUE;
                        }
                    } else {
                        if ($term->name[0] == $search_string) {
                            $response_arr['result'] .= '<div class="single-brand">
                                <a href="' . get_term_link($term, 'product_brand') . '">
                                    <span>' . $term->name . $count . '</span>
                                </a>
                            </div>';
                            $response_arr['flag'] = TRUE;
                        }
                    }
                }
            }
            echo json_encode($response_arr);
            exit();
        }

    }
}