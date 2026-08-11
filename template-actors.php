<?php
/* Template Name: BOSSMASTER Actors */
defined( 'ABSPATH' ) || exit;
get_header();
$terms = get_terms( array( 'taxonomy' => 'actors', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 300 ) );
$count = is_wp_error( $terms ) ? 0 : count( $terms );
?>
<section class="bmd-page-hero bmd-page-hero--actors">
	<div class="bmd-container">
		<p class="bmd-eyebrow"><?php esc_html_e( 'ACTORS', 'bossmaster-display' ); ?></p>
		<h1><?php esc_html_e( 'นักแสดงทั้งหมด', 'bossmaster-display' ); ?></h1>
		<p class="bmd-page-description"><?php esc_html_e( 'เลือกนักแสดงเพื่อดูประวัติย่อและผลงานที่เกี่ยวข้อง', 'bossmaster-display' ); ?></p>
		<span class="bmd-page-number" aria-hidden="true"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
	</div>
</section>
<section class="bmd-section bmd-directory-section bmd-actors-page-section">
	<div class="bmd-container">
		<label class="bmd-directory-search"><span>⌕</span><input type="search" data-bmd-term-search placeholder="<?php esc_attr_e( 'ค้นหาชื่อนักแสดง…', 'bossmaster-display' ); ?>"></label>
		<div class="bmd-term-grid" data-bmd-term-grid>
			<?php if ( ! is_wp_error( $terms ) ) : foreach ( $terms as $term ) : bmd_render_term_card( $term ); endforeach; endif; ?>
		</div>
	</div>
</section>
<?php get_footer(); ?>
