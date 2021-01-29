<?php
require_once 'utilities.php';

require_once 'magee_transition/ms_button.php';
require_once 'magee_transition/ms_column.php';
require_once 'magee_transition/ms_custombox.php';
require_once 'magee_transition/ms_divider.php';
// require_once 'magee_transition/ms_dropcap.php';
require_once 'magee_transition/ms_icon.php';
require_once 'magee_transition/ms_image_banner.php';
require_once 'magee_transition/ms_quote.php';
require_once 'magee_transition/ms_row.php';
require_once 'magee_transition/ms_timeline_item.php';
require_once 'magee_transition/ms_timeline.php';

// counter
// flipbox
// featurebox
// accordion
// accordion item
// expand

function register_ms_shortcodes()
{
      add_shortcode('ms_button', 'ms_button');
      add_shortcode('ms_column', 'ms_column');
      add_shortcode('ms_custombox', 'ms_custombox');
      add_shortcode('ms_divider', 'ms_divider');
      // add_shortcode('ms_dropcap', 'ms_dropcap');
      add_shortcode('ms_icon', 'ms_icon');
      add_shortcode('ms_image_banner', 'ms_image_banner');
      add_shortcode('ms_quote', 'ms_quote');
      add_shortcode('ms_row', 'ms_row');
      add_shortcode('ms_timeline_item', 'ms_timeline_item');
      add_shortcode('ms_timeline', 'ms_timeline');
}

add_action('init', 'register_ms_shortcodes');
