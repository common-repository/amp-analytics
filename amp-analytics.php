<?php
/**
 * Plugin Name: AMP Analytics
 * Description: Extend AMP to support analytics for your WordPress site.
 * Plugin URI:  https://wordpress.org/plugins/amp-analytics/
 * Author:      Valet
 * Author URI:  https://www.valet.io/
 * Version:     0.0.2
 * Text Domain: amp-analytics
 * Domain Path: /languages/
 * License:     GPLv2 or later
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     AMP
 * @subpackage  Analytics
 * @author      Dylan Ryan
 * @version     0.0.2
 */

/**
 * Filter the plugin's action links to add our settings page.
 *
 * @since 0.0.1
 *
 * @param $links
 *
 * @return array
 */
function amp_analytics_settings_link( $links ) {
	return array_merge( array( 'settings' => '<a href="' . admin_url( 'options-general.php?page=amp-analytics.php' ) . '">' . __( 'Settings', 'amp-analytics' ) . '</a>' ), $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'amp_analytics_settings_link' );

/**
 * Print Google's AMP Analytics script.
 *
 * @since 0.0.1
 */
function amp_analytics_print_scripts(){
?>
	<script async custom-element="amp-analytics" src="https://cdn.ampproject.org/v0/amp-analytics-0.1.js"></script>
<?php
}
add_action( 'amp_post_template_head', 'amp_analytics_print_scripts' );

/**
 * Print the JSON used for tracking.
 *
 * @since 0.0.1
 */
function amp_analytics_maybe_add_analytics() {
	$options = get_option( 'amp_analytics_settings' );
	$account = '"account": ' . '"' . esc_js( $options['amp_analytics_ga_ua'] ) . '"' . PHP_EOL;

	// Make sure we have a UA before injecting script
	if ( isset( $options['amp_analytics_ga_ua'] ) && '' !== $options['amp_analytics_ga_ua'] ) {
		?>
			<amp-analytics type="googleanalytics" id="googleanalytics1">
				<script type="application/json">
					{
						"vars": {
							<?php echo $account; ?>
						},
						"triggers": {
							"trackPageview": {
								"on": "visible",
								"request": "pageview"
							}
						}
					}
				</script>
			</amp-analytics>
		<?php
	}
}
add_action( 'amp_post_template_footer', 'amp_analytics_maybe_add_analytics' );

/**
 * Add options page.
 *
 * @since 0.0.1
 */
function amp_analytics_add_options_page() {
	add_options_page( 'AMP Analytics', 'AMP Analytics', 'manage_options', 'amp-analytics.php', 'amp_analytics_render_options_page' );
}
add_action( 'admin_menu', 'amp_analytics_add_options_page' );

/**
 * Register settings, sections, and fields.
 *
 * @since 0.0.1
 */
function amp_analytics_register_settings() {

	add_settings_section( 'amp_analytics_google_analytics', __( 'Google Analytics', 'amp-analytics' ), 'amp_analytics_google_analytics_callback', 'amp-analytics' );

	add_settings_field( 'amp_analytics_ga_ua', __( 'Google Analytics ID: <br/><em><a href="https://support.google.com/analytics/answer/1032385?hl=en" target="_blank">Need help finding your tracking ID?</a></em>', 'amp-analytics' ), 'amp_analytics_ga_ua_callback', 'amp-analytics', 'amp_analytics_google_analytics' );

	register_setting( 'amp_analytics_settings', 'amp_analytics_settings', 'amp_analytics_settings_sanitize_callback' );

}
add_action( 'admin_init', 'amp_analytics_register_settings' );



/**
 * Render options page.
 *
 * @since 0.0.1
 */
function amp_analytics_render_options_page() {
?>
	<form action='options.php' method='post' enctype='multipart/form-data'>

		<h1>AMP Analytics</h1>

		<?php
		settings_fields( 'amp_analytics_settings' );
		do_settings_sections( 'amp-analytics' );
		submit_button();
		?>

	</form>
<?php
}

/**
 * Google Analytics section callback.
 *
 * @since 0.0.1
 */
function amp_analytics_google_analytics_callback() {}

/**
 * Google Analytics UA setting callback.
 *
 * @since 0.0.1
 */
function amp_analytics_ga_ua_callback() {
	$options = get_option( 'amp_analytics_settings' );
	?>
		<input type='text' name='amp_analytics_settings[amp_analytics_ga_ua]' value='<?php echo $options['amp_analytics_ga_ua']; ?>'>
	<?php
}

/**
 * Sanitizes settings before they get to the database.
 * 
 * @since 0.0.2
 * 
 * @param $input array Options array.
 *
 * @return array Sanitized, database-ready options array.
 */
function amp_analytics_settings_sanitize_callback( $input ) {

	if ( $input['amp_analytics_ga_ua'] ) {
		$input['amp_analytics_ga_ua'] = sanitize_text_field( $input['amp_analytics_ga_ua'] );
	}

	return $input;
}