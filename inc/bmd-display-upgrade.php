<?php
/**
 * BOSSMASTER Display 1.1 upgrade helpers.
 *
 * @package BOSSMASTER_Display
 */

defined( 'ABSPATH' ) || exit;

/**
 * Sanitize a value against a fixed list.
 *
 * @param mixed  $value   Submitted value.
 * @param array  $allowed Allowed values.
 * @param string $default Default value.
 * @return string
 */
function bmd_sanitize_choice( $value, array $allowed, $default ) {
	$value = sanitize_key( (string) $value );
	return in_array( $value, $allowed, true ) ? $value : $default;
}

/**
 * Add visual state classes used by the live settings panel.
 *
 * @param array $classes Body classes.
 * @return array
 */
function bmd_upgrade_body_classes( $classes ) {
	$classes[] = 'bmd-preset-' . bmd_sanitize_choice( get_theme_mod( 'bmd_color_preset', 'neon' ), array( 'neon', 'pastel', 'vanilla' ), 'neon' );
	$classes[] = 'bmd-cards-' . bmd_sanitize_choice( get_theme_mod( 'bmd_card_ratio', 'landscape' ), array( 'portrait', 'landscape' ), 'landscape' );
	$classes[] = 'bmd-density-' . bmd_sanitize_choice( get_theme_mod( 'bmd_density', 'compact' ), array( 'standard', 'compact' ), 'compact' );
	return array_values( array_unique( $classes ) );
}
add_filter( 'body_class', 'bmd_upgrade_body_classes', 40 );

/**
 * Add the restored Copyright editor and the missing display controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 * @return void
 */
