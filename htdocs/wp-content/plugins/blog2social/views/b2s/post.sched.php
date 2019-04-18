<?php
/* Data */
require_once (B2S_PLUGIN_DIR . 'includes/B2S/Post/Filter.php');
require_once (B2S_PLUGIN_DIR . 'includes/Util.php');
require_once B2S_PLUGIN_DIR . 'includes/B2S/Settings/Item.php';
$b2sShowByDate = isset($_GET['b2sShowByDate']) ? trim($_GET['b2sShowByDate']) : "";
$b2sUserAuthId = isset($_GET['b2sUserAuthId']) ? (int) $_GET['b2sUserAuthId'] : "";
$b2sPostBlogId = isset($_GET['b2sPostBlogId']) ? (int) $_GET['b2sPostBlogId'] : "";
$b2sShowByNetwork = isset($_GET['b2sShowByNetwork']) ? (int) $_GET['b2sShowByNetwork'] : "";
$options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
$optionUserTimeZone = $options->_getOption('user_time_zone');
$userTimeZone = ($optionUserTimeZone !== false) ? $optionUserTimeZone : get_option('timezone_string');
$userTimeZoneOffset = (empty($userTimeZone)) ? get_option('gmt_offset') : B2S_Util::getOffsetToUtcByTimeZone($userTimeZone);
$metaSettings = get_option('B2S_PLUGIN_GENERAL_OPTIONS');
?>

<div class="b2s-container">
    <div class="b2s-inbox">
        <?php require_once (B2S_PLUGIN_DIR . 'views/b2s/html/header.phtml'); ?>
        <div class="col-md-12 del-padding-left">
            <div class="col-md-9 del-padding-left">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <!--Posts from Wordpress Start-->
                        <!--Filter Start-->
                        <div class="b2s-post">
                            <div class="grid-body">
                                <div class="pull-right"><code id="b2s-user-time"><?php echo B2S_Util::getLocalDate($userTimeZoneOffset, substr(B2S_LANGUAGE, 0, 2)); ?> <?php echo ((substr(B2S_LANGUAGE, 0, 2) == 'de') ? __('Uhr', 'blog2social') : '') ?></code></div>
                                <?php require_once (B2S_PLUGIN_DIR . 'views/b2s/html/post.navbar.phtml'); ?>
                                <!-- Filter Post Start-->
                                <form class="b2sSortForm form-inline pull-left" action="#">
                                    <input id="b2sType" type="hidden" value="sched" name="b2sType">
                                    <input id="b2sShowByDate" type="hidden" value="<?php echo $b2sShowByDate; ?>" name="b2sShowByDate">
                                    <input id="b2sUserAuthId" type="hidden" value="<?php echo $b2sUserAuthId; ?>" name="b2sUserAuthId">
                                    <input id="b2sPostBlogId" type="hidden" value="<?php echo $b2sPostBlogId; ?>" name="b2sPostBlogId">
                                    <input id="b2sShowByNetwork" type="hidden" value="<?php echo $b2sShowByNetwork; ?>" name="b2sShowByNetwork">
                                    <input id="b2sPagination" type="hidden" value="1" name="b2sPagination">
                                    <?php
                                    $postFilter = new B2S_Post_Filter('sched');
                                    echo $postFilter->getItemHtml('blog2social-sched');
                                    ?>
                                </form>
                                <!-- Filter Post Ende-->
                                <br/>
                            </div>       
                        </div>
                        <div class="clearfix"></div> 
                        <!--Filter End-->
                        <div class="b2s-sort-area">
                            <div class="b2s-loading-area" style="display:none">
                                <br>
                                <div class="b2s-loader-impulse b2s-loader-impulse-md"></div>
                                <div class="clearfix"></div>
                                <div class="text-center b2s-loader-text"><?php _e("Loading...", "blog2social"); ?></div>
                            </div>
                            <div class="row b2s-sort-result-area">
                                <div class="col-md-12">
                                    <ul class="list-group b2s-sort-result-item-area"></ul>
                                    <br>
                                    <nav class="b2s-sort-pagination-area text-center"></nav>
                                    <?php require_once (B2S_PLUGIN_DIR . 'views/b2s/html/footer.phtml'); ?> 
                                </div>
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

