<?php
/**
 * BOSSMASTER Display child theme.
 *
 * @package BOSSMASTER_Display
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'BMD_VERSION' ) ) {
	define( 'BMD_VERSION', '1.1.2' );
}
if ( ! defined( 'BMD_DIR' ) ) {
	define( 'BMD_DIR', get_stylesheet_directory() );
}
if ( ! defined( 'BMD_URI' ) ) {
	define( 'BMD_URI', get_stylesheet_directory_uri() );
}

/**
 * Return a transparent asset URL from the bundled asset pack.
 *
 * @param string $filename Asset filename.
 * @return string
 */
function bmd_asset_url( $filename ) {
	$filename = sanitize_file_name( (string) $filename );
	if ( '' === $filename ) {
		return '';
	}
	$path = BMD_DIR . '/assets/img/transparent-assets/' . $filename;
	if ( ! is_file( $path ) ) {
		return '';
	}
	return trailingslashit( BMD_URI . '/assets/img/transparent-assets' ) . $filename;
}

$upgrade_file = BMD_DIR . '/inc/bmd-display-upgrade.php';
if ( is_file( $upgrade_file ) ) {
	require_once $upgrade_file;
}

/**
 * Theme setup.
 */
function bmd_setup() {
	load_child_theme_textdomain( 'bossmaster-display', BMD_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array( 'height' => 88, 'width' => 360, 'flex-height' => true, 'flex-width' => true ) );
	add_image_size( 'bmd-card', 520, 780, true );
	add_image_size( 'bmd-wide', 960, 540, true );
}
add_action( 'after_setup_theme', 'bmd_setup', 20 );

/**
 * Front-end assets.
 */
function bmd_enqueue_assets() {
	$css_file = BMD_DIR . '/assets/css/bossmaster-display.css';
	$js_file  = BMD_DIR . '/assets/js/bossmaster-display.js';

	wp_enqueue_style(
		'bossmaster-display',
		BMD_URI . '/assets/css/bossmaster-display.css',
		array( 'wpst-custom-style' ),
		BMD_VERSION . '.' . ( is_file( $css_file ) ? filemtime( $css_file ) : '0' )
	);
	wp_add_inline_style(
		'bossmaster-display',
		'body{background:#1A0A35!important;color:#fff!important;}'
	);
	wp_enqueue_script(
		'bossmaster-display',
		BMD_URI . '/assets/js/bossmaster-display.js',
		array(),
		BMD_VERSION . '.' . ( is_file( $js_file ) ? filemtime( $js_file ) : '0' ),
		true
	);
	wp_localize_script(
		'bossmaster-display',
		'bmdData',
		array(
			'homeUrl' => home_url( '/' ),
			'light'   => __( 'โหมดสว่าง', 'bossmaster-display' ),
			'dark'    => __( 'โหมดมืด', 'bossmaster-display' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'bmd_enqueue_assets', 120 );

/**
 * Body classes.
 */
function bmd_body_classes( $classes ) {
	$classes[] = 'bossmaster-display';
	$classes[] = 'bmd-theme-' . sanitize_html_class( get_theme_mod( 'bmd_default_theme', 'dark' ) );
	$featured_style = bmd_sanitize_choice( get_theme_mod( 'bmd_featured_style', 'carousel' ), array( 'carousel', 'grid' ), 'carousel' );
	$nav_mode = bmd_sanitize_choice( get_theme_mod( 'bmd_nav_mode', 'auto' ), array( 'auto', 'compact', 'stacked' ), 'auto' );
	$classes[] = 'bmd-featured-style-' . sanitize_html_class( $featured_style );
	$classes[] = 'bmd-nav-mode-' . sanitize_html_class( $nav_mode );
	$classes[] = 'bmd-ui-' . sanitize_html_class( bmd_get_ui_language() );
	return $classes;
}
add_filter( 'body_class', 'bmd_body_classes' );

/**
 * Return the active UI language for theme text.
 *
 * @return string
 */
function bmd_get_ui_language() {
	$lang = '';
	if ( isset( $_GET['lang'] ) ) {
		$lang = sanitize_key( wp_unslash( (string) $_GET['lang'] ) );
	}
	if ( '' === $lang && isset( $_COOKIE['bmd-ui-lang'] ) ) {
		$lang = sanitize_key( wp_unslash( (string) $_COOKIE['bmd-ui-lang'] ) );
	}
	if ( ! in_array( $lang, array( 'en', 'th' ), true ) ) {
		$lang = 'th';
	}
	return $lang;
}

/**
 * Return a theme UI string in the active language.
 *
 * @param string $th Thai string.
 * @param string $en English string.
 * @return string
 */
function bmd_ui_text( $th, $en = '' ) {
	$th = (string) $th;
	$en = (string) $en;
	return 'en' === bmd_get_ui_language() && '' !== $en ? $en : $th;
}

/**
 * Normalize a homepage section key so the theme can consume legacy values.
 *
 * @param string $key Section key.
 * @return string
 */
function bmd_normalize_home_section_key( $key ) {
	$key = sanitize_key( (string) $key );
	$map = array(
		'directories' => 'explore',
		'studio'     => 'categories',
		'studios'    => 'categories',
		'gallery_tags' => 'tags',
		'latest_videos' => 'latest',
	);
	return $map[ $key ] ?? $key;
}

/**
 * AVFP professional settings stored by WP MY BOSS.
 *
 * This theme consumes these settings as source of truth for homepage section visibility.
 *
 * @return array<string,mixed>
 */
function bmd_get_avfp_professional_settings() {
	static $settings = null;
	if ( null !== $settings ) {
		return $settings;
	}

	$stored = get_option( 'avfp_professional_settings', array() );
	if ( ! is_array( $stored ) ) {
		$stored = array();
	}

	$defaults = array(
		'home_sections_enabled' => 1,
		'home_show_featured'    => 1,
		'home_show_seo'         => 0,
		'home_show_studios'     => 1,
		'home_show_actors'      => 1,
		'home_show_tags'        => 1,
		'home_featured_count'   => 20,
		'home_studio_count'     => 20,
		'home_actor_count'      => 20,
		'home_tag_count'        => 20,
		'home_featured_slider'  => 1,
		'home_studio_slider'    => 0,
		'home_actor_slider'     => 0,
		'home_tag_slider'       => 1,
		'home_featured_view_all'=> 1,
		'home_tag_view_all'     => 0,
		'home_slider_autoplay'  => 1,
		'home_featured_autoplay'=> 1,
		'home_studio_autoplay'  => 0,
		'home_actor_autoplay'   => 0,
		'home_tag_autoplay'     => 1,
		'home_slider_interval'  => 3000,
		'home_section_order'    => 'featured,latest_videos,studio,actors,gallery_tags',
	);

	$settings = wp_parse_args( $stored, $defaults );
	return $settings;
}

/**
 * Get a single AVFP home setting value.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Default fallback.
 * @return mixed
 */
function bmd_get_avfp_professional_setting( $key, $default = null ) {
	$settings = bmd_get_avfp_professional_settings();
	return array_key_exists( $key, $settings ) ? $settings[ $key ] : $default;
}

/**
 * Whether AVFP home sections are enabled.
 *
 * @return bool
 */
function bmd_avfp_home_sections_enabled() {
	return (bool) bmd_get_avfp_professional_setting( 'home_sections_enabled', 1 );
}

/**
 * Whether a named AVFP home section should be visible.
 *
 * @param string $key Setting key.
 * @return bool
 */
function bmd_avfp_home_section_visible( $key ) {
	return ! empty( bmd_get_avfp_professional_setting( $key, 0 ) );
}

/**
 * Return normalized AVFP home section order.
 *
 * @return string[]
 */
function bmd_avfp_home_section_order() {
	$allowed = array( 'featured', 'latest_videos', 'studio', 'actors', 'gallery_tags', 'seo' );
	$order   = sanitize_text_field( (string) bmd_get_avfp_professional_setting( 'home_section_order', 'featured,latest_videos,studio,actors,gallery_tags,seo' ) );
	$parts   = array();
	foreach ( array_map( 'trim', explode( ',', $order ) ) as $part ) {
		if ( in_array( $part, $allowed, true ) && ! in_array( $part, $parts, true ) ) {
			$parts[] = $part;
		}
	}

	if ( ! in_array( 'latest_videos', $parts, true ) ) {
		$featured_index = array_search( 'featured', $parts, true );
		if ( false !== $featured_index ) {
			array_splice( $parts, $featured_index + 1, 0, array( 'latest_videos' ) );
		} else {
			array_unshift( $parts, 'latest_videos' );
		}
	}

	foreach ( $allowed as $section ) {
		if ( ! in_array( $section, $parts, true ) ) {
			$parts[] = $section;
		}
	}

	return $parts;
}

/**
 * Render AVFP-powered homepage sections with BOSSMASTER markup.
 */
function bmd_avfp_home_sections_render() {
	if ( ! bmd_avfp_home_sections_enabled() || is_paged() ) {
		return;
	}

	$sections = array();
	foreach ( bmd_avfp_home_section_order() as $section ) {
		switch ( $section ) {
			case 'featured':
				if ( bmd_avfp_home_section_visible( 'home_show_featured' ) ) {
					$sections[] = bmd_avfp_home_section_featured();
				}
				break;
			case 'latest_videos':
				$sections[] = bmd_avfp_home_section_latest();
				break;
			case 'studio':
				if ( bmd_avfp_home_section_visible( 'home_show_studios' ) ) {
					$sections[] = bmd_avfp_home_section_studios();
				}
				break;
			case 'actors':
				if ( bmd_avfp_home_section_visible( 'home_show_actors' ) ) {
					$sections[] = bmd_avfp_home_section_actors();
				}
				break;
			case 'gallery_tags':
				if ( bmd_avfp_home_section_visible( 'home_show_tags' ) ) {
					$sections[] = bmd_avfp_home_section_gallery_tags();
				}
				break;
			case 'seo':
				if ( bmd_avfp_home_section_visible( 'home_show_seo' ) ) {
					$sections[] = bmd_avfp_home_section_seo();
				}
				break;
		}
	}

	echo implode( "\n", array_filter( $sections ) );
}

/**
 * Render the featured section.
 */
function bmd_avfp_home_section_featured() {
	$limit = max( 4, min( 48, absint( bmd_get_avfp_professional_setting( 'home_featured_count', 20 ) ) ) );
	$query = bmd_query_homepage_posts( $limit, array( 'ignore_sticky_posts' => true ) );
	if ( ! $query->have_posts() ) {
		return '';
	}
	$archive_url = get_post_type_archive_link( 'post' ) ?: home_url( '/?post_type=post' );
	ob_start();
	?>
<section class="bmd-section bmd-featured-section" data-bmd-home-block="featured">
	<div class="bmd-container">
		<div class="bmd-featured-heading">
			<div class="bmd-section-heading">
				<div><p class="bmd-eyebrow"><?php echo esc_html( sprintf( __( 'FEATURED · %d STORIES', 'bossmaster-display' ), $query->post_count ) ); ?></p><h2><?php esc_html_e( 'เรื่องเด่นแนะนำ', 'bossmaster-display' ); ?></h2><p class="bmd-section-subtitle"><?php esc_html_e( 'เลื่อนอัตโนมัติ หรือกดลูกศรเพื่อเลื่อนเอง', 'bossmaster-display' ); ?></p></div>
			</div>
			<div class="bmd-slider-actions">
				<a href="<?php echo esc_url( $archive_url ); ?>">⭐ <?php esc_html_e( 'ดูทั้งหมด', 'bossmaster-display' ); ?> →</a>
				<button type="button" data-bmd-slider-prev aria-label="<?php esc_attr_e( 'เรื่องก่อนหน้า', 'bossmaster-display' ); ?>">←</button>
				<button type="button" data-bmd-slider-pause aria-label="<?php esc_attr_e( 'หยุดสไลด์', 'bossmaster-display' ); ?>">Ⅱ</button>
				<button type="button" data-bmd-slider-next aria-label="<?php esc_attr_e( 'เรื่องถัดไป', 'bossmaster-display' ); ?>">→</button>
			</div>
		</div>
		<div class="bmd-featured-carousel" data-bmd-slider>
			<div class="bmd-featured-track" data-bmd-slider-track>
				<?php while ( $query->have_posts() ) : $query->the_post(); bmd_render_video_card( get_the_ID(), array( 'class' => 'is-featured-card' ) ); endwhile; wp_reset_postdata(); ?>
			</div>
			<div class="bmd-slider-progress" aria-hidden="true"><span data-bmd-slider-progress></span></div>
		</div>
	</div>
</section>
	<?php
	return ob_get_clean();
}

/**
 * Render the latest posts section.
 */
function bmd_avfp_home_section_latest() {
	$default_latest = absint( get_theme_mod( 'bmd_latest_count', 8 ) );
	$limit = max( 4, min( 24, absint( bmd_get_avfp_professional_setting( 'home_latest_count', $default_latest ) ) ) );
	$latest = bmd_query_homepage_posts( $limit, array( 'ignore_sticky_posts' => true ) );
	if ( ! $latest->have_posts() ) {
		return '';
	}
	$archive_url = get_post_type_archive_link( 'post' ) ?: home_url( '/?post_type=post' );
	ob_start();
	?>
<section class="bmd-section bmd-latest-section" id="bmd-latest" data-bmd-home-block="latest">
	<div class="bmd-container">
		<div class="bmd-section-heading">
			<div><p class="bmd-eyebrow"><?php esc_html_e( 'LATEST UPDATE', 'bossmaster-display' ); ?></p><h2><?php esc_html_e( 'วิดีโอล่าสุด', 'bossmaster-display' ); ?></h2></div>
			<a href="<?php echo esc_url( $archive_url ); ?>">⭐ <?php esc_html_e( 'ดูทั้งหมด', 'bossmaster-display' ); ?> →</a>
		</div>
		<div class="bmd-video-grid bmd-latest-grid">
			<?php if ( $latest->have_posts() ) : while ( $latest->have_posts() ) : $latest->the_post(); bmd_render_video_card(); endwhile; wp_reset_postdata(); else : ?>
				<div class="bmd-empty-state"><h3><?php esc_html_e( 'ยังไม่มีวิดีโอ', 'bossmaster-display' ); ?></h3><p><?php esc_html_e( 'เมื่อมีการเผยแพร่โพสต์ รายการจะปรากฏที่นี่โดยอัตโนมัติ', 'bossmaster-display' ); ?></p></div>
			<?php endif; ?>
		</div>
	</div>
</section>
	<?php
	return ob_get_clean();
}

/**
 * Render the studio section.
 */
function bmd_avfp_home_section_studios() {
	$count = max( 4, min( 48, absint( bmd_get_avfp_professional_setting( 'home_studio_count', 20 ) ) ) );
	$terms = get_terms(
		array(
			'taxonomy'   => 'studio',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => $count,
		)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}
	$all_url = bmd_page_url_by_template( 'template-studio.php', '/studio/' );
	ob_start();
	?>
<section class="bmd-section bmd-directory-section bmd-studio-section" data-bmd-home-block="studio">
	<div class="bmd-container">
		<div class="bmd-section-heading">
			<div><p class="bmd-eyebrow"><?php esc_html_e( 'STUDIOS', 'bossmaster-display' ); ?></p><h2><?php esc_html_e( 'ค่ายแนะนำ', 'bossmaster-display' ); ?></h2></div>
			<a href="<?php echo esc_url( $all_url ); ?>">⭐ <?php esc_html_e( 'ดูทั้งหมด', 'bossmaster-display' ); ?> →</a>
		</div>
		<div class="bmd-term-grid is-studio">
			<?php foreach ( $terms as $term ) : bmd_render_term_card( $term ); endforeach; ?>
		</div>
	</div>
</section>
	<?php
	return ob_get_clean();
}

/**
 * Render the actors section.
 */
function bmd_avfp_home_section_actors() {
	$count = max( 4, min( 48, absint( bmd_get_avfp_professional_setting( 'home_actor_count', 20 ) ) ) );
	$terms = get_terms(
		array(
			'taxonomy'   => 'actors',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => $count,
		)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}
	$all_url = bmd_page_url_by_template( 'template-actors.php', '/actors/' );
	ob_start();
	?>
<section class="bmd-section bmd-directory-section bmd-actors-section" data-bmd-home-block="actors">
	<div class="bmd-container">
		<div class="bmd-section-heading">
			<div><p class="bmd-eyebrow"><?php esc_html_e( 'ACTORS', 'bossmaster-display' ); ?></p><h2><?php esc_html_e( 'นักแสดงยอดนิยม', 'bossmaster-display' ); ?></h2></div>
			<a href="<?php echo esc_url( $all_url ); ?>">⭐ <?php esc_html_e( 'ดูทั้งหมด', 'bossmaster-display' ); ?> →</a>
		</div>
		<div class="bmd-term-grid">
			<?php foreach ( $terms as $term ) : bmd_render_term_card( $term ); endforeach; ?>
		</div>
	</div>
</section>
	<?php
	return ob_get_clean();
}

/**
 * Render the gallery tags section.
 */
function bmd_avfp_home_section_gallery_tags() {
	$count = max( 4, min( 48, absint( bmd_get_avfp_professional_setting( 'home_tag_count', 20 ) ) ) );
	$terms = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => $count,
		)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}
	$all_url = bmd_page_url_by_template( 'template-tags.php', '/tags/' );
	ob_start();
	?>
<section class="bmd-section bmd-gallery-tags-section" data-bmd-home-block="gallery_tags">
	<div class="bmd-container">
		<div class="bmd-section-heading">
			<div><p class="bmd-eyebrow"><?php esc_html_e( 'POPULAR TAGS', 'bossmaster-display' ); ?></p><h2><?php esc_html_e( 'ค้นหาด้วยแท็ก', 'bossmaster-display' ); ?></h2></div>
			<a href="<?php echo esc_url( $all_url ); ?>">⭐ <?php esc_html_e( 'ดูทั้งหมด', 'bossmaster-display' ); ?> →</a>
		</div>
		<div class="bmd-tag-cloud">
			<?php foreach ( $terms as $term ) : $url = get_term_link( $term ); if ( is_wp_error( $url ) ) { continue; } ?>
				<a href="<?php echo esc_url( $url ); ?>" class="bmd-tag-chip"><strong><?php echo esc_html( $term->name ); ?></strong><small><?php echo esc_html( number_format_i18n( $term->count ) ); ?> <?php esc_html_e( 'รายการ', 'bossmaster-display' ); ?></small></a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
	<?php
	return ob_get_clean();
}

/**
 * Render a lightweight SEO section.
 */
function bmd_avfp_home_section_seo() {
	$title = get_bloginfo( 'name' );
	$description = get_bloginfo( 'description' );
	if ( '' === trim( $title ) && '' === trim( $description ) ) {
		return '';
	}
	ob_start();
	?>
<section class="bmd-section bmd-seo-section" data-bmd-home-block="seo">
	<div class="bmd-container">
		<div class="bmd-section-heading">
			<div><p class="bmd-eyebrow"><?php esc_html_e( 'SEO', 'bossmaster-display' ); ?></p><h2><?php echo esc_html( $title ); ?></h2></div>
		</div>
		<div class="bmd-seo-copy">
			<?php if ( $description ) : ?><p><?php echo esc_html( $description ); ?></p><?php endif; ?>
			<a class="bmd-button is-ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'ดูเว็บไซต์ทั้งหมด', 'bossmaster-display' ); ?></a>
		</div>
	</div>
</section>
	<?php
	return ob_get_clean();
}

/**
 * Get the first non-empty post meta value.
 */
function bmd_first_meta( $post_id, array $keys, $default = '' ) {
	foreach ( $keys as $key ) {
		$value = get_post_meta( $post_id, $key, true );
		if ( is_array( $value ) ) {
			if ( ! empty( $value ) ) {
				return $value;
			}
			continue;
		}
		if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
			return $value;
		}
	}
	return $default;
}

