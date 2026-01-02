<?php
/**
 * Checkout Template
 *
 * This template loads the React app which is responsible for the whole checkout process.
 *
 * BLOCK THEME COMPATIBILITY:
 * For block themes, this template pre-processes header and footer template parts during
 * wp_enqueue_scripts to ensure their block-specific styles are properly enqueued.
 * This is necessary because block_header_area() and block_footer_area() don't
 * automatically trigger style enqueuing.
 *
 * KNOWN TRADE-OFF: Template parts are parsed twice (once for style enqueuing, once for
 * rendering). This overhead is acceptable for most use cases and necessary for proper
 * block theme support.
 *
 * @package Kinola
 * @since 1.0.0
 */

/**
 * Render the Kinola checkout application
 *
 * This helper function eliminates code duplication between block and classic theme sections.
 *
 * @since 1.0.0
 */
function kinola_render_checkout_app() {
	?>
	<?php if ( apply_filters( 'kinola/checkout/show_title', true ) ) : ?>
		<h1 class="kinola-checkout-title">
			<?php echo esc_html( \Kinola\KinolaWp\Checkout::get_title() ); ?>
		</h1>
	<?php endif; ?>

	<?php do_action( 'kinola/checkout/before_content' ); ?>

	<div id="kinola-container"></div>

	<script src="<?php echo esc_url( \Kinola\KinolaWp\Checkout::get_kinola_js_url() ); ?>"></script>
	<script>
		const container = document.getElementById('kinola-container')

		window.Kinola.render(container, {
			apiBaseUrl: <?php echo wp_json_encode( \Kinola\KinolaWp\Checkout::get_plugin_api_base_url() ); ?>,
			eventId: <?php echo wp_json_encode( \Kinola\KinolaWp\Checkout::get_event_id() ); ?>,
			scheduleUrl: <?php echo wp_json_encode( get_home_url() ); ?>,
			hasNewsletterCheckbox: <?php echo \Kinola\KinolaWp\Helpers::has_newsletter_checkbox() ? 'true' : 'false'; ?>,
			newsletterCheckedByDefault: <?php echo \Kinola\KinolaWp\Helpers::newsletter_checked_by_default() ? 'true' : 'false'; ?>,
			selectedLang: <?php echo wp_json_encode( \Kinola\KinolaWp\Helpers::get_language() ); ?>,
			strings: <?php echo wp_json_encode( \Kinola\KinolaWp\Checkout::get_strings() ); ?>,
			<?php if ( \Kinola\KinolaWp\Helpers::get_checkout_terms_link() ) : ?>
				termsLink: <?php echo wp_json_encode( \Kinola\KinolaWp\Helpers::get_checkout_terms_link() ); ?>,
			<?php endif; ?>
		})
	</script>

	<?php do_action( 'kinola/checkout/after_content' ); ?>
	<?php
}

// Determine theme type and render accordingly
if ( wp_is_block_theme() ) {
	// Block theme: need to output complete HTML structure with wp_head() and wp_footer()

	// Pre-process template parts to ensure their block styles get enqueued
	add_action( 'wp_enqueue_scripts', function() {
		// Get header template part and parse its blocks to trigger style enqueuing
		$header_template = get_block_template( get_stylesheet() . '//header', 'wp_template_part' );
		if ( $header_template && ! empty( $header_template->content ) ) {
			// Parse blocks to trigger style enqueuing without outputting
			do_blocks( $header_template->content );
		}

		// Get footer template part and parse its blocks to trigger style enqueuing
		$footer_template = get_block_template( get_stylesheet() . '//footer', 'wp_template_part' );
		if ( $footer_template && ! empty( $footer_template->content ) ) {
			// Parse blocks to trigger style enqueuing without outputting
			do_blocks( $footer_template->content );
		}
	}, 5); // Priority 5 to run before wp_head()

	?>
	<!doctype html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>

	<div class="wp-site-blocks">
		<header class="wp-block-template-part">
			<?php block_header_area(); ?>
		</header>

		<main class="wp-block-group kinola-checkout-main has-global-padding is-layout-constrained wp-block-group-is-layout-constrained" id="wp--skip-link--target">
			<?php kinola_render_checkout_app(); ?>
		</main>

		<footer class="wp-block-template-part">
			<?php block_footer_area(); ?>
		</footer>
	</div>

	<?php wp_footer(); ?>
	</body>
	</html>
	<?php
} else {
	// Classic theme: use traditional header/footer
	get_header();

	kinola_render_checkout_app();

	get_footer();
}
