<?php

// File Security Check
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! function_exists( 'dt_woocommerce_enqueue_scripts' ) ) :

	/**
	 * Enqueue stylesheets and scripts.
	 */
	function dt_woocommerce_enqueue_scripts() {
		// remove woocommerce styles
		add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
	}

endif;

if ( ! function_exists( 'dt_wc_upsell_display_args_filter' ) ) {

	/**
	 * Display upsell in 5 columns.
	 *
	 * @param $args
	 * @return mixed
	 */
	function dt_wc_upsell_display_args_filter( $args ) {
		$args['columns'] = 5;
		return $args;
	}
}

if ( ! function_exists( 'woocommerce_cross_sell_display' ) ) {

	/**
	 * Output the cart cross-sells.
	 * (override)
	 *
	 * @param  integer $posts_per_page
	 * @param  integer $columns
	 * @param  string $orderby
	 */
	function woocommerce_cross_sell_display( $posts_per_page = 5, $columns = 5, $orderby = 'rand') {
	    if ( is_checkout() ) {
	      return;
	    }
	    // Get visble cross sells then sort them at random.
	    $cross_sells = array_filter( array_map( 'wc_get_product', WC()->cart->get_cross_sells() ), 'wc_products_array_filter_visible' );

	    // Handle orderby and limit results.
	    $orderby     = apply_filters( 'woocommerce_cross_sells_orderby', $orderby );
	    $cross_sells = wc_products_array_orderby( $cross_sells, $orderby );
	    $posts_per_page       = apply_filters( 'woocommerce_cross_sells_total', $posts_per_page );
	    $cross_sells = $posts_per_page > 0 ? array_slice( $cross_sells, 0, $posts_per_page ) : $cross_sells;

	    wc_get_template( 'cart/cross-sells.php', array(
	      'cross_sells'        => $cross_sells,

	      // Not used now, but used in previous version of up-sells.php.
	      'posts_per_page'   => $posts_per_page,
	      'orderby'       => $orderby,
	      'columns'       => $columns,
	    ) );
	  }
	 
}


if ( ! function_exists( 'dt_woocommerce_related_products_args' ) ) :

	/**
	 * Change related products args to array( 'posts_per_page' => 5, 'columns' => 5, 'orderby' => 'date' ).
	 * 
	 * @param  array $args
	 * @return array
	 */
	function dt_woocommerce_related_products_args( $args ) {
		$args['posts_per_page'] = of_get_option( 'woocommerce_rel_products_max', 5 );
		$args['columns'] = 5;
		$args['orderby'] = 'date';

		return $args;
	}

endif;

if ( ! function_exists( 'dt_woocommerce_body_class' ) ) :

	/**
	 * Body classes filter.
	 * 
	 * @param  array $classes
	 * @return array
	 */
	function dt_woocommerce_body_class( $classes ) {
		if ( is_product() && in_array( presscore_get_config()->get( 'header_title' ), array( 'enabled', 'fancy' ) ) ) {
			$classes[] = 'hide-product-title';
		}
		return $classes;
	}

endif;

if ( ! function_exists( 'dt_woocommerce_before_main_content' ) ) :

	/**
	 * Display main content open tags and fire hooks.
	 */
			add_action( 'presscore_before_main_container', 'presscore_wc_cart_progress', 17 );
			function presscore_wc_cart_progress() {
				if ( (is_cart() || is_checkout()) && of_get_option( 'woocommerce_show_steps' ) )
			    	woocommerce_get_template('/checkout/header.php');
		
	}
	function dt_woocommerce_before_main_content () {

		// remove woocommerce breadcrumbs
		remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

		// only for shop
		if ( is_shop() || is_product_category() || is_product_tag() ) {

			// remove woocommerce title
			add_filter( 'woocommerce_show_page_title', '__return_false');
		}

	?>
		<!-- Content -->
		<div id="content" class="content" role="main">
	<?php
	}

endif;

if ( ! function_exists( 'dt_woocommerce_after_main_content' ) ) :

	/**
	 * Display main content end tags.
	 */
	function dt_woocommerce_after_main_content () {
	?>
		</div>
	<?php
	}

endif;

