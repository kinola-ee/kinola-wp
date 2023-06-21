<?php
/**
 * This template is rendered inside the_content of a single Film post type.
 * It displays the detailed information of a film and all future screenings of that film.
 */
?>

<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<div class="kinola-film-content">
    <?php echo \Kinola\KinolaWp\View::render( 'film/meta', [ 'film' => $film ] ); ?>
    <?php echo \Kinola\KinolaWp\View::render( 'film/trailer', [ 'film' => $film ] ); ?>
    <?php echo \Kinola\KinolaWp\View::render( 'film/gallery', [ 'film' => $film ] ); ?>
    <?php echo \Kinola\KinolaWp\View::render( 'film/screenings', [
        'film'            => $film,
        'events'          => $events,
        'rendered_filter' => $rendered_filter,
    ] ); ?>
</div>
