<?php
/*
Plugin Name: Upnews Plugin
Plugin URI: http://www.4mj.it/voto-upnewsit-dal-tuo-sito
Description: Pulsante voto per Upnews.it.
Version: 2.1.3
Author: Giuseppe Argento
Author URI: http://www.4mj.it
*/
function upnews_options() {
	add_menu_page('Upnews', 'Upnews', 8, basename(__FILE__), 'upnews_options_page');
}
function upnews_build_options() {
	global $post;
	// get the permalink
    if (get_post_status($post->ID) == 'publish') {
        $url = get_permalink();
    }
    $button = urlencode($url);
	return $button;
}
/**
* Generate the button
*/
function upnews_generate_button() {
	// build up the outer style
    $button = '<div class="upnews_button" style="' . get_option('upnews_style') . '">';
    $button .= "<script type=\"text/javascript\"><!--\n";
    $button .= "submit_url =\"" . upnews_build_options() ."\";\n";
    $button .= "//-->\n";
    $button .= "</script>\n";
    $button .= '<script src="http://www.upnews.it/tools/' . get_option('upnews_version') . '.js" type="text/javascript">';
	// close
	$button .= '</script></div>';
	// return the button
    echo $button;
}
function upnews_update($content) {
    global $post;
    if (get_option('upnews_enable') == 'no') {
		return $content;
	}
    // page
    if (get_option('upnews_display_page') == null && is_page()) {
        return $content;
    }
	// front page
    if (get_option('upnews_display_front') == null && is_home()) {
        return $content;
    }
	
	// remove from feed
	if (is_feed() && get_option('tm_display_rss') == 1) {
		return $content;
	}
	$button = upnews_generate_button();
	$where = 'upnews_where';
	if (get_option($where) == 'shortcode') {
		return str_replace('<!--upnews-->', $button, $content);
	} else {
		// if we have switched the button off
		if (get_post_meta($post->ID, 'upnews') == null) {
			if (get_option($where) == 'beforeandafter') {
				// adding it before and after
				return $button . $content . $button;
			} else if (get_option($where) == 'before') {
				// just before
				return $button . $content;
			} else {
				// just after
				return $content . $button;
			}
		} else {
			// not at all
			return $content;
		}
	}
}
function upnews_remove_filter($content) {
    	remove_action('the_content', 'upnews_update');
    return $content;
}
function upnews_options_page() {
?>
    <div class="wrap">
    <div class="icon32" id="icon-options-general"><br/></div><h2>Impostazioni Upnews Plugin</h2>
    <p>Questo plugin aggiunge il pulsante Upnews ai contenuti dei vostri posts.
    </p>
    <form method="post" action="options.php">
    <?php
        // New way of setting the fields, for WP 2.7 and newer
        if(function_exists('settings_fields')){
            settings_fields('tm-options');
        } else {
            wp_nonce_field('update-options');
            ?>
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="page_options" value="upnews_where,upnews_style,upnews_version,upnews_display_page,upnews_display_front,upnews_enable" />
            <?php
        }
    ?>
        <table class="form-table">
            <tr>
	            <tr>
					<th>Attiva</th>
					<td>
						<input type="radio" value="yes" <?php if (get_option('upnews_enable') == 'yes' || !get_option('upnews_enable')) echo 'checked="checked"'; ?> name="upnews_enable" id="upnews_enable_yes" group="upnews_enable"/>
		                <label for="upnews_enable_yes">Si</label>
		                <br/>
		                <input type="radio" value="no" <?php if (get_option('upnews_enable') == 'no') echo 'checked="checked"'; ?> name="upnews_enable" id="upnews_enable_no" group="upnews_enable" />
		                <label for="upnews_enable_no">No</label>
            		</td>
				</tr>
	            <tr>
	                <th scope="row" valign="top">
	                    Display
	                </th>
	                <td>
	                    <input type="checkbox" value="1" <?php if (get_option('upnews_display_page') == '1') echo 'checked="checked"'; ?> name="upnews_display_page" id="upnews_display_page" group="upnews_display"/>
	                    <label for="upnews_display_page">Mostra il pulsante nelle pagine</label>
	                    <br/>
	                    <input type="checkbox" value="1" <?php if (get_option('upnews_display_front') == '1') echo 'checked="checked"'; ?> name="upnews_display_front" id="upnews_display_front" group="upnews_display"/>
	                    <label for="upnews_display_front">Mostra il pulsante in front page</label>
	                </td>
	            </tr>
                <th scope="row" valign="top">
                    Posizione
                </th>
                <td>
                	<select name="upnews_where">
                		<option <?php if (get_option('upnews_where') == 'before') echo 'selected="selected"'; ?> value="before">Prima</option>
                		<option <?php if (get_option('upnews_where') == 'after') echo 'selected="selected"'; ?> value="after">Dopo</option>
                		<option <?php if (get_option('upnews_where') == 'beforeandafter') echo 'selected="selected"'; ?> value="beforeandafter">Prima e dopo</option>
                		<option <?php if (get_option('upnews_where') == 'shortcode') echo 'selected="selected"'; ?> value="shortcode">Shortcode [upnews]</option>
                	</select>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top"><label for="upnews_style">Stile</label></th>
                <td>
                    <input type="text" value="<?php echo htmlspecialchars(get_option('upnews_style')); ?>" name="upnews_style" id="upnews_style" />
                    <span class="description">Aggiungi lo stile al pulsante, es. <code>float: left; margin-right: 10px;</code></span>
                </td>
            </tr>
            <tr>
                <th scope="row" valign="top">
                    Tipo
                </th>
                <td>
                    <input type="radio" value="button" <?php if (get_option('upnews_version') == 'button') echo 'checked="checked"'; ?> name="upnews_version" id="upnews_version_large" group="upnews_version"/>
                    <label for="upnews_version_large">Upnews Normal Button</label>
                    <br/>
                    <input type="radio" value="minibutton" <?php if (get_option('upnews_version') == 'minibutton') echo 'checked="checked"'; ?> name="upnews_version" id="upnews_version_mini" group="upnews_version" />
                    <label for="upnews_version_mini">Upnews Mini Button</label>
                    <br/>
                    <input type="radio" value="compact" <?php if (get_option('upnews_version') == 'compact') echo 'checked="checked"'; ?> name="upnews_version" id="upnews_version_compact" group="upnews_version" />
                    <label for="upnews_version_compact">Upnews Compact Button</label>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Salva le impostazioni') ?>" />
        </p>
    </form>
    </div>
<?php
}
// On access of the admin page, register these variables (required for WP 2.7 & newer)
function upnews_init(){
    if(function_exists('register_setting')){
        register_setting('tm-options', 'upnews_display_page');
        register_setting('tm-options', 'upnews_display_front');
        register_setting('tm-options', 'upnews_display_rss');
        register_setting('tm-options', 'upnews_style');
        register_setting('tm-options', 'upnews_version');
        register_setting('tm-options', 'upnews_where');
		register_setting('tm-options', 'upnews_enable');
    }
}
// Only all the admin options if the user is an admin
if(is_admin()){
    add_action('admin_menu', 'upnews_options');
    add_action('admin_init', 'upnews_init');
}
// Set the default options when the plugin is activated
function upnews_activate(){
    add_option('upnews_where', 'before');
    add_option('upnews_style', 'float: right; margin-left: 10px;');
    add_option('upnews_version', 'compact');
    add_option('upnews_display_page', '1');
    add_option('upnews_display_front', '1');
    add_option('tm_display_rss', '0');
    add_option('upnews_enable', 'yes');
}
// Set the default options when the plugin is activated
add_filter('the_content', 'upnews_update', 8);
add_filter('get_the_excerpt', 'upnews_remove_filter', 9);
register_activation_hook( __FILE__, 'upnews_activate');