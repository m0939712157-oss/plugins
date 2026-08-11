<?php
/** Standard page. */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="bmd-page-hero is-simple"><div class="bmd-container"><p class="bmd-eyebrow"><?php esc_html_e( 'PAGE', 'bossmaster-display' ); ?></p><h1><?php the_title(); ?></h1></div></section>
<section class="bmd-section"><div class="bmd-container bmd-prose"><?php while ( have_posts() ) : the_post(); the_content(); endwhile; ?></div></section>
<?php get_footer(); ?>
