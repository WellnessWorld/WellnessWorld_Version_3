<?php

class B2S_Ship_Item {

    private $allowTitleProfile = array(7, 9, 13, 15, 16);
    private $allowTitlePage = array();
    private $allowTitleGroup = array();
    private $setPostFormat = array(1, 2, 10, 12);
    private $isCommentProfile = array(1, 3, 8, 10, 17, 19);
    private $isCommentPage = array(1);
    private $isCommentGroup = array(1);
    private $allowTag = array(4, 9, 11, 16);
    private $allowHtml = array(4, 11, 14);
    private $showTitleProfile = array(4, 9, 11, 14, 16);
    private $showTitlePage = array(8, 19 => array(1)); //Xing Business Page
    private $showTitleGroup = array(8, 11, 19);
    private $onlyImage = array(6, 7, 12);
    private $allowNoImageProfile = array(5, 9);
    private $allowNoCustomImageProfile = array(8, 15, 19);
    private $allowNoEmoji = array(9, 13, 14, 15, 16, 18);
    private $allowNoImagePage = array(8);
    private $allowEditUrl = array(1, 2, 3, 4, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19);
    private $showBoards = array(6);
    private $showRelay = array(2);
    private $showBoardsGroup = array(10);
    private $showGroups = array(8, 15, 19);
    private $changeDisplayName = array(8);
    private $setShortTextProfile = array(1 => 239, 2 => 255, 3 => 239, 6 => 300, 8 => 239, 10 => 442, 12 => 240, 9 => 200, 16 => 250, 17 => 442, 18 => 800, 19 => 239);
    private $setShortTextProfileLimit = array(1 => 400, 2 => 256, 3 => 400, 6 => 400, 8 => 400, 10 => 500, 12 => 400, 9 => 200, 18 => 1000, 19 => 400);
    private $setShortTextPage = array(1 => 239, 3 => 239, 8 => 400, 10 => 442, 17 => 442, 19 => 239);
    private $setShortTextPageLimit = array(1 => 400, 3 => 400, 8 => 400, 10 => 500, 19 => array(0 => 400, 1 => 2000)); // XING Company Page, Business Page
    private $limitCharacterTitlePage = array(19 => array(1 => 150)); //XING Business Page selected over networkKind
    private $setShortTextGroup = array(1 => 239, 8 => 239, 10 => 442, 17 => 442, 19 => 239);
    private $setShortTextGroupLimit = array(1 => 400, 8 => 400, 10 => 500);
    private $allowHashTags = array(1, 2, 3, 6, 10, 12, 17);
    private $limitCharacterProfile = array(2 => 280, 3 => 1300, 6 => 500, 8 => 420, 9 => 250, 15 => 300, 12 => 2000, 18 => 1500, 19 => 420);
    private $showImageAreaProfile = array(6, 7, 10, 12, 16, 17, 18);
    private $showImageAreaPage = array(10, 17, 19);
    private $showImageAreaGroup = array(8, 10, 17, 19);
    private $showMarketplace = array(19);
    private $limitCharacterPage = array(3 => 1300, 8 => 1200, 19 => array(0 => 400, 1 => 2000)); // XING Company Page, Business Page
    private $requiredUrl = array(1, 3, 8, 9, 10, 15, 19);
    private $getText = array(1, 7, 10, 12, 16, 17, 18);
    private $allowSchedCustomizeContent = array(1, 2, 3, 6, 7, 8, 9, 10, 12, 15, 17, 18, 19);
    private $maxWeekTimeSelect = 52;
    private $networkTosProfile = array(2);
    private $networkTosGroup = array(8, 19);
    private $maxMonthTimeSelect = 12;
    private $maxTimeSelect = 50;
    private $maxSchedCount = 3;
    private $selMarketplaceCategory = 3;
    private $selMarketplaceType = 1;
    private $selGroup = null;
    private $selBoard = null;
    //private $maxReplayCount = 3;
    private $setRelayCount = 0;
    private $maxDaySelect = 31;
    private $noScheduleRegularly = array(2, 4, 11, 14, 15, 18);
    private $noScheduleRegularlyPage = array(8, 19);
    private $addNoMoreSchedPage = array(8, 19);
    private $addNoMoreSchedGroup = array(8, 19);
    private $defaultImage;
    private $postData;
    private $postUrl;
    private $postStatus;
    private $websiteName;
    private $postId;
    private $userLang;
    private $selSchedDate;
    private $viewMode;
    private $userOptions;
    private $b2sPostType;

    public function __construct($postId, $userLang = 'en', $selSchedDate = "", $b2sPostType = "", $relayCount = 0) {
        $this->postId = $postId;
        $this->postData = get_post($this->postId);
        $this->postStatus = $this->postData->post_status;
        $this->websiteName = get_option('blogname');
        $this->b2sPostType = (!empty($b2sPostType) ? $b2sPostType : ( (isset($this->postData->post_type) && $this->postData->post_type == 'b2s_ex_post') ? 'ex' : ''));
        $this->postUrl = ($this->b2sPostType == 'ex') ? $this->postData->guid : (get_permalink($this->postData->ID) !== false ? get_permalink($this->postData->ID) : $this->postData->guid);
        $this->userLang = $userLang;
        $this->selSchedDate = $selSchedDate;
        $this->setRelayCount = $relayCount;
    }

    protected function getPostId() {
        return $this->postId;
    }

