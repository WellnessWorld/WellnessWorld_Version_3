<?php
$b2sSiteUrl = get_option('siteurl') . ((substr(get_option('siteurl'), -1, 1) == '/') ? '' : '/');
$b2sGeneralOptions = get_option('B2S_PLUGIN_GENERAL_OPTIONS');
?>

<div class="b2s-container">
    <div class="b2s-inbox">
<?php require_once (B2S_PLUGIN_DIR . 'views/b2s/html/header.phtml'); ?>
        <div class="col-md-6 del-padding-left">
            <div class="panel panel-default">
                <div class="panel-body" style="min-height: 188px">
                    <div class="grid-body">
<?php require_once (B2S_PLUGIN_DIR . 'views/b2s/widgets/newsletter.php'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 del-padding-left">
            <div class="panel panel-default">
                <div class="panel-body" style="min-height: 188px">
                    <div class="grid-body">
<?php require_once (B2S_PLUGIN_DIR . 'views/b2s/widgets/tutorial.php'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-12 del-padding-left">
            <div class="panel panel-default">
                <div class="panel-body"  style="height: 500px;">
                    <div class="grid-body">
<?php require_once (B2S_PLUGIN_DIR . 'views/b2s/widgets/calendar.php'); ?>
                    </div>
                </div>
            </div>

        </div>
        <div class="clearfix"></div>
        <div class="col-md-6 del-padding-left">
            <div class="panel panel-default">
                <div class="panel-body"  style="height: 381px;">
                    <div class="grid-body">
<?php require_once (B2S_PLUGIN_DIR . 'views/b2s/widgets/activity.php'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 del-padding-left">
            <div class="panel panel-default">
                <div class="panel-body" style="height: 381px;">
                    <div class="grid-body">
<?php require_once (B2S_PLUGIN_DIR . 'views/b2s/widgets/posts.php'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 del-padding-left">
            <div class="panel panel-default">
                <div class="panel-body" style="min-height: 280px;">
                    <div class="grid-body">
<?php require_once (B2S_PLUGIN_DIR . 'views/b2s/widgets/content.php'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 del-padding-left">
            <div class="panel panel-default">
                <div class="panel-body" style="min-height: 280px;">
                    <div class="grid-body">
<?php require_once (B2S_PLUGIN_DIR . 'views/b2s/widgets/support.php'); ?>
                    </div>
                </div>
            </div>
        </div>


        <div class="clearfix"></div>
<?php if (B2S_PLUGIN_USER_VERSION > 0) { ?>
            <div class="col-md-12 del-padding-left" style="text-align: center;">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <h5 class="b2s-dashboard-h5"><?php _e('Couldn\'t find your answer?', 'blog2social') ?></h5>
                        <a target="_blank" class="btn btn-primary" href="<?php echo B2S_Tools::getSupportLink('faq'); ?>">
                            <span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> <?php _e('Contact Support by Email', 'blog2social') ?>
                        </a>
                        <span class="btn btn-success b2s-dashoard-btn-phone"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span> <?php _e('Call us: +49 2181 7569-277', 'blog2social') ?></span>
                        <br>
                        <div class="b2s-info-sm"><?php _e('(Call times: from 9:00 a.m. to 5:00 p.m. CET on working days)', 'blog2social') ?></div>
                    </div>
                </div>
            </div>
<?php } ?>
    </div>
</div>
<div class="col-md-12">
<?php
$noLegend = 1;
require_once (B2S_PLUGIN_DIR . 'views/b2s/html/footer.phtml');
?>
</div>
<div class="clearfix"></div>

<input type="hidden" id="b2s-redirect-url-sched-post" value="<?php echo $b2sSiteUrl . 'wp-admin/admin.php?page=blog2social-sched'; ?>"/>
<input type="hidden" id="isLegacyMode" value="<?php echo (isset($b2sGeneralOptions['legacy_mode']) ? (int) $b2sGeneralOptions['legacy_mode'] : 0); ?>">