if ( ! function_exists( 'dt_woocommerce_mini_cart' ) ) :

	/**
	 * Display customized shop mini cart.
	 */
	function dt_woocommerce_mini_cart() {
		get_template_part('inc/mods/compatibility/woocommerce/front/templates/cart/mod-wc-mini-cart');

		// enqueue custom script
		wp_enqueue_script( 'dt-wc-custom', get_template_directory_uri() . '/inc/mods/compatibility/woocommerce/assets/js/mod-wc-scripts.js', array( 'jquery' ), wp_get_theme()->get( 'Version' ), true );
	}

endif;

if ( ! function_exists( 'dt_woocommerce_replace_theme_breadcrumbs' ) ) :

	/**
	 * Breadcrumbs filter
	 * 
	 * @param  string $html
	 * @param  array  $args
	 * @return string
	 */
	function dt_woocommerce_replace_theme_breadcrumbs( $html = '', $args = array() ) {

		if ( ! $html ) {

			ob_start();
			woocommerce_breadcrumb( array(
				'delimiter' => '',
				'wrap_before' => '<div class="assistive-text"></div><ol' . $args['listAttr'] . ' xmlns:v="http://rdf.data-vocabulary.org/#">',
				'wrap_after' => '</ol>',
				'before' => '<li typeof="v:Breadcrumb">',
				'after' => '</li>',
				'home' => __( 'Home', 'the7mk2' ),
			) );
			$html = ob_get_clean();

			$html = apply_filters( 'presscore_get_breadcrumbs', $args['beforeBreadcrumbs'] . $html . $args['afterBreadcrumbs'] );

		}

		return $html;
	}

endif;

if ( ! function_exists( 'dt_woocommerce_template_loop_product_title' ) ) :

	/**
	 * Show the product title in the product loop.
	 */
	function dt_woocommerce_template_loop_product_title() {
		if ( presscore_config()->get( 'show_titles' ) && get_the_title() ) : ?>
			<h4 class="entry-title">
				<a href="<?php the_permalink(); ?>" title="<?php echo the_title_attribute( 'echo=0' ); ?>" rel="bookmark"><?php
					the_title();
				?></a>
			</h4>
		<?php endif;
	}

endif;
if ( ! function_exists( 'dt_woocommerce_template_loop_product_short_desc' ) ) :

	/**
	 * Show the product title in the product loop.
	 */
	function dt_woocommerce_template_loop_product_short_desc() {
    	wc_get_template( 'single-product/short-description.php' );
  	}
endif;
if ( ! function_exists( 'dt_woocommerce_template_loop_category_title' ) ) :

	/**
	 * Show the subcategory title in the product loop.
	 */
	function dt_woocommerce_template_loop_category_title( $category ) {
		if ( presscore_config()->get( 'show_titles' ) ) :
		?>
			<h3 class="entry-title">
				<a href="<?php echo get_term_link( $category->slug, 'product_cat' ); ?>"><?php
					echo $category->name;

					if ( $category->count > 0 )
						echo apply_filters( 'woocommerce_subcategory_count_html', ' <mark class="count">(' . $category->count . ')</mark>', $category );
				?></a>
			</h3>
		<?php
		endif;
	}

endif;

if ( ! function_exists( 'dt_woocommerce_get_page_title' ) ) :

	/**
	 * Wrap for woocommerce_page_title( false ).
	 * 
	 * @param  string $title
	 * @return string
	 */
	function dt_woocommerce_get_page_title( $title = '' ) {
		return woocommerce_page_title( false );
	}

endif;

if ( ! function_exists( 'dt_woocommerce_template_product_desc_for_list' ) ) :

	/**
	 * Display product description under image template.
	 */
	function dt_woocommerce_template_product_desc_for_list() {
		get_template_part( 'inc/mods/compatibility/woocommerce/front/templates/product/mod-wc-product-desc-list-layout' );
	}

endif;

if ( ! function_exists( 'dt_woocommerce_template_product_desc_under' ) ) :

	/**
	 * Display product description under image template.
	 */
	function dt_woocommerce_template_product_desc_under() {
		get_template_part( 'inc/mods/compatibility/woocommerce/front/templates/product/mod-wc-product-desc-under' );
	}

endif;



