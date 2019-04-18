<?php

class B2S_Settings_Item {

    private $userSchedTimeData = array();
    private $networkData = array();
    private $settings = array();
    private $networkAuthData = array();
    private $networkAutoPostData;
    private $networkAuthCount = false;
    private $lang;
    private $allowPage;
    private $options;
    private $generalOptions;
    private $allowGroup;
    private $timeInfo;
    private $postTypesData;
    private $authUrl;

    public function __construct() {
        $this->getSettings();
        $this->options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
        $this->generalOptions = new B2S_Options(0, 'B2S_PLUGIN_GENERAL_OPTIONS');
        $this->lang = substr(B2S_LANGUAGE, 0, 2);
        $this->allowPage = unserialize(B2S_PLUGIN_NETWORK_ALLOW_PAGE);
        $this->allowGroup = unserialize(B2S_PLUGIN_NETWORK_ALLOW_GROUP);
        $this->timeInfo = unserialize(B2S_PLUGIN_SCHED_DEFAULT_TIMES_INFO);
        $this->postTypesData = get_post_types(array('public' => true));
        $this->authUrl = B2S_PLUGIN_API_ENDPOINT_AUTH_SHORTENER . '?b2s_token=' . B2S_PLUGIN_TOKEN . '&sprache=' . substr(B2S_LANGUAGE, 0, 2);
    }

