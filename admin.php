<?php

// XML DOCS LIST TABLE

// Add the "files in group" column to the file groups edit screen
function xmldoc_columns( $columns, $post_type ) {
	if ( post_type_supports( $post_type, 'xmldoc' ) )
		$columns = array_merge(array_slice($columns,0,2),array('xmldoc'=>'XML Document'),array_slice($columns,2));
	return $columns;
}

// Displays the "files in group" column in the file groups edit screen
function xmldoc_column_list( $column_name, $post_ID ) {
	if ( $column_name == 'xmldoc' && $xml_ID = get_post_meta( $post_ID, '_xml', true ) ) {
		$file = get_post_meta( $xml_ID, '_wp_attached_file', true);
		$file = get_option('upload_path') . '/' . $file;
		if ( file_exists( $file ) )
			echo '<a href="' . esc_html( wp_get_attachment_url( $xml_ID ) ) . '">' . esc_html( get_the_title($xml_ID) ) . '</a></p>';
	}
}

// XML DOCS EDIT SCREEN

// Add XML document meta box
function xmldoc_register_meta_box() {
	global $post_type;
	if ( post_type_supports( $post_type, 'xmldoc' ) ) {
		add_meta_box( 'xml-document', 'XML Document', 'xmldoc_meta_box', $post_type, 'normal', 'core' );
		add_thickbox();
		wp_enqueue_script('media-upload');
		$src = plugins_url( 'admin.js', __FILE__ );
		wp_enqueue_script( 'xml-document-admin', $src, array( 'jquery' ) , '1.0', true );
	}
}

function xmldoc_meta_box() {
	global $post;	
	$xml = get_post_meta( $post->ID, '_xml', true );
//	$xslt = get_post_meta( $post->ID, '_xslt', true );
	echo _xmldoc_document_html( $xml );
}

function _xmldoc_document_html( $xml_ID ) {
	global $content_width, $_wp_additional_image_sizes, $post_ID;

	$set_thumbnail_link = '<p class="hide-if-no-js"><a title="' . esc_attr( 'Set XML document' ) . '" href="' . esc_url( get_upload_iframe_src('media') ) . '" id="set-xml-document" class="thickbox">%s</a></p>';
	$content = sprintf($set_thumbnail_link, esc_html( 'Set XML document' ));

	$file = get_post_meta( $xml_ID, '_wp_attached_file', true);
	$abspath = get_option('upload_path') . '/' . $file;
	if ( $file && file_exists( $abspath ) )
		$content .= '<p><img src="' . admin_url('images/yes.png') . '" alt="XML document specified"/> XML document specified: <a href="' . esc_html( wp_get_attachment_url( $xml_ID ) ) . '">' . esc_html( get_the_title($xml_ID) ) . '</a></p>';

	return $content;
}

// XML DOCUMENT MEDIA ITEM MODS
function xmldoc_media_form_enqueue( $page ) {
	if ( 'media-upload-popup' != $page )
		return;
	$src = plugins_url( 'set-xml-document.js', __FILE__ );
	wp_enqueue_script( 'set-xml-document', $src, array( 'jquery' ) , '1.0', true );
}

function xmldoc_media_form_fields($form_fields, $post) {
	if ( $post->post_mime_type == 'application/xml' ) {
		$attachment_id = $post->ID;
		$calling_post_id = 0;
		if ( isset( $_GET['post_id'] ) )
			$calling_post_id = absint( $_GET['post_id'] );
		elseif ( isset( $_POST ) && count( $_POST ) ) // Like for async-upload where $_GET['post_id'] isn't set
			$calling_post_id = $post->post_parent;
		if ( $calling_post_id ) {
			$ajax_nonce = wp_create_nonce( "set_xml_document-$calling_post_id" );
			$form_fields['buttons'] = array( 'tr' => "\t\t<tr class='submit'><td></td><td class='savesend'><a class='wp-xml-document' id='wp-xml-document-{$attachment_id}' href='#' onclick='WPSetAsXMLDoc(\"$attachment_id\", \"$ajax_nonce\");return false;'>" . esc_html( "Use as XML document" ) . "</a></td></tr>\n" );
		}
	}
	return $form_fields;
}

function xmldoc_set_xml_document() {
	global $post_ID;
	$post_ID = $_POST['post_id'];
	$xml_ID = $_POST['xml_id'];
	if ( isset($post_ID) && check_ajax_referer( "set_xml_document-$post_ID", '_ajax_nonce' ) && isset($xml_ID) )
		update_post_meta( $post_ID, '_xml', $xml_ID );
	die( _xmldoc_document_html( $xml_ID ) );
}
