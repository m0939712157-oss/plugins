<?php
/**
 * Generic archive.
 *
 * @package BOSSMASTER_Display
 */

defined( 'ABSPATH' ) || exit;
get_header();

global $wp_query;
$count       = isset( $wp_query->found_posts ) ? absint( $wp_query->found_posts ) : 0;
$title       = get_the_archive_title();
$description = get_the_archive_description();
if ( is_home() ) {
	$title       = __( 'วิดีโอใหม่', 'bossmaster-display' );
	$description = __( 'รายการทั้งหมดเรียงจากโพสต์ล่าสุด', 'bossmaster-display' );
}
$current_sort = isset( $_GET['bmd_sort'] ) ? sanitize_key( wp_unslash( $_GET['bmd_sort'] ) ) : 'latest';
?>
<section class="bmd-page-hero bmd-page-hero--archive">
	<div class="bmd-container">
		<p class="bmd-eyebrow"><?php esc_html_e( 'BROWSE', 'bossmaster-display' ); ?></p>
		<h1><?php echo wp_kses_post( $title ); ?></h1>
		<?php if ( $description ) : ?><div class="bmd-page-description"><?php echo wp_kses_post( $description ); ?></div><?php endif; ?>
		<span class="bmd-page-number" aria-hidden="true"><?php echo esc_html( number_format_i18n( $count ) ); ?></span>
	</div>
</section>

<section class="bmd-section bmd-archive-section">
	<div class="bmd-container">
		<div class="bmd-archive-toolbar">
			<span><?php echo esc_html( sprintf( __( 'พบ %s รายการ', 'bossmaster-display' ), number_format_i18n( $count ) ) ); ?></span>
			<form method="get" class="bmd-sort-form">
				<?php foreach ( $_GET as $key => $value ) : if ( 'bmd_sort' === $key || is_array( $value ) ) { continue; } ?>
					<input type="hidden" name="<?php echo esc_attr( sanitize_key( $key ) ); ?>" value="<?php echo esc_attr( sanitize_text_field( wp_unslash( $value ) ) ); ?>">
				<?php endforeach; ?>
				<label for="bmd-sort"><?php esc_html_e( 'เรียงตาม', 'bossmaster-display' ); ?></label>
				<select id="bmd-sort" name="bmd_sort" onchange="this.form.submit()">
					<option value="latest" <?php selected( $current_sort, 'latest' ); ?>><?php esc_html_e( 'ล่าสุด', 'bossmaster-display' ); ?></option>
					<option value="oldest" <?php selected( $current_sort, 'oldest' ); ?>><?php esc_html_e( 'เก่าสุด', 'bossmaster-display' ); ?></option>
					<option value="title" <?php selected( $current_sort, 'title' ); ?>><?php esc_html_e( 'ชื่อเรื่อง', 'bossmaster-display' ); ?></option>
					<option value="popular" <?php selected( $current_sort, 'popular' ); ?>><?php esc_html_e( 'ยอดนิยม', 'bossmaster-display' ); ?></option>
				</select>
			</form>
		</div>

		<?php if ( have_posts() ) : ?>
			<div class="bmd-video-grid">
				<?php while ( have_posts() ) : the_post(); bmd_render_video_card(); endwhile; ?>
			</div>
			<div class="bmd-pagination"><?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => '←', 'next_text' => '→' ) ); ?></div>
		<?php else : ?>
			<div class="bmd-empty-state"><h2><?php esc_html_e( 'ยังไม่พบรายการ', 'bossmaster-display' ); ?></h2><p><?php esc_html_e( 'ลองเลือกหมวดหมู่อื่นหรือใช้ช่องค้นหาด้านบน', 'bossmaster-display' ); ?></p></div>
		<?php endif; ?>
	</div>
</section>
<?php get_footer(); ?>
