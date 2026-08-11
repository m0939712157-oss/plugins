<?php
/**
 * Header for BOSSMASTER Display.
 *
 * @package BOSSMASTER_Display
 */

defined( 'ABSPATH' ) || exit;

$header_layout = bmd_sanitize_choice( get_theme_mod( 'bmd_nav_mode', 'auto' ), array( 'auto', 'compact', 'stacked' ), 'auto' );
$header_show_theme_toggle = (bool) get_theme_mod( 'bmd_header_show_theme_toggle', true );
$header_show_search = (bool) get_theme_mod( 'bmd_header_show_search', true );
$header_show_language = (bool) get_theme_mod( 'bmd_header_show_language', true );
$header_sticky = (bool) get_theme_mod( 'bmd_header_sticky', true );
$header_height = max( 56, min( 112, absint( get_theme_mod( 'bmd_header_height', 76 ) ) ) );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-bmd-default-theme="<?php echo esc_attr( get_theme_mod( 'bmd_default_theme', 'dark' ) ); ?>">
<?php wp_body_open(); ?>
<div id="page" class="bmd-site">
	<?php if ( get_theme_mod( 'bmd_show_top_notice', true ) ) : ?>
		<div class="bmd-top-notice">
			<div class="bmd-container">
				<strong><?php echo esc_html( get_theme_mod( 'bmd_top_notice', 'BOSSMASTER INTERACTIVE DISPLAY' ) ); ?></strong>
				<span><?php esc_html_e( 'ทุกเมนูเชื่อมข้อมูลจริงจาก WordPress', 'bossmaster-display' ); ?></span>
			</div>
		</div>
	<?php endif; ?>

	<header class="bmd-header" id="bmd-header" data-bmd-sticky="<?php echo esc_attr( $header_sticky ? 'true' : 'false' ); ?>">
		<div class="bmd-container bmd-header-inner" data-bmd-header-layout="<?php echo esc_attr( $header_layout ); ?>" style="--bmd-header-height:<?php echo esc_attr( $header_height ); ?>px;">
			<div class="bmd-brand">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<a class="bmd-brand-fallback" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
						<span class="bmd-brand-mark">B</span>
						<span class="bmd-brand-copy"><strong>BOSSMASTER</strong><small>ENTERTAINMENT</small></span>
					</a>
				<?php endif; ?>
			</div>

			<nav class="bmd-nav" aria-label="<?php esc_attr_e( 'Primary navigation', 'bossmaster-display' ); ?>">
				<?php bmd_render_header_menu( array( 'menu_class' => 'bmd-nav-list' ) ); ?>
			</nav>

			<div class="bmd-header-actions">
				<?php if ( $header_show_theme_toggle ) : ?>
				<button type="button" class="bmd-icon-button bmd-theme-toggle" aria-label="<?php esc_attr_e( 'สลับโหมดสี', 'bossmaster-display' ); ?>" title="<?php esc_attr_e( 'สลับโหมดสี', 'bossmaster-display' ); ?>">
					<span aria-hidden="true">◐</span>
				</button>
				<?php endif; ?>
				<?php if ( $header_show_search ) : ?>
				<button type="button" class="bmd-icon-button bmd-search-toggle" aria-expanded="false" aria-label="<?php esc_attr_e( 'ค้นหา', 'bossmaster-display' ); ?>" title="<?php esc_attr_e( 'ค้นหา', 'bossmaster-display' ); ?>">
					<span aria-hidden="true">⌕</span>
				</button>
				<?php endif; ?>
				<?php if ( $header_show_language ) : ?>
				<a class="bmd-icon-button bmd-language-toggle" href="<?php echo esc_url( add_query_arg( 'lang', 'th', home_url( '/' ) ) ); ?>" aria-label="<?php esc_attr_e( 'ไทย', 'bossmaster-display' ); ?>" title="TH">TH</a>
				<?php endif; ?>
				<button type="button" class="bmd-icon-button bmd-menu-toggle" aria-controls="bmd-mobile-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'เปิดเมนู', 'bossmaster-display' ); ?>">
					<span aria-hidden="true">☰</span>
				</button>
			</div>
		</div>

		<div class="bmd-search-panel" id="bmd-search-panel" hidden>
			<div class="bmd-container">
				<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="bmd-search-form">
					<label class="screen-reader-text" for="bmd-search-input"><?php esc_html_e( 'ค้นหา', 'bossmaster-display' ); ?></label>
					<input id="bmd-search-input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'ค้นหาชื่อเรื่อง รหัส นักแสดง หรือค่าย…', 'bossmaster-display' ); ?>">
					<button type="submit"><?php esc_html_e( 'ค้นหา', 'bossmaster-display' ); ?> →</button>
				</form>
			</div>
		</div>

		<div class="bmd-mobile-menu" id="bmd-mobile-menu" hidden>
			<div class="bmd-container">
				<?php bmd_render_header_menu( array( 'menu_class' => 'bmd-mobile-nav-list' ) ); ?>
			</div>
		</div>
	</header>

	<main id="content" class="bmd-main-content">
