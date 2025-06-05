<!doctype html>
<?php
/**
 * This template loads the React app which is responsible for the whole checkout process.
 */
$template_html = get_the_block_template_html();
?>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>" />
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="wp-site-blocks">
    <?php echo do_blocks( '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->' ); ?>

    <?php do_action('kinola/checkout/before_content'); ?>

    <div id="kinola-container"></div>

    <script src="<?php echo \Kinola\KinolaWp\Checkout::get_kinola_js_url(); ?>"></script>
    <script>
        const container = document.getElementById('kinola-container')

        window.Kinola.render(container, {
            apiBaseUrl: '<?php echo \Kinola\KinolaWp\Checkout::get_plugin_api_base_url(); ?>',
            eventId: '<?php echo \Kinola\KinolaWp\Checkout::get_event_id(); ?>',
            scheduleUrl: '<?php echo get_home_url(); ?>',
            hasNewsletterCheckbox: <?php echo \Kinola\KinolaWp\Helpers::has_newsletter_checkbox() ? 'true' : 'false'; ?>,
            newsletterCheckedByDefault: <?php echo \Kinola\KinolaWp\Helpers::newsletter_checked_by_default() ? 'true' : 'false'; ?>,
            selectedLang: '<?php echo \Kinola\KinolaWp\Helpers::get_language(); ?>',
            strings: <?php echo json_encode(\Kinola\KinolaWp\Checkout::get_strings()); ?>,

            <?php if (\Kinola\KinolaWp\Helpers::get_checkout_terms_link()): ?>
                termsLink: '<?php echo \Kinola\KinolaWp\Helpers::get_checkout_terms_link(); ?>',
            <?php endif; ?>
        })
    </script>

    <?php do_action('kinola/checkout/after_content'); ?>

    <?php echo do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->' ); ?>
</div>

</body>
</html>
