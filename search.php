<?php
/**
 * Search results.
 *
 * @package BOSSMASTER_Display
 */

defined( 'ABSPATH' ) || exit;
get_header();
global $wp_query;
$count = isset( $wp_query->found_posts ) ? absint( $wp_query->found_posts ) : 0;
?>
<section class="bmd-page-hero bmd-page-hero--search">
	<div class="bmd-container">
		<p class="bmd-eyebrow"><?php esc_html_e( 'SEARCH RESULTS', 'bossmaster-display' ); ?></p>
		<h1><?php echo esc_html( sprintf( __( 'ผลการค้นหา “%s”', 'bossmaster-display' ), get_search_query() ) ); ?></h1>
		<p class="bmd-page-description"><?php echo esc_html( sprintf( __( 'พบ %s รายการที่ตรงกับคำค้นหา', 'bossmaster-display' ), number_format_i18n( $count ) ) ); ?></p>
		<span class="bmd-page-number" aria-hidden="true"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
	</div>
</section>
<section class="bmd-section bmd-archive-section">
	<div class="bmd-container">
		<?php if ( have_posts() ) : ?>
			<div class="bmd-video-grid">
				<?php while ( have_posts() ) : the_post(); bmd_render_video_card(); endwhile; ?>
			</div>
			<div class="bmd-pagination"><?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => '←', 'next_text' => '→' ) ); ?></div>
		<?php else : ?>
			<div class="bmd-search-empty">
				<span class="bmd-search-empty-icon">⌕</span>
				<div><h2><?php esc_html_e( 'ยังไม่พบรายการที่ตรงกับคำค้นหานี้', 'bossmaster-display' ); ?></h2><p><?php esc_html_e( 'ลองใช้รหัส ชื่อนักแสดง ชื่อค่าย หรือคำที่สั้นลง', 'bossmaster-display' ); ?></p></div>
				<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="bmd-search-form is-inline">
					<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'ค้นหาอีกครั้ง…', 'bossmaster-display' ); ?>">
					<button type="submit"><?php esc_html_e( 'ดูวิธีค้นหา', 'bossmaster-display' ); ?> →</button>
				</form>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
