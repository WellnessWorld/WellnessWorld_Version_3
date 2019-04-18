<?php
/* Data */
$options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
$optionUserTimeZone = $options->_getOption('user_time_zone');
$userTimeZone = ($optionUserTimeZone !== false) ? $optionUserTimeZone : get_option('timezone_string');
$userTimeZoneOffset = (empty($userTimeZone)) ? get_option('gmt_offset') : B2S_Util::getOffsetToUtcByTimeZone($userTimeZone);
$selSchedDate = (isset($_GET['schedDate']) && !empty($_GET['schedDate'])) ? date("Y-m-d H:i:s", (strtotime($_GET['schedDate'] . ' ' . gmdate('H:i:s')))) : "";    //routing from calendar
?>
<div class="b2s-container">
    <div class="b2s-inbox">
        <?php require_once (B2S_PLUGIN_DIR . 'views/b2s/html/header.phtml'); ?>
        <div class="col-md-12 del-padding-left">
            <div class="col-md-9 del-padding-left">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="b2s-post">
                            <div class="grid-body">
                                <?php require_once (B2S_PLUGIN_DIR . 'views/b2s/html/post.navbar.phtml'); ?>
                                <br>
                            </div>       
                        </div>
                        <div class="clearfix"></div>
                        <div id="b2s-curation-no-review-info" class="alert alert-danger">
                            <span class="glyphicon glyphicon-remove glyphicon-danger"></span> <?php _e('No link preview available. Please check your link.', 'blog2social'); ?>

                        </div>
                        <div id="b2s-curation-no-auth-info" class="alert alert-danger">
                            <span class="glyphicon glyphicon-remove glyphicon-danger"></span> <?php _e('No connected networks. Please make sure to connect at least one social media account.', 'blog2social'); ?>
                        </div>
                        <div id="b2s-curation-no-data-info" class="alert alert-danger">
                            <span class="glyphicon glyphicon-remove glyphicon-danger"></span> <?php _e('Invalid data. Please check your data.', 'blog2social'); ?>
                        </div>
                        <div class="b2s-curation-area">
                            <div class="row b2s-curation-input-area">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <p class="b2s-curation-input-area-info-header-text"> <?php _e("Enter a link you want share on your social media channels", "blog2social"); ?></p>
                                        <small id="b2s-curation-input-url-help" class="form-text text-muted b2s-color-text-red"><?php _e("Please enter a valid link", "blog2social") ?></small>
                                        <input type="email" class="form-control" id="b2s-curation-input-url" value="" placeholder="<?php _e("Enter link", "blog2social"); ?>">
                                        <div class="clearfix"></div>
                                        <div class="b2s-curation-input-area-btn">
                                            <button class="btn btn-primary b2s-btn-curation-continue"><?php _e("continue", "blog2social"); ?></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="b2s-loading-area" style="display:none">
                                    <br>
                                    <div class="b2s-loader-impulse b2s-loader-impulse-md"></div>
                                    <div class="clearfix"></div>
                                    <div class="text-center b2s-loader-text"><?php _e("Load data...", "blog2social"); ?></div>
                                </div>
                                <div class="row b2s-curation-result-area">
                                    <div class="col-md-12">
                                        <form id="b2s-curation-post-form" method="post">
                                            <input type="hidden" id="b2s_user_timezone" name="b2s_user_timezone" value="<?php echo $userTimeZoneOffset ?>">
                                            <div class="b2s-curation-preview-area"></div>
                                            <div class="clearfix"></div>
                                            <div class="b2s-curation-settings-area"></div>
                                        </form>
                                    </div>

                                    <input type="hidden" id="b2sSelSchedDate" value="<?php echo (($selSchedDate != "") ? strtotime($selSchedDate) . '000' : ''); ?>">
                                    <input type="hidden" id="b2sServerUrl" value="<?php echo B2S_PLUGIN_SERVER_URL; ?>">
                                    <input type="hidden" id="b2sJsTextPublish" value="<?php _e('published', 'blog2social') ?>">
                                </div>
                                <div class="row b2s-curation-post-list-area"></div>     
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php require_once (B2S_PLUGIN_DIR . 'views/b2s/html/service.phtml'); ?>
            <?php require_once (B2S_PLUGIN_DIR . 'views/b2s/html/sidebar.phtml'); ?>
        </div>
    </div>
</div>

