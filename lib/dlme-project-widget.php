<?php
/**
 * Adds a Project Widget to override the Illidy Project widget
 *
 */
class DLME_Project_Widget extends WP_Widget
{
  function __construct()
  {
      parent::__construct(
          'DLME Project Widget', // Base ID
          __('DLME Project Widget', 'clir-widgets-bundle'), // Name
          array( 'description' => __('Adds a DLME project.', 'clir-widgets-bundle'),) // Args
      );
  }

  public function widget( $args, $instance )
  {
      if (array_key_exists('before_widget', $args)) echo $args['before_widget'];

      $title = apply_filters('widget_title', $instance['title']);
      $url = apply_filters('widget_url', $instance['url']);
      $image = apply_filters('widget_image', $instance['image']);

      echo '<div id="illdy_project-12" class="col-sm-3 col-xs-6 no-padding widget_dmle_project"><a href="' . $instance['url'] . '" title="'. $instance['title'].'" class="project no-url" style="background-image: url(&quot;'. $instance['image'] .'&quot;); height: 338px;"><span class="project-overlay">'.$instance['title'].'</span></a></div>';


      // echo '<div class="col-sm-3 col=xs-6 no-padding widget_dlme_project">' . $title . '</div>';
      // wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.12.1/jquery-ui.min.js');

      if (array_key_exists('after_widget', $args)) echo $args['after_widget'];
  }
  /**
   * Back-end widget form.
   *
   * @see WP_Widget::form()
   *
   * @param array $instance Previously saved values from database.
   */
  public function form( $instance )
  {

    $title = ! empty( $instance['title'] ) ? $instance['title'] : '';
    $title = ! empty( $instance['url'] ) ? $instance['url'] : '';
    $title = ! empty( $instance['image'] ) ? $instance['image'] : '';
  ?>
  <p>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
  </p>
  <p>
    <label for="<?php echo $this->get_field_id( 'image' ); ?>"><?php _e( 'Image URL:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'image' ); ?>" name="<?php echo $this->get_field_name( 'image' ); ?>" type="text" value="<?php echo esc_attr( $image ); ?>">
  </p>
  <p>
    <label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php _e( 'URL:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name( 'utr' ); ?>" type="text" value="<?php echo esc_attr( $url ); ?>">
  </p>

  <?php
  }

  /**
   * Sanitize widget form values as they are saved.
   *
   * @see WP_Widget::update()
   *
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   *
   * @return array Updated safe values to be saved.
   */
  public function update( $new_instance, $old_instance )
  {
      $instance = $old_instance;
      $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
      $instance[ 'url' ] = strip_tags( $new_instance[ 'title' ] );
      $instance[ 'image' ] = strip_tags( $new_instance[ 'title' ] );
      var_dump($instance);
      return $instance;
  }
}
