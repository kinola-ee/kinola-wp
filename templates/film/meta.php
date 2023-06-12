<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<section class="kinola-film-meta">
    <?php echo $film->get_field( 'description' ); ?>
    <br><hr><br>
    <strong><?php echo $film->get_field( 'title' ); ?></strong> <br>
    <em><?php echo $film->get_field( 'title_original' ); ?></em> <br>

    <?php if ($film->get_field( 'countries' )): ?>
        <?php echo $film->get_field( 'countries' ); ?> <br>
    <?php endif; ?>

    <?php if ($film->get_field( 'release_date' )): ?>
        <?php echo $film->get_field( 'release_date' ); ?>,
    <?php endif; ?>
    <?php if ($film->get_field( 'runtime' )): ?>
        <?php echo $film->get_field( 'runtime' ); ?> <?php _ex( 'min', 'minutes', 'kinola' ); ?>
    <?php endif; ?>

    <br><br>

    <?php if ( $film->get_director() ): ?>
        <strong><?php _e( 'Director', 'kinola' ); ?></strong> <br>
        <?php echo $film->get_director(); ?>
        <br><br>
    <?php endif; ?>

    <?php if ( $film->get_cast() ): ?>
        <strong><?php _e( 'Cast', 'kinola' ); ?></strong> <br>
        <?php echo $film->get_cast(); ?>
        <br><br>
    <?php endif; ?>

    <?php if ($film->get_field('languages')): ?>
        <strong><?php _e('Language', 'kinola'); ?></strong> <br>
        <?php echo $film->get_field('languages'); ?>
        <br><br>
    <?php endif; ?>

    <?php if ($film->get_field('subtitles')): ?>
        <strong><?php _e('Subtitles', 'kinola'); ?></strong> <br>
        <?php echo $film->get_field('subtitles'); ?>
        <br><br>
    <?php endif; ?>

</section>
