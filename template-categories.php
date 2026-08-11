<?php
/* Template Name: BOSSMASTER Categories */
defined( 'ABSPATH' ) || exit;
get_header();
$terms = get_terms( array( 'taxonomy' => 'category', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC' ) );
$count = is_wp_error( $terms ) ? 0 : count( $terms );
$icons = array( '✦', '↗', '☆', '▣', '◫', '∞', '↺', '●' );
?>
<section class="bmd-page-hero bmd-page-hero--categories">
	<div class="bmd-container">
		<p class="bmd-eyebrow"><?php esc_html_e( 'CATEGORIES', 'bossmaster-display' ); ?></p>
		<h1><?php esc_html_e( 'หมวดหมู่ทั้งหมด', 'bossmaster-display' ); ?></h1>
		<p class="bmd-page-description"><?php esc_html_e( 'เลือกหมวดหมู่เพื่อเปิดดูวิดีโอที่เกี่ยวข้องทั้งหมด', 'bossmaster-display' ); ?></p>
		<span class="bmd-page-number" aria-hidden="true"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
	</div>
</section>
<section class="bmd-section bmd-categories-page-section">
	<div class="bmd-container">
		<div class="bmd-category-list">
			<?php if ( ! is_wp_error( $terms ) ) : foreach ( $terms as $index => $term ) : $url = get_term_link( $term ); if ( is_wp_error( $url ) ) { continue; } ?>
				<a href="<?php echo esc_url( $url ); ?>">
					<span class="bmd-category-icon"><?php echo esc_html( $icons[ $index % count( $icons ) ] ); ?></span>
					<span class="bmd-category-copy"><strong><?php echo esc_html( $term->name ); ?></strong><small><?php echo esc_html( $term->description ? wp_html_excerpt( wp_strip_all_tags( $term->description ), 72, '…' ) : __( 'เปิดดูวิดีโอในหมวดหมู่นี้', 'bossmaster-display' ) ); ?></small></span>
					<span class="bmd-category-count"><strong><?php echo esc_html( number_format_i18n( $term->count ) ); ?></strong><small><?php esc_html_e( 'เรื่อง', 'bossmaster-display' ); ?></small></span>
					<span class="bmd-category-arrow">→</span>
				</a>
			<?php endforeach; endif; ?>
		</div>
	</div>
</section>
<?php get_footer(); ?>
