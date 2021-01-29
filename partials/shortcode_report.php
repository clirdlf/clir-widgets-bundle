<?php

function wrap_line($result)
{
    return '<li><a href="'. $result->ID . '">' . $result->post_title . '</a></li>';
}

echo '<ul>';
foreach ($results as $result) {
    echo wrap_line($result);
}
echo '</ul>';
