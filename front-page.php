<?php
/**
 * Front page.
 *
 * @package BOSSMASTER_Display
 */

defined( 'ABSPATH' ) || exit;
get_header();

$featured_count = max( 3, min( 12, absint( get_theme_mod( 'bmd_featured_count', 5 ) ) ) );
$latest_count   = max( 4, min( 24, absint( get_theme_mod( 'bmd_latest_count', 8 ) ) ) );
$featured_style = bmd_sanitize_choice( get_theme_mod( 'bmd_featured_style', 'carousel' ), array( 'carousel', 'grid' ), 'carousel' );
$section_gap    = max( 8, min( 72, absint( get_theme_mod( 'bmd_home_section_gap', 24 ) ) ) );
$home_order     = bmd_get_home_section_order();
$show_random_latest = (bool) get_theme_mod( 'bmd_show_random_latest', true );
$random_latest_count = max( 6, min( 24, absint( get_theme_mod( 'bmd_random_latest_count', 15 ) ) ) );
$random_latest_title = trim( (string) get_theme_mod( 'bmd_random_latest_title', __( 'สุ่มจากรายการล่าสุด', 'bossmaster-display' ) ) );
$random_latest_columns_desktop = max( 3, min( 6, absint( get_theme_mod( 'bmd_random_latest_columns_desktop', 5 ) ) ) );
$random_latest_columns_tablet = max( 2, min( 5, absint( get_theme_mod( 'bmd_random_latest_columns_tablet', 4 ) ) ) );
$random_latest_columns_mobile = max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_columns_mobile', 3 ) ) ) );
$random_latest_gap = max( 8, min( 40, absint( get_theme_mod( 'bmd_random_latest_gap', 16 ) ) ) );
$random_latest_rows_desktop = max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_rows_desktop', 2 ) ) ) );
$random_latest_rows_tablet = max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_rows_tablet', 2 ) ) ) );
$random_latest_rows_mobile = max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_rows_mobile', 2 ) ) ) );
$random_latest_natural = (bool) get_theme_mod( 'bmd_random_latest_natural', true );
$random_latest_view_all = trim( (string) get_theme_mod( 'bmd_random_latest_view_all', __( 'ดูทั้งหมด', 'bossmaster-display' ) ) );
$random_latest  = bmd_get_random_latest_posts( $random_latest_count );
$explore_count = max( 3, min( 6, absint( get_theme_mod( 'bmd_explore_count', 5 ) ) ) );
$explore_columns_desktop = max( 2, min( 4, absint( get_theme_mod( 'bmd_explore_columns_desktop', 3 ) ) ) );
$explore_columns_tablet = max( 1, min( 3, absint( get_theme_mod( 'bmd_explore_columns_tablet', 2 ) ) ) );
$explore_columns_mobile = max( 1, min( 2, absint( get_theme_mod( 'bmd_explore_columns_mobile', 1 ) ) ) );
$explore_gap = max( 8, min( 32, absint( get_theme_mod( 'bmd_explore_gap', 12 ) ) ) );
$featured_args  = array(
	'posts_per_page'      => $featured_count,
	'ignore_sticky_posts' => false,
	'orderby'             => 'date',
	'order'               => 'DESC',
);
$featured = bmd_query_homepage_posts( $featured_count, $featured_args );
$latest = bmd_query_homepage_posts( $latest_count, array( 'ignore_sticky_posts' => true ) );
$hero_post_count = 5;
$hero_posts      = array();
$hero_images     = array();
$hero_pool       = array_merge( (array) $featured->posts, (array) $latest->posts );

// First pass: collect posts that have poster images (avoid placeholders)
foreach ( $hero_pool as $post ) {
	if ( count( $hero_posts ) >= $hero_post_count ) {
		break;
	}
	if ( ! $post || empty( $post->ID ) || isset( $hero_posts[ $post->ID ] ) ) {
		continue;
	}
	$poster = bmd_get_poster( $post->ID, 'bmd-card' );
	if ( ! $poster ) {
		continue; // skip posts without poster to avoid empty placeholders
	}
	if ( in_array( $poster, $hero_images, true ) ) {
		continue; // avoid duplicate images
	}
	$hero_posts[ $post->ID ] = $post;
	$hero_images[] = $poster;
}

// If still short, query additional posts that have a featured image (thumbnail)
if ( count( $hero_posts ) < $hero_post_count ) {
	$exclude_ids = array_keys( $hero_posts );
	$needed = $hero_post_count - count( $hero_posts );
	$more_query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $needed,
			'post__not_in'        => $exclude_ids,
			'meta_query'          => array( array( 'key' => '_thumbnail_id', 'compare' => 'EXISTS' ) ),
			'orderby'             => 'date',
			'order'               => 'DESC',
			'no_found_rows'       => true,
		)
	);
	if ( $more_query->have_posts() ) {
		foreach ( $more_query->posts as $post ) {
			if ( count( $hero_posts ) >= $hero_post_count ) {
				break;
			}
			if ( ! $post || empty( $post->ID ) || isset( $hero_posts[ $post->ID ] ) ) {
				continue;
			}
			$poster = bmd_get_poster( $post->ID, 'bmd-card' );
			if ( ! $poster || in_array( $poster, $hero_images, true ) ) {
				continue;
			}
			$hero_posts[ $post->ID ] = $post;
			$hero_images[] = $poster;
		}
		wp_reset_postdata();
	}
}

