# Blimply

## Description

Blimply is a plugin that will allow you to send push notifications to your mobile users utilizing Urban Airship API. [Urban Airship](http://urbanairship.com/) account in order to be able to use this plugin. The plugin features the ability to make a push for posts/pages/custom post types, and a handy Dashboard widget.

## Installation

1. `git clone https://github.com/rinatkhaziev/blimply.git` in your WP plugins directory
1. Navigate to freshly created folder and do `git submodule update --init --recursive`
1. Make sure that PEAR_Info and HTTP_Request PEAR packages are installed
1. Configure Urban Airship API key and secret (note that it should be master secret)
1. (Optional) Set up Urban Airship tags

## Disclaimer

This plugin is under active development. Reports of any issues and pull requests are welcome!

## Future improvements
* Multiple Airship apps support
* Rich Push
* Get rid of PEAR dependencies. Refactor RESTful client to use WordPress core HTTP API