function bmd_upgrade_customize_register( $wp_customize ) {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	// Keep Copyright as a clearly visible top-level menu, matching the old theme.
	if ( ! $wp_customize->get_section( 'wpst_copyright' ) ) {
		$wp_customize->add_section(
			'wpst_copyright',
			array(
				'title'       => __( 'Copyright', 'bossmaster-display' ),
				'description' => __( 'แก้ข้อความลิขสิทธิ์และข้อความท้ายเว็บไซต์ รองรับลิงก์และตัวหนา', 'bossmaster-display' ),
				'priority'    => 55,
			)
		);
	} else {
		$section              = $wp_customize->get_section( 'wpst_copyright' );
		$section->title       = __( 'Copyright', 'bossmaster-display' );
		$section->description = __( 'แก้ข้อความลิขสิทธิ์และข้อความท้ายเว็บไซต์ รองรับลิงก์และตัวหนา', 'bossmaster-display' );
		$section->priority    = 55;
	}

	if ( ! $wp_customize->get_setting( 'copyright_content' ) ) {
		$wp_customize->add_setting(
			'copyright_content',
			array(
				'default'           => '© ' . wp_date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. All rights reserved.',
				'transport'         => 'refresh',
				'sanitize_callback' => 'wp_kses_post',
			)
		);
	}

	if ( ! $wp_customize->get_control( 'wpst_copyright_content' ) ) {
		if ( class_exists( 'Text_Editor_Custom_Control' ) ) {
			$wp_customize->add_control(
				new Text_Editor_Custom_Control(
					$wp_customize,
					'wpst_copyright_content',
					array(
						'label'    => __( 'Content', 'bossmaster-display' ),
						'section'  => 'wpst_copyright',
						'settings' => 'copyright_content',
						'type'     => 'textarea',
					)
				)
			);
		} else {
			$wp_customize->add_control(
				'wpst_copyright_content',
				array(
					'label'       => __( 'Content', 'bossmaster-display' ),
					'description' => __( 'ใส่ HTML พื้นฐาน เช่น <strong> และ <a> ได้', 'bossmaster-display' ),
					'section'     => 'wpst_copyright',
					'settings'    => 'copyright_content',
					'type'        => 'textarea',
				)
			);
		}
	}

	$footer_settings = array(
		'bmd_footer_note' => array(
			'default'  => __( 'ชุดแสดงผลใหม่ควบคุมโดย WP MY BOSS', 'bossmaster-display' ),
			'label'    => __( 'ข้อความบรรทัดเล็กใต้ Copyright', 'bossmaster-display' ),
			'type'     => 'text',
			'sanitize' => 'sanitize_text_field',
		),
		'bmd_footer_show_menu' => array(
			'default'  => true,
			'label'    => __( 'แสดงเมนูท้ายเว็บไซต์', 'bossmaster-display' ),
			'type'     => 'checkbox',
			'sanitize' => static function ( $value ) { return (bool) $value; },
		),
		'bmd_footer_show_back_top' => array(
			'default'  => true,
			'label'    => __( 'แสดงปุ่มกลับด้านบน', 'bossmaster-display' ),
			'type'     => 'checkbox',
			'sanitize' => static function ( $value ) { return (bool) $value; },
		),
	);
	foreach ( $footer_settings as $id => $args ) {
		if ( ! $wp_customize->get_setting( $id ) ) {
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => $args['default'],
					'transport'         => 'refresh',
					'sanitize_callback' => $args['sanitize'],
				)
			);
		}
		if ( ! $wp_customize->get_control( $id ) ) {
			$wp_customize->add_control(
				$id,
				array(
					'label'    => $args['label'],
					'section'  => 'wpst_copyright',
					'settings' => $id,
					'type'     => $args['type'],
				)
			);
		}
	}

	$extra_settings = array(
		'bmd_color_preset' => array(
			'section' => 'bmd_general', 'label' => __( 'ชุดสีเว็บไซต์', 'bossmaster-display' ), 'default' => 'neon', 'type' => 'select',
			'choices' => array( 'neon' => 'Neon Night', 'pastel' => 'Pastel Dream', 'vanilla' => 'Warm Vanilla' ),
			'sanitize' => static function ( $value ) { return bmd_sanitize_choice( $value, array( 'neon', 'pastel', 'vanilla' ), 'neon' ); },
		),
		'bmd_hero_highlight' => array(
			'section' => 'bmd_home', 'label' => __( 'ข้อความสีชมพูใน Hero', 'bossmaster-display' ), 'default' => 'ให้ค้นหาได้ง่ายกว่าเดิม', 'type' => 'text', 'sanitize' => 'sanitize_text_field',
		),
		'bmd_show_hero' => array(
			'section' => 'bmd_home', 'label' => __( 'แสดง Hero หน้าแรก', 'bossmaster-display' ), 'default' => true, 'type' => 'checkbox', 'sanitize' => static function ( $value ) { return (bool) $value; },
		),
		'bmd_show_featured' => array(
			'section' => 'bmd_home', 'label' => __( 'แสดงเรื่องเด่นแนะนำ', 'bossmaster-display' ), 'default' => true, 'type' => 'checkbox', 'sanitize' => static function ( $value ) { return (bool) $value; },
		),
		'bmd_show_latest' => array(
			'section' => 'bmd_home', 'label' => __( 'แสดงวิดีโอล่าสุด', 'bossmaster-display' ), 'default' => true, 'type' => 'checkbox', 'sanitize' => static function ( $value ) { return (bool) $value; },
		),
		'bmd_featured_autoplay' => array(
			'section' => 'bmd_home', 'label' => __( 'เลื่อนเรื่องเด่นอัตโนมัติ', 'bossmaster-display' ), 'default' => true, 'type' => 'checkbox', 'sanitize' => static function ( $value ) { return (bool) $value; },
		),
		'bmd_slider_interval' => array(
			'section' => 'bmd_home', 'label' => __( 'ความเร็วสไลด์ (มิลลิวินาที)', 'bossmaster-display' ), 'default' => 4000, 'type' => 'number', 'sanitize' => static function ( $value ) { return max( 1500, min( 30000, absint( $value ) ) ); }, 'input_attrs' => array( 'min' => 1500, 'max' => 30000, 'step' => 500 ),
		),
		'bmd_card_ratio' => array(
			'section' => 'bmd_cards', 'label' => __( 'รูปแบบการ์ด', 'bossmaster-display' ), 'default' => 'landscape', 'type' => 'select',
			'choices' => array( 'portrait' => __( 'แนวตั้ง 2:3', 'bossmaster-display' ), 'landscape' => __( 'แนวกว้าง 16:9', 'bossmaster-display' ) ),
			'sanitize' => static function ( $value ) { return bmd_sanitize_choice( $value, array( 'portrait', 'landscape' ), 'landscape' ); },
		),
		'bmd_density' => array(
			'section' => 'bmd_cards', 'label' => __( 'ความหนาแน่น', 'bossmaster-display' ), 'default' => 'compact', 'type' => 'select',
			'choices' => array( 'standard' => __( 'มาตรฐาน', 'bossmaster-display' ), 'compact' => __( 'กระชับ', 'bossmaster-display' ) ),
			'sanitize' => static function ( $value ) { return bmd_sanitize_choice( $value, array( 'standard', 'compact' ), 'compact' ); },
		),
	);

	foreach ( $extra_settings as $id => $args ) {
		if ( ! $wp_customize->get_setting( $id ) ) {
			$wp_customize->add_setting(
				$id,
				array(
					'default'           => $args['default'],
					'transport'         => 'refresh',
					'sanitize_callback' => $args['sanitize'],
				)
			);
		}
		if ( ! $wp_customize->get_control( $id ) ) {
			$control = array(
				'label'    => $args['label'],
				'section'  => $args['section'],
				'settings' => $id,
				'type'     => $args['type'],
			);
			if ( isset( $args['choices'] ) ) {
				$control['choices'] = $args['choices'];
			}
			if ( isset( $args['input_attrs'] ) ) {
				$control['input_attrs'] = $args['input_attrs'];
			}
			$wp_customize->add_control( $id, $control );
		}
	}
}
add_action( 'customize_register', 'bmd_upgrade_customize_register', 95 );

