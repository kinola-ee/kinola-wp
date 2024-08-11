<div id="kinola-container"></div>

<script src="<?php echo \Kinola\KinolaWp\Checkout::get_kinola_js_url(); ?>"></script>
<script>
    const container = document.getElementById('kinola-container')

    window.Kinola.render(container, {
        apiBaseUrl: '<?php echo \Kinola\KinolaWp\Checkout::get_plugin_api_base_url(); ?>',
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
