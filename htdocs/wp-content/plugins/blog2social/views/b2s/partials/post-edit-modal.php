<div id="b2s-edit-event-modal-<?= $item->getB2SId(); ?>" class="modal fade" role="dialog" aria-labelledby="b2s-trial-modal" aria-hidden="true" data-backdrop="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="b2s-modal-close close release_locks" data-modal-name="#b2s-edit-event-modal-<?= $item->getB2SId(); ?>">&times;</button>
                <h4 class="modal-title">
                    <?php echo __("Edit Post", "blog2social"); ?>
                    <?php if (B2S_PLUGIN_USER_VERSION == 0) { ?>
                        <span class="label label-success"><a href="#" class="b2s-btn-label-premium btn-label-premium-xs b2s-info-btn" data-modal-target="b2sInfoMetaBoxModalAutoPost"><?= __("PREMIUM", "blog2social"); ?></a></span>
                    <?php } ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <form>
                            <input type="hidden" class="b2s-input-hidden" name="action" value="b2s_edit_save_post" />
                            <input type="hidden" class="b2s-input-hidden" id="post_id" name="post_id" value="<?= $item->getPostId(); ?>">
                            <input type="hidden" class="b2s-input-hidden" id="original_blog_user_id" name="original_blog_user_id" value="<?= $item->getBlogUserId(); ?>">
                            <input type="hidden" class="b2s-input-hidden" name="b2s_id" value="<?= $item->getB2SId(); ?>">
                            <input type="hidden" class="b2s-input-hidden" name="sched_details_id" value="<?= $item->getSchedDetailsId(); ?>">
                            <input type="hidden" class="b2s-input-hidden" id="save_method" name="save_method" value="apply-this" />
                            <input type="hidden" class="b2sChangeOgMeta b2s-input-hidden" data-network-auth-id="<?= $item->getNetworkAuthId(); ?>" name="change_og_meta" value="0">
                            <input type="hidden" class="b2sChangeCardMeta b2s-input-hidden"  data-network-auth-id="<?= $item->getNetworkAuthId(); ?>" name="change_card_meta" value="0">
                            <input type="hidden" class="b2s-input-hidden" id="b2sUserTimeZone" name="user_timezone" value="0">
                            <input type="hidden" class="b2s-input-hidden" id="b2sRelayPrimaryPostId" name="relay_primary_post_id" value="<?= $item->getRelayPrimaryPostId(); ?>">
                            <input type="hidden" class="b2s-input-hidden" id="b2sRelayPrimarySchedDate" name="relay_primary_sched_date" value="<?= $item->getRelayPrimarySchedDate(); ?>">
                            <input type="hidden" class="b2s-input-hidden" id="b2sPostForRelay" name="post_for_relay" value="<?= $item->getPostForRelay(); ?>">
                            <input type="hidden" class="b2s-input-hidden" id="b2sPostForApprove" name="post_for_approve" value="<?= $item->getPostForApprove(); ?>">
                            <input type="hidden" class="b2s-input-hidden" id="b2sCurrentPostFormat" value="<?= $item->getPostFormat(); ?>">
                            <input type="hidden" class="b2s-input-hidden" id="b2sNetworkAuthId" name="network_auth_id" value="<?= $item->getNetworkAuthId(); ?>">

                            <?php if ($lock_user_id && $lock_user_id != get_current_user_id()) { ?>
                                <div class="alert alert-danger">
                                    <?= str_replace("%1", esc_html($lock_user->user_login), __('This post is blocked by %1', 'blog2social')); ?>.
                                </div>
                            <?php } ?>

                            <?= $item->getEditHtml(); ?>
                            <div class="panel panel-group">
                                <div class="b2s-post-item-details-release-area-details">
                                    <!-- deprecated Network Xing,G+ 8,10 -->
                                    <?php if ($item->getNetworkId() == 8 || $item->getNetworkId() == 10) { ?>
                                        <div class="network-tos-deprecated-warning alert alert-danger" style="display: none;" data-network-id="<?= $item->getNetworkId(); ?>" data-network-count="0"  data-network-auth-id="<?= $item->getNetworkAuthId(); ?>">
                                            <?php
                                            if ($item->getNetworkId() == 8) {
                                                _e("Please note: Your account is connected via an old XING API that is no longer supported by XING after March 31. Please connect your XING profile, as well as your XING company pages (Employer branding profiles) and business pages with the new XING interface in the Blog2Social network settings. To do this, go to the Blog2Social Networks section and connect your XING accounts with the new XING.", "blog2social");
                                                ?>  <a href="<?= B2S_Tools::getSupportLink('network_tos_blog_032019'); ?>" target="_blank"><?= __('Learn more', 'blog2social') ?></a>
                                            <?php
                                            } else {
                                                _e("Please note: Google will shut down Google+ for all private accounts (profiles, pages, groups) on 2nd April 2019. You can find further information and the next steps, including how to download your photos and other content here:", "blog2social");
                                                ?>  <a href="https://support.google.com/plus/answer/9195133" target="_blank">https://support.google.com/plus/answer/9195133</a> 
                                            <?php } ?>
                                        </div>
                                        <?php } ?>

                                        <ul class="list-group b2s-post-item-details-release-area-details-ul" data-network-auth-id="<?= $item->getNetworkAuthId(); ?>">
                                            <li class="list-group-item">
                                                <div class="form-group b2s-post-item-details-release-area-details-row" data-network-count="1"  data-network-auth-id="<?= $item->getNetworkAuthId(); ?>">
                                                        <?php if ((int) $item->getRelayPrimaryPostId() == 0) { ?>
                                                        <div class="clearfix"></div>
                                                        <label class="col-xs-3 del-padding-left b2s-post-item-details-release-area-label-date" data-network-auth-id="<?= $item->getNetworkAuthId(); ?>" data-network-count="1"><?= __('Date', 'blog2social'); ?></label>
                                                        <label class="col-xs-3 del-padding-left b2s-post-item-details-release-area-label-time" data-network-auth-id="<?= $item->getNetworkAuthId(); ?>" data-network-count="1"><?= __('Time', 'blog2social'); ?></label>
                                                        <div class="clearfix"></div>
                                                        <div class="col-xs-3 del-padding-left b2s-post-item-details-release-area-label-date" data-network-auth-id="<?= $item->getNetworkAuthId(); ?>" data-network-count="1"><input type="text" placeholder="<?= __('Date', 'blog2social'); ?>" name="b2s[<?= $item->getNetworkAuthId(); ?>][date][0]" data-network-id="<?= $item->getNetworkId(); ?>" data-network-type="<?= $item->getNetworkType(); ?>" data-network-count="0" data-network-auth-id="<?= $item->getNetworkAuthId(); ?>"  class="b2s-post-item-details-release-input-date form-control" value="<?= (substr(B2S_LANGUAGE, 0, 2) == 'de') ? date('d.m.Y', $item->getSchedDate()) : date('Y-m-d', $item->getSchedDate()); ?>" style="min-width: 93px;"></div>
                                                        <div class="col-xs-3 del-padding-left b2s-post-item-details-release-area-label-time" data-network-auth-id="<?= $item->getNetworkAuthId(); ?>" data-network-count="1"><input type="text" placeholder="<?= __('Time', 'blog2social'); ?>" name="b2s[<?= $item->getNetworkAuthId(); ?>][time][0]" data-network-id="<?= $item->getNetworkId(); ?>" data-network-type="<?= $item->getNetworkType(); ?>" data-network-count="0"  data-network-auth-id="<?= $item->getNetworkAuthId(); ?>"  class="b2s-post-item-details-release-input-time form-control" value="<?= date('H:i', $item->getSchedDate()); ?>"></div>
                                                        <div class="col-xs-5 del-padding-left b2s-post-item-details-release-area-label-day" data-network-auth-id="<?= $item->getNetworkAuthId(); ?>" data-network-count="1">
                                                            <?php
                                                            //is relay post ?
                                                        } else {
                                                            ?>
                                                            <div class="clearfix"></div>
                                                            <label class="col-xs-3 del-padding-left b2s-post-item-details-relay-area-label-delay" data-network-auth-id="<?= $item->getNetworkAuthId(); ?>" data-network-count="1"><?= __('Delay', 'blog2social'); ?></label>
                                                            <div class="clearfix"></div>
                                                            <div class="col-xs-3 del-padding-left b2s-post-item-details-relay-area-div-delay" data-network-auth-id="<?= $item->getNetworkAuthId(); ?>" data-network-count="1">
                                                                <select name="b2s[<?= $item->getNetworkAuthId(); ?>][post_relay_delay][0]" class="form-control b2s-select b2s-post-item-details-relay-input-delay" data-network-count="0"  data-network-auth-id="<?= $item->getNetworkAuthId(); ?>">
                                                                    <option value="15" <?= (($item->getRelayDelayMin() == 15) ? 'selected' : ''); ?> >15 <?= __('min', 'blog2social') ?></option>
                                                                    <option value="30" <?= (($item->getRelayDelayMin() == 30) ? 'selected' : ''); ?>>30 <?= __('min', 'blog2social') ?></option>
                                                                    <option value="45" <?= (($item->getRelayDelayMin() == 45) ? 'selected' : ''); ?>>45 <?= __('min', 'blog2social') ?></option>
                                                                    <option value="60" <?= (($item->getRelayDelayMin() == 60) ? 'selected' : ''); ?>>60 <?= __('min', 'blog2social') ?></option>
                                                                </select>
                                                            </div>
                                                            <div class="col-xs-9">
                                                                <strong><?= __('The orginal tweet is scheduled on:', 'blog2social') ?> <?= B2S_Util::getCustomDateFormat($item->getRelayPrimarySchedDate(), substr(B2S_LANGUAGE, 0, 2)) ?></strong>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="clearfix"></div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                    <?php if (!$lock_user_id || $lock_user_id == get_current_user_id()) { ?>
                                    <div class="col-xs-12" style="margin-top: 20px;">
                                        <div class="pull-left" style="line-height: 33px">
                                            <span class="b2s-edit-post-delete btn btn-danger" data-post-for-relay="<?= $item->getPostForRelay(); ?>" data-post-for-approve="<?= $item->getPostForApprove(); ?>"  data-post-id="<?= $item->getPostId(); ?>" data-b2s-id="<?= $item->getB2SId(); ?>">
                                                <span class="glyphicon glyphicon glyphicon-trash "></span> <?= esc_attr(__("Delete", "blog2social")); ?>
                                            </span>
                                        </div>
                                        <div class="pull-right">
                                            <input class="btn btn-success pull-right b2s-edit-post-save-this" type="submit" value="<?= esc_attr(__('Change details', 'blog2social')); ?>" data-post-id="<?= $item->getPostId(); ?>" data-b2s-id="<?= $item->getB2SId(); ?>">
                                        </div>
                                    </div>
                                <?php } ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
