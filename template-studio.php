<?php
/* Template Name: BOSSMASTER Studios */
defined( 'ABSPATH' ) || exit;
get_header();
$terms = get_terms( array( 'taxonomy' => 'studio', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 300 ) );
$count = is_wp_error( $terms ) ? 0 : count( $terms );
?>
<section class="bmd-page-hero bmd-page-hero--studio">
	<div class="bmd-container">
		<p class="bmd-eyebrow"><?php esc_html_e( 'STUDIOS', 'bossmaster-display' ); ?></p>
		<h1><?php esc_html_e( 'ค่ายทั้งหมด', 'bossmaster-display' ); ?></h1>
		<p class="bmd-page-description"><?php esc_html_e( 'รวมค่ายและสตูดิโอ พร้อมผลงานที่เกี่ยวข้อง', 'bossmaster-display' ); ?></p>
		<span class="bmd-page-number" aria-hidden="true"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
	</div>
</section>
<section class="bmd-section bmd-directory-section bmd-studio-page-section">
	<div class="bmd-container">
		<label class="bmd-directory-search"><span>⌕</span><input type="search" data-bmd-term-search placeholder="<?php esc_attr_e( 'ค้นหาชื่อค่าย…', 'bossmaster-display' ); ?>"></label>
		<div class="bmd-term-grid is-studio" data-bmd-term-grid>
			<?php if ( ! is_wp_error( $terms ) ) : foreach ( $terms as $term ) : bmd_render_term_card( $term ); endforeach; endif; ?>
		</div>
	</div>
</section>
<?php get_footer(); ?>