    private function getSettings() {
        $result = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, array('action' => 'getSettings', 'portal_view_mode' => true, 'portal_auth_count' => true, 'token' => B2S_PLUGIN_TOKEN, 'version' => B2S_PLUGIN_VERSION)));
        if (is_object($result) && isset($result->result) && (int) $result->result == 1 && isset($result->portale) && is_array($result->portale)) {
            $this->networkData = $result->portale;
            if (isset($result->settings) && is_object($result->settings)) {
                $this->settings = $result->settings;
            }
            $this->networkAuthCount = isset($result->portal_auth_count) ? $result->portal_auth_count : false;
            $this->networkAuthData = isset($result->portal_auth) ? $result->portal_auth : array();
            $this->networkAutoPostData = isset($result->portal_auto_post) ? $result->portal_auto_post : array();
        }
    }

    public function getGeneralSettingsHtml() {

        $isCheckedAllowShortcode = (get_option('B2S_PLUGIN_USER_ALLOW_SHORTCODE_' . B2S_PLUGIN_BLOG_USER_ID) !== false) ? 1 : 0;

        $optionUserTimeZone = $this->options->_getOption('user_time_zone');
        $optionUserHashTag = $this->options->_getOption('user_allow_hashtag');
        $contentTwitter = $this->options->_getOption('content_network_twitter');
        $legacyMode = $this->generalOptions->_getOption('legacy_mode');
        $isCheckedLegacyMode = ($legacyMode !== false && $legacyMode == 1) ? 1 : 0;  //default not active , 1=active 0=not active
        $userTimeZone = ($optionUserTimeZone !== false) ? $optionUserTimeZone : get_option('timezone_string');
        $userTimeZoneOffset = (empty($userTimeZone)) ? get_option('gmt_offset') : B2S_Util::getOffsetToUtcByTimeZone($userTimeZone);
        $userInfo = get_user_meta(B2S_PLUGIN_BLOG_USER_ID);
        $isChecked = (isset($this->settings->short_url) && (int) $this->settings->short_url == 0) ? 1 : 0;
        $isCheckedAllowHashTag = ($optionUserHashTag === false || $optionUserHashTag == 1) ? 1 : 0;  //default allow , 1=include 0=not include
        $isShortenerBitlyActive = (isset($this->settings->shortener_bitly_display_name) && !empty($this->settings->shortener_bitly_display_name)) ? true : false;
        $isChecked = ($isShortenerBitlyActive) ? 0 : 1;

        $content = '';
        $content .='<h4>' . __('Account', 'blog2social') . '</h4>';
        $content .='<div class="form-inline">';
        $content .='<div class="col-xs-12 del-padding-left">';
        $content .='<label class="b2s-user-time-zone-label" for="b2s-user-time-zone">' . __('Personal Time Zone', 'blog2social') . '</label>';
        $content .=' <select id="b2s-user-time-zone" class="form-control b2s-select" name="b2s-user-time-zone">';
        $content .= B2S_Util::createTimezoneList($userTimeZone);
        $content .= '</select>';
        $content .= ' <a href="#" data-toggle="modal" data-target="#b2sInfoTimeZoneModal" class="b2s-info-btn hidden-xs">' . __('Info', 'Blog2Social') . '</a>';
        $content .='</div>';
        $content .='<br><div class="b2s-settings-time-zone-info">' . __('Timezone for Scheduling', 'blog2social') . ' (' . __('User', 'blog2social') . ': ' . (isset($userInfo['nickname'][0]) ? $userInfo['nickname'][0] : '-') . ') <code id="b2s-user-time">' . B2S_Util::getLocalDate($userTimeZoneOffset, substr(B2S_LANGUAGE, 0, 2)) . '</code></span></div>';
        $content .='</div>';
        $content .='<div class="clearfix"></div>';
        $content .='<br>';
        $content .='<hr>';
        $content .='<h4>' . __('Content', 'blog2social') . '</h4>';
        $content .='<strong>' . __('Url Shortener', 'blog2social') . '</strong> <a href="#" data-toggle="modal" data-target="#b2sInfoUrlShortenerModal" class="b2s-info-btn del-padding-left">' . __('Info', 'Blog2Social') . '</a><br>';
        $content .='<input type="checkbox" value="' . $isChecked . '" id="b2s-user-network-settings-short-url" ' . (($isChecked == 0) ? 'checked="checked"' : '') . ' /><label for="b2s-user-network-settings-short-url"> <img class="b2s-shortener-image" alt="Bitly" src="' . plugins_url('/assets/images/settings/bitly.png', B2S_PLUGIN_FILE) . '"> ' . __('Bitly', 'blog2social') . '</label>';
        $content .=' <span class="b2s-user-network-shortener-account-area" data-provider-id="0">';
        $content .='<input type="hidden" id="b2s-user-network-shortener-state" data-provider-id="0" value="' . (($isShortenerBitlyActive) ? 1 : 0) . '"/>';
        $content .='<span class="b2s-user-network-shortener-connect" data-provider-id="0" style="display:' . ((!$isShortenerBitlyActive) ? 'inline-block' : 'none') . ';" ><a href="#" class="b2s-shortener-account-connect-btn" data-provider-id="0" onclick="wopShortener(\'' . $this->authUrl . '&provider_id=0\', \'Blog2Social Network\'); return false;">' . __('connect', 'blog2social') . '</a> </span>';
        $content .='<span class="b2s-user-network-shortener-account-detail" data-provider-id="0" style="display:' . (($isShortenerBitlyActive) ? 'inline-block' : 'none') . ';">(' . __('Account', 'blog2social') . ': <span id="b2s-shortener-account-display-name" data-provider-id="0">' . (($isShortenerBitlyActive) ? $this->settings->shortener_bitly_display_name : '') . '</span> <a href="#" class="b2s-shortener-account-change-btn" data-provider-id="0" onclick="wopShortener(\'' . $this->authUrl . '&provider_id=0\', \'Blog2Social Network\'); return false;">' . __('change', 'blog2social') . '</a> | <a href="#" class="b2s-shortener-account-delete-btn" data-provider-id="0">' . __('delete', 'blog2social') . '</a>)</span>';
        $content .='</span>';
        $content .= '<br>';
        $content .= '<input type="checkbox" value="' . $isCheckedAllowShortcode . '" id="b2s-user-network-settings-allow-shortcode" ' . (($isCheckedAllowShortcode == 1) ? 'checked="checked"' : '') . ' /><label for="b2s-user-network-settings-allow-shortcode"> ' . __('allow shortcodes in my post', 'blog2social') . ' <a href="#" data-toggle="modal" data-target="#b2sInfoAllowShortcodeModal" class="b2s-info-btn del-padding-left">' . __('Info', 'Blog2Social') . '</a></label>';
        $content .= '<br>';
        $content .= '<input type="checkbox" value="' . (($isCheckedAllowHashTag == 1) ? 0 : 1) . '" id="b2s-user-network-settings-allow-hashtag" ' . (($isCheckedAllowHashTag == 1) ? 'checked="checked"' : '') . ' /><label for="b2s-user-network-settings-allow-hashtag"> ' . __('include Wordpress tags as hashtags in my post', 'blog2social') . ' <a href="#" data-toggle="modal" data-target="#b2sInfoAllowHashTagModal" class="b2s-info-btn del-padding-left">' . __('Info', 'Blog2Social') . '</a></label>';
        $content .= '<br>';
        $content .= '<br>';
        $content .='<p>' . __('Define Twitter post content', 'blog2social');
        if (B2S_PLUGIN_USER_VERSION < 1) {
            $content .=' <span class="label label-success label-sm"><a href="#" class="btn-label-premium" data-toggle="modal" data-target="#b2sPreFeatureModal">' . __("PREMIUM", "blog2social") . '</a></span>';
        }
        $content .='</p>';
        $content .='<select id="b2s-user-network-twitter-content">';
        $content .='<option ' . (($contentTwitter === false || $contentTwitter == 0 || B2S_PLUGIN_USER_VERSION < 1) ? 'selected' : '') . ' value="0">' . __('title', 'blog2social') . '' . (($isCheckedAllowHashTag == 1) ? ' & ' . __('hashtags', 'blog2social') : '') . '</option>';
        $content .='<option ' . (($contentTwitter !== false && $contentTwitter == 1 && B2S_PLUGIN_USER_VERSION >= 1) ? 'selected' : '') . ' value="1">' . __('title & content', 'blog2social') . '' . (($isCheckedAllowHashTag == 1) ? ' & ' . __('hashtags', 'blog2social') : '') . ' ' . ((B2S_PLUGIN_USER_VERSION < 1) ? __('(PREMIUM)', 'blog2social') : '') . '</option>';
        $content .='<option ' . (($contentTwitter !== false && $contentTwitter == 2 && B2S_PLUGIN_USER_VERSION >= 1) ? 'selected' : '') . ' value="2">' . __('only content', 'blog2social') . '' . (($isCheckedAllowHashTag == 1) ? ' & ' . __('hashtags', 'blog2social') : '') . ' ' . ((B2S_PLUGIN_USER_VERSION < 1) ? __('(PREMIUM)', 'blog2social') : '') . '</option>';
        $content .='</select> <a href="#" data-toggle="modal" data-target="#b2sInfoContentTwitterModal" class="b2s-info-btn hidden-xs">' . __('Info', 'blog2social') . '</a>';
        $content .='<div class="clearfix"></div>';
        $content .='<br>';
        $content .='<br>';
        $content .='<hr>';
        $content .='<h4>' . __('System', 'blog2social') . '</h4>';
        $content .='<strong>' . __('This is a global feature for your blog, which can only be edited by users with admin rights.', 'blog2social') . '</strong><br>';
        $content .= '<input type="checkbox" value="' . (($isCheckedLegacyMode == 1) ? 0 : 1) . '" id="b2s-general-settings-legacy-mode" ' . (($isCheckedLegacyMode == 1) ? 'checked="checked"' : '') . ' /><label for="b2s-general-settings-legacy-mode"> ' . __('activate Legacy mode', 'blog2social') . ' <a href="#" data-toggle="modal" data-target="#b2sInfoLegacyMode" class="b2s-info-btn del-padding-left">' . __('Info', 'Blog2Social') . '</a></label>';
        return $content;
    }

    public function getAutoPostingSettingsHtml() {

        $optionAutoPost = $this->options->_getOption('auto_post');
        $optionAutoPostImport = $this->options->_getOption('auto_post_import');

        $isPremium = (B2S_PLUGIN_USER_VERSION == 0) ? ' <span class="label label-success label-sm">' . __("PREMIUM", "blog2social") . '</span>' : '';
        $versionType = unserialize(B2S_PLUGIN_VERSION_TYPE);
        $limit = unserialize(B2S_PLUGIN_AUTO_POST_LIMIT);

        $content = '';
        $content .='<div class="panel panel-group b2s-auto-post-own-general-warning"><div class="panel-body"><span class="glyphicon glyphicon-exclamation-sign glyphicon-warning"></span> ' . __('Auto-posts for Facebook Profiles will be shown in the "Instant Sharing" tab on your "Posts & Sharing" navigation bar and can be shared on your Facebook Profile by clicking on the "Share" button next to your auto-post.', 'blog2social') . '</div>';
        $content .='</div>';
        $content .='<h4>' . __('Auto-post your own created posts', 'blog2social') . ' ' . $isPremium . ' <a href="#" data-toggle="modal" data-target="#b2sInfoAutoShareModal" class="b2s-info-btn del-padding-left">' . __('Info', 'Blog2Social') . '</a>';
        $content .='<br><div class="b2s-text-sm">' . __('Define by default to automatically post your posts on social media:', 'blog2social') . '</div>';
        $content .='</h4>';
        $content .='<p class="b2s-bold">' . __('Select by default if the auto-poster is activated when you publish a new post or update a post.', 'blog2social') . '</p>';
        $content .='<br>';
        $content .= '<form id = "b2s-user-network-settings-auto-post-own" method = "post" ' . (!empty($isPremium) ? 'class="b2s-btn-disabled"' : '') . ' >';
        $content .='<div class="row">';
        $content .='<div class="col-xs-12 col-md-2">';
        $content .='<label class="b2s-auto-post-publish-label">' . __('new posts', 'blog2social') . '</label>';
        $content .='<br><small><button class="btn btn-link btn-xs hidden-xs b2s-post-type-select-btn" data-post-type="publish" data-select-toogle-state="0" data-select-toogle-name="' . __('Unselect all', 'blog2social') . '">' . __('Select all', 'blog2social') . '</button></small>';
        $content .='</div>';
        $content .='<div class="col-xs-12 col-md-6">';
        $content .= $this->getPostTypesHtml($optionAutoPost);
        $content .='</div>';
        $content .='</div>';
        $content .='<br>';
        $content .='<div class="row">';
        $content .='<div class="col-md-12"><div class="panel panel-group b2s-auto-post-own-update-warning" style="display: none;"><div class="panel-body"><span class="glyphicon glyphicon-exclamation-sign glyphicon-warning"></span> ' . __('By enabling this feature your previously published social media posts will be sent again to your selected social media channels as soon as the post is updated.', 'blog2social') . '</div></div></div>';
        $content .='<div class"clearfix"></div>';
        $content .='<div class="col-xs-12 col-md-2">';
        $content .='<label class="b2s-auto-post-update-label">' . __('updating existing posts', 'blog2social') . '</label>';
        $content .='<br><small><button class="btn btn-link btn-xs hidden-xs b2s-post-type-select-btn" data-post-type="update" data-select-toogle-state="0" data-select-toogle-name="' . __('Unselect all', 'blog2social') . '">' . __('Select all', 'blog2social') . '</button></small>';
        $content .='</div>';
        $content .='<div class="col-xs-12 col-md-6">';
        $content .= $this->getPostTypesHtml($optionAutoPost, 'update');
        $content .='</div>';
        $content .='</div>';
        if (B2S_PLUGIN_USER_VERSION > 0) {
            $content .= '<button class="pull-right btn btn-primary btn-sm" type="submit">';
        } else {
            $content .= '<button class="pull-right btn btn-primary btn-sm b2s-btn-disabled b2s-save-settings-pro-info" data-toggle = "modal" data-target = "#b2sInfoAutoShareModal">';
        }
        $content .= __('Save', 'blog2social') . '</button>';
        $content .= '<input type="hidden" name="action" value="b2s_user_network_settings">';
        $content .= '<input type="hidden" name="type" value="auto_post">';
        $content .='</form>';

        $content .='<div class="clearfix"></div>';
        $content .='<br>';
        $content .='<hr>';
        $content .='<h4>' . __('Auto-post your imported posts to Twitter & Facebook', 'blog2social') . ' ' . $isPremium . ' <a href="#" data-toggle="modal" data-target="#b2sInfoAutoShareModal" class="b2s-info-btn del-padding-left">' . __('Info', 'Blog2Social') . '</a>';
        $content .='<br><div class="b2s-text-sm">' . __('Define by default to automatically share your imported posts to social media:', 'blog2social') . '</div>';
        $content .='</h4>';

        $content .='<p>' . __('Your current licence:', 'blog2social') . '<span class="b2s-key-name"> ' . $versionType[B2S_PLUGIN_USER_VERSION] . '</span>';
        if (B2S_PLUGIN_USER_VERSION == 0) {
            $content .='<br>' . __('Immediate Cross-Posting across all networks: Share an unlimited number of posts', 'blog2social') . '<br>';
            $content .=__('Scheduled Auto-Posting', 'blog2social') . ': <a class="b2s-info-btn" href="' . B2S_Tools::getSupportLink('affiliate') . '" target="_blank">' . __('Upgrade', 'blog2social') . '</a>';
        } else {
            $content .='(' . __('share up to', 'blog2social') . ' ' . $limit[B2S_PLUGIN_USER_VERSION] . ' ' . __('posts per day', 'blog2social') . ') ';
            $content .='<a class="b2s-info-btn" href="' . B2S_Tools::getSupportLink('affiliate') . '" target="_blank">' . __('Upgrade', 'blog2social') . '</a>';
        }
        $content .='</p>';

        $content .='<br>';
        $content .='<p class="b2s-bold">' . __('Select by default to automatically share your imported posts', 'blog2social') . '</p>';
        $content .= '<form id="b2s-user-network-settings-auto-post-imported-own" method = "post" ' . (!empty($isPremium) ? 'class="b2s-btn-disabled"' : '') . ' >';
        $content .='<input data-size="mini" data-toggle="toggle" data-width="90" data-height="22" data-onstyle="primary" data-on="ON" data-off="OFF" ' . ((isset($optionAutoPostImport['active']) && (int) $optionAutoPostImport['active'] == 1) ? 'checked' : '') . '  name="b2s-import-auto-post" value="1" type="checkbox">';
        $content .='<br><br>';
        $content .='<p class="b2s-bold">' . __('Select to auto-post to your standard networks:', 'blog2social') . '</p>';
        $content .='<div class="b2s-network-tos-auto-post-import-warning"><div class="alert alert-danger">' . __('In accordance with the new Twitter TOS, one Twitter account can be selected as your primary Twitter account for auto-posting.', 'blog2social') . ' <a href="' . B2S_Tools::getSupportLink('network_tos_faq_032018') . '" target="_blank">' . __('More information', 'blog2social') . '</a></div></div>';
        $content .= $this->getNetworkAutoPostData($optionAutoPostImport);
        $content .='<p class="b2s-bold">' . __('Select to auto-post immediately after publishing or with a delay', 'blog2social') . '</p>';
        $content .='<input id="b2s-import-auto-post-time-now" name="b2s-import-auto-post-time-state" ' . (((isset($optionAutoPostImport['ship_state']) && (int) $optionAutoPostImport['ship_state'] == 0) || !isset($optionAutoPostImport['ship_state'])) ? 'checked' : '') . ' value="0" type="radio"><label for="b2s-import-auto-post-time-now">' . __('immediately', 'blog2social') . '</label><br>';
        $content .='<input id="b2s-import-auto-post-time-delay" name="b2s-import-auto-post-time-state" value="1" ' . ((isset($optionAutoPostImport['ship_state']) && (int) $optionAutoPostImport['ship_state'] == 1) ? 'checked' : '') . ' type="radio"><label for="b2s-import-auto-post-time-delay">' . __('publish with a delay of', 'blog2social');
        $content .=' <input type="number" maxlength="2" max="10" min="1" class="b2s-input-text-size-45" name="b2s-import-auto-post-time-data" value="' . (isset($optionAutoPostImport['ship_delay_time']) ? $optionAutoPostImport['ship_delay_time'] : 1) . '" placeholder="1" > (1-10) ' . __('minutes', 'blog2social') . '</label>';

        $content .='<br>';
        $content .= $this->getChosenPostTypesData($optionAutoPostImport);
        if (B2S_PLUGIN_USER_VERSION > 0) {
            $content .= '<button class="pull-right btn btn-primary btn-sm" id="b2s-auto-post-import-settings-btn" type="submit">';
        } else {
            $content .= '<button class="pull-right btn btn-primary btn-sm b2s-btn-disabled b2s-save-settings-pro-info" data-toggle = "modal" data-target = "#b2sInfoAutoShareModal">';
        }
        $content .= __('Save', 'blog2social') . '</button>';
        $content .= '<input type="hidden" name="action" value="b2s_user_network_settings">';
        $content .= '<input type="hidden" name="type" value="auto_post_imported">';

        $content .='</form>';

        return $content;
    }

    private function getChosenPostTypesData($data = array()) {

        $html = '';
        if (is_array($this->postTypesData) && !empty($this->postTypesData)) {
            $html .='<br>';
            $html .='<p><b><input value="1"  ' . ((isset($data['post_filter']) && (int) $data['post_filter'] == 1) ? 'checked' : '') . ' name="b2s-import-auto-post-filter" type="checkbox"> ' . __('Filter Posts (Only posts that meet the following criteria will be autoposted)', 'blog2social') . '</b></p>';
            $html .='<p>' . __('Post Types', 'blog2social');
            $html .=' <input id="b2s-import-auto-post-type-state-include" name="b2s-import-auto-post-type-state" value="0" ' . (((isset($data['post_type_state']) && (int) $data['post_type_state'] == 0) || !isset($data['post_type_state'])) ? 'checked' : '') . ' type="radio"><label class="padding-bottom-3" for="b2s-import-auto-post-type-state-include">' . __('Include (Post only...)', 'blog2social') . '</label> ';
            $html .='<input id="b2s-import-auto-post-type-state-exclude" name="b2s-import-auto-post-type-state" value="1" ' . ((isset($data['post_type_state']) && (int) $data['post_type_state'] == 1) ? 'checked' : '') . ' type="radio"><label class="padding-bottom-3" for="b2s-import-auto-post-type-state-exclude">' . __('Exclude (Do no post ...)', 'blog2social') . '</label>';
            $html .='</p>';
            $html .='<select name="b2s-import-auto-post-type-data[]" data-placeholder="Select Post Types" class="b2s-import-auto-post-type" multiple>';

            $selected = (is_array($data['post_type']) && isset($data['post_type'])) ? $data['post_type'] : array();

            foreach ($this->postTypesData as $k => $v) {
                if ($v != 'attachment' && $v != 'nav_menu_item' && $v != 'revision') {
                    $selItem = (in_array($v, $selected)) ? 'selected' : '';
                    $html .= '<option ' . $selItem . ' value="' . $v . '">' . $v . '</option>';
                }
            }

            $html .='</select>';
        }
        return $html;
    }

    private function getNetworkAutoPostData($data = array()) {
        $html = '';
        if (!empty($this->networkAutoPostData)) {
            $selected = (is_array($data['network_auth_id']) && isset($data['network_auth_id'])) ? $data['network_auth_id'] : array();
            $networkName = unserialize(B2S_PLUGIN_NETWORK);
            $html .= '<ul class="list-group b2s-network-details-container-list">';
            foreach ($this->networkAutoPostData as $k => $v) {
                $maxNetworkAccount = ($this->networkAuthCount !== false && is_array($this->networkAuthCount)) ? ((isset($this->networkAuthCount[$v])) ? $this->networkAuthCount[$v] : $this->networkAuthCount[0]) : false;
                $html .='<li class="list-group-item">';
                $html .='<div class="media">';
                $html .='<img class="pull-left hidden-xs b2s-img-network" alt="' . $networkName[$v] . '" src="' . plugins_url('/assets/images/portale/' . $v . '_flat.png', B2S_PLUGIN_FILE) . '">';
                $html .='<div class="media-body network">';
                $html .='<h4>' . ucfirst($networkName[$v]);
                if ($maxNetworkAccount !== false) {
                    $html .=' <span class="b2s-network-auth-count">(' . __("Connections", "blog2social") . ' <span class="b2s-network-auth-count-current" data-network-count-trigger="true" data-network-id="' . $v . '"></span>/' . $maxNetworkAccount . ')</span>';
                }
                $html .=' <a href="admin.php?page=blog2social-network" class="b2s-info-btn">' . __('add/change connection', 'blog2social') . '</a>';
                $html .='</h4>';
                $html .= '<ul class="b2s-network-item-auth-list" data-network-id="' . $v . '" data-network-count="true" >';
                if (!empty($this->networkAuthData)) {
                    foreach ($this->networkAuthData as $i => $t) {
                        if ($v == $t->networkId) {
                            $html .= '<li class="b2s-network-item-auth-list-li" data-network-auth-id="' . $t->networkAuthId . '"  data-network-id="' . $t->networkId . '" data-network-type="0">';
                            $networkType = ((int) $t->networkType == 0 ) ? __('Profile', 'blog2social') : __('Page', 'blog2social');
                            if ($t->notAllow !== false) {
                                $html .='<span class="glyphicon glyphicon-remove-circle glyphicon-danger"></span> <span class="not-allow">' . $networkType . ': ' . stripslashes($t->networkUserName) . '</span> ';
                            } else {
                                $selItem = (in_array($t->networkAuthId, $selected)) ? 'checked' : '';
                                $html .= '<input id="b2s-import-auto-post-network-auth-id-' . $t->networkAuthId . '" class="b2s-network-tos-check" data-network-id="' . $t->networkId . '" ' . $selItem . ' value="' . $t->networkAuthId . '" name="b2s-import-auto-post-network-auth-id[]" type="checkbox"> <label for="b2s-import-auto-post-network-auth-id-' . $t->networkAuthId . '">' . $networkType . ': ' . stripslashes($t->networkUserName) . '</label>';
                            }
                            $html .= '</li>';
                        }
                    }
                }

                $html .= '</ul>';
                $html .='</div>';
                $html .='</div>';
                $html .='</li>';
            }

            $html .= '</ul>';
        }


        return $html;
    }

    public function getSocialMetaDataHtml() {

        $og = $this->generalOptions->_getOption('og_active');
        $card = $this->generalOptions->_getOption('card_active');
        //$user_meta_author_data = $this->options->_getOption('meta_author_data');
        $og_isChecked = ($og !== false && $og == 1) ? 0 : 1;
        $card_isChecked = ($card !== false && $card == 1) ? 0 : 1;
        $selectCardType = $this->generalOptions->_getOption('card_default_type');

        $content = '<div class="col-md-12">';
        if (B2S_PLUGIN_ADMIN) {
            $content .= '<a href="#" class="pull-right btn btn-primary btn-xs b2sClearSocialMetaTags">' . __('Reset all page and post meta data', 'blog2social') . '</a>';
        }
        $content .='<strong>' . __('This is a global feature for your blog, which can only be edited by users with admin rights.', 'blog2social') . '</strong>';
        $content .= '<br>';
        $content .='<div class="' . ( (B2S_PLUGIN_ADMIN) ? "" : "b2s-disabled-div") . '">';
        $content .='<h4>' . __('Meta Tags Settings for Posts and Pages', 'blog2social') . '</h4>';
        $content .= '<input type="checkbox" value="' . $og_isChecked . '" name="b2s_og_active" ' . ((B2S_PLUGIN_ADMIN) ? "" : "onclick=\"return false\"") . '  id="b2s_og_active" ' . (($og_isChecked == 0) ? 'checked="checked"' : '') . ' /><label for="b2s_og_active"> ' . __('Add Open Graph meta tags to your shared posts or pages, required by Facebook and other social networks to display your post or page image, title and description correctly.', 'blog2social', 'blog2social') . ' <a href="#" class="b2s-load-info-meta-tag-modal b2s-info-btn del-padding-left" data-meta-type="og" data-meta-origin="settings">' . __('Info', 'Blog2Social') . '</a></label>';
        $content .='<br>';
        $content .= '<input type="checkbox" value="' . $card_isChecked . '" name="b2s_card_active" ' . ((B2S_PLUGIN_ADMIN) ? "" : "onclick=\"return false\"") . ' id="b2s_card_active" ' . (($card_isChecked == 0) ? 'checked="checked"' : '') . ' /><label for="b2s_card_active"> ' . __('Add Twitter Card meta tags to your shared posts or pages, required by Twitter to display your post or page image, title and description correctly.', 'blog2social', 'blog2social') . ' <a href="#" class="b2s-load-info-meta-tag-modal b2s-info-btn del-padding-left" data-meta-type="card" data-meta-origin="settings">' . __('Info', 'Blog2Social') . '</a></label>';
        $content .='</div>';
        $content .='<br><br><hr>';

        /* $content .='<h4>' . __('Authorship Settings', 'blog2social');
          if (B2S_PLUGIN_USER_VERSION < 1) {
          $content .=' <span class="label label-success label-sm"><a href="#" class="btn-label-premium" data-toggle="modal" data-target="#b2sPreFeatureModal">' . __("PREMIUM", "blog2social") . '</a></span>';
          }
          $content .='</h4>';
          $content .='<div class="' . ( (B2S_PLUGIN_USER_VERSION >= 1) ? "" : "b2s-disabled-div") . '">';
          $content .='<p>' . __('Add authorship tags to your articles. When somesone shares your links on Facebook or Twitter, you will be automatically linked as the author.', 'blog2social') . '</p>';
          $content .='<div class="col-md-8">';
          $content .='<div class="form-group"><label for="b2s_og_article_author"><img alt="" class="b2s-post-item-network-image" src="' . plugins_url('/assets/images/portale/1_flat.png', B2S_PLUGIN_FILE) . '"> <strong>' . __("Facebook author link", "blog2social") . ':</strong></label><input type="text" placeholder="' . __("Enter your Facebook link profile here (e.g. https://www.facebook.com/Blog2Social/)", "blog2social") . '" ' . ((B2S_PLUGIN_USER_VERSION >= 1) ? "" : "readonly") . ' value="' . (($user_meta_author_data !== false && isset($user_meta_author_data['og_article_author'])) ? $user_meta_author_data['og_article_author'] : "") . '" name="b2s_og_article_author" class="form-control" id="b2s_og_article_author"></div>';
          $content .='<div class="form-group"><label for="b2s_card_twitter_creator"><img alt="" class="b2s-post-item-network-image" src="' . plugins_url('/assets/images/portale/2_flat.png', B2S_PLUGIN_FILE) . '"> <strong>' . __("Twitter Username", "blog2social") . ':</strong></label><input type="text" placeholder="' . __("Enter your Twitter Username here (e.g. @blog2social)", "blog2social") . '" ' . ((B2S_PLUGIN_USER_VERSION >= 1) ? "" : "readonly") . ' value="' . (($user_meta_author_data !== false && isset($user_meta_author_data['card_twitter_creator'])) ? $user_meta_author_data['card_twitter_creator'] : "") . '" name="b2s_card_twitter_creator" class="form-control" id="b2s_card_twitter_creator"></div>';
          $content .='</div>';
          $content .='</div>';
          $content .='<div class="clearfix"></div>';
          $content .='<hr>'; */

        $content .='<strong>' . __('This is a global feature for your blog, which can only be edited by users with admin rights.', 'blog2social') . '</strong>';
        $content .='<div class="' . ( (B2S_PLUGIN_ADMIN) ? "" : "b2s-disabled-div") . '">';
        $content .='<h4>' . __('Frontpage Settings', 'blog2social') . '</h4>';
        $content .='<div><img alt="" class="b2s-post-item-network-image" src="' . plugins_url('/assets/images/portale/1_flat.png', B2S_PLUGIN_FILE) . '"> <b>Facebook</b></div>';
        $content .= '<p>' . __('Add the default Open Graph parameters for title, description and image you want Facebook to display, if you share the frontpage of your blog as link post (http://www.yourblog.com)', 'blog2social') . '</p>';
        $content .='<br>';
        $content .='<div class="col-md-8">';
        $content .='<div class="form-group"><label for="b2s_og_default_title"><strong>' . __("Title", "blog2social") . ':</strong></label><input type="text" ' . ((B2S_PLUGIN_ADMIN) ? "" : "readonly") . ' value="' . ( ($this->generalOptions->_getOption('og_default_title') !== false) ? $this->generalOptions->_getOption('og_default_title') : get_bloginfo('name') ) . '" name="b2s_og_default_title" class="form-control" id="b2s_og_default_title"></div>';
        $content .='<div class="form-group"><label for="b2s_og_default_desc"><strong>' . __("Description", "blog2social") . ':</strong></label><input type="text" ' . ((B2S_PLUGIN_ADMIN) ? "" : "readonly") . ' value="' . ( ($this->generalOptions->_getOption('og_default_desc') !== false) ? $this->generalOptions->_getOption('og_default_desc') : get_bloginfo('description') ) . '" name="b2s_og_default_desc" class="form-control" id="b2s_og_default_desc"></div>';
        $content .='<div class="form-group"><label for="b2s_og_default_image"><strong>' . __("Image URL", "blog2social") . ':</strong></label>';
        if (B2S_PLUGIN_ADMIN) {
            $content .='<button class="btn btn-link btn-xs b2s-upload-image pull-right" data-id="b2s_og_default_image">' . __("Image upload / Media Gallery", "blog2social") . '</button>';
        }
        $content .='<input type="text" ' . ((B2S_PLUGIN_ADMIN) ? "" : "readonly") . ' value="' . (($this->generalOptions->_getOption('og_default_image') !== false) ? $this->generalOptions->_getOption('og_default_image') : '') . '" name="b2s_og_default_image" class="form-control" id="b2s_og_default_image">';
        $content .='<span>' . __('Please note: Facebook supports images with a minimum dimension of 200x200 pixels and an aspect ratio of 1:1.', 'blog2social') . '</span>';
        $content .='</div>';
        $content .='</div>';
        $content .='<div class="clearfix"></div>';
        $content .='<br>';
        $content .='<div><img alt="" class="b2s-post-item-network-image" src="' . plugins_url('/assets/images/portale/2_flat.png', B2S_PLUGIN_FILE) . '"> <b>Twitter</b></div>';
        $content .='<p>' . __('Add the default Twitter Card parameters for title, description and image you want Twitter to display, if you share the frontpage of your blog as link post (http://www.yourblog.com)', 'blog2social') . '</p>';
        $content .='<br>';
        $content .='<div class="col-md-8">';
        $content .='<div class="form-group"><label for="b2s_card_default_card_type"><strong>' . __("The default card type to use", "blog2social") . ':</strong></label>';
        $content .='<select class="form-control" name="b2s_card_default_type">';
        $content .='<option ' . (($selectCardType === false || $selectCardType == 0 || B2S_PLUGIN_USER_VERSION < 1) ? 'selected"' : '') . ' value="0">' . __('Summary', 'blog2social') . '</option>';
        $content .='<option ' . (($selectCardType !== false && $selectCardType == 1 && B2S_PLUGIN_USER_VERSION >= 1) ? 'selected' : '') . ' value="1">' . __('Summary with large image', 'blog2social') . ' ' . ((B2S_PLUGIN_USER_VERSION < 1) ? __('(PREMIUM)', 'blog2social') : '') . '</option>';
        $content .='</select></div>';
        $content .='<div class="form-group"><label for="b2s_card_default_title"><strong>' . __("Title", "blog2social") . ':</strong></label><input type="text" ' . ((B2S_PLUGIN_ADMIN) ? "" : "readonly") . ' value="' . ( ($this->generalOptions->_getOption('card_default_title') !== false) ? $this->generalOptions->_getOption('card_default_title') : get_bloginfo('name') ) . '" name="b2s_card_default_title" class="form-control" id="b2s_card_default_title"></div>';
        $content .='<div class="form-group"><label for="b2s_card_default_desc"><strong>' . __("Description", "blog2social") . ':</strong></label><input type="text" ' . ((B2S_PLUGIN_ADMIN) ? "" : "readonly") . ' value="' . ( ($this->generalOptions->_getOption('card_default_desc') !== false) ? $this->generalOptions->_getOption('card_default_desc') : get_bloginfo('description') ) . '" name="b2s_card_default_desc" class="form-control" id="b2s_card_default_desc"></div>';
        $content .='<div class="form-group"><label for="b2s_card_default_image"><strong>' . __("Image URL", "blog2social") . ':</strong></label> ';
        if (B2S_PLUGIN_ADMIN) {
            $content .='<button class="btn btn-link btn-xs pull-right b2s-upload-image" data-id="b2s_card_default_image">' . __("Image upload / Media Gallery", "blog2social") . '</button>';
        }
        $content .='<input type="text" ' . ((B2S_PLUGIN_ADMIN) ? "" : "readonly") . ' value="' . (($this->generalOptions->_getOption('card_default_image') !== false) ? $this->generalOptions->_getOption('card_default_image') : '') . '" name="b2s_card_default_image" class="form-control" id="b2s_card_default_image">';
        $content .='<span>' . __('Please note: Twitter supports images with a minimum dimension of 144x144 pixels and a maximum dimension of 4096x4096 pixels and less than 5 BM. The image will be cropped to a square. Twitter supports JPG, PNG, WEBP and GIF formats.', 'blog2social') . '</span>';
        $content .='</div>';
        $content .='</div>';
        $content .='</div>';
        $content .='</div>';

        return $content;
    }

    public function getNetworkSettingsHtml() {
        $optionPostFormat = $this->options->_getOption('post_format');
        $content = '';
        $networkName = unserialize(B2S_PLUGIN_NETWORK);

        if (B2S_PLUGIN_USER_VERSION < 2) {
            $content .='<div class="alert alert-default">';
            $content .= '<b>' . __('Did you know?', 'blog2social') . '</b><br>';
            $content .= __('With Premium Pro, you can change the custom post format photo post or link post for each individual social media post and channel (profile, page, group).', 'blog2social') . ' <a target="_blank" href="' . B2S_Tools::getSupportLink('affiliate') . '">' . __('Upgrade to Premium Pro now.', 'blog2social') . '</a>';
            $content .='<hr></div>';
        }

        foreach (array(1, 2, 10, 12) as $n => $networkId) { //FB,TW,GB,IN
            $type = ($networkId == 1 || $networkId == 10) ? array(0, 1, 2) : array(0);
            foreach ($type as $t => $typeId) { //Profile,Page,Group
                if (!isset($optionPostFormat[$networkId]['all'])) {
                    $optionPostFormat[$networkId]['all'] = 0;
                }

                $post_format_0 = ((isset($optionPostFormat[$networkId]) && is_array($optionPostFormat[$networkId]) && (((int) $optionPostFormat[$networkId]['all'] == 0) || (isset($optionPostFormat[$networkId][$typeId]) && (int) $optionPostFormat[$networkId][$typeId] == 0)) ) ? 'b2s-settings-checked' : (!isset($optionPostFormat[$networkId]) ? 'b2s-settings-checked' : '' )); //LinkPost
                $post_format_1 = empty($post_format_0) ? 'b2s-settings-checked' : ''; //PhotoPost
                $postFormatType = ($networkId == 12) ? 'image' : 'post';

                $content .='<div class="b2s-user-network-settings-post-format-area col-md-12" data-post-format-type="' . $postFormatType . '" data-network-type="' . $typeId . '"  data-network-id="' . $networkId . '" data-network-title="' . $networkName[$networkId] . '" style="display:none;" >';
                $content .='<div class="col-md-6 col-xs-12">';
                $content .= '<b>1) ' . (($networkId == 12) ? __('Image with frame', 'blog2social') : __('Link Post', 'blog2social') . ' <span class="glyphicon glyphicon-link b2s-color-green"></span>' ) . '</b><br><br>';
                $content .= '<label><input type="radio" name="b2s-user-network-settings-post-format-' . $networkId . '" class="b2s-user-network-settings-post-format ' . $post_format_0 . '" data-post-wp-type="" data-post-format-type="' . $postFormatType . '" data-network-type="' . $typeId . '" data-network-id="' . $networkId . '" data-post-format="0" value="0"/><img class="img-responsive b2s-display-inline" src="' . plugins_url('/assets/images/settings/b2s-post-format-' . $networkId . '-1-' . (($this->lang == 'de') ? $this->lang : 'en') . '.png', B2S_PLUGIN_FILE) . '">';
                $content .='</label>';
                $content .='<br><br>';
                if ($networkId == 12) {
                    $content .= __('Insert white frames to show the whole image in your timeline. All image information will be shown in your timeline.', 'blog2social');
                } else {
                    $content .= __('The link post format displays posts title, link address and the first one or two sentences of the post. The networks scan this information from your META or OpenGraph.  PLEASE NOTE: If you want your link posts to display the selected image from the Blog2Social preview editor, please make sure you have activated the Social Meta Tags for Facebook and Twitter in your Blog2Social settings. You find these settings in the tab "Social Meta Data". If you don\'t select a specific post image, some networks display the first image detected on your page. The image links to your blog post. PLEASE NOTE: For link posts on Google + , only images from the blog posts gallery can be selected and will be displayed on the network. ', 'blog2social');
                }
                $content .='</div>';
                $content .='<div class="col-md-6 col-xs-12">';
                $content .= '<b>2) ' . (($networkId == 12) ? __('Image cut out', 'blog2social') : __('Photo Post', 'blog2social') . ' <span class="glyphicon glyphicon-picture b2s-color-green"></span>' ) . '</b><br><br>';
                $content .= '<label><input type="radio" name="b2s-user-network-settings-post-format-' . $networkId . '" class="b2s-user-network-settings-post-format ' . $post_format_1 . '" data-post-wp-type="" data-post-format-type="' . $postFormatType . '" data-network-type="' . $typeId . '" data-network-id="' . $networkId . '" data-post-format="1" value="1" /><img class="img-responsive b2s-display-inline" src="' . plugins_url('/assets/images/settings/b2s-post-format-' . $networkId . '-2-' . (($this->lang == 'de') ? $this->lang : 'en') . '.png', B2S_PLUGIN_FILE) . '">';
                $content .='</label>';
                $content .='<br><br>';
                if ($networkId == 12) {
                    $content .= __('The image preview will be cropped automatically to fit the default Instagram layout for your Instagram timeline. The image will be shown uncropped when opening the preview page for your Instagram post.', 'blog2social');
                } else {
                    $content .= __('A photo or image post displays the selected image in the one-page preview of Blog2Social and your comment above the image. The image links to the image view on your image gallery in the respective network. Blog2Social adds the link to your post in your comment. The main benefit of photo posts is that your image is uploaded to your personal image albums or gallery. In Facebook, you can edit the albums name with a description of your choice.', 'blog2social');
                }
                $content .='</div>';
                $content .='</div>';
            }
        }
        return $content;
    }

    public function getNetworkSettingsPostFormatHtml($networkId = 1) {

        $optionPostFormat = $this->options->_getOption('post_format');
        $optionNoCache = $this->options->_getOption('link_no_cache');
        $optionNoCache = ($optionNoCache === false || $optionNoCache == 0) ? 0 : 1;  //default inactive , 1=active 0=not
//Take old settings
        if (!isset($optionPostFormat[$networkId])) {
            $oldPostFormatSettings = ($networkId == 1) ? (isset($this->settings->network_post_format_1) ? (int) $this->settings->network_post_format_1 : 0) : (isset($this->settings->network_post_format_2) ? (int) $this->settings->network_post_format_2 : 1);  // Twitter Default Photopost
            $post_format[$networkId] = array();
            $post_format[$networkId] = array('all' => $oldPostFormatSettings);
            $optionPostFormat = $post_format;
            $this->options->_setOption('post_format', $post_format);
        }

        if (!isset($optionPostFormat[$networkId]['all'])) {
            $optionPostFormat[$networkId]['all'] = 0;
        }

        $disabledInputType = (B2S_PLUGIN_USER_VERSION < 2) ? 'disabled' : '';
        $disabledInputAll = (B2S_PLUGIN_USER_VERSION == 0) ? 'disabled' : '';
        $disabledTextType = (B2S_PLUGIN_USER_VERSION < 2) ? 'font-gray' : '';
        $disabledTextAll = (B2S_PLUGIN_USER_VERSION == 0) ? 'font-gray' : '';
        $textAll = ($networkId == 1 || $networkId == 10) ? __('All', 'blog2social') : __('Profile', 'blog2social');


        $content = '';
        $content .='<div class="col-md-6 col-xs-12">';
        $content .= '<b>1) ' . (($networkId == 12) ? __('Image with frame', 'blog2social') : __('Link Post', 'blog2social') . ' <span class="glyphicon glyphicon-link b2s-color-green"></span>') . '</b><br><br>';
        $content .= '<img class="img-responsive b2s-display-inline" src="' . plugins_url('/assets/images/settings/b2s-post-format-' . $networkId . '-1-' . (($this->lang == 'de') ? $this->lang : 'en') . '.png', B2S_PLUGIN_FILE) . '">';
        $content .= '<br><br>';
        $content .='<div class="padding-left-15">';

        if ((B2S_PLUGIN_USER_VERSION < 2 && ($networkId == 1 || $networkId == 10)) || $networkId == 2 || $networkId == 12) {
            $content .= '<div class="col-lg-3 col-md-4 col-xs-5 del-padding-left del-padding-right b2s-input-margin-bottom-5"><input type="radio" ' . $disabledInputAll . ' id="all-' . $networkId . '-1"  ' . ( (isset($optionPostFormat[$networkId]) && is_array($optionPostFormat[$networkId]) && (int) $optionPostFormat[$networkId]['all'] == 0) ? 'checked' : ((!isset($optionPostFormat[$networkId])) ? 'checked' : '' )) . '   name="all" value="0"><label class="' . $disabledTextAll . '" for="all-' . $networkId . '-1">' . $textAll . '</label></div><div class="clearfix"></div>';
        }
        if ($networkId == 1 || $networkId == 10) {
            $content .= '<div class="col-lg-3 col-md-4 col-xs-5 del-padding-left del-padding-right"><input type="radio" ' . $disabledInputType . ' id="type[0]-' . $networkId . '-1" ' . ((isset($optionPostFormat[$networkId][0]) && (int) $optionPostFormat[$networkId][0] == 0) ? 'checked' : ( (int) $optionPostFormat[$networkId]['all'] == 0 && !isset($optionPostFormat[$networkId][0]) && B2S_PLUGIN_USER_VERSION >= 2) ? 'checked' : '') . ' name="type-format[0]" value="0"><label class="' . $disabledTextType . '" for="type[0]-' . $networkId . '-1">' . __('Profile', 'blog2social') . '</label></div><div class="clearfix"></div>';
            $content .= '<div class="col-lg-3 col-md-4 col-xs-5 del-padding-left del-padding-right"><input type="radio" ' . $disabledInputType . ' id="type[1]-' . $networkId . '-1" ' . ( (isset($optionPostFormat[$networkId][1]) && (int) $optionPostFormat[$networkId][1] == 0) ? 'checked' : ( (int) $optionPostFormat[$networkId]['all'] == 0 && !isset($optionPostFormat[$networkId][0]) && B2S_PLUGIN_USER_VERSION >= 2) ? 'checked' : '') . ' name="type-format[1]" value="0"><label class="' . $disabledTextType . '" for="type[1]-' . $networkId . '-1">' . __('Page', 'blog2social') . '</label></div><div class="clearfix"></div>';
            $content .= '<div class="col-lg-3 col-md-4 col-xs-5 del-padding-left del-padding-right"><input type="radio" ' . $disabledInputType . ' id="type[2]-' . $networkId . '-1" ' . ( (isset($optionPostFormat[$networkId][2]) && (int) $optionPostFormat[$networkId][2] == 0) ? 'checked' : ( (int) $optionPostFormat[$networkId]['all'] == 0 && !isset($optionPostFormat[$networkId][0]) && B2S_PLUGIN_USER_VERSION >= 2) ? 'checked' : '') . ' name="type-format[2]" value="0"><label class="' . $disabledTextType . '" for="type[2]-' . $networkId . '-1">' . __('Group', 'blog2social') . '</label></div><div class="clearfix"></div>';

//Option: no_cache param for varnish caching
            if ($networkId == 1) {
                $content .= '<br><div class="col-lg-6 col-md-6 col-xs-12 del-padding-left del-padding-right"><input id="link-no-cache" type="checkbox" ' . (($optionNoCache == 1) ? 'checked' : '') . ' name="no_cache" value="' . (($optionNoCache == 1) ? 0 : 1) . '"><label for="link-no-cache">' . __('Activate Instant Caching', 'blog2social') . '</label> <a href="#" data-toggle="modal" data-target="#b2sInfoNoCache" class="b2s-info-btn vertical-middle del-padding-left">' . __('Info', 'Blog2Social') . '</a></div><div class="clearfix"></div>';
            }
        }
        $content .='</div>';
        $content .='</div>';

        $content .='<div class="col-md-6 col-xs-12">';
        $content .= '<b>2)  ' . (($networkId == 12) ? __('Image cut out', 'blog2social') : __('Photo Post', 'blog2social') . ' <span class="glyphicon glyphicon-picture b2s-color-green"></span>') . '</b><br><br>';
        $content .= '<img class="img-responsive b2s-display-inline" src="' . plugins_url('/assets/images/settings/b2s-post-format-' . $networkId . '-2-' . (($this->lang == 'de') ? $this->lang : 'en') . '.png', B2S_PLUGIN_FILE) . '">';
        $content .= '<br><br>';
        $content .='<div class="padding-left-15">';

        if ((B2S_PLUGIN_USER_VERSION < 2 && ($networkId == 1 || $networkId == 10)) || $networkId == 2 || $networkId == 12) {
            $content .= '<div class="col-lg-3 col-md-4 col-xs-5 del-padding-left del-padding-right b2s-input-margin-bottom-5"><input type="radio" ' . $disabledInputAll . ' id="all-' . $networkId . '-2" ' . ((isset($optionPostFormat[$networkId]) && is_array($optionPostFormat[$networkId]) && (int) $optionPostFormat[$networkId]['all'] == 1) ? 'checked' : '') . '  name="all"  value="1"><label class="' . $disabledTextAll . '" for="all-' . $networkId . '-2">' . $textAll . '</label></div><div class="clearfix"></div>';
        }
        if ($networkId == 1 || $networkId == 10) {
            $content .= '<div class="col-lg-3 col-md-4 col-xs-5 del-padding-left del-padding-right"><input type="radio" ' . $disabledInputType . ' id="type[0]-' . $networkId . '-2" ' . ( (isset($optionPostFormat[$networkId][0]) && (int) $optionPostFormat[$networkId][0] == 1) ? 'checked' : ( (int) $optionPostFormat[$networkId]['all'] == 1 && !isset($optionPostFormat[$networkId][0]) && B2S_PLUGIN_USER_VERSION >= 2) ? 'checked' : '') . ' name="type-format[0]" value="1"><label class="' . $disabledTextType . '" for="type[0]-' . $networkId . '-2">' . __('Profile', 'blog2social') . '</label></div><div class="clearfix"></div>';
            $content .= '<div class="col-lg-3 col-md-4 col-xs-5 del-padding-left del-padding-right"><input type="radio" ' . $disabledInputType . ' id="type[1]-' . $networkId . '-2" ' . ( (isset($optionPostFormat[$networkId][1]) && (int) $optionPostFormat[$networkId][1] == 1) ? 'checked' : ( (int) $optionPostFormat[$networkId]['all'] == 1 && !isset($optionPostFormat[$networkId][1]) && B2S_PLUGIN_USER_VERSION >= 2) ? 'checked' : '') . ' name="type-format[1]" value="1"><label class="' . $disabledTextType . '" for="type[1]-' . $networkId . '-2">' . __('Page', 'blog2social') . '</label></div><div class="clearfix"></div>';
            $content .= '<div class="col-lg-3 col-md-4 col-xs-5 del-padding-left del-padding-right"><input type="radio" ' . $disabledInputType . ' id="type[2]-' . $networkId . '-2" ' . ( (isset($optionPostFormat[$networkId][2]) && (int) $optionPostFormat[$networkId][2] == 1) ? 'checked' : ( (int) $optionPostFormat[$networkId]['all'] == 1 && !isset($optionPostFormat[$networkId][1]) && B2S_PLUGIN_USER_VERSION >= 2) ? 'checked' : '') . ' name="type-format[2]" value="1"><label class="' . $disabledTextType . '" for="type[2]-' . $networkId . '-2">' . __('Group', 'blog2social') . '</label></div><div class="clearfix"></div>';
        }
        $content .='</div>';
        $content .='</div>';
        return $content;
    }

