<?php
/**
Plugin Name: TW Recent Posts Widget
Plugin URI: http://vuckovic.biz/wordpress-plugins/tw-recent-posts-widget
Description: TW Recent Posts Widget is advanced version of the WordPress Recent Posts widget allowing increased customization to display recent posts from category you define.
Author: Igor Vučković
Author URI: http://vuckovic.biz
Version: 1.0.5
*/

//	Set the wp-content and plugin urls/paths
if(!defined('WP_CONTENT_URL'))
    define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if(!defined('WP_CONTENT_DIR'))
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
if(!defined('WP_PLUGIN_URL'))
    define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
if(!defined('WP_PLUGIN_DIR'))
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');

/**
 * Class TW_Recent_Posts
 */
class TW_Recent_Posts extends WP_Widget
{

    //	@var string(The plugin version)
    var $version = '1.0.5';
    //	@var string(Domain used for localization)
    var $localization_domain = 'tw-recent-posts';
    //	@var string(The url to this plugin)
    var $plugin_url = '';
    //	@var string(The path to this plugin)
    var $plugin_path = '';


    /**
     * Constructor
     */
    public function __construct()
    {
        $name = dirname(plugin_basename(__FILE__));
        $this->plugin_url = WP_PLUGIN_URL . "/$name/";
        $this->plugin_path = WP_PLUGIN_DIR . "/$name/";
        add_action('wp_print_styles', array(&$this, 'tw_recent_posts_css'));

        $widget_ops = array('classname' => 'tw-recent-posts', 'description' => __('Show recent posts from selected category. Includes advanced options.', $this->localization_domain));

        parent::__construct('tw-recent-posts', __('TW Recent Posts ', $this->localization_domain), $widget_ops);
    }

    /**
     * Inserts CSS style
     */
    public function tw_recent_posts_css()
    {
        $name = "tw-recent-posts-widget.css";
        if (false !== file_exists(TEMPLATEPATH . "/$name")) {
            $css = get_template_directory_uri() . "/$name";
        } else {
            $css = $this->plugin_url . $name;
        }

        wp_enqueue_style('tw-recent-posts-widget', $css, false, $this->version, 'screen');
    }

    /**
     * Truncates input text to the defined length
     *
     * @param int $amount
     * @param string $allowed
     * @return string
     */
    private function _truncate_post($amount, $allowed = '')
    {
        global $post;

        $post_excerpt = $post->post_excerpt;

        if($post_excerpt != '') {

            if(strlen($post_excerpt) <= $amount) {
                $echo_out = '';
            } else {
                $echo_out = '...';
            }

            $post_excerpt = strip_tags($post_excerpt, $allowed);

            if($echo_out == '...') {
                $post_excerpt = substr($post_excerpt, 0, strrpos(substr($post_excerpt, 0, $amount), ' '));
            } else {
                $post_excerpt = substr($post_excerpt, 0, $amount);
            }

            return($post_excerpt . $echo_out);

        } else {

            $truncate = $post->post_content;
            $truncate = preg_replace('@\[caption[^\]]*?\].*?\[\/caption]@si', '', $truncate);

            if(strlen($truncate) <= $amount) {
                $echo_out = '';
            } else {
                $echo_out = '...';
            }

            $truncate = apply_filters('the_content', $truncate);
            $truncate = preg_replace('@<script[^>]*?>.*?</script>@si', '', $truncate);
            $truncate = preg_replace('@<style[^>]*?>.*?</style>@si', '', $truncate);
            $truncate = strip_tags($truncate, $allowed);

            if($echo_out == '...') {
                $truncate = substr($truncate, 0, strrpos(substr($truncate, 0, $amount), ' '));
            } else {
                $truncate = substr($truncate, 0, $amount);
            }

            return $truncate . $echo_out;
        }
    }

