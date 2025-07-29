<!DOCTYPE html>
<?php
/**
 * This template loads the React app which is responsible for the whole checkout process.
 */
$template_html = get_the_block_template_html();
?>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title><?php bloginfo('name'); ?></title>
  <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>
  
  <?php get_header(); ?>

  <main class="kinola-content">
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
  </div>

  <?php get_footer(); ?>
  
  <?php wp_footer(); ?>
</body>
</html>
