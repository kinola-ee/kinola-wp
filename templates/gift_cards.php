<div id="kinola-gift-card"></div>
<script src="<?php echo esc_url( \Kinola\KinolaWp\Checkout::get_kinola_js_url() ); ?>"></script>
<script>
    const container = document.getElementById('kinola-gift-card')

    window.Kinola.renderGiftCard(container, {
        apiBaseUrl: <?php echo wp_json_encode( \Kinola\KinolaWp\Checkout::get_plugin_api_base_url() ); ?>,
        strings: <?php echo wp_json_encode( \Kinola\KinolaWp\Checkout::get_strings() ); ?>,

        <?php if (\Kinola\KinolaWp\Helpers::get_checkout_terms_link()): ?>
        termsLink: <?php echo wp_json_encode( \Kinola\KinolaWp\Helpers::get_checkout_terms_link() ); ?>,
        <?php endif; ?>
    })
</script>