    /**
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        extract($args);
        
        $title = apply_filters('title', isset($instance['title']) ? esc_attr($instance['title']) : '');
        $category = apply_filters('category', isset($instance['category']) ? esc_attr($instance['category']) : '');
        $moretext = apply_filters('moretext', isset($instance['moretext']) ? esc_attr($instance['moretext']) : '');
        $count = apply_filters('count', isset($instance['count']) && is_numeric($instance['count']) ? esc_attr($instance['count']) : '');
        $orderby = apply_filters('orderby', isset($instance['orderby']) ? $instance['orderby'] : '');
        $order = apply_filters('order', isset($instance['order']) ? $instance['order'] : '');
        $width = apply_filters('width', isset($instance['width']) && is_numeric($instance['width']) ?(int)$instance['width'] : 60);
        $height = apply_filters('height', isset($instance['height']) && is_numeric($instance['height']) ?(int)$instance['height'] : 60);
        $length = apply_filters('length', isset($instance['length']) && is_numeric($instance['length']) ?(int)$instance['length'] : 100);
        $show_post_title = apply_filters('show_post_title', isset($instance['show_post_title']) ?(bool)$instance['show_post_title'] : false);
        $show_post_time = apply_filters('show_post_time', isset($instance['show_post_time']) ? (bool)$instance['show_post_time'] : false);
        $show_post_thumb = apply_filters('show_post_thumb', isset($instance['show_post_thumb']) ?(bool)$instance['show_post_thumb'] : false);
        $show_post_excerpt = apply_filters('show_post_excerpt', isset($instance['show_post_excerpt']) ?(bool)$instance['show_post_excerpt'] : false);

        echo $before_widget;
        if(!empty($title)) {
            echo $before_title . $title . $after_title;
        }
?>

<div class="featured-posts textwidget">
<?php
$wp_query = new WP_Query(array(
    'cat' => $category,
    'posts_per_page' => $count,
    'orderby' => $orderby,
    'order' => $order,
    'nopagging' => true
));

while($wp_query->have_posts()) : $wp_query->the_post(); ?>
	<div class="featured-post">

	<?php if ($show_post_title) { ?>
		<h4>
            <a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute() ?>">
                <?php the_title() ?>
            </a>
        </h4>
	<?php } ?>

	<?php if ($show_post_time) { ?>
		<div class="post-time">
			<?php the_time(get_option('date_format')); ?>
		</div>
	<?php } ?>

	<?php if ($show_post_thumb && has_post_thumbnail()) { ?>
		<a href="<?php the_permalink(); ?>">
            <?php the_post_thumbnail(array($width,$height), array('title' => '', 'class' => 'alignleft')); ?>
        </a>
	<?php } ?>

	<?php if($show_post_excerpt) { ?>
		<div class="excerpt">
            <?php echo $this->_truncate_post($length)
                . ($moretext != '' ? ' <a href="' . get_permalink() . '" class="read-more">' . $moretext . '</a>' : ''); ?>
		</div>
	<?php } ?>

		<div class="clear"></div>
	</div>
<?php
endwhile;
wp_reset_query();
wp_reset_postdata();
		?>
</div>
<?php
		echo $after_widget;
	}

    /**
     * @param array $new_instance
     * @param arrau $old_instance
     * @return mixed
     */
    public function update($new_instance, $old_instance)
    {
        return $new_instance;
    }