if ( ! function_exists( 'dt_woocommerce_get_product_description' ) ) :

	/**
	 * Display product description template.
	 */
	function dt_woocommerce_get_product_description() {
		ob_start();
		get_template_part( 'inc/mods/compatibility/woocommerce/front/templates/product/mod-wc-product-description' );

		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

endif;

if ( ! function_exists( 'dt_woocommerce_template_subcategory_desc_under' ) ) :

	/**
	 * Display subcategory description under image template.
	 */
	function dt_woocommerce_template_subcategory_desc_under() {
		get_template_part( 'inc/mods/compatibility/woocommerce/front/templates/subcategory/mod-wc-subcategory-desc-under' );
	}

endif;


if ( ! function_exists( 'dt_woocommerce_template_subcategory_description' ) ) :

	/**
	 * Display subcategory description template.
	 */
	function dt_woocommerce_template_subcategory_description() {
		get_template_part( 'inc/mods/compatibility/woocommerce/front/templates/subcategory/mod-wc-subcategory-description' );
	}

endif;

/* Get Hover image for WooCommerce Grid */

function dt_woocommerce_get_alt_product_thumbnail() {
    if ( of_get_option( 'woocommerce_hover_image' ) ) {
	    global $product;
	    $attachment_ids = $product->get_gallery_attachment_ids();
	    $class = 'show-on-hover back-image';
	    $size = 'shop_catalog';
	    $image_size = apply_filters( 'single_product_archive_thumbnail_size', $size );

	    if ( $attachment_ids ) {
	        $loop = 0;
	        foreach ( $attachment_ids as $attachment_id ) {
	            $image_link = wp_get_attachment_url( $attachment_id);
	            if ( ! $image_link )
	                continue;
	            $loop++;
	            echo apply_filters('dt_woocommerce_get_alt_product_thumbnail', wp_get_attachment_image( $attachment_id, 'shop_catalog', false, array( 'class' => $class, $image_size )));
	            if ($loop == 1) break;
	        }
	    }
	}
}

add_action( 'dt_woocommerce_shop_loop_images', 'dt_woocommerce_get_alt_product_thumbnail', 11 );

/* Remove and add Product image */
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
add_action( 'dt_woocommerce_shop_loop_images', 'woocommerce_template_loop_product_thumbnail', 10 );
if ( ! function_exists( 'presscore_wc_template_loop_product_thumbnail' ) ) :

	/**
	 * Display woocommerce_template_loop_product_thumbnail() wrapped with 'a' tag.
	 * 
	 * @param  string $class
	 */

	function presscore_wc_template_loop_product_thumbnail( $class = '' ) {
		if ( presscore_lazy_loading_enabled() ) {
			$class .= ' layzr-bg';
		}
		if ( of_get_option( 'woocommerce_hover_image' ) ) {
			echo '<a href="' . get_permalink() . '" class="' . esc_attr( $class ) . '">';
			do_action( 'dt_woocommerce_shop_loop_images' );
			echo '</a>';
		}else{
			ob_start();
			woocommerce_template_loop_product_thumbnail();
			$img = ob_get_contents();
			ob_end_clean();

			$img = str_replace( 'wp-post-image', 'wp-post-image preload-me', $img );

			echo '<a href="' . get_permalink() . '" class="' . esc_attr( $class ) . '">' . $img . '</a>';
		}
	}

endif;

if ( ! function_exists( 'presscore_wc_add_masonry_lazy_load_attrs' ) ) :

	/**
	 * Add lazy loading images attributes.
	 */
	function presscore_wc_add_masonry_lazy_load_attrs() {
		add_filter( 'wp_get_attachment_image_attributes', 'presscore_wc_image_lazy_loading', 10, 3 );
	}

endif;

if ( ! function_exists( 'presscore_wc_remove_masonry_lazy_load_attrs' ) ) :

	/**
	 * Remove lazy loading images attributes.
	 */
	function presscore_wc_remove_masonry_lazy_load_attrs() {
		remove_filter( 'wp_get_attachment_image_attributes', 'presscore_wc_image_lazy_loading', 10 );
	}

endif;

if ( ! function_exists( 'presscore_wc_image_lazy_loading' ) ) :

	/**
	 * Add lazy loading capability to images.
	 *
	 * @since  3.2.1
	 *
	 * @param  array $attr
	 * @param  WP_Post $attachment
	 * @param  string $size
	 * @return array
	 */
	function presscore_wc_image_lazy_loading( $attr, $attachment, $size ) {
		if ( presscore_lazy_loading_enabled() ) {
			$attr['data-src'] = $attr['src'];
			$image = wp_get_attachment_image_src( $attachment->ID, $size );
			$attr['src'] = "data:image/svg+xml;charset=utf-8,%3Csvg xmlns%3D'http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg' viewBox%3D'0 0 {$image[1]} {$image[2]}'%2F%3E";
			if( in_array( presscore_config()->get( 'layout' ), array( 'masonry', 'grid' ) ) ){
				$lazy_class = 'iso-lazy-load';
			} else {
				$lazy_class = 'lazy-load';
			}
			$attr['class'] = ( isset( $attr['class'] ) ? $attr['class'] . " {$lazy_class}" : $lazy_class );
			if ( isset( $attr['srcset'] ) ) {
				$attr['data-srcset'] = $attr['srcset'];
				unset( $attr['srcset'], $attr['sizes'] );
			}
		}

		$attr['class'] = ( isset( $attr['class'] ) ? $attr['class'] . ' preload-me' : '' );

		return $attr;
	}

endif;

if ( ! function_exists( 'dt_woocommerce_subcategory_thumbnail' ) ) :

	/**
	 * Display woocommerce_subcategory_thumbnail() wrapped with 'a' targ.
	 * 
	 * @param  mixed $category
	 * @param  string $class
	 */
	function dt_woocommerce_subcategory_thumbnail( $category, $class = '' ) {
		ob_start();
		woocommerce_subcategory_thumbnail( $category );
		$img = ob_get_contents();
		ob_end_clean();

		$img = str_replace( '<img', '<img class="preload-me"', $img );

		echo '<a href="' . get_term_link( $category->slug, 'product_cat' ) . '" class="' . esc_attr( $class ) . '">' . $img . '</a>';
	}

endif;

if ( ! function_exists( 'dt_woocommerce_product_info_controller' ) ) :

	/**
	 * Controls product price and rating visibility.
	 */
	function dt_woocommerce_product_info_controller() {
		$config = presscore_config();

		if ( $config->get( 'product.preview.show_price' ) ) {
			add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 5 );
		}

		if ( $config->get( 'product.preview.show_rating' ) ) {
			add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 10 );
		}
	}

