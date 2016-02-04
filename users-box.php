<?php
/*
Plugin Name: User Box
Plugin URI:  http://www.ivansevcik.cz/user-box-wordpress-plugin/
Description: Display a box with a random user avatars.
Version:     1.0.4
Author:      Ivan Sevcik
Author URI:  http://www.ivansevcik.cz/user-box-wordpress-plugin/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class UsersBoxWidget extends WP_Widget {

	public function __construct() {
		$widget_ops = array( 
			'class_name' => 'usersboxwidget',
			'description' => 'Display a box with a random user avatars.',
		);
		parent::__construct( 'usersboxwidget', 'User Box Widget', $widget_ops );

		add_action('wp_enqueue_scripts',  array(&$this, '_enqueue_scripts'), 0);
	}

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$number = ( ! empty( $instance['number'] ) ) ? absint( $instance['number'] ) : 10;
		if ( ! $number ) {
			$number = 10;
		}
		$shape = isset( $instance['shape'] ) ? $instance['shape'] : 'circle';

		echo $args['before_widget'];

		if ( $title ) {
			if ( ! empty( $instance['title_url'] ) ) {
				$title = "<a href='${instance['title_url']}'>$title</a>";
			}
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$this->_get_users( $number, $shape );

		echo $args['after_widget'];
	}

	public function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'title_url' => '') );
			$title = $instance['title'];
			$title_url = $instance['title_url'];
			$number = isset( $instance['number'] ) ? absint( $instance['number'] ) : 10;
			$shape = isset( $instance['shape'] ) ? $instance['shape'] : 'circle';
	?>
			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('title_url'); ?>"><?php _e('Title link URL:'); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id('title_url'); ?>" name="<?php echo $this->get_field_name('title_url'); ?>" type="text" value="<?php echo esc_attr($title_url); ?>" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of users to show:' ); ?></label>
				<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'shape' ); ?>"><?php _e( 'Avatar shape:' ); ?></label> 
				<select name="<?php echo $this->get_field_name( 'shape' ); ?>" id="<?php echo $this->get_field_id( 'shape' ); ?>">
					<option value="circle" <?php echo "circle" == $shape ? "selected" : ""; ?> ><?php _e('Circle'); ?></option>
					<option value="rectangle" <?php echo "rectangle" == $shape ? "selected" : ""; ?> ><?php _e('Rectangle'); ?></option>
				</select>
			</p>
	<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => '', 'title_url' => ''));
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['title_url'] = strip_tags($new_instance['title_url']);
		$instance['number'] = absint( $new_instance['number'] );
		$instance['shape'] = ( ! empty( $new_instance['shape'] ) ) ? strip_tags( $new_instance['shape'] ) : 'circle';

		return $instance;
	}

	public function _get_users( $count, $shape ) {
		$count = intval($count);
		if ( $count > 500 ) $count = 500;
		global $wpdb;

		$users = $wpdb->get_col( "
			SELECT * FROM
				(SELECT r1.ID
					FROM $wpdb->users AS r1 JOIN
						(SELECT CEIL(RAND() *
							(SELECT MAX(ID)
								FROM $wpdb->users)) AS ID)
					AS r2
				WHERE ( r1.ID + $count ) >= r2.ID
				ORDER BY r1.id ASC
				LIMIT $count) r
			ORDER BY RAND()"
		);

		foreach ( $users as $id ) {
			?>
			<div class="ubw-users-box">
				<div class="ubw-user ubw-shape-<?php echo $shape ?>" title="<?php the_author_meta( 'user_login', $id ) ?>">
					<?php echo get_avatar( $id, 40 ); ?>
				</div>
			</div>
			<?php
		}
	}

	public function _enqueue_scripts() {
		wp_register_style('users-box-widget', plugin_dir_url(__FILE__ ) . 'css/style.css' );
		wp_enqueue_style('users-box-widget'); 
	}
}

add_action('widgets_init',
	create_function('', 'return register_widget("UsersBoxWidget");')
);

?>