<?php
/* Template Name: BOSSMASTER Tags */
defined( 'ABSPATH' ) || exit;
get_header();
$terms = get_terms( array( 'taxonomy' => 'post_tag', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 300 ) );
$count = is_wp_error( $terms ) ? 0 : count( $terms );
?>
<section class="bmd-page-hero bmd-page-hero--tags">
	<div class="bmd-container">
		<p class="bmd-eyebrow"><?php esc_html_e( 'POPULAR TAGS', 'bossmaster-display' ); ?></p>
		<h1><?php esc_html_e( 'ค้นหาด้วยแท็ก', 'bossmaster-display' ); ?></h1>
		<p class="bmd-page-description"><?php esc_html_e( 'เลือกคำที่สนใจเพื่อดูรายการที่มีแท็กเดียวกัน', 'bossmaster-display' ); ?></p>
		<span class="bmd-page-number" aria-hidden="true"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
	</div>
</section>
<section class="bmd-section bmd-tags-page-section">
	<div class="bmd-container">
		<label class="bmd-directory-search"><span>⌕</span><input type="search" data-bmd-term-search placeholder="<?php esc_attr_e( 'ค้นหาแท็ก…', 'bossmaster-display' ); ?>"></label>
		<div class="bmd-tag-cloud" data-bmd-term-grid>
			<?php if ( ! is_wp_error( $terms ) ) : foreach ( $terms as $term ) : $url = get_term_link( $term ); if ( is_wp_error( $url ) ) { continue; } ?>
				<a href="<?php echo esc_url( $url ); ?>" class="bmd-tag-chip"><strong>#<?php echo esc_html( $term->name ); ?></strong><small><?php echo esc_html( number_format_i18n( $term->count ) ); ?> <?php esc_html_e( 'รายการ', 'bossmaster-display' ); ?></small></a>
			<?php endforeach; endif; ?>
		</div>
	</div>
</section>
<?php get_footer(); ?>
