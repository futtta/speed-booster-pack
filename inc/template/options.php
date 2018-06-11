<div class="wrap about-wrap">
	<div class="sb-pack">

		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<div class="about-text">
			<?php
			/* Translators: Welcome Screen Description. */
			echo esc_html__(
				'Speed Booster Pack is a lightweight, frequently updated, easy to use and well supported plugin which allows you to improve your website’s loading speed. Visitors usually close a website if it doesn’t load in a few seconds and the slower a site loads the greater the chances are that the visitors will leave. And you don’t want that to happen, do you? 
', 'sb-pack'
			);
			?>
		</div>
		<div class="wp-badge sbp-welcome-logo"></div>

		<h2 class="nav-tab-wrapper wp-clearfix">
			<a class="nav-tab" href="#general-options"><?php _e( 'General', 'sb-pack' ); ?></a>
			<a class="nav-tab" href="#more-options"><?php _e( 'More Optimization', 'sb-pack' ); ?></a>

		</h2>

		<form method="post" action="options.php">

			<?php settings_fields( 'speed_booster_settings_group' ); ?>

			<div id="general-options" class="sb-pack-tab">

				<h3><?php _e( 'General', 'sb-pack' ); ?></h3>

				<p>
					<input id="sbp_settings[minify_html_js]" name="sbp_settings[minify_html_js]" type="checkbox" value="1" <?php checked( 1, isset( $sbp_options['minify_html_js'] ) ); ?> />
					<label for="sbp_settings[minify_html_js]"><?php _e( 'Optimize HTML', 'sb-pack' ); ?></label>
					<span class="tooltip-right"
						  data-tooltip="<?php echo __( 'Activate this option only if you don’t want to use other minify plugins or other speed optimization plugin that has minify option included. If something goes wrong, simply uncheck this option and save the settings.', 'sb-pack' ); ?>">
						<i class="dashicons dashicons-editor-help"></i>
					</span>
				</p>

				<h3> <?php _e( 'More settings', 'sb-pack' ); ?></h3>

				<p>
					<input id="sbp_settings[query_strings]" name="sbp_settings[query_strings]" type="checkbox" value="1" <?php checked( 1, isset( $sbp_options['query_strings'] ) ); ?> />
					<label for="sbp_settings[query_strings]"><?php _e( 'Remove query strings', 'sb-pack' ); ?></label>
					<span class="tooltip-right"
						  data-tooltip="<?php echo __( 'Removing query strings (or more specificaly the ver parameter) will not improve load time, but will sometimes improve performance scores in Google Page Speed Insights.', 'sb-pack' ); ?>">
						<i class="dashicons dashicons-editor-help"></i>
					</span>
				</p>

				<p>
					<input id="sbp_settings[remove_emojis]" name="sbp_settings[remove_emojis]" type="checkbox" value="1" <?php checked( 1, isset( $sbp_options['remove_emojis'] ) ); ?> />
					<label for="sbp_settings[remove_emojis]"><?php _e( 'Remove WordPress Emoji scripts', 'sb-pack' ); ?></label>
					<span class="tooltip-right"
						  data-tooltip="<?php echo __( 'Emojis are fun and all, but if you are aren’t using them they actually load a JavaScript file (wp-emoji-release.min.js) on every page of your website. For a lot of businesses, this is not needed and simply adds load time to your site. So we recommend disabling this.', 'sb-pack' ); ?>">
						<i class="dashicons dashicons-editor-help"></i>
					</span>
				</p>

				<p>
					<input id="sbp_settings[remove_wsl]" name="sbp_settings[remove_wsl]" type="checkbox" value="1" <?php checked( 1, isset( $sbp_options['remove_wsl'] ) ); ?> />
					<label for="sbp_settings[remove_wsl]"><?php _e( 'Remove WordPress Shortlink', 'sb-pack' ); ?></label>
					<span class="tooltip-right"
						  data-tooltip="<?php echo __( 'WordPress URL shortening is sometimes useful, but it automatically adds an ugly code in your header, so you can remove it.', 'sb-pack' ); ?>">
						<i class="dashicons dashicons-editor-help"></i>
					</span>
				</p>

				<p>
					<input id="sbp_settings[remove_adjacent]" name="sbp_settings[remove_adjacent]" type="checkbox" value="1" <?php checked( 1, isset( $sbp_options['remove_adjacent'] ) ); ?> />
					<label for="sbp_settings[remove_adjacent]"><?php _e( 'Remove Adjacent Posts Links', 'sb-pack' ); ?></label>
					<span class="tooltip-right"
						  data-tooltip="<?php echo __( 'WordPress incorrectly implements this feature that supposedly should fix a pagination issues but it messes up, so there is no reason to keep these around. However, some browsers may use Adjacent Posts Links to navigate your site, although you can remove it if you run a well designed theme.', 'sb-pack' ); ?>">
						<i class="dashicons dashicons-editor-help"></i>
					</span>
				</p>

				<p>
					<input id="sbp_settings[wml_link]" name="sbp_settings[wml_link]" type="checkbox" value="1" <?php checked( 1, isset( $sbp_options['wml_link'] ) ); ?> />
					<label for="sbp_settings[wml_link]"><?php _e( 'Remove Windows Manifest', 'sb-pack' ); ?></label>
					<span class="tooltip-right"
						  data-tooltip="<?php echo __( 'Windows Live Writer (WLW) is a Microsoft application for composing and managing blog posts offline and publish them later. If you are not using Windows Live Writer application, you can remove it from the WP head.', 'sb-pack' ); ?>">
						<i class="dashicons dashicons-editor-help"></i>
					</span>
				</p>

				<p>
					<input id="sbp_settings[rsd_link]" name="sbp_settings[rsd_link]" type="checkbox" value="1" <?php checked( 1, isset( $sbp_options['rsd_link'] ) ); ?> />
					<label for="sbp_settings[rsd_link]"><?php _e( 'Remove RSD(Really Simple Discovery) Link', 'sb-pack' ); ?></label>
					<span class="tooltip-right"
						  data-tooltip="<?php echo __( 'This type of link is used by blog clients. If you edit your site from your browser then you don’t need this. It is also used by some 3rd party applications that utilize XML-RPC requests. In most cases, this is just unnecessary code.', 'sb-pack' ); ?>">
						<i class="dashicons dashicons-editor-help"></i>
					</span>
				</p>

				<p>
					<input id="sbp_settings[wp_generator]" name="sbp_settings[wp_generator]" type="checkbox" value="1" <?php checked( 1, isset( $sbp_options['wp_generator'] ) ); ?> />
					<label for="sbp_settings[wp_generator]"><?php _e( 'Remove the WordPress Version', 'sb-pack' ); ?></label>
					<span class="tooltip-right"
						  data-tooltip="<?php echo __( 'This option is added for security reasons and cleaning the header.', 'sb-pack' ); ?>">
						<i class="dashicons dashicons-editor-help"></i>
					</span>
				</p>

				<p>
					<input id="sbp_settings[remove_all_feeds]" name="sbp_settings[remove_all_feeds]" type="checkbox" value="1" <?php checked( 1, isset( $sbp_options['remove_all_feeds'] ) ); ?> />
					<label for="sbp_settings[remove_all_feeds]"><?php _e( 'Remove all rss feed links', 'sb-pack' ); ?></label>
					<span class="tooltip-right"
						  data-tooltip="<?php echo __( 'This option wil remove all rss feed links to cleanup your WordPress header. It is also useful on Unicorn – The W3C Markup Validation Service to get rid out the “feed does not validate” error.', 'sb-pack' ); ?>">
						<i class="dashicons dashicons-editor-help"></i>
					</span>
				</p>

				<p>
					<input id="sbp_settings[disable_xmlrpc]" name="sbp_settings[disable_xmlrpc]" type="checkbox" value="1" <?php checked( 1, isset( $sbp_options['disable_xmlrpc'] ) ); ?> />
					<label for="sbp_settings[disable_xmlrpc]"><?php _e( 'Disable XML-RPC', 'sb-pack' ); ?></label>
					<span class="tooltip-right"
						  data-tooltip="<?php echo __( 'XML-RPC was added in WordPress 3.5 and allows for remote connections, and unless you are using your mobile device to post to WordPress it does more bad than good. In fact, it can open your site up to a bunch of security risks. There are a few plugins that utilize this such as JetPack, but we don’t recommend using JetPack for performance reasons.', 'sb-pack' ); ?>">
						<i class="dashicons dashicons-editor-help"></i>
					</span>
				</p>

				<h3><?php _e( 'Need even more speed?', 'sb-pack' ); ?></h3>

				<p>
					<input id="sbp_settings[sbp_css_minify]" name="sbp_settings[sbp_css_minify]" type="checkbox" value="1" <?php checked( 1, isset( $sbp_options['sbp_css_minify'] ) ); ?> />
					<label for="sbp_settings[sbp_css_minify]"><?php _e( 'Minify all (previously) inlined CSS styles', 'sb-pack' ); ?></label>
					<span class="tooltip-right"
						  data-tooltip="<?php echo __( 'Minifying all inlined CSS styles will optimize the CSS delivery and will eliminate the annoying message on Google Page Speed regarding to render-blocking css.', 'sb-pack' ); ?>">
								<i class="dashicons dashicons-editor-help"></i>
							</span>
				</p>

				<div class="td-border-last"></div>

				<h4><?php _e( 'Exclude styles from being inlined and/or minified option: ', 'sb-pack' ); ?></h4>
				<p>
					<textarea cols="50" rows="3" name="sbp_css_exceptions" id="sbp_css_exceptions" value="<?php echo esc_attr( $css_exceptions ); ?>" /><?php echo wp_kses_post( $css_exceptions ); ?></textarea>
				</p>
				<p class="description">
					<?php _e( 'Enter one by line, the handles of css files or the final part of the style URL. For example: <code>font-awesome</code> or <code>font-awesome.min.css</code>', 'sb-pack' ); ?>
				</p>

				<div class="td-border-last"></div>

			</div><!--#general-options-->

			<div id="more-options" class="sb-pack-tab">

				<br />
				<?php
				$plugins = array(
					'shortpixel-image-optimiser' => array(
						'title'       => esc_html__( 'ShortPixel Image Optimizer', 'sb-pack' ),
						'description' => esc_html__( 'Increase your website’s SEO ranking, number of visitors and ultimately your sales by optimizing any image or PDF document on your website. ', 'sb-pack' ),
						'more'        => 'https://shortpixel.com/h/af/IVAKFSX31472',
					),

				);

				if ( ! function_exists( 'get_plugins' ) || ! function_exists( 'is_plugin_active' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
				}

				$installed_plugins = get_plugins();

				function sbp_get_plugin_basename_from_slug( $slug, $installed_plugins ) {
					$keys = array_keys( $installed_plugins );
					foreach ( $keys as $key ) {
						if ( preg_match( '|^' . $slug . '/|', $key ) ) {
							return $key;
						}
					}

					return $slug;
				}

				?>

				<div class="sbp-recommended-plugins">
					<?php
					foreach ( $plugins as $slug => $plugin ) {

						$label       = __( 'Install + Activate & get 500 free credits', 'sb-pack' );
						$action      = 'install';
						$plugin_path = sbp_get_plugin_basename_from_slug( $slug, $installed_plugins );
						$url         = '#';
						$class       = '';

						if ( file_exists( ABSPATH . 'wp-content/plugins/' . $plugin_path ) ) {

							if ( is_plugin_active( $plugin_path ) ) {
								$label  = __( 'Activated', 'sb-pack' );
								$action = 'disable';
								$class  = 'disabled';
							} else {
								$label  = __( 'Activate & get 500 free credits', 'sb-pack' );
								$action = 'activate';
								$url    = wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'activate',
											'plugin' => $plugin_path,
										), admin_url( 'plugins.php' )
									), 'activate-plugin_' . $plugin_path
								);
							}
						}

						?>
						<div class="sbp-recommended-plugin">
							<div class="plugin-image">
								<img src="https://ps.w.org/shortpixel-image-optimiser/assets/icon-128x128.png?rev=1038819">
							</div>
							<div class="plugin-information">
								<h3 class="plugin-name">
									<strong><?php echo esc_html( $plugin['title'] ); ?></strong></h3>
								<p class="plugin-description"><?php echo esc_html( $plugin['description'] ); ?></p>

								<a href="<?php echo esc_url( $url ); ?>" data-action="<?php echo esc_attr( $action ); ?>" data-slug="<?php echo esc_attr( $plugin_path ); ?>" data-message="<?php esc_html_e( 'Activated', 'sb-pack' ); ?>" class="button-primary sbp-plugin-button <?php echo esc_attr( $class ); ?>"><?php echo esc_html( $label ); ?></a>
								<?php if ( isset( $plugin['more'] ) ) : ?>
									<a href="<?php echo esc_url( $plugin['more'] ); ?>" class="button-secondary" target="_blank"><?php esc_html_e( 'Test your site for free', 'sb-pack' ); ?></a>
								<?php endif ?>
							</div>
						</div>
					<?php } ?>
				</div>

			</div><!--#image-options-->

			<div class="textright">
				<hr />
				<?php submit_button( '', 'button button-primary button-hero' ); ?>
			</div>

		</form>

	</div><!--/.sb-pack-->
</div> <!-- end wrap div -->
