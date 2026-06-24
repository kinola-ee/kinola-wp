<?php /* @var $film \Kinola\KinolaWp\Film */ ?>

<section class="kinola-film-meta">
	<img alt="Film poster" src="<?php echo esc_url( $film->get_field( 'poster' ) ); ?>">
    <?php echo wp_kses_post( $film->get_field( 'description' ) ); ?>
    <br><hr><br>
    <strong><?php echo esc_html( $film->get_field( 'title' ) ); ?></strong> <br>
    <em><?php echo esc_html( $film->get_field( 'title_original' ) ); ?></em> <br>

    <?php if ($film->get_field( 'countries' )): ?>
        <?php echo esc_html( $film->get_field( 'countries' ) ); ?> <br>
    <?php endif; ?>

    <?php if ($film->get_field( 'release_date' )): ?>
        <?php echo esc_html( $film->get_field( 'release_date' ) ); ?>,
    <?php endif; ?>
    <?php if ($film->get_field( 'runtime' )): ?>
        <?php echo esc_html( $film->get_field( 'runtime' ) ); ?> <?php _ex( 'min', 'minutes', 'kinola' ); ?>
    <?php endif; ?>

    <br><br>

    <?php if ( $film->get_director() ): ?>
        <strong><?php _e( 'Director', 'kinola' ); ?></strong> <br>
        <?php echo esc_html( $film->get_director() ); ?>
        <br><br>
    <?php endif; ?>

    <?php if ( $film->get_cast() ): ?>
        <strong><?php _e( 'Cast', 'kinola' ); ?></strong> <br>
        <?php echo esc_html( $film->get_cast() ); ?>
        <br><br>
    <?php endif; ?>

    <?php if ($film->get_field('languages')): ?>
        <strong><?php _e('Language', 'kinola'); ?></strong> <br>
        <?php echo esc_html( $film->get_field('languages') ); ?>
        <br><br>
    <?php endif; ?>

    <?php if ($film->get_field('subtitles')): ?>
        <strong><?php _e('Subtitles', 'kinola'); ?></strong> <br>
        <?php echo esc_html( $film->get_field('subtitles') ); ?>
        <br><br>
    <?php endif; ?>

</section>
