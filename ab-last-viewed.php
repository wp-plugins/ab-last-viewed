<?php
/**
 * @package AB_Last_Viewed
 * @version 0.1
 */
/**
 * Plugin Name: AB Last Viewed
 * Plugin URI: http://abidubi.altervista.org/ab-human-time/
 * Description: This plugin adds a widget that shows the last five post read.
 * Author: Andrea Bianchini
 * Version: 0.1
 * Author URI: http://abidubi.altervista.org
 * Text Domain: ab-last-viewed
 */
/* 
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

	add_action( 'init' , 'ab_start_session',1 );
	add_action( 'wp_logout' , 'ab_session_destroy' );
	add_action( 'wp_login' , 'ab_session_destroy' );
	add_action( 'the_content' , 'ab_add_a_post' );
	
	
	/**
	 * Loads localizations
	 */
	function ab_last_viewed_init() {
  		load_plugin_textdomain( 'ab-last-viewed', false, dirname( plugin_basename( __FILE__ ) ) . 		'/languages/' );
	}
	add_action( 'plugins_loaded', 'ab_last_viewed_init' );
	
	/**
	 * Start session
	 */
	function ab_start_session() {
		if ( ! session_id() ) {
			session_start();
		}
	}
	
	/**
	 * Destroy session
	 */
	function ab_session_destroy() {
		session_destroy();
	}
	
	/**
	 * Generate the actual output of the widget
	 */
	function ab_last_viewed() {
		$lv_list = array();
		echo "<div class='ab_last_viewed'><ul>";
		if ( isset( $_SESSION['ab_list_viewed'])) {
			$lvposts = $_SESSION['ab_list_viewed'];
			foreach ($lvposts as $key => $lvpID) {
				$args='p='.$lvpID;
				$my_query = new WP_Query($args);
				while ( $my_query->have_posts() ) :
					$my_query->the_post();
					
					echo '<li><a name="post-' . get_the_ID() . '" href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
				endwhile;
				wp_reset_query();
			}
		echo "</ul></div>";
		}
		echo $list_div;
	}
	
	/**
	 * Appends the last read post id
	 * 
	 * @param string $content
	 * @return string
	 */
	function ab_add_a_post($content) {
		define(LV_POST_NUM, 5);
		global $post;
		$last_list= array();
		if ( ($post->post_type == 'post' ) ) {
			$postId = $post->ID;
			$last_list = ($_SESSION['ab_list_viewed']) ? $_SESSION['ab_list_viewed'] :
				$last_list;
			if ( ! in_array( $postId, $last_list)) {
				if ( LV_POST_NUM - 1 < count($last_list) ) {
					array_shift( $last_list );
				}
				array_push( $last_list, $postId );
				$_SESSION['ab_list_viewed'] = $last_list;
			}
		}
		return $content;
	}
	
	/**
 	 * Adds AB_Last_Read widget.
 	 */
	class AB_Last_Read extends WP_Widget {
		
		/**
		 * Register widget with WordPress.
		 */
		function AB_Last_Read() {
			parent::__construct('ab_last_read', $name = __('Last viewed', 'ab-last-viewed'));
		}
		
		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		function widget($args, $instance) {
			extract( $args );
			$title = apply_filters('widget_title', empty($instance['title']) ? __('Last read',
			 'ab-last-viewed') : trim($instance['title']), $instance, $this->id_base);
			echo $before_widget;
			echo $before_title
				. $title
				. $after_title;
			ab_last_viewed();
			echo $after_widget;
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
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);

			return $instance;
		}
		
		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		function form( $instance ) {
		 	$title = esc_attr($instance['title']);
			?>
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>">
						<?php _e( 'Title:' ); ?></label>
						<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
								name="<?php echo $this->get_field_name('title'); ?>" type="text"
								value="<?php echo $title; ?>" />
				</p>
			<?php
		 }
	}
	
	add_action('widgets_init', create_function('', 'return register_widget("AB_Last_Read");'));
?>