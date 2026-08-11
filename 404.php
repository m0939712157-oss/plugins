<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="bmd-section bmd-404-section"><div class="bmd-container"><div class="bmd-empty-state bmd-empty-state--404"><span class="bmd-page-number is-static">404</span><h1><?php esc_html_e( 'ไม่พบหน้าที่ต้องการ', 'bossmaster-display' ); ?></h1><p><?php esc_html_e( 'ลิงก์อาจถูกเปลี่ยนหรือรายการนี้ไม่มีอยู่แล้ว', 'bossmaster-display' ); ?></p><a class="bmd-button is-primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'กลับหน้าแรก', 'bossmaster-display' ); ?> →</a></div></div></section>
<?php get_footer(); ?>
