<?php
/**
 * Footer for BOSSMASTER Display.
 *
 * @package BOSSMASTER_Display
 */

defined( 'ABSPATH' ) || exit;
$show_footer_menu = (bool) get_theme_mod( 'bmd_footer_show_menu', true );
$show_back_top   = (bool) get_theme_mod( 'bmd_footer_show_back_top', true );
$can_customize   = current_user_can( 'edit_theme_options' ) && get_theme_mod( 'bmd_show_admin_floating', true );
$color_preset    = bmd_sanitize_choice( get_theme_mod( 'bmd_color_preset', 'neon' ), array( 'neon', 'pastel', 'vanilla' ), 'neon' );
$card_ratio      = bmd_sanitize_choice( get_theme_mod( 'bmd_card_ratio', 'landscape' ), array( 'portrait', 'landscape' ), 'landscape' );
$density         = bmd_sanitize_choice( get_theme_mod( 'bmd_density', 'compact' ), array( 'standard', 'compact' ), 'compact' );
?>
	</main>

	<footer class="bmd-footer">
		<div class="bmd-container bmd-footer-inner">
			<div class="bmd-footer-brand">
				<a class="bmd-brand-fallback" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<span class="bmd-brand-mark">B</span>
					<span class="bmd-brand-copy"><strong>BOSSMASTER</strong><small>ENTERTAINMENT</small></span>
				</a>
			</div>
			<?php if ( $show_footer_menu ) : ?>
			<div class="bmd-footer-menu">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'wpst-footer-menu',
						'container'      => false,
						'menu_class'     => 'bmd-footer-links',
						'fallback_cb'    => false,
						'depth'          => 1,
					)
				);
				?>
			</div>
			<?php endif; ?>
			<div class="bmd-copyright">
				<div class="bmd-copyright-content"><?php echo bmd_get_copyright_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
				<?php $footer_note = trim( (string) get_theme_mod( 'bmd_footer_note', 'ชุดแสดงผลใหม่ควบคุมโดย WP MY BOSS' ) ); ?>
				<?php if ( $footer_note ) : ?><small><?php echo esc_html( $footer_note ); ?></small><?php endif; ?>
			</div>
			<?php if ( $show_back_top ) : ?><a class="bmd-back-top" href="#page"><?php esc_html_e( 'กลับด้านบน', 'bossmaster-display' ); ?> ↑</a><?php endif; ?>
		</div>
	</footer>

	<?php if ( $can_customize ) : ?>
		<button class="bmd-admin-float" type="button" data-bmd-settings-open aria-controls="bmd-quick-settings" aria-expanded="false"><span aria-hidden="true">⚙</span> <?php esc_html_e( 'ตั้งค่าการแสดงผล', 'bossmaster-display' ); ?></button>
		<div class="bmd-settings-backdrop" data-bmd-settings-backdrop hidden></div>
		<aside class="bmd-settings-panel" id="bmd-quick-settings" aria-label="<?php esc_attr_e( 'ชุดแสดงผลใหม่', 'bossmaster-display' ); ?>" aria-hidden="true">
			<div class="bmd-settings-panel-head">
				<div><p><?php esc_html_e( 'WP MY BOSS', 'bossmaster-display' ); ?></p><h2><?php esc_html_e( 'ชุดแสดงผลใหม่', 'bossmaster-display' ); ?></h2></div>
				<button type="button" data-bmd-settings-close aria-label="<?php esc_attr_e( 'ปิด', 'bossmaster-display' ); ?>">×</button>
			</div>
			<p class="bmd-settings-intro"><?php esc_html_e( 'ทดลองเลือกสีและรูปแบบก่อนบันทึก ค่าที่บันทึกจะใช้กับเว็บไซต์จริงทันที', 'bossmaster-display' ); ?></p>
			<form class="bmd-settings-form" data-bmd-settings-form>
				<fieldset>
					<legend><?php esc_html_e( 'ชุดสีเว็บไซต์', 'bossmaster-display' ); ?></legend>
					<div class="bmd-preset-options">
						<label class="bmd-preset-card"><input type="radio" name="color_preset" value="neon" <?php checked( $color_preset, 'neon' ); ?>><span class="is-neon"></span><b>Neon Night</b></label>
						<label class="bmd-preset-card"><input type="radio" name="color_preset" value="pastel" <?php checked( $color_preset, 'pastel' ); ?>><span class="is-pastel"></span><b>Pastel Dream</b></label>
						<label class="bmd-preset-card"><input type="radio" name="color_preset" value="vanilla" <?php checked( $color_preset, 'vanilla' ); ?>><span class="is-vanilla"></span><b>Warm Vanilla</b></label>
					</div>
				</fieldset>
				<fieldset>
					<legend><?php esc_html_e( 'รูปแบบการ์ด', 'bossmaster-display' ); ?></legend>
					<div class="bmd-segmented">
						<label><input type="radio" name="card_ratio" value="landscape" <?php checked( $card_ratio, 'landscape' ); ?>><span><?php esc_html_e( 'แนวกว้าง 16:9', 'bossmaster-display' ); ?></span></label>
						<label><input type="radio" name="card_ratio" value="portrait" <?php checked( $card_ratio, 'portrait' ); ?>><span><?php esc_html_e( 'แนวตั้ง 2:3', 'bossmaster-display' ); ?></span></label>
					</div>
				</fieldset>
				<fieldset>
					<legend><?php esc_html_e( 'ความหนาแน่น', 'bossmaster-display' ); ?></legend>
					<div class="bmd-segmented">
						<label><input type="radio" name="density" value="standard" <?php checked( $density, 'standard' ); ?>><span><?php esc_html_e( 'มาตรฐาน', 'bossmaster-display' ); ?></span></label>
						<label><input type="radio" name="density" value="compact" <?php checked( $density, 'compact' ); ?>><span><?php esc_html_e( 'กระชับ', 'bossmaster-display' ); ?></span></label>
					</div>
				</fieldset>
				<fieldset>
					<legend><?php esc_html_e( 'หัวข้อ', 'bossmaster-display' ); ?></legend>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'แสดงปุ่มสลับโหมด', 'bossmaster-display' ); ?></span><input type="checkbox" name="show_header_theme_toggle" value="1" <?php checked( get_theme_mod( 'bmd_header_show_theme_toggle', true ) ); ?>></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'แสดงปุ่มค้นหา', 'bossmaster-display' ); ?></span><input type="checkbox" name="show_header_search" value="1" <?php checked( get_theme_mod( 'bmd_header_show_search', true ) ); ?>></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'แสดงปุ่มภาษา', 'bossmaster-display' ); ?></span><input type="checkbox" name="show_header_language" value="1" <?php checked( get_theme_mod( 'bmd_header_show_language', true ) ); ?>></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'หัวข้อแบบติดด้านบน', 'bossmaster-display' ); ?></span><input type="checkbox" name="header_sticky" value="1" <?php checked( get_theme_mod( 'bmd_header_sticky', true ) ); ?>></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'ความสูงหัวข้อ (พิกเซล)', 'bossmaster-display' ); ?></span><input type="number" name="header_height" min="56" max="112" step="2" value="<?php echo esc_attr( max( 56, min( 112, absint( get_theme_mod( 'bmd_header_height', 76 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'Breakpoint เมนูมือถือ (พิกเซล)', 'bossmaster-display' ); ?></span><input type="number" name="header_breakpoint" min="640" max="1400" step="10" value="<?php echo esc_attr( max( 640, min( 1400, absint( get_theme_mod( 'bmd_header_breakpoint', 900 ) ) ) ) ); ?>"></label>
				</fieldset>
				<fieldset>
					<legend><?php esc_html_e( 'แถบบน', 'bossmaster-display' ); ?></legend>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'แสดงแถบบน', 'bossmaster-display' ); ?></span><input type="checkbox" name="show_top_notice" value="1" <?php checked( get_theme_mod( 'bmd_show_top_notice', true ) ); ?>></label>
				</fieldset>
				<fieldset>
					<legend><?php esc_html_e( 'สุ่มจากรายการล่าสุด', 'bossmaster-display' ); ?></legend>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'ใช้รูปแบบภาพตามธรรมชาติ', 'bossmaster-display' ); ?></span><input type="checkbox" name="random_latest_natural" value="1" <?php checked( get_theme_mod( 'bmd_random_latest_natural', true ) ); ?>></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'จำนวนการ์ดสุ่ม', 'bossmaster-display' ); ?></span><input type="number" name="random_latest_count" min="6" max="24" step="1" value="<?php echo esc_attr( max( 6, min( 24, absint( get_theme_mod( 'bmd_random_latest_count', 15 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'คอลัมน์ (เดสก์ทอป)', 'bossmaster-display' ); ?></span><input type="number" name="random_latest_columns_desktop" min="3" max="6" step="1" value="<?php echo esc_attr( max( 3, min( 6, absint( get_theme_mod( 'bmd_random_latest_columns_desktop', 5 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'คอลัมน์ (แท็บเล็ต)', 'bossmaster-display' ); ?></span><input type="number" name="random_latest_columns_tablet" min="2" max="5" step="1" value="<?php echo esc_attr( max( 2, min( 5, absint( get_theme_mod( 'bmd_random_latest_columns_tablet', 4 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'คอลัมน์ (มือถือ)', 'bossmaster-display' ); ?></span><input type="number" name="random_latest_columns_mobile" min="1" max="4" step="1" value="<?php echo esc_attr( max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_columns_mobile', 3 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'แถว (เดสก์ทอป)', 'bossmaster-display' ); ?></span><input type="number" name="random_latest_rows_desktop" min="1" max="4" step="1" value="<?php echo esc_attr( max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_rows_desktop', 2 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'แถว (แท็บเล็ต)', 'bossmaster-display' ); ?></span><input type="number" name="random_latest_rows_tablet" min="1" max="4" step="1" value="<?php echo esc_attr( max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_rows_tablet', 2 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'แถว (มือถือ)', 'bossmaster-display' ); ?></span><input type="number" name="random_latest_rows_mobile" min="1" max="4" step="1" value="<?php echo esc_attr( max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_rows_mobile', 2 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'ระยะห่างการ์ด (px)', 'bossmaster-display' ); ?></span><input type="number" name="random_latest_gap" min="8" max="40" step="1" value="<?php echo esc_attr( max( 8, min( 40, absint( get_theme_mod( 'bmd_random_latest_gap', 16 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'หัวข้อ', 'bossmaster-display' ); ?></span><input type="text" name="random_latest_title" value="<?php echo esc_attr( trim( (string) get_theme_mod( 'bmd_random_latest_title', __( 'สุ่มจากรายการล่าสุด', 'bossmaster-display' ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'ข้อความปุ่มดูทั้งหมด', 'bossmaster-display' ); ?></span><input type="text" name="random_latest_view_all" value="<?php echo esc_attr( trim( (string) get_theme_mod( 'bmd_random_latest_view_all', __( 'ดูทั้งหมด', 'bossmaster-display' ) ) ) ); ?>"></label>
				</fieldset>

				<fieldset>
					<legend><?php esc_html_e( 'เลือกดูตามความสนใจ (Explore)', 'bossmaster-display' ); ?></legend>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'จำนวนปุ่ม', 'bossmaster-display' ); ?></span><input type="number" name="explore_count" min="3" max="6" step="1" value="<?php echo esc_attr( max( 3, min( 6, absint( get_theme_mod( 'bmd_explore_count', 5 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'คอลัมน์ (เดสก์ทอป)', 'bossmaster-display' ); ?></span><input type="number" name="explore_columns_desktop" min="2" max="4" step="1" value="<?php echo esc_attr( max( 2, min( 4, absint( get_theme_mod( 'bmd_explore_columns_desktop', 3 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'คอลัมน์ (แท็บเล็ต)', 'bossmaster-display' ); ?></span><input type="number" name="explore_columns_tablet" min="1" max="3" step="1" value="<?php echo esc_attr( max( 1, min( 3, absint( get_theme_mod( 'bmd_explore_columns_tablet', 2 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'คอลัมน์ (มือถือ)', 'bossmaster-display' ); ?></span><input type="number" name="explore_columns_mobile" min="1" max="2" step="1" value="<?php echo esc_attr( max( 1, min( 2, absint( get_theme_mod( 'bmd_explore_columns_mobile', 1 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'ระยะห่าง (px)', 'bossmaster-display' ); ?></span><input type="number" name="explore_gap" min="8" max="32" step="1" value="<?php echo esc_attr( max( 8, min( 32, absint( get_theme_mod( 'bmd_explore_gap', 12 ) ) ) ) ); ?>"></label>
				</fieldset>

				<fieldset>
					<legend><?php esc_html_e( 'การตั้งค่า Gallery (Single page preview only)', 'bossmaster-display' ); ?></legend>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'แสดง Gallery', 'bossmaster-display' ); ?></span><input type="checkbox" name="single_show_gallery" value="1" <?php checked( get_theme_mod( 'bmd_single_show_gallery', true ) ); ?>></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'คอลัมน์ Gallery (เดสก์ทอป)', 'bossmaster-display' ); ?></span><input type="number" name="single_gallery_columns" min="2" max="6" step="1" value="<?php echo esc_attr( max( 2, min( 6, absint( get_theme_mod( 'bmd_single_gallery_columns', 4 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'คอลัมน์ Gallery (แท็บเล็ต)', 'bossmaster-display' ); ?></span><input type="number" name="single_gallery_columns_tablet" min="2" max="4" step="1" value="<?php echo esc_attr( max( 2, min( 4, absint( get_theme_mod( 'bmd_single_gallery_columns_tablet', 3 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'คอลัมน์ Gallery (มือถือ)', 'bossmaster-display' ); ?></span><input type="number" name="single_gallery_columns_mobile" min="1" max="3" step="1" value="<?php echo esc_attr( max( 1, min( 3, absint( get_theme_mod( 'bmd_single_gallery_columns_mobile', 2 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'แถว Gallery', 'bossmaster-display' ); ?></span><input type="number" name="single_gallery_rows" min="1" max="6" step="1" value="<?php echo esc_attr( max( 1, min( 6, absint( get_theme_mod( 'bmd_single_gallery_rows', 2 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'จำนวนภาพสูงสุด', 'bossmaster-display' ); ?></span><input type="number" name="single_gallery_max_items" min="1" max="24" step="1" value="<?php echo esc_attr( max( 1, min( 24, absint( get_theme_mod( 'bmd_single_gallery_max_items', 8 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'ระยะห่าง Gallery (px)', 'bossmaster-display' ); ?></span><input type="number" name="single_gallery_gap" min="6" max="32" step="1" value="<?php echo esc_attr( max( 6, min( 32, absint( get_theme_mod( 'bmd_single_gallery_gap', 10 ) ) ) ) ); ?>"></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'อัตราส่วนภาพ Gallery', 'bossmaster-display' ); ?></span>
						<select name="single_gallery_ratio">
							<option value="landscape" <?php selected( get_theme_mod( 'bmd_single_gallery_ratio', 'landscape' ), 'landscape' ); ?>><?php esc_html_e( 'แนวกว้าง 16:9', 'bossmaster-display' ); ?></option>
							<option value="portrait" <?php selected( get_theme_mod( 'bmd_single_gallery_ratio', 'landscape' ), 'portrait' ); ?>><?php esc_html_e( 'แนวตั้ง 2:3', 'bossmaster-display' ); ?></option>
						</select>
					</label>
				</fieldset>
				<fieldset>
					<legend><?php esc_html_e( 'บล็อกหน้าแรก', 'bossmaster-display' ); ?></legend>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'Hero ด้านบน', 'bossmaster-display' ); ?></span><input type="checkbox" name="show_hero" value="1" <?php checked( get_theme_mod( 'bmd_show_hero', true ) ); ?>></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'เรื่องเด่นแนะนำ', 'bossmaster-display' ); ?></span><input type="checkbox" name="show_featured" value="1" <?php checked( get_theme_mod( 'bmd_show_featured', true ) ); ?>></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'วิดีโอล่าสุด', 'bossmaster-display' ); ?></span><input type="checkbox" name="show_latest" value="1" <?php checked( get_theme_mod( 'bmd_show_latest', true ) ); ?>></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'สุ่มจากรายการล่าสุด', 'bossmaster-display' ); ?></span><input type="checkbox" name="show_random_latest" value="1" <?php checked( get_theme_mod( 'bmd_show_random_latest', true ) ); ?>></label>
					<label class="bmd-switch-row"><span><?php esc_html_e( 'เลือกดูตามความสนใจ', 'bossmaster-display' ); ?></span><input type="checkbox" name="show_directories" value="1" <?php checked( get_theme_mod( 'bmd_show_directories', true ) ); ?>></label>
				</fieldset>
				<div class="bmd-settings-actions">
					<button type="submit" class="bmd-settings-save"><?php esc_html_e( 'บันทึก WordPress จริง', 'bossmaster-display' ); ?></button>
					<a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=bmd_display_panel' ) ); ?>"><?php esc_html_e( 'เปิดหน้าปรับแต่งแบบเต็ม', 'bossmaster-display' ); ?> →</a>
				</div>
				<p class="bmd-settings-status" data-bmd-settings-status aria-live="polite"></p>
			</form>
		</aside>
	<?php endif; ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
