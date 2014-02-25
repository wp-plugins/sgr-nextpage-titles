=== Multipage Plugin ===
Contributors: SGr33n
Donate link: http://goo.gl/QuRfT
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: multipage, nextpage, subpages, seo, posts, page, index, shortcode
Requires at least: 3.0
Tested up to: 3.8
Stable tag: 1.0.1

Order your posts into subpages: multipage posts will have a table of contents linking single subpages with their titles.

== Description ==

Multipage Plugin for WordPress will give you the ability to order a post in multipages, giving each subpage a title and having a table of contents on the first or on every subpage.

= Make order in your posts! =
Forget about too long posts, infinite height pages, diffculty to found a post section. With sGR Nextpage Titles you can divide every post into many subpages, giving them a title. Then a table of contents will appear to redirect your visitors to the desidered subpage of the post.

= Benefit from different subpages =
Every subpage will generate a different page view, so your statistics will benefit.

= Forget about the old `&lt;!--nextpage--&gt;` =
The old WordPress code is not useful anymore. Even if you will not indicate a title for your `[nextpage]` code, sGR Nextpage Titles will use a default title in the form "Page #".

= Make we know you care =
Please make we know you care about sGR Nextpage Titles plugin development rating it (5 stars).

== Installation ==

1. Upload the `sgr-nextpage-titles` folder to the `/wp-content/plugins/` directory
2. Activate the sGR Nextpage Titles Plugin through the 'Plugins' menu in WordPress
3. Configure the plugin by going to the `Multipage` menu under `Settings`
4. Add a few `[nextpage title="Pretty title"]` codes to your posts

== Screenshots ==

1. Here you can see how to use sGR Nextpage Titles shortcodes. It's very simple.
2. A sample page using sGR Nextpage Titles plugin.
3. The configuration page placed under "Settings > Multipage".

== Changelog ==

= 1.0.1 =

* Bug fix:
	* Loads default values even if never saved settings. 

= 1.0 =

* New Features:
	* Added the option to hide comments on all the subpages except in the first one.
	* Added new options to customize the table of contents: "Hide the new TOC header", "Add a link for comments", "Show only on the first page", "Label choices", "Show before or after the content", "Hide it".
* Enhancements:
	* Completely rewritten part of the main code in order to improve performances.
	* Changed some classes name (maybe you need to correct your customized css).
	* The settings menu is now named "Multipage" (still under "Settings").	
* Bug fix:
	* Now multipage posts will appear correctly in non is_single() pages even if there is no `<!--more-->` code. 
* i18n:
	* Updated .pot file
    * Updated Italian (it_IT)
    * Updated Deutsch (de_DE)

= 0.94 =
* Bug fix:
	* Fixed a bug that returned, in some conditions (there is no intro title), 404 on the last page. 

= 0.93 =
* New Features:
	* Now you can configure some summary appearance options. The admin menu is under "Settings".

= 0.92 =
* Bug fix:
	* Now returns 404 error if the requested page number doesn't exists. It works also if the requested page is 1, because the real permalink is the base one.

= 0.91 =
* Enhancements:
	* Updated "Tested up to" with the new WordPress 3.8.
	* Added language support for German (thanks to <a rel="nofollow" href="http://wordpress.org/support/profile/myigel">Igor Scheller</a>).

= 0.90 =
* Bug fix:
	* Now you can navigate through the pages also in previews.
* Enhancements:
	* RTL first support.
	* Customized css automatic load (put your nextpage-titles.css in WordPress theme/child-theme css directory ex. /wp-content/themes/twentythirteen/css/nextpage-titles.css)

= 0.85 =
* Bug fix:
	* Fix a permalink bug with structures without a slash at the end.

= 0.82 =
* Bug fix:
	* Removed a deprecated function that in some conditions generated errors. 

= 0.8 =
* Bug fix:
	* Definitively solved the 404 error, caused by pretty urls that are now deprecated (eventually waiting for a 2.0 working version) in favor of native page numbers. Different subpages will have now the same link of the original `<!--nextpage-->` code. 
* New Features *
	* Added the initial code for the configuration page (not active yet).

= 0.7 =
* Bug fix:
	* Correct a bug that sometimes displayed summary on loop pages.
* New Features *
	* Possibility to give a different name to "intro", just create a nextpage title shortcode on the first line of the editor.

= 0.6.3 =
* Bug fix:
	* Version numbers, somebody couldn't update it. Now it's ok.

= 0.6 =
* New Features:
	* Quite completely rewritten! now uses the internal core of WordPress nextpage original code.
* Bug fix:
	* Many...
	* Unfortunately I had to modify the subpage pretty link for permalinks due to conflicts with attachment pages, now subpages have 'subpage-' prefix.
	* Now works with all post_types, anyway pretty url works only on "post".

= 0.55 =
* Bug fix:
	* Empty page doesn't return notices (debug mode on) anymore.
	* Bloked sGR Nextpage Titles use on post_types different from posts cause permalinks doesn't work yet.

= 0.50 =
* New features:
	* The summary is now linked as a page.
	* Added next & prev link rel to the head.
* Enhancements:
	* Rewrite the code to request parts via pagenumber.
	* Made changes to bottom links style.
* Bug fix:
	* Uncorrect pagetitle now returns 404.

= 0.38 =
* New features:
	* Added previous/next page links to the bottom.
	* Added language files support and the italian translation.

= 0.30 =
* New features:
	* The subpage title is now part of the page title (head & html).
* Enhancements:
	* Added code to load translations (even if there are no words to translate yet).
	* A few code enhancements.
	* No more needed to flush rewrite rules after activation.

= 0.22 =
* Initial beta release.

= To Do Release 2.0 =
* Pretty urls (not sure).
