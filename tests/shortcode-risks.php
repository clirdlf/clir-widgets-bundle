<?php
/** Regression checks using WordPress's escaping and HTML parser. */
$assert = static function ( $condition, $message ) {
    if ( ! $condition ) {
        throw new RuntimeException( $message );
    }
};
$attributes = static function ( $html, $tag ) {
    $parser = new WP_HTML_Tag_Processor( $html );
    if ( ! $parser->next_tag( $tag ) ) {
        throw new RuntimeException( 'Missing expected element: ' . $tag );
    }
    return $parser;
};

// Quotes must remain attribute text; URL protocols and CSS dimensions are bounded.
$payload = '" onload="alert(1)';
$html = iframe( array( 'src' => 'https://example.org/embed?a=1&b=2', 'title' => $payload, 'width' => '1; color:red', 'height' => '100%', 'allow' => $payload ) );
$parser = $attributes( $html, 'IFRAME' );
$assert( $payload === $parser->get_attribute( 'title' ), 'Iframe title was not escaped as text.' );
$assert( null === $parser->get_attribute( 'onload' ), 'Iframe allowed an injected event attribute.' );
$assert( '800' === $parser->get_attribute( 'width' ) && '100%' === $parser->get_attribute( 'height' ), 'Iframe dimension validation failed.' );
$assert( '' === iframe( array( 'src' => 'javascript:alert(1)' ) ), 'Iframe accepted an executable URL.' );
$assert( '' === iframe( array() ), 'Missing iframe URL did not return empty output.' );

foreach ( array( null, '', array(), 'javascript:alert(1)', 'https://example.org/file.svg', 'https://example.org/no-image' ) as $content ) {
    $assert( '' === image_frame( array(), $content ), 'Invalid image content was accepted.' );
}
foreach ( array( 'JPG', 'png', 'gif', 'webp', 'avif' ) as $extension ) {
    $url = 'https://example.org/image.' . $extension . '?token=abc#preview';
    $html = image_frame( array( 'title' => $payload, 'alt' => $payload, 'width' => '1; color:red', 'caption' => '<script>alert(1)</script>' ), $url );
    $parser = $attributes( $html, 'IMG' );
    $assert( $url === $parser->get_attribute( 'src' ), 'Unknown attachment did not preserve its original URL.' );
    $assert( $payload === $parser->get_attribute( 'alt' ) && null === $parser->get_attribute( 'onload' ), 'Image attributes were not escaped.' );
    $assert( null === $parser->get_attribute( 'width' ), 'Invalid image dimension was emitted.' );
    $assert( false === strpos( $html, '<script>' ) && false === strpos( $html, '-150x150' ), 'Image emitted markup or guessed a thumbnail.' );
}

// Exercise the media API contract without creating database records or files.
$lookup = static function ( $id, $url ) {
    return 'https://example.org/known.jpg' === $url ? 123 : $id;
};
$size = static function ( $result, $id, $requested ) {
    return 123 === $id && 'thumbnail' === $requested ? array( 'https://example.org/known-thumb.jpg', 150, 150, true ) : $result;
};
add_filter( 'pre_attachment_url_to_postid', $lookup, 10, 2 );
add_filter( 'image_downsize', $size, 10, 3 );
try {
    $parser = $attributes( image_frame( array( 'width' => '240', 'height' => '120' ), 'https://example.org/known.jpg?cache=1' ), 'IMG' );
    $assert( 'https://example.org/known-thumb.jpg' === $parser->get_attribute( 'src' ), 'Attachment thumbnail was not selected.' );
    $assert( '240' === $parser->get_attribute( 'width' ) && '120' === $parser->get_attribute( 'height' ), 'Valid image dimensions were lost.' );
} finally {
    remove_filter( 'pre_attachment_url_to_postid', $lookup, 10 );
    remove_filter( 'image_downsize', $size, 10 );
}
$assert( '10000' === clir_shortcode_dimension( '10000' ) && '' === clir_shortcode_dimension( '10001' ) && '' === clir_shortcode_dimension( '0' ), 'Pixel dimension bounds failed.' );
$assert( '100%' === clir_shortcode_dimension( '100%', '', true ) && '' === clir_shortcode_dimension( '101%', '', true ), 'Percentage dimension bounds failed.' );

foreach ( array( null, '', array(), 'invalid-address', '<script>@example.org' ) as $content ) {
    $assert( '' === hide_email( array(), $content ), 'Invalid email did not return a string fallback.' );
}
$parser = $attributes( hide_email( array(), ' example@example.org ' ), 'A' );
$assert( 'mailto:example@example.org' === $parser->get_attribute( 'href' ), 'Email obfuscation changed the address.' );

$original = array( 'extended_valid_elements' => 'span[data-example],a[rel]', 'unrelated' => 'preserve' );
$merged = add_iframe( $original );
$assert( str_starts_with( $merged['extended_valid_elements'], $original['extended_valid_elements'] . ',' ) && 'preserve' === $merged['unrelated'], 'TinyMCE settings were overwritten.' );
$assert( $merged === add_iframe( $merged ), 'TinyMCE iframe rule was duplicated.' );
$custom = array( 'extended_valid_elements' => 'iframe[data-custom|src],span[class]' );
$assert( $custom === add_iframe( $custom ), 'Existing iframe configuration was replaced.' );

echo "Shortcode escaping, missing-input, dimension, email and editor regression checks passed.\n";
