# Kinola WordPress plugin
This plugin integrates your WordPress site with Kinola web app. 
It imports productions and events/screenings from Kinola, displays them on your site and allows customers to buy tickets to events.

## Getting started
### Requirements
- PHP >=7.4 
- WP >= 6.0

### Installation
1. Clone the plugin from here.
2. Run `composer install` to generate the autoloader. (This requires you to have [Composer](https://getcomposer.org/) installed.)
3. Upload the plugin (including the newly generated `vendor` folder) to WP as you would with a regular plugin.

Note: Once the plugin is more or less "done," it will be uploaded to the official WordPress plugin repo.

### Setup
1. In your wp-config.php, add the following constant:  
`define( 'KINOLA_URL', 'https://your-cinema.kinola.ee' );`  

2. Create a Page for Films. In the content, add the following shortcode:  
`[kinola_films]`  

3. Create a Page for Events. In the content, add the following shortcode:  
`[kinola_events]`  

4. Ensure your WP site's time and date formats and time zones are set properly.

### Using the plugin
The plugin creates two menu items in the admin menu - Films and Events. Neither of them can be created or edited via WordPress - they must be imported
from Kinola instead. Any changes must be made in Kinola and then re-imported to WP.  

Productions must currently be imported manually from the admin - "Films > Import Films"  

Events are imported automatically every 15 minutes using WP's own task scheduling system. Please note that only future events 
are imported; past events are ignored.

## Technical stuff
The plugin creates two custom post types - `production` and `event`. `create_posts` capability on these posts has been disabled.  

The plugin overrides content on Production single post templates using `the_content` filter.  

Checkout and payments are handled by a custom React component in `checkout.php` template which is rendered via a custom endpoint. 
The component communicates with Kinola to book seats and process payments, so technically, all payment-related functionality 
is handled by Kinola itself (and therefore cannot be customized).

### Development & customization
The plugin is - or _should be_ - fully customizable. If there is something you'd like to change, but cannot do that:
- open an issue
- or make a pull request (see details below)
- or contact us (see details below)

### Templates
All templates used by the plugin are overrideable. To do so, create a folder called `kinola` in your theme and simply copy-paste 
the template you wish to override from the plugin `templates` folder. Follow the same folder structure as in the plugin's `templates` folder.

### Data
All event and film data is stored as postmeta.  
The `films`, `film` and `events` templates are all passed the corresponding instance(s) of `\Kinola\KinolaWp\Event` or `\Kinola\KinolaWp\Film` objects.
They contain a number of useful public functions. Some examples:

### Actions
`kinola/checkout/before_content`
Use this action to display your own custom content before the contents of the Checkout page.

### Filters
`kinola/checkout/show_title`
This filter can be used to control whether or not the default title is displayed on Checkout page.

`kinola/post_type/film`  
This filter allows you to modify the name of the Film post type.

`kinola/post_type/film/supports`  
This filter allows you to modify the `supports` parameter of Film post type.

`kinola/post_type/event`  
This filter allows you to modify the name of the Event post type.

`kinola/post_type/event/supports`  
This filter allows you to modify the `supports` parameter of Event post type.

`kinola/checkout/slug`  
This filter allows you to modify the URL slug of the checkout page.

`kinola/template_directories`  
This filter allows you to modify which folders are used to look for Kinola templates.

`kinola/template`  
This filter runs every time a Kinola template is loaded. Using it, you can completely customize which templates are loaded and from where.

### Advanced
Technically, further customization (modifying/removing the plugin's own actions, for example) is possible 
using the globals defined in `kinola.php` - for example:
```php
remove_action( 'init', [ $GLOBALS['KINOLA_ADMIN'], 'register_films_post_type' ], 1 );
add_action( 'init', 'register_your_own_films_post_type' );
```

**However**, please keep in mind that any changes you make like this might easily break after updates to the plugin source. 
If you need to change something this way, instead consider making a pull request to add filters to the relevant parts of the code base.

### Translations
The plugin is fully translatable - a translation template is located in `translations` folder.  
If you translate the plugin to your language, please make a pull request and share the translation po file!  

Note that many translatable strings have context "Admin" - these are strings that the end user will never see, 
so you may not want spend time on translating them.

## Contact
andres at elektriteater.ee

## Contributing
Please follow the WordPress coding standards.