    /**
     * @param array $instance
     */
    public function form($instance) {
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$category = isset($instance['category']) ? esc_attr($instance['category']) : '';
		$moretext = isset($instance['moretext']) ? esc_attr($instance['moretext']) : 'more&raquo;';
		$count = isset($instance['count']) && is_numeric($instance['count']) ? esc_attr($instance['count']) : 4;
		$orderby = isset($instance['orderby']) ? $instance['orderby'] : '';
		$order = isset($instance['order']) ? $instance['order'] : '';
		$width = isset($instance['width']) && is_numeric($instance['width']) ? (int)$instance['width'] : 60;
		$height = isset($instance['height']) && is_numeric($instance['height']) ? (int)$instance['height'] : 60;
		$length = isset($instance['length']) && is_numeric($instance['length']) ? (int)$instance['length'] : 100;
		$show_post_title = isset($instance['show_post_title']) ? (bool)$instance['show_post_title'] : false;
		$show_post_time = isset($instance['show_post_time']) ? (bool)$instance['show_post_time'] : false;
		$show_post_thumb = isset($instance['show_post_thumb']) ? (bool)$instance['show_post_thumb'] : false;
		$show_post_excerpt = isset($instance['show_post_excerpt']) ? (bool)$instance['show_post_excerpt'] : false;
?>

<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', $this->localization_domain); ?> <input
	class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
	name="<?php echo $this->get_field_name('title'); ?>" type="text"
	value="<?php echo $title; ?>" /></label></p>

<p><label for="<?php echo $this->get_field_id('category'); ?>"><?php _e('Category:', $this->localization_domain); ?></label><select
	id="<?php echo $this->get_field_id('category'); ?>"
	name="<?php echo $this->get_field_name('category'); ?>">
	<?php
	echo '<option value="0" ' .('0' == $category ? 'selected="selected"' : ''). '>'. __('All categories', $this->localization_domain).'</option>';
	$cats = get_categories(array('hide_empty' => 0, 'taxonomy' => 'category', 'hierarchical' => 1));
	foreach($cats as $cat) {
		echo '<option value="' . $cat->term_id . '" ' .($cat->term_id == $category ? 'selected="selected"' : ''). '>' . $cat->name . '</option>';
	} ?>
	</select></p>

<p><label for="<?php echo $this->get_field_id('orderby'); ?>"><?php _e('Order by:', $this->localization_domain); ?></label><select
	id="<?php echo $this->get_field_id('orderby'); ?>"
	name="<?php echo $this->get_field_name('orderby'); ?>">
	<option value="date"
		<?php echo 'date' == $orderby ? 'selected="selected"' : '' ?>><?php _e('Date', $this->localization_domain); ?></option>
	<option value="ID"
		<?php echo 'ID' == $orderby ? 'selected="selected"' : '' ?>><?php _e('ID', $this->localization_domain); ?></option>
	<option value="title"
		<?php echo 'title' == $orderby ? 'selected="selected"' : '' ?>><?php _e('Title', $this->localization_domain); ?></option>
	<option value="author"
		<?php echo 'author' == $orderby ? 'selected="selected"' : '' ?>><?php _e('Author', $this->localization_domain); ?></option>
	<option value="comment_count"
		<?php echo 'comment_count' == $orderby ? 'selected="selected"' : '' ?>><?php _e('Comment count', $this->localization_domain); ?></option>
	<option value="rand"
		<?php echo 'rand' == $orderby ? 'selected="selected"' : '' ?>><?php _e('Random', $this->localization_domain); ?></option>
</select></p>

<p><label for="<?php echo $this->get_field_id('order'); ?>"><?php _e('Order:', $this->localization_domain); ?></label><select
	id="<?php echo $this->get_field_id('order'); ?>"
	name="<?php echo $this->get_field_name('order'); ?>">
	<option value="DESC"
		<?php echo 'DESC' == $order ? 'selected="selected"' : '' ?>><?php _e('DESC:', $this->localization_domain); ?></option>
	<option value="ASC"
		<?php echo 'ASC' == $order ? 'selected="selected"' : '' ?>><?php _e('ASC:', $this->localization_domain); ?></option>
</select></p>

<p><label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Number of posts to show:', $this->localization_domain); ?> <input
	id="<?php echo $this->get_field_id('count'); ?>"
	name="<?php echo $this->get_field_name('count'); ?>" type="text"
	size="3" value="<?php echo $count; ?>" /></label></p>

<p><input id="<?php echo $this->get_field_id('show_post_title'); ?>"
	name="<?php echo $this->get_field_name('show_post_title'); ?>"
	type="checkbox" <?php checked($show_post_title); ?> /> <label
	for="<?php echo $this->get_field_id('show_post_title'); ?>"><?php _e('Show post title', $this->localization_domain); ?></label>
</p>

<p><input id="<?php echo $this->get_field_id('show_post_time'); ?>"
	name="<?php echo $this->get_field_name('show_post_time'); ?>"
	type="checkbox" <?php checked($show_post_time); ?> /> <label
	for="<?php echo $this->get_field_id('show_post_time'); ?>"><?php _e('Show post time', $this->localization_domain); ?></label>
</p>

<p><input id="<?php echo $this->get_field_id('show_post_thumb'); ?>"
	name="<?php echo $this->get_field_name('show_post_thumb'); ?>"
	type="checkbox" <?php checked($show_post_thumb); ?> /> <label
	for="<?php echo $this->get_field_id('show_post_thumb'); ?>"><?php _e('Show post thumb', $this->localization_domain); ?></label><br />
<small><?php _e('Thumbnail size(W-H):', $this->localization_domain); ?></small>
<input type="text" size="3"
	name="<?php echo $this->get_field_name('width'); ?>"
	value="<?php echo $width; ?>" />px <input type="text" size="3"
	name="<?php echo $this->get_field_name('height'); ?>"
	value="<?php echo $height; ?>" />px</p>

<p><input id="<?php echo $this->get_field_id('show_post_excerpt'); ?>"
	name="<?php echo $this->get_field_name('show_post_excerpt'); ?>"
	type="checkbox" <?php checked($show_post_excerpt); ?> /> <label
	for="<?php echo $this->get_field_id('show_post_excerpt'); ?>"><?php _e('Show post excerpt', $this->localization_domain); ?></label><br />
<small><?php _e('Post excerpt length(characters)', $this->localization_domain); ?></small>
<input id="<?php echo $this->get_field_id('length'); ?>"
	name="<?php echo $this->get_field_name('length'); ?>" type="text"
	size="3" value="<?php echo $length; ?>" /><br />
<small><?php _e('Read more text', $this->localization_domain); ?></small>
<input name="<?php echo $this->get_field_name('moretext'); ?>"
	type="text" size="12" value="<?php echo $moretext; ?>" /></p>

<?php
    }

} // end class TW_Recent_Posts

add_action('widgets_init', function () {
    register_widget('TW_Recent_Posts');
});