    public function getItemHtml($data, $show_time = true) {

        $this->viewMode = (isset($data->view) && !empty($data->view)) ? $data->view : null;  //normal or modal(Kalendar)
        //Override data values from edit modus
        $schedMetaData = ($this->viewMode == 'modal') ? $this->hook_sched_data(array()) : array();
        if (!empty($schedMetaData) && is_array($schedMetaData)) {
            $data->instantSharing = 0;
            if (isset($schedMetaData['network_kind'])) {
                $data->networkKind = $schedMetaData['network_kind'];
            }
            if (isset($schedMetaData['network_tos_group_id'])) {
                $data->networkTosGroupId = $schedMetaData['network_tos_group_id'];
            }
            if (isset($schedMetaData['marketplace_category'])) {
                $this->selMarketplaceCategory = (int) $schedMetaData['marketplace_category'];
            }
            if (isset($schedMetaData['marketplace_type'])) {
                $this->selMarketplaceType = (int) $schedMetaData['marketplace_type'];
            }
            if (isset($schedMetaData['group'])) {
                $this->selGroup = $schedMetaData['group'];
            }
            if (isset($schedMetaData['board'])) {
                $this->selBoard = $schedMetaData['board'];
            }
        }

        $userOptions = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
        $allowUserHashTag = $userOptions->_getOption('user_allow_hashtag');
        $contentTwitter = $userOptions->_getOption('content_network_twitter');
        $networkName = unserialize(B2S_PLUGIN_NETWORK);
        $networkTypeName = unserialize(B2S_PLUGIN_NETWORK_TYPE);
        $networkKindName = unserialize(B2S_PLUGIN_NETWORK_KIND);
        $limit = false;
        $limitValue = 0;
        $textareaLimitInfo = "";
        $textareaOnKeyUp = "";
        $this->defaultImage = plugins_url('/assets/images/no-image.png', B2S_PLUGIN_FILE);

//Settings
        switch ($data->networkType) {
            case '0':
//profil
                if (isset($this->limitCharacterProfile[$data->networkId]) && (int) $this->limitCharacterProfile[$data->networkId] > 0) {
                    $limitValue = $this->limitCharacterProfile[$data->networkId];
                    $limit = true;
                }
                $infoImage = (in_array($data->networkId, $this->allowNoImageProfile)) ? __('Network does not support image for profiles', 'blog2social') . '!' : '';
                $infoImage .= (in_array($data->networkId, $this->allowNoCustomImageProfile)) ? (!empty($infoImage) ? ' | ' : '') . __('Network defines image by link', 'blog2social') . '!' : '';
                $htmlTags = highlight_string("<p><br><i><b><a><img>", true);
                $infoImage .= (in_array($data->networkId, $this->allowHtml)) ? (!empty($infoImage) ? ' | ' : '') . __('Supported HTML tags', 'blog2social') . ': ' . $htmlTags : '';
                $infoImage .= (in_array($data->networkId, $this->allowNoEmoji)) ? (!empty($infoImage) ? ' | ' : '') . __('Network does not support emojis', 'blog2social') . '!' : '';

                $network_display_name = $data->network_display_name;
                $isRequiredTextarea = (in_array($data->networkId, $this->isCommentProfile)) ? '' : 'required="required"';

//ShortText
                if (isset($this->setShortTextProfile[$data->networkId]) && (int) $this->setShortTextProfile[$data->networkId] > 0) {
//$preContent = ($data->networkId == 2) ? B2S_Util::getTitleByLanguage($this->postData->post_title, $this->userLang) : B2S_Util::prepareContent($this->postId, $this->postData->post_content, $this->postUrl, false, (in_array($data->networkId, $this->allowNoEmoji) ? false : true), $this->userLang);
                    if ($data->networkId == 2) {
                        $preContent = B2S_Util::getTitleByLanguage($this->postData->post_title, $this->userLang);
                        if ($contentTwitter !== false && $contentTwitter == 1 && B2S_PLUGIN_USER_VERSION >= 1) {
                            $preContent .=' ' . B2S_Util::prepareContent($this->postId, $this->postData->post_content, $this->postUrl, false, false, $this->userLang); //append
                        }
                        if ($contentTwitter !== false && $contentTwitter == 2 && B2S_PLUGIN_USER_VERSION >= 1) {
                            $preContent = B2S_Util::prepareContent($this->postId, $this->postData->post_content, $this->postUrl, false, false, $this->userLang); //only
                        }
                    } else {
                        $preContent = B2S_Util::prepareContent($this->postId, $this->postData->post_content, $this->postUrl, false, (in_array($data->networkId, $this->allowNoEmoji) ? false : true), $this->userLang);
                    }
                    $message = B2S_Util::getExcerpt($preContent, (int) $this->setShortTextProfile[$data->networkId], (isset($this->setShortTextProfileLimit[$data->networkId]) ? (int) $this->setShortTextProfileLimit[$data->networkId] : false));
                } else {
                    $message = (in_array($data->networkId, $this->allowTitleProfile) ? (in_array($data->networkId, $this->allowNoEmoji) ? B2S_Util::remove4byte(B2S_Util::getTitleByLanguage($this->postData->post_title, $this->userLang)) : B2S_Util::getTitleByLanguage($this->postData->post_title, $this->userLang)) : B2S_Util::prepareContent($this->postId, $this->postData->post_content, $this->postUrl, (in_array($data->networkId, $this->allowHtml) ? '<p><h1><h2><br><i><b><a><img>' : false), (in_array($data->networkId, $this->allowNoEmoji) ? false : true), $this->userLang));
                }

//Feature Image Html-Network
                if (in_array($data->networkId, $this->allowHtml)) {
                    $featuredImage = wp_get_attachment_url(get_post_thumbnail_id($this->postId));
                    if ($featuredImage !== false) {
                        $title = in_array($data->networkId, $this->allowNoEmoji) ? B2S_Util::remove4byte(B2S_Util::getTitleByLanguage($this->postData->post_title, $this->userLang)) : B2S_Util::getTitleByLanguage($this->postData->post_title, $this->userLang);
                        $message = '<img class="b2s-post-item-details-image-html-src" src="' . $featuredImage . '" alt="' . $title . '"/><br />' . $message;
                    }
                }

//Hashtags
                if (in_array($data->networkId, $this->allowHashTags) && ($allowUserHashTag === false || $allowUserHashTag == 1)) {
                    $add = ($data->networkId != 2) ? "\n\n" : "";
                    $message .= $this->getHashTagsString($add);
                }

                $message = $this->hook_message($message);

                $countCharacter = 0;
                if ($limit !== false) {
                    $infoCharacterCount = ($data->networkId != 2) ? ' (' . __('Text only', 'blog2social') . ')' : '';
                    $textareaLimitInfo .= '<span class="b2s-post-item-countChar" data-network-count="-1" data-network-auth-id="' . $data->networkAuthId . '">' . (int) $countCharacter . '</span>/' . $limitValue . ' ' . __('characters', 'blog2social') . $infoCharacterCount . '</span>';
                    $textareaOnKeyUp = 'onkeyup="networkLimitAll(\'' . $data->networkAuthId . '\',\'' . $data->networkId . '\',\'' . $limitValue . '\');"';
                } else {
                    $textareaOnKeyUp = 'onkeyup="networkCount(\'' . $data->networkAuthId . '\');"';
                    $textareaLimitInfo = '<span class="b2s-post-item-countChar" data-network-count="-1" data-network-auth-id="' . $data->networkAuthId . '">' . (int) $countCharacter . '</span> ' . __('characters', 'blog2social') . '</span>';
                }

                break;
            case '1':
//page
                if (isset($this->limitCharacterPage[$data->networkId]) && ((int) $this->limitCharacterPage[$data->networkId] > 0) || isset($this->limitCharacterPage[$data->networkId][$data->networkKind])) {
                    $limitValue = (isset($this->limitCharacterPage[$data->networkId][$data->networkKind])) ? $this->limitCharacterPage[$data->networkId][$data->networkKind] : $this->limitCharacterPage[$data->networkId];
                    $limit = true;
                }
                $infoImage = (in_array($data->networkId, $this->allowNoImagePage)) ? __('Network does not support image for pages', 'blog2social') . '!' : '';
                $infoImage .= (in_array($data->networkId, $this->allowNoEmoji)) ? (!empty($infoImage) ? ' | ' : '') . __('Network does not support emojis', 'blog2social') . '!' : '';

//ShortText
                if (isset($this->setShortTextPage[$data->networkId]) && (int) $this->setShortTextPage[$data->networkId] > 0) {
                    if ($data->networkId == 8) { //Xing -1 Leerzeichen
                        $this->setShortTextPage[$data->networkId] = (int) $this->setShortTextPage[$data->networkId] - mb_strlen($this->postUrl, 'UTF-8') - 1;
                    }
                    $message = B2S_Util::getExcerpt(B2S_Util::prepareContent($this->postId, $this->postData->post_content, $this->postUrl, false, (in_array($data->networkId, $this->allowNoEmoji) ? false : true), $this->userLang), (isset($this->setShortTextPage[$data->networkId]) ? (int) $this->setShortTextPage[$data->networkId] : false), (isset($this->setShortTextPageLimit[$data->networkId]) ? (( is_array($this->setShortTextPageLimit[$data->networkId]) && isset($this->setShortTextPageLimit[$data->networkId][$data->networkKind])) ? (int) $this->setShortTextPageLimit[$data->networkId][$data->networkKind] : (int) $this->setShortTextPageLimit[$data->networkId] ) : false));
                } else {
                    $message = (in_array($data->networkId, $this->allowTitlePage) ? (in_array($data->networkId, $this->allowNoEmoji) ? B2S_Util::remove4byte(B2S_Util::getTitleByLanguage($this->postData->post_title, $this->userLang)) : B2S_Util::getTitleByLanguage($this->postData->post_title, $this->userLang)) : B2S_Util::prepareContent($this->postId, $this->postData->post_content, $this->postUrl, (in_array($data->networkId, $this->allowHtml) ? '<p><h1><h2><br><i><b><a><img>' : false), (in_array($data->networkId, $this->allowNoEmoji) ? false : true), $this->userLang));
                }

//Hashtags
                if (in_array($data->networkId, $this->allowHashTags) && ($allowUserHashTag === false || $allowUserHashTag == 1)) {
                    $message .= $this->getHashTagsString();
                }

                $message = $this->hook_message($message);

                $network_display_name = $data->network_display_name;
                $isRequiredTextarea = (in_array($data->networkId, $this->isCommentPage)) ? '' : 'required="required"';

                $countCharacter = 0;
                if ($limit !== false) {
                    $textareaLimitInfo = '<span class="b2s-post-item-countChar" data-network-count="-1" data-network-auth-id="' . $data->networkAuthId . '">' . (int) $countCharacter . '</span>/' . $limitValue . ' ' . __('characters', 'blog2social') . '</span>';
                    $textareaOnKeyUp = 'onkeyup="networkLimitAll(\'' . $data->networkAuthId . '\',\'' . $data->networkId . '\',\'' . $limitValue . '\');"';
                } else {
                    $textareaOnKeyUp = 'onkeyup="networkCount(\'' . $data->networkAuthId . '\');"';
                    $textareaLimitInfo = '<span class="b2s-post-item-countChar" data-network-count="-1" data-network-auth-id="' . $data->networkAuthId . '">' . (int) $countCharacter . '</span> ' . __('characters', 'blog2social') . '</span>';
                }
                break;
            case'2':
//group
//ShortText
                if (isset($this->setShortTextGroup[$data->networkId]) && (int) $this->setShortTextGroup[$data->networkId] > 0) {
                    $message = B2S_Util::getExcerpt(B2S_Util::prepareContent($this->postId, $this->postData->post_content, $this->postUrl, false, (in_array($data->networkId, $this->allowNoEmoji) ? false : true), $this->userLang), (isset($this->setShortTextGroup[$data->networkId]) ? (int) $this->setShortTextGroup[$data->networkId] : false), (isset($this->setShortTextGroupLimit[$data->networkId]) ? (int) $this->setShortTextGroupLimit[$data->networkId] : false));
                } else {
                    $message = (in_array($data->networkId, $this->allowTitleGroup) ? (in_array($data->networkId, $this->allowNoEmoji) ? B2S_Util::remove4byte(B2S_Util::getTitleByLanguage($this->postData->post_title, $this->userLang)) : B2S_Util::getTitleByLanguage($this->postData->post_title, $this->userLang)) : B2S_Util::prepareContent($this->postId, $this->postData->post_content, $this->postUrl, (in_array($data->networkId, $this->allowHtml) ? '<p><h1><h2><br><i><b><a><img>' : false), (in_array($data->networkId, $this->allowNoEmoji) ? false : true), $this->userLang));
                }
//Hashtags
                if (in_array($data->networkId, $this->allowHashTags) && ($allowUserHashTag === false || $allowUserHashTag == 1)) {
                    $message .= $this->getHashTagsString();
                }

                $message = $this->hook_message($message);
                $network_display_name = $data->network_display_name;
                $isRequiredTextarea = (in_array($data->networkId, $this->isCommentGroup)) ? '' : 'required="required"';
                $countCharacter = 0;
                if ($limit !== false) {
                    $textareaLimitInfo = '<span class="b2s-post-item-countChar" data-network-count="-1" data-network-auth-id="' . $data->networkAuthId . '">' . (int) $countCharacter . '</span>/' . $limitValue . ' ' . __('characters', 'blog2social') . '</span>';
                    $textareaOnKeyUp = 'onkeyup="networkLimitAll(\'' . $data->networkAuthId . '\',\'' . $data->networkId . '\',\'' . $limitValue . '\');"';
                } else {
                    $textareaOnKeyUp = 'onkeyup="networkCount(\'' . $data->networkAuthId . '\');"';
                    $textareaLimitInfo = '<span class="b2s-post-item-countChar" data-network-count="-1" data-network-auth-id="' . $data->networkAuthId . '">' . (int) $countCharacter . '</span> ' . __('characters', 'blog2social') . '</span>';
                }
                break;
        }


//Infotexte
        $messageInfo = (!empty($infoImage)) ? '<p class="b2s-post-item-message-info pull-left hidden-sm hidden-xs">' . $infoImage . '</p>' : '';
        $onlyimage = in_array($data->networkId, $this->onlyImage) ? 'b2sOnlyWithImage' : '';

        $content = '<div class="b2s-post-item ' . $onlyimage . '" data-network-auth-id="' . $data->networkAuthId . '" data-network-id="' . $data->networkId . '">';
        $content .= '<div class="panel panel-group" data-network-auth-id="' . $data->networkAuthId . '">';
        $content .= '<div class="panel-body ' . ((isset($this->viewMode) && $this->viewMode == 'modal') ? 'del-padding-left del-padding-right' : '') . ' ">';
        $content .= '<div class="b2s-post-item-area" data-network-auth-id="' . $data->networkAuthId . '">';
        $content .= '<div class="b2s-post-item-thumb hidden-xs">';
        $content .= '<img alt="" data-network-auth-id="' . $data->networkAuthId . '" class="img-responsive b2s-post-item-network-image" src="' . plugins_url('/assets/images/portale/' . $data->networkId . '_flat.png', B2S_PLUGIN_FILE) . '">';
        $content .= '</div>';
        $content .= '<div class="b2s-post-item-details">';
        //XING deprecated
        if ($data->networkId == 8) {
            $content .='<div class="b2s-post-item-network-deprecated"><span class="glyphicon glyphicon-exclamation-sign glyphicon-info"></span> ' . __('Connection expires on 31 March 2019', 'blog2social') . '</div>';
        }
        // G+ deprecated 
        if ($data->networkId == 10) {
            $content .='<div class="b2s-post-item-network-deprecated"><span class="glyphicon glyphicon-exclamation-sign glyphicon-info"></span> ' . __('Connection expires on 2 April 2019', 'blog2social') . '</div>';
        }
        $content .= '<h4 class="pull-left b2s-post-item-details-network-display-name" data-network-auth-id="' . $data->networkAuthId . '">' . stripslashes($network_display_name) . '</h4>';
        $content .= '<div class="clearfix"></div>';
        $content .= '<p class="pull-left">' . ($data->networkId == 19 && $data->networkType == 1 && isset($networkKindName[$data->networkKind]) ? $networkKindName[$data->networkKind] . '-' : '') . $networkTypeName[$data->networkType] . (($data->networkId == 19 && (int) $data->networkType == 1 && (int) $data->networkKind == 0) ? ' (' . __('Employer Branding', 'blog2social') . ')' : '') . ' | ' . $networkName[$data->networkId];
        $content .= '<div class="b2s-post-item-details-message-result" data-network-auth-id="' . $data->networkAuthId . '" style="display:none;"></div>';
        $content .= '<span class="hidden-xs b2s-post-item-details-message-info" data-network-auth-id="' . $data->networkAuthId . '">' . $messageInfo . '</span></span>';

        $content .= '<div class="pull-right hidden-xs b2s-post-item-info-area">';

        if (in_array($data->networkId, $this->setPostFormat)) {
            $postFormatType = ($data->networkId == 12) ? 'image' : 'post';
            $addCSS = (B2S_PLUGIN_USER_VERSION == 0) ? 'b2s-btn-disabled' : '';
            $content .= '<button class="btn btn-xs btn-link b2s-post-ship-item-post-format ' . $addCSS . '" data-post-wp-type="' . $this->b2sPostType . '" data-post-format-type="' . $postFormatType . '" data-network-auth-id="' . $data->networkAuthId . '" data-network-type="' . $data->networkType . '" data-network-id="' . $data->networkId . '" >' . __('post format', 'blog2social') . ': <span class="b2s-post-ship-item-post-format-text" data-post-format-type="' . $postFormatType . '" data-network-auth-id="' . $data->networkAuthId . '" data-network-type="' . $data->networkType . '"  data-network-id="' . $data->networkId . '" ></span></button>';
            if (B2S_PLUGIN_USER_VERSION > 0) {
                $content .= '<input type="hidden" class="b2s-post-item-details-post-format" name="b2s[' . $data->networkAuthId . '][post_format]" data-network-auth-id="' . $data->networkAuthId . '" data-network-id="' . $data->networkId . '" data-network-type="' . $data->networkType . '" value="0" />';
            } else {
                $content .= '<span class="label label-success"><a target="_blank" class="btn-label-premium b2s-btn-trigger-post-ship-item-post-format" data-network-auth-id="' . $data->networkAuthId . '" href="#">PREMIUM</a></span>';
            }
        }
        $content .='<span class="b2s-post-tool-area" data-network-auth-id="' . $data->networkAuthId . '">';
        $content .= (in_array($data->networkId, $this->setPostFormat)) ? '  | ' : '';
        if (in_array($data->networkId, $this->getText)) {
            $content .= '<button class="btn btn-xs btn-link b2s-post-ship-item-full-text" data-network-count="-1" data-network-auth-id="' . $data->networkAuthId . '" >' . __('Insert full-text', 'blog2social') . '</button> | ';
        }
        $content .= '<button class="btn btn-xs btn-link b2s-post-ship-item-message-delete" data-network-count="-1" data-network-auth-id="' . $data->networkAuthId . '">' . __('Delete text', 'blog2social') . '</button> | ';
        $content .= $textareaLimitInfo . '</span></div>';

        $content .= '</p>';

        //TOS Facebook 072018
        if ((int) $data->networkId == 1 && (int) $data->networkType == 0 && isset($data->instantSharing) && (int) $data->instantSharing == 1) {
            $content .='';
            $content .='<div class="b2s-post-approve-info" data-network-auth-id="' . $data->networkAuthId . '"><div class="clearfix"></div><div class="alert alert-warning">' . __('You can customize your post with individual comments, @-handles, emojis, and more in the Instant Sharing window, after planning your post.', 'blog2social') . '</div></div>';
        }

        //TOS Twitter 030218
        if ($data->networkType == 0 && in_array($data->networkId, $this->networkTosProfile)) {
            $content .='';
            $content .='<div class="b2s-unique-content" data-network-id="' . $data->networkId . '" data-network-auth-id="' . $data->networkAuthId . '"><div class="clearfix"></div><div class="alert b2s-unique-content-alert alert-danger">' . __('Please keep in mind that according to Twitter’s new TOS, users are no longer allowed to post identical or substantially similar content to multiple accounts or multiple duplicate updates on one account.', 'blog2social') . '<br><strong>' . __('Violating these rules can result in Twitter suspending your account. Always vary your Tweets with different comments, hashtags or handles to prevent duplicate posts.', 'blog2social') . '</strong> <a href="' . B2S_Tools::getSupportLink('network_tos_blog_032018') . '" target="_blank">' . __('Learn more about this', 'blog2social') . '</a></div></div>';
        }
        //TOS Xing Group 080218
        if ($data->networkType == 2 && in_array($data->networkId, $this->networkTosGroup)) {
            $content .='';
            $content .='<div class="b2s-content-info" data-network-id="' . $data->networkId . '" data-network-auth-id="' . $data->networkAuthId . '"><div class="clearfix"></div><div class="alert alert-warning">' . __('Please note: XING allows identical posts to be published only once within a group and no more than three times across different groups.', 'blog2social') . ' <a href="' . B2S_Tools::getSupportLink('network_tos_blog_082018') . '" target="_blank">' . __('Read more', 'blog2social') . '</a></div></div>';
        }

        $content .= '<div class="b2s-post-item-details-edit-area" data-network-auth-id="' . $data->networkAuthId . '">';
        $content .= (in_array($data->networkId, $this->showBoards) || ($data->networkType == 2 && in_array($data->networkId, $this->showBoardsGroup))) ? $this->getBoardHtml($data->networkAuthId, $data->networkId) : '';
        $content .= (in_array($data->networkId, $this->showGroups) && ($data->networkType == 2 || $data->networkId == 15)) ? $this->getGroupsHtml($data->networkAuthId, $data->networkId) : '';
        $content .= (in_array($data->networkId, $this->showMarketplace) && $data->networkType == 2) ? $this->getMarketplaceAreaHtml($data->networkAuthId, $data->networkId, $data->networkType, $data->networkKind) : '';
        $content .= ((in_array($data->networkId, $this->showTitleProfile) && $data->networkType == 0) || (((in_array($data->networkId, $this->showTitlePage) && !is_array($this->showTitlePage[$data->networkId])) || (is_array($this->showTitlePage[$data->networkId]) && in_array($data->networkKind, $this->showTitlePage[$data->networkId]))) && $data->networkType == 1) || (in_array($data->networkId, $this->showTitleGroup) && $data->networkType == 2)) ? $this->getTitleHtml($data->networkId, $data->networkAuthId, $data->networkKind, $data->networkType, $this->postData->post_title) : '';
        $content .= $this->getCustomEditArea($data->networkId, $data->networkAuthId, $data->networkType, $message, $isRequiredTextarea, $textareaOnKeyUp, $limit, $limitValue, isset($data->image_url) ? $data->image_url : null);
        $content .= (in_array($data->networkId, $this->allowTag) && $data->networkType == 0) ? $this->getTagsHtml($data->networkAuthId, (($allowUserHashTag === false || $allowUserHashTag == 1) ? true : false)) : '';

        //Calendar
        if (!(isset($this->viewMode) && $this->viewMode == 'modal')) {
            $content .='<br>';
            $content .='<div class="b2s-calendar-filter-area col-xs-2 pull-right del-padding-right hide" data-network-auth-id="' . $data->networkAuthId . '">';
            $content .='<select class="b2s-calendar-filter-network-sel form-control" name="b2s-calendar-filter-network-sel" data-last-sel="' . $data->networkId . '" data-network-auth-id="' . $data->networkAuthId . '"><option value="all">show all</option><option selected value="' . $data->networkId . '">' . $networkName[$data->networkId] . '</option></select>';
            $content .='</div>';
            if (in_array($data->networkId, $this->showRelay)) {
                $content .= $this->getRelayBtnHtml($data->networkAuthId, $data->networkId);
            }
            $content .='<a href="#" class="b2s-toogle-calendar-btn btn btn-primary pull-right btn-xs hidden-xs" data-network-id="' . $data->networkId . '" data-network-auth-id="' . $data->networkAuthId . '" data-toogle-text-btn="' . __("hide calendar", "blog2social") . '">' . __("show calendar", "blog2social") . '</a>';
            $content .='<div class="clearfix"></div><div class="b2s-post-item-calendar-area hide hidden-xs" data-network-auth-id="' . $data->networkAuthId . '"></div>';

            if (in_array($data->networkId, $this->showRelay)) {
                $content .= $this->getRelayContentHtml($data->networkAuthId, $data->networkId);
            }
        }

        if ($show_time) {
            $content .= $this->getShippingTimeHtml($data->networkAuthId, $data->networkType, $data->networkId, $data->networkType, $message, $isRequiredTextarea, $textareaOnKeyUp, $limit, $limitValue, isset($data->image_url) ? $data->image_url : null);
        }

        $content .= '</div>';

        $content .= '</div>';
        $content .= '</div>';
        $content .= '</div>';

        $content .= '<input type="hidden" class="form-control" name="b2s[' . $data->networkAuthId . '][network_id]" value="' . $data->networkId . '">';
        $content .= '<input type="hidden" class="form-control" name="b2s[' . $data->networkAuthId . '][network_type]" value="' . $data->networkType . '">';
        $content .= '<input type="hidden" class="form-control" name="b2s[' . $data->networkAuthId . '][instant_sharing]" value="' . $data->instantSharing . '">';
        $content .= '<input type="hidden" class="form-control" name="b2s[' . $data->networkAuthId . '][network_tos_group_id]" value="' . $data->networkTosGroupId . '">';
        $content .= '<input type="hidden" class="form-control networkKind" data-network-auth-id="' . $data->networkAuthId . '" name="b2s[' . $data->networkAuthId . '][network_kind]" value="' . $data->networkKind . '">';
        $content .= '<input type="hidden" data-network-auth-id="' . $data->networkAuthId . '" class="form-control b2s-post-ship-network-display-name" name="b2s[' . $data->networkAuthId . '][network_display_name]" value="' . $data->network_display_name . '">';

        $content .= '</div>';
        return $content;
    }

