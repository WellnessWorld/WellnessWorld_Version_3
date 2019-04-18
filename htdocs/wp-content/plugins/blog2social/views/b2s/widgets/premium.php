<h5 class="b2s-dashboard-h5"><?php _e('Your license: Blog2Social', 'blog2social') ?>
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
</h5>
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
<!--Features-->
<div class="hidden-xs">
    <br>
    <div class="row">
        <div class="col-xs-2 col-md-3 col-lg-2 col-hide-padding-left">
            <div class="thumbnail text-center">
                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/networks-choice.png', B2S_PLUGIN_FILE); ?>" alt="Network">
            </div>
        </div>
        <div class="col-xs-10 col-md-9 col-lg-10">
            <h6 class="b2s-dashboard-h6"><?php _e('Network Choice', 'blog2social') ?></h6>
            <p><?php _e('Cross-share to all popular social networks', 'blog2social') ?></p>
            <span class="pull-right label label-info">FREE</span>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-2 col-md-3 col-lg-2 col-hide-padding-left">
            <div class="thumbnail text-center">
                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/automation.png', B2S_PLUGIN_FILE); ?>" alt="Auto-Posting">
            </div>
        </div>
        <div class="col-xs-10 col-md-9 col-lg-10">
            <h6 class="b2s-dashboard-h6"><?php _e('Auto-Posting', 'blog2social') ?></h6>
            <p><?php _e('Automatically share your posts whenever you publish a new blog post', 'blog2social') ?></p>
            <span class="pull-right label label-success"><a target="_blank" class="btn-label-premium" href="<?php echo B2S_Tools::getSupportLink('affiliate'); ?>">PREMIUM</a></span>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-2 col-md-3 col-lg-2  col-hide-padding-left">
            <div class="thumbnail text-center">
                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/advanced-customization.png', B2S_PLUGIN_FILE); ?>" alt="Customization">
            </div>
        </div>
        <div class="col-xs-10 col-md-9 col-lg-10">
            <h6 class="b2s-dashboard-h6"><?php _e('Custom Sharing', 'blog2social') ?></h6>
            <p><?php _e('Edit or add comments, hashtags or handles. Edit posts in HTML for re-publishing on blogging networks', 'blog2social') ?></p>
            <span class="pull-right label label-info">FREE</span>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-2 col-md-3 col-lg-2  col-hide-padding-left">
            <div class="thumbnail text-center">
                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/best-time-scheduling.png', B2S_PLUGIN_FILE); ?>" alt="Scheduling">
            </div>
        </div>
        <div class="col-xs-10 col-md-9 col-lg-10">
            <h6 class="b2s-dashboard-h6"><?php _e('Best Time Scheduler', 'blog2social') ?></h6>
            <p><?php _e('Choose pre-defined times to post or edit and define your own time settings', 'blog2social') ?></p>
            <span class="pull-right label label-success"><a target="_blank" class="btn-label-premium" href="<?php echo B2S_Tools::getSupportLink('affiliate'); ?>">PREMIUM</a></span>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-2 col-md-3 col-lg-2  col-hide-padding-left">
            <div class="thumbnail text-center">
                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/advanced-scheduling.png', B2S_PLUGIN_FILE); ?>" alt="Advanced Scheduling">
            </div>
        </div>
        <div class="col-xs-10 col-md-9 col-lg-10">
            <h6 class="b2s-dashboard-h6"><?php _e('Custom Scheduling', 'blog2social') ?></h6>
            <p><?php _e('Unlimited scheduling options: once, repeatedly or recurringly to multiple profiles, pages and groups', 'blog2social') ?></p>
            <span class="pull-right label label-success"><a target="_blank" class="btn-label-premium" href="<?php echo B2S_Tools::getSupportLink('affiliate'); ?>">PREMIUM</a></span>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-2 col-md-3 col-lg-2  col-hide-padding-left">
            <div class="thumbnail text-center">
                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/one-step-workflow.png', B2S_PLUGIN_FILE); ?>" alt="One-Step Workflow">
            </div>
        </div>
        <div class="col-xs-10 col-md-9 col-lg-10">
            <h6 class="b2s-dashboard-h6"><?php _e('One-Step Workflow', 'blog2social') ?></h6>
            <p><?php _e('One-page preview editor for all social networks for easy customizing', 'blog2social') ?></p>
            <span class="pull-right label label-info">FREE</span>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-2 col-md-3 col-lg-2  col-hide-padding-left">
            <div class="thumbnail text-center">
                <img class="b2s-feature-img-with-90" src="<?php echo plugins_url('/assets/images/features/reporting.png', B2S_PLUGIN_FILE); ?>" alt="Reporting">
            </div>
        </div>
        <div class="col-xs-10 col-md-9 col-lg-10">
            <h6 class="b2s-dashboard-h6"><?php _e('Reporting', 'blog2social') ?></h6>
            <p><?php _e('All scheduled and published social media posts with direct links for easy access or re-sharing', 'blog2social') ?></p>
            <span class="pull-right label label-success"><a target="_blank" class="btn-label-premium" href="<?php echo B2S_Tools::getSupportLink('affiliate'); ?>">PREMIUM</a></span>
        </div>
    </div>
</div>
<br>
<?php if (B2S_PLUGIN_USER_VERSION == 0) { ?>
    <a class="btn btn-primary btn-lg btn-block" href="<?php echo B2S_Tools::getSupportLink('affiliate'); ?>"><?php _e('Unlock Premium', 'blog2social') ?></a>
<?php
}