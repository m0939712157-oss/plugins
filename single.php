<?php
/**
 * Single video/post page.
 *
 * @package BOSSMASTER_Display
 */

defined( 'ABSPATH' ) || exit;
get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$post_id       = get_the_ID();
		$poster        = bmd_get_poster( $post_id, 'large' );
		$code          = bmd_get_code( $post_id );
		$duration      = bmd_get_duration( $post_id );
		$quality       = strtoupper( trim( (string) bmd_first_meta( $post_id, array( '_acs_quality', 'quality', 'video_quality', '_wpmb_quality' ), 'HD' ) ) );
		$actor         = bmd_first_term( $post_id, 'actors' );
		$studio        = bmd_first_term( $post_id, 'studio' );
		$category      = bmd_first_term( $post_id, 'category' );
		$category_name = $category ? $category->name : __( 'ไม่มีหมวดหมู่', 'bossmaster-display' );
		$category_url  = $category ? bmd_term_url( $category, home_url( '/' ) ) : '';
		$show_category  = (bool) get_theme_mod( 'bmd_single_show_category', true );
		$category_label = trim( (string) get_theme_mod( 'bmd_single_category_label', __( 'หมวดหมู่', 'bossmaster-display' ) ) );
		$show_tags      = (bool) get_theme_mod( 'bmd_single_show_other_terms', true );
		$show_actions   = (bool) get_theme_mod( 'bmd_single_show_actions', true );
		$status_label   = trim( (string) get_theme_mod( 'bmd_single_status_label', __( 'อัปเดตล่าสุด', 'bossmaster-display' ) ) );
		$gallery        = bmd_get_gallery_images( $post_id, 12 );
		$related        = bmd_related_query( $post_id, 4 );
		$post_tags      = wp_get_post_terms( $post_id, 'post_tag' );
		$poster_orientation = bmd_get_card_orientation( 'single-poster' );
		$show_gallery   = (bool) get_theme_mod( 'bmd_single_show_gallery', true );
		$gallery_columns = max( 2, min( 6, absint( get_theme_mod( 'bmd_single_gallery_columns', 4 ) ) ) );
		$gallery_columns_tablet = max( 2, min( 4, absint( get_theme_mod( 'bmd_single_gallery_columns_tablet', 3 ) ) ) );
		$gallery_columns_mobile = max( 1, min( 3, absint( get_theme_mod( 'bmd_single_gallery_columns_mobile', 2 ) ) ) );
		$gallery_rows = max( 1, min( 6, absint( get_theme_mod( 'bmd_single_gallery_rows', 2 ) ) ) );
		$gallery_max_items = max( 1, min( 24, absint( get_theme_mod( 'bmd_single_gallery_max_items', 8 ) ) ) );
		$gallery_gap = max( 6, min( 32, absint( get_theme_mod( 'bmd_single_gallery_gap', 10 ) ) ) );
		$gallery_ratio = bmd_sanitize_choice( get_theme_mod( 'bmd_single_gallery_ratio', 'landscape' ), array( 'landscape', 'portrait' ), 'landscape' );
		$gallery_limit = min( $gallery_max_items, max( 1, $gallery_columns * $gallery_rows ) );
		$gallery = array_slice( $gallery, 0, $gallery_limit );
		$meta_layout = bmd_sanitize_choice( get_theme_mod( 'bmd_single_meta_layout', 'stacked' ), array( 'stacked', 'inline' ), 'stacked' );
		$ads            = array(
			'top'            => class_exists( 'AVFP_Ads' ) ? AVFP_Ads::html( 'ads_single_static_top' ) : '',
			'under'          => class_exists( 'AVFP_Ads' ) ? AVFP_Ads::html( 'ads_single_video_page_under_player' ) : '',
			'before_related' => class_exists( 'AVFP_Ads' ) ? AVFP_Ads::html( 'ads_single_video_page_before_related_videos' ) : '',
		);
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'bmd-single bmd-detail-view' ); ?>>
			<section class="bmd-single-player-section">
				<div class="bmd-container bmd-detail-width">
					<nav class="bmd-breadcrumbs" aria-label="<?php esc_attr_e( 'เส้นทางหน้า', 'bossmaster-display' ); ?>">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'หน้าแรก', 'bossmaster-display' ); ?></a>
						<span aria-hidden="true">›</span>
						<span><?php echo esc_html( $code ); ?></span>
					</nav>
					<?php if ( trim( $ads['top'] ) ) : ?><div class="bmd-ad-zone"><?php echo $ads['top']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
					<div class="bmd-player-shell">
						<?php get_template_part( 'loop-templates/content', 'video-player' ); ?>
					</div>
					<?php if ( trim( $ads['under'] ) ) : ?><div class="bmd-ad-zone"><?php echo $ads['under']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div><?php endif; ?>
				</div>
			</section>

			<section class="bmd-single-info-section">
				<div class="bmd-container bmd-detail-width bmd-single-info-grid">
					<div class="bmd-single-copy bmd-single-copy--<?php echo esc_attr( $meta_layout ); ?>">
						<p class="bmd-eyebrow"><?php echo esc_html( sprintf( 'NEW · %s', $code ) ); ?></p>
						<h1 class="bmd-single-title"><?php the_title(); ?></h1>
						<div class="bmd-single-meta" aria-label="<?php esc_attr_e( 'ข้อมูลวิดีโอ', 'bossmaster-display' ); ?>">
							<span>
								<span class="bmd-meta-icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
										<path d="M2.75 12c0-5.1 3.6-8.75 9.25-8.75S21.25 6.9 21.25 12 17.65 20.75 12 20.75 2.75 17.1 2.75 12Z" />
										<circle cx="12" cy="12" r="3.2" />
									</svg>
								</span>
								<span class="bmd-meta-text"><?php echo esc_html( bmd_get_views( $post_id ) ); ?> <?php esc_html_e( 'ครั้ง', 'bossmaster-display' ); ?></span>
							</span>
							<?php if ( $duration ) : ?><span>
								<span class="bmd-meta-icon" aria-hidden="true">
									<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
										<circle cx="12" cy="12" r="8.5" />
										<path d="M12 7.5v5.2l3.1 2.3" />
									</svg>
								</span>
								<span class="bmd-meta-text"><?php echo esc_html( $duration ); ?></span>
							</span><?php endif; ?>
							<?php if ( $quality ) : ?><span><?php echo esc_html( $quality ); ?></span><?php endif; ?>
							<?php if ( $status_label ) : ?><span><?php echo esc_html( $status_label ); ?></span><?php endif; ?>
						</div>

						<?php if ( $show_actions ) : ?>
							<div class="bmd-detail-actions">
								<button type="button" class="bmd-detail-action" data-bmd-like data-post-id="<?php echo esc_attr( $post_id ); ?>">
									<span class="bmd-action-icon" aria-hidden="true">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
											<path d="M12 20s-6.2-4.2-8.4-8.2A5.2 5.2 0 0 1 12 5.2a5.2 5.2 0 0 1 8.4 6.6C18.2 15.8 12 20 12 20Z" />
										</svg>
									</span>
									<span class="bmd-action-label"><?php esc_html_e( 'ถูกใจ', 'bossmaster-display' ); ?></span>
								</button>
								<button type="button" class="bmd-detail-action" data-bmd-save data-post-id="<?php echo esc_attr( $post_id ); ?>">
									<span class="bmd-action-icon" aria-hidden="true">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
											<path d="M6.5 4.5A1.5 1.5 0 0 1 8 3h8a1.5 1.5 0 0 1 1.5 1.5v15l-5.5-3-5.5 3Z" />
										</svg>
									</span>
									<span class="bmd-action-label"><?php esc_html_e( 'บันทึก', 'bossmaster-display' ); ?></span>
								</button>
								<button type="button" class="bmd-copy-link" data-bmd-copy-link>
									<span class="bmd-action-icon" aria-hidden="true">
										<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
											<path d="M9.5 8.5H8a3.5 3.5 0 0 0 0 7h1.5" />
											<path d="M14.5 15.5H16a3.5 3.5 0 0 0 0-7h-1.5" />
											<path d="M9.2 12h5.6" />
										</svg>
									</span>
									<span class="bmd-action-label"><?php esc_html_e( 'คัดลอกลิงก์', 'bossmaster-display' ); ?></span>
								</button>
							</div>
						<?php endif; ?>

						<div class="bmd-single-content">
							<?php
							// Tell AV Framework PRO that this template renders its own labels.
							do_action( 'avfp_single_video_template_ready', $post_id );
							$content = (string) get_the_content();
							$content = preg_replace( '/\[video[^\]]*\].*?\[\/video\]|\[video[^\]]*\]/is', '', $content );
							$content = preg_replace( '/\[gallery[^\]]*\]/is', '', $content );
							$content = preg_replace( '/<iframe\b[^>]*>.*?<\/iframe>/is', '', $content );
							$content = preg_replace( '/<video\b[^>]*>.*?<\/video>/is', '', $content );
							$content = apply_filters( 'the_content', $content );
							$content = bmd_strip_legacy_gallery_blocks( $content );
							$content = bmd_strip_legacy_taxonomy_blocks( $content );
							echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?>
						</div>

						<?php if ( $show_tags && ! is_wp_error( $post_tags ) && $post_tags ) : ?>
							<div class="bmd-term-chips" data-bmd-single-tags aria-label="<?php esc_attr_e( 'แท็ก', 'bossmaster-display' ); ?>">
								<?php foreach ( $post_tags as $tag ) : ?>
									<?php $tag_url = get_term_link( $tag ); ?>
									<?php $tag_display_name = ltrim( $tag->name, "# \t\n\r\0\x0B" ); ?><?php if ( ! is_wp_error( $tag_url ) ) : ?><a href="<?php echo esc_url( $tag_url ); ?>"><?php echo esc_html( $tag_display_name ); ?></a><?php endif; ?>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>

					<aside class="bmd-single-poster-card" data-bmd-single-poster>
						<div class="bmd-single-poster<?php echo $poster ? '' : ' is-placeholder'; ?>" data-bmd-card-orientation="<?php echo esc_attr( $poster_orientation ); ?>">
							<?php if ( $poster ) : ?><img src="<?php echo esc_url( $poster ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="eager" decoding="async"><?php else : ?><span><?php echo esc_html( $code ); ?></span><?php endif; ?>
							<strong><?php echo esc_html( $code ); ?></strong>
						</div>
						<dl class="bmd-single-metadata" data-bmd-single-metadata>
							<?php if ( $show_category ) : ?><div data-bmd-meta-category><dt><?php echo esc_html( $category_label ?: __( 'หมวดหมู่', 'bossmaster-display' ) ); ?></dt><dd><?php if ( $category_url ) : ?><a href="<?php echo esc_url( $category_url ); ?>"><?php echo esc_html( $category_name ); ?></a><?php else : ?><span><?php echo esc_html( $category_name ); ?></span><?php endif; ?></dd></div><?php endif; ?>
							<?php if ( $actor ) : ?><div data-bmd-meta-actors><dt><?php esc_html_e( 'นักแสดง', 'bossmaster-display' ); ?></dt><dd><a href="<?php echo esc_url( bmd_term_url( $actor, home_url( '/' ) ) ); ?>"><?php echo esc_html( $actor->name ); ?></a></dd></div><?php endif; ?>
							<?php if ( $studio ) : ?><div data-bmd-meta-studio><dt><?php esc_html_e( 'ค่าย', 'bossmaster-display' ); ?></dt><dd><a href="<?php echo esc_url( bmd_term_url( $studio, home_url( '/' ) ) ); ?>"><?php echo esc_html( $studio->name ); ?></a></dd></div><?php endif; ?>
							<div data-bmd-meta-published><dt><?php esc_html_e( 'เผยแพร่', 'bossmaster-display' ); ?></dt><dd><?php echo esc_html( get_the_date() ); ?></dd></div>
						</dl>
					</aside>
				</div>
			</section>

			<?php if ( $show_gallery && $gallery ) : ?>
			<section class="bmd-section bmd-gallery-section">
				<div class="bmd-container bmd-detail-width">
					<div class="bmd-section-heading"><div><p class="bmd-eyebrow"><?php esc_html_e( 'GALLERY', 'bossmaster-display' ); ?></p><h2><?php esc_html_e( 'ภาพตัวอย่าง', 'bossmaster-display' ); ?></h2></div></div>
					<div class="bmd-gallery-grid" data-bmd-single-gallery style="--bmd-single-gallery-cols:<?php echo esc_attr( $gallery_columns ); ?>;--bmd-single-gallery-cols-tablet:<?php echo esc_attr( $gallery_columns_tablet ); ?>;--bmd-single-gallery-cols-mobile:<?php echo esc_attr( $gallery_columns_mobile ); ?>;--bmd-single-gallery-gap:<?php echo esc_attr( $gallery_gap ); ?>px;--bmd-single-gallery-rows:<?php echo esc_attr( $gallery_rows ); ?>;--bmd-single-gallery-ratio:<?php echo esc_attr( 'portrait' === $gallery_ratio ? '2/3' : '16/9' ); ?>;">
						<?php foreach ( $gallery as $index => $image ) : ?>
							<a href="<?php echo esc_url( $image['url'] ); ?>" class="bmd-gallery-item" data-bmd-gallery-item data-bmd-card-orientation="<?php echo esc_attr( bmd_get_card_orientation( 'single-gallery' ) ); ?>" data-bmd-lightbox>
								<img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( sprintf( __( 'ภาพ %d', 'bossmaster-display' ), $index + 1 ) ); ?>" loading="lazy" decoding="async">
								<span><?php echo esc_html( sprintf( __( 'ภาพ %d', 'bossmaster-display' ), $index + 1 ) ); ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</section>
			<?php endif; ?>

			<?php if ( trim( $ads['before_related'] ) ) : ?><div class="bmd-container bmd-detail-width"><div class="bmd-ad-zone"><?php echo $ads['before_related']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div></div><?php endif; ?>

			<?php if ( $related->have_posts() ) : ?>
			<section class="bmd-section bmd-related-section">
				<div class="bmd-container bmd-detail-width">
					<div class="bmd-section-heading"><div><p class="bmd-eyebrow"><?php esc_html_e( 'YOU MAY ALSO LIKE', 'bossmaster-display' ); ?></p><h2><?php esc_html_e( 'เรื่องที่เกี่ยวข้อง', 'bossmaster-display' ); ?></h2></div><a href="<?php echo esc_url( $category ? bmd_term_url( $category, home_url( '/' ) ) : home_url( '/' ) ); ?>">⭐ <?php esc_html_e( 'ดูทั้งหมด', 'bossmaster-display' ); ?> →</a></div>
					<div class="bmd-video-grid bmd-related-grid" data-bmd-section="related"><?php while ( $related->have_posts() ) : $related->the_post(); bmd_render_video_card( get_the_ID(), array( 'orientation' => 'natural' ) ); endwhile; wp_reset_postdata(); ?></div>
				</div>
			</section>
			<?php endif; ?>
		</article>
		<?php
	endwhile;
endif;
get_footer();
