<?php
/**
 * This template is the output of [kinola_films] shortcode.
 * It displays a list of all films.
 */
?>

<?php if ( count( $films ) ): ?>
    <div class="w-full max-w-screen-1xl flex flex-col sm:grid sm:grid-cols-2 lg:grid-cols-3 gap-x-3 gap-y-12 !mt-10">
        <?php foreach ( $films as $film ): ?>
            <?php /* @var $film \Kinola\KinolaWp\Film */ ?>
            <a href="<?php echo get_permalink( $film->get_local_id() ); ?>">
                <div class="w-full aspect-4/3 overflow-hidden relative">
                    <?php if ( $film->get_field( 'poster' ) ): ?>
                        <div class="absolute -inset-4 bg-center blur-2xl">
                            <img class="w-full h-full object-cover object-center" src="<?php echo $film->get_field( 'poster' ); ?>" alt="<?php echo $film->get_field( 'title' ); ?>">
                        </div>
                        <div class="w-full h-full flex items-center justify-center relative backdrop-blur-xl bg-white/5">
                            <img class="w-full h-full object-center object-contain" src="<?php echo $film->get_field( 'poster' ); ?>" alt="<?php echo $film->get_field( 'title' ); ?>">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mt-3 text-base leading-6 text-neutral-900">
                    <?php if ( $film->get_field( 'title' ) ): ?>
                        <div class="text-1xl font-semibold mb-1">
                            <?php echo $film->get_field( 'title' ); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( $film->get_field( 'runtime' ) ): ?>
                        <div>
                            <?php echo $film->get_field( 'runtime' ); ?> <?php _ex( 'min', 'minutes', 'kinola' ); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( $film->get_field( 'languages' ) ): ?>
                        <div>
                            <?php _e('Language', 'kinola'); ?>: <?php echo $film->get_field( 'languages' ); ?>
                        </div>
                    <?php endif; ?>
                    <?php if ( $film->get_field( 'subtitles' ) ): ?>
                        <div>
                            <?php _e('Subtitles', 'kinola'); ?>: <?php echo $film->get_field( 'subtitles' ); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="w-full text-center text-xl font-semibold font-heading uppercase !my-28">
        <?php _e( 'No films to display.', 'kinola' ); ?>
    </div>
<?php endif; ?>