<div class="modal fade b2s-delete-sched-modal" tabindex="-1" role="dialog" aria-labelledby="b2s-delete-sched-modal" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="b2s-modal-close close" data-modal-name=".b2s-delete-sched-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php _e('Delete entries form the scheduling', 'blog2social') ?></h4>
            </div>
            <div class="modal-body">
                <b><?php _e('You are sure, you want to delete entries from the scheduling?', 'blog2social') ?> </b>
                <br>
                (<?php _e('Number of entries', 'blog2social') ?>:  <span id="b2s-delete-confirm-post-count"></span>)
                <input type="hidden" value="" id="b2s-delete-confirm-post-id">
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal"><?php _e('NO', 'blog2social') ?></button>
                <button class="btn btn-danger b2s-sched-delete-confirm-btn"><?php _e('YES, delete', 'blog2social') ?></button>
            </div>
        </div>
    </div>
</div>

<div id="b2s-network-select-image" class="modal fade" role="dialog" aria-labelledby="b2s-network-select-image" aria-hidden="true" style="z-index: 1070">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="b2s-modal-close close" data-modal-name="#b2s-network-select-image">&times;</button>
                <h4 class="modal-title"><?php _e('Select image for', 'blog2social') ?> <span class="b2s-selected-network-for-image-info"></span></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div class="b2s-network-select-image-content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div id="b2s-post-ship-item-post-format-modal" class="modal fade" role="dialog" aria-labelledby="b2s-post-ship-item-post-format-modal" aria-hidden="true" data-backdrop="false" style="z-index: 1070">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="b2s-modal-close close" data-modal-name="#b2s-post-ship-item-post-format-modal">&times;</button>
                <h4 class="modal-title"><?php _e('Choose your', 'blog2social') ?> <span id="b2s-post-ship-item-post-format-network-title"></span> <?php _e('Post Format', 'blog2social') ?>
                    <?php if (B2S_PLUGIN_USER_VERSION >= 2) { ?>
                        <?php _e('for:', 'blog2social') ?> <span id="b2s-post-ship-item-post-format-network-display-name"></span>
                    <?php } ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <?php
                        $settingsItem = new B2S_Settings_Item();
                        echo $settingsItem->setNetworkSettingsHtml();
                        echo $settingsItem->getNetworkSettingsHtml();
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="text-center">
                            <br>
                            <div class="b2s-post-format-settings-info" data-network-id="1" style="display:none;">
                                <b><?php _e('Define the default settings for the custom post format for all of your Facebook accounts in the Blog2Social settings.', 'blog2social'); ?></b>
                            </div>
                            <div class="b2s-post-format-settings-info" data-network-id="2" style="display:none;">
                                <b><?php _e('Define the default settings for the custom post format for all of your Twitter accounts in the Blog2Social settings.', 'blog2social'); ?></b>
                            </div>
                            <div class="b2s-post-format-settings-info" data-network-id="10" style="display:none;">
                                <b><?php _e('Define the default settings for the custom post format for all of your Google+ accounts in the Blog2Social settings.', 'blog2social'); ?></b>
                            </div>
                            <div class="b2s-post-format-settings-info" data-network-id="12" style="display:none;">
                                <b><?php _e('Define the default settings for the custom post format for all of your Instagram accounts in the Blog2Social settings.', 'blog2social'); ?></b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<input type="hidden" id="b2sLang" value="<?php echo substr(B2S_LANGUAGE, 0, 2); ?>">
<input type="hidden" id="b2sJSTextAddPost" value="<?php echo _e("add post", "blog2social"); ?>">                    
<input type="hidden" id="b2sUserLang" value="<?php echo strtolower(substr(get_locale(), 0, 2)); ?>">
<input type='hidden' id="user_timezone" name="user_timezone" value="<?php echo $userTimeZoneOffset; ?>">
<input type="hidden" id="user_version" name="user_version" value="<?php echo B2S_PLUGIN_USER_VERSION; ?>">
<input type="hidden" id="b2sDefaultNoImage" value="<?php echo plugins_url('/assets/images/no-image.png', B2S_PLUGIN_FILE); ?>">
<input type="hidden" id="b2sPostId" value="">
<input type="hidden" id="b2sInsertImageType" value="0">
<input type="hidden" id="isOgMetaChecked" value="<?php echo (isset($metaSettings['og_active']) ? (int) $metaSettings['og_active'] : 0); ?>">
<input type="hidden" id="isCardMetaChecked" value="<?php echo (isset($metaSettings['card_active']) ? (int) $metaSettings['card_active'] : 0); ?>">

<script>
    var b2s_has_premium = <?= B2S_PLUGIN_USER_VERSION > 0 ? "true" : "false"; ?>;
    var b2s_plugin_url = '<?= B2S_PLUGIN_URL; ?>';
    var b2s_post_formats = <?= json_encode(array('post' => array(__('Link Post', 'blog2social'), __('Photo Post', 'blog2social')), 'image' => array(__('Image with frame'), __('Image cut out')))); ?>;
    var b2s_is_calendar = true;
</script>