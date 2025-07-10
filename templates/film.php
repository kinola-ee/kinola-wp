<?php
/**
 * This template is rendered inside the_content of a single Film post type.
 * It displays the detailed information of a film and all future screenings of that film.
 */
?>

<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<section class="kinola-film">
    <article class="kinola-film-sidebar">
        <?php echo \Kinola\KinolaWp\View::render( 'film/meta', [ 'film' => $film ] ); ?>
    </article>
    <article class="kinola-film-main">
        <?php if ( $film->get_field( 'gallery' ) && count( $film->get_field( 'gallery' ) ) || ($film->get_field( 'poster' )) ): ?>
            <div class="kinola-film-poster">
                <?php if ( $film->get_field( 'gallery' ) && count( $film->get_field( 'gallery' ) ) ): ?>
                    <img class="kinola-film-poster-photo" src="<?php echo $film->get_field( 'gallery' )[0]['src'] ?>" alt="<?php echo $film->get_field( 'title' ); ?>">
                <?php else: ?>
                    <div class="kinola-film-photo-placeholder"></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="kinola-film-wrap">
            <article class="kinola-film-content">
                <?php if ( count( $events ) ): ?>
                    <div class="kinola-film-tickets-reference">
                        <a href="#screenings" class="kinola-btn kinola-btn-full">
                            <?php _e( 'Screenings and tickets', 'kinola' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="kinola-film-title-desktop">
                        <h2><?php echo $film->get_field( 'title' ); ?></h2>
                    </div>
                    <?php echo $film->get_field( 'description' ); ?>
                </div>
                <?php echo \Kinola\KinolaWp\View::render( 'film/trailer', [ 'film' => $film ] ); ?>
                <?php echo \Kinola\KinolaWp\View::render( 'film/gallery', [ 'film' => $film ] ); ?>
            </article>

            <sidebar class="kinola-film-screenings" id="screenings">
                <?php echo \Kinola\KinolaWp\View::render( 'film/screenings', [
                    'film'            => $film,
                    'events'          => $events,
                    'rendered_filter' => $rendered_filter,
                ] ); ?>
            </sidebar>
        </div>
    </article>
</section>