    public function getCustomEditArea($networkId, $networkAuthId, $networkType, $message, $isRequiredTextarea, $textareaOnKeyUp, $limit, $limitValue, $imageUrl = null) {
        $meta = array();
        if ($networkId == 1 || ($networkId == 8 && $networkType == 0) || ($networkId == 19 && $networkType == 0) || $networkId == 3 || $networkId == 2) {
            if (trim(strtolower($this->postStatus)) == 'publish' || $this->b2sPostType == 'ex') {
                //is calendar edit => scrape post url and not custom post url by override from edit function for meta tags!
                //$editPostUrl = $this->viewMode == 'modal') ? (get_permalink($this->postData->ID) !== false ? get_permalink($this->postData->ID) : $this->postData->guid) : $this->postUrl;
                $meta = B2S_Util::getMetaTags($this->postId, $this->postUrl, $networkId);
                //Case: no twitter image tag try og image tag
                if ($networkId == 2 && !isset($meta['image'])) {
                    $meta = B2S_Util::getMetaTags($this->postId, $this->postUrl);
                }
            } else {
                $desc = B2S_Util::getExcerpt(B2S_Util::prepareContent($this->postId, $this->postData->post_content, $this->postUrl, false, (in_array($networkId, $this->allowNoEmoji) ? false : true), $this->userLang), 150);
                if (empty($desc) && isset($this->postData->post_excerpt) && !empty($this->postData->post_excerpt)) {
                    $desc = B2S_Util::getExcerpt(B2S_Util::prepareContent($this->postId, $this->postData->post_excerpt, $this->postUrl, false, (in_array($networkId, $this->allowNoEmoji) ? false : true), $this->userLang), 150);
                }
                $meta = array('title' => B2S_Util::getExcerpt(B2S_Util::getTitleByLanguage($this->postData->post_title, $this->userLang), 50) . ' - ' . $this->websiteName, 'description' => $desc);
            }

            //EDIT Function - Calendar
            $meta = (is_array($meta)) ? $meta : array();
            $meta = $this->hook_meta($meta);
            $imageUrl = $imageUrl ? $imageUrl : (isset($meta['image']) ? $meta['image'] : null);


            if ($networkId == 1) {
                $edit = '<textarea class="form-control fb-textarea-input b2s-post-item-details-item-message-input ' . (in_array($networkId, $this->allowHtml) ? 'b2s-post-item-details-item-message-input-allow-html' : '') . '"data-network-count="-1" data-network-text-limit="' . $limitValue . '" data-network-auth-id="' . $networkAuthId . '" placeholder="' . __('Write something about your post...', 'blog2social') . '" name="b2s[' . $networkAuthId . '][content]" ' . $isRequiredTextarea . ' ' . $textareaOnKeyUp . '>' . $message . '</textarea>';
                $edit .= '<div class="row">';
                $edit .= '<div class="' . ((isset($this->viewMode) && $this->viewMode == 'modal') ? 'col-xs-12' : 'col-xs-12 col-sm-5 col-lg-3') . '">';
                $edit .= '<button class="btn btn-primary btn-circle b2s-image-remove-btn" data-network-count="-1" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" ' . ($imageUrl ? '' : 'style="display:none"') . '><i class="glyphicon glyphicon-trash"></i></button>';
                $edit .= '<img src="' . (isset($meta['image']) && !empty($meta['image']) ? $meta['image'] : $this->defaultImage) . '" class="fb-url-image b2s-post-item-details-url-image center-block img-responsive" data-network-count="-1" data-network-id="' . $networkId . '" data-network-image-change="1" data-network-auth-id="' . $networkAuthId . '">';
                $edit .= '<input type="hidden" class="b2s-image-url-hidden-field form-control" data-network-count="-1" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" value="' . ($imageUrl ? $imageUrl : "") . '" name="b2s[' . $networkAuthId . '][image_url]">';
                $edit .= '<div class="clearfix"></div>';
                $edit .= '<button class="btn btn-link btn-xs center-block b2s-select-image-modal-open" data-network-count="-1" data-meta-type="og" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" data-post-id="' . $this->postId . '" data-image-url="' . esc_attr($imageUrl) . '">' . __('Change image', 'blog2social') . '</button>';
                $edit .= '</div>';
                $edit .= '<div class="' . ((isset($this->viewMode) && $this->viewMode == 'modal') ? 'col-xs-12"' : 'col-xs-12 col-sm-7 col-lg-9 b2s-post-original-area" data-network-auth-id="' . $networkAuthId . '"') . '>';
                if (B2S_PLUGIN_USER_VERSION > 0) {
                    $edit .= '<button data-network-count="-1" data-network-auth-id="' . $networkAuthId . '" data-meta-type="og" data-meta-origin="ship" class=" btn btn-xs hidden-xs btn-link b2s-load-info-meta-tag-modal">' . __('Info: Change Open Graph Meta tags image, title and description for this network', 'blog2social') . '</button>';
                } else {
                    $edit .= '<a target="_blank" class="btn-label-premium btn-label-premium-xs b2s-load-info-meta-tag-modal" data-meta-type="og" data-meta-origin="ship" href="#"><span class="label label-success">PREMIUM</span></a>';
                    $edit .= '<a href="#" class="btn btn-link btn-xs b2s-load-info-meta-tag-modal" data-meta-type="og" data-meta-origin="ship">' . __('You want to change your link image, link title and link description for this network? Click here.', 'blog2social') . '</a> ';
                }
                $edit .= '<input type="text" readonly class="form-control fb-url-title b2s-post-item-details-preview-title change-meta-tag og_title" placeholder="' . __('OG Meta title', 'blog2social') . '" name="b2s[' . $networkAuthId . '][og_title]"  data-meta="og_title" data-meta-type="og" data-network-auth-id="' . $networkAuthId . '" value="' . (isset($meta['title']) && !empty($meta['title']) ? $meta['title'] : '') . '" />';
                $edit .= '<input type="text" readonly class="form-control fb-url-desc b2s-post-item-details-preview-desc change-meta-tag og_desc" placeholder="' . __('OG Meta description', 'blog2social') . '" name="b2s[' . $networkAuthId . '][og_desc]" data-meta="og_desc"  data-meta-type="og" data-network-auth-id="' . $networkAuthId . '" value="' . (isset($meta['description']) && !empty($meta['description']) ? $meta['description'] : '' ) . '" />';
                $edit .= $this->getUrlHtml($networkId, $networkType, $networkAuthId, $limit, $limitValue, true, 'fb-url-input', true, $imageUrl);
                $edit .= '</div>';
                $edit .= '</div>';
            }

            if ($networkId == 2) {
                $edit = '<textarea class="form-control tw-textarea-input b2s-post-item-details-item-message-input ' . (in_array($networkId, $this->allowHtml) ? 'b2s-post-item-details-item-message-input-allow-html' : '') . '"data-network-count="-1" data-network-text-limit="' . $limitValue . '" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" placeholder="' . __('Write something about your post...', 'blog2social') . '" unique="currency" name="b2s[' . $networkAuthId . '][content]" ' . $isRequiredTextarea . ' ' . $textareaOnKeyUp . '>' . $message . '</textarea>';
                $edit .= '<div class="row">';
                $edit .= '<div class="' . ((isset($this->viewMode) && $this->viewMode == 'modal') ? 'col-xs-12' : 'col-xs-12 col-sm-5 col-lg-3') . '">';
                $edit .= '<button class="btn btn-primary btn-circle b2s-image-remove-btn" data-network-count="-1" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" ' . ($imageUrl ? '' : 'style="display:none"') . '><i class="glyphicon glyphicon-trash"></i></button>';
                $edit .= '<img src="' . (isset($meta['image']) && !empty($meta['image']) ? $meta['image'] : $this->defaultImage) . '" class="tw-url-image b2s-post-item-details-url-image center-block img-responsive" data-network-count="-1" data-network-id="' . $networkId . '" data-network-image-change="1" data-network-auth-id="' . $networkAuthId . '">';
                $edit .= '<input type="hidden" class="b2s-image-url-hidden-field form-control" data-network-count="-1" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" value="' . ($imageUrl ? $imageUrl : "") . '" name="b2s[' . $networkAuthId . '][image_url]">';
                $edit .= '<div class="clearfix"></div>';
                $edit .= '<button class="btn btn-link btn-xs center-block b2s-select-image-modal-open" data-meta-type="card" data-network-count="-1" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" data-post-id="' . $this->postId . '" data-image-url="' . esc_attr($imageUrl) . '">' . __('Change image', 'blog2social') . '</button>';
                $edit .= '</div>';
                $edit .= '<div class="' . ((isset($this->viewMode) && $this->viewMode == 'modal') ? 'col-xs-12"' : 'col-xs-12 col-sm-7 col-lg-9 b2s-post-original-area" data-network-auth-id="' . $networkAuthId . '"') . '>';
                if (B2S_PLUGIN_USER_VERSION > 0) {
                    $edit .= '<button data-network-auth-id="' . $networkAuthId . '" data-meta-type="card" data-meta-origin="ship" class=" btn btn-xs hidden-xs btn-link b2s-load-info-meta-tag-modal">' . __('Info: Change Card Meta tags image, title and description for this network', 'blog2social') . '</button>';
                } else {
                    $edit .= '<a target="_blank" class="btn-label-premium btn-label-premium-xs b2s-load-info-meta-tag-modal" data-meta-type="card" data-meta-origin="ship" href="#"><span class="label label-success">PREMIUM</span></a>';
                    $edit .= '<a href="#" class="btn btn-link btn-xs b2s-load-info-meta-tag-modal" data-meta-type="card" data-meta-origin="ship">' . __('You want to change your link image, link title and link description for this network? Click here.', 'blog2social') . '</a> ';
                }
                $edit .= '<input type="text" readonly class="form-control tw-url-title b2s-post-item-details-preview-title change-meta-tag card_title"  placeholder="' . __('Card Meta title', 'blog2social') . '" name="b2s[' . $networkAuthId . '][card_title]"  data-meta="card_title" data-meta-type="card" data-network-auth-id="' . $networkAuthId . '" value="' . (isset($meta['title']) && !empty($meta['title']) ? $meta['title'] : '') . '" />';
                $edit .= '<input type="text" readonly class="form-control tw-url-desc b2s-post-item-details-preview-desc change-meta-tag card_desc"  placeholder="' . __('Card Meta description', 'blog2social') . '" name="b2s[' . $networkAuthId . '][card_desc]"  data-meta="card_desc" data-meta-type="card" data-network-auth-id="' . $networkAuthId . '" value="' . (isset($meta['description']) && !empty($meta['description']) ? $meta['description'] : '' ) . '" />';
                $edit .= $this->getUrlHtml($networkId, $networkType, $networkAuthId, $limit, $limitValue, true, 'tw-url-input', true);
                $edit .= '</div>';
                $edit .= '</div>';
            }

            if ($networkId == 3) {
                $edit = '<textarea class="form-control linkedin-textarea-input b2s-post-item-details-item-message-input ' . (in_array($networkId, $this->allowHtml) ? 'b2s-post-item-details-item-message-input-allow-html' : '') . '"data-network-count="-1" data-network-text-limit="' . $limitValue . '" data-network-auth-id="' . $networkAuthId . '" placeholder="' . __('Write something about your post...', 'blog2social') . '" name="b2s[' . $networkAuthId . '][content]" ' . $isRequiredTextarea . ' ' . $textareaOnKeyUp . '>' . $message . '</textarea>';
                $edit .= '<div class="row">';
                $edit .= '<div class="' . ((isset($this->viewMode) && $this->viewMode == 'modal') ? 'col-xs-12' : 'col-xs-12 col-sm-5 col-lg-3') . '" >';
                $edit .= '<button class="btn btn-primary btn-circle b2s-image-remove-btn" data-network-count="-1" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" ' . ($imageUrl ? '' : 'style="display:none"') . '><i class="glyphicon glyphicon-trash"></i></button>';
                $edit .= '<img src="' . (isset($meta['image']) && !empty($meta['image']) ? $meta['image'] : $this->defaultImage) . '" class="linkedin-url-image b2s-post-item-details-url-image center-block img-responsive" data-network-count="-1" data-network-id="' . $networkId . '" data-network-image-change="1" data-network-auth-id="' . $networkAuthId . '">';
                $edit .= '<input type="hidden" class="b2s-image-url-hidden-field form-control" data-network-count="-1" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" value="' . ($imageUrl ? $imageUrl : "") . '" name="b2s[' . $networkAuthId . '][image_url]">';
                $edit .= '<div class="clearfix"></div>';
                $edit .= '<button class="btn btn-link btn-xs center-block b2s-select-image-modal-open" data-network-count="-1" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" data-post-id="' . $this->postId . '" data-image-url="' . esc_attr($imageUrl) . '">' . __('Change image', 'blog2social') . '</button>';
                $edit .= '</div>';
                $edit .= '<div class="' . ((isset($this->viewMode) && $this->viewMode == 'modal') ? 'col-xs-12"' : 'col-xs-12 col-sm-7 col-lg-9 b2s-post-original-area" data-network-auth-id="' . $networkAuthId . '"') . '>';
                $edit .= '<p class="linkedin-url-title b2s-post-item-details-preview-title hidden-xs" data-network-auth-id="' . $networkAuthId . '">' . (isset($meta['title']) && !empty($meta['title']) ? $meta['title'] : '') . '</p>';
                $edit .= $this->getUrlHtml($networkId, $networkType, $networkAuthId, $limit, $limitValue, true, 'linkedin-url-input', true);
                $edit .= '<p class="linkedin-url-desc b2s-post-item-details-preview-desc hidden-xs" data-network-auth-id="' . $networkAuthId . '">' . (isset($meta['description']) && !empty($meta['description']) ? $meta['description'] : '' ) . '</p>';
                $edit .= '</div>';
                $edit .= '</div>';
            }

            if (($networkId == 8 || $networkId == 19) && $networkType == 0) {
                $edit = '<textarea class="form-control xing-textarea-input b2s-post-item-details-item-message-input ' . (in_array($networkId, $this->allowHtml) ? 'b2s-post-item-details-item-message-input-allow-html' : '') . '"data-network-count="-1" data-network-text-limit="' . $limitValue . '" data-network-auth-id="' . $networkAuthId . '" placeholder="' . __('Write something about your post...', 'blog2social') . '" name="b2s[' . $networkAuthId . '][content]" ' . $isRequiredTextarea . ' ' . $textareaOnKeyUp . '>' . $message . '</textarea>';
                $edit .= '<div class="row">';
                $edit .= '<div class="' . ((isset($this->viewMode) && $this->viewMode == 'modal') ? 'col-xs-12' : 'col-xs-12 col-sm-5 col-lg-3') . '">';
                $edit .= '<img src="' . (isset($meta['image']) && !empty($meta['image']) ? $meta['image'] : $this->defaultImage) . '" class="xing-url-image b2s-post-item-details-url-image center-block img-responsive" data-network-count="-1" data-network-id="' . $networkId . '" data-network-image-change="0" data-network-auth-id="' . $networkAuthId . '">';
                $edit .= '<input type="hidden" class="b2s-image-url-hidden-field form-control" data-network-count="-1" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" value="' . ($imageUrl ? $imageUrl : "") . '" name="b2s[' . $networkAuthId . '][image_url]">';
                $edit .= '<div class="clearfix"></div>';
                $edit .= '</div>';
                $edit .= '<div class="' . ((isset($this->viewMode) && $this->viewMode == 'modal') ? 'col-xs-12"' : 'col-xs-12 col-sm-7 col-lg-9 b2s-post-original-area" data-network-auth-id="' . $networkAuthId . '"') . '>';
                $edit .= '<p class="xing-url-title b2s-post-item-details-preview-title hidden-xs" data-network-auth-id="' . $networkAuthId . '">' . (isset($meta['title']) && !empty($meta['title']) ? $meta['title'] : '') . '</p>';
                $edit .= '<span class="xing-url-desc b2s-post-item-details-preview-desc hidden-xs" data-network-auth-id="' . $networkAuthId . '">' . (isset($meta['description']) && !empty($meta['description']) ? $meta['description'] : '' ) . '</span>';
                $edit .= $this->getUrlHtml($networkId, $networkType, $networkAuthId, $limit, $limitValue, true, 'xing-url-input', true);
                $edit .= '</div>';
                $edit .= '</div>';
            }
        } else {
            $edit = '<textarea class="form-control b2s-post-item-details-item-message-input ' . (in_array($networkId, $this->allowHtml) ? 'b2s-post-item-details-item-message-input-allow-html' : '') . '"data-network-count="-1" data-network-id="' . $networkId . '" data-network-text-limit="' . $limitValue . '" data-network-auth-id="' . $networkAuthId . '" placeholder="' . __('Write something about your post...', 'blog2social') . '"  name="b2s[' . $networkAuthId . '][content]" ' . $isRequiredTextarea . ' ' . $textareaOnKeyUp . '>' . $message . '</textarea>';

//EDIT Function - Calendar
            $meta = $this->hook_meta(array());
            $imageUrl = $imageUrl ? $imageUrl : (isset($meta['image']) ? $meta['image'] : null);
            $edit .= $this->getUrlHtml($networkId, $networkType, $networkAuthId, $limit, $limitValue, false, '', false, $imageUrl);
            if ($networkId == 14) {  //FeatureImage Network Torial (Portfolio)
                $edit .= '<input type="hidden" class="b2s-image-url-hidden-field form-control" data-network-id="' . $networkId . '" data-network-count="-1" data-network-auth-id="' . $networkAuthId . '" value="' . ($imageUrl ? $imageUrl : "") . '" name="b2s[' . $networkAuthId . '][image_url]">';
            }
        }
        return $edit;
    }

