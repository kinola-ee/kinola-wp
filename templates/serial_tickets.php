<div id="kinola-serial-ticket"></div>
<script src="<?php echo \Kinola\KinolaWp\Checkout::get_kinola_js_url(); ?>"></script>
<script>
    const container = document.getElementById('kinola-serial-ticket')

    window.Kinola.renderSerialTicket(container, {
        apiBaseUrl: '<?php echo \Kinola\KinolaWp\Checkout::get_plugin_api_base_url(); ?>',
        strings: <?php echo json_encode(\Kinola\KinolaWp\Checkout::get_strings()); ?>,

        <?php if (\Kinola\KinolaWp\Helpers::get_checkout_terms_link()): ?>
        termsLink: '<?php echo \Kinola\KinolaWp\Helpers::get_checkout_terms_link(); ?>',
        <?php endif; ?>
    })
</script>
