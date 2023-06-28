<!doctype html>
<?php
/**
 * This template loads the React app which is responsible for the whole checkout process.
 */
?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <title><?php echo \Kinola\KinolaWp\Checkout::get_title(); ?></title>
</head>
<body>

<?php if (apply_filters('kinola/checkout/show_title', true)): ?>
    <h1 style="font-family: Poppins; text-align: center; padding: 40px 0;">
        <?php echo \Kinola\KinolaWp\Checkout::get_title(); ?>
    </h1>
<?php endif; ?>

<?php do_action('kinola/checkout/before_content'); ?>

<div id="kinola-container"></div>

<script src="<?php echo \Kinola\KinolaWp\Checkout::get_kinola_js_url(); ?>"></script>
<script>
    const container = document.getElementById('kinola-container')

    window.Kinola.render(container, {
        apiBaseUrl: '<?php echo \Kinola\KinolaWp\Checkout::get_plugin_api_base_url(); ?>',
        eventId: '<?php echo \Kinola\KinolaWp\Checkout::get_event_id(); ?>',
        scheduleUrl: '<?php echo get_home_url(); ?>',
        hasNewsletterCheckbox: <?php echo apply_filters('kinola/checkout/show_newsletter_checkbox', false); ?>,
        selectedLang: '<?php echo \Kinola\KinolaWp\Helpers::get_language(); ?>',
        strings: <?php echo json_encode(\Kinola\KinolaWp\Checkout::get_strings()); ?>
    })
</script>
</body>
</html>
