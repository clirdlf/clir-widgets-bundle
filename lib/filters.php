<?php

/**
 * Excerpt filters.
 *
 * @package CLIR_Widgets_Bundle
 */

/**
 * Filter the "read more" excerpt string link to the post.
 * @see https://developer.wordpress.org/reference/functions/the_excerpt/
 *
 * @param string $more "Read more" excerpt string.
 * @return string (Maybe) modified "read more" excerpt string.
 */
function wpdocs_excerpt_more($more)
{
    return sprintf(
        '<a class="read-more" href="%1$s"> %2$s</a>',
        get_permalink(get_the_ID()),
        __('Read More', 'textdomain')
    );
}
add_filter('excerpt_more', 'wpdocs_excerpt_more');
