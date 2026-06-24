<?php

namespace Kinola\KinolaWp\Admin;

use Kinola\KinolaWp\Helpers;

class Settings {

    public function __construct() {
        // Priority 11: must run after Admin::register_main_menu() (priority 10) has
        // created the 'kinola' parent menu, otherwise this submenu's page hook is
        // mis-registered and admin.php?page=kinola-settings becomes unreachable.
        add_action( 'admin_menu', [ $this, 'register_settings_page' ], 11 );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function register_settings_page() {
        add_submenu_page(
            'kinola',
            _x( 'Kinola settings', 'Admin', 'kinola' ),
            _x( 'Settings', 'Admin', 'kinola' ),
            'manage_options',
            'kinola-settings',
            [ $this, 'render_settings_page' ]
        );
    }

    public function register_settings() {
        register_setting( 'kinola_settings', Helpers::OPTION_SCHEMA_ENABLED, [
            'type'              => 'string',
            'default'           => '1',
            'sanitize_callback' => function ( $value ) {
                return $value ? '1' : '0';
            },
        ] );

        // Empty title and no intro callback: do_settings_sections() renders neither a heading nor any
        // text above the field. The explanatory copy lives under the field instead (render_schema_enabled_field).
        add_settings_section(
            'kinola_settings_seo',
            '',
            '__return_null',
            'kinola-settings'
        );

        add_settings_field(
            Helpers::OPTION_SCHEMA_ENABLED,
            _x( 'Structured data', 'Admin', 'kinola' ),
            [ $this, 'render_schema_enabled_field' ],
            'kinola-settings',
            'kinola_settings_seo'
        );
    }

    public function render_schema_enabled_field() {
        // Hidden input makes sure unchecking the box persists '0' instead of submitting nothing.
        ?>
        <input type="hidden" name="<?php echo esc_attr( Helpers::OPTION_SCHEMA_ENABLED ); ?>" value="0"/>
        <label>
            <input type="checkbox"
                   name="<?php echo esc_attr( Helpers::OPTION_SCHEMA_ENABLED ); ?>"
                   value="1" <?php checked( Helpers::is_schema_enabled() ); ?> />
            <br>
            <?php echo esc_html_x( 'Add structured data (schema.org models: MovieTheater, ScreeningEvent and Movie) so search engines and AI agents can read your films, screenings and venues', 'Admin', 'kinola' ); ?>
        </label>
        <p class="description">
            <?php echo esc_html_x( 'When enabled, structured metadata regarding screenings, events and cinema venues is added to your pages automatically, with nothing else to set up.', 'Admin', 'kinola' ); ?>
        </p>
        <?php
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html_x( 'Kinola settings', 'Admin', 'kinola' ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'kinola_settings' );
                do_settings_sections( 'kinola-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
