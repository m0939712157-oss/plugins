<?php
/** Actor profile archive. */
defined( 'ABSPATH' ) || exit;
get_header();
$term  = get_queried_object();
$image = $term instanceof WP_Term ? bmd_get_term_image( $term, 'large' ) : '';
$letter = $term instanceof WP_Term ? ( function_exists( 'mb_substr' ) ? mb_substr( $term->name, 0, 1 ) : substr( $term->name, 0, 1 ) ) : 'A';
?>
<section class="bmd-profile-hero">
	<div class="bmd-container bmd-profile-grid">
		<a class="bmd-back-link" href="<?php echo esc_url( bmd_page_url_by_template( 'template-actors.php', '/actors/' ) ); ?>">← <?php esc_html_e( 'กลับหน้ารวม', 'bossmaster-display' ); ?></a>
		<div class="bmd-profile-image<?php echo $image ? '' : ' is-placeholder'; ?>">
			<?php if ( $image ) : ?><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $term->name ); ?>"><?php else : ?><span><?php echo esc_html( strtoupper( $letter ) ); ?></span><?php endif; ?>
		</div>
		<div class="bmd-profile-copy">
			<p class="bmd-eyebrow"><?php esc_html_e( 'ACTOR PROFILE', 'bossmaster-display' ); ?></p>
			<h1><?php echo esc_html( $term->name ); ?></h1>
			<?php if ( $term->description ) : ?><div class="bmd-profile-description"><?php echo wp_kses_post( wpautop( $term->description ) ); ?></div><?php endif; ?>
			<div class="bmd-profile-stats">
				<div><strong><?php echo esc_html( number_format_i18n( $term->count ) ); ?></strong><span><?php esc_html_e( 'ผลงานทั้งหมด', 'bossmaster-display' ); ?></span></div>
				<div><strong><?php echo esc_html( max( 1, (int) ceil( $term->count / max( 1, get_option( 'posts_per_page', 10 ) ) ) ) ); ?></strong><span><?php esc_html_e( 'หน้ารายการ', 'bossmaster-display' ); ?></span></div>
				<div><strong>HD</strong><span><?php esc_html_e( 'คุณภาพสูง', 'bossmaster-display' ); ?></span></div>
			</div>
		</div>
	</div>
</section>
<section class="bmd-section">
	<div class="bmd-container">
		<div class="bmd-section-heading"><div><p class="bmd-eyebrow"><?php esc_html_e( 'RELATED VIDEOS', 'bossmaster-display' ); ?></p><h2><?php echo esc_html( sprintf( __( 'ผลงานของ %s', 'bossmaster-display' ), $term->name ) ); ?></h2></div></div>
		<?php if ( have_posts() ) : ?><div class="bmd-video-grid"><?php while ( have_posts() ) : the_post(); bmd_render_video_card(); endwhile; ?></div><div class="bmd-pagination"><?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => '←', 'next_text' => '→' ) ); ?></div><?php else : ?><div class="bmd-empty-state"><h2><?php esc_html_e( 'ยังไม่มีผลงาน', 'bossmaster-display' ); ?></h2></div><?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
