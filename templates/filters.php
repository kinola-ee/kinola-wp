<?php
/**
 * This template displays the venue, date and time filters for events.
 */
?>

<div class="kinola-filters">
    <form>
        <?php if ( count( $venues ) ): ?>
            <select class="js-kinola-venue-filter kinola-venue-filter"
                <?php if ( $film_id ): ?> data-film="<?php echo $film_id; ?>" <?php endif; ?>
                    name="<?php echo \Kinola\KinolaWp\Ajax::PARAM_VENUE ?>">
                <?php foreach ( $venues as $key => $venue ): ?>
                    <option value="<?php echo $key; ?>" <?php selected( $key, $selected_venue ); ?>>
                        <?php echo $venue; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <select class="js-kinola-date-filter kinola-date-filter"
            <?php if ( $film_id ): ?> data-film="<?php echo $film_id; ?>" <?php endif; ?>
                name="<?php echo \Kinola\KinolaWp\Ajax::PARAM_DATE ?>">
            <?php foreach ( $dates as $key => $date ): ?>
                <option value="<?php echo $key; ?>" <?php selected( $key, $selected_date ); ?>>
                    <?php echo $date; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select class="js-kinola-time-filter kinola-time-filter"
            <?php if ( $film_id ): ?> data-film="<?php echo $film_id; ?>" <?php endif; ?>
                name="<?php echo \Kinola\KinolaWp\Ajax::PARAM_TIME ?>">
            <?php foreach ( $times as $key => $time ): ?>
                <option value="<?php echo $key; ?>" <?php selected( $key, $selected_time ); ?>>
                    <?php echo $time; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="<?php echo __('Filter', 'kinola'); ?>" class="kinola-filter-button" />
    </form>
</div>
