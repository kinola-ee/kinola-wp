<?php
/**
 * This template is the output of [kinola_films] shortcode.
 * It displays a list of all films.
 */
?>

<h1><?php the_title(); ?></h1>

<?php if ( count( $films ) ): ?>
    <div class="kinola-items">
        <?php foreach ( $films as $film ): ?>
            <?php /* @var $film \Kinola\KinolaWp\Film */ ?>
            <div class="kinola-item">
                <a href="<?php echo get_permalink( $film->get_local_id() ); ?>" class="kinola-item-link">
                    <div class="kinola-item-photo-wrapper">
                        <?php if ( $film->get_field( 'poster' ) ): ?>
                            <div class="kinola-item-photo-bg">
                                <img class="kinola-item-photo-bg-img" src="<?php echo $film->get_field( 'poster' ); ?>" alt="<?php echo $film->get_field( 'title' ); ?>">
                            </div>
                            <div class="kinola-item-photo">
                                <img class="kinola-item-photo-img" src="<?php echo $film->get_field( 'poster' ); ?>" alt="<?php echo $film->get_field( 'title' ); ?>">
                            </div>
                        <?php else: ?>
                            <div class="kinola-item-photo-placeholder"></div>
                        <?php endif; ?>
                    </div>
                    <div class="kinola-item-info">
                        <?php if ( $film->get_field( 'title' ) ): ?>
                            <h4>
                                <?php echo $film->get_field( 'title' ); ?>
                            </h4>
                        <?php endif; ?>
                        <div>
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
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="kinola-items-none">
        <h2><?php _e( 'No films to display.', 'kinola' ); ?></h2>
    </div>
<?php endif; ?>
