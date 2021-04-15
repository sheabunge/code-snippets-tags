=== Code Snippets Tags ===
Contributors: bungeshea
Donate link: http://code-snippets.bungeshea.com/donate/
Tags: code-snippets-plugin, snippets, tags, category, organization, code, gui,
Requires at least: 3.3
Tested up to: 4.0
Stable tag: 1.2.1
License: MIT
License URI: http://opensource.org/licenses/MIT

Organize your code snippets with tags. Adds support to the Code Snippets WordPress plugin for adding tags to snippets.

== Description ==

> In Code Snippets version 2.0 or later, tags functionality is built in and this plugin is redundant

Adds support to the Code Snippets WordPress plugin for adding tags to snippets. Requires [Code Snippets](http://wordpress.org/plugins/code-snippets) 1.8 or later.

You can assign tags to snippets using an interactive UI when editing or adding a new snippet. Then, you can filter snippets by tag on the snippets table, or see what tags are assigned to a particular snippet with a glance at the new table column. Tags are stored in your database and can be exported and imported along with the other snippet data.

Contribute to the plugin development on [GitHub](https://github.com/sheabunge/code-snippets-tags).

== Installation ==

This plugin extends the functionality of [Code Snippets](http://wordpress.org/plugins/code-snippets), and requires Code Snippets version 1.8 or greater to be installed in order to work.

= Automatic installation =

1. Log into your WordPress admin
2. Click __Plugins__
3. Click __Add New__
4. Search for __Code Snippets Tags__
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

= 1.2.1 =
* Fixed an incorrect filter hook preventing snippet tags from saving

= 1.2 =
* Fixes for Code Snippets 1.8
* Make sure plugin cannot be loaded with an older version of Code Snippets

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

= 1.2.1 =
Fixes a bug preventing tags from saving

= 1.2 =
Fixes for Code Snippets 1.8

= 1.1 =
Added German translation thanks to David Decker; database improvements
