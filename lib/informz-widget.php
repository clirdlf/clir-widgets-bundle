<?php
/**
 * Adds Informz_Tracking_Widget widget.
 */
class Informz_Tracking_Widget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    public function __construct() {
        parent::__construct(
            'informz_tracking_widget', // Base ID
            'Informz_Tracking_Widget', // Name
            array( 'description' => __( 'Tracking beacon for Informz', 'clir-widgets-bundle' ), ) // Args
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance ) {
        extract( $args );

        echo $before_widget;
        $account = $instance['z_account'];
        $collector = $instance['z_collector'];
        $cookieDomain = $instance['z_cookieDomain'];

        $html = <<<EOT
<script>
  var z_account="$account",z_collector="$collector",z_cookieDomain="$cookieDomain";(function(o,e,r,c,n,t,a){o[n]||(o.GlobalSnowplowNamespace=o.GlobalSnowplowNamespace||[],o.GlobalSnowplowNamespace.push(n),o[n]=function(){(o[n].q=o[n].q||[]).push(arguments)},o[n].q=o[n].q||[],t=e.createElement(r),a=e.getElementsByTagName(r)[0],t.async=1,t.src=c,a.parentNode.insertBefore(t,a))})(window,document,"script","https://"+z_collector+"/web_trk/sp.js","informz_trk"),informz_trk("newTracker","infz",z_collector+"/web_trk/collector/",{appId:z_account,cookieDomain:z_cookieDomain}),informz_trk("setUserIdFromLocation","_zs"),informz_trk("enableActivityTracking",30,15),informz_trk("trackPageView",null);
</script>
EOT;

        echo $html;

        echo $after_widget;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {
        if ( isset( $instance[ 'z_account' ] ) ) {
            $z_account = $instance[ 'z_account' ];
        }
        else {
            $z_account = '';
        }
        if ( isset( $instance[ 'z_collector' ] ) ) {
            $z_collector = $instance[ 'z_collector' ];
        }
        else {
            $z_collector = '';
        }

        if ( isset( $instance[ 'z_cookieDomain' ] ) ) {
            $z_cookieDomain = $instance[ 'z_cookieDomain' ];
        }
        else {
            $z_cookieDomain = '.clir.org';
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_name( 'z_account' ); ?>"><?php _e( 'Account:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'z_account' ); ?>" name="<?php echo $this->get_field_name( 'z_account' ); ?>" type="text" value="<?php echo esc_attr( $z_account ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_name( 'z_collector' ); ?>"><?php _e( 'Collector:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'z_collector' ); ?>" name="<?php echo $this->get_field_name( 'z_collector' ); ?>" type="text" value="<?php echo esc_attr( $z_collector ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_name( 'z_cookieDomain' ); ?>"><?php _e( 'Cookie Domain:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'z_cookieDomain' ); ?>" name="<?php echo $this->get_field_name( 'z_cookieDomain' ); ?>" type="text" value="<?php echo esc_attr( $z_cookieDomain ); ?>" />
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
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['z_account'] = ( !empty( $new_instance['z_account'] ) ) ? strip_tags( $new_instance['z_account'] ) : '';
        $instance['z_collector'] = ( !empty( $new_instance['z_collector'] ) ) ? strip_tags( $new_instance['z_collector'] ) : '';
        $instance['z_cookieDomain'] = ( !empty( $new_instance['z_cookieDomain'] ) ) ? strip_tags( $new_instance['z_cookieDomain'] ) : '';

        return $instance;
    }

} // class Foo_Widget