// Final fallback: if still short, accept posts without poster but ensure uniqueness
if ( count( $hero_posts ) < $hero_post_count ) {
	$pool2 = array_merge( (array) $featured->posts, (array) $latest->posts );
	foreach ( $pool2 as $post ) {
		if ( count( $hero_posts ) >= $hero_post_count ) {
			break;
		}
		if ( ! $post || empty( $post->ID ) || isset( $hero_posts[ $post->ID ] ) ) {
			continue;
		}
		$hero_posts[ $post->ID ] = $post;
	}
}

$hero_posts = array_values( $hero_posts );

$archive_url = get_post_type_archive_link( 'post' ) ?: home_url( '/?post_type=post' );
$hero_title = trim( (string) get_theme_mod( 'bmd_hero_title', 'คลังวิดีโอที่จัดเรียงให้ค้นหาได้ง่ายและดูสบายตา' ) );
$hero_highlight = trim( (string) get_theme_mod( 'bmd_hero_highlight', 'ค้นหาได้ง่าย' ) );
if ( $hero_highlight && false !== ( function_exists( 'mb_strpos' ) ? mb_strpos( $hero_title, $hero_highlight ) : strpos( $hero_title, $hero_highlight ) ) ) {
	$hero_title = trim( str_replace( $hero_highlight, '', $hero_title ) );
}
if ( '' === $hero_title ) {
	$hero_title = 'คลังวิดีโอที่จัดเรียง';
}
?>

<?php if ( in_array( 'hero', $home_order, true ) && get_theme_mod( 'bmd_show_hero', true ) && ! empty( $hero_posts ) ) : ?>
<section class="bmd-hero" data-bmd-home-block="hero" style="--bmd-home-section-gap:<?php echo esc_attr( $section_gap ); ?>px;">
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
<?php endif; ?>

<?php if ( bmd_avfp_home_sections_enabled() ) : ?>
	<?php bmd_avfp_home_sections_render(); ?>
