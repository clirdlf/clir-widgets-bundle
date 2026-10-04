<?php
/** Generate a controlled browser page from actual WordPress shortcode output. */
require_once __DIR__ . '/bootstrap.php';
$directory = dirname( __DIR__ ) . '/build/browser';
wp_mkdir_p( $directory );
$image = imagecreatetruecolor( 400, 300 );
imagepng( $image, $directory . '/image.png' );
file_put_contents( $directory . '/embed.html', '<!doctype html><title>Local embed</title><p id="embedded">Embedded fixture</p>' );
$caption = 'Caption <b>literal</b> & text';
$html = do_shortcode( '[image_frame width="180" height="120" alt="Browser fixture" caption="' . $caption . '"]http://127.0.0.1:8081/image.png[/image_frame]' );
$html .= do_shortcode( '[iframe src="http://127.0.0.1:8081/embed.html" title="Local embed" width="320" height="180"]' );
$html .= do_shortcode( '[email]example@example.org[/email][clearboth]' );
$script = <<<'JS'
<script>
window.addEventListener('load', () => {
    const failures = [];
    const check = (condition, message) => { if (!condition) failures.push(message); };
    const img = document.querySelector('figure img');
    check(img && img.complete && img.naturalWidth === 400, 'Image did not load');
    check(img && img.getBoundingClientRect().width === 180 && img.getBoundingClientRect().height === 120, 'Image dimensions changed');
    const figure = document.querySelector('figure');
    check(figure && getComputedStyle(figure).maxWidth === '180px', 'Figure maximum width missing');
    const caption = document.querySelector('figcaption');
    check(caption && caption.textContent === 'Caption <b>literal</b> & text' && !caption.querySelector('b'), 'Caption became markup');
    const frame = document.querySelector('iframe');
    check(frame && frame.clientWidth === 320 && frame.clientHeight === 180, 'Iframe dimensions changed');
    check(frame && frame.contentDocument && frame.contentDocument.querySelector('#embedded'), 'Iframe did not load');
    check(document.querySelector('a').getAttribute('href') === 'mailto:example@example.org', 'Email link changed');
    const result = document.createElement('pre');
    result.id = 'clir-browser-result';
    result.textContent = failures.length ? 'FAIL: ' + failures.join('; ') : 'PASS';
    document.body.appendChild(result);
});
</script>
JS;
file_put_contents( $directory . '/index.html', '<!doctype html><html><head><meta charset="utf-8"><title>CLIR shortcode browser fixture</title></head><body>' . $html . $script . '</body></html>' );
echo "Browser fixture generated in build/browser.\n";
