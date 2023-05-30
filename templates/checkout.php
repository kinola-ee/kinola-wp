<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport"
	      content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<title><?php echo \Kinola\KinolaWp\Checkout::get_title(); ?></title>
</head>
<body>
<h1 style="font-family: Poppins; text-align: center; padding: 40px 0;">
	<?php echo \Kinola\KinolaWp\Checkout::get_title(); ?>
</h1>
<div id="kinola-container"></div>

<script src="<?php echo \Kinola\KinolaWp\Checkout::get_kinola_js_url(); ?>"></script>
<script>
    const container = document.getElementById('kinola-container')

    window.Kinola.render(container, {
        apiBaseUrl: '<?php echo \Kinola\KinolaWp\Checkout::get_plugin_api_base_url(); ?>',
        eventId: '<?php echo \Kinola\KinolaWp\Checkout::get_event_id(); ?>',
        scheduleUrl: '<?php echo get_home_url(); ?>',
    })
</script>
</body>
</html>