//view=ship
    public function setNetworkSettingsHtml() {
        $optionPostFormat = $this->options->_getOption('post_format');
        $content = "<input type='hidden' class='b2sNetworkSettingsPostFormatText' value='" . json_encode(array('post' => array(__('Link Post', 'blog2social'), __('Photo Post', 'blog2social')), 'image' => array(__('Image with frame'), __('Image cut out')))) . "'/>";
        foreach (array(1, 2, 10, 12) as $n => $networkId) { //FB,TW,In
//Take old settings
            if (!isset($optionPostFormat[$networkId])) {
                $oldPostFormatSettings = ($networkId == 1 || $networkId == 10) ? (isset($this->settings->network_post_format_1) ? (int) $this->settings->network_post_format_1 : 0) : (isset($this->settings->network_post_format_2) ? (int) $this->settings->network_post_format_2 : 1);  // Twitter Default Photopost
                $post_format[$networkId] = array();
                $post_format[$networkId] = array('all' => $oldPostFormatSettings);
                $optionPostFormat = $post_format;
                $this->options->_setOption('post_format', $post_format);
            }

            $postFormatType = ($networkId == 12) ? 'image' : 'post';
            $type = ($networkId == 1 || $networkId == 10) ? array(0, 1, 2) : array(0);
            foreach ($type as $t => $typeId) { //Profile,Page,Group                
                if (!isset($optionPostFormat[$networkId]['all']) && !isset($optionPostFormat[$networkId][$typeId])) { //DEFAULT
                    $optionPostFormat[$networkId]['all'] = 0;
                }
                $value = ((isset($optionPostFormat[$networkId]) && is_array($optionPostFormat[$networkId]) && ((isset($optionPostFormat[$networkId]['all']) && (int) $optionPostFormat[$networkId]['all'] == 0) || (isset($optionPostFormat[$networkId][$typeId]) && (int) $optionPostFormat[$networkId][$typeId] == 0)) ) ? 0 : (!isset($optionPostFormat[$networkId]) ? 0 : 1 ));
                $content .= "<input type='hidden' class='b2sNetworkSettingsPostFormatCurrent' data-post-format-type='" . $postFormatType . "' data-network-id='" . $networkId . "' data-network-type='" . $typeId . "' value='" . (int) $value . "' />";
            }
        }
        return $content;
    }

    private function getPostTypesHtml($selected = array(), $type = 'publish') {
        $content = '';
        $selected = (is_array($selected) && isset($selected[$type])) ? $selected[$type] : array();
        if (is_array($this->postTypesData) && !empty($this->postTypesData)) {
            foreach ($this->postTypesData as $k => $v) {
                if ($v != 'attachment' && $v != 'nav_menu_item' && $v != 'revision') {
                    $selItem = (in_array($v, $selected)) ? 'checked' : '';
                    $content .= ' <div class="b2s-post-type-list"><input id="b2s-post-type-item-' . $type . '-' . $v . '" class="b2s-post-type-item-' . $type . '" value="' . $v . '" name="b2s-settings-auto-post-' . $type . '[]" type="checkbox" ' . $selItem . '><label for="b2s-post-type-item-' . $type . '-' . $v . '">' . $v . '</label></div>';
                }
            }
        }
        return $content;
    }

}