<div class="modal fade b2s-publish-approve-modal" tabindex="-1" role="dialog" aria-labelledby="b2s-publish-approve-modal" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e('Do you want to mark this post as published ?', 'blog2social') ?> </h4>
            </div>
            <div class="modal-body">
                <input type="hidden" value="" id="b2s-approve-network-auth-id">
                <input type="hidden" value="" id="b2s-approve-post-id">
                <button class="btn btn-success b2s-approve-publish-confirm-btn"><?php _e('YES', 'blog2social') ?></button>
                <button class="btn btn-default" data-dismiss="modal"><?php _e('NO', 'blog2social') ?></button>
            </div>
        </div>
    </div>
</div>



<div id="b2s-sched-post-modal" class="modal fade" role="dialog" aria-labelledby="b2s-sched-post-modal" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="b2s-modal-close close" data-modal-name="#b2s-sched-post-modal">&times;</button>
                <h4 class="modal-title"><?php _e('Need to schedule your posts?', 'blog2social') ?></h4>
            </div>
            <div class="modal-body">
                <p><?php _e('Blog2Social Premium covers everything you need.', 'blog2social') ?></p>
                <br>
                <div class="clearfix"></div>
                <b><?php _e('Schedule for specific dates', 'blog2social') ?></b>
                <p><?php _e('You want to publish a post on a specific date? No problem! Just enter your desired date and you are ready to go!', 'blog2social') ?></p>
                <br>
                <b><?php _e('Schedule post recurrently', 'blog2social') ?></b>
                <p><?php _e('You have evergreen content you want to re-share from time to time in your timeline? Schedule your evergreen content to be shared once, multiple times or recurringly at specific times.', 'blog2social') ?></p>
                <br>
                <b><?php _e('Best Time Scheduler', 'blog2social') ?></b>
                <p><?php _e('Whenever you publish a post, only a fraction of your followers will actually see your post. Use the Blog2Social Best Times Scheduler to share your post at the best times for each social network. Get more outreach and extend the lifespan of your posts.', 'blog2social') ?></p>
                <br>
                <?php if (B2S_PLUGIN_USER_VERSION == 0) { ?>
                    <hr>
                    <?php _e('With Blog2Social Premium you can:', 'blog2social') ?>
                    <br>
                    <br>
                    <span class="glyphicon glyphicon-ok glyphicon-success"></span> <?php _e('Post on pages and groups', 'blog2social') ?><br>
                    <span class="glyphicon glyphicon-ok glyphicon-success"></span> <?php _e('Share on multiple profiles, pages and groups', 'blog2social') ?><br>
                    <span class="glyphicon glyphicon-ok glyphicon-success"></span> <?php _e('Auto-post and auto-schedule new and updated blog posts', 'blog2social') ?><br>
                    <span class="glyphicon glyphicon-ok glyphicon-success"></span> <?php _e('Schedule your posts at the best times on each network', 'blog2social') ?><br>
                    <span class="glyphicon glyphicon-ok glyphicon-success"></span> <?php _e('Best Time Manager: use predefined best time scheduler to auto-schedule your social media posts', 'blog2social') ?><br>
                    <span class="glyphicon glyphicon-ok glyphicon-success"></span> <?php _e('Schedule your post for one time, multiple times or recurrently', 'blog2social') ?><br>
                    <span class="glyphicon glyphicon-ok glyphicon-success"></span> <?php _e('Schedule and re-share old posts', 'blog2social') ?><br>
                    <span class="glyphicon glyphicon-ok glyphicon-success"></span> <?php _e('Select link format or image format for your posts', 'blog2social') ?><br>
                    <span class="glyphicon glyphicon-ok glyphicon-success"></span> <?php _e('Select individual images per post', 'blog2social') ?><br>
                    <span class="glyphicon glyphicon-ok glyphicon-success"></span> <?php _e('Reporting & calendar: keep track of your published and scheduled social media posts', 'blog2social') ?><br>
                    <br>
                    <a target="_blank" href="<?php echo B2S_Tools::getSupportLink('affiliate'); ?>" class="btn btn-success center-block"><?php _e('Upgrade to PREMIUM', 'blog2social') ?></a>
                    <br>
                    <center><?php _e('or <a href="http://service.blog2social.com/trial" target="_blank">start with free 30-days-trial of Blog2Social Premium</a> (no payment information needed)', 'blog2social') ?></center>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
