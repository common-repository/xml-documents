<?php
/*
Plugin Name: XML Documents
Plugin URI: http://mitcho.com/code/
Description: Support for managing XML documents as a custom post type and displaying them with XSLT stylesheets
Version: 0.2
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com
*/

include( dirname(__FILE__) . '/admin.php' );

// Content filter: render XML
add_filter( 'the_content', 'xmldoc_render' );

// Support uploading of XML
add_filter( 'upload_mimes', 'add_xml_mime' );
add_filter( 'wp_mime_type_icon', 'xml_mime_type_icon', 10, 3 );

// Setup XML document edit screen:
// hook into dbx_post_advanced instead of add_meta_boxes so we get in earlier
add_action( 'dbx_post_advanced', 'xmldoc_register_meta_box' );

// Modify Media upload screen for XML documents
add_action( 'admin_enqueue_scripts', 'xmldoc_media_form_enqueue', 10, 1 );
add_filter( 'attachment_fields_to_edit', 'xmldoc_media_form_fields', 10, 2 );
add_action( 'wp_ajax_set-xml-document', 'xmldoc_set_xml_document' );

// Add custom columns to the listing of file groups
add_filter( 'manage_posts_columns', 'xmldoc_columns', 10, 2 );
add_action( 'manage_posts_custom_column', 'xmldoc_column_list', 10, 2 );

function add_xml_mime($mimes) {
  $mimes['xml'] = 'application/xml';
  return $mimes;
}
function xml_mime_type_icon($icon, $mime, $post_id) {
	if ( $mime == 'application/xml' || $mime == 'text/xml' )
		return wp_mime_type_icon('document');
	return $icon;
}

function xmldoc_get_option( $option, $default = false ) {
	$options = get_option( 'xmldoc', array() );
	if ( isset( $options[$option] ) )
		return $options[$option];
	return $default;
}

// XML DOCUMENT DISPLAY
function xmldoc_render( $content ) {
	global $post;

	if ( !post_type_supports( $post->post_type, 'xmldoc' ) )
		return $content;

	if ( !class_exists( 'XSLTProcessor' ) || !class_exists( 'DOMDocument' ) )
		return 'XML and XSLT processing is not supported by your PHP installation. Please install <a href="http://www.php.net/manual/en/book.xsl.php">the PHP XSL module</a>.';

	if ( !$xml_ID = get_post_meta( $post->ID, '_xml', true ) )
		return 'XML document not set.';

	$xml = get_post_meta( $xml_ID, '_wp_attached_file', true);
	$xml = get_option('upload_path') . '/' . $xml;
	if ( !file_exists( $xml ) )
		return 'XML document not found.';	

	$xslt_ID = get_post_meta( $post->ID, '_xslt', true);
	// If there's a document-specific XSLT set...
	if ( isset( $xslt ) ) {
		if ( is_int( $xslt ) ) {
			// if it's an int, it's an attachment ID.
			// THIS IS NOT SUPPORTED YET, or at least there's no UI for it.
			$xslt = get_post_meta( $xslt_ID, '_wp_attached_file', true);
			$xslt = get_option('upload_path') . '/' . $xslt;
		} else {
			// else in current theme dir
			$xslt = STYLESHEETPATH . '/' . $xslt;
		}
	} else {
		$xslt = STYLESHEETPATH . '/stylesheet.xsl';
	}
	if ( !file_exists( $xslt ) )
		return 'XSLT stylesheet not found.';	
	
	$xslDoc = new DOMDocument();
	$xslDoc->load($xslt);
	
	$xmlDoc = new DOMDocument();
	$xmlDoc->load($xml);

	$proc = new XSLTProcessor();

	$proc->importStylesheet($xslDoc);			
	return $proc->transformToXML($xmlDoc);
}
