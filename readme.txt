=== SGR Nextpage Titles ===
Contributors: SGr33n
Donate link: http://goo.gl/QuRfT
Tags: nextpage, seo, subpages
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 0.6.1
License: GPLv2

Make order in your posts. With SGR Nextpage Titles you can have post subpages with their own titles and a navigation index.

== Description ==

SGR Nextpage Titles is a WordPress plugin that will make you forget the old `<!--nextpage-->` code because it allows to add more subpages of a post, giving each subpage a title.
Then SGR Nextpage Titles will show an index, that will link to each subpage found in the post, if shortcode is present. It will show on every page, in order to easily navigate between subpages.

= Make order in your posts! =
Forget about too long posts, infinite height pages, diffculty to found a post section. With SGR Nextpage Titles you can divide every post into different subpages, giving them a title and obtaining an index that will show the summary of the post and will redirect your visitors to the desidered section (subpage) of your post.

= Have SEO friendly url for your subpages =
Every subpage will have SEO friendly urls based on the given title, to make also happy Search Engines and spider bots that will navigate through your Blog pages.

= Forget about the old `&lt;!--nextpage--&gt;` =
Even if you will not indicate a title for your `[nextpage]` code, SGR Nextpage Titles will use a default title in the form "page-n" where "n" is the number of the page.

= Make we know you care =
Please make we know you care about SGR Nextpage Titles plugin development rating it (5 stars).

== Installation ==

1. Upload the `sgr-nextpage-titles` folder to the `/wp-content/plugins/` directory
2. Activate the SGR Nextpage Titles Plugin through the 'Plugins' menu in WordPress
3. Add few `[nextpage title="Pretty title"]` codes to your posts

== Screenshots ==

1. Here you can see how to use SGR Nextpage Titles shortcodes. It's very simple.
2. A sample page using SGR Nextpage Titles plugin.

== Changelog ==

= 0.60 =
* New Features:
	* Quite completely rewritten! now uses the internal core of WordPress nextpage original code.
* Bug fix:
	* Many...
	* Unfortunately I had to modify the subpage pretty link for permalinks due to conflicts with attachment pages, now subpages have 'subpage-' prefix.
	* Now works with all post_types, anyway pretty url works only on "post".

= 0.55 =
* Bug fix:
	* Empty page doesn't return notices (debug mode on) anymore.
	* Bloked SGRNp use on post_types different from posts cause permalinks doesn't work yet.

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

= To Do Release 1.0 =
* Legacy mode.
* Settings page.
* Make possible to change the 'subpage-' prefix.
