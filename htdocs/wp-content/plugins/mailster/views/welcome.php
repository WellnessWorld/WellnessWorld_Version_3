<div class="wrap mailster-welcome-wrap">

	<h1><?php printf( esc_html__( 'Welcome to %s', 'mailster' ), 'Mailster 2.3' ); ?></h1>

	<div class="about-text">
		<?php esc_html_e( 'Easily create, send and track your Newsletter Campaigns', 'mailster' ); ?><br>
	</div>

	<div class="mailster-badge"><?php printf( esc_html__( 'Version %s', 'mailster' ), MAILSTER_VERSION ); ?></div>

	<div class="nav-tab-wrapper">
		<a href="admin.php?page=mailster_welcome" class="nav-tab nav-tab-active"><?php esc_html_e( 'What\'s New', 'mailster' ); ?></a>
		<?php if ( current_user_can( 'mailster_manage_templates' ) ) : ?>
		<a href="edit.php?post_type=newsletter&page=mailster_templates&more" class="nav-tab"><?php esc_html_e( 'Templates', 'mailster' ); ?></a>
		<?php endif; ?>
		<?php if ( current_user_can( 'mailster_manage_addons' ) ) : ?>
		<a href="edit.php?post_type=newsletter&page=mailster_addons" class="nav-tab"><?php esc_html_e( 'Add Ons', 'mailster' ); ?></a>
		<?php endif; ?>

	</div>

<?php if ( get_transient( '_mailster_mymail' ) ) : ?>

		<div class="feature-section one-col">
			<h2>Mailster is the new MyMail!</h2>

			<p>Providing our customer with the best email marketing software possible to increase their interaction and grow their business has always been our main goal. To serve you even better in the future we are changing some important things on how we manage and help our customers. Delivering outstanding support is key to us. For that reason we’ve worked hard and are now introducing our new support and license center.</p>
			<p>From now on you can manage all your Mailster licenses from your account and check the status of your support tickets. By giving our customers a dedicated place where they can reach out to us and get helped quickly we will increase our efficiency and be able to respond even faster to your questions.</p>
			<p>As our team and customer base is growing we try to expand every aspect of our business with many great new features for Mailster in the pipeline as well as improving existing functionality.</p>

			<p>Thanks for being a loyal Mailster (MyMail) customer. We are looking forward continuing to provide you with the last email marketing software you’ll ever have to buy!</p>

			<p>Your Mailster Team</p>

			<div class="mailster-transition">
				<div class="mailster-transition-box">
					<img src="https://mailster.github.io/welcome/My.svg" class="mailster-transition-my" width="162">
					<img src="https://mailster.github.io/welcome/Mail.svg" class="mailster-transition-mail" width="222">
					<img src="https://mailster.github.io/welcome/ster.svg" class="mailster-transition-ster" width="211">
				</div>
			</div>
		</div>

<?php endif; ?>

		<div class="feature-section one-col main-feature">
			<h2>Updated Editor</h2>
			<p>The new Editor helps to crate your campaigns faster and provides more options. You can now edit your text inline and see the result immediately.</p>
			<video loop muted preload="auto" autoplay src="https://mailster.github.io/videos/mailster_editor.mp4" poster="https://mailster.github.io/videos/mailster_editor.png">
				<source src="https://mailster.github.io/videos/mailster_editor.mp4" type="video/mp4">
			</video>
			<div class="return-to-dashboard align-center"><a href="post-new.php?post_type=newsletter">Create a new Campaign</a></div>
		</div>

		<div class="feature-section two-col">
			<div class="col">
				<div class="media-container">
				<?php
				echo wp_video_shortcode( array(
					'mp4'      => 'https://mailster.github.io/videos/segmentation.mp4',
					'poster'   => 'https://mailster.github.io/videos/segmentation.png',
					'width'    => 505,
					'height'   => 284,
					'autoplay' => true,
					'loop'     => true,
				) );
				?>
				</div>
				<h3>Improved Segmentation</h3>
				<p>Target your audience even better with improved segmentation.</p>
				<div class="return-to-dashboard"></div>
			</div>
			<div class="col">
				<div class="media-container">
					<img src="https://mailster.github.io/welcome/time_frame.jpg" width="505" height="284">
				</div>
				<h3>Delivery Time Frame</h3>
				<p>Mailster can now send campaign in a defined time frame.</p>
				<div class="return-to-dashboard"></div>
			</div>
			<div class="col">
				<div class="media-container">
				<?php
				echo wp_video_shortcode( array(
					'mp4'      => 'https://mailster.github.io/videos/image_crop.mp4',
					'poster'   => 'https://mailster.github.io/videos/image_crop.png',
					'width'    => 505,
					'height'   => 284,
					'autoplay' => true,
					'loop'     => true,
				) );
				?>
				</div>
				<h3>Improved Image Handling</h3>
				<p>The Mailster PicPicker allows to quickly select and crop images. It now also works better with third party plugins.</p>
				<div class="return-to-dashboard"></div>
			</div>
			<div class="col">
				<div class="media-container">
					<img src="https://mailster.github.io/welcome/subscriber_query.jpg" width="505" height="284">
				</div>
				<h3>Subscriber Query Class</h3>
				<p>Similar to the WP_Query you can now simple query your subscribers.</p>
				<div class="return-to-dashboard"></div>
			</div>
		</div>

		<div class="feature-section two-col">
			<div class="col">
				<h3>List Based Subscription</h3>
				<p>Mailster now handles list based subscription next to global subscription.</p>
				<div class="return-to-dashboard"></div>
			</div>
			<div class="col">
				<h3>Optional Web Version</h3>
				<p>Disable the web version individually for your campaigns an auto responders.</p>
				<div class="return-to-dashboard"></div>
			</div>
		</div>


		<div class="changelog">
			<h2>Further Improvements</h2>

			<div class="feature-section under-the-hood three-col">
				<div class="col">
					<h4>Notice Capabilities</h4>
					<p>Notifications are now shown to the targeted users.</p>
				</div>
				<div class="col">
					<h4>Disable Tracking</h4>
					<p>Disable tracking globally or on a campaign basis.</p>
				</div>
				<div class="col">
					<h4>Custom Template Screenshot</h4>
					<p>A screenshot.jpg file can now be used for the screenshots in template folders.</p>
				</div>
			</div>
			<div class="feature-section under-the-hood three-col">
				<div class="col">
					<h4>Optional Web version bar</h4>
					<p>You can now disable the web version bar.</p>
				</div>
				<div class="col">
					<h4>Tests</h4>
					<p>A dedicate test page helps to identify problems faster.</p>
				</div>
				<div class="col">
					<h4>XLS Export</h4>
					<p>Export your subscribers in native Excel format.</p>
				</div>
				<div class="col">
					<h4>Cron</h4>
					<p>The cron mechanism has been improved by splitting into multiple processes.</p>
				</div>
				<div class="col">
					<h4>Subscriber Grow indicator</h4>
					<p>You can now see exactly your subscriber gain on the WordPress dashboard.</p>
				</div>
				<div class="col">
					<h4>New <code>{lists}</code> tag</h4>
					<p>Display the lists of your campaign.</p>
				</div>
				<div class="col">
					<h4>Disable user Avatar</h4>
					<p>For more privacy you can now disable user avatars across Mailster</p>
				</div>
			</div>

		</div>
		<div class="clear"></div>

		<div class="return-to-dashboard">
			<a href="admin.php?page=mailster_dashboard">Back to Dashboard</a>
		</div>

<div class="clear"></div>

<div id="ajax-response"></div>
<br class="clear">
</div>