/**
 * Normalize a movie/content code.
 */
function bmd_get_code( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$code    = bmd_first_meta( $post_id, array( '_acs_code', 'acs_code', '_wpmb_code', 'code', 'product_code' ) );
	$code    = strtoupper( trim( (string) $code ) );
	if ( '' !== $code ) {
		return $code;
	}

	$post = get_post( $post_id );
	$haystack = $post ? $post->post_name . ' ' . $post->post_title : '';
	if ( preg_match( '/\b([A-Z]{2,12})[-_ ]?(\d{2,6})\b/i', $haystack, $match ) ) {
		return strtoupper( $match[1] . '-' . $match[2] );
	}
	return 'POST-' . $post_id;
}

/**
 * Normalize a duration value.
 */
function bmd_get_duration( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$value   = bmd_first_meta( $post_id, array( '_acs_time', 'time', 'duration_hms', 'duration', 'video_duration' ) );
	$value   = trim( (string) $value );
	if ( '' !== $value ) {
		if ( ctype_digit( $value ) ) {
			$value = absint( $value );
			$hours = floor( $value / 3600 );
			$mins  = floor( ( $value % 3600 ) / 60 );
			$secs  = $value % 60;
			return $hours > 0 ? sprintf( '%d:%02d:%02d', $hours, $mins, $secs ) : sprintf( '%02d:%02d', $mins, $secs );
		}
		return $value;
	}

	$seconds = absint( bmd_first_meta( $post_id, array( '_acs_duration_seconds', 'duration_seconds' ), 0 ) );
	if ( $seconds > 0 ) {
		$hours = floor( $seconds / 3600 );
		$mins  = floor( ( $seconds % 3600 ) / 60 );
		$secs  = $seconds % 60;
		return $hours > 0 ? sprintf( '%d:%02d:%02d', $hours, $mins, $secs ) : sprintf( '%02d:%02d', $mins, $secs );
	}
	return '';
}

/**
 * Get a reliable poster/cover URL.
 */
function bmd_get_poster( $post_id = 0, $size = 'bmd-card' ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	if ( function_exists( 'avfp_get_post_best_image_url' ) ) {
		$url = avfp_get_post_best_image_url( $post_id, $size );
		if ( $url ) {
			return $url;
		}
	}
	$url = get_the_post_thumbnail_url( $post_id, $size );
	if ( $url ) {
		return $url;
	}
	foreach ( array( '_avfp_poster_id', 'poster_id', 'poster', 'poster_url', 'cover', 'cover_url', 'thumb', 'video_thumb_url' ) as $key ) {
		$value = get_post_meta( $post_id, $key, true );
		if ( is_numeric( $value ) ) {
			$attachment_url = wp_get_attachment_image_url( absint( $value ), $size );
			if ( $attachment_url ) {
				return $attachment_url;
			}
		}
		if ( is_string( $value ) && preg_match( '~^https?://~i', $value ) ) {
			return esc_url_raw( $value );
		}
	}
	return '';
}

/**
 * Views displayed on cards.
 */
function bmd_get_views( $post_id = 0 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$views   = absint( bmd_first_meta( $post_id, array( 'post_views_count', 'views', '_post_views', 'video_views', '_avfp_views' ), 0 ) );
	if ( $views < 1000 ) {
		return number_format_i18n( $views );
	}
	return number_format_i18n( $views / 1000, 1 ) . 'K';
}

/**
 * First term from a taxonomy.
 */
function bmd_first_term( $post_id, $taxonomy ) {
	$terms = wp_get_post_terms( $post_id, $taxonomy );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return null;
	}
	return reset( $terms );
}

/**
 * Return a directory page URL by its template filename.
 */
function bmd_page_url_by_template( $template, $fallback = '/' ) {
	$pages = get_pages(
		array(
			'meta_key'   => '_wp_page_template',
			'meta_value' => $template,
			'number'     => 1,
		)
	);
	if ( ! empty( $pages ) ) {
		return get_permalink( $pages[0] );
	}
	return home_url( $fallback );
}


/**
 * Safe taxonomy count.
 */
