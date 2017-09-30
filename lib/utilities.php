<?php
/**
 * Create an excerpt of an arbitrary length for a given Post
 *
 * @see https://codex.wordpress.org/Function_Reference/get_the_excerpt
 *
 * @param Post $post Post to generate excerpt from
 * @param int  $length Length of the excerpt
 *
 * @return String Excerpt of length $length
 */
 function the_excerpt_max_charlength($post, $charlength)
 {
     $excerpts = get_the_excerpt($post['ID']);
     $excerpt = $excerpts[0];
     $charlength++;

     if (mb_strlen($excerpt) > $charlength) {
         $subex = mb_substr($excerpt, 0, $charlength - 5);
         $exwords = explode(' ', $subex);
         $excut = - (mb_strlen($exwords[ count($exwords) - 1 ]));
         if ($excut < 0) {
             echo mb_substr($subex, 0, $excut);
         } else {
             echo $subex;
         }
         echo '[...]';
     } else {
         echo $excerpt;
     }
 }
