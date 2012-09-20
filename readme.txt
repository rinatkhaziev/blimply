=== Blimply ===
Contributors: rinatkhaziev, doejo
Tags: push, urban airship, notifications, widget
Requires at least: 3.3
Tested up to: 3.4.2
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Blimply is a plugin that will allow you to send push notifications to your mobile users utilizing Urban Airship API. 

== Description ==

You will need an [Urban Airship](http://urbanairship.com/) account in order to be able to use this plugin. The plugin features the ability to make a push for posts/pages/custom post types, and a handy Dashboard widget.


== Installation ==

1. Install PEAR_Info pear package `pear install PEAR_Info`
1. Install HTTP_Request pear package `pear install HTTP_Request` (this is a dependency for Urban Airship PHP SDK)
1. Upload `blimply` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Set application key and application secret in 'Settings' -> 'Blimply Settings'
1. Enjoy responsibly

== Frequently Asked Questions ==

None yet

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the directory of the stable readme.txt, so in this case, `/tags/4.3/screenshot-1.png` (or jpg, jpeg, gif)
2. This is the second screen shot

== Changelog ==

= 0.1 =

* Initial release

== Frequently Asked Questions ==

= Why do I need to install PEAR packages = 

This plugin uses official Urban Airship PHP SDK. The SDK itself uses PEAR package HTTP_Request to implement REST Client. Without PEAR_Info and HTTP_Request the plugin simply won't be functional.
I used Urban Airship to face tight deadline for the initial release. I might refactor the REST client in future releases, but for now, these PEAR packages are required for the plugin to work.
