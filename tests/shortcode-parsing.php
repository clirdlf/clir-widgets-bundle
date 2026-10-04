<?php
/** Exercise WordPress's parser, not just direct callback invocation. */
$parser = clir_test_element( do_shortcode( '[iframe src="https://example.org/embed?a=1&b=2" title="An embedded page" width="75%" height="240"]' ), 'IFRAME' );
clir_test_assert( 'https://example.org/embed?a=1&b=2' === $parser->get_attribute( 'src' ), 'Shortcode parsing changed the iframe URL.' );
clir_test_assert( 'An embedded page' === $parser->get_attribute( 'title' ) && '75%' === $parser->get_attribute( 'width' ) && '240' === $parser->get_attribute( 'height' ), 'Shortcode parsing lost iframe attributes.' );
clir_test_assert( '' === do_shortcode( '[iframe src="javascript:alert(1)"]' ), 'Parsed iframe accepted an executable URL.' );

$html = do_shortcode( '[image_frame alt="A sample image" caption="Caption &amp; text" width="180" height="120" style="sample image"]https://example.org/image.png?version=2[/image_frame]' );
$parser = clir_test_element( $html, 'IMG' );
clir_test_assert( 'https://example.org/image.png?version=2' === $parser->get_attribute( 'src' ), 'Enclosed shortcode content was changed.' );
clir_test_assert( 'A sample image' === $parser->get_attribute( 'alt' ) && 'sample image' === $parser->get_attribute( 'class' ), 'Parsed image attributes were lost.' );
clir_test_assert( '180' === $parser->get_attribute( 'width' ) && '120' === $parser->get_attribute( 'height' ), 'Parsed image dimensions were lost.' );
clir_test_assert( str_contains( $html, 'Caption &amp; text' ), 'Caption entities were double escaped.' );
foreach ( array( '[image_frame /]', '[image_frame][/image_frame]', '[email /]', '[email]invalid[/email]' ) as $shortcode ) {
    clir_test_assert( '' === do_shortcode( $shortcode ), 'Missing or invalid parsed content was accepted: ' . $shortcode );
}
$parser = clir_test_element( do_shortcode( '[email] example@example.org [/email]' ), 'A' );
clir_test_assert( 'mailto:example@example.org' === $parser->get_attribute( 'href' ), 'Parsed email address was changed.' );
$html = do_shortcode( '[clearboth][iframe src="https://example.org/one"][iframe src="https://example.org/two"]' );
clir_test_assert( 2 === substr_count( $html, '<iframe ' ) && str_contains( $html, 'clearfix' ), 'Adjacent shortcode instances interfered with each other.' );
clir_test_assert( '[iframe src="https://example.org/example"]' === do_shortcode( '[[iframe src="https://example.org/example"]]' ), 'Escaped shortcode example was rendered.' );
echo "WordPress shortcode parsing checks passed.\n";