endif;

if ( ! function_exists( 'dt_woocommerce_get_product_icons_count' ) ) :

	/**
	 * Counts product icons for shop pages.
	 * 
	 * @return integer
	 */
	function dt_woocommerce_get_product_icons_count() {
		$config = presscore_config();

		$count = 0;

		if ( $config->get( 'product.preview.icons.show_cart' ) ) {
			$count++;
		}

		return apply_filters( 'dt_woocommerce_get_product_icons_count', $count );
	}

endif;

if ( ! function_exists( 'dt_woocommerce_product_show_content' ) ) :

	/**
	 * Controls content visibility.
	 * 
	 * @return bool
	 */
	function dt_woocommerce_product_show_content() {
		return apply_filters( 'dt_woocommerce_product_show_content', presscore_config()->get( 'post.preview.content.visible' ) );
	}

endif;

if ( ! function_exists( 'dt_woocommerce_get_product_add_to_cart_icon' ) ) :

	/**
	 * Return product add to cart icon html.
	 * 
	 * @return string
	 */
	function dt_woocommerce_get_product_add_to_cart_icon() {
		global $product;

		if ( $product && presscore_config()->get( 'product.preview.icons.show_cart' ) ) {
		    ob_start();
			woocommerce_template_loop_add_to_cart(array(
				'class' => implode( ' ', array_filter( array(
						'product_type_' . $product->get_type(),
						$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
						$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : ''
				) ) )
			));
			$html = ob_get_clean();

			return $html;
		}

		return '';
	}

endif;

if ( ! function_exists( 'dt_woocommerce_render_product_add_to_cart_icon' ) ) :

	/**
	 * Display add to cart product icon.
	 */
	function dt_woocommerce_render_product_add_to_cart_icon() {
		$icon = dt_woocommerce_get_product_add_to_cart_icon();
		if ( $icon ) {
			echo '<div class="woo-buttons">' . $icon . '</div>';
		}
	}

endif;

