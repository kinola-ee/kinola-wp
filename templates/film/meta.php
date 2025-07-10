<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<div class="kinola-film-meta">
    <?php if ( $film->get_field( 'gallery' ) && count( $film->get_field( 'gallery' ) ) || ($film->get_field( 'poster' )) ): ?>
        <div class="kinola-film-poster">
            <?php if ($film->get_field( 'poster' )): ?>
                <img class="kinola-film-poster-photo" src="<?php echo $film->get_field( 'poster' ); ?>" alt="<?php echo $film->get_field( 'title' ); ?>">
            <?php else: ?>
                <div class="kinola-film-photo-placeholder"></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="kinola-film-poster-mobile">
        <?php if ( $film->get_field( 'gallery' ) && count( $film->get_field( 'gallery' ) ) ): ?>
            <img class="kinola-film-poster-photo" src="<?php echo $film->get_field( 'gallery' )[0]['src'] ?>" alt="<?php echo $film->get_field( 'title' ); ?>">
        <?php else: ?>
            <?php if ($film->get_field( 'poster' )): ?>
                <img class="kinola-film-poster-photo" src="<?php echo $film->get_field( 'poster' ); ?>" alt="<?php echo $film->get_field( 'title' ); ?>">
            <?php else: ?>
                <div class="kinola-film-photo-placeholder"></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="kinola-film-info">
        <div class="kinola-film-info-column">
            <div class="kinola-film-title-mobile">
                <h2><?php echo $film->get_field( 'title' ); ?></h2>
            </div>
            <?php if ($film->get_field( 'title_original' )): ?>
                <div class="kinola-film-info-column-value">
                    <?php echo $film->get_field( 'title_original' ); ?>
                </div>
            <?php endif; ?>
            <?php if ($film->get_field( 'countries' )): ?>
                <div>
                    <?php echo $film->get_field( 'countries' ); ?>
                </div>
            <?php endif; ?>
            <?php if ($film->get_field( 'release_date' )): ?>
                <div>
                    <?php
                        $date=date_create($film->get_field( 'release_date' ));
                        echo date_format($date, "Y");
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( $film->get_director() ): ?>
            <div class="kinola-film-info-column">
                <div>
                    <?php _e( 'Director', 'kinola' ); ?>
                </div>
                <div class="kinola-film-info-column-value">
                    <?php echo $film->get_director(); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ( $film->get_cast() ): ?>
            <div class="kinola-film-info-column">
                <div>
                    <?php _e( 'Cast', 'kinola' ); ?>
                </div>
                <div class="kinola-film-info-column-value">
                    <?php echo $film->get_cast(); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="kinola-film-info-column">
            <?php if ($film->get_field( 'runtime' )): ?>
                <div>
                    <?php echo $film->get_field( 'runtime' ); ?> <?php _ex( 'min', 'minutes', 'kinola' ); ?>
                </div>
            <?php endif; ?>
            <?php if ($film->get_field('languages')): ?>
                <div>
                    <?php _e('Language', 'kinola'); ?></strong>: <?php echo $film->get_field('languages'); ?>
                </div>
            <?php endif; ?>
            <?php if ($film->get_field('subtitles')): ?>
                <div>
                    <?php _e('Subtitles', 'kinola'); ?></strong>: <?php echo $film->get_field('subtitles'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
