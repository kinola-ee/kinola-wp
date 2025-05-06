<?php
/**
 * This template is rendered inside the_content of a single Film post type.
 * It displays the detailed information of a film and all future screenings of that film.
 */
?>

<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<section class="w-full max-w-screen-1xl flex flex-col lg:flex-row gap-x-3 font-base">
    <article class="w-full lg:w-sidebar shrink-0">
        <?php echo \Kinola\KinolaWp\View::render( 'film/meta', [ 'film' => $film ] ); ?>
    </article>
    <article class="w-full flex flex-col gap-y-10">
        <?php if ( $film->get_field( 'gallery' ) && count( $film->get_field( 'gallery' ) ) || ($film->get_field( 'poster' )) ): ?>
            <div class="h-98 max-lg:hidden relative overflow-hidden">
                <?php if ( $film->get_field( 'gallery' ) && count( $film->get_field( 'gallery' ) ) ): ?>
                    <img class="w-full h-full object-cover object-center" src="<?php echo $film->get_field( 'gallery' )[0]['src'] ?>" alt="<?php echo $film->get_field( 'title' ); ?>">
                <?php else: ?>
                    <div class="absolute inset-0 bg-gradient-to-br from-accentII100 to-accentIII100 opacity-10 -z-10"></div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col max-xl:gap-y-6 xl:flex-row xl:gap-x-14">
            <article class="w-full flex flex-col gap-y-6">
                <?php if ( count( $events ) ): ?>
                    <div class="fixed inset-x-4 bottom-4 lg:hidden z-50">
                        <a href="#screenings" class="w-full inline-block px-12 py-3 rounded-full bg-primary100 text-sm text-center text-white font-semibold tracking-wide uppercase hover:bg-accentI100 active:bg-accentI80 focus-visible:bg-primary80 focus-visible:outline focus-visible:outline-accentI40 focus-visible:outline-6 focus-visible:outline-offset-0 transition">
                            <?php _e( 'Screenings and tickets', 'kinola' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="max-lg:hidden">
                        <h2><?php echo $film->get_field( 'title' ); ?></h2>
                    </div>
                    <?php echo $film->get_field( 'description' ); ?>
                </div>
                <?php echo \Kinola\KinolaWp\View::render( 'film/trailer', [ 'film' => $film ] ); ?>
                <?php echo \Kinola\KinolaWp\View::render( 'film/gallery', [ 'film' => $film ] ); ?>
            </article>

            <sidebar class="w-full lg:w-105 shrink-0 scroll-m-6" id="screenings">
                <?php echo \Kinola\KinolaWp\View::render( 'film/screenings', [
                    'film'            => $film,
                    'events'          => $events,
                    'rendered_filter' => $rendered_filter,
                ] ); ?>
            </sidebar>
        </div>
    </article>
</section>