    public function getCustomEditSchedArea($schedCount = 0, $networkId, $networkAuthId, $networkType, $message, $isRequiredTextarea, $textareaOnKeyUp, $limit, $limitValue, $infoArea, $imageUrl = null) {
        if ($networkId == 1 || ($networkId == 8 && $networkType == 0) || ($networkId == 19 && $networkType == 0) || $networkId == 3 || $networkId == 2) {
            if ($networkId == 1) {
                $edit = '<div class="row"><br>';
                $edit .= '<div class="col-xs-12 col-sm-5 col-lg-3">';
                $edit .= '<button class="btn btn-primary btn-circle b2s-image-remove-btn" data-network-id="' . $networkId . '" data-network-count="' . $schedCount . '" data-network-auth-id="' . $networkAuthId . '" ' . ($imageUrl ? '' : 'style="display:none"') . '><i class="glyphicon glyphicon-trash"></i></button>';
                $edit .= '<img src="' . $this->defaultImage . '" class="fb-url-image b2s-post-item-details-url-image center-block img-responsive" data-network-count="' . $schedCount . '" data-network-id="' . $networkId . '" data-network-image-change="1" data-network-auth-id="' . $networkAuthId . '">';
                $edit .= '<input type="hidden" class="b2s-image-url-hidden-field form-control"  data-network-count="' . $schedCount . '"  data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" value="' . ($imageUrl ? $imageUrl : "") . '" name="b2s[' . $networkAuthId . '][sched_image_url][' . $schedCount . ']">';
                $edit .= '<div class="clearfix"></div>';
                $edit .= '<button class="btn btn-link btn-xs center-block b2s-select-image-modal-open" data-meta-type="og" data-network-count="' . $schedCount . '" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" data-post-id="' . $this->postId . '" data-image-url="' . esc_attr($imageUrl) . '">' . __('Change image', 'blog2social') . '</button>';
                $edit .= '</div>';
                $edit .= '<div class="col-xs-12 col-sm-7 col-lg-9">';
                $edit .= $infoArea;
                $edit .= '<textarea disabled="disabled" class="form-control fb-textarea-input b2s-post-item-sched-customize-text b2s-post-item-details-item-message-input ' . (in_array($networkId, $this->allowHtml) ? 'b2s-post-item-details-item-message-input-allow-html' : '') . '" data-network-count="' . $schedCount . '" data-network-text-limit="' . $limitValue . '" data-network-auth-id="' . $networkAuthId . '" placeholder="' . __('Write something about your post...', 'blog2social') . '" name="b2s[' . $networkAuthId . '][sched_content][' . $schedCount . ']" ' . $isRequiredTextarea . ' ' . $textareaOnKeyUp . '>' . $message . '</textarea>';
                $edit .= '</div>';
                $edit .= '</div>';
            }

            if ($networkId == 2) {
                $edit = '<div class="row"><br>';
                //TOS Twitter 032018
                $edit .='<div class="b2s-unique-content col-xs-12" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '"><div class="clearfix"></div><div class="alert b2s-unique-content-alert alert-danger">' . __('Please keep in mind that according to Twitter’s new TOS, users are no longer allowed to post identical or substantially similar content to multiple accounts or multiple duplicate updates on one account.', 'blog2social') . '<br><strong>' . __('Violating these rules can result in Twitter suspending your account. Always vary your Tweets with different comments, hashtags or handles to prevent duplicate posts.', 'blog2social') . '</strong> <a href="' . B2S_Tools::getSupportLink('network_tos_blog_032018') . '" target="_blank">' . __('Learn more about this', 'blog2social') . '</a></div><br></div>';
                $edit .= '<div class="col-xs-12 col-sm-5 col-lg-3">';
                $edit .= '<button class="btn btn-primary btn-circle b2s-image-remove-btn" data-network-id="' . $networkId . '" data-network-count="' . $schedCount . '" data-network-auth-id="' . $networkAuthId . '" ' . ($imageUrl ? '' : 'style="display:none"') . '><i class="glyphicon glyphicon-trash"></i></button>';
                $edit .= '<img src="' . $this->defaultImage . '" class="tw-url-image b2s-post-item-details-url-image center-block img-responsive" data-network-count="' . $schedCount . '" data-network-id="' . $networkId . '" data-network-image-change="1" data-network-auth-id="' . $networkAuthId . '">';
                $edit .= '<input type="hidden" class="b2s-image-url-hidden-field form-control" data-network-count="' . $schedCount . '" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" value="' . ($imageUrl ? $imageUrl : "") . '" name="b2s[' . $networkAuthId . '][sched_image_url][' . $schedCount . ']">';
                $edit .= '<div class="clearfix"></div>';
                $edit .= '<button class="btn btn-link btn-xs center-block b2s-select-image-modal-open" data-meta-type="card" data-network-count="' . $schedCount . '" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" data-post-id="' . $this->postId . '" data-image-url="' . esc_attr($imageUrl) . '">' . __('Change image', 'blog2social') . '</button>';
                $edit .= '</div>';
                $edit .= '<div class="col-xs-12 col-sm-7 col-lg-9">';
                $edit .= $infoArea;
                $edit .= '<textarea disabled="disabled" class="form-control tw-textarea-input b2s-post-item-sched-customize-text b2s-post-item-details-item-message-input ' . (in_array($networkId, $this->allowHtml) ? 'b2s-post-item-details-item-message-input-allow-html' : '') . '" data-network-count="' . $schedCount . '" data-network-text-limit="' . $limitValue . '" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" placeholder="' . __('Write something about your post...', 'blog2social') . '" unique="currency" name="b2s[' . $networkAuthId . '][sched_content][' . $schedCount . ']" ' . $isRequiredTextarea . ' ' . $textareaOnKeyUp . '></textarea>';
                $edit .= '</div>';
                $edit .= '</div>';
            }

            if ($networkId == 3) {
                $edit = '<div class="row"><br>';
                $edit .= '<div class="col-xs-12 col-sm-5 col-lg-3">';
                $edit .= '<button class="btn btn-primary btn-circle b2s-image-remove-btn" data-network-count="' . $schedCount . '" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" ' . ($imageUrl ? '' : 'style="display:none"') . '><i class="glyphicon glyphicon-trash"></i></button>';
                $edit .= '<img src="' . $this->defaultImage . '" class="linkedin-url-image b2s-post-item-details-url-image center-block img-responsive"  data-network-count="' . $schedCount . '"  data-network-id="' . $networkId . '" data-network-image-change="1" data-network-auth-id="' . $networkAuthId . '">';
                $edit .= '<input type="hidden" class="b2s-image-url-hidden-field form-control" data-network-count="' . $schedCount . '" data-network-count="' . $schedCount . '" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" value="' . ($imageUrl ? $imageUrl : "") . '" name="b2s[' . $networkAuthId . '][sched_image_url][' . $schedCount . ']">';
                $edit .= '<div class="clearfix"></div>';
                $edit .= '<button class="btn btn-link btn-xs center-block b2s-select-image-modal-open" data-network-count="' . $schedCount . '" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" data-post-id="' . $this->postId . '" data-image-url="' . esc_attr($imageUrl) . '">' . __('Change image', 'blog2social') . '</button>';
                $edit .= '</div>';
                $edit .= '<div class="col-xs-12 col-sm-7 col-lg-9">';
                $edit .= $infoArea;
                $edit .= '<textarea disabled="disabled" class="form-control linkedin-textarea-input b2s-post-item-sched-customize-text b2s-post-item-details-item-message-input ' . (in_array($networkId, $this->allowHtml) ? 'b2s-post-item-details-item-message-input-allow-html' : '') . '" data-network-count="' . $schedCount . '" data-network-text-limit="' . $limitValue . '" data-network-auth-id="' . $networkAuthId . '" placeholder="' . __('Write something about your post...', 'blog2social') . '" name="b2s[' . $networkAuthId . '][sched_content][' . $schedCount . ']" ' . $isRequiredTextarea . ' ' . $textareaOnKeyUp . '>' . $message . '</textarea>';
                $edit .= '</div>';
                $edit .= '</div>';
            }

            if (($networkId == 8 || $networkId == 19) && $networkType == 0) {
                $edit = '<div class="row"><br>';
                $edit .= '<div class="col-xs-12 col-sm-5 col-lg-3">';
                $edit .= '<img src="' . $this->defaultImage . '" class="xing-url-image b2s-post-item-details-url-image center-block img-responsive" data-network-count="' . $schedCount . '" data-network-id="' . $networkId . '" data-network-image-change="0" data-network-auth-id="' . $networkAuthId . '">';
                $edit .= '<input type="hidden" class="b2s-image-url-hidden-field form-control" data-network-count="' . $schedCount . '" data-network-count="' . $schedCount . '" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" value="' . ($imageUrl ? $imageUrl : "") . '" name="b2s[' . $networkAuthId . '][sched_image_url][' . $schedCount . ']">';
                $edit .= '</div>';
                $edit .= '<div class="col-xs-12 col-sm-7 col-lg-9">';
                $edit .= $infoArea;
                $edit .= '<textarea disabled="disabled" class="form-control xing-textarea-input b2s-post-item-sched-customize-text b2s-post-item-details-item-message-input ' . (in_array($networkId, $this->allowHtml) ? 'b2s-post-item-details-item-message-input-allow-html' : '') . '" data-network-count="' . $schedCount . '" data-network-text-limit="' . $limitValue . '" data-network-auth-id="' . $networkAuthId . '" placeholder="' . __('Write something about your post...', 'blog2social') . '" name="b2s[' . $networkAuthId . '][sched_content][' . $schedCount . ']" ' . $isRequiredTextarea . ' ' . $textareaOnKeyUp . '>' . $message . '</textarea>';
                $edit .= '</div>';
                $edit .= '</div>';
            }
        } else {
            $edit = '<div class="row"><br>';
            if ((in_array($networkId, $this->showImageAreaProfile) && $networkType == 0) || (in_array($networkId, $this->showImageAreaPage) && $networkType == 1) || (in_array($networkId, $this->showImageAreaGroup) && $networkType == 2)) {
                $edit .= '<div class="col-xs-12 col-sm-5 col-lg-3">';
                $edit .= '<button class="btn btn-primary btn-circle b2s-image-remove-btn" data-network-count="' . $schedCount . '" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" ' . ($imageUrl ? '' : 'style="display:none"') . '><i class="glyphicon glyphicon-trash"></i></button>';
                $edit .= '<img src="' . $this->defaultImage . '" class="b2s-post-item-details-url-image center-block img-responsive b2s-image-border" data-network-id="' . $networkId . '" data-network-count="' . $schedCount . '" data-network-image-change="1" data-network-auth-id="' . $networkAuthId . '">';
                $edit .= '<input type="hidden" class="b2s-image-url-hidden-field form-control" data-network-count="' . $schedCount . '" data-network-count="' . $schedCount . '" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" value="' . ($imageUrl ? $imageUrl : "") . '" name="b2s[' . $networkAuthId . '][sched_image_url][' . $schedCount . ']">';
                $edit .= '<div class="clearfix"></div>';
                $edit .= '<button class="btn btn-link btn-xs center-block b2s-select-image-modal-open" data-network-id="' . $networkId . '" data-network-count="' . $schedCount . '" data-network-auth-id="' . $networkAuthId . '" data-post-id="' . $this->postId . '" data-image-url="' . esc_attr($imageUrl) . '">' . __('Change image', 'blog2social') . '</button>';
                $edit .= '</div>';
                $edit .= '<div class="col-xs-12 col-sm-7 col-lg-9">';
                $edit .= $infoArea;
                $edit .= '<textarea disabled="disabled" class="form-control b2s-post-item-sched-customize-text b2s-post-item-details-item-message-input ' . (in_array($networkId, $this->allowHtml) ? 'b2s-post-item-details-item-message-input-allow-html' : '') . '" data-network-count="' . $schedCount . '" data-network-text-limit="' . $limitValue . '" data-network-id="' . $networkId . '" data-network-count="' . $schedCount . '" data-network-auth-id="' . $networkAuthId . '" placeholder="' . __('Write something about your post...', 'blog2social') . '"  name="b2s[' . $networkAuthId . '][sched_content][' . $schedCount . ']" ' . $isRequiredTextarea . ' ' . $textareaOnKeyUp . '>' . $message . '</textarea>';
                $edit .= '</div>';
            } else {
                $edit .= '<div class="col-xs-12">';
                $edit .= $infoArea;
                $edit .= '<textarea disabled="disabled" class="form-control b2s-post-item-sched-customize-text b2s-post-item-details-item-message-input ' . (in_array($networkId, $this->allowHtml) ? 'b2s-post-item-details-item-message-input-allow-html' : '') . '" data-network-count="' . $schedCount . '" data-network-text-limit="' . $limitValue . '" data-network-count="' . $schedCount . '" data-network-auth-id="' . $networkAuthId . '" placeholder="' . __('Write something about your post...', 'blog2social') . '" name="b2s[' . $networkAuthId . '][sched_content][' . $schedCount . '] ' . $isRequiredTextarea . ' ' . $textareaOnKeyUp . '>' . $message . '</textarea>';
                $edit .= '</div>';
            }
            $edit .= '</div>';
        }
        return $edit;
    }

