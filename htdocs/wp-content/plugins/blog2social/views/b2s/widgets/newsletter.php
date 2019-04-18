<?php
$updateMail = get_option('B2S_UPDATE_MAIL_' . B2S_PLUGIN_BLOG_USER_ID);
?>
<h5 class="b2s-dashboard-h5"><?php _e('Get news and updates for promoting your blog on social media', 'blog2social') ?></h5>
<?php if ($updateMail == false || empty($updateMail)) { ?>
    <div class="form-inline" id="b2snewsletter">
        <div class="form-group">
            <input id="b2s-mail-update-input" class="form-control" name="b2sMailUpdate" value="<?php echo $wpUserData->user_email; ?>" placeholder="E-Mail" type="text">
            <input type="hidden" id="user_lang" value="<?php echo substr(B2S_LANGUAGE, 0, 2) ?>">
            <a class="btn btn-success b2s-mail-btn" href="#"><?php _e('Get updates', 'blog2social') ?></a>
        </div>
        <div class="b2s-info-sm hidden-xs"><?php _e('We hate spam, too. We will never sell your email address to any other company or for any other purpose.', 'blog2social') ?></div>
    </div>
<?php } else { ?>
    <p>
        <?php _e('You have already subscribed to the newsletter. Awesome!', 'blog2social') ?>
    </p>
<?php
}
