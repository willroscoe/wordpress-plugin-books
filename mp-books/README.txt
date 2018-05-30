=== MP-Books ===
Contributors: Will Roscoe, Edward Akerboom (opensource@infostreams.net)
Tags: mattering press, book, publisher, epub, pdf, mobi, open source
Requires at least: 4.4
Tested up to: 4.9.6
Stable tag: 1.0
License: MIT
License URI: http://www.opensource.org/licenses/mit-license.php

A wordpress 'book' plugin.

== Description ==

This plugin adds a 'Books' custom post type with additional meta boxes, various related widgets, and allows reading of epub files directly on your site. 

The epub processing/rendering part of this plugin is significantly based on an earlier version by Edward Akerboom (opensource@infostreams.net), who has kindly permitted his code to be adapted for this wordpress plugin.


== Installation ==

1. Upload the `mp-books` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Update your site's permalink settings to Custom Structure: /%category%/%postname%
4. Your theme will need to use the relevant hooks to display some of the book data


== Acknowledgements ==

The epub processing/rendering part of this plugin is significantly based on an earlier version by Edward Akerboom (opensource@infostreams.net), who has kindly permitted his code to be adapted for this wordpress plugin.


== 3rd Party libraries included ==

* lessphp - licensed under MIT license - http://leafo.net/lessphp
* PHP Simple HTML DOM Parser - licensed under MIT license - http://sourceforge.net/projects/simplehtmldom/
* BookGluttonEpub - licensed under MIT license - https://github.com/Vaporbook/BookGluttonEpub


== Changelog ==

= 1.0 =
* Initial production-ready version.