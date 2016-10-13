<?php


 /**
  * Adds social media links.
  */
 class Social_Media_Links extends WP_Widget
 {
     /*
      * Register widget with WordPress.
      */
      public function __construct()
      {
          parent::__construct(
                'Social_Media_Links', // Base ID
                __('Social Media Links', 'clir-widgets-bundle'), // Name
                array('description' => __('Adds social media links for the site.', 'clir-widgets-bundle')) // Args
            );
      }

      /**
      * Overrides alchem_get_social
      * @see alchem_get_social
      */
     public function clir_get_social( $position, $class = 'top-bar-sns',$placement='top',$target='_blank')
     {
         global $alchem_social_icons;
         $return = '';
         $rel = '';

         $social_links_nofollow = alchem_option('social_links_nofollow', 'no');
         $social_new_window = alchem_option('social_new_window', 'yes');
         if ($social_new_window == 'no') {
             $target = '_self';
         }

         if ($social_links_nofollow == 'yes') {
             $rel = 'nofollow';
         }

         if (is_array($alchem_social_icons) && !empty($alchem_social_icons)):
            $return .= '<ul class="'.esc_attr($class).'">';
         $i = 1;
         foreach ($alchem_social_icons as $sns_list_item) {
             $icon = alchem_option($position.'_social_icon_'.$i, '');
             $title = alchem_option($position.'_social_title_'.$i, '');
             $link = alchem_option($position.'_social_link_'.$i, '');
             if ($icon != '') {
                 $return .= '<li><a target="'.esc_attr($target).'" rel="'.$rel.'" href="'.esc_url($link).'" data-placement="'.esc_attr($placement).'" data-toggle="tooltip" title="'.esc_attr($title).'"><i class="fa fa-'.esc_attr($icon).'"></i> '.esc_attr($title) .'</a></li>';
             }
             ++$i;
         }
         $return .= '</ul>';
         endif;

         return $return;
     }

     /**
      * Front-end display of widget.
      *
      * @see WP_Widget::widget()
      *
      * @param array $args     Widget arguments
      * @param array $instance Saved values from database
      */
     public function widget($args, $instance)
     {
         if (array_key_exists('before_widget', $args)) {
             echo $args['before_widget'];
         }

         echo '<h2 class="widget-title">'.$instance['title'].'</h2>';
         echo self::clir_get_social('footer', 'list-unstyled clir-social', 'left');

         if (array_key_exists('after_widget', $args)) {
             echo $args['after_widget'];
         }
     }

     /**
      * Back-end widget form.
      *
      * @see WP_Widget::form()
      *
      * @param array $instance Previously saved values from database
      */
     public function form($instance)
     {
         if (isset($instance['title'])) {
             $title = $instance[ 'title' ];
         } else {
             $title = '';
         } ?>
     <p>
       <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
       <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title); ?>">
     </p>
 <?php

     }

     /**
      * Sanitize widget form values as they are saved.
      *
      * @see WP_Widget::update()
      *
      * @param array $new_instance Values just sent to be saved
      * @param array $old_instance Previously saved values from database
      *
      * @return array Updated safe values to be saved
      */
     public function update($new_instance, $old_instance)
     {
         $instance = array();
         $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

         return $instance;
     }
 }
