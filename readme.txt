=== XML Documents ===
Contributors: mitchoyoshitaka
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com
Tags: custom post type, cpt, XML, XSLT, TEI, documents
Requires at least: 3.1
Tested up to: 3.2
Stable tag: 0.2

Support for managing XML documents as a custom post type and displaying them with XSLT stylesheets.

== Description ==

This plugin adds the necessary infrastructure to add a "XML Document"-type custom post type which renders an XML document with an XSLT stylesheet in lieu of the regular post content. This can be useful in custom applications where there is a need to display XML documents from source, but you also want the regular benefits of WordPress tagging, commenting, etc. for these documents.

This plugin will not work out of the box... it is an infrastructure plugin. Additional coding (albeit minor) is necessary to get it running. See the "Installation" section for more information and sample code.

The development of this plugin is supported by [MIT Global Shakespeares](http://globalshakespeares.org), where it will be used to render the full text of Shakespeare plays from TEI-XML source.

== Installation ==

1. Make sure you have PHP5 and [the PHP XSL module](http://us3.php.net/xsl) installed.
2. Install and activate this plugin.
3. Create a new custom post type with the `supports` attribute `xmldoc`. This custom post type will completely ignore its `post_content`, so make sure its `supports` statement does not include `editor`.
4. Place your stylesheet in your current theme's directory, named as `stylesheet.xsl`.
5. Create a new entity of your new post type. There will be an option to upload and choose an XML document (see screenshots). Do that, publish it, and view it, and you will see the XML document rendered with the XSLT as the content of that entry.

Here's some sample code:

	register_post_type('script',
		array(
			'label' => 'Scripts',
			'public' => true,
			'hierarchical' => false,
			'supports' => array('title', 'comments', 'xmldoc')
		)
	);

For more information on `register_post_type` and Custom Post Types, [visit the Codex](http://codex.wordpress.org/Post_Types).

== Frequently Asked Questions ==

= Your question here! =

Our answer here!

== Screenshots ==

1. A custom post type with XML Document support: here, "Scripts" for the display of Shakespeare scripts.
2. Selecting an uploaded XML Document as the document to be rendered.

== Changelog ==

= 0.2 =
* A couple bugfixes for the admin interface.

= 0.1 =
* Initial public release.

== To-do ==

* Custom stylesheets, per-document and/or custom parameters to be passed to the XSL transform
* Search integration