function bmd_count_terms( $taxonomy ) {
	$count = wp_count_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => true ) );
	return is_wp_error( $count ) ? 0 : absint( $count );
}

/**
 * Safe term URL.
 */
function bmd_term_url( $term, $fallback = '' ) {
	if ( ! $term instanceof WP_Term ) {
		return $fallback;
	}
	$url = get_term_link( $term );
	return is_wp_error( $url ) ? $fallback : $url;
}

/**
 * Return the first registered navigation location intended for the header.
 */
function bmd_get_header_menu_location() {
	$preferred_locations = array(
		'wpst-primary-menu',
		'primary',
		'main-menu',
		'menu-1',
		'header-menu',
		'primary-menu',
		'main',
	);

	foreach ( $preferred_locations as $location ) {
		if ( has_nav_menu( $location ) ) {
			return $location;
		}
	}

	foreach ( get_registered_nav_menus() as $location => $description ) {
		if ( has_nav_menu( $location ) ) {
			return $location;
		}
	}

	return '';
}

/**
 * Render the current WordPress menu assigned to the header.
 */
function bmd_render_header_menu( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'menu_class' => 'bmd-nav-list',
			'depth'      => 0,
		)
	);

	$location = bmd_get_header_menu_location();

	if ( $location ) {
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'container'      => false,
				'menu_class'     => $args['menu_class'],
				'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
				'fallback_cb'    => 'bmd_menu_fallback',
				'depth'          => $args['depth'],
			)
		);
		return;
	}

	bmd_menu_fallback( $args );
}

/**
 * Menu fallback when no assigned menu location exists.
 */
function bmd_menu_fallback( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'menu_class' => 'bmd-nav-list',
		)
	);

	if ( current_user_can( 'edit_theme_options' ) ) {
		echo '<div class="bmd-menu-fallback-message">';
		echo esc_html__( 'ยังไม่ได้กำหนดเมนูหลัก กรุณาเลือกเมนูใน รูปลักษณ์ > เมนู', 'bossmaster-display' );
		echo '</div>';
	}

	wp_page_menu(
		array(
			'menu_class' => $args['menu_class'],
			'show_home'  => false,
			'echo'       => true,
		)
	);
}

/**
 * Return the default homepage section definitions.
 *
 * @return array<string,array{enabled:bool,order:int,eyebrow:string,heading:string}>
 */
function bmd_home_section_defaults() {
	return array(
		'hero'       => array( 'enabled' => true,  'order' => 10, 'eyebrow' => '',      'heading' => '' ),
		'featured'   => array( 'enabled' => true,  'order' => 20, 'eyebrow' => 'FEATURED', 'heading' => 'เรื่องเด่นแนะนำ' ),
		'actors'     => array( 'enabled' => true,  'order' => 30, 'eyebrow' => 'ACTORS', 'heading' => 'นักแสดงยอดนิยม' ),
		'latest'     => array( 'enabled' => true,  'order' => 40, 'eyebrow' => 'LATEST', 'heading' => 'วิดีโอล่าสุด' ),
		'categories' => array( 'enabled' => true,  'order' => 50, 'eyebrow' => '',      'heading' => 'หมวดหมู่' ),
		'tags'       => array( 'enabled' => true,  'order' => 60, 'eyebrow' => 'POPULAR TAGS', 'heading' => 'ค้นหาด้วยแท็ก' ),
		'explore'    => array( 'enabled' => true,  'order' => 70, 'eyebrow' => 'EXPLORE', 'heading' => 'เลือกดูตามความสนใจ' ),
		'random_latest' => array( 'enabled' => true, 'order' => 45, 'eyebrow' => 'RANDOM PICK', 'heading' => 'สุ่มจากรายการล่าสุด' ),
	);
}

/**
 * Read homepage section settings from the customizer.
 *
 * @return array<string,array{enabled:bool,order:int,eyebrow:string,heading:string}>
 */
function bmd_get_home_section_settings() {
	$defaults = bmd_home_section_defaults();
	$settings = array();
	foreach ( $defaults as $key => $default ) {
		$settings[ $key ] = array(
			'enabled' => (bool) ( $default['enabled'] ?? true ),
			'order'   => absint( $default['order'] ?? 0 ),
			'eyebrow' => (string) ( $default['eyebrow'] ?? '' ),
			'heading' => (string) ( $default['heading'] ?? '' ),
		);
	}

	$stored = get_theme_mod( 'bmd_home_sections', '' );
	if ( is_string( $stored ) && '' !== trim( $stored ) ) {
		$decoded = json_decode( $stored, true );
		if ( is_array( $decoded ) ) {
			foreach ( $settings as $key => $config ) {
				if ( isset( $decoded[ $key ] ) && is_array( $decoded[ $key ] ) ) {
					$entry = $decoded[ $key ];
					$settings[ $key ]['enabled'] = isset( $entry['enabled'] ) ? (bool) $entry['enabled'] : $config['enabled'];
					$settings[ $key ]['order']   = isset( $entry['order'] ) ? absint( $entry['order'] ) : $config['order'];
					$settings[ $key ]['eyebrow'] = isset( $entry['eyebrow'] ) ? sanitize_text_field( (string) $entry['eyebrow'] ) : $config['eyebrow'];
					$settings[ $key ]['heading'] = isset( $entry['heading'] ) ? sanitize_text_field( (string) $entry['heading'] ) : $config['heading'];
				}
			}
		}
	}

	$legacy = array(
		'hero'         => (bool) get_theme_mod( 'bmd_show_hero', true ),
		'featured'     => (bool) get_theme_mod( 'bmd_show_featured', true ),
		'latest'       => (bool) get_theme_mod( 'bmd_show_latest', true ),
		'random_latest' => (bool) get_theme_mod( 'bmd_show_random_latest', true ),
		'categories'   => (bool) get_theme_mod( 'bmd_show_directories', true ),
		'explore'      => (bool) get_theme_mod( 'bmd_show_directories', true ),
	);
	foreach ( $settings as $key => $config ) {
		if ( ! in_array( $key, array_keys( $legacy ), true ) ) {
			continue;
		}
		if ( ! isset( $stored ) || '' === trim( (string) $stored ) ) {
			$settings[ $key ]['enabled'] = $legacy[ $key ];
		}
	}

	return $settings;
}

/**
 * Return the active homepage sections ordered by the configured values.
 *
 * @param array<string,array{enabled:bool,order:int,eyebrow:string,heading:string}> $settings Optional section settings.
 * @return array<string,array{enabled:bool,order:int,eyebrow:string,heading:string}>
 */
function bmd_get_active_home_sections( $settings = array() ) {
	$settings = empty( $settings ) ? bmd_get_home_section_settings() : $settings;
	$active   = array_filter(
		$settings,
		static function ( $section ) {
			return ! empty( $section['enabled'] );
		}
	);
	uasort(
		$active,
		static function ( $left, $right ) {
			return ( absint( $left['order'] ) <=> absint( $right['order'] ) );
		}
	);
	return $active;
}

/**
 * Normalize the homepage section order from theme settings.
 *
 * @return string[]
 */
function bmd_get_home_section_order() {
	$settings = bmd_get_active_home_sections();
	$ordered = array();
	$custom_order = get_theme_mod( 'bmd_home_section_order', '' );
	if ( is_string( $custom_order ) && '' !== trim( $custom_order ) ) {
		foreach ( array_map( 'trim', explode( ',', $custom_order ) ) as $section ) {
			$section = bmd_normalize_home_section_key( $section );
			if ( ! isset( $settings[ $section ] ) ) {
				continue;
			}
			if ( ! empty( $settings[ $section ]['enabled'] ) && ! in_array( $section, $ordered, true ) ) {
				$ordered[] = $section;
			}
		}
	}
	foreach ( array_keys( $settings ) as $section ) {
		if ( ! empty( $settings[ $section ]['enabled'] ) && ! in_array( $section, $ordered, true ) ) {
			$ordered[] = $section;
		}
	}
	if ( empty( $ordered ) ) {
		$ordered = array( 'hero', 'featured', 'latest', 'random_latest', 'categories', 'actors', 'tags', 'explore' );
	}
	return $ordered;
}

/**
 * Render a standardized section heading for homepage blocks.
 *
 * @param array<string,mixed> $section The section config.
 * @param string              $default_eyebrow Fallback eyebrow.
 * @param string              $default_title Fallback title.
 * @param string              $link_url Optional link URL.
 * @param string              $link_text Optional link text.
 * @return string
 */
