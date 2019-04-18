<div style="max-width: 1150px;margin: 0 auto;">
    <div class="b2s-container">
        <div class="b2s-inbox">
            <?php require_once (B2S_PLUGIN_DIR . 'views/b2s/html/header.phtml'); ?>
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="grid-body">
                            <h2 style="margin-top:0"><?php _e('Your license: Blog2Social', 'blog2social') ?>
                                <span class="b2s-key-name">
                                    <?php
                                    $versionType = unserialize(B2S_PLUGIN_VERSION_TYPE);
                                    if (defined("B2S_PLUGIN_TRAIL_END") && strtotime(B2S_PLUGIN_TRAIL_END) > time()) {
                                        echo 'FREE-TRIAL (' . $versionType[B2S_PLUGIN_USER_VERSION] . ')';
                                    } else {
                                        echo $versionType[B2S_PLUGIN_USER_VERSION];
                                    }
                                    ?>
                                </span>
                            </h2>
                            <p><?php _e('Upgrade to Blog2Social Premium to schedule your posts for the best time, once or recurringly with the Best Time Scheduler and post to pages, groups and multiple accounts per network.', 'blog2social') ?>
                                <a target="_blank" class="b2s-btn-link" href="<?php echo B2S_Tools::getSupportLink('feature'); ?>"><?php _e('Learn more', 'blog2social') ?></a></p>
                            <div class="clearfix"></div>
                            <br>
                            <div class="b2s-key-area">
                                <div class="input-group">
                                    <input class="form-control input-sm b2s-key-area-input" placeholder="<?php _e('Enter license key and change your version', 'blog2social'); ?>" value="" type="text">
                                    <span class="input-group-btn">
                                        <button class="btn btn-success btn-sm b2s-key-area-btn-submit"><?php _e('Activate', 'blog2social'); ?></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--Features-->
            <div class="hidden-xs">
                <br>
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div class="grid-body">
                                <div class="col-md-8 del-padding-left del-padding-right col-lg-offset-2">
                                    <div class="row">
                                        <div class="col-xs-2 col-md-3 col-lg-3 col-hide-padding-left">
                                            <div class="thumbnail text-center">
                                                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/networks-choice.png', B2S_PLUGIN_FILE); ?>" alt="Network">
                                            </div>
                                            <p>
                                                <strong><?php _e('Social Media Sharing', 'blog2social') ?></strong><br>
                                                <?php _e('Cross-share to all popular social networks', 'blog2social') ?>
                                            </p>
                                        </div>
                                        <div class="col-xs-2 col-md-3 col-lg-3 col-hide-padding-left">
                                            <div class="thumbnail text-center">
                                                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/automation.png', B2S_PLUGIN_FILE); ?>" alt="Auto-Posting">
                                            </div>
                                            <p>
                                                <strong><?php _e('Auto Posting', 'blog2social') ?></strong><br>
                                                <?php _e('Automatically share your posts whenever you publish a new blog post', 'blog2social') ?>
                                            </p>
                                        </div>
                                        <div class="col-xs-2 col-md-3 col-lg-3  col-hide-padding-left">
                                            <div class="thumbnail text-center">
                                                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/advanced-customization.png', B2S_PLUGIN_FILE); ?>" alt="Customization">
                                            </div>
                                            <p>
                                                <strong><?php _e('Customizing Social Media Posts', 'blog2social') ?></strong><br>
                                                <?php _e('Edit or add comments, hashtags or handles. Edit posts in HTML for re-publishing on blogging networks', 'blog2social') ?>
                                            </p>
                                        </div>
                                        <div class="col-xs-2 col-md-3 col-lg-3  col-hide-padding-left">
                                            <div class="thumbnail text-center">
                                                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/best-time-scheduling.png', B2S_PLUGIN_FILE); ?>" alt="Scheduling">
                                            </div>
                                            <p>
                                                <strong><?php _e('Best Time Scheduler', 'blog2social') ?></strong><br>
                                                <?php _e('Choose pre-defined times to post or edit and define your own time settings', 'blog2social') ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-xs-2 col-md-3 col-lg-3  col-hide-padding-left">
                                            <div class="thumbnail text-center">
                                                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/advanced-scheduling.png', B2S_PLUGIN_FILE); ?>" alt="Advanced Scheduling">
                                            </div>
                                            <p>
                                                <strong><?php _e('Social Media Scheduler', 'blog2social') ?></strong><br>
                                                <?php _e('Social media scheduling: once, repeatedly or recurrently to multiple profiles, pages and groups', 'blog2social') ?>
                                            </p>
                                        </div>
                                        <div class="col-xs-2 col-md-3 col-lg-3  col-hide-padding-left">
                                            <div class="thumbnail text-center">
                                                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/image-select.png', B2S_PLUGIN_FILE); ?>" alt="One-Step Workflow">
                                            </div>
                                            <p>
                                                <strong><?= _e('Individual Images for Each Social Media Post', 'blog2social'); ?></strong><br>
                                                <?php _e('Select any image from your media gallery for each social media post and channel', 'blog2social') ?>
                                            </p>
                                        </div>
                                        <div class="col-xs-2 col-md-3 col-lg-3  col-hide-padding-left">
                                            <div class="thumbnail text-center">
                                                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/post-types.png', B2S_PLUGIN_FILE); ?>" alt="One-Step Workflow">
                                            </div>
                                            <p>
                                                <strong><?php _e('Select Post Format', 'blog2social') ?></strong><br>
                                                <?php _e('One-page preview editor for all social networks for easy customizing', 'blog2social') ?>
                                            </p>
                                        </div>
                                        <div class="col-xs-2 col-md-3 col-lg-3  col-hide-padding-left">
                                            <div class="thumbnail text-center">
                                                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/reporting.png', B2S_PLUGIN_FILE); ?>" alt="Reporting">
                                            </div>
                                            <p>
                                                <strong><?php _e('Social Media Reporting', 'blog2social'); ?></strong>
                                                <?php _e('Keep track of your scheduled and shared posts', 'blog2social') ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row" style="text-align: center;margin-top: 15px;margin-bottom: 15px;">
                                        <a class="btn btn-success" href="<?php echo B2S_Tools::getSupportLink('affiliate'); ?>" target="_blank">   <?php _e('Show me plans and prices', 'blog2social') ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>