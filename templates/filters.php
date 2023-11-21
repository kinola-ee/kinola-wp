<?php
/**
 * This template displays the venue, date and time filters for events.
 */
?>

<div class="kinola-filters">
    <form class="js-kinola-filters-form" <?php if ( $film_id ): ?> data-film="<?php echo $film_id; ?>" <?php endif; ?>>
        <?php if ( apply_filters( 'kinola/filters/film', false ) && ! $film_id ): ?>
            <select
                class="js-kinola-film-filter kinola-film-filter"
                name="<?php echo \Kinola\KinolaWp\Helpers::get_film_parameter_slug() ?>"
            >
                <?php foreach ( $films as $key => $film ): ?>
                    <option value="<?php echo $key; ?>" <?php selected( $key, $selected_film ); ?>>
                        <?php echo $film; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <?php if ( count( $venues ) ): ?>
            <select
                class="js-kinola-venue-filter kinola-venue-filter"
                name="<?php echo \Kinola\KinolaWp\Helpers::get_venue_parameter_slug() ?>"
            >
                <?php foreach ( $venues as $key => $venue ): ?>
                    <option value="<?php echo $key; ?>" <?php selected( $key, $selected_venue ); ?>>
                        <?php echo $venue; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <select
            class="js-kinola-date-filter kinola-date-filter"
            name="<?php echo \Kinola\KinolaWp\Helpers::get_date_parameter_slug() ?>"
        >
            <?php foreach ( $dates as $key => $date ): ?>
                <option value="<?php echo $key; ?>" <?php selected( $key, $selected_date ); ?>>
                    <?php echo $date; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ( apply_filters( 'kinola/filters/time', false ) ): ?>
            <select
                class="js-kinola-time-filter kinola-time-filter"
                name="<?php echo \Kinola\KinolaWp\Helpers::get_time_parameter_slug() ?>"
            >
                <?php foreach ( $times as $key => $time ): ?>
                    <option value="<?php echo $key; ?>" <?php selected( $key, $selected_time ); ?>>
                        <?php echo $time; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <input type="submit" value="<?php echo __( 'Filter', 'kinola' ); ?>" class="kinola-filter-button"/>
    </form>
</div>
