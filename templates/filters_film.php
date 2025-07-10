<?php $gridColsClass = count( $venues ) ? 'has-two-cols' : 'has-one-col'; ?>

<div class="kinola-film-filters <?php echo $gridColsClass ?>">
    <?php if ( count( $venues ) ): ?>
        <div class="kinola-filters-item">
            <div class="kinola-filters-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <select
                class="js-kinola-venue-filter"
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

    <div class="kinola-filters-item">
        <div class="kinola-filters-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
        <select
            class="js-kinola-date-filter"
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