    private function getUrlHtml($networkId, $networkType, $networkAuthId, $limit, $limitValue, $hideInfo = false, $class = '', $refeshBtn = false, $imageUrl = null) {
        if (in_array($networkId, $this->allowEditUrl)) {
            $urlLimit = ($limit !== false) ? ' onkeyup="networkLimitAll(\'' . $networkAuthId . '\',\'' . $networkId . '\',\'' . $limitValue . '\');"' : 'onkeyup="networkCount(\'' . $networkAuthId . '\');"';
            $isRequiredClass = (in_array($networkId, $this->requiredUrl)) ? 'required_network_url' : '';
            $isRequiredText = (!empty($isRequiredClass)) ? '<small>(' . __('required', 'blog2social') . ')</small>' : '';

            $url = '';
            if ((in_array($networkId, $this->showImageAreaProfile) && $networkType == 0) || (in_array($networkId, $this->showImageAreaPage) && $networkType == 1) || (in_array($networkId, $this->showImageAreaGroup) && $networkType == 2)) {
                $url .= '<br>';
                $url .= '<div class="row">';
                $url .= '<div class="' . ((isset($this->viewMode) && $this->viewMode == 'modal') ? 'col-xs-12' : 'col-xs-12 col-sm-5 col-lg-3') . '">';
                $url .= '<div>';
                $url .= '<button class="btn btn-primary btn-circle b2s-image-remove-btn" data-network-count="-1" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" ' . ($imageUrl ? '' : 'style="display:none"') . '><i class="glyphicon glyphicon-trash"></i></button>';
                $url .= '<img src="' . (($imageUrl != null) ? $imageUrl : $this->defaultImage) . '" class="b2s-post-item-details-url-image center-block img-responsive b2s-image-border" data-network-count="-1" data-network-id="' . $networkId . '" data-network-image-change="1" data-network-auth-id="' . $networkAuthId . '">';
                $url .= '<input type="hidden" class="b2s-image-url-hidden-field form-control" data-network-count="-1" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" value="' . (($imageUrl != null) ? $imageUrl : "") . '" name="b2s[' . $networkAuthId . '][image_url]">';
                $url .= '</div>';
                $url .= '<div class="clearfix"></div>';
                $url .= '<button class="btn btn-link btn-xs center-block b2s-select-image-modal-open" data-network-count="-1" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" data-post-id="' . $this->postId . '" data-image-url="' . esc_attr($imageUrl) . '">' . __('Change image', 'blog2social') . '</button></div>';
                $url .= '<div class="' . ((isset($this->viewMode) && $this->viewMode == 'modal') ? 'col-xs-12"' : 'col-xs-12 col-sm-7 col-lg-9 b2s-post-original-area" data-network-auth-id="' . $networkAuthId . '"') . '>';
            }

            $url .= (!$hideInfo) ? '<div class="b2s-post-item-details-url-title hidden-xs">Link ' . $isRequiredText . '</div>' : '';

            if ($refeshBtn && trim(strtolower($this->postStatus)) == 'publish') {
                $url .= '<div class="input-group"><input class="form-control ' . $class . ' b2s-post-item-details-item-url-input ' . $isRequiredClass . ' complete_network_url" name="b2s[' . $networkAuthId . '][url]" ' . $urlLimit . ' placeholder="' . __('Link', 'blog2social') . '" data-network-count="-1" data-network-auth-id="' . $networkAuthId . '" value="' . $this->postUrl . '" name="b2s[' . $networkAuthId . '][url]"/><span class="input-group-addon"><span class="glyphicon glyphicon-refresh b2s-post-item-details-preview-url-reload" data-network-auth-id="' . $networkAuthId . '" data-network-id="' . $networkId . '" aria-hidden="true"></span></span></div>';
            } else {
                $url .= '<input class="form-control ' . $class . ' b2s-post-item-details-item-url-input ' . $isRequiredClass . ' complete_network_url" name="b2s[' . $networkAuthId . '][url]" ' . $urlLimit . ' placeholder="' . __('Link', 'blog2social') . '" data-network-count="-1" data-network-auth-id="' . $networkAuthId . '" value="' . $this->postUrl . '" name="b2s[' . $networkAuthId . '][url]"/>';
            }
            if ((in_array($networkId, $this->showImageAreaProfile) && $networkType == 0) || (in_array($networkId, $this->showImageAreaPage) && $networkType == 1) || (in_array($networkId, $this->showImageAreaGroup) && $networkType == 2)) {
                $url .= '</div>';
                $url .= '</div>';
                $url .= '<div class="col-xs-12"><br></div>';
            }
        } else {
            $url = '<input type="hidden" name="b2s[' . $networkAuthId . '][url]" value="' . $this->postUrl . '">';
        }


        return $url;
    }

