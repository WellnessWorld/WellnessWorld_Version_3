<div style="max-width: 1150px;margin: 0 auto;">
    <div class="b2s-container">
        <div class="b2s-inbox">
            <?php require_once (B2S_PLUGIN_DIR . 'views/b2s/html/header.phtml'); ?>
            <div class="col-md-12 del-padding-left">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="grid-body">
                            <h5 class="b2s-dashboard-h5"><?php _e('How to use Blog2Social','blog2social'); ?></h5>
                            <div class="col-md-6 del-padding-left">
                                <div class="embed-responsive embed-responsive-16by9">
                                    <iframe class="border embed-responsive-item" type="text/html" src="https://www.youtube.com/embed/YYjlIgWOGTU" frameborder="0" allowfullscreen></iframe>
                                </div>
                            </div>
                            <div class="col-md-6 del-padding-right">
                                <p id="b2s_howto_text">
                                    <?php _e('Learn how to get the most out of Blog2Social to promote your blog on social media. Find step-by-step instructions and tips for FREE and PREMIUM users.','blog2social'); ?>
                                </p>
                                <a class="btn btn-primary btn-block btn-lg" href="<?php echo B2S_Tools::getSupportLink('howto'); ?>" target="_blank">Blog2Social Manual</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 del-padding-left">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="grid-body">
                            <div class="col-md-6 clearfix">
                                <h5 class="b2s-dashboard-h5"><?php _e('Do you need help?','blog2social'); ?></h5>
                                <p id="b2s_faq_text">
                                    <?php _e('Find answers to common questions in our FAQ.','blog2social'); ?>
                                </p>
                                <form action="<?php echo B2S_Tools::getSupportLink('faq_direct'); ?>" method="GET" target="_blank">
                                    <input type="hidden" name="action" value="search" />
                                    <input name="search" class="form-control" style="width: 100%;margin-bottom: 10px;">
                                    <button class="btn btn-success" style="float:right;"><?php _e('search FAQ','blog2social'); ?></button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <h5 class="b2s-dashboard-h5"><?php _e('TOP 5 FAQ','blog2social'); ?></h5>
                                <div class="b2s-faq-area">
                                    <div class="b2s-loading-area-faq" style="display:block">
                                        <br>
                                        <div class="b2s-loader-impulse b2s-loader-impulse-md"></div>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="b2s-faq-content"></div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>