if ( ! function_exists( 'dt_woocommerce_get_product_details_icon' ) ) :

	/**
	 * DEPRECATED. Return product details icon html.
	 * 
	 * @param int
	 * @param mixed
	 *
	 * @return string
	 */
	function dt_woocommerce_get_product_details_icon( $post_id = null, $class = 'project-details' ) {
		if ( ! presscore_config()->get( 'show_details' ) ) {
			return '';
		}

		if ( ! $post_id ) {
			global $product;
			$post_id = $product->id;
		}

		if ( is_array( $class ) ) {
			$class = implode( ' ', $class );
		}

		$output = '<a href="' . get_permalink( $post_id ) . '" class="' . esc_attr( $class ) . '" rel="nofollow">' . __( 'Product details', 'the7mk2' ) . '</a>';

		return apply_filters( 'dt_woocommerce_get_product_details_icon', $output, $post_id, $class );
	}

endif;

if ( ! function_exists( 'dt_woocommerce_filter_product_preview_icons' ) ) :

	/**
	 * Filters product icons for shop pages.
	 * 
	 * @return string
	 */
	function dt_woocommerce_filter_product_preview_icons( $icons = '' ) {
        add_filter( 'woocommerce_loop_add_to_cart_link', 'dt_woocommerce_wrap_add_to_cart_text_in_filter_popup', 10, 2 );
		$add_to_cart_icon = dt_woocommerce_get_product_add_to_cart_icon();
        remove_filter( 'woocommerce_loop_add_to_cart_link', 'dt_woocommerce_wrap_add_to_cart_text_in_filter_popup', 10 );

		return "{$icons}{$add_to_cart_icon}";
	}

endif;

if ( ! function_exists( 'dt_woocommerce_get_product_preview_icons' ) ) :

	/**
	 * Returns product icons for shop pages.
	 * 
	 * @return string
	 */
	function dt_woocommerce_get_product_preview_icons() {
		return apply_filters( 'dt_woocommerce_get_product_preview_icons', '' );
	}

endif;

if ( ! function_exists( 'dt_woocommerce_template_config' ) ) :

	/**
	 * Return new instance of DT_WC_Template_Config
	 *
	 * @param  object $config
	 * @return object
	 */
	function dt_woocommerce_template_config( $config ) {
		return new DT_WC_Template_Config( $config );
	}

endif;

if ( ! function_exists( 'dt_woocommerce_add_product_template_to_search' ) ) :

	function dt_woocommerce_add_product_template_to_search( $html ) {
		static $products_config = array();

		if ( ! $html ) {

			$config = presscore_config();
			if ( empty( $products_config ) ) {

				$config->set( 'post.preview.description.style', 'wc_btn_on_hoover' );
				$config->set( 'post.preview.description.alignment', 'center' );
				$config->set( 'show_titles', true );
				$config->set( 'show_details', true );
				$config->set( 'product.preview.show_price', true );
				$config->set( 'product.preview.show_rating', false );
				$config->set( 'product.preview.icons.show_cart', true );

				$products_config = $config->get();

				dt_woocommerce_product_info_controller();
			} else {
				$config->reset( $products_config );
			}

			ob_start();

			presscore_wc_add_masonry_lazy_load_attrs();
			get_template_part( 'woocommerce/content-product' );
			presscore_wc_remove_masonry_lazy_load_attrs();

			$html = ob_get_contents();
			ob_end_clean();
		}
		return $html;
	}

endif;

if ( ! function_exists( 'dt_woocommerce_add_classes_to_the_search_page_wrapper' ) ) :

	/**
	 * @param array $classes
	 *
	 * @return array
	 */
    function dt_woocommerce_add_classes_to_the_search_page_wrapper( $classes ) {
        $classes[] = 'woo-hover';
        $classes[] = 'wc-img-hover';

        return $classes;
    }

endif;

if ( ! function_exists( 'dt_woocommerce_change_paypal_icon' ) ) :

	function dt_woocommerce_change_paypal_icon() {
		return WC_HTTPS::force_https_url( WC()->plugin_url() . '/includes/gateways/paypal/assets/images/paypal.png' );
	}

endif;

if ( ! function_exists( 'dt_woocommerce_filter_product_add_to_cart_text' ) ) :

	function dt_woocommerce_filter_product_add_to_cart_text( $text, $product_obj ) {
		// If have no child and not in stock.
		if ( !$product_obj->is_in_stock() ) {
			$text = __( 'Details', 'the7mk2' );
		}

		return $text;
	}

endif;