    protected function hook_message($message) {
        return $message;
    }

    protected function hook_meta(array $meta) {
        return $meta;
    }

    protected function hook_sched_data(array $schedData) {
        return $schedData;
    }

    private function getHashTagsString($add = "\n\n") {
        $hashTagsData = get_the_tags($this->postId);
        $hashTags = '';
        if (is_array($hashTagsData) && !empty($hashTagsData)) {
            foreach ($hashTagsData as $tag) {
                $hashTags .= ' #' . str_replace(array(" ", "-"), "", $tag->name);
            }
        }
        return (!empty($hashTags) ? (!empty($add) ? $add . $hashTags : $hashTags) : '');
    }

    private function getBoardHtml($networkAuthId, $networkId) {
        $board = '';
        $result = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, array('action' => 'getBoards', 'token' => B2S_PLUGIN_TOKEN, 'networkAuthId' => $networkAuthId, 'selBoard' => $this->selBoard, 'networkId' => $networkId)));
        if (is_object($result) && !empty($result) && isset($result->data) && !empty($result->data) && isset($result->result) && (int) $result->result == 1) {
            $board = '<select class="form-control b2s-select-area" name="b2s[' . $networkAuthId . '][board]">';
            $board .= $result->data;
            $board .= '</select>';
        }
        return $board;
    }

    private function getGroupsHtml($networkAuthId, $networkId) {
        $group = '';
        $result = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, array('action' => 'getGroups', 'token' => B2S_PLUGIN_TOKEN, 'networkAuthId' => $networkAuthId, 'selGroup' => $this->selGroup, 'networkId' => $networkId, 'lang' => B2S_LANGUAGE)));
        $changeDisplayName = in_array($networkId, $this->changeDisplayName) ? 'true' : 'false';
        if (is_object($result) && !empty($result) && isset($result->data) && !empty($result->data) && isset($result->result) && (int) $result->result == 1) {
            $group = '<select class="form-control b2s-select-area b2s-post-item-details-item-group-select" data-change-network-display-name="' . $changeDisplayName . '" data-network-auth-id="' . $networkAuthId . '" data-network-id="' . $networkId . '" name="b2s[' . $networkAuthId . '][group]">';
            $group .= $result->data;
            $group .= '</select>';
        }
        return $group;
    }

    private function getMarketplaceAreaHtml($networkAuthId = 0, $networkId = 0, $networkType = 0, $networkKind = 0) {
        $marketplace = '<div class="marketplace_area" data-network-id="' . $networkId . '" data-network-type="' . $networkType . '" data-network-auth-id="' . $networkAuthId . '" style="display:' . (($networkKind == 3) ? 'block' : 'none') . ';">';
        $marketplace .='<input type="radio" id="marketplace_category_1" class="form-control marketplace_category" ' . (($this->selMarketplaceCategory == 1) ? 'checked=""' : '') . ' name="b2s[' . $networkAuthId . '][marketplace_category]" value="1"> <label class="" for="marketplace_category_1">' . __('Jobs & Projects', 'blog2social') . '</label> ';
        $marketplace .='<input type="radio" id="marketplace_category_2" class="form-control marketplace_category" ' . (($this->selMarketplaceCategory == 2) ? 'checked=""' : '') . ' name="b2s[' . $networkAuthId . '][marketplace_category]" value="2"> <label class="" for="marketplace_category_2">' . __('Events', 'blog2social') . '</label> ';
        $marketplace .='<input type="radio" id="marketplace_category_3" class="form-control marketplace_category" ' . (($this->selMarketplaceCategory == 3) ? 'checked=""' : '') . '  name="b2s[' . $networkAuthId . '][marketplace_category]" value="3"> <label class="" for="marketplace_category_3">' . __('Classified Ads', 'blog2social') . '</label> ';
        $marketplace .='<div class="clearfix"></div>';
        $marketplace .='<input type="radio" id="marketplace_type_1" class="form-control marketplace_type" ' . (($this->selMarketplaceType == 1) ? 'checked=""' : '') . ' name="b2s[' . $networkAuthId . '][marketplace_type]" value="1"> <label class="" for="marketplace_type_1">' . __('Offer', 'blog2social') . '</label> ';
        $marketplace .='<input type="radio" id="marketplace_type_2" class="form-control marketplace_type" ' . (($this->selMarketplaceType == 2) ? 'checked=""' : '') . ' name="b2s[' . $networkAuthId . '][marketplace_type]" value="2"> <label class="" for="marketplace_type_2">' . __('Request', 'blog2social') . '</label>';
        $marketplace .='<div class="clearfix"></div><br/>';
        $marketplace .='</div>';
        return $marketplace;

        //<input type="radio" id="type[0]-1-2" checked="" name="b2s['.$networkAuthId.'][marketplace_type]" value="1"> <label class="" for="type[0]-1-2">Profil</label>
    }

    private function getTitleHtml($networkId = 0, $networkdAutId = 0, $networkKind = 0, $networkType = 0, $title = '') {
        $title = in_array($networkId, $this->allowNoEmoji) ? B2S_Util::remove4byte(B2S_Util::getTitleByLanguage($title, $this->userLang)) : B2S_Util::getTitleByLanguage($title, $this->userLang);
        $maxLength = ($networkType == 1 && isset($this->limitCharacterTitlePage[$networkId][$networkKind])) ? (int) $this->limitCharacterTitlePage[$networkId][$networkKind] : 254;
        return '<input type="text" name="b2s[' . $networkdAutId . '][custom_title]" class="form-control b2s-post-item-details-item-title-input" data-network-auth-id="' . $networkdAutId . '" placeholder="' . __('The Headline...', 'blog2social') . '" required="required" maxlength="' . $maxLength . '" value="' . $title . '" />';
    }

    private function getTagsHtml($networkAuthId, $allowTags = true) {
        $tags = '<div class="b2s-post-item-details-tag-area">';
        $tags .= '<div class="b2s-post-item-details-tag-title"> ' . __('Hashtags', 'blog2social') . ' </div>';
        $tags .= '<div class="b2s-post-item-details-tag-input form-inline">';
        $posttags = get_the_tags($this->postId);
        $countTags = 0;
        if ($posttags && $allowTags) {
            foreach ($posttags as $tag) {
                $name = str_replace(" ", "", $tag->name);
                $countTags += 1;
                $tags .= '<input class="form-control b2s-post-item-details-tag-input-elem" name="b2s[' . $networkAuthId . '][tags][]" data-network-auth-id="' . $networkAuthId . '" value="' . $name . '">';
            }
        } else {
            $tags .= '<input class="form-control b2s-post-item-details-tag-input-elem" name="b2s[' . $networkAuthId . '][tags][]" data-network-auth-id="' . $networkAuthId . '" value="">';
        }
        $showRemoveTagBtn = ($countTags >= 2) ? '' : 'display:none;';
        $tags .= '<div class="form-control b2s-post-item-details-tag-add-div">';
        $tags .= '<span class="remove-tag-btn glyphicon glyphicon-minus" data-network-auth-id="' . $networkAuthId . '" style="' . $showRemoveTagBtn . '" onclick="removeTag(\'' . $networkAuthId . '\');" ></span>';
        $tags .= '<span class="ad-tag-btn glyphicon glyphicon-plus" data-network-auth-id="' . $networkAuthId . '" onclick="addTag(\'' . $networkAuthId . '\');" ></span>';
        $tags .= '</div>';
        $tags .= '</div>';
        $tags .= '</div>';

        return $tags;
    }

    private function getRelayBtnHtml($networkAuthId, $networkId) {
        $relay = '<div class="form-group b2s-post-relay-area-select pull-left"><div class="checkbox checbox-switch switch-success"><label>';
        $relay .='<input type="checkbox" class="b2s-post-item-details-relay" data-user-version="' . B2S_PLUGIN_USER_VERSION . '" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" name="b2s[' . $networkAuthId . '][post_relay]" value="1"/>';
        $relay .='<span></span>';
        $relay .= __('Enable Retweets for all Tweets with the selected profile', 'blog2social') . ' <a href="#" data-toggle="modal" data-target="#b2sInfoPostRelayModal" class="btn-xs hidden-sm">' . __('Info', 'blog2social') . '</a>';
        $relay .=' </label></div></div>';
        return $relay;
    }

    private function getRelayContentHtml($networkAuthId, $networkId) {
        $relay = '';
        if (B2S_PLUGIN_USER_VERSION > 0) {
            $relay .= '<div class="b2s-post-item-relay-area-details">';
            $relay .= '<ul class="list-group b2s-post-item-relay-area-details-ul" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" style="display:none;">';
            $relay .= '<li class="list-group-item">';

            for ($relaycount = 0; $relaycount < $this->setRelayCount; $relaycount++) {

                $relay .= '<div class="form-group b2s-post-item-relay-area-details-row" data-network-count="' . $relaycount . '"  data-network-auth-id="' . $networkAuthId . '" style="display:none">';

                $relay .= $relaycount != 0 ? '<div class="clearfix"></div><hr class="b2s-hr-small">' : '';

                $relay .= '<label class="hidden-sm hidden-xs col-md-2 del-padding-left b2s-post-item-details-relay-area-label-account" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $relaycount . '">' . __('Account', 'blog2social') . '</label>';
                $relay .= '<label class="hidden-sm hidden-xs col-md-2 del-padding-left b2s-post-item-details-relay-area-label-delay" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $relaycount . '">' . __('Delay', 'blog2social') . '</label>';

                $relay .= '<div class="clearfix"></div>';

                $relay .= '<div class="col-xs-12 col-sm-6 col-md-2 del-padding-left b2s-post-item-details-relay-area-div-account" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $relaycount . '">';
                $relay .='<select name="b2s[' . $networkAuthId . '][post_relay_account][' . $relaycount . ']" class="form-control b2s-select b2s-post-item-details-relay-input-account" data-network-count="' . $relaycount . '"  data-network-auth-id="' . $networkAuthId . '" style="display:none;" disabled="disabled">';
                $relay .= '</select></div>';

                $relay .= '<div class="col-xs-12 col-sm-6 col-md-2 del-padding-left b2s-post-item-details-relay-area-div-delay" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $relaycount . '">';
                $relay .='<select name="b2s[' . $networkAuthId . '][post_relay_delay][' . $relaycount . ']" class="form-control b2s-select b2s-post-item-details-relay-input-delay" data-network-count="' . $relaycount . '"  data-network-auth-id="' . $networkAuthId . '" style="display:none;" disabled="disabled">';
                $relay .= '<option value="15">15' . __('min', 'blog2social') . '</option>';
                $relay .= '<option value="30">30' . __('min', 'blog2social') . '</option>';
                $relay .= '<option value="45">45' . __('min', 'blog2social') . '</option>';
                $relay .= '<option value="60">60' . __('min', 'blog2social') . '</option>';
                $relay .= '</select></div>';

                $relay .= '<div class="col-md-2 del-padding-left">';
                $relay .= ( $relaycount >= 1) ? '<button class="btn btn-link b2s-post-item-details-relay-input-hide"  data-network-count="' . $relaycount . '"  data-network-auth-id="' . $networkAuthId . '" data-network-count="1" style="display:none;">-' . __('delete', 'blog2social') . '</button>' : '';
                $relay .= $relaycount < $this->setRelayCount - 1 ? '<button class="btn btn-link b2s-post-item-details-relay-input-add"  data-network-count="' . $relaycount . '"  data-network-auth-id="' . $networkAuthId . '" data-network-count="1" style="display:none;">+' . __('Add Retweet', 'blog2social') . '</button>' : '';
                $relay .= '</div>';
                $relay .= '</div>';
                $relay .= '<div class="clearfix"></div>';
            }
            $relay .= '</li>';
            $relay .= '</ul>';
            $relay .= '</div>';
        }
        return $relay;
    }

    private function getShippingTimeHtml($networkAuthId, $networkTyp, $networkId, $networkType, $message, $isRequiredTextarea, $textareaOnKeyUp, $limit, $limitValue, $imageUrl = null) {

        $isSelectedSched = (B2S_PLUGIN_USER_VERSION > 0 && (trim(strtolower($this->postStatus)) == 'future' || !empty($this->selSchedDate))) ? 'selected="selected"' : '';
        $isSelectedNow = (empty($isSelectedSched)) ? 'selected="selected"' : '';
        $showSchedRegularly = (!($networkTyp == 2 || (in_array($networkId, $this->noScheduleRegularly)) || ($networkTyp == 1 && in_array($networkId, $this->noScheduleRegularlyPage)))) ? true : false;

        $shipping = '<br>';
        $shipping .= '<select name="b2s[' . $networkAuthId . '][releaseSelect]" data-user-version="' . B2S_PLUGIN_USER_VERSION . '" data-network-type="' . $networkTyp . '" data-network-customize-content="' . (in_array($networkId, $this->allowSchedCustomizeContent) ? 1 : 0) . '" data-network-id="' . $networkId . '" data-network-auth-id="' . $networkAuthId . '" class="form-control b2s-select b2s-post-item-details-release-input-date-select ' . (B2S_PLUGIN_USER_VERSION == 0 ? 'b2s-post-item-details-release-input-date-select-reset' : '') . '" >';
        $shipping .= '<option value="0" ' . $isSelectedNow . '>' . __('Share Now', 'blog2social') . '</option>';

        $isPremium = (B2S_PLUGIN_USER_VERSION == 0) ? ' [' . __("PREMIUM", "blog2social") . ']' : '';
        $shipping .= '<option value="1" ' . $isSelectedSched . '>' . __('Schedule for specific dates', 'blog2social') . $isPremium . '</option>';

        $shipping .= ($showSchedRegularly) ? '<option value="2">' . __('Schedule Recurrent Post', 'blog2social') . $isPremium . '</option>' : '';

        $shipping .= '</select>';

        if (B2S_PLUGIN_USER_VERSION > 0) {
            $shipping .= '<div class="b2s-post-item-details-release-area-details">';
//TOS Twitter 032018
            $shipping .= '<div class="b2s-network-tos-sched-warning" data-network-auth-id="' . $networkAuthId . '" style="display: none;"><div class="clearfix"></div><div class="alert b2s-network-tos-sched-alert alert-warning">' . __('Please keep in mind that according to Twitter’s new TOS, users are no longer allowed to post identical or substantially similar content to multiple accounts or multiple duplicate updates on one account.', 'blog2social') . '<br><strong>' . __('Violating these rules can result in Twitter suspending your account. Always vary your Tweets with different comments, hashtags or handles to prevent duplicate posts.', 'blog2social') . '</strong> <a href="' . B2S_Tools::getSupportLink('network_tos_blog_032018') . '" target="_blank">' . __('Learn more about this', 'blog2social') . '</a></div></div>';
            $shipping .= '<ul class="list-group b2s-post-item-details-release-area-details-ul" data-network-auth-id="' . $networkAuthId . '" style="display:none;">';
            $shipping .= '<li class="list-group-item">';

//Sched post
            $time = time();
            if (trim(strtolower($this->postStatus)) == 'future') {
                $time = strtotime($this->postData->post_date);
            }
//Routing form calendar
            if (!empty($this->selSchedDate)) {
                $time = strtotime($this->selSchedDate);
            }

            if (date('H') == '23' && date('i') >= 30) {
                $time = strtotime('+ 1 days');
            }

            $currentDate = (strtolower(substr(get_locale(), 0, 2)) == 'de') ? date('d.m.Y', $time) : date('Y-m-d', $time);
            $currentDay = date('d', $time);

            $maxSchedCount = ($networkId == 18) ? 1 : $this->maxSchedCount;
            for ($schedcount = 0; $schedcount < $maxSchedCount; $schedcount++) {
                $shipping .= '<div class="form-group b2s-post-item-details-release-area-details-row" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '" style="display:none">';

                $shipping .= $schedcount != 0 ? '<div class="clearfix"></div><hr class="b2s-hr-small">' : '';

                //deprecated Network
                if ($networkId == 8) {
                    $shipping.='<div class="network-tos-deprecated-warning alert alert-danger"  style="display: none;" data-network-id="' . $networkId . '" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '">' . __("Please note: Your account is connected via an old XING API that is no longer supported by XING after March 31. Please connect your XING profile, as well as your XING company pages (Employer branding profiles) and business pages with the new XING interface in the Blog2Social network settings. To do this, go to the Blog2Social Networks section and connect your XING accounts with the new XING.", "blog2social") . ' <a href="' . B2S_Tools::getSupportLink('network_tos_blog_032019') . '" target="_blank">' . __('Learn more', 'blog2social') . '</a></div>';
                }
                //deprecated Network
                if ($networkId == 10) {
                    $shipping.='<div class="network-tos-deprecated-warning alert alert-danger"  style="display: none;" data-network-id="' . $networkId . '" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '">' . __("Please note: Google will shut down Google+ for all private accounts (profiles, pages, groups) on 2nd April 2019. You can find further information and the next steps, including how to download your photos and other content here:", "blog2social") . ' <a href="https://support.google.com/plus/answer/9195133" target="_blank">https://support.google.com/plus/answer/9195133</a></div>';
                }

                $shipping .= '<label class="hidden-sm hidden-xs col-md-2 del-padding-left b2s-post-item-details-release-area-label-interval" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '">' . __('Repeats', 'blog2social') . '</label>';
                $shipping .= '<label class="hidden-sm hidden-xs col-md-2 del-padding-left b2s-post-item-details-release-area-label-duration" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '">' . __('Duration', 'blog2social') . '</label>';

//new since 4.5.0
                $shipping .= '<label class="hidden-sm hidden-xs col-md-2 del-padding-left b2s-post-item-details-release-area-label-duration-month" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" style="display:none;">' . __('Duration', 'blog2social') . '</label>';
                $shipping .= '<label class="hidden-sm hidden-xs col-md-2 del-padding-left b2s-post-item-details-release-area-label-duration-time" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" style="display:none;">' . __('Number of repeats', 'blog2social') . '</label>';
                $shipping .= '<label class="hidden-sm hidden-xs col-md-2 del-padding-left b2s-post-item-details-release-area-label-select-day" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" style="display:none;">' . __('Day of month', 'blog2social') . '</label>';
                $shipping .= '<label class="hidden-sm hidden-xs col-md-2 del-padding-left b2s-post-item-details-release-area-label-select-timespan" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" style="display:none;">' . __('Repeats every (days)', 'blog2social') . '</label>';

                $shipping .= '<label class="hidden-sm hidden-xs col-md-2 del-padding-left b2s-post-item-details-release-area-label-date" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '">' . __('Start date', 'blog2social') . '</label>';
                $shipping .= '<label class="hidden-sm hidden-xs col-md-2 del-padding-left b2s-post-item-details-release-area-label-time" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '">' . __('Time to publish', 'blog2social') . '</label>';
                $shipping .= '<label class="hidden-sm hidden-xs col-md-4 del-padding-left b2s-post-item-details-release-area-label-day" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '">' . __('Days', 'blog2social') . '</label>';

                $shipping .= '<div class="clearfix"></div>';



                if ($showSchedRegularly) {
                    $shipping .= '<div class="col-xs-12 col-sm-6 col-md-2 del-padding-left b2s-post-item-details-release-area-div-interval" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '"><select name="b2s[' . $networkAuthId . '][intervalSelect][' . $schedcount . ']" class="form-control b2s-select b2s-post-item-details-release-input-interval-select" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '" style="display:none;" disabled="disabled">';
                    $shipping .= '<option value="0" selected="selected">' . __('weekly', 'blog2social') . '</option>';
                    $shipping .= '<option value="1">' . __('monthly', 'blog2social') . '</option>';
                    $shipping .= '<option value="2">' . __('own period', 'blog2social') . '</option>';
                    $shipping .= '</select></div>';

                    $shipping .= '<div class="col-xs-12 col-sm-6 col-md-2 del-padding-left b2s-post-item-details-release-area-div-duration" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '"><select name="b2s[' . $networkAuthId . '][weeks][' . $schedcount . ']" class="form-control b2s-select b2s-post-item-details-release-input-weeks" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '" style="display:none;" disabled="disabled">';
                    $defaultWeek = isset($this->defaultScheduleTime[$networkId][$schedcount]['weeks']) ? $this->defaultScheduleTime[$networkId][$schedcount]['weeks'] : 1;
                    for ($i = 1; $i <= $this->maxWeekTimeSelect; $i++) {
                        $weekName = ($i == 1) ? __('Week', 'blog2social') : __('Weeks', 'blog2social');
                        $shipping .= '<option value="' . $i . '" ' . ($defaultWeek == $i ? 'selected="selected"' : '') . '>' . $i . ' ' . $weekName . '</option>';
                    }
                    $shipping .= '</select></div>';

//new since 4.5.0
                    $shipping .= '<div class="col-xs-12 col-sm-6 col-md-2 del-padding-left b2s-post-item-details-release-area-div-duration-month" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" style="display:none;"><select name="b2s[' . $networkAuthId . '][duration_month][' . $schedcount . ']" class="form-control b2s-select b2s-post-item-details-release-input-months" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '" style="display:none;" disabled="disabled">';
                    $defaultMonth = isset($this->defaultScheduleTime[$networkId][$schedcount]['month']) ? $this->defaultScheduleTime[$networkId][$schedcount]['month'] : 1;
                    for ($i = 1; $i <= $this->maxMonthTimeSelect; $i++) {
                        $monthName = ($i == 1) ? __('Month', 'blog2social') : __('Months', 'blog2social');
                        $shipping .= '<option value="' . $i . '" ' . ($defaultMonth == $i ? 'selected="selected"' : '') . '>' . $i . ' ' . $monthName . '</option>';
                    }
                    $shipping .= '</select></div>';

//new since 4.5.0
                    $shipping .= '<div class="col-xs-12 col-sm-6 col-md-2 del-padding-left b2s-post-item-details-release-area-div-duration-time" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" style="display:none;"><select name="b2s[' . $networkAuthId . '][duration_time][' . $schedcount . ']" class="form-control b2s-select b2s-post-item-details-release-input-times" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '" style="display:none;" disabled="disabled">';
                    $defaultTime = isset($this->defaultScheduleTime[$networkId][$schedcount]['time']) ? $this->defaultScheduleTime[$networkId][$schedcount]['time'] : 1;
                    for ($i = 1; $i <= $this->maxTimeSelect; $i++) {
                        $timeName = ""; //($i == 1) ? __('Time', 'blog2social') : __('Times', 'blog2social');
                        $shipping .= '<option value="' . $i . '" ' . ($defaultTime == $i ? 'selected="selected"' : '') . '>' . $i . ' ' . $timeName . '</option>';
                    }
                    $shipping .= '</select></div>';

//new since 4.5.0
                    $shipping .= '<div class="col-xs-12 col-sm-6 col-md-2 del-padding-left b2s-post-item-details-release-area-label-select-day" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" style="display:none;"><select name="b2s[' . $networkAuthId . '][select_day][' . $schedcount . ']" class="form-control b2s-select b2s-post-item-details-release-input-select-day" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '" style="display:none;" disabled="disabled">';
                    $defaultTime = isset($this->defaultScheduleTime[$networkId][$schedcount]['select_day']) ? $this->defaultScheduleTime[$networkId][$schedcount]['select_day'] : 1;
                    for ($i = 1; $i <= $this->maxDaySelect; $i++) {
                        $shipping .= '<option value="' . $i . '" ' . ($defaultTime == $i ? 'selected="selected"' : '') . '>' . $i . '</option>';
                    }
                    $shipping .= '<option value="0">' . __("End Of Month", "blog2social") . '</option>';
                    $shipping .= '</select></div>';
                }

//new since 4.5.0
                $shipping .= '<div class="col-xs-12 col-sm-6 col-md-2 del-padding-left b2s-post-item-details-release-area-label-select-timespan" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" style="display:none;"><input type="number" min="1" max="100" placeholder="' . __('Timespan', 'blog2social') . '" name="b2s[' . $networkAuthId . '][select_timespan][' . $schedcount . ']" data-network-id="' . $networkId . '" data-network-type="' . $networkTyp . '" data-network-count="' . $schedcount . '" data-network-auth-id="' . $networkAuthId . '"  class="b2s-post-item-details-release-input-select-timespan form-control" style="display:none;"  disabled="disabled" value="1"></div>';

                $shipping .= '<div class="col-xs-12 col-sm-6 col-md-2 del-padding-left b2s-post-item-details-release-area-label-date" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '"><input type="text" placeholder="' . __('Date', 'blog2social') . '" name="b2s[' . $networkAuthId . '][date][' . $schedcount . ']" data-network-id="' . $networkId . '" data-network-type="' . $networkTyp . '" data-network-count="' . $schedcount . '" data-network-auth-id="' . $networkAuthId . '"  class="b2s-post-item-details-release-input-date form-control" style="display:none;"  disabled="disabled" readonly value="' . $currentDate . '"></div>';
                $shipping .= '<div class="col-xs-12 col-sm-6 col-md-2 del-padding-left b2s-post-item-details-release-area-label-time" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '"><input type="text" placeholder="' . __('Time', 'blog2social') . '" name="b2s[' . $networkAuthId . '][time][' . $schedcount . ']" data-network-id="' . $networkId . '" data-network-type="' . $networkTyp . '" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '"  class="b2s-post-item-details-release-input-time form-control" style="display:none;" disabled="disabled" readonly value=""></div>';
                $shipping .= '<div class="col-xs-12 col-sm-6 col-md-4 del-padding-left b2s-post-item-details-release-area-label-day" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '">';

                if ($showSchedRegularly) {
                    $shipping .= '<div class="b2s-post-item-details-release-input-daySelect" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '"  style="display:none;">';
                    $shipping .= '<input id="b2s-' . $networkAuthId . '-' . $schedcount . '-mo" type="checkbox" name="b2s[' . $networkAuthId . '][mo][' . $schedcount . ']" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" class="form-control b2s-post-item-details-release-input-days b2s-post-item-details-release-input-lable-day-mo" value="1" disabled="disabled"><label for="b2s-' . $networkAuthId . '-' . $schedcount . '-mo" class="b2s-post-item-details-release-input-lable-day">' . __('Mon', 'blog2social') . '</label>'; //MO
                    $shipping .= '<input id="b2s-' . $networkAuthId . '-' . $schedcount . '-di" type="checkbox" name="b2s[' . $networkAuthId . '][di][' . $schedcount . ']" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" class="form-control b2s-post-item-details-release-input-days b2s-post-item-details-release-input-lable-day-di" value="1" disabled="disabled"><label for="b2s-' . $networkAuthId . '-' . $schedcount . '-di" class="b2s-post-item-details-release-input-lable-day">' . __('Tue', 'blog2social') . '</label>'; //Di
                    $shipping .= '<input id="b2s-' . $networkAuthId . '-' . $schedcount . '-mi" type="checkbox" name="b2s[' . $networkAuthId . '][mi][' . $schedcount . ']" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" class="form-control b2s-post-item-details-release-input-days b2s-post-item-details-release-input-lable-day-mi" value="1" disabled="disabled"><label for="b2s-' . $networkAuthId . '-' . $schedcount . '-mi" class="b2s-post-item-details-release-input-lable-day">' . __('Wed', 'blog2social') . '</label>'; //Mi
                    $shipping .= '<input id="b2s-' . $networkAuthId . '-' . $schedcount . '-do" type="checkbox" name="b2s[' . $networkAuthId . '][do][' . $schedcount . ']" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" class="form-control b2s-post-item-details-release-input-days b2s-post-item-details-release-input-lable-day-do" value="1" disabled="disabled"><label for="b2s-' . $networkAuthId . '-' . $schedcount . '-do" class="b2s-post-item-details-release-input-lable-day">' . __('Thu', 'blog2social') . '</label>'; //Do
                    $shipping .= '<input id="b2s-' . $networkAuthId . '-' . $schedcount . '-fr" type="checkbox" name="b2s[' . $networkAuthId . '][fr][' . $schedcount . ']" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" class="form-control b2s-post-item-details-release-input-days b2s-post-item-details-release-input-lable-day-fr" value="1" disabled="disabled"><label for="b2s-' . $networkAuthId . '-' . $schedcount . '-fr" class="b2s-post-item-details-release-input-lable-day">' . __('Fri', 'blog2social') . '</label>'; //Fr
                    $shipping .= '<input id="b2s-' . $networkAuthId . '-' . $schedcount . '-sa" type="checkbox" name="b2s[' . $networkAuthId . '][sa][' . $schedcount . ']" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" class="form-control b2s-post-item-details-release-input-days b2s-post-item-details-release-input-lable-day-sa" value="1" disabled="disabled"><label for="b2s-' . $networkAuthId . '-' . $schedcount . '-sa" class="b2s-post-item-details-release-input-lable-day">' . __('Sat', 'blog2social') . '</label>'; //Sa
                    $shipping .= '<input id="b2s-' . $networkAuthId . '-' . $schedcount . '-so" type="checkbox" name="b2s[' . $networkAuthId . '][so][' . $schedcount . ']" data-network-auth-id="' . $networkAuthId . '" data-network-count="' . $schedcount . '" class="form-control b2s-post-item-details-release-input-days b2s-post-item-details-release-input-lable-day-so" value="1" disabled="disabled"><label for="b2s-' . $networkAuthId . '-' . $schedcount . '-so" class="b2s-post-item-details-release-input-lable-day">' . __('Sun', 'blog2social') . '</label>'; //So
                    $shipping .= '</div>';
                }
                $shipping .= '</div>';
                $shipping .= '<div class="col-md-2 del-padding-left">';
                if (!($networkTyp >= 1 && (in_array($networkId, $this->addNoMoreSchedPage) || in_array($networkId, $this->addNoMoreSchedGroup)))) {
                    $shipping .= ( $schedcount >= 1) ? '<button class="btn btn-link b2s-post-item-details-release-input-hide"  data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '" data-network-count="1" style="display:none;">-' . __('delete', 'blog2social') . '</button>' : '';
                    $shipping .= $schedcount < $maxSchedCount - 1 ? '<button class="btn btn-link b2s-post-item-details-release-input-add" data-network-id="' . $networkId . '" data-network-type="' . $networkTyp . '" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '" data-network-count="1" style="display:none;">+' . __('add another post', 'blog2social') . '</button>' : '';
                }
                $shipping .= '</div>';

//since 4.8.0 customize content 
                if (in_array($networkId, $this->allowSchedCustomizeContent)) {
                    $countCharacter = 0;
                    if ($limit !== false) {
                        $textareaLimitInfo = '<span class="b2s-post-item-countChar" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '">' . (int) $countCharacter . '</span>/' . $limitValue . ' ' . __('characters', 'blog2social') . '</span>';
                    } else {
                        $textareaLimitInfo = '<span class="b2s-post-item-countChar" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '">' . (int) $countCharacter . '</span> ' . __('characters', 'blog2social') . '</span>';
                    }

                    $edit = '<div class="pull-right hidden-xs b2s-post-item-info-area" data-network-id="' . $networkId . '" data-network-count="' . $schedcount . '" data-network-auth-id="' . $networkAuthId . '">';
                    $edit .= '<button class="btn btn-xs btn-link b2s-post-ship-item-copy-original-text" data-network-id="' . $networkId . '" data-network-count="' . $schedcount . '" data-network-auth-id="' . $networkAuthId . '" >' . __('Copy from original', 'blog2social') . '</button> | ';
                    if (in_array($networkId, $this->getText)) {
                        $edit .= '<button class="btn btn-xs btn-link b2s-post-ship-item-full-text" data-network-count="' . $schedcount . '" data-network-auth-id="' . $networkAuthId . '" >' . __('Insert full-text', 'blog2social') . '</button> | ';
                    }
                    $edit .= '<button class="btn btn-xs btn-link b2s-post-ship-item-message-delete" data-network-count="' . $schedcount . '" data-network-auth-id="' . $networkAuthId . '">' . __('Delete text', 'blog2social') . '</button> | ';
                    $edit .= $textareaLimitInfo . '</div>';
                    $shipping .= '<div class="form-group b2s-post-item-details-release-customize-sched-area-details-row" data-network-count="' . $schedcount . '"  data-network-auth-id="' . $networkAuthId . '" style="display:none;">';
                    $shipping .= '<div class="clearfix"></div>';
                    $shipping .= $this->getCustomEditSchedArea($schedcount, $networkId, $networkAuthId, $networkType, $message, $isRequiredTextarea, $textareaOnKeyUp, $limit, $limitValue, $edit, $imageUrl);
                    $shipping .= '</div>';
                }

                $shipping .= '</div>';
            }
            $shipping .= '<div class="col-xs-12 del-padding-left">';
            $shipping .= '<label class="b2s-settings-time-zone-text"></label>';
            $shipping .= '<button class="btn btn-sm btn-link pull-right b2s-post-item-details-release-area-sched-for-all" data-network-auth-id="' . $networkAuthId . '">' . __('Apply Settings To All Networks', 'blog2social') . '</button>';
            $shipping .= '<label class="pull-right btn btn-link btn-sm b2s-post-item-details-release-save-settings-label" data-network-auth-id="' . $networkAuthId . '"><input class="b2s-post-item-details-release-save-settings" data-network-auth-id="' . $networkAuthId . '" type="checkbox" name="b2s[' . $networkAuthId . '][saveSchedSetting]" value="1" disabled="disabled">' . __('Save as best time for this network', 'blog2social') . '</label>';
            $shipping .= '</div><div class="clearfix"></div>';
            $shipping .= '</li>';
            $shipping .= '</ul>';
            $shipping .= '</div>';
        }
        return $shipping;
    }

    public function setPostUrl($value) {
        $this->postUrl = $value;
    }

    public function setTitle($value) {
        if ($this->postData) {
            $this->postData->post_title = $value;
        }
    }

}