function bmd_render_home_section_heading( array $section, $default_eyebrow = '', $default_title = '', $link_url = '', $link_text = '' ) {
	$eyebrow = trim( (string) ( $section['eyebrow'] ?? '' ) );
	if ( '' === $eyebrow ) {
		$eyebrow = $default_eyebrow;
	}
	$title = trim( (string) ( $section['heading'] ?? '' ) );
	if ( '' === $title ) {
		$title = $default_title;
	}
	$link_text = $link_text ?: __( 'ดูทั้งหมด', 'bossmaster-display' );
	ob_start();
	?>
	<div class="bmd-section-heading">
		<div class="bmd-section-heading__content">
			<?php if ( $eyebrow ) : ?>
				<p class="bmd-section-heading__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
			<?php endif; ?>
			<h2 class="bmd-section-heading__title"><?php echo esc_html( $title ); ?></h2>
		</div>
		<?php if ( $link_url ) : ?>
			<a class="bmd-section-heading__link" href="<?php echo esc_url( $link_url ); ?>"><?php echo esc_html( $link_text ); ?> →</a>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Render the homepage hero block.
 *
 * @param array<string,mixed> $section Section configuration.
 * @param array<int,WP_Post>  $hero_posts Hero posts.
 * @param string              $archive_url Archive URL.
 * @param string              $hero_title Hero title.
 * @param string              $hero_highlight Hero highlight.
 * @param int                 $section_gap Section gap.
 * @return string
 */
function bmd_render_home_hero( array $section, array $hero_posts, $archive_url, $hero_title, $hero_highlight, $section_gap ) {
	ob_start();
	?>
	<section class="bmd-hero bmd-home-section" data-bmd-home-block="hero" style="--bmd-home-section-gap:<?php echo esc_attr( (string) $section_gap ); ?>px;">
		<div class="bmd-container bmd-hero-grid">
			<div class="bmd-hero-copy">
				<p class="bmd-eyebrow"><?php esc_html_e( 'BOSSMASTER COLLECTION', 'bossmaster-display' ); ?></p>
				<h1><span><?php echo esc_html( $hero_title ); ?></span><?php if ( $hero_highlight ) : ?><em><?php echo esc_html( $hero_highlight ); ?></em><?php endif; ?></h1>
				<p class="bmd-hero-description"><?php echo esc_html( get_theme_mod( 'bmd_hero_description', 'ผสานข้อมูลวิดีโอ นักแสดง ค่าย หมวดหมู่ และแท็กไว้ในหน้าเดียว เพื่อให้การเรียกดูรู้สึกเรียบง่ายและสนุกขึ้น' ) ); ?></p>
				<div class="bmd-hero-actions">
					<a class="bmd-button is-primary" href="#bmd-latest"><?php esc_html_e( 'ดูรายการล่าสุด', 'bossmaster-display' ); ?> →</a>
					<a class="bmd-button is-ghost" href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'ดูหน้ารายละเอียดตัวอย่าง', 'bossmaster-display' ); ?></a>
				</div>
			</div>
			<div class="bmd-hero-stack" aria-hidden="true">
				<?php foreach ( $hero_posts as $index => $hero_post ) : $poster = bmd_get_poster( $hero_post->ID, 'bmd-card' ); ?>
					<?php if ( $poster ) : ?>
						<div class="bmd-hero-card bmd-hero-card-<?php echo esc_attr( (string) ( $index + 1 ) ); ?>">
							<img src="<?php echo esc_url( $poster ); ?>" alt="" loading="eager" decoding="async">
							<span class="bmd-hero-card-number"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
						</div>
					<?php else : ?>
						<div class="bmd-hero-card bmd-hero-card--fallback bmd-hero-card-<?php echo esc_attr( (string) ( $index + 1 ) ); ?>">
							<span class="bmd-hero-card--fallback-icon">♥</span>
							<span class="bmd-hero-card--fallback-number"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="bmd-container">
			<div class="bmd-quick-features" aria-label="<?php esc_attr_e( 'ทางลัดหน้าแรก', 'bossmaster-display' ); ?>">
				<a class="bmd-quick-feature is-update" href="<?php echo esc_url( $archive_url ); ?>"><strong><?php esc_html_e( 'อัปเดตทุกวัน', 'bossmaster-display' ); ?></strong><span><?php esc_html_e( 'เปิดดูรายการใหม่ทั้งหมด', 'bossmaster-display' ); ?> →</span></a>
				<a class="bmd-quick-feature is-explore" href="<?php echo esc_url( bmd_page_url_by_template( 'template-categories.php', '/categories/' ) ); ?>"><strong><?php esc_html_e( 'เลือกได้หลายแบบ', 'bossmaster-display' ); ?></strong><span><?php esc_html_e( 'ค้นตามหมวดหมู่และแท็ก', 'bossmaster-display' ); ?> →</span></a>
				<a class="bmd-quick-feature is-secure" href="<?php echo esc_url( bmd_page_url_by_template( 'template-actors.php', '/actors/' ) ); ?>"><strong><?php esc_html_e( 'ข้อมูลเชื่อมถึงกัน', 'bossmaster-display' ); ?></strong><span><?php esc_html_e( 'จากนักแสดงไปยังผลงาน', 'bossmaster-display' ); ?> →</span></a>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Render the featured homepage block.
 *
 * @param array<string,mixed> $section Section configuration.
 * @param WP_Query            $featured Featured posts query.
 * @param string              $featured_style Featured layout mode.
 * @param string              $archive_url Archive URL.
 * @param int                 $section_gap Section gap.
 * @return string
 */
function bmd_render_home_featured( array $section, WP_Query $featured, $featured_style, $archive_url, $section_gap ) {
	ob_start();
	?>
	<section class="bmd-section bmd-featured-section bmd-home-section" data-bmd-home-block="featured" style="--bmd-home-section-gap:<?php echo esc_attr( (string) $section_gap ); ?>px;">
		<div class="bmd-container">
			<?php echo bmd_render_home_section_heading( $section, 'FEATURED', __( 'เรื่องเด่นแนะนำ', 'bossmaster-display' ), $archive_url, __( 'ดูทั้งหมด', 'bossmaster-display' ) ); ?>
			<?php if ( 'grid' === $featured_style ) : ?>
			<div class="bmd-video-grid bmd-featured-grid">
				<?php while ( $featured->have_posts() ) : $featured->the_post(); bmd_render_video_card( get_the_ID(), array( 'class' => 'is-featured-card' ) ); endwhile; wp_reset_postdata(); ?>
			</div>
			<?php else : ?>
			<div class="bmd-featured-carousel" data-bmd-slider>
				<div class="bmd-featured-track" data-bmd-slider-track>
					<?php while ( $featured->have_posts() ) : $featured->the_post(); bmd_render_video_card( get_the_ID(), array( 'class' => 'is-featured-card' ) ); endwhile; wp_reset_postdata(); ?>
				</div>
				<div class="bmd-slider-progress" aria-hidden="true"><span data-bmd-slider-progress></span></div>
			</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Render the latest homepage block.
 *
 * @param array<string,mixed> $section Section configuration.
 * @param WP_Query            $latest Latest posts query.
 * @param string              $archive_url Archive URL.
 * @param int                 $section_gap Section gap.
 * @return string
 */
function bmd_render_home_latest( array $section, WP_Query $latest, $archive_url, $section_gap ) {
	ob_start();
	?>
	<section class="bmd-section bmd-latest-section bmd-home-section" id="bmd-latest" data-bmd-home-block="latest" style="--bmd-home-section-gap:<?php echo esc_attr( (string) $section_gap ); ?>px;">
		<div class="bmd-container">
			<?php echo bmd_render_home_section_heading( $section, 'LATEST UPDATE', __( 'วิดีโอล่าสุด', 'bossmaster-display' ), $archive_url, __( 'ดูทั้งหมด', 'bossmaster-display' ) ); ?>
			<div class="bmd-video-grid bmd-latest-grid">
				<?php if ( $latest->have_posts() ) : while ( $latest->have_posts() ) : $latest->the_post(); bmd_render_video_card(); endwhile; wp_reset_postdata(); else : ?>
					<div class="bmd-empty-state"><h3><?php esc_html_e( 'ยังไม่มีวิดีโอ', 'bossmaster-display' ); ?></h3><p><?php esc_html_e( 'เมื่อมีการเผยแพร่โพสต์ รายการจะปรากฏที่นี่โดยอัตโนมัติ', 'bossmaster-display' ); ?></p></div>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Render the random latest homepage block.
 *
 * @param array<string,mixed> $section Section configuration.
 * @param WP_Query            $random_latest Random latest posts query.
 * @param int                 $count Number of cards.
 * @param string              $title Section title.
 * @param string              $view_all_text Link label.
 * @param int                 $columns_desktop Desktop columns.
 * @param int                 $columns_tablet Tablet columns.
 * @param int                 $columns_mobile Mobile columns.
 * @param int                 $gap Gap between cards.
 * @param int                 $rows_desktop Desktop rows.
 * @param int                 $rows_tablet Tablet rows.
 * @param int                 $rows_mobile Mobile rows.
 * @param bool                $natural Use natural orientation.
 * @param string              $archive_url Archive URL.
 * @param int                 $section_gap Section gap.
 * @return string
 */
function bmd_render_home_random_latest( array $section, WP_Query $random_latest, $count, $title, $view_all_text, $columns_desktop, $columns_tablet, $columns_mobile, $gap, $rows_desktop, $rows_tablet, $rows_mobile, $natural, $archive_url, $section_gap ) {
	if ( ! $random_latest->have_posts() ) {
		return '';
	}
	ob_start();
	?>
	<section class="bmd-section bmd-random-latest-section bmd-home-section" data-bmd-home-block="random_latest" style="--bmd-home-section-gap:<?php echo esc_attr( (string) $section_gap ); ?>px;">
		<div class="bmd-container">
			<?php echo bmd_render_home_section_heading( $section, 'RANDOM PICK', $title, $archive_url, $view_all_text ); ?>
			<div class="bmd-video-grid bmd-random-latest-grid" style="--bmd-random-latest-cols-desktop:<?php echo esc_attr( (string) $columns_desktop ); ?>;--bmd-random-latest-cols-tablet:<?php echo esc_attr( (string) $columns_tablet ); ?>;--bmd-random-latest-cols-mobile:<?php echo esc_attr( (string) $columns_mobile ); ?>;--bmd-random-latest-gap:<?php echo esc_attr( (string) $gap ); ?>px;--bmd-random-latest-rows-desktop:<?php echo esc_attr( (string) $rows_desktop ); ?>;--bmd-random-latest-rows-tablet:<?php echo esc_attr( (string) $rows_tablet ); ?>;--bmd-random-latest-rows-mobile:<?php echo esc_attr( (string) $rows_mobile ); ?>;">
				<?php $rendered = 0; while ( $random_latest->have_posts() ) : $random_latest->the_post(); if ( $rendered >= $count ) { break; } $rendered++; bmd_render_video_card( get_the_ID(), array( 'orientation' => $natural ? 'natural' : '' ) ); endwhile; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Render the categories homepage block.
 *
 * @param array<string,mixed> $section Section configuration.
 * @param string              $archive_url Archive URL.
 * @param int                 $section_gap Section gap.
 * @return string
 */
function bmd_render_home_categories( array $section, $archive_url, $section_gap ) {
	$categories = get_categories(
		array(
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => true,
			'number'     => 8,
		)
	);
	if ( empty( $categories ) ) {
		return '';
	}
	ob_start();
	?>
	<section class="bmd-section bmd-explore-section bmd-home-section" data-bmd-home-block="categories" style="--bmd-home-section-gap:<?php echo esc_attr( (string) $section_gap ); ?>px;">
		<div class="bmd-container">
			<?php echo bmd_render_home_section_heading( $section, 'CATEGORIES', __( 'หมวดหมู่', 'bossmaster-display' ), $archive_url, __( 'ดูทั้งหมด', 'bossmaster-display' ) ); ?>
			<div class="bmd-category-list">
				<?php foreach ( $categories as $category ) : $cat_url = get_category_link( $category ); if ( ! $cat_url ) { continue; } ?>
					<a href="<?php echo esc_url( $cat_url ); ?>">
						<span class="bmd-category-icon">#</span>
						<span class="bmd-category-copy"><strong><?php echo esc_html( $category->name ); ?></strong><small><?php echo esc_html( wp_strip_all_tags( $category->description ) ); ?></small></span>
						<span class="bmd-category-count"><strong><?php echo esc_html( number_format_i18n( $category->count ) ); ?></strong><small><?php esc_html_e( 'ผลงาน', 'bossmaster-display' ); ?></small></span>
						<span class="bmd-category-arrow">→</span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Render the actors homepage block.
 *
 * @param array<string,mixed> $section Section configuration.
 * @param string              $archive_url Archive URL.
 * @param int                 $section_gap Section gap.
 * @return string
 */
function bmd_render_home_actors( array $section, $archive_url, $section_gap ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'actors',
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => 8,
		)
	);
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return '';
	}
	ob_start();
	?>
	<section class="bmd-section bmd-directory-section bmd-home-section" data-bmd-home-block="actors" style="--bmd-home-section-gap:<?php echo esc_attr( (string) $section_gap ); ?>px;">
		<div class="bmd-container">
			<?php echo bmd_render_home_section_heading( $section, 'ACTORS', __( 'นักแสดงยอดนิยม', 'bossmaster-display' ), $archive_url, __( 'ดูทั้งหมด', 'bossmaster-display' ) ); ?>
			<div class="bmd-term-grid">
				<?php foreach ( $terms as $term ) : bmd_render_term_card( $term ); endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Render the popular tags homepage block.
 *
 * @param array<string,mixed> $section Section configuration.
 * @param string              $archive_url Archive URL.
 * @param int                 $section_gap Section gap.
 * @return string
 */
function bmd_render_home_tags( array $section, $archive_url, $section_gap ) {
	$tags = get_tags(
		array(
			'orderby'    => 'count',
			'order'      => 'DESC',
			'hide_empty' => true,
			'number'     => 24,
		)
	);
	if ( empty( $tags ) ) {
		return '';
	}
	ob_start();
	?>
	<section class="bmd-section bmd-tags-section bmd-home-section" data-bmd-home-block="tags" style="--bmd-home-section-gap:<?php echo esc_attr( (string) $section_gap ); ?>px;">
		<div class="bmd-container">
			<?php echo bmd_render_home_section_heading( $section, 'POPULAR TAGS', __( 'ค้นหาด้วยแท็ก', 'bossmaster-display' ), $archive_url, __( 'ดูทั้งหมด', 'bossmaster-display' ) ); ?>
			<div class="bmd-tags-grid">
				<?php foreach ( $tags as $tag ) : $tag_url = get_tag_link( $tag->term_id ); if ( ! $tag_url ) { continue; } ?>
					<a class="bmd-tag-pill" href="<?php echo esc_url( $tag_url ); ?>">#<?php echo esc_html( $tag->name ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Render the explore homepage block.
 *
 * @param array<string,mixed> $section Section configuration.
 * @param string              $archive_url Archive URL.
 * @param int                 $section_gap Section gap.
 * @param int                 $count Number of buttons.
 * @param int                 $columns_desktop Desktop columns.
 * @param int                 $columns_tablet Tablet columns.
 * @param int                 $columns_mobile Mobile columns.
 * @param int                 $gap Gap.
 * @return string
 */
function bmd_render_home_explore( array $section, $archive_url, $section_gap, $count, $columns_desktop, $columns_tablet, $columns_mobile, $gap ) {
	ob_start();
	?>
	<section class="bmd-section bmd-explore-section bmd-home-section" data-bmd-home-block="explore" style="--bmd-home-section-gap:<?php echo esc_attr( (string) $section_gap ); ?>px;">
		<div class="bmd-container">
			<?php echo bmd_render_home_section_heading( $section, 'EXPLORE', __( 'เลือกดูตามความสนใจ', 'bossmaster-display' ), bmd_page_url_by_template( 'template-categories.php', '/categories/' ), __( 'ดูทั้งหมด', 'bossmaster-display' ) ); ?>
			<div class="bmd-explore-grid" style="--bmd-explore-cols-desktop:<?php echo esc_attr( (string) $columns_desktop ); ?>;--bmd-explore-cols-tablet:<?php echo esc_attr( (string) $columns_tablet ); ?>;--bmd-explore-cols-mobile:<?php echo esc_attr( (string) $columns_mobile ); ?>;--bmd-explore-gap:<?php echo esc_attr( (string) $gap ); ?>px;">
				<?php $explore_items = array(
					array( 'url' => bmd_page_url_by_template( 'template-actors.php', '/actors/' ), 'title' => __( 'นักแสดงยอดนิยม', 'bossmaster-display' ), 'copy' => __( 'เลือกดูผลงานตามนักแสดง', 'bossmaster-display' ) ),
					array( 'url' => bmd_page_url_by_template( 'template-studio.php', '/studio/' ), 'title' => __( 'ค่ายแนะนำ', 'bossmaster-display' ), 'copy' => __( 'ค้นหาผลงานจากค่ายที่ชอบ', 'bossmaster-display' ) ),
					array( 'url' => $archive_url, 'title' => __( 'รวมวิดีโอใหม่', 'bossmaster-display' ), 'copy' => __( 'อัปเดตรายการล่าสุดทุกวัน', 'bossmaster-display' ) ),
					array( 'url' => bmd_page_url_by_template( 'template-tags.php', '/tags/' ), 'title' => __( 'เลือกตามแท็ก', 'bossmaster-display' ), 'copy' => __( 'ค้นหาหัวข้อที่สนใจได้ทันที', 'bossmaster-display' ) ),
					array( 'url' => bmd_page_url_by_template( 'template-categories.php', '/categories/' ), 'title' => __( 'คอลเลกชันพิเศษ', 'bossmaster-display' ), 'copy' => __( 'รวมหมวดหมู่และรายการคัดสรร', 'bossmaster-display' ) ),
				); foreach ( array_slice( $explore_items, 0, $count ) as $index => $item ) : ?>
					<a href="<?php echo esc_url( $item['url'] ); ?>"><span><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><h3><?php echo esc_html( $item['title'] ); ?></h3><p><?php echo esc_html( $item['copy'] ); ?></p><strong><?php esc_html_e( 'เปิดดู', 'bossmaster-display' ); ?> →</strong></a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Filter homepage posts to only show real published content.
 *
 * @param array<int|WP_Post> $posts Posts to inspect.
 * @return array<int,WP_Post>
 */
function bmd_filter_homepage_posts( $posts ) {
	if ( ! is_array( $posts ) ) {
		return array();
	}

	$filtered = array();
	foreach ( $posts as $post ) {
		$post_object = $post instanceof WP_Post ? $post : get_post( $post );
		if ( ! $post_object instanceof WP_Post ) {
			continue;
		}
		if ( 'post' !== $post_object->post_type || 'publish' !== $post_object->post_status ) {
			continue;
		}

		$title = trim( wp_strip_all_tags( $post_object->post_title ) );
		if ( '' === $title ) {
			continue;
		}

		$normalized_title = strtolower( preg_replace( '/\s+/', ' ', $title ) );
		$bad_markers = array( 'hello world', 'สวัสดีชาวโลก', 'sample post', 'test post' );
		$has_bad_marker = false;
		foreach ( $bad_markers as $marker ) {
			if ( false !== strpos( $normalized_title, $marker ) ) {
				$has_bad_marker = true;
				break;
			}
		}
		if ( $has_bad_marker ) {
			continue;
		}

		$content = trim( wp_strip_all_tags( strip_shortcodes( $post_object->post_content ) ) );
		if ( '' !== $content ) {
			$normalized_content = strtolower( preg_replace( '/\s+/', ' ', $content ) );
			foreach ( array( 'hello world', 'สวัสดีชาวโลก', 'welcome to wordpress', 'welcome' ) as $marker ) {
				if ( false !== strpos( $normalized_content, $marker ) ) {
					$has_bad_marker = true;
					break;
				}
			}
			if ( $has_bad_marker ) {
				continue;
			}
		}

		$filtered[] = $post_object;
	}

	return $filtered;
}

/**
 * Run a homepage query with real published posts only.
 *
 * @param int   $count Number of posts to fetch.
 * @param array $args Additional query args.
 * @return WP_Query
 */
function bmd_query_homepage_posts( $count = 8, array $args = array() ) {
	$count = max( 1, absint( $count ) );
	$query = new WP_Query(
		wp_parse_args(
			$args,
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'posts_per_page'      => $count,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'orderby'             => 'date',
				'order'               => 'DESC',
			)
		)
	);

	if ( $query->have_posts() ) {
		$posts = bmd_filter_homepage_posts( $query->posts );
		$query->posts = $posts;
		$query->post_count = count( $posts );
		$query->found_posts = count( $posts );
		$query->rewind_posts();
	}

	return $query;
}

/**
 * Return a small random selection of recent posts for the lower homepage section.
 */
function bmd_get_random_latest_posts( $count = 15 ) {
	$count = max( 4, min( 24, absint( $count ) ) );
	$query = bmd_query_homepage_posts(
		$count,
		array(
			'date_query' => array(
				'after' => gmdate( 'Y-m-d', strtotime( '-180 days' ) ),
			),
		)
	);
	if ( $query->have_posts() ) {
		$posts = $query->posts;
		shuffle( $posts );
		$query->posts = $posts;
		$query->post_count = count( $posts );
		$query->rewind_posts();
	}
	return $query;
}

/**
 * Resolve a card orientation from the global setting and component context.
 */
function bmd_get_card_orientation( $context = '' ) {
	$context = sanitize_key( (string) $context );
	if ( 'single-poster' === $context || 'related' === $context || 'gallery' === $context ) {
		return 'natural';
	}
	if ( 'single-gallery' === $context ) {
		$ratio = bmd_sanitize_choice( get_theme_mod( 'bmd_single_gallery_ratio', 'landscape' ), array( 'landscape', 'portrait' ), 'landscape' );
		return 'portrait' === $ratio ? 'vertical' : 'horizontal';
	}
	if ( 'natural' === $context ) {
		return 'natural';
	}
	$ratio = bmd_sanitize_choice( get_theme_mod( 'bmd_card_ratio', 'landscape' ), array( 'portrait', 'landscape' ), 'landscape' );
	return 'portrait' === $ratio ? 'vertical' : 'horizontal';
}

/**
 * Render a reusable video card.
 */
function bmd_render_video_card( $post_id = 0, array $args = array() ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$post    = get_post( $post_id );
	if ( ! $post ) {
		return;
	}
	$args = wp_parse_args(
		$args,
		array(
			'class'      => '',
			'show_meta'  => true,
			'wide'       => false,
			'lazy'       => true,
			'orientation' => '',
			'fallback'   => 'latest',
		)
	);
	$poster   = bmd_get_poster( $post_id, $args['wide'] ? 'bmd-wide' : 'bmd-card' );
	$code     = bmd_get_code( $post_id );
	$duration = bmd_get_duration( $post_id );
	$actor    = bmd_first_term( $post_id, 'actors' );
	$category = bmd_first_term( $post_id, 'category' );
	$badge    = $category ? $category->name : get_theme_mod( 'bmd_default_badge', 'ใหม่' );
	$orientation = bmd_get_card_orientation( $args['orientation'] );
	$classes  = 'bmd-video-card ' . ( $args['wide'] ? 'is-wide ' : '' ) . sanitize_html_class( $args['class'] );
	?>
	<article class="<?php echo esc_attr( trim( $classes ) ); ?>" data-bmd-card-orientation="<?php echo esc_attr( $orientation ); ?>">
		<a class="bmd-card-link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" aria-label="<?php echo esc_attr( get_the_title( $post_id ) ); ?>">
			<div class="bmd-card-poster<?php echo $poster ? '' : ' is-placeholder'; ?>" data-bmd-card-orientation="<?php echo esc_attr( $orientation ); ?>">
				<?php if ( $poster ) : ?>
					<img src="<?php echo esc_url( $poster ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" <?php echo $args['lazy'] ? 'loading="lazy"' : 'loading="eager"'; ?> decoding="async" data-bmd-fallback-image>
				<?php else : ?>
					<?php echo bmd_get_fallback_art( 'video' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
				<span class="bmd-fallback-art-slot" aria-hidden="true"><?php echo bmd_get_fallback_art( 'video' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="bmd-card-badge"><?php echo esc_html( $badge ); ?></span>
				<?php if ( $duration ) : ?><span class="bmd-card-duration"><?php echo esc_html( $duration ); ?></span><?php endif; ?>
				<span class="bmd-card-code"><?php echo esc_html( $code ); ?></span>
			</div>
			<div class="bmd-card-body">
				<h3><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
				<?php if ( $args['show_meta'] ) : ?>
					<p><?php echo esc_html( $actor ? $actor->name : get_bloginfo( 'name' ) ); ?> · <?php echo esc_html( bmd_get_views( $post_id ) ); ?> <?php esc_html_e( 'ครั้ง', 'bossmaster-display' ); ?></p>
				<?php endif; ?>
			</div>
		</a>
	</article>
	<?php
}

/**
 * Decorative, self-contained fallback artwork for missing media. No external icon font is used.
 */
function bmd_get_fallback_art( $type = 'video' ) {
	$type = sanitize_key( $type );
	$paths = array(
		'actor'      => '<path d="M42 91c3-21 15-34 31-38 17 4 29 17 32 38"/><path d="M51 32c0-14 9-23 21-23 13 0 22 10 22 24 0 13-9 25-22 25-12 0-21-12-21-26Z"/><path d="M42 91c10-7 18-10 30-10s21 3 33 10"/>',
		'studio'     => '<path d="M29 72h86v24H29z"/><path d="m35 61 15-24 22 17 22-17 15 24-37 15z"/><path d="M50 37 43 25l18 8 11-18 11 18 18-8-7 12"/>',
		'tag'        => '<path d="M25 49 58 16h41l20 20v41L86 110 25 49Z"/><circle cx="84" cy="44" r="6"/>',
		'collection' => '<path d="m72 12 40 30-40 70-40-70 40-30Z"/><path d="m32 42 40 70 40-70M32 42h80M72 12v100"/>',
		'video'      => '<rect x="20" y="28" width="104" height="70" rx="12"/><path d="m65 48 31 15-31 16V48Z"/><path d="M31 18v10m20-10v10m20-10v10m20-10v10m20-10v10M31 98v10m20-10v10m20-10v10m20-10v10m20-10v10"/>',
	);
	$path = $paths[ isset( $paths[ $type ] ) ? $type : 'video' ];
	return '<svg class="bmd-fallback-art bmd-fallback-art--' . esc_attr( $type ) . '" viewBox="0 0 144 124" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
}

/**
 * Term image with theme helper fallback.
 */
function bmd_get_term_image( $term, $size = 'bmd-card' ) {
	if ( ! $term instanceof WP_Term ) {
		return '';
	}
	if ( function_exists( 'avfp_get_term_original_meta_image_url' ) ) {
		$url = avfp_get_term_original_meta_image_url( $term, $term->taxonomy, $size );
		if ( $url ) {
			return $url;
		}
	}
	foreach ( array( 'thumbnail_id', 'image_id', 'term_image_id', 'actors-image-id', 'studio-image-id', 'category-image-id' ) as $key ) {
		$value = get_term_meta( $term->term_id, $key, true );
		if ( is_numeric( $value ) ) {
			$url = wp_get_attachment_image_url( absint( $value ), $size );
			if ( $url ) {
				return $url;
			}
		}
	}
	return '';
}

/**
 * Render an actor/studio directory card.
 */
function bmd_render_term_card( WP_Term $term ) {
	$url   = get_term_link( $term );
	$image = bmd_get_term_image( $term );
	if ( is_wp_error( $url ) ) {
		return;
	}
	$fallback_type = 'studio' === $term->taxonomy ? 'studio' : 'actor';
	?>
	<article class="bmd-term-card">
		<a href="<?php echo esc_url( $url ); ?>">
			<div class="bmd-term-cover<?php echo $image ? '' : ' is-placeholder'; ?>">
				<?php if ( $image ) : ?>
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $term->name ); ?>" loading="lazy" decoding="async" data-bmd-fallback-image>
				<?php else : ?>
					<?php echo bmd_get_fallback_art( $fallback_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
				<span class="bmd-fallback-art-slot" aria-hidden="true"><?php echo bmd_get_fallback_art( $fallback_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			</div>
			<div class="bmd-term-body">
				<h3><?php echo esc_html( $term->name ); ?></h3>
				<?php if ( $term->description ) : ?><p><?php echo esc_html( wp_html_excerpt( wp_strip_all_tags( $term->description ), 68, '…' ) ); ?></p><?php endif; ?>
				<small><?php echo esc_html( number_format_i18n( $term->count ) ); ?> <?php esc_html_e( 'ผลงาน', 'bossmaster-display' ); ?> →</small>
			</div>
		</a>
	</article>
	<?php
}

/**
 * Collect gallery images without changing stored data.
 */
function bmd_get_gallery_images( $post_id = 0, $limit = 12 ) {
	$post_id = $post_id ? absint( $post_id ) : get_the_ID();
	$items   = array();
	$add     = static function ( $value ) use ( &$items ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $part ) {
				if ( is_array( $part ) || is_object( $part ) ) {
					$part = (array) $part;
					foreach ( $part as $nested ) {
						if ( is_scalar( $nested ) ) {
							$items[] = (string) $nested;
						}
					}
				} elseif ( is_scalar( $part ) ) {
					$items[] = (string) $part;
				}
			}
			return;
		}
		if ( is_scalar( $value ) ) {
			$value = trim( (string) $value );
			if ( '' === $value ) {
				return;
			}
			$maybe = maybe_unserialize( $value );
			if ( $maybe !== $value ) {
				if ( is_array( $maybe ) ) {
					foreach ( $maybe as $part ) {
						if ( is_scalar( $part ) ) {
							$items[] = (string) $part;
						}
					}
					return;
				}
			}
			foreach ( preg_split( '/[\s,|;]+/', $value, -1, PREG_SPLIT_NO_EMPTY ) as $part ) {
				$items[] = $part;
			}
		}
	};

	foreach ( array( '_avfp_42_gallery_ids', 'avfp_gallery_ids', '_gallery_image_ids', 'gallery_image_ids', 'gallery', '_gallery', 'images', '_images' ) as $key ) {
		$add( get_post_meta( $post_id, $key, true ) );
	}
	$content = (string) get_post_field( 'post_content', $post_id );
	if ( preg_match_all( '/\[gallery[^\]]*ids=["\']([^"\']+)["\']/', $content, $matches ) ) {
		foreach ( $matches[1] as $ids ) {
			$add( $ids );
		}
	}
	foreach ( get_attached_media( 'image', $post_id ) as $attachment ) {
		$items[] = (string) $attachment->ID;
	}

	$featured = get_post_thumbnail_id( $post_id );
	$resolved = array();
	$seen     = array( 'ids' => array(), 'urls' => array() );
	foreach ( array_values( array_unique( array_filter( $items ) ) ) as $item ) {
		$id  = ctype_digit( (string) $item ) ? absint( $item ) : 0;
		$url = '';
		if ( $id ) {
			if ( $id === $featured || isset( $seen['ids'][ $id ] ) ) {
				continue;
			}
			$url = wp_get_attachment_image_url( $id, 'large' );
		} elseif ( preg_match( '~^https?://~i', (string) $item ) ) {
			$url = esc_url_raw( $item );
		}
		if ( ! $url || isset( $seen['urls'][ $url ] ) ) {
			continue;
		}
		$seen['urls'][ $url ] = true;
		if ( $id ) {
			$seen['ids'][ $id ] = true;
		}
		$resolved[] = array( 'id' => $id, 'url' => $url );
		if ( count( $resolved ) >= $limit ) {
			break;
		}
	}
	return $resolved;
}

/**
 * Related posts.
 */
function bmd_related_query( $post_id, $count = 4 ) {
	$tax_query = array( 'relation' => 'OR' );
	foreach ( array( 'category', 'actors', 'studio' ) as $taxonomy ) {
		$ids = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
		if ( ! is_wp_error( $ids ) && ! empty( $ids ) ) {
			$tax_query[] = array( 'taxonomy' => $taxonomy, 'field' => 'term_id', 'terms' => $ids );
		}
	}
	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => absint( $count ),
		'post__not_in'        => array( absint( $post_id ) ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( count( $tax_query ) > 1 ) {
		$args['tax_query'] = $tax_query;
	}
	return new WP_Query( $args );
}

/**
 * Archive sorting.
 */
function bmd_sort_archives( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! ( $query->is_home() || $query->is_archive() || $query->is_search() ) ) {
		return;
	}
	$sort = isset( $_GET['bmd_sort'] ) ? sanitize_key( wp_unslash( $_GET['bmd_sort'] ) ) : '';
	if ( 'oldest' === $sort ) {
		$query->set( 'order', 'ASC' );
		$query->set( 'orderby', 'date' );
	} elseif ( 'title' === $sort ) {
		$query->set( 'order', 'ASC' );
		$query->set( 'orderby', 'title' );
	} elseif ( 'popular' === $sort ) {
		$query->set( 'meta_key', 'post_views_count' );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'order', 'DESC' );
	}
}
add_action( 'pre_get_posts', 'bmd_sort_archives' );

/**
 * Remove taxonomy boxes injected by the parent theme from filtered post content.
 * The BOSSMASTER single template renders one category box and one tag row itself,
 * so leaving the legacy block creates the duplicate shown in the Customizer.
 *
 * @param string $html Filtered post HTML.
 * @return string
 */
function bmd_strip_legacy_gallery_blocks( $html ) {
	$html = (string) $html;
	if ( '' === trim( $html ) ) {
		return $html;
	}

	$patterns = array(
		'#<([a-z0-9]+)\b[^>]*class=("|\\\')[^"\\\']*\\bwp-caption\\b[^"\\\']*\\2[^>]*>.*?</\\1>#is',
		'#<([a-z0-9]+)\b[^>]*class=("|\\\')[^"\\\']*\\bgallery\\b[^"\\\']*\\2[^>]*>.*?</\\1>#is',
		'#<([a-z0-9]+)\b[^>]*class=("|\\\')[^"\\\']*\\bgallery-item\\b[^"\\\']*\\2[^>]*>.*?</\\1>#is',
		'#<([a-z0-9]+)\b[^>]*id=("|\\\')gallery-[^"\\\']*\\2[^>]*>.*?</\\1>#is',
	);
	foreach ( $patterns as $pattern ) {
		$html = preg_replace( $pattern, '', $html );
	}
	$html = preg_replace( '#<script[^>]*id=["\']wpmb-native-gallery-display-js["\'][^>]*>.*?</script>#is', '', $html );
	$html = preg_replace( '#<link[^>]*id=["\']wpmb-native-gallery-display-css["\'][^>]*>#is', '', $html );
	$html = preg_replace( '#<style[^>]*id=["\']wp-block-gallery-inline-css["\'][^>]*>.*?</style>#is', '', $html );
	return $html;
}

/**
 * Remove taxonomy boxes injected by the parent theme from filtered post content.
 * The BOSSMASTER single template renders one category box and one tag row itself,
 * so leaving the legacy block creates the duplicate shown in the Customizer.
 *
 * @param string $html Filtered post HTML.
 * @return string
 */
function bmd_strip_legacy_taxonomy_blocks( $html ) {
	$html = (string) $html;
	if ( '' === trim( $html ) ) {
		return $html;
	}

	$target_classes = array( 'avfp-custom-tags', 'category-list', 'tags-list', 'avfp-post-tags' );

	if ( class_exists( 'DOMDocument' ) && class_exists( 'DOMXPath' ) ) {
		$previous = libxml_use_internal_errors( true );
		$document = new DOMDocument( '1.0', 'UTF-8' );
		$wrapped  = '<!doctype html><html><head><meta charset="utf-8"></head><body><div id="bmd-content-root">' . $html . '</div></body></html>';
		$loaded   = $document->loadHTML( '<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );

		if ( $loaded ) {
			$xpath = new DOMXPath( $document );
			foreach ( $target_classes as $class_name ) {
				$query = '//*[contains(concat(" ", normalize-space(@class), " "), " ' . $class_name . ' ")]';
				$nodes = $xpath->query( $query );
				if ( $nodes ) {
					foreach ( iterator_to_array( $nodes ) as $node ) {
						if ( $node->parentNode ) {
							$node->parentNode->removeChild( $node );
						}
					}
				}
			}

			$root_nodes = $xpath->query( '//*[@id="bmd-content-root"]' );
			if ( $root_nodes && $root_nodes->length ) {
				$root   = $root_nodes->item( 0 );
				$output = '';
				foreach ( $root->childNodes as $child ) {
					$output .= $document->saveHTML( $child );
				}
				libxml_clear_errors();
				libxml_use_internal_errors( $previous );
				return $output;
			}
		}

		libxml_clear_errors();
		libxml_use_internal_errors( $previous );
	}

	// Fallback for servers without the DOM extension.
	foreach ( $target_classes as $class_name ) {
		$html = preg_replace( '#<([a-z0-9]+)\\b[^>]*class=("|\\\')[^"\\\']*\\b' . preg_quote( $class_name, '#' ) . '\\b[^"\\\']*\\2[^>]*>.*?</\\1>#is', '', $html );
	}
	return $html;
}

/**
 * Customizer settings for the child theme.
 */
function bmd_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'bmd_display_panel',
		array(
			'title'       => __( 'BOSSMASTER Display', 'bossmaster-display' ),
			'description' => __( 'ตั้งค่าหน้าตาธีมใหม่โดยไม่แก้ข้อมูลโพสต์เดิม', 'bossmaster-display' ),
			'priority'    => 2,
		)
	);
	$wp_customize->add_section( 'bmd_general', array( 'title' => __( 'หน้าตาและส่วนหัว', 'bossmaster-display' ), 'panel' => 'bmd_display_panel', 'priority' => 10 ) );
	$wp_customize->add_section( 'bmd_home', array( 'title' => __( 'หน้าแรก', 'bossmaster-display' ), 'panel' => 'bmd_display_panel', 'priority' => 20 ) );
	$wp_customize->add_section( 'bmd_cards', array( 'title' => __( 'การ์ดและคอลัมน์', 'bossmaster-display' ), 'panel' => 'bmd_display_panel', 'priority' => 30 ) );
	$wp_customize->add_section( 'bmd_single', array( 'title' => __( 'หน้ารายละเอียดและหมวดหมู่', 'bossmaster-display' ), 'panel' => 'bmd_display_panel', 'priority' => 40 ) );

	$settings = array(
		'bmd_default_theme' => array( 'default' => 'dark', 'sanitize' => static function ( $value ) { return in_array( $value, array( 'dark', 'light' ), true ) ? $value : 'dark'; }, 'section' => 'bmd_general', 'label' => __( 'โหมดเริ่มต้น', 'bossmaster-display' ), 'type' => 'select', 'choices' => array( 'dark' => __( 'มืด', 'bossmaster-display' ), 'light' => __( 'สว่าง', 'bossmaster-display' ) ) ),
		'bmd_top_notice' => array( 'default' => 'BOSSMASTER INTERACTIVE DISPLAY', 'sanitize' => 'sanitize_text_field', 'section' => 'bmd_general', 'label' => __( 'ข้อความแถบบน', 'bossmaster-display' ), 'type' => 'text' ),
		'bmd_show_top_notice' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_general', 'label' => __( 'แสดงแถบบน', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_show_admin_floating' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_general', 'label' => __( 'แสดงปุ่มตั้งค่าลอยสำหรับผู้ดูแล', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_hero_title' => array( 'default' => 'คลังวิดีโอที่ออกแบบให้ค้นหาได้ง่ายกว่าเดิม', 'sanitize' => 'sanitize_text_field', 'section' => 'bmd_home', 'label' => __( 'หัวข้อ Hero', 'bossmaster-display' ), 'type' => 'text' ),
		'bmd_hero_description' => array( 'default' => 'รวมผลงานล่าสุด นักแสดง ค่าย หมวดหมู่ และแท็กไว้ในหน้าตาที่อ่านง่ายทั้งคอมพิวเตอร์และมือถือ', 'sanitize' => 'sanitize_textarea_field', 'section' => 'bmd_home', 'label' => __( 'คำอธิบาย Hero', 'bossmaster-display' ), 'type' => 'textarea' ),
		'bmd_featured_count' => array( 'default' => 5, 'sanitize' => 'absint', 'section' => 'bmd_home', 'label' => __( 'จำนวนเรื่องเด่น', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 3, 'max' => 12 ) ),
		'bmd_latest_count' => array( 'default' => 8, 'sanitize' => 'absint', 'section' => 'bmd_home', 'label' => __( 'จำนวนวิดีโอล่าสุด', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 4, 'max' => 24 ) ),
		'bmd_featured_style' => array( 'default' => 'carousel', 'sanitize' => static function ( $value ) { return in_array( $value, array( 'carousel', 'grid' ), true ) ? $value : 'carousel'; }, 'section' => 'bmd_home', 'label' => __( 'โครงสร้างเรื่องเด่น', 'bossmaster-display' ), 'type' => 'select', 'choices' => array( 'carousel' => __( 'สไลด์', 'bossmaster-display' ), 'grid' => __( 'กริด', 'bossmaster-display' ) ) ),
		'bmd_show_featured' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_home', 'label' => __( 'แสดงเรื่องเด่นแนะนำ', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_show_latest' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_home', 'label' => __( 'แสดงวิดีโอล่าสุด', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_show_random_latest' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_home', 'label' => __( 'แสดงสุ่มจากรายการล่าสุด', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_show_directories' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_home', 'label' => __( 'แสดงทางลัดนักแสดง ค่าย หมวดหมู่ และแท็ก', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_random_latest_title' => array( 'default' => __( 'สุ่มจากรายการล่าสุด', 'bossmaster-display' ), 'sanitize' => 'sanitize_text_field', 'section' => 'bmd_home', 'label' => __( 'หัวข้อสุ่มจากรายการล่าสุด', 'bossmaster-display' ), 'type' => 'text' ),
		'bmd_random_latest_count' => array( 'default' => 15, 'sanitize' => static function ( $value ) { return max( 6, min( 24, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'จำนวนการ์ดสุ่มจากรายการล่าสุด', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 6, 'max' => 24 ) ),
		'bmd_random_latest_columns_desktop' => array( 'default' => 5, 'sanitize' => static function ( $value ) { return max( 3, min( 6, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'คอลัมน์สุ่มจากรายการล่าสุด (เดสก์ทอป)', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 3, 'max' => 6 ) ),
		'bmd_random_latest_columns_tablet' => array( 'default' => 4, 'sanitize' => static function ( $value ) { return max( 2, min( 5, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'คอลัมน์สุ่มจากรายการล่าสุด (แท็บเล็ต)', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 2, 'max' => 5 ) ),
		'bmd_random_latest_columns_mobile' => array( 'default' => 3, 'sanitize' => static function ( $value ) { return max( 1, min( 4, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'คอลัมน์สุ่มจากรายการล่าสุด (มือถือ)', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 1, 'max' => 4 ) ),
		'bmd_random_latest_gap' => array( 'default' => 16, 'sanitize' => static function ( $value ) { return max( 8, min( 40, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'ระยะห่างสุ่มจากรายการล่าสุด', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 8, 'max' => 40, 'step' => 1 ) ),
		'bmd_random_latest_natural' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_home', 'label' => __( 'ใช้รูปแบบภาพตามธรรมชาติในสุ่มจากรายการล่าสุด', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_random_latest_view_all' => array( 'default' => __( 'ดูทั้งหมด', 'bossmaster-display' ), 'sanitize' => 'sanitize_text_field', 'section' => 'bmd_home', 'label' => __( 'ข้อความปุ่มดูทั้งหมดของสุ่มจากรายการล่าสุด', 'bossmaster-display' ), 'type' => 'text' ),
		'bmd_random_latest_rows_desktop' => array( 'default' => 2, 'sanitize' => static function ( $value ) { return max( 1, min( 4, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'แถวสุ่มจากรายการล่าสุด (เดสก์ทอป)', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 1, 'max' => 4 ) ),
		'bmd_random_latest_rows_tablet' => array( 'default' => 2, 'sanitize' => static function ( $value ) { return max( 1, min( 4, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'แถวสุ่มจากรายการล่าสุด (แท็บเล็ต)', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 1, 'max' => 4 ) ),
		'bmd_random_latest_rows_mobile' => array( 'default' => 2, 'sanitize' => static function ( $value ) { return max( 1, min( 4, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'แถวสุ่มจากรายการล่าสุด (มือถือ)', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 1, 'max' => 4 ) ),
		'bmd_explore_count' => array( 'default' => 5, 'sanitize' => static function ( $value ) { return max( 3, min( 6, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'จำนวนปุ่มเลือกดูตามความสนใจ', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 3, 'max' => 6 ) ),
		'bmd_explore_columns_desktop' => array( 'default' => 3, 'sanitize' => static function ( $value ) { return max( 2, min( 4, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'คอลัมน์เลือกดูตามความสนใจ (เดสก์ทอป)', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 2, 'max' => 4 ) ),
		'bmd_explore_columns_tablet' => array( 'default' => 2, 'sanitize' => static function ( $value ) { return max( 1, min( 3, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'คอลัมน์เลือกดูตามความสนใจ (แท็บเล็ต)', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 1, 'max' => 3 ) ),
		'bmd_explore_columns_mobile' => array( 'default' => 1, 'sanitize' => static function ( $value ) { return max( 1, min( 2, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'คอลัมน์เลือกดูตามความสนใจ (มือถือ)', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 1, 'max' => 2 ) ),
		'bmd_explore_gap' => array( 'default' => 12, 'sanitize' => static function ( $value ) { return max( 8, min( 32, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'ระยะห่างเลือกดูตามความสนใจ', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 8, 'max' => 32, 'step' => 1 ) ),
		'bmd_home_section_order' => array( 'default' => 'hero,featured,latest,random_latest,directories', 'sanitize' => static function ( $value ) { $allowed = array( 'hero', 'featured', 'latest', 'random_latest', 'directories', 'studio', 'actors', 'tags', 'seo' ); $parts = array(); foreach ( array_map( 'trim', explode( ',', (string) $value ) ) as $part ) { $part = sanitize_key( $part ); if ( in_array( $part, $allowed, true ) && ! in_array( $part, $parts, true ) ) { $parts[] = $part; } } return implode( ',', $parts ); }, 'section' => 'bmd_home', 'label' => __( 'ลำดับบล็อกหน้าแรก', 'bossmaster-display' ), 'type' => 'text' ),
		'bmd_home_sections' => array( 'default' => '', 'sanitize' => 'sanitize_text_field', 'section' => 'bmd_home', 'label' => __( 'การตั้งค่าโครงสร้างหน้าแรก', 'bossmaster-display' ), 'type' => 'text' ),
		'bmd_home_section_gap' => array( 'default' => 24, 'sanitize' => static function ( $value ) { return max( 8, min( 72, absint( $value ) ) ); }, 'section' => 'bmd_home', 'label' => __( 'ระยะห่างบล็อกหน้าแรก', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 8, 'max' => 72, 'step' => 2 ) ),
		'bmd_nav_mode' => array( 'default' => 'auto', 'sanitize' => static function ( $value ) { return in_array( $value, array( 'auto', 'compact', 'stacked' ), true ) ? $value : 'auto'; }, 'section' => 'bmd_general', 'label' => __( 'รูปแบบเมนูบนหน้าจอ', 'bossmaster-display' ), 'type' => 'select', 'choices' => array( 'auto' => __( 'อัตโนมัติ', 'bossmaster-display' ), 'compact' => __( 'ซ่อนเมนูหลัก', 'bossmaster-display' ), 'stacked' => __( 'ใช้เมนูมือถือ', 'bossmaster-display' ) ) ),
		'bmd_header_breakpoint' => array( 'default' => 900, 'sanitize' => static function ( $value ) { return max( 640, min( 1400, absint( $value ) ) ); }, 'section' => 'bmd_general', 'label' => __( 'จุดพักเมนูแบบย่อ', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 640, 'max' => 1400, 'step' => 50 ) ),
		'bmd_header_show_search' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_general', 'label' => __( 'แสดงปุ่มค้นหา', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_header_show_language' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_general', 'label' => __( 'แสดงปุ่มภาษา', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_header_show_theme_toggle' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_general', 'label' => __( 'แสดงปุ่มสลับโหมด', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_header_sticky' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_general', 'label' => __( 'ติดหัวข้อเมื่อเลื่อนหน้า', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_header_height' => array( 'default' => 76, 'sanitize' => static function ( $value ) { return max( 56, min( 112, absint( $value ) ) ); }, 'section' => 'bmd_general', 'label' => __( 'ความสูงแถบหัวข้อ', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 56, 'max' => 112, 'step' => 2 ) ),
		'bmd_columns_desktop' => array( 'default' => 4, 'sanitize' => 'absint', 'section' => 'bmd_cards', 'label' => __( 'คอลัมน์คอมพิวเตอร์', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 2, 'max' => 6 ) ),
		'bmd_columns_tablet' => array( 'default' => 3, 'sanitize' => 'absint', 'section' => 'bmd_cards', 'label' => __( 'คอลัมน์แท็บเล็ต', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 2, 'max' => 4 ) ),
		'bmd_columns_mobile' => array( 'default' => 2, 'sanitize' => 'absint', 'section' => 'bmd_cards', 'label' => __( 'คอลัมน์มือถือ', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 1, 'max' => 2 ) ),
		'bmd_default_badge' => array( 'default' => 'ใหม่', 'sanitize' => 'sanitize_text_field', 'section' => 'bmd_cards', 'label' => __( 'ป้ายเริ่มต้นบนการ์ด', 'bossmaster-display' ), 'type' => 'text' ),
		'bmd_single_show_category' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_single', 'label' => __( 'แสดงกล่องหมวดหมู่ 1 จุด', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_single_category_label' => array( 'default' => 'หมวดหมู่', 'sanitize' => 'sanitize_text_field', 'section' => 'bmd_single', 'label' => __( 'ข้อความหน้าชื่อหมวดหมู่', 'bossmaster-display' ), 'type' => 'text' ),
		'bmd_single_show_other_terms' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_single', 'label' => __( 'แสดงแท็กใต้เนื้อหา', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_single_show_actions' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_single', 'label' => __( 'แสดงปุ่มถูกใจ บันทึก และคัดลอกลิงก์', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_single_status_label' => array( 'default' => 'อัปเดตล่าสุด', 'sanitize' => 'sanitize_text_field', 'section' => 'bmd_single', 'label' => __( 'ข้อความสถานะข้างข้อมูลวิดีโอ', 'bossmaster-display' ), 'type' => 'text' ),
		'bmd_single_show_gallery' => array( 'default' => true, 'sanitize' => static function ( $value ) { return (bool) $value; }, 'section' => 'bmd_single', 'label' => __( 'แสดง Gallery ภาพตัวอย่าง', 'bossmaster-display' ), 'type' => 'checkbox' ),
		'bmd_single_gallery_columns' => array( 'default' => 4, 'sanitize' => static function ( $value ) { return max( 2, min( 6, absint( $value ) ) ); }, 'section' => 'bmd_single', 'label' => __( 'คอลัมน์ Gallery (เดสก์ทอป)', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 2, 'max' => 6 ) ),
		'bmd_single_gallery_columns_tablet' => array( 'default' => 3, 'sanitize' => static function ( $value ) { return max( 2, min( 4, absint( $value ) ) ); }, 'section' => 'bmd_single', 'label' => __( 'คอลัมน์ Gallery (แท็บเล็ต)', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 2, 'max' => 4 ) ),
		'bmd_single_gallery_columns_mobile' => array( 'default' => 2, 'sanitize' => static function ( $value ) { return max( 1, min( 3, absint( $value ) ) ); }, 'section' => 'bmd_single', 'label' => __( 'คอลัมน์ Gallery (มือถือ)', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 1, 'max' => 3 ) ),
		'bmd_single_gallery_rows' => array( 'default' => 2, 'sanitize' => static function ( $value ) { return max( 1, min( 6, absint( $value ) ) ); }, 'section' => 'bmd_single', 'label' => __( 'แถว Gallery สูงสุด', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 1, 'max' => 6 ) ),
		'bmd_single_gallery_max_items' => array( 'default' => 8, 'sanitize' => static function ( $value ) { return max( 1, min( 24, absint( $value ) ) ); }, 'section' => 'bmd_single', 'label' => __( 'จำนวนภาพ Gallery สูงสุด', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 1, 'max' => 24 ) ),
		'bmd_single_gallery_gap' => array( 'default' => 10, 'sanitize' => static function ( $value ) { return max( 6, min( 32, absint( $value ) ) ); }, 'section' => 'bmd_single', 'label' => __( 'ระยะห่าง Gallery', 'bossmaster-display' ), 'type' => 'number', 'input_attrs' => array( 'min' => 6, 'max' => 32, 'step' => 1 ) ),
		'bmd_single_gallery_ratio' => array( 'default' => 'landscape', 'sanitize' => static function ( $value ) { return in_array( $value, array( 'landscape', 'portrait' ), true ) ? $value : 'landscape'; }, 'section' => 'bmd_single', 'label' => __( 'อัตราส่วนภาพ Gallery', 'bossmaster-display' ), 'type' => 'select', 'choices' => array( 'landscape' => __( 'แนวกว้าง 16:9', 'bossmaster-display' ), 'portrait' => __( 'แนวตั้ง 2:3', 'bossmaster-display' ) ) ),
		'bmd_single_meta_layout' => array( 'default' => 'stacked', 'sanitize' => static function ( $value ) { return in_array( $value, array( 'stacked', 'inline' ), true ) ? $value : 'stacked'; }, 'section' => 'bmd_single', 'label' => __( 'รูปแบบพื้นที่ข้อมูลวิดีโอ', 'bossmaster-display' ), 'type' => 'select', 'choices' => array( 'stacked' => __( 'เรียงซ้อน', 'bossmaster-display' ), 'inline' => __( 'ชิดแถว', 'bossmaster-display' ) ) ),
	);
	foreach ( $settings as $id => $args ) {
		$wp_customize->add_setting( $id, array( 'default' => $args['default'], 'transport' => 'refresh', 'sanitize_callback' => $args['sanitize'] ) );
		$control = array( 'label' => $args['label'], 'section' => $args['section'], 'settings' => $id, 'type' => $args['type'] );
		if ( isset( $args['choices'] ) ) {
			$control['choices'] = $args['choices'];
		}
		if ( isset( $args['input_attrs'] ) ) {
			$control['input_attrs'] = $args['input_attrs'];
		}
		$wp_customize->add_control( $id, $control );
	}

	$colors = array(
		'bmd_accent' => array( '#ff2f8c', __( 'สีหลัก', 'bossmaster-display' ) ),
		'bmd_accent_2' => array( '#8c3cff', __( 'สีไล่ระดับ', 'bossmaster-display' ) ),
		'bmd_dark_background' => array( '#090311', __( 'พื้นหลังโหมดมืด', 'bossmaster-display' ) ),
		'bmd_dark_surface' => array( '#160b22', __( 'พื้นผิวโหมดมืด', 'bossmaster-display' ) ),
		'bmd_light_background' => array( '#f7f4fb', __( 'พื้นหลังโหมดสว่าง', 'bossmaster-display' ) ),
		'bmd_light_surface' => array( '#ffffff', __( 'พื้นผิวโหมดสว่าง', 'bossmaster-display' ) ),
		'bmd_single_category_bg' => array( '#f3edf8', __( 'พื้นหลังกล่องหมวดหมู่', 'bossmaster-display' ) ),
		'bmd_single_category_border' => array( '#ddd2e8', __( 'ขอบกล่องหมวดหมู่', 'bossmaster-display' ) ),
		'bmd_single_category_text' => array( '#4b3b55', __( 'ตัวอักษรกล่องหมวดหมู่', 'bossmaster-display' ) ),
	);
	foreach ( $colors as $id => $data ) {
		$wp_customize->add_setting( $id, array( 'default' => $data[0], 'transport' => 'refresh', 'sanitize_callback' => 'sanitize_hex_color' ) );
		$color_section = 0 === strpos( $id, 'bmd_single_category_' ) ? 'bmd_single' : 'bmd_general';
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array( 'label' => $data[1], 'section' => $color_section, 'settings' => $id ) ) );
	}
}
add_action( 'customize_register', 'bmd_customize_register', 25 );

/**
 * CSS variables generated from Customizer values.
 */
function bmd_custom_css() {
	$accent = sanitize_hex_color( get_theme_mod( 'bmd_accent', '#ff2f8c' ) ) ?: '#ff2f8c';
	$accent_2 = sanitize_hex_color( get_theme_mod( 'bmd_accent_2', '#8c3cff' ) ) ?: '#8c3cff';
	$dark_bg = sanitize_hex_color( get_theme_mod( 'bmd_dark_background', '#090311' ) ) ?: '#090311';
	$dark_surface = sanitize_hex_color( get_theme_mod( 'bmd_dark_surface', '#160b22' ) ) ?: '#160b22';
	$light_bg = sanitize_hex_color( get_theme_mod( 'bmd_light_background', '#f7f4fb' ) ) ?: '#f7f4fb';
	$light_surface = sanitize_hex_color( get_theme_mod( 'bmd_light_surface', '#ffffff' ) ) ?: '#ffffff';
	$category_bg = sanitize_hex_color( get_theme_mod( 'bmd_single_category_bg', '#f3edf8' ) ) ?: '#f3edf8';
	$category_border = sanitize_hex_color( get_theme_mod( 'bmd_single_category_border', '#ddd2e8' ) ) ?: '#ddd2e8';
	$category_text = sanitize_hex_color( get_theme_mod( 'bmd_single_category_text', '#4b3b55' ) ) ?: '#4b3b55';
	$desktop = max( 2, min( 6, absint( get_theme_mod( 'bmd_columns_desktop', 4 ) ) ) );
	$tablet = max( 2, min( 4, absint( get_theme_mod( 'bmd_columns_tablet', 3 ) ) ) );
	$mobile = max( 1, min( 2, absint( get_theme_mod( 'bmd_columns_mobile', 2 ) ) ) );
	$gallery_cols = max( 2, min( 6, absint( get_theme_mod( 'bmd_single_gallery_columns', 4 ) ) ) );
	$gallery_cols_tablet = max( 2, min( 4, absint( get_theme_mod( 'bmd_single_gallery_columns_tablet', 3 ) ) ) );
	$gallery_cols_mobile = max( 1, min( 3, absint( get_theme_mod( 'bmd_single_gallery_columns_mobile', 2 ) ) ) );
	$gallery_rows = max( 1, min( 6, absint( get_theme_mod( 'bmd_single_gallery_rows', 2 ) ) ) );
	$gallery_gap = max( 6, min( 32, absint( get_theme_mod( 'bmd_single_gallery_gap', 10 ) ) ) );
	$gallery_ratio = bmd_sanitize_choice( get_theme_mod( 'bmd_single_gallery_ratio', 'landscape' ), array( 'landscape', 'portrait' ), 'landscape' );
	$random_cols_desktop = max( 3, min( 6, absint( get_theme_mod( 'bmd_random_latest_columns_desktop', 5 ) ) ) );
	$random_cols_tablet = max( 2, min( 5, absint( get_theme_mod( 'bmd_random_latest_columns_tablet', 4 ) ) ) );
	$random_cols_mobile = max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_columns_mobile', 3 ) ) ) );
	$random_gap = max( 8, min( 40, absint( get_theme_mod( 'bmd_random_latest_gap', 16 ) ) ) );
	$random_rows_desktop = max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_rows_desktop', 2 ) ) ) );
	$random_rows_tablet = max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_rows_tablet', 2 ) ) ) );
	$random_rows_mobile = max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_rows_mobile', 2 ) ) ) );
	$header_breakpoint = max( 640, min( 1400, absint( get_theme_mod( 'bmd_header_breakpoint', 900 ) ) ) );
	$header_height = max( 56, min( 112, absint( get_theme_mod( 'bmd_header_height', 76 ) ) ) );
	$section_gap = max( 8, min( 72, absint( get_theme_mod( 'bmd_home_section_gap', 24 ) ) ) );
	$css = ':root{--bmd-accent:' . $accent . ';--bmd-accent-2:' . $accent_2 . ';--bmd-dark-bg:' . $dark_bg . ';--bmd-dark-surface:' . $dark_surface . ';--bmd-light-bg:' . $light_bg . ';--bmd-light-surface:' . $light_surface . ';--bmd-single-category-bg:' . $category_bg . ';--bmd-single-category-border:' . $category_border . ';--bmd-single-category-text:' . $category_text . ';--bmd-cols-desktop:' . $desktop . ';--bmd-cols-tablet:' . $tablet . ';--bmd-cols-mobile:' . $mobile . ';--bmd-single-gallery-cols:' . $gallery_cols . ';--bmd-single-gallery-cols-tablet:' . $gallery_cols_tablet . ';--bmd-single-gallery-cols-mobile:' . $gallery_cols_mobile . ';--bmd-single-gallery-gap:' . $gallery_gap . 'px;--bmd-single-gallery-ratio:' . ( 'portrait' === $gallery_ratio ? '2/3' : '16/9' ) . ';--bmd-random-latest-cols-desktop:' . $random_cols_desktop . ';--bmd-random-latest-cols-tablet:' . $random_cols_tablet . ';--bmd-random-latest-cols-mobile:' . $random_cols_mobile . ';--bmd-random-latest-gap:' . $random_gap . 'px;--bmd-random-latest-rows-desktop:' . $random_rows_desktop . ';--bmd-random-latest-rows-tablet:' . $random_rows_tablet . ';--bmd-random-latest-rows-mobile:' . $random_rows_mobile . ';--bmd-header-breakpoint:' . $header_breakpoint . 'px;--bmd-header-height:' . $header_height . 'px;--bmd-home-section-gap:' . $section_gap . 'px;}@media (max-width:' . $header_breakpoint . 'px){.bmd-header-inner{grid-template-columns:1fr auto;}.bmd-nav{display:none;}.bmd-menu-toggle{display:inline-grid;}.bmd-mobile-menu .bmd-container{padding-block:10px 16px;}.bmd-mobile-nav-list{display:grid;gap:5px;}.bmd-mobile-nav-list a{display:block;padding:11px 12px;border:1px solid var(--bmd-border);border-radius:10px;background:var(--bmd-surface);color:var(--bmd-text);}.bmd-mobile-nav-list .sub-menu{list-style:none;margin:5px 0 0;padding-left:12px;display:grid;gap:5px;}}';
	wp_add_inline_style( 'bossmaster-display', $css );
}
add_action( 'wp_enqueue_scripts', 'bmd_custom_css', 130 );