if ( ! function_exists( 'dt_woocommerce_filter_frontend_scripts_data' ) ) :

	function dt_woocommerce_filter_frontend_scripts_data( $data ) {
		$data['i18n_view_cart'] = esc_attr__( 'View cart', 'the7mk2' );
		return $data;
	}

endif;

if ( ! function_exists( 'dt_woocommerce_set_product_cart_button_position' ) ) :

	/**
	 * Choose where to display product cart button.
	 */
	function dt_woocommerce_set_product_cart_button_position() {
		if ( 'wc_btn_on_hoover' === presscore_config()->get( 'post.preview.description.style' ) || 'wc_btn_on_img' === presscore_config()->get( 'post.preview.description.style' ) ) {
		    add_filter( 'dt_woocommerce_get_product_preview_icons', 'dt_woocommerce_filter_product_preview_icons' );
		} else {
			add_action( 'woocommerce_after_shop_loop_item', 'dt_woocommerce_render_product_add_to_cart_icon', 40 );
		};
	}

endif;

if ( ! function_exists( 'dt_woocommerce_filter_masonry_container_class' ) ) :

	/**
	 * Filers masonry container class array.
	 * 
	 * @param  array  $class
	 * @return array
	 */
	function dt_woocommerce_filter_masonry_container_class( $class = array() ) {
		if ( 'under_image' === presscore_config()->get( 'post.preview.description.style' ) ) {
			$class[] = 'cart-btn-below-img';
		}  else {
			$class[] = 'cart-btn-on-img';
		};
		if ( 'wc_btn_on_hoover' === presscore_config()->get( 'post.preview.description.style' ) ) {
			$class[] = 'cart-btn-on-hover';
		}
		
		if ( 'browser_width_based' === of_get_option( 'woocommerce_shop_template_responsiveness' ) ) {
			$class[] = 'resize-by-browser-width';
		}
		if ( of_get_option( 'woocommerce_hover_image' ) ) {
			$class[] = 'wc-img-hover';
		}
		if(!of_get_option( 'woocommerce_show_masonry_desc' )){
			$class[] = 'hide-description';
		}
		return $class;
	}

endif;


if ( ! function_exists( 'dt_woocommerce_add_masonry_container_filters' ) ) :

	/**
	 * Add masonry container class filters.
	 */
	function dt_woocommerce_add_masonry_container_filters() {
		add_filter( 'presscore_masonry_container_class', 'dt_woocommerce_filter_masonry_container_class' );
	}

endif;

if ( ! function_exists( 'dt_woocommerce_remove_masonry_container_filters' ) ) :

	/**
	 * Remove masonry container class filters.
	 */
	function dt_woocommerce_remove_masonry_container_filters() {
		remove_filter( 'presscore_masonry_container_class', 'dt_woocommerce_filter_masonry_container_class' );
		remove_filter( 'presscore_masonry_container_data_atts', 'dt_woocommerce_filter_masonry_container_data_atts' );
	}

endif;

if ( ! function_exists( 'dt_woocommerce_set_product_title_to_h2_filter' ) ) :

	/**
	 * Wrap product title with h2 tag.
	 *
	 * There is h1 title on product page so we need to replace it with h2 here.
	 *
	 * @param  string $title
	 * @return string
	 */
	function dt_woocommerce_set_product_title_to_h2_filter( $title ) {
		return str_replace( array( '<h1', '</h1' ), array( '<h2', '</h2' ), $title );
	}

endif;

if ( ! function_exists( 'dt_woocommerce_share_buttons_action' ) ) :

	/**
	 * Display share buttons on product page.
	 */
	function dt_woocommerce_share_buttons_action() {
		presscore_display_share_buttons_for_post( 'product' );
	}

endif;

if ( ! function_exists( 'dt_woocommerce_hide_share_on_plugin_pages' ) ) :

	/**
	 * Filter. Hide share buttons on woocommerce checkout and cart pages.
	 *
	 * @param bool $hide_share
	 *
	 * @return bool
	 */
	function dt_woocommerce_hide_share_on_plugin_pages( $hide_share = false ) {
		if ( is_cart() || is_checkout() ) {
			return true;
		}

		return $hide_share;
	}

endif;

