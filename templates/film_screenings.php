<?php
/**
 * This template is rendered by [kinola_film_screenings] shortcode.
 */
?>

<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<div class="kinola-film-content">
    <?php echo \Kinola\KinolaWp\View::render( 'film/screenings', [
        'film'            => $film,
        'events'          => $events,
        'rendered_filter' => $rendered_filter,
    ] ); ?>
</div>
