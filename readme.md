# Wordpress 'book' plugin

A 'book' plugin for wordpress

## Description

This plugin adds a 'Books' custom post type with additional meta boxes, various related widgets, and allows reading of epub files directly on your site.

## Acknowledgements
The epub processing/rendering part of this plugin is significantly based on an earlier version by Edward Akerboom (opensource@infostreams.net), who has kindly permitted his code to be adapted for this wordpress plugin.


## Installation

1. Upload the `mp-books` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Update your site's permalink settings to Custom Structure: /%category%/%postname%
4. Your theme will need to use the relevant hooks to display some of the book data


## Changelog

= 1.0 =
* Initial production-ready version.