<?php else : ?>
	<?php if ( in_array( 'featured', $home_order, true ) && get_theme_mod( 'bmd_show_featured', true ) && $featured->have_posts() ) : ?>
	<section class="bmd-section bmd-featured-section" data-bmd-home-block="featured" style="--bmd-home-section-gap:<?php echo esc_attr( $section_gap ); ?>px;">
		<div class="bmd-container">
			<div class="bmd-featured-heading">
				<div class="bmd-section-heading">
					<div><p class="bmd-eyebrow"><?php echo esc_html( sprintf( __( 'FEATURED · %d STORIES', 'bossmaster-display' ), $featured->post_count ) ); ?></p><h2><?php esc_html_e( 'เรื่องเด่นแนะนำ', 'bossmaster-display' ); ?></h2><p class="bmd-section-subtitle"><?php esc_html_e( 'เลื่อนอัตโนมัติ หรือกดลูกศรเพื่อเลื่อนเอง', 'bossmaster-display' ); ?></p></div>
				</div>
				<div class="bmd-slider-actions">
					<a href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'ดูทั้งหมด', 'bossmaster-display' ); ?> →</a>
					<button type="button" data-bmd-slider-prev aria-label="<?php esc_attr_e( 'เรื่องก่อนหน้า', 'bossmaster-display' ); ?>">←</button>
					<button type="button" data-bmd-slider-pause aria-label="<?php esc_attr_e( 'หยุดสไลด์', 'bossmaster-display' ); ?>">Ⅱ</button>
					<button type="button" data-bmd-slider-next aria-label="<?php esc_attr_e( 'เรื่องถัดไป', 'bossmaster-display' ); ?>">→</button>
				</div>
			</div>
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
	<?php endif; ?>

	<?php if ( in_array( 'latest', $home_order, true ) && get_theme_mod( 'bmd_show_latest', true ) && $latest->have_posts() ) : ?>
	<section class="bmd-section bmd-latest-section" id="bmd-latest" data-bmd-home-block="latest" style="--bmd-home-section-gap:<?php echo esc_attr( $section_gap ); ?>px;">
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
	<?php endif; ?>

	<?php if ( in_array( 'random_latest', $home_order, true ) && $show_random_latest && $random_latest->have_posts() ) : ?>
	<section class="bmd-section bmd-random-latest-section" data-bmd-home-block="random_latest" style="--bmd-home-section-gap:<?php echo esc_attr( $section_gap ); ?>px;">
		<div class="bmd-container">
			<div class="bmd-section-heading">
				<div><p class="bmd-eyebrow"><?php esc_html_e( 'RANDOM PICK', 'bossmaster-display' ); ?></p><h2><?php echo esc_html( $random_latest_title ?: __( 'สุ่มจากรายการล่าสุด', 'bossmaster-display' ) ); ?></h2></div>
				<a href="<?php echo esc_url( $archive_url ); ?>">⭐ <?php echo esc_html( $random_latest_view_all ?: __( 'ดูทั้งหมด', 'bossmaster-display' ) ); ?> →</a>
			</div>
			<div class="bmd-video-grid bmd-random-latest-grid" style="--bmd-random-latest-cols-desktop:<?php echo esc_attr( $random_latest_columns_desktop ); ?>;--bmd-random-latest-cols-tablet:<?php echo esc_attr( $random_latest_columns_tablet ); ?>;--bmd-random-latest-cols-mobile:<?php echo esc_attr( $random_latest_columns_mobile ); ?>;--bmd-random-latest-gap:<?php echo esc_attr( $random_latest_gap ); ?>px;--bmd-random-latest-rows-desktop:<?php echo esc_attr( $random_latest_rows_desktop ); ?>;--bmd-random-latest-rows-tablet:<?php echo esc_attr( $random_latest_rows_tablet ); ?>;--bmd-random-latest-rows-mobile:<?php echo esc_attr( $random_latest_rows_mobile ); ?>;">
				<?php $rendered = 0; while ( $random_latest->have_posts() ) : $random_latest->the_post(); if ( $rendered >= $random_latest_count ) { break; } $rendered++; bmd_render_video_card( get_the_ID(), array( 'orientation' => $random_latest_natural ? 'natural' : '' ) ); endwhile; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( in_array( 'directories', $home_order, true ) && get_theme_mod( 'bmd_show_directories', true ) ) : ?>
	<section class="bmd-section bmd-explore-section" data-bmd-home-block="directories" style="--bmd-home-section-gap:<?php echo esc_attr( $section_gap ); ?>px;">
		<div class="bmd-container">
			<div class="bmd-section-heading">
				<div><p class="bmd-eyebrow"><?php esc_html_e( 'EXPLORE', 'bossmaster-display' ); ?></p><h2><?php esc_html_e( 'เลือกดูตามความสนใจ', 'bossmaster-display' ); ?></h2></div>
				<a href="<?php echo esc_url( bmd_page_url_by_template( 'template-categories.php', '/categories/' ) ); ?>">⭐ <?php esc_html_e( 'ดูทั้งหมด', 'bossmaster-display' ); ?> →</a>
			</div>
			<div class="bmd-explore-grid" style="--bmd-explore-cols-desktop:<?php echo esc_attr( $explore_columns_desktop ); ?>;--bmd-explore-cols-tablet:<?php echo esc_attr( $explore_columns_tablet ); ?>;--bmd-explore-cols-mobile:<?php echo esc_attr( $explore_columns_mobile ); ?>;--bmd-explore-gap:<?php echo esc_attr( $explore_gap ); ?>px;">
				<?php $explore_items = array(
					array( 'url' => bmd_page_url_by_template( 'template-actors.php', '/actors/' ), 'title' => __( 'นักแสดงยอดนิยม', 'bossmaster-display' ), 'copy' => __( 'เลือกดูผลงานตามนักแสดง', 'bossmaster-display' ) ),
					array( 'url' => bmd_page_url_by_template( 'template-studio.php', '/studio/' ), 'title' => __( 'ค่ายแนะนำ', 'bossmaster-display' ), 'copy' => __( 'ค้นหาผลงานจากค่ายที่ชอบ', 'bossmaster-display' ) ),
					array( 'url' => $archive_url, 'title' => __( 'รวมวิดีโอใหม่', 'bossmaster-display' ), 'copy' => __( 'อัปเดตรายการล่าสุดทุกวัน', 'bossmaster-display' ) ),
					array( 'url' => bmd_page_url_by_template( 'template-tags.php', '/tags/' ), 'title' => __( 'เลือกตามแท็ก', 'bossmaster-display' ), 'copy' => __( 'ค้นหาหัวข้อที่สนใจได้ทันที', 'bossmaster-display' ) ),
					array( 'url' => bmd_page_url_by_template( 'template-categories.php', '/categories/' ), 'title' => __( 'คอลเลกชันพิเศษ', 'bossmaster-display' ), 'copy' => __( 'รวมหมวดหมู่และรายการคัดสรร', 'bossmaster-display' ) ),
				); foreach ( array_slice( $explore_items, 0, $explore_count ) as $index => $item ) : ?>
					<a href="<?php echo esc_url( $item['url'] ); ?>"><span><?php echo esc_html( str_pad( (string) ( $index + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span><h3><?php echo esc_html( $item['title'] ); ?></h3><p><?php echo esc_html( $item['copy'] ); ?></p><strong><?php esc_html_e( 'เปิดดู', 'bossmaster-display' ); ?> →</strong></a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>
<?php endif; ?>

<?php get_footer(); ?>
