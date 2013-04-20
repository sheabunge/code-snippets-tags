=== Code Snippets Tags ===
Contributors: bungeshea
Donate link: http://code-snippets.bungeshea.com/donate/
Tags: code-snippets, snippets, tags, category, organization
Requires at least: 3.3
Tested up to: 3.5.1
Stable tag: 1.0
License: MIT
License URI: http://opensource.org/licenses/mit-license.php

Organize your code snippets with tags. Adds support to the Code Snippets WordPress plugin for adding tags to snippets.

== Description ==

Adds support to the Code Snippets WordPress plugin for adding tags to snippets. Requires [Code Snippets](http://wordpress.org/extend/plugins/code-snippets) 1.7 or later.

You can assign tags to snippets using an interactive UI when editing or adding a new snippet. Then, you can filter snippets by tag on the snippets table, or see what tags are assigned to a particular snippet with a glance at the new table column. Tags are stored in your database, and can be exported and imported along with the other snippet information.

Visit the [plugin homepage](http://code-snippets.bungeshea.com/plugins/tags/) or contribute to its development on [GitHub](https://github.com/bungeshea/code-snippets-tags).

== Installation ==

This plugin extends the functionality of [Code Snippets](http://wordpress.org/extend/plugins/code-snippets), and requires Code Snippets version 1.7 or greater to be installed in order to work.

= Automatic installation =

1. Log into your WordPress admin
2. Click __Plugins__
3. Click __Add New__
4. Search for __Code Snippets__
5. Click __Install Now__ under "Code Snippets Tags"
6. Activate the plugin

= Manual installation =

1. Download the plugin
2. Extract the contents of the zip file
3. Upload the contents of the zip file to the `wp-content/plugins/` folder of your WordPress installation
4. Activate the "Code Snippets Tags" plugin from 'Plugins' page.

== Screenshots ==

1. The tags column in the snippets table
2. Filtering snippets based on tag
3. Editing a snippet's tags

== Changelog ==

= 1.1 =
* Tags are now stored in database as comma-separated values - no more serialized arrays!
* Improved database table creation process (now requires Code Snippets 1.7.1)
* Added German translation thanks to [David Decker](http://deckerweb.de/)
* Make sure **nothing** is loaded before main Code Snippets plugin

= 1.0 =
* Added table column to snippets menu
* Added tags field to single snippet menu
* Added tag filter dropdown to snippets menu
* Link tags in table column to relevant tag filter

== Upgrade Notice ==

= 1.1 =
* Added German translation thanks to David Decker; database improvements