/**
 * Return the configured Copyright HTML.
 *
 * @return string
 */
function bmd_get_copyright_html() {
	$content = trim( (string) get_theme_mod( 'copyright_content', '' ) );
	if ( '' === $content || false !== stripos( $content, 'WP-Script.com' ) ) {
		$content = '© ' . wp_date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . '. All rights reserved.';
	}
	$content = str_replace(
		array( '[year]', '[site_name]', '[site_url]' ),
		array( wp_date( 'Y' ), get_bloginfo( 'name' ), home_url( '/' ) ),
		$content
	);
	return wp_kses_post( $content );
}

/**
 * Localize the admin-only live settings panel.
 *
 * @return void
 */
function bmd_localize_quick_settings() {
	if ( ! wp_script_is( 'bossmaster-display', 'enqueued' ) ) {
		return;
	}
	wp_localize_script(
		'bossmaster-display',
		'bmdQuickSettings',
		array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'bmd_quick_settings' ),
			'canSave'  => current_user_can( 'edit_theme_options' ),
			'saved'    => __( 'บันทึกแล้ว', 'bossmaster-display' ),
			'saving'   => __( 'กำลังบันทึก…', 'bossmaster-display' ),
			'error'    => __( 'บันทึกไม่สำเร็จ', 'bossmaster-display' ),
			'interval' => max( 1500, min( 30000, absint( get_theme_mod( 'bmd_slider_interval', 4000 ) ) ) ),
			'autoplay' => (bool) get_theme_mod( 'bmd_featured_autoplay', true ),
			'headerBreakpoint' => max( 640, min( 1400, absint( get_theme_mod( 'bmd_header_breakpoint', 900 ) ) ) ),
			// Gallery and random/latest defaults for quick-settings preview
			'singleGallery' => array(
				'cols' => max( 2, min( 6, absint( get_theme_mod( 'bmd_single_gallery_columns', 4 ) ) ) ),
				'cols_tablet' => max( 2, min( 4, absint( get_theme_mod( 'bmd_single_gallery_columns_tablet', 3 ) ) ) ),
				'cols_mobile' => max( 1, min( 3, absint( get_theme_mod( 'bmd_single_gallery_columns_mobile', 2 ) ) ) ),
				'rows' => max( 1, min( 6, absint( get_theme_mod( 'bmd_single_gallery_rows', 2 ) ) ) ),
				'gap' => max( 6, min( 32, absint( get_theme_mod( 'bmd_single_gallery_gap', 10 ) ) ) ),
				'ratio' => bmd_sanitize_choice( get_theme_mod( 'bmd_single_gallery_ratio', 'landscape' ), array( 'landscape', 'portrait' ), 'landscape' ),
			),
			'randomLatest' => array(
				'count' => max( 6, min( 24, absint( get_theme_mod( 'bmd_random_latest_count', 15 ) ) ) ),
				'cols_desktop' => max( 3, min( 6, absint( get_theme_mod( 'bmd_random_latest_columns_desktop', 5 ) ) ) ),
				'cols_tablet' => max( 2, min( 5, absint( get_theme_mod( 'bmd_random_latest_columns_tablet', 4 ) ) ) ),
				'cols_mobile' => max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_columns_mobile', 3 ) ) ) ),
				'rows_desktop' => max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_rows_desktop', 2 ) ) ) ),
				'rows_tablet' => max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_rows_tablet', 2 ) ) ) ),
				'rows_mobile' => max( 1, min( 4, absint( get_theme_mod( 'bmd_random_latest_rows_mobile', 2 ) ) ) ),
				'gap' => max( 8, min( 40, absint( get_theme_mod( 'bmd_random_latest_gap', 16 ) ) ) ),
				'natural' => (bool) get_theme_mod( 'bmd_random_latest_natural', true ),
				'title' => trim( (string) get_theme_mod( 'bmd_random_latest_title', __( 'สุ่มจากรายการล่าสุด', 'bossmaster-display' ) ) ),
				'view_all' => trim( (string) get_theme_mod( 'bmd_random_latest_view_all', __( 'ดูทั้งหมด', 'bossmaster-display' ) ) ),
			),
			'explore' => array(
				'count' => max( 3, min( 6, absint( get_theme_mod( 'bmd_explore_count', 5 ) ) ) ),
				'cols_desktop' => max( 2, min( 4, absint( get_theme_mod( 'bmd_explore_columns_desktop', 3 ) ) ) ),
				'cols_tablet' => max( 1, min( 3, absint( get_theme_mod( 'bmd_explore_columns_tablet', 2 ) ) ) ),
				'cols_mobile' => max( 1, min( 2, absint( get_theme_mod( 'bmd_explore_columns_mobile', 1 ) ) ) ),
				'gap' => max( 8, min( 32, absint( get_theme_mod( 'bmd_explore_gap', 12 ) ) ) ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'bmd_localize_quick_settings', 140 );

/**
 * Persist settings from the floating panel for administrators.
 *
 * @return void
 */
function bmd_save_quick_settings() {
	check_ajax_referer( 'bmd_quick_settings', 'nonce' );
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'ไม่มีสิทธิ์บันทึกการตั้งค่า', 'bossmaster-display' ) ), 403 );
	}

	$values = isset( $_POST['settings'] ) && is_array( $_POST['settings'] ) ? wp_unslash( $_POST['settings'] ) : array();
	$map = array(
		'color_preset'    => array( 'mod' => 'bmd_color_preset', 'type' => 'choice', 'allowed' => array( 'neon', 'pastel', 'vanilla' ), 'default' => 'neon' ),
			'nav_mode' => array( 'mod' => 'bmd_nav_mode', 'type' => 'choice', 'allowed' => array( 'auto', 'compact', 'stacked' ), 'default' => 'auto' ),
		'card_ratio'      => array( 'mod' => 'bmd_card_ratio', 'type' => 'choice', 'allowed' => array( 'portrait', 'landscape' ), 'default' => 'landscape' ),
		'density'         => array( 'mod' => 'bmd_density', 'type' => 'choice', 'allowed' => array( 'standard', 'compact' ), 'default' => 'compact' ),
		'show_header_theme_toggle' => array( 'mod' => 'bmd_header_show_theme_toggle', 'type' => 'bool' ),
		'show_header_search' => array( 'mod' => 'bmd_header_show_search', 'type' => 'bool' ),
		'show_header_language' => array( 'mod' => 'bmd_header_show_language', 'type' => 'bool' ),
		'header_sticky'   => array( 'mod' => 'bmd_header_sticky', 'type' => 'bool' ),
		'header_height'   => array( 'mod' => 'bmd_header_height', 'type' => 'number', 'default' => 76, 'min' => 56, 'max' => 112 ),
		'show_top_notice' => array( 'mod' => 'bmd_show_top_notice', 'type' => 'bool' ),
		'header_breakpoint' => array( 'mod' => 'bmd_header_breakpoint', 'type' => 'number', 'default' => 900, 'min' => 640, 'max' => 1400 ),
		'random_latest_natural' => array( 'mod' => 'bmd_random_latest_natural', 'type' => 'bool' ),
			'random_latest_count' => array( 'mod' => 'bmd_random_latest_count', 'type' => 'number', 'default' => 15, 'min' => 6, 'max' => 24 ),
			'random_latest_columns_desktop' => array( 'mod' => 'bmd_random_latest_columns_desktop', 'type' => 'number', 'default' => 5, 'min' => 3, 'max' => 6 ),
			'random_latest_columns_tablet' => array( 'mod' => 'bmd_random_latest_columns_tablet', 'type' => 'number', 'default' => 4, 'min' => 2, 'max' => 5 ),
			'random_latest_columns_mobile' => array( 'mod' => 'bmd_random_latest_columns_mobile', 'type' => 'number', 'default' => 3, 'min' => 1, 'max' => 4 ),
			'random_latest_rows_desktop' => array( 'mod' => 'bmd_random_latest_rows_desktop', 'type' => 'number', 'default' => 2, 'min' => 1, 'max' => 4 ),
			'random_latest_rows_tablet' => array( 'mod' => 'bmd_random_latest_rows_tablet', 'type' => 'number', 'default' => 2, 'min' => 1, 'max' => 4 ),
			'random_latest_rows_mobile' => array( 'mod' => 'bmd_random_latest_rows_mobile', 'type' => 'number', 'default' => 2, 'min' => 1, 'max' => 4 ),
			'random_latest_gap' => array( 'mod' => 'bmd_random_latest_gap', 'type' => 'number', 'default' => 16, 'min' => 8, 'max' => 40 ),
			'random_latest_title' => array( 'mod' => 'bmd_random_latest_title', 'type' => 'text', 'default' => __( 'สุ่มจากรายการล่าสุด', 'bossmaster-display' ) ),
			'random_latest_view_all' => array( 'mod' => 'bmd_random_latest_view_all', 'type' => 'text', 'default' => __( 'ดูทั้งหมด', 'bossmaster-display' ) ),
		'show_hero'       => array( 'mod' => 'bmd_show_hero', 'type' => 'bool' ),
		'show_featured'   => array( 'mod' => 'bmd_show_featured', 'type' => 'bool' ),
		'show_latest'     => array( 'mod' => 'bmd_show_latest', 'type' => 'bool' ),
		'show_random_latest' => array( 'mod' => 'bmd_show_random_latest', 'type' => 'bool' ),
		'show_directories'=> array( 'mod' => 'bmd_show_directories', 'type' => 'bool' ),
			'explore_count' => array( 'mod' => 'bmd_explore_count', 'type' => 'number', 'default' => 5, 'min' => 3, 'max' => 6 ),
			'explore_columns_desktop' => array( 'mod' => 'bmd_explore_columns_desktop', 'type' => 'number', 'default' => 3, 'min' => 2, 'max' => 4 ),
			'explore_columns_tablet' => array( 'mod' => 'bmd_explore_columns_tablet', 'type' => 'number', 'default' => 2, 'min' => 1, 'max' => 3 ),
			'explore_columns_mobile' => array( 'mod' => 'bmd_explore_columns_mobile', 'type' => 'number', 'default' => 1, 'min' => 1, 'max' => 2 ),
			'explore_gap' => array( 'mod' => 'bmd_explore_gap', 'type' => 'number', 'default' => 12, 'min' => 8, 'max' => 32 ),
			// Single page gallery quick settings
			'single_show_gallery' => array( 'mod' => 'bmd_single_show_gallery', 'type' => 'bool' ),
			'single_gallery_columns' => array( 'mod' => 'bmd_single_gallery_columns', 'type' => 'number', 'default' => 4, 'min' => 2, 'max' => 6 ),
			'single_gallery_columns_tablet' => array( 'mod' => 'bmd_single_gallery_columns_tablet', 'type' => 'number', 'default' => 3, 'min' => 2, 'max' => 4 ),
			'single_gallery_columns_mobile' => array( 'mod' => 'bmd_single_gallery_columns_mobile', 'type' => 'number', 'default' => 2, 'min' => 1, 'max' => 3 ),
			'single_gallery_rows' => array( 'mod' => 'bmd_single_gallery_rows', 'type' => 'number', 'default' => 2, 'min' => 1, 'max' => 6 ),
			'single_gallery_max_items' => array( 'mod' => 'bmd_single_gallery_max_items', 'type' => 'number', 'default' => 8, 'min' => 1, 'max' => 24 ),
			'single_gallery_gap' => array( 'mod' => 'bmd_single_gallery_gap', 'type' => 'number', 'default' => 10, 'min' => 6, 'max' => 32 ),
			'single_gallery_ratio' => array( 'mod' => 'bmd_single_gallery_ratio', 'type' => 'choice', 'allowed' => array( 'landscape', 'portrait' ), 'default' => 'landscape' ),
	);

	foreach ( $map as $key => $config ) {
		if ( ! array_key_exists( $key, $values ) ) {
			continue;
		}
		if ( 'bool' === $config['type'] ) {
			$value = empty( $values[ $key ] ) || 'false' === $values[ $key ] || '0' === (string) $values[ $key ] ? false : true;
		} elseif ( 'number' === $config['type'] ) {
			$value = absint( $values[ $key ] );
			if ( isset( $config['min'] ) ) {
				$value = max( $config['min'], $value );
			}
			if ( isset( $config['max'] ) ) {
				$value = min( $config['max'], $value );
			}
		} elseif ( 'choice' === $config['type'] ) {
			$value = bmd_sanitize_choice( $values[ $key ], $config['allowed'], $config['default'] );
		} elseif ( 'text' === $config['type'] ) {
			$value = sanitize_text_field( $values[ $key ] );
		} else {
			$value = sanitize_text_field( $values[ $key ] );
		}
		set_theme_mod( $config['mod'], $value );
	}

	do_action( 'litespeed_purge_all' );
	if ( function_exists( 'wp_cache_flush' ) ) {
		wp_cache_flush();
	}
	wp_send_json_success( array( 'message' => __( 'บันทึกการแสดงผลแล้ว', 'bossmaster-display' ) ) );
}
add_action( 'wp_ajax_bmd_save_quick_settings', 'bmd_save_quick_settings' );


/**
 * Apply the requested wide/compact detail presentation once after updating.
 */
function bmd_upgrade_112_defaults() {
	$schema = (string) get_option( 'bmd_display_schema_version', '0' );
	if ( version_compare( $schema, '1.1.2', '>=' ) ) {
		return;
	}
	set_theme_mod( 'bmd_card_ratio', 'landscape' );
	set_theme_mod( 'bmd_density', 'compact' );
	set_theme_mod( 'bmd_single_show_category', true );
	set_theme_mod( 'bmd_single_show_other_terms', true );
	set_theme_mod( 'bmd_single_show_actions', true );
	update_option( 'bmd_display_schema_version', '1.1.2', false );
}
add_action( 'after_setup_theme', 'bmd_upgrade_112_defaults', 99 );
