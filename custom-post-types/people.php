<?php

/**
* Custom post types for People
*/

function clir_register_post_types()
{
  $defaults = array(
    'public' => true,
    'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt', 'custom-background'),
    'menu_position' => 20,
    'hierarchical' => false,
    'has_archive' => true,
    'show_in_nav_menus' => true
  );

  $post_types = array(
    'people' => array(
      'labels' => array(
        'name' => __( 'People' ),
        'singular_name' => __( 'Person' )
      ),
      'rewrite' => array('slug' => 'people')
    );

    foreach($post_types as $name => $args){
      $args = array_merge($args, $defaults);
      register_post_type($name, $args);
    }
  }

  add_action('init', clir_register_post_types);
