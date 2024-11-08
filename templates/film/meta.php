<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<div class="flex flex-col gap-y-10">
    <div class="h-98">
        <img class="w-full h-full object-cover object-center" src="<?php echo $film->get_field( 'poster' ); ?>" alt="<?php echo $film->get_field( 'title' ); ?>">
    </div>

    <div class="w-full lg:max-w-60 flex flex-col gap-y-6">
        <div class="flex flex-col gap-y-1">
            <?php if ($film->get_field( 'title_original' )): ?>
                <div class="font-semibold">
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
            <div class="flex flex-col gap-y-1">
                <div>
                    <?php _e( 'Director', 'kinola' ); ?>
                </div>
                <div class="font-semibold">
                    <?php echo $film->get_director(); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ( $film->get_cast() ): ?>
            <div class="flex flex-col gap-y-1">
                <div>
                    <?php _e( 'Cast', 'kinola' ); ?>
                </div>
                <div class="font-semibold">
                    <?php echo $film->get_cast(); ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="flex flex-col gap-y-1">
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
