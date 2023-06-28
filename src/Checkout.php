<?php

namespace Kinola\KinolaWp;

use Kinola\KinolaWp\Api\Kinola_Api;

class Checkout {
    public static function get_title(): string {
        return get_bloginfo();
    }

    public static function get_plugin_api_base_url(): string {
        return Kinola_Api::get_plugin_api_base_url();
    }

    public static function get_kinola_js_url(): string {
        return 'https://plugin.kinola.ee/index.js';
    }

    public static function get_event_id() {
        global $wp_query;

        return $wp_query->query_vars[ Helpers::get_checkout_url_slug() ];
    }

    public static function get_strings(): array {
        return [
            'timer_label'                  => __( 'Session expires in:', 'kinola' ),
            'session_expired'              => __( 'Session expired', 'kinola' ),
            'floorplan_screen'             => __( 'Screen', 'kinola' ),
            'floorplan_entry'              => __( 'entrance', 'kinola' ),
            'floorplan_stage'              => __( 'stage', 'kinola' ),
            'floorplan_balcony'            => __( 'balcony', 'kinola' ),
            'floorplan_sideBalcony'        => __( 'side balcony', 'kinola' ),
            'intro_freeSeats'              => __( 'Free seats', 'kinola' ),
            'tickets_title'                => __( 'Choose tickets', 'kinola' ),
            'tickets_max'                  => __( 'Sorry, the maximum number of tickets is limited', 'kinola' ),
            'tickets_total'                => __( 'Total', 'kinola' ),
            'cards_title'                  => __( 'Client card and gift cards', 'kinola' ),
            'cards_label'                  => __( 'Client card or gift card number', 'kinola' ),
            'cards_button'                 => __( 'Confirm number', 'kinola' ),
            'cards_notFound'               => __( 'We couldn\'t find a card with this number', 'kinola' ),
            'cards_alreadyAdded'           => __( 'This card has already been added to this session', 'kinola' ),
            'contact_title'                => __( 'Contact', 'kinola' ),
            'contact_description'          => __( 'We\'ll use your contact info to e-mail you your tickets and contact you in the case of unexpected changes to the screening.', 'kinola' ),
            'contact_name'                 => __( 'name', 'kinola' ),
            'contact_name_invalid'         => __( 'Please enter your name', 'kinola' ),
            'contact_email'                => __( 'e-mail', 'kinola' ),
            'contact_email_invalid'        => __( 'Please enter a valid e-mail address', 'kinola' ),
            'contact_email_card_mismatch'  => __( 'The e-mail address you entered does not match the e-mail address on your card', 'kinola' ),
            'contact_phone'                => __( 'phone', 'kinola' ),
            'contact_newsletter'           => __( 'Sign up to our newsletter' ),
            'banks_title'                  => __( 'Choose payment', 'kinola' ),
            'banks_paymentFailed'          => __( 'Your payment was not successful. Please try again.', 'kinola' ),
            'banks_required'               => __( 'Please choose your payment method.', 'kinola' ),
            'submit_register'              => __( 'Register', 'kinola' ),
            'submit_pay'                   => __( 'Pay', 'kinola' ),
            'schedule'                     => __( 'Schedule', 'kinola' ),
            'completed_title'              => __( 'Ticket purchase successful!', 'kinola' ),
            'completed_seats'              => __( 'Seats', 'kinola' ),
            'completed_tickets'            => __( 'Tickets', 'kinola' ),
            'completed_startNew'           => __( 'Buy more tickets', 'kinola' ),
            'soldOut_title'                => __( 'Sold out', 'kinola' ),
            'soldOut_text'                 => __( 'Sorry, all tickets have been sold out.', 'kinola' ),
            'temporarilyUnavailable_title' => __( 'Sorry, tickets are temporarily unavailable.', 'kinola' ),
            'pending_title'                => __( 'Payment pending', 'kinola' ),
            'pending_text'                 => __( 'Please complete your payment.', 'kinola' ),

            // Backwards compatibility with older API versions, can be removed soon
            'tickets_text'                 => __( 'You can ask for your tickets from the cashier.', 'kinola' ),
            'completed_downloadNote'       => __( 'You can also download your tickets here:', 'kinola' ),
            'completed_downloadButton'     => __( 'Download tickets', 'kinola' ),
        ];
    }
}
