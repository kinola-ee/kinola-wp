<?php
/**
 * This template displays the location and date filters for events.
 */
?>

<div>
    <form>
        <select class="js-kinola-date-filter" onchange="this.form.submit()"
                name="<?php echo \Kinola\KinolaWp\Helpers::get_date_parameter_slug(); ?>">
            <?php foreach ( $dates as $key => $date ): ?>
                <option value="<?php echo $key; ?>" <?php selected( $key, $selected_date ); ?>>
                    <?php echo $date; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ( count( $venues ) ): ?>
            <select class="js-kinola-location-filter" onchange="this.form.submit()"
                    name="<?php echo \Kinola\KinolaWp\Helpers::get_venue_parameter_slug(); ?>">
                <?php foreach ( $venues as $key => $venue ): ?>
                    <option value="<?php echo $key; ?>" <?php selected( $key, $selected_venue ); ?>>
                        <?php echo $venue; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </form>
</div>
