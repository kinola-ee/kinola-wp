<?php 
    $gridColsClasses = '';
    if ( apply_filters( 'kinola/filters/film', true ) && count( $venues ) )
        $gridColsClasses .= ' has-three-cols';
    elseif ( apply_filters( 'kinola/filters/film', true ) || count( $venues ) )
        $gridColsClasses .= ' has-two-cols';
?>

<div class="kinola-events-filters <?php echo $gridColsClasses ?>">
    <?php if ( apply_filters( 'kinola/filters/film', true ) ): ?>
        <div class="kinola-filters-item">
            <div class="kinola-filters-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="2" width="20" height="20" rx="2.18" ry="2.18"></rect>
                    <line x1="7" y1="2" x2="7" y2="22"></line>
                    <line x1="17" y1="2" x2="17" y2="22"></line>
                    <line x1="2" y1="12" x2="22" y2="12"></line>
                    <line x1="2" y1="7" x2="7" y2="7"></line>
                    <line x1="2" y1="17" x2="7" y2="17"></line>
                    <line x1="17" y1="17" x2="22" y2="17"></line>
                    <line x1="17" y1="7" x2="22" y2="7"></line>
                </svg>
            </div>
            <select
                class="js-kinola-film-filter"
                name="<?php echo \Kinola\KinolaWp\Helpers::get_film_parameter_slug() ?>"
                onchange="this.form.submit()"
            >
                <?php foreach ( $films as $key => $film ): ?>
                    <option value="<?php echo $key; ?>" <?php selected( $key, $selected_film ); ?>>
                        <?php echo $film; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>  

    <?php if ( count( $venues ) ): ?>
        <div class="kinola-filters-item">
            <div class="kinola-filters-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <select
                class="js-kinola-venue-filter kinola-venue-filter"
                name="<?php echo \Kinola\KinolaWp\Helpers::get_venue_parameter_slug() ?>"
                onchange="this.form.submit()"
            >
                <?php foreach ( $venues as $key => $venue ): ?>
                    <option value="<?php echo $key; ?>" <?php selected( $key, $selected_venue ); ?>>
                        <?php echo $venue; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <div class="kinola-filters-item group">
        <div class="kinola-filters-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
        <select
            class="js-kinola-date-filter kinola-date-filter"
            name="<?php echo \Kinola\KinolaWp\Helpers::get_date_parameter_slug() ?>"
            onchange="this.form.submit()"
        >
            <?php foreach ( $dates as $key => $date ): ?>
                <option value="<?php echo $key; ?>" <?php selected( $key, $selected_date ); ?>>
                    <?php echo $date; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