if ( ! function_exists( 'dt_woocommerce_product_shortcode_classes_filter' ) ) :

	/**
	 * Hook for 'presscore_masonry_container_class' filter. Removes 'iso-grid', 'iso-container', add 'wc-single-shortcode' classes.
     *
     * @param array $classes
     *
     * @return array
	 */
    function dt_woocommerce_product_shortcode_classes_filter( $classes ) {
        $classes = array_diff( $classes, array(
	        'iso-grid',
	        'iso-container',
        ) );

        $classes[] = 'wc-single-shortcode';

        return $classes;
    }

endif;

if ( ! function_exists( 'dt_woocommerce_destroy_layout' ) ) :

    function dt_woocommerce_destroy_layout() {
        presscore_config()->set( 'layout', 'none' );
    }

endif;

if ( ! function_exists( 'dt_woocommerce_wrap_add_to_cart_text_in_filter_popup' ) ) :

	/**
	 * @param string $link
	 * @param object $product
	 *
	 * @return string
	 */
    function dt_woocommerce_wrap_add_to_cart_text_in_filter_popup( $link, $product ) {
        $text = $product->add_to_cart_text();
        $link = str_replace( $text, "<span class='filter-popup'>{$text}</span>", $link );

        return $link;
    }

endif;

// **********************************************************************//
// ! Grid/List switcher
// **********************************************************************// 


add_action( 'woocommerce_before_shop_loop', 'the7_layout_switcher_wrap_start', 14 );
if ( ! function_exists( 'the7_layout_switcher_wrap_start' ) ) {

	function the7_layout_switcher_wrap_start() {
		?>
        <div class="switcher-wrap">
		<?php
	}
}

add_action( 'woocommerce_before_shop_loop', 'the7_layout_switcher_wrap_end', 35 );
if ( ! function_exists( 'the7_layout_switcher_wrap_end' ) ) {

	function the7_layout_switcher_wrap_end() {
		?>
        </div>
		<?php
	}
}

add_action('woocommerce_before_shop_loop', 'the7_layout_mode_switcher',15);
if(!function_exists('the7_layout_mode_switcher')) {
	function the7_layout_mode_switcher() {
		$current_url = the7_shop_page_link(true);

		$view_mode = of_get_option('wc_view_mode');


		if($view_mode == 'masonry_grid' || $view_mode == 'list') return;

		$url_grid = add_query_arg( 'wc_view_mode', 'masonry_grid', remove_query_arg( 'wc_view_mode', $current_url ) );
		$url_list = add_query_arg( 'wc_view_mode', 'list', remove_query_arg( 'wc_view_mode', $current_url ) );

		$current_mode = the7_get_view_mode();

		?>
		<div class="view-mode-switcher">

			<?php if($view_mode == 'view_mode'): ?>
				<a class="switch-mode-grid <?php if( $current_mode == 'masonry_grid' ) echo 'switcher-active'; ?>" href="<?php echo esc_url( $url_grid ); ?>"><i class="fa fa-th" aria-hidden="true"></i><span class="filter-popup"><?php esc_html_e('Grid view', 'the7mk2'); ?>
				</span></a>

				<a class="switch-mode-list <?php if( $current_mode == 'list' ) echo 'switcher-active'; ?>" href="<?php echo esc_url( $url_list ); ?>"><i class="fa fa-th-list" aria-hidden="true"></i><span class="filter-popup"><?php esc_html_e('List view', 'the7mk2'); ?></span></a>
			<?php endif ;?>
		</div>
		<?php

	}
}

if ( ! function_exists( 'the7_get_view_mode' ) ) {

	function the7_get_view_mode() {
		$current_mode = 'masonry_grid';

		if ( isset( $_REQUEST['wc_view_mode'] ) ) {
			$current_mode = ( $_REQUEST['wc_view_mode'] );
		} elseif ( of_get_option( 'woocommerce_shop_template_layout_default' ) == 'list_default' ) {
			$current_mode = 'list';
		}

		return $current_mode;
	}
}

if ( ! function_exists( 'the7_shop_page_link' ) ) {

	function the7_shop_page_link( $keep_query = false ) {
		if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
			$link = home_url();
		} elseif ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id( 'shop' ) ) ) {
			$link = get_post_type_archive_link( 'product' );
		} else {
			$link = get_term_link( get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
		}

		if ( $keep_query ) {
			foreach ( $_GET as $key => $val ) {
				if ( 'orderby' === $key || 'submit' === $key ) {
					continue;
				}
				$link = add_query_arg( $key, $val, $link );
			}
		}

		return $link;
	}
}