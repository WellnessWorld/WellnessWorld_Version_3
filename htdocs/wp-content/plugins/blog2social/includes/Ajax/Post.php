<?php

class Ajax_Post {

    static private $instance = null;

    static public function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function __construct() {
        add_action('wp_ajax_b2s_save_ship_data', array($this, 'saveShipData'));
        add_action('wp_ajax_b2s_save_user_mandant', array($this, 'saveUserMandant'));
        add_action('wp_ajax_b2s_delete_mandant', array($this, 'deleteUserMandant'));
        add_action('wp_ajax_b2s_lock_auto_post_import', array($this, 'lockAutoPostImport'));
        add_action('wp_ajax_b2s_delete_user_auth', array($this, 'deleteUserAuth'));
        add_action('wp_ajax_b2s_update_user_version', array($this, 'updateUserVersion'));
        add_action('wp_ajax_b2s_accept_privacy_policy', array($this, 'acceptPrivacyPolicy'));
        add_action('wp_ajax_b2s_create_trail', array($this, 'createTrail'));
        add_action('wp_ajax_b2s_save_network_board_and_group', array($this, 'saveNetworkBoardAndGroup'));
        add_action('wp_ajax_b2s_delete_user_sched_post', array($this, 'deleteUserSchedPost'));
        add_action('wp_ajax_b2s_delete_user_publish_post', array($this, 'deleteUserPublishPost'));
        add_action('wp_ajax_b2s_delete_user_approve_post', array($this, 'deleteUserApprovePost'));
        add_action('wp_ajax_b2s_user_network_settings', array($this, 'saveUserNetworkSettings'));
        add_action('wp_ajax_b2s_save_social_meta_tags', array($this, 'saveSocialMetaTags'));
        add_action('wp_ajax_b2s_reset_social_meta_tags', array($this, 'resetSocialMetaTags'));
        add_action('wp_ajax_b2s_save_user_time_settings', array($this, 'saveUserTimeSettings'));
        add_action('wp_ajax_b2s_network_save_auth_to_settings', array($this, 'saveAuthToSettings'));
        add_action('wp_ajax_b2s_prg_login', array($this, 'prgLogin'));
        add_action('wp_ajax_b2s_prg_logout', array($this, 'prgLogout'));
        add_action('wp_ajax_b2s_prg_ship', array($this, 'prgShip'));
        add_action('wp_ajax_b2s_notice_hide', array($this, 'noticeHide'));
        add_action('wp_ajax_b2s_ship_navbar_save_settings', array($this, 'b2sShipNavbarSaveSettings'));
        add_action('wp_ajax_b2s_post_mail_update', array($this, 'b2sPostMailUpdate'));
        add_action('wp_ajax_b2s_calendar_move_post', array($this, 'b2sCalendarMovePost'));
        add_action('wp_ajax_b2s_delete_post', array($this, 'b2sDeletePost'));
        add_action('wp_ajax_b2s_edit_save_post', array($this, 'b2sEditSavePost'));
        add_action("wp_ajax_b2s_get_calendar_release_locks", array($this, 'releaseLocks'));
        add_action("wp_ajax_b2s_update_approve_post", array($this, 'updateApprovePost'));
        add_action("wp_ajax_b2s_hide_rating", array($this, 'hideRating'));
        add_action("wp_ajax_b2s_hide_premium_message", array($this, 'hidePremiumMessage'));
        add_action("wp_ajax_b2s_hide_trail_message", array($this, 'hideTrailMessage'));
        add_action("wp_ajax_b2s_hide_trail_ended_message", array($this, 'hideTrailEndedMessage'));
        add_action("wp_ajax_b2s_plugin_deactivate_delete_sched_post", array($this, 'b2sPluginDeactivate'));
        add_action("wp_ajax_b2s_curation_share", array($this, 'curationShare'));
        add_action("wp_ajax_b2s_curation_customize", array($this, 'curationCustomize'));
    }

    public function curationShare() {
        //save as blog post
        if (isset($_POST['title']) && !empty($_POST['title']) && isset($_POST['comment']) && !empty($_POST['comment']) && isset($_POST['url']) && !empty($_POST['url'])) {
            require_once (B2S_PLUGIN_DIR . 'includes/B2S/Curation/Save.php');
            $data = array('title' => $_POST['title'], 'url' => $_POST['url'], 'content' => (isset($_POST['comment']) ? $_POST['comment'] : ''), 'author_id' => B2S_PLUGIN_BLOG_USER_ID);
            $curation = new B2S_Curation_Save($data);
            $postId = $curation->insertContent();
            if ($postId !== false) {
                //check Data
                if (isset($_POST['profile_select'])) {
                    $profilId = (int) $_POST['profile_select'];
                    if (isset($_POST['profile_data_' . $profilId]) && !empty($_POST['profile_data_' . $profilId])) {
                        $networkData = json_decode(base64_decode($_POST['profile_data_' . $profilId]));
                        if ($networkData !== false && is_array($networkData) && !empty($networkData)) {
                            $notAllowNetwork = array(4, 11, 14, 16, 18);
                            $tosCrossPosting = unserialize(B2S_PLUGIN_NETWORK_CROSSPOSTING_LIMIT);
                            $allowNetworkOnlyImage = array(6, 7, 12);
                            //TOS Twitter 032018 - none multiple Accounts - User select once
                            $selectedTwitterProfile = (isset($_POST['twitter_select']) && !empty($_POST['twitter_select'])) ? (int) $_POST['twitter_select'] : '';
                            require_once (B2S_PLUGIN_DIR . 'includes/B2S/QuickPost.php');
                            $quickPost = new B2S_QuickPost($_POST['comment'], $_POST['title']);
                            $defaultShareData = array('default_titel' => strip_tags($_POST['title']),
                                'image_url' => (!empty($_POST['image_url'])) ? trim(urldecode($_POST['image_url'])) : '',
                                'lang' => trim(strtolower(substr(B2S_LANGUAGE, 0, 2))),
                                'board' => '',
                                'group' => '',
                                'post_id' => $postId,
                                'blog_user_id' => B2S_PLUGIN_BLOG_USER_ID,
                                'tags' => array(),
                                'url' => $_POST['url'],
                                'no_cache' => 0,
                                'token' => B2S_PLUGIN_TOKEN,
                                'user_timezone' => (isset($_POST['b2s_user_timezone']) ? $_POST['b2s_user_timezone'] : 0 ),
                                'publish_date' => isset($_POST['publish_date']) ? date('Y-m-d H:i:s', strtotime($_POST['publish_date'])) : date('Y-m-d H:i:s', current_time('timestamp')));
                            require_once (B2S_PLUGIN_DIR . 'includes/B2S/Ship/Save.php');
                            $b2sShipSend = new B2S_Ship_Save();
                            $content = array();
                            foreach ($networkData as $k => $value) {
                                if (isset($value->networkAuthId) && (int) $value->networkAuthId > 0 && isset($value->networkId) && (int) $value->networkId > 0 && isset($value->networkType)) {
                                    //TOS Twitter 032018 - none multiple Accounts - User select once
                                    if ((int) $value->networkId != 2 || ((int) $value->networkId == 2 && (empty($selectedTwitterProfile) || ((int) $selectedTwitterProfile == (int) $value->networkAuthId)))) {
                                        //Filter: image network
                                        if (in_array($value->networkId, $allowNetworkOnlyImage) && (!isset($_POST['image_url']) || empty($_POST['image_url']))) {
                                            $content = array_merge($content, array('networkDisplayName' => $value->networkUserName, 'networkAuthId' => $value->networkAuthId, 'networkId' => $value->networkId, 'networkType' => $value->networkType, 'html' => $b2sShipSend->getItemHtml($value->networkId, 'IMAGE_FOR_CURATION')));
                                            continue;
                                        }
                                        //Filter: Blog network
                                        if (in_array($value->networkId, $notAllowNetwork)) {
                                            continue;
                                        }

                                        //Filter: TOS Crossposting ignore
                                        if (isset($tosCrossPosting[$value->networkId][$value->networkType])) {
                                            continue;
                                        }

                                        //Filter: DeprecatedNetwork-8 31 march
                                        if ($value->networkId == 8) {
                                            if (isset($_POST['ship_type']) && (int) $_POST['ship_type'] == 1 && isset($_POST['ship_date']) && !empty($_POST['ship_date']) && strtotime($_POST['ship_date']) !== false) {
                                                if (date('Y-m-d', strtotime($_POST['ship_date'])) >= '2019-03-31') {
                                                    //special case xing groups  contains network_display_name
                                                    global $wpdb;
                                                    $networkDetailsId = 0;
                                                    if ($value->networkType == 2) {
                                                        $networkDetailsIdSelect = $wpdb->get_col($wpdb->prepare("SELECT postNetworkDetails.id FROM b2s_posts_network_details AS postNetworkDetails WHERE postNetworkDetails.network_auth_id = %s AND postNetworkDetails.network_display_name = %s", $value->networkAuthId, trim($value->networkUserName)));
                                                    } else {
                                                        $networkDetailsIdSelect = $wpdb->get_col($wpdb->prepare("SELECT postNetworkDetails.id FROM b2s_posts_network_details AS postNetworkDetails WHERE postNetworkDetails.network_auth_id = %s", $value->networkAuthId));
                                                    }
                                                    if (isset($networkDetailsIdSelect[0])) {
                                                        $networkDetailsId = (int) $networkDetailsIdSelect[0];
                                                    } else {
                                                        $wpdb->insert('b2s_posts_network_details', array(
                                                            'network_id' => (int) $value->networkId,
                                                            'network_type' => (int) $value->networkType,
                                                            'network_auth_id' => (int) $value->networkAuthId,
                                                            'network_display_name' => $value->networkUserName), array('%d', '%d', '%d', '%s'));
                                                        $networkDetailsId = $wpdb->insert_id;
                                                    }
                                                    $timeZone= (isset($_POST['b2s_user_timezone']) ? $_POST['b2s_user_timezone'] : 0 );
                                                    $wpdb->insert('b2s_posts', array(
                                                        'post_id' => $postId,
                                                        'blog_user_id' => B2S_PLUGIN_BLOG_USER_ID,
                                                        'user_timezone' =>$timeZone,
                                                        'publish_date' => date('Y-m-d H:i:s', strtotime(B2S_Util::getUTCForDate(gmdate('Y-m-d H:i:s'), $timeZone * (-1)))),
                                                        'publish_error_code' => 'DEPRECATED_NETWORK_8',
                                                        'network_details_id' => $networkDetailsId), array('%d', '%d', '%s', '%s', '%s', '%d'));
                                                    continue;
                                                }
                                            }
                                        }
                                        $shareData = $quickPost->prepareShareData($value->networkAuthId, $value->networkId, $value->networkType);
                                        if ($shareData !== false) {
                                            $shareData['network_id'] = $value->networkId;
                                            $shareData['network_type'] = $value->networkType;
                                            $shareData['instant_sharing'] = ((isset($value->instant_sharing) && (int) $value->instant_sharing == 1) ? 1 : 0);
                                            $shareData['network_display_name'] = $value->networkUserName;
                                            $shareData['network_auth_id'] = $value->networkAuthId;
                                            $shareData = array_merge($shareData, $defaultShareData);
                                            //Type schedule
                                            if (isset($_POST['ship_type']) && (int) $_POST['ship_type'] == 1 && isset($_POST['ship_date']) && !empty($_POST['ship_date']) && strtotime($_POST['ship_date']) !== false) {
                                                $shipDateTime = array('date' => array(date('Y-m-d', strtotime($_POST['ship_date']))), 'time' => array(date('H:i', strtotime($_POST['ship_date']))));
                                                $schedData = array(
                                                    'date' => $shipDateTime['date'],
                                                    'time' => $shipDateTime['time'],
                                                    'releaseSelect' => 1,
                                                    'user_timezone' => (isset($_POST['b2s_user_timezone']) ? $_POST['b2s_user_timezone'] : 0 ),
                                                    'saveSetting' => false);
                                                $schedRes = $b2sShipSend->saveSchedDetails($shareData, $schedData, array());
                                                $schedResult = array_merge($schedRes, array('networkDisplayName' => $value->networkUserName, 'networkId' => $value->networkId, 'networkType' => $value->networkType));
                                                $content = array_merge($content, array($schedResult));
                                            } else {
                                                //TYPE direct share
                                                $b2sShipSend->savePublishDetails($shareData, array(), true);
                                            }
                                        }
                                    }
                                }
                            }
                            if (!empty($b2sShipSend->postDataApprove)) {
                                $sendResult = $b2sShipSend->getShareApproveDetails(true);
                                $content = array_merge($content, $sendResult);
                            }
                            if (!empty($b2sShipSend->postData)) {
                                $sendResult = $b2sShipSend->postPublish(true);
                                $content = array_merge($content, $sendResult);
                            }
                            //Render Ouput
                            if (is_array($content) && !empty($content)) {
                                require_once (B2S_PLUGIN_DIR . 'includes/B2S/Curation/View.php');
                                $view = new B2S_Curation_View();
                                echo json_encode(array('result' => true, 'content' => $view->getResultListHtml($content)));
                                wp_die();
                            }
                        }
                        echo json_encode(array('result' => false, 'error' => 'NO_AUTH'));
                        wp_die();
                    }
                    echo json_encode(array('result' => false, 'error' => 'NO_AUTH'));
                    wp_die();
                }
            }
        }
        echo json_encode(array('result' => false, 'error' => 'NO_DATA'));
        wp_die();
    }

    public function curationCustomize() {
        if (isset($_POST['title']) && !empty($_POST['title']) && isset($_POST['url']) && !empty($_POST['url'])) {
            require_once (B2S_PLUGIN_DIR . 'includes/B2S/Curation/Save.php');
            $data = array('title' => $_POST['title'], 'url' => $_POST['url'], 'content' => (isset($_POST['comment']) ? $_POST['comment'] : ''), 'author_id' => B2S_PLUGIN_BLOG_USER_ID);
            $curation = new B2S_Curation_Save($data);
            $postId = $curation->insertContent();
            if ($postId !== false) {
                $redirect_url = get_option('siteurl') . ((substr(get_option('siteurl'), -1, 1) == '/') ? '' : '/') . 'wp-admin/admin.php?page=blog2social-ship&b2sPostType=ex&postId=' . $postId;
                if (isset($_POST['ship_type']) && (int) $_POST['ship_type'] == 1 && isset($_POST['ship_date']) && !empty($_POST['ship_date'])) {
                    $sched_date_time = date('Y-m-d H:i:s', strtotime($_POST['ship_date']));
                    if ($sched_date_time !== false) {
                        $redirect_url.='&schedDateTime=' . $sched_date_time;
                    }
                }
                if (isset($_POST['profile_select']) && (int) $_POST['profile_select'] > 0) {
                    $redirect_url.='&profile=' . $_POST['profile_select'];
                }
                if (isset($_POST['image_url']) && !empty($_POST['image_url'])) {
                    $redirect_url.='&img=' . base64_encode($_POST['image_url']);
                }
                echo json_encode(array('result' => true, 'redirect' => $redirect_url));
                wp_die();
            }
        }
        echo json_encode(array('result' => false));
        wp_die();
    }

    public function b2sPluginDeactivate() {
        if (isset($_POST['delete_sched_post']) && (int) $_POST['delete_sched_post'] == 1) {
            update_option("B2S_PLUGIN_DEACTIVATE_SCHED_POST", 1,false);
        } else {
            delete_option("B2S_PLUGIN_DEACTIVATE_SCHED_POST");
        }
        echo json_encode(array('result' => true));
        wp_die();
    }

    public function prgShip() {

        if (!empty($_POST) && isset($_POST['token']) && !empty($_POST['token']) && isset($_POST['prg_id']) && (int) $_POST['prg_id'] > 0 && isset($_POST['blog_user_id']) && (int) $_POST['blog_user_id'] > 0 && isset($_POST['post_id']) && (int) $_POST['post_id'] > 0) {
            $dataPost = $_POST;
            $type = $dataPost['publish'];
            $dataPost['status'] = ((int) $type == 1) ? 'hold' : 'open';
            unset($dataPost['confirm']);
            unset($dataPost['blog_user_id']);
            unset($dataPost['post_id']);
            unset($dataPost['publish']);
            $result = json_decode(trim(PRG_Api_Post::post(B2S_PLUGIN_PRG_API_ENDPOINT . 'post.php', $dataPost)));

            if (is_object($result) && !empty($result) && isset($result->result) && (int) $result->result == 1 && isset($result->create) && (int) $result->create == 1) {
//Contact
                global $wpdb;
                $sqlCheckUser = $wpdb->prepare("SELECT `id` FROM `b2s_user_contact` WHERE `blog_user_id` = %d", $_POST['blog_user_id']);
                $userEntry = $wpdb->get_var($sqlCheckUser);
                $userContact = array('name_mandant' => strip_tags($_POST['name_mandant']),
                    'created' => date('Y-m-d H:i;s'),
                    'name_presse' => strip_tags($_POST['name_presse']),
                    'anrede_presse' => strip_tags($_POST['anrede_presse']),
                    'vorname_presse' => strip_tags($_POST['vorname_presse']),
                    'nachname_presse' => strip_tags($_POST['nachname_presse']),
                    'strasse_presse' => strip_tags($_POST['strasse_presse']),
                    'nummer_presse' => strip_tags($_POST['nummer_presse']),
                    'plz_presse' => strip_tags($_POST['plz_presse']),
                    'ort_presse' => strip_tags($_POST['ort_presse']),
                    'land_presse' => strip_tags($_POST['land_presse']),
                    'email_presse' => strip_tags($_POST['email_presse']),
                    'telefon_presse' => strip_tags($_POST['telefon_presse']),
                    'fax_presse' => isset($_POST['fax_presse']) ? strip_tags($_POST['fax_presse']) : '',
                    'url_presse' => strip_tags($_POST['url_presse'])
                );

                if (!$userEntry) {
                    $insertData = array_merge(array('blog_user_id' => (int) $_POST['blog_user_id']), $userContact);
                    $wpdb->insert('b2s_user_contact', $insertData);
                } else {
                    $wpdb->update('b2s_user_contact', $userContact, array('blog_user_id' => (int) $_POST['blog_user_id']));
                }
                echo json_encode(array('result' => true, 'error' => 0, 'type' => $type));
                wp_die();
            }
            echo json_encode(array('result' => false, 'error' => 2, 'type' => $type)); //NOTSHIP
            wp_die();
        }
        echo json_encode(array('result' => false, 'error' => 1, 'type' => $type)); //INVALIDDATA
        wp_die();
    }

    public function lockAutoPostImport() {
        if (isset($_POST['userId']) && (int) $_POST['userId'] > 0) {
            update_option('B2S_LOCK_AUTO_POST_IMPORT_' . (int) $_POST['userId'], 1,false);
        }
        echo json_encode(array('result' => true));
        wp_die();
    }

    public function prgLogin() {
        if (isset($_POST['postId']) && (int) $_POST['postId'] > 0 && isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password'])) {
            $pubKey = json_decode(PRG_Api_Get::get(B2S_PLUGIN_PRG_API_ENDPOINT . 'auth.php?publicKey=true', array()));
            if (!empty($pubKey) && is_object($pubKey) && isset($pubKey->publicKey) && !empty($pubKey->publicKey) && function_exists('openssl_public_encrypt')) {
                $usernameCrypted = '';
                $passwordCrypted = '';
                openssl_public_encrypt(trim($_POST['username']), $usernameCrypted, $pubKey->publicKey);
                openssl_public_encrypt(trim($_POST['password']), $passwordCrypted, $pubKey->publicKey);
                $datas = array(
                    'action' => 'loginPRG',
                    'username' => base64_encode($usernameCrypted),
                    'password' => base64_encode($passwordCrypted),
                );
                $result = json_decode(trim(PRG_Api_Post::post(B2S_PLUGIN_PRG_API_ENDPOINT . 'auth.php', $datas)));
                if (!empty($result) && is_object($result) && isset($result->prg_token) && !empty($result->prg_token) && isset($result->prg_id) && !empty($result->prg_id)) {
                    if ((int) $result->prg_id > 0) {
                        $prgInfo = array('B2S_PRG_ID' => $result->prg_id,
                            'B2S_PRG_TOKEN' => $result->prg_token);

                        update_option('B2S_PLUGIN_PRG_' . B2S_PLUGIN_BLOG_USER_ID, $prgInfo,false);
                        echo json_encode(array('result' => true, 'error' => 0));
                        wp_die();
                    }
                }
                echo json_encode(array('result' => false, 'error' => 1));
                wp_die();
            }
            echo json_encode(array('result' => false, 'error' => 2)); //SSL ERRROR
            wp_die();
        }
        echo json_encode(array('result' => false, 'error' => 1));
        wp_die();
    }

    public function prgLogout() {
        delete_option('B2S_PLUGIN_PRG_' . B2S_PLUGIN_BLOG_USER_ID);
        echo json_encode(array('result' => true));
        wp_die();
    }

    public function saveShipData() {

        require_once (B2S_PLUGIN_DIR . 'includes/B2S/Ship/Save.php');
        $post = $_POST;
        $metaOg = false;
        $metaCard = false;


        if (!isset($post['b2s']) || !is_array($post['b2s'])) {
            echo json_encode(array('result' => false));
            wp_die();
        }

        $b2sShipSend = new B2S_Ship_Save();

        delete_option('B2S_PLUGIN_POST_META_TAGES_TWITTER_' . (int) $post['post_id']);
        delete_option('B2S_PLUGIN_POST_META_TAGES_OG_' . (int) $post['post_id']);

        $options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
        $optionNoCache = $options->_getOption('link_no_cache');

        $content = array();
        $schedResult = array();
        $defaultPostData = array('token' => B2S_PLUGIN_TOKEN,
            'blog_user_id' => B2S_PLUGIN_BLOG_USER_ID,
            'post_id' => (int) $post['post_id'],
            'default_titel' => isset($post['default_titel']) ? $post['default_titel'] : '',
            'no_cache' => (($optionNoCache === false || $optionNoCache == 0) ? 0 : 1), //default inactive , 1=active 0=not
            'lang' => trim(strtolower(substr(B2S_LANGUAGE, 0, 2))));

        foreach ($post['b2s'] as $networkAuthId => $data) {
            if (!isset($data['url']) || !isset($data['network_id'])) {
                continue;
            }

            //Change/Set MetaTags
            if ((int) $data['network_id'] == 1 && $metaOg == false && (int) $post['post_id'] > 0 && isset($data['post_format']) && (int) $data['post_format'] == 0 && isset($post['change_og_meta']) && (int) $post['change_og_meta'] == 1) {  //LinkPost
                $metaOg = true;
                $meta = B2S_Meta::getInstance();
                $meta->getMeta((int) $post['post_id']);
                if (isset($data['og_title']) && !empty($data['og_title'])) {
                    $meta->setMeta('og_title', $data['og_title']);
                }
                if (isset($data['og_desc']) && !empty($data['og_desc'])) {
                    $meta->setMeta('og_desc', $data['og_desc']);
                }
                if (isset($data['image_url']) && !empty($data['image_url'])) {
                    $meta->setMeta('og_image', trim($data['image_url']));
                }
                $meta->updateMeta((int) $post['post_id']);
            }

//Change/Set MetaTags
            if ((int) $data['network_id'] == 2 && $metaCard == false && (int) $post['post_id'] > 0 && isset($data['post_format']) && (int) $data['post_format'] == 0 && isset($post['change_card_meta']) && (int) $post['change_card_meta'] == 1) {  //LinkPost
                $metaCard = true;
                $meta = B2S_Meta::getInstance();
                $meta->getMeta((int) $post['post_id']);
                if (isset($data['card_title']) && !empty($data['card_title'])) {
                    $meta->setMeta('card_title', $data['card_title']);
                }
                if (isset($data['card_desc']) && !empty($data['card_desc'])) {
                    $meta->setMeta('card_desc', $data['card_desc']);
                }
                if (isset($data['image_url']) && !empty($data['image_url'])) {
                    $meta->setMeta('card_image', trim($data['image_url']));
                }
                $meta->updateMeta((int) $post['post_id']);
            }

            //TOS XING Group
            if (isset($data['network_tos_group_id']) && !empty($data['network_tos_group_id'])) {
                $options = new B2S_Options(0, 'B2S_PLUGIN_TOS_XING_GROUP_CROSSPOSTING');
                $options->_setOption((int) $post['post_id'], $data['network_tos_group_id'], true);
            }

            $sendData = array("board" => isset($data['board']) ? $data['board'] : '',
                "group" => isset($data['group']) ? $data['group'] : '',
                "custom_title" => isset($data['custom_title']) ? strip_tags($data['custom_title']) : '',
                "content" => (isset($data['content']) && !empty($data['content'])) ? strip_tags(html_entity_decode($data['content']), '<p><h1><h2><br><i><b><a><img>') : '',
                'url' => isset($data['url']) ? $data['url'] : '',
                'image_url' => isset($data['image_url']) ? trim($data['image_url']) : '',
                'tags' => isset($data['tags']) ? $data['tags'] : array(),
                'network_id' => isset($data['network_id']) ? $data['network_id'] : '',
                'instant_sharing' => isset($data['instant_sharing']) ? (int) $data['instant_sharing'] : 0,
                'network_tos_group_id' => (isset($data['network_tos_group_id']) && !empty($data['network_tos_group_id'])) ? $data['network_tos_group_id'] : '',
                'network_type' => isset($data['network_type']) ? $data['network_type'] : '',
                'network_kind' => isset($data['network_kind']) ? (int) $data['network_kind'] : 0,
                'marketplace_category' => isset($data['marketplace_category']) ? (int) $data['marketplace_category'] : 0,
                'marketplace_type' => isset($data['marketplace_type']) ? (int) $data['marketplace_type'] : 0,
                'network_display_name' => isset($data['network_display_name']) ? $data['network_display_name'] : '',
                'network_auth_id' => $networkAuthId,
                'post_format' => isset($data['post_format']) ? (int) $data['post_format'] : '',
                'user_timezone' => isset($post['user_timezone']) ? $post['user_timezone'] : 0,
                'publish_date' => isset($post['publish_date']) ? date('Y-m-d H:i:s', strtotime($post['publish_date'])) : date('Y-m-d H:i:s', current_time('timestamp'))
            );
//since V4.8.0 Check Relay and prepare Data
            $relayData = array();
            if ((int) $data['network_id'] == 2 && isset($data['post_relay_account'][0]) && !empty($data['post_relay_account'][0]) && isset($data['post_relay_delay'][0]) && !empty($data['post_relay_delay'][0])) {
                $relayData = array('auth' => $data['post_relay_account'], 'delay' => $data['post_relay_delay']);
            }

//mode: share now
            $schedData = array();
            if (isset($data['releaseSelect']) && (int) $data['releaseSelect'] == 0) {
                $b2sShipSend->savePublishDetails(array_merge($defaultPostData, $sendData), $relayData);
//mode: schedule custom once times
            } else if (isset($data['releaseSelect']) && (int) $data['releaseSelect'] == 1 && isset($data['date'][0]) && isset($data['time'][0])) {
                $schedData = array(
                    'date' => isset($data['date']) ? $data['date'] : array(),
                    'time' => isset($data['time']) ? $data['time'] : array(),
                    'sched_content' => isset($data['sched_content']) ? $data['sched_content'] : array(),
                    'sched_image_url' => isset($data['sched_image_url']) ? $data['sched_image_url'] : array(),
                    'releaseSelect' => isset($data['releaseSelect']) ? $data['releaseSelect'] : 0,
                    'user_timezone' => isset($post['user_timezone']) ? $post['user_timezone'] : 0,
                    'saveSetting' => isset($data['saveSchedSetting']) ? true : false);
                $schedResult [] = $b2sShipSend->saveSchedDetails(array_merge($defaultPostData, $sendData), $schedData, $relayData);
                $content = array_merge($content, $schedResult);
//mode: recurrently schedule
            } else {
                $schedData = array(
                    'interval_select' => isset($data['intervalSelect']) ? $data['intervalSelect'] : array(),
                    'duration_month' => isset($data['duration_month']) ? $data['duration_month'] : array(),
                    'select_day' => isset($data['select_day']) ? $data['select_day'] : array(),
                    'duration_time' => isset($data['duration_time']) ? $data['duration_time'] : array(),
                    'select_timespan' => isset($data['select_timespan']) ? $data['select_timespan'] : array(),
                    'weeks' => isset($data['weeks']) ? $data['weeks'] : 0,
                    'date' => isset($data['date']) ? $data['date'] : array(),
                    'time' => isset($data['time']) ? $data['time'] : array(),
                    'mo' => isset($data['mo']) ? $data['mo'] : array(),
                    'di' => isset($data['di']) ? $data['di'] : array(),
                    'mi' => isset($data['mi']) ? $data['mi'] : array(),
                    'do' => isset($data['do']) ? $data['do'] : array(),
                    'fr' => isset($data['fr']) ? $data['fr'] : array(),
                    'sa' => isset($data['sa']) ? $data['sa'] : array(),
                    'so' => isset($data['so']) ? $data['so'] : array(),
                    'releaseSelect' => isset($data['releaseSelect']) ? $data['releaseSelect'] : 0,
                    'user_timezone' => isset($post['user_timezone']) ? $post['user_timezone'] : 0,
                    'saveSetting' => isset($data['saveSchedSetting']) ? true : false
                );

                $schedResult [] = $b2sShipSend->saveSchedDetails(array_merge($defaultPostData, $sendData), $schedData, $relayData);
                $content = array_merge($content, $schedResult);
            }
        }

        if (!empty($b2sShipSend->postDataApprove)) {
            $sendResult = $b2sShipSend->getShareApproveDetails();
            $content = array_merge($content, $sendResult);
        }

        if (!empty($b2sShipSend->postData)) {
            $sendResult = $b2sShipSend->postPublish();
            $content = array_merge($content, $sendResult);
        }

        echo json_encode(array('result' => true, 'content' => $content));
        wp_die();
    }

    public function saveSocialMetaTags() {

        $result = array('result' => true);
        if (isset($_POST['is_admin']) && (int) $_POST['is_admin'] == 1) {
            $options = new B2S_Options(0, 'B2S_PLUGIN_GENERAL_OPTIONS');

            $og_active = (!isset($_POST['b2s_og_active'])) ? 0 : 1;
            $options->_setOption('og_active', $og_active);
            $options->_setOption('og_default_title', $_POST['b2s_og_default_title']);
            $options->_setOption('og_default_desc', $_POST['b2s_og_default_desc']);
            $options->_setOption('og_default_image', $_POST['b2s_og_default_image']);

            $card_active = (!isset($_POST['b2s_card_active'])) ? 0 : 1;
            $options->_setOption('card_active', $card_active);
            $options->_setOption('card_default_type', $_POST['b2s_card_default_type']);
            $options->_setOption('card_default_title', $_POST['b2s_card_default_title']);
            $options->_setOption('card_default_desc', $_POST['b2s_card_default_desc']);
            $options->_setOption('card_default_image', $_POST['b2s_card_default_image']);

            $meta = B2S_Meta::getInstance();
            $result['b2s'] = ($card_active == 1 || $og_active == 1) ? true : false;
            $result['yoast'] = $meta->is_yoast_seo_active();
            $result['aioseop'] = $meta->is_aioseop_active();
            $result['webdados'] = $meta->is_webdados_active();
        }

//Customize per user premium function
        /* if (isset($_POST['version']) && (int) $_POST['version'] >= 1 && isset($_POST['b2s_og_article_author']) && isset($_POST['b2s_card_twitter_creator'])) {
          $options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
          $og_article_author = (isset($_POST['b2s_og_article_author']) && !empty($_POST['b2s_og_article_author'])) ? trim($_POST['b2s_og_article_author']) : "";
          $card_twitter_creator = (isset($_POST['b2s_card_twitter_creator']) && !empty($_POST['b2s_card_twitter_creator'])) ? trim($_POST['b2s_card_twitter_creator']) : "";
          $meta_author_data = array('og_article_author' => $og_article_author, 'card_twitter_creator' => $card_twitter_creator);
          $options->_setOption('meta_author_data', $meta_author_data);
          } */
        echo json_encode($result);
        wp_die();
    }

    public function resetSocialMetaTags() {
        global $wpdb;
        $sql = "DELETE FROM " . $wpdb->postmeta . " WHERE meta_key = %s";
        $sql = $wpdb->prepare($sql, "_b2s_post_meta");
        $wpdb->query($sql);
        echo json_encode(array('result' => true));
        wp_die();
    }

    public function saveNetworkBoardAndGroup() {
        if (isset($_POST['networkAuthId']) && !empty($_POST['networkAuthId']) && isset($_POST['networkType']) && isset($_POST['boardAndGroup']) && !empty($_POST['boardAndGroup']) && isset($_POST['networkId']) && !empty($_POST['networkId']) && isset($_POST['lang']) && !empty($_POST['lang'])) {
            $post = array('token' => B2S_PLUGIN_TOKEN,
                'action' => 'saveNetworkBoardAndGroup',
                'networkAuthId' => $_POST['networkAuthId'],
                'networkType' => (int) $_POST['networkType'],
                'networkId' => (int) $_POST['networkId'],
                'boardAndGroup' => $_POST['boardAndGroup'],
                'boardAndGroupName' => (isset($_POST['boardAndGroupName']) && !empty($_POST['boardAndGroupName'])) ? strip_tags($_POST['boardAndGroupName']) : '',
                'lang' => $_POST['lang']);
            $result = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, $post));
            if ($result->result == true) {
                echo json_encode(array('result' => true));
                wp_die();
            }
        }
        echo json_encode(array('result' => false));
        wp_die();
    }

    public function saveUserNetworkSettings() {

        if (isset($_POST['short_url'])) {
            $post = array('token' => B2S_PLUGIN_TOKEN,
                'action' => 'saveSettings',
                'short_url' => (int) $_POST['short_url']);
            $result = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, $post));
            if ($result->result == true) {
                echo json_encode(array('result' => true, 'content' => (((int) $_POST['short_url'] >= 1) ? 0 : 1)));
                wp_die();
            }

            echo json_encode(array('result' => true, 'content' => (isset($_POST['short_url']) ? (int) $_POST['short_url'] : 0)));
            wp_die();
        }


        if (isset($_POST['shortener_account_auth_delete'])) {
            $post = array('token' => B2S_PLUGIN_TOKEN,
                'action' => 'saveSettings',
                'shortener_account_auth_delete' => (int) $_POST['shortener_account_auth_delete']);
            $result = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, $post));
            if ($result->result == true) {
                echo json_encode(array('result' => true));
                wp_die();
            }
            echo json_encode(array('result' => true));
            wp_die();
        }

        if (isset($_POST['type']) && $_POST['type'] == 'post_format') {
            $options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
            $post_format = $options->_getOption('post_format');

            $post_format[(int) $_POST['network_id']] = array();

            if (isset($_POST['type-format']) && is_array($_POST['type-format'])) {
                $post_format[(int) $_POST['network_id']] = $_POST['type-format'];
            } else {
                $post_format[(int) $_POST['network_id']] = array('all' => $_POST['all']);
            }

            $options->_setOption('post_format', $post_format);

//Option no_cache
            if ((int) $_POST['network_id'] == 1) {
                $noCache = isset($_POST['no_cache']) ? (int) $_POST['no_cache'] : 0;
                $options->_setOption('link_no_cache', $noCache);
            }
            echo json_encode(array('result' => true));
            wp_die();
        }

        if (isset($_POST['allow_shortcode'])) {
            if ((int) $_POST['allow_shortcode'] == 1) {
                delete_option('B2S_PLUGIN_USER_ALLOW_SHORTCODE_' . B2S_PLUGIN_BLOG_USER_ID);
            } else {
                update_option('B2S_PLUGIN_USER_ALLOW_SHORTCODE_' . B2S_PLUGIN_BLOG_USER_ID, 1,false);
            }
            echo json_encode(array('result' => true, 'content' => (((int) $_POST['allow_shortcode'] == 1) ? 0 : 1)));
            wp_die();
        }

        if (isset($_POST['type']) && $_POST['type'] == 'auto_post') {
            $publish = isset($_POST['b2s-settings-auto-post-publish']) && is_array($_POST['b2s-settings-auto-post-publish']) ? $_POST['b2s-settings-auto-post-publish'] : array();
            $update = isset($_POST['b2s-settings-auto-post-update']) && is_array($_POST['b2s-settings-auto-post-update']) ? $_POST['b2s-settings-auto-post-update'] : array();
            $auto_post = array('publish' => $publish, 'update' => $update);
            $options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
            $options->_setOption('auto_post', $auto_post);
            echo json_encode(array('result' => true));
            wp_die();
        }

        if (isset($_POST['user_time_zone']) && !empty($_POST['user_time_zone'])) {
            $options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
            $options->_setOption('user_time_zone', $_POST['user_time_zone']);
            echo json_encode(array('result' => true));
            wp_die();
        }

        if (isset($_POST['content_network_twitter'])) {
            $options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
            $options->_setOption('content_network_twitter', (int) $_POST['content_network_twitter']);
            echo json_encode(array('result' => true));
            wp_die();
        }


        if (isset($_POST['allow_hashtag'])) {
            $options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
            $options->_setOption('user_allow_hashtag', (int) $_POST['allow_hashtag']);
            echo json_encode(array('result' => true, 'content' => (((int) $_POST['allow_hashtag'] == 1) ? 0 : 1)));
            wp_die();
        }
        if (isset($_POST['legacy_mode'])) {
            $options = new B2S_Options(0, 'B2S_PLUGIN_GENERAL_OPTIONS');
            $options->_setOption('legacy_mode', (int) $_POST['legacy_mode']);
            echo json_encode(array('result' => true, 'content' => (((int) $_POST['legacy_mode'] == 1) ? 0 : 1)));
            wp_die();
        }

        if (isset($_POST['type']) && $_POST['type'] == 'auto_post_imported') {
            if (isset($_POST['b2s-import-auto-post']) && (int) $_POST['b2s-import-auto-post'] == 1 && !isset($_POST['b2s-import-auto-post-network-auth-id'])) {
                echo json_encode(array('result' => false, 'type' => 'no-auth-selected'));
                wp_die();
            }


            $network_auth_id = isset($_POST['b2s-import-auto-post-network-auth-id']) && is_array($_POST['b2s-import-auto-post-network-auth-id']) ? $_POST['b2s-import-auto-post-network-auth-id'] : array();
            $post_type = isset($_POST['b2s-import-auto-post-type-data']) && is_array($_POST['b2s-import-auto-post-type-data']) ? $_POST['b2s-import-auto-post-type-data'] : array();

            $auto_post_import = array('active' => ((isset($_POST['b2s-import-auto-post']) && (int) $_POST['b2s-import-auto-post'] == 1) ? 1 : 0),
                'network_auth_id' => $network_auth_id,
                'ship_state' => ((isset($_POST['b2s-import-auto-post-time-state']) && (int) $_POST['b2s-import-auto-post-time-state'] == 1) ? 1 : 0),
                'ship_delay_time' => (int) $_POST['b2s-import-auto-post-time-data'],
                'post_filter' => ((isset($_POST['b2s-import-auto-post-filter']) && (int) $_POST['b2s-import-auto-post-filter'] == 1) ? 1 : 0),
                'post_type_state' => ((isset($_POST['b2s-import-auto-post-type-state']) && (int) $_POST['b2s-import-auto-post-type-state'] == 1) ? 1 : 0),
                'post_type' => $post_type);

            $options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
            $options->_setOption('auto_post_import', $auto_post_import);
            echo json_encode(array('result' => true));
            wp_die();
        }


        echo json_encode(array('result' => false));
        wp_die();
    }

    public function saveUserMandant() {
        require_once (B2S_PLUGIN_DIR . 'includes/B2S/Network/Save.php');
        $mandant = isset($_POST['mandant']) ? strip_tags($_POST['mandant']) : '';
        if (empty($mandant)) {
            echo json_encode(array('result' => false, 'content' => ""));
            wp_die();
        }
        $mandantResult = B2S_Network_Save::saveUserMandant($mandant);
        echo json_encode(array('result' => $mandantResult['result'], 'mandantId' => $mandantResult['mandantId'], 'mandantName' => $mandantResult['mandantName'], 'content' => $mandantResult['content']));
        wp_die();
    }

    public function deleteUserMandant() {
        if (isset($_POST['mandantId']) && (int) $_POST['mandantId'] > 0) {
            $post = array('token' => B2S_PLUGIN_TOKEN,
                'action' => 'deleteUserMandant',
                'mandantId' => (int) $_POST['mandantId']);
            $deleteResult = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, $post));
            if ($deleteResult->result == true) {
                global $wpdb;
                $wpdb->delete('b2s_user_network_settings', array('mandant_id' => $_POST['mandantId'], 'blog_user_id' => B2S_PLUGIN_BLOG_USER_ID), array('%d', '%d'));
                echo json_encode(array('result' => true, 'mandantId' => (int) $_POST['mandantId']));
                wp_die();
            }
        }
        echo json_encode(array('result' => false, 'mandantId' => ''));
        wp_die();
    }

    public function deleteUserAuth() {
        require_once (B2S_PLUGIN_DIR . 'includes/B2S/Post/Tools.php');
        if (isset($_POST['networkAuthId']) && (int) $_POST['networkAuthId'] > 0 && isset($_POST['networkId']) && (int) $_POST['networkId'] > 0 && isset($_POST['networkType'])) {
            global $wpdb;
            if (isset($_POST['deleteSchedPost']) && (int) $_POST['deleteSchedPost'] == 1) {
                $res = $wpdb->get_results($wpdb->prepare("SELECT b.id, b.post_id, b.post_for_approve, b.post_for_relay FROM b2s_posts b LEFT JOIN b2s_posts_network_details d ON (d.id = b.network_details_id) WHERE d.network_auth_id= %d AND b.hide = %d AND b.publish_date =%s", (int) $_POST['networkAuthId'], 0, '0000-00-00 00:00:00'));
                if (is_array($res) && !empty($res)) {
                    foreach ($res as $k => $row) {
                        if (isset($row->id) && (int) $row->id > 0) {
                            $hookAction = (isset($row->post_for_approve) && (int) $row->post_for_approve == 0) ? 3 : 0;   //since 4.9.1 Facebook Instant Sharing
                            $wpdb->update('b2s_posts', array('hook_action' => $hookAction, 'hide' => 1), array('id' => (int) $row->id));
                            //is post for relay
                            if ((int) $row->post_for_relay == 1) {
                                $relay = B2S_Post_Tools::getAllRelayByPrimaryPostId($row->id);
                                if (is_array($relay) && !empty($relay)) {
                                    foreach ($relay as $item) {
                                        if (isset($item->id) && (int) $item->id > 0) {
                                            $wpdb->update('b2s_posts', array('hook_action' => 3, 'hide' => 1), array('id' => $item->id));
                                        }
                                    }
                                }
                            }
                        }
                    }
                    B2S_Heartbeat::getInstance()->deleteSchedPost();
                    sleep(2);
                }
            }
            $post = array('token' => B2S_PLUGIN_TOKEN,
                'action' => 'deleteUserAuth',
                'networkAuthId' => (int) $_POST['networkAuthId']);
            $deleteResult = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, $post));
            if ($deleteResult->result == true) {
                $wpdb->delete('b2s_user_network_settings', array('network_auth_id' => $_POST['networkAuthId'], 'blog_user_id' => B2S_PLUGIN_BLOG_USER_ID), array('%d', '%d'));
                echo json_encode(array('result' => true, 'networkId' => (int) $_POST['networkId'], 'networkAuthId' => (int) $_POST['networkAuthId']));
                wp_die();
            }
        }
        echo json_encode(array('result' => false, 'networkId' => 0, 'networkAuthId' => 0));
        wp_die();
    }

    public function updateUserVersion() {
        require_once (B2S_PLUGIN_DIR . '/includes/Tools.php');
        if (isset($_POST['key']) && !empty($_POST['key'])) {
            $post = array('token' => B2S_PLUGIN_TOKEN,
                'action' => 'updateUserVersion',
                'version' => B2S_PLUGIN_VERSION,
                'key' => $_POST['key']);
            $keyResult = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, $post));
            if ($keyResult->result == true) {
                B2S_Tools::setUserDetails();
                $lizenzName = unserialize(B2S_PLUGIN_VERSION_TYPE);
                $printName = (isset($keyResult->trail) && $keyResult->trail == true) ? 'FREE-TRIAL' : $lizenzName[$keyResult->version];
                echo json_encode(array('result' => true, 'lizenzName' => $printName));
                wp_die();
            } else if (isset($keyResult->reason)) {
                echo json_encode(array('result' => false, 'reason' => $keyResult->reason));
                wp_die();
            }
        }
        echo json_encode(array('result' => false, 'reason' => 0));
        wp_die();
    }

    public function acceptPrivacyPolicy() {
        require_once (B2S_PLUGIN_DIR . '/includes/Tools.php');
        if (isset($_POST['accept'])) {
            $post = array('token' => B2S_PLUGIN_TOKEN,
                'action' => 'updatePrivacyPolicy',
                'version' => B2S_PLUGIN_VERSION);
            $result = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, $post));
            if ($result->result == true) {
                echo json_encode(array('result' => true));
                delete_option('B2S_PLUGIN_PRIVACY_POLICY_USER_ACCEPT_' . B2S_PLUGIN_BLOG_USER_ID);
                wp_die();
            }
        }
        echo json_encode(array('result' => false));
        wp_die();
    }

    public function createTrail() {
        require_once (B2S_PLUGIN_DIR . '/includes/Tools.php');
        if (isset($_POST['vorname']) && !empty($_POST['vorname']) && isset($_POST['nachname']) && !empty($_POST['nachname']) && isset($_POST['email']) && !empty($_POST['email']) && isset($_POST['url']) && !empty($_POST['url'])) {
            $data = array('token' => B2S_PLUGIN_TOKEN,
                'action' => 'createTrail',
                'vorname' => $_POST['vorname'],
                'nachname' => $_POST['nachname'],
                'email' => $_POST['email'],
                'url' => $_POST['url'],
                'lang' => trim(strtolower(substr(B2S_LANGUAGE, 0, 2))));
            $trailResult = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, $data));
            if ($trailResult->result == true) {
                B2S_Tools::setUserDetails();
                $lizenzName = unserialize(B2S_PLUGIN_VERSION_TYPE);
                $printName = 'FREE-TRIAL (' . $lizenzName[$trailResult->version] . ')';
                echo json_encode(array('result' => true, 'lizenzName' => $printName));
                wp_die();
            }
        }
        echo json_encode(array('result' => false));
        wp_die();
    }

    public function deleteUserPublishPost() {
        require_once (B2S_PLUGIN_DIR . '/includes/B2S/Post/Tools.php');
        if (isset($_POST['postId']) && !empty($_POST['postId'])) {
            $postIds = explode(',', $_POST['postId']);
            if (is_array($postIds) && !empty($postIds)) {
                echo json_encode(B2S_Post_Tools::deleteUserPublishPost($postIds));
                wp_die();
            }
        }
        echo json_encode(array('result' => false));
        wp_die();
    }

    public function deleteUserApprovePost() {
        require_once (B2S_PLUGIN_DIR . '/includes/B2S/Post/Tools.php');
        if (isset($_POST['postId']) && !empty($_POST['postId'])) {
            $postIds = explode(',', $_POST['postId']);
            if (is_array($postIds) && !empty($postIds)) {
                echo json_encode(B2S_Post_Tools::deleteUserApprovePost($postIds));
                wp_die();
            }
        }
        echo json_encode(array('result' => false));
        wp_die();
    }

    public function sendTrailFeedback() {
        require_once (B2S_PLUGIN_DIR . '/includes/Tools.php');
        if (isset($_POST['feedback']) && !empty($_POST['feedback'])) {
            $post = array('token' => B2S_PLUGIN_TOKEN,
                'action' => 'sendTrailFeedback',
                'feedback' => $_POST['feedback']);
            $result = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, $post));
            if ($result->result == true) {
                echo json_encode(array('result' => true));
                wp_die();
            }
        }
        echo json_encode(array('result' => false));
        wp_die();
    }

    //NEW V5.1.0
    public function saveUserTimeSettings() {
        if (isset($_POST['b2s-user-sched-data']) && !empty($_POST['b2s-user-sched-data']) && isset($_POST['b2s-user-sched-data']['time']) && isset($_POST['b2s-user-sched-data']['delay_day'])) {
            foreach ($_POST['b2s-user-sched-data']['time'] as $k => $v) {
                $_POST['b2s-user-sched-data']['time'][$k] = date('H:i', strtotime(date('Y-m-d') . ' ' . $v));
            }
            $options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
            $options->_setOption('auth_sched_time', array('delay_day' => $_POST['b2s-user-sched-data']['delay_day'], 'time' => $_POST['b2s-user-sched-data']['time']));
            echo json_encode(array('result' => true));
            wp_die();
        }
        echo json_encode(array('result' => false));
        wp_die();
    }

    public function noticeHide() {
        global $wpdb;
        $wpdb->update('b2s_user', array('feature' => 1), array('blog_user_id' => B2S_PLUGIN_BLOG_USER_ID), array('%d'), array('%d'));
        echo json_encode(array('result' => true));
        wp_die();
    }

    public function b2sShipNavbarSaveSettings() {
        if (isset($_POST['mandantId'])) {
            global $wpdb;

            $wpdb->delete('b2s_user_network_settings', array('mandant_id' => $_POST['mandantId'], 'blog_user_id' => B2S_PLUGIN_BLOG_USER_ID), array('%d', '%d'));
            if (isset($_POST['selectedAuth']) && is_array($_POST['selectedAuth'])) {
                foreach ($_POST['selectedAuth'] as $k => $networkAuthId) {
                    $wpdb->insert('b2s_user_network_settings', array('blog_user_id' => B2S_PLUGIN_BLOG_USER_ID, 'mandant_id' => $_POST['mandantId'], 'network_auth_id' => $networkAuthId), array('%d', '%d', '%d'));
                }
            }
            echo json_encode(array('result' => true));
            wp_die();
        }
        echo json_encode(array('result' => false));
        wp_die();
    }

    public function saveAuthToSettings() {
        if (isset($_POST['mandandId']) && isset($_POST['networkAuthId'])) {
            global $wpdb;
            $mandantCount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(mandant_id)FROM b2s_user_network_settings  WHERE mandant_id =%d AND blog_user_id=%d ", $_POST['mandandId'], B2S_PLUGIN_BLOG_USER_ID));
            if ($mandantCount > 0) {
                $wpdb->insert('b2s_user_network_settings', array('blog_user_id' => B2S_PLUGIN_BLOG_USER_ID, 'mandant_id' => $_POST['mandandId'], 'network_auth_id' => $_POST['networkAuthId']), array('%d', '%d', '%d'));
            }
            echo json_encode(array('result' => true));
            wp_die();
        }
        echo json_encode(array('result' => false));
        wp_die();
    }

    public function b2sPostMailUpdate() {
        if (isset($_POST['email']) && !empty($_POST['email'])) {
            require_once (B2S_PLUGIN_DIR . '/includes/Tools.php');
            $post = array('action' => 'updateMail',
                'email' => $_POST['email'],
                'lang' => $_POST['lang']);
            B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, $post);
            update_option('B2S_UPDATE_MAIL_' . B2S_PLUGIN_BLOG_USER_ID, $post['email'],false);
        }
        echo json_encode(array('result' => true));
        wp_die();
    }

    public function updateApprovePost() {
//post_id
        if (is_numeric($_POST['post_id']) && (int) $_POST['post_id'] > 0) {
            global $wpdb;
            require_once (B2S_PLUGIN_DIR . '/includes/Options.php');
            require_once (B2S_PLUGIN_DIR . '/includes/Util.php');
            $option = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
            $optionUserTimeZone = $option->_getOption('user_time_zone');
            $userTimeZone = ($optionUserTimeZone !== false) ? $optionUserTimeZone : get_option('timezone_string');
            $userTimeZoneOffset = (empty($userTimeZone)) ? get_option('gmt_offset') : B2S_Util::getOffsetToUtcByTimeZone($userTimeZone);

            $sql = "UPDATE b2s_posts "
                    . "SET sched_date = '0000-00-00 00:00:00', "
                    . "sched_date_utc = '0000-00-00 00:00:00', "
                    . "publish_date = '" . B2S_Util::getbyIdentLocalDate($userTimeZoneOffset) . "', "
                    . "publish_link =  '" . ((isset($_POST['publish_link']) && !empty($_POST['publish_link'])) ? addslashes(strip_tags($_POST['publish_link'])) : '') . "', "
                    . "publish_error_code = '" . ((isset($_POST['publish_error_code']) && !empty($_POST['publish_error_code'])) ? addslashes(strip_tags($_POST['publish_error_code'])) : '') . "', "
                    . "post_for_approve = 0 "
                    . "WHERE id = " . (int) $_POST['post_id'];
            $wpdb->query($sql);
            echo json_encode(array('result' => true));
            wp_die();
        }
    }

    public function b2sCalendarMovePost() {

        global $wpdb;
        if (is_numeric($_POST['b2s_id']) && is_string($_POST['sched_date'])) {

//since V4.9.1 Instant Share Approve - Facebook Profile
            $shareApprove = (isset($_POST['post_for_approve']) && (int) $_POST['post_for_approve'] == 1) ? 1 : 0;
            $sql = "UPDATE b2s_posts "
                    . "SET sched_date = '" . date('Y-m-d H:i:s', strtotime($_POST['sched_date'])) . "', "
                    . "user_timezone = '" . $_POST['user_timezone'] . "', "
                    . "publish_date = '0000-00-00 00:00:00' ,"
                    . "sched_date_utc = '" . B2S_Util::getUTCForDate($_POST['sched_date'], $_POST['user_timezone'] * -1) . "', "
                    . "hook_action = " . (($shareApprove == 0) ? 2 : 0)
                    . " WHERE id = " . $_POST['b2s_id'];

            $wpdb->query($sql);

//is post for relay?
            if (isset($_POST['post_for_relay']) && (int) $_POST['post_for_relay'] == 1) {
                $res = $this->getAllRelayByPrimaryPostId($_POST['b2s_id']);
                if (is_array($res) && !empty($res)) {
                    foreach ($res as $item) {
                        if (isset($item->id) && (int) $item->id > 0 && isset($item->relay_delay_min) && (int) $item->relay_delay_min > 0) {
                            $relay_sched_date = date('Y-m-d H:i:00', strtotime("+" . $item->relay_delay_min . " minutes", strtotime($_POST['sched_date'])));
                            $relay_sched_date_utc = date('Y-m-d H:i:00', strtotime(B2S_Util::getUTCForDate($relay_sched_date, $_POST['user_timezone'] * (-1))));
                            $wpdb->update('b2s_posts', array(
                                'user_timezone' => $_POST['user_timezone'],
                                'publish_date' => "0000-00-00 00:00:00",
                                'sched_date' => $relay_sched_date,
                                'sched_date_utc' => $relay_sched_date_utc,
                                'hook_action' => 2
                                    ), array("id" => $item->id), array('%s', '%s', '%s', '%s', '%d'));
                        }
                    }
                }
            }
        }
        echo json_encode(array('result' => true));
        wp_die();
    }

    public function deleteUserSchedPost() {
        require_once (B2S_PLUGIN_DIR . '/includes/B2S/Post/Tools.php');

        if (isset($_POST['postId']) && !empty($_POST['postId'])) {
            $postIds = explode(',', $_POST['postId']);
            if (is_array($postIds) && !empty($postIds)) {
                echo json_encode(B2S_Post_Tools::deleteUserSchedPost($postIds));
                wp_die();
            }
        }
        echo json_encode(array('result' => false));
        wp_die();
    }

    public function b2sDeletePost() {
        require_once (B2S_PLUGIN_DIR . '/includes/B2S/Post/Tools.php');
        global $wpdb;
        if (isset($_POST['b2s_id']) && (int) $_POST['b2s_id'] > 0 && isset($_POST['post_id']) && (int) $_POST['post_id'] > 0) {
            $sql = $wpdb->prepare("SELECT id,post_id,post_for_approve,post_for_relay FROM b2s_posts WHERE id =%d AND publish_date = %s", (int) $_POST['b2s_id'], "0000-00-00 00:00:00");
            $row = $wpdb->get_row($sql);
            if (isset($row->id) && (int) $row->id == (int) $_POST['b2s_id']) {
                $hookAction = (isset($row->post_for_approve) && (int) $row->post_for_approve == 0) ? 3 : 0;   //since 4.9.1 Facebook Instant Sharing
                $wpdb->update('b2s_posts', array('hook_action' => $hookAction, 'hide' => 1), array('id' => (int) $_POST['b2s_id']));
//is post for relay
                if ((int) $row->post_for_relay == 1) {
                    $res = B2S_Post_Tools::getAllRelayByPrimaryPostId($row->id);
                    if (is_array($res) && !empty($res)) {
                        foreach ($res as $item) {
                            if (isset($item->id) && (int) $item->id > 0) {
                                $wpdb->update('b2s_posts', array('hook_action' => 3, 'hide' => 1), array('id' => $item->id));
                            }
                        }
                    }
                }
            }
            delete_option("B2S_PLUGIN_CALENDAR_BLOCKED_" . $_POST['b2s_id']);
            delete_option('B2S_PLUGIN_POST_META_TAGES_TWITTER_' . (int) $_POST['post_id']);
            delete_option('B2S_PLUGIN_POST_META_TAGES_OG_' . (int) $_POST['post_id']);
        }
        echo json_encode(array('result' => true));
        wp_die();
    }

    public function b2sEditSavePost() {
        global $wpdb;
        require_once (B2S_PLUGIN_DIR . 'includes/B2S/Calendar/Save.php');

        $post = $_POST;
        $metaOg = false;
        $metaCard = false;
        $sched_date = '';

        /* if ($post['save_method'] == "apply-all") {
          $b2sids = array();
          $sql = "SELECT id "
          . "FROM b2s_posts "
          . "WHERE post_id = %d";

          $sql = $wpdb->prepare($sql, array($_POST['post_id']));
          $items = $wpdb->get_results($sql);
          foreach ($items as $item) {
          $b2sids[] = $item->id;
          }
          } else { */
        $b2sids = array($post['b2s_id']);
        /* } */

        delete_option('B2S_PLUGIN_POST_META_TAGES_TWITTER_' . (int) $post['post_id']);
        delete_option('B2S_PLUGIN_POST_META_TAGES_OG_' . (int) $post['post_id']);

        require_once(B2S_PLUGIN_DIR . 'includes/Options.php');
        $options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
        $optionNoCache = $options->_getOption('link_no_cache');

        foreach ($b2sids as $b2s_id) {
            $b2sShipSend = new B2S_Calendar_Save();

            $defaultPostData = array(
                'original_blog_user_id' => (int) $post['original_blog_user_id'],
                'last_edit_blog_user_id' => B2S_PLUGIN_BLOG_USER_ID,
                'post_id' => (int) $post['post_id'],
                'b2s_id' => (int) $b2s_id,
                'default_titel' => isset($post['default_titel']) ? $post['default_titel'] : '',
                'no_cache' => (($optionNoCache === false || $optionNoCache == 0) ? 0 : 1), //default inactive , 1=active 0=not
                'lang' => trim(strtolower(substr(B2S_LANGUAGE, 0, 2))));


//is relay post?
            if (isset($post['relay_primary_post_id']) && (int) $post['relay_primary_post_id'] > 0 && (int) $b2s_id > 0) {
                if (isset($post['relay_primary_sched_date']) && !empty($post['relay_primary_sched_date']) && isset($post['network_auth_id']) && (int) $post['network_auth_id'] > 0) {
                    if (isset($post['b2s'][$post['network_auth_id']]['post_relay_delay'][0]) && (int) $post['b2s'][$post['network_auth_id']]['post_relay_delay'][0] > 0) {
                        $sched_date = date('Y-m-d H:i:00', strtotime("+" . $post['b2s'][$post['network_auth_id']]['post_relay_delay'][0] . " minutes", strtotime($post['relay_primary_sched_date'])));
                        $sched_date_utc = date('Y-m-d H:i:00', strtotime(B2S_Util::getUTCForDate($sched_date, $post['user_timezone'] * (-1))));
                        $wpdb->update('b2s_posts', array(
                            'user_timezone' => $post['user_timezone'],
                            'publish_date' => "0000-00-00 00:00:00",
                            'sched_date' => $sched_date,
                            'sched_date_utc' => $sched_date_utc,
                            'hook_action' => 2
                                ), array("id" => $b2s_id), array('%s', '%s', '%s', '%s', '%d'));
                        $sched_date = B2S_Util::getCustomDateFormat(date('Y-m-d H:i:00', strtotime($sched_date)), substr(B2S_LANGUAGE, 0, 2));
                    }
                }
            } else {

                foreach ($post['b2s'] as $networkAuthId => $data) {
                    if (!isset($data['url']) || !isset($data['content']) || !isset($data['network_id'])) {
                        continue;
                    }
//Change/Set MetaTags
                    if ((int) $data['network_id'] == 1 && $metaOg == false && (int) $post['post_id'] > 0 && isset($data['post_format']) && (int) $data['post_format'] == 0 && isset($post['change_og_meta']) && (int) $post['change_og_meta'] == 1) {  //LinkPost
                        $metaOg = true;
                        $meta = B2S_Meta::getInstance();
                        $res = $meta->getMeta((int) $post['post_id']);
                        if (isset($data['og_title']) && !empty($data['og_title'])) {
                            $meta->setMeta('og_title', $data['og_title']);
                        }
                        if (isset($data['og_desc']) && !empty($data['og_desc'])) {
                            $meta->setMeta('og_desc', $data['og_desc']);
                        }
                        if (isset($data['image_url']) && !empty($data['image_url'])) {
                            $meta->setMeta('og_image', trim($data['image_url']));
                        }
                        $meta->updateMeta((int) $post['post_id']);
                    }

//Change/Set MetaTags
                    if ((int) $data['network_id'] == 2 && $metaCard == false && (int) $post['post_id'] > 0 && isset($data['post_format']) && (int) $data['post_format'] == 0 && isset($post['change_card_meta']) && (int) $post['change_card_meta'] == 1) {  //LinkPost
                        $metaCard = true;
                        $meta = B2S_Meta::getInstance();
                        $meta->getMeta((int) $post['post_id']);
                        if (isset($data['card_title']) && !empty($data['card_title'])) {
                            $meta->setMeta('card_title', $data['card_title']);
                        }
                        if (isset($data['card_desc']) && !empty($data['card_desc'])) {
                            $meta->setMeta('card_desc', $data['card_desc']);
                        }
                        if (isset($data['image_url']) && !empty($data['image_url'])) {
                            $meta->setMeta('card_image', trim($data['image_url']));
                        }
                        $meta->updateMeta((int) $post['post_id']);
                    }

                    $sendData = array("board" => isset($data['board']) ? $data['board'] : '',
                        "group" => isset($data['group']) ? $data['group'] : '',
                        "custom_title" => isset($data['custom_title']) ? strip_tags($data['custom_title']) : '',
                        "content" => (isset($data['content']) && !empty($data['content'])) ? strip_tags(html_entity_decode($data['content']), '<p><h1><h2><br><i><b><a><img>') : '',
                        'url' => isset($data['url']) ? $data['url'] : '',
                        'image_url' => isset($data['image_url']) ? trim($data['image_url']) : '',
                        'tags' => isset($data['tags']) ? $data['tags'] : array(),
                        'network_id' => isset($data['network_id']) ? $data['network_id'] : '',
                        'network_type' => isset($data['network_type']) ? $data['network_type'] : '',
                        'network_tos_group_id' => (isset($data['network_tos_group_id']) && !empty($data['network_tos_group_id'])) ? $data['network_tos_group_id'] : '',
                        'network_kind' => isset($data['network_kind']) ? (int) $data['network_kind'] : 0,
                        'marketplace_category' => isset($data['marketplace_category']) ? (int) $data['marketplace_category'] : 0,
                        'marketplace_type' => isset($data['marketplace_type']) ? (int) $data['marketplace_type'] : 0,
                        'network_display_name' => isset($data['network_display_name']) ? $data['network_display_name'] : '',
                        'network_auth_id' => $networkAuthId,
                        'post_format' => isset($data['post_format']) ? (int) $data['post_format'] : '',
                        'post_for_approve' => isset($post['post_for_approve']) ? (int) $post['post_for_approve'] : 0,
                        'user_timezone' => isset($post['user_timezone']) ? $post['user_timezone'] : 0,
                        'sched_details_id' => isset($post['sched_details_id']) ? $post['sched_details_id'] : null,
                        'publish_date' => isset($post['publish_date']) ? date('Y-m-d H:i:s', strtotime($post['publish_date'])) : date('Y-m-d H:i:s', current_time('timestamp'))
                    );

                    if (isset($data['date'][0]) && isset($data['time'][0])) {
                        $sched_date = B2S_Util::getCustomDateFormat(date('Y-m-d H:i:00', strtotime($data['date'][0] . ' ' . $data['time'][0])), substr(B2S_LANGUAGE, 0, 2));
                        $schedData = array(
                            'date' => isset($data['date']) ? $data['date'] : array(),
                            'time' => isset($data['time']) ? $data['time'] : array(),
                            'releaseSelect' => 1,
                            'user_timezone' => isset($post['user_timezone']) ? $post['user_timezone'] : 0,
                            'saveSetting' => isset($data['saveSchedSetting']) ? true : false
                        );

                        $b2sShipSend->saveSchedDetails(array_merge($defaultPostData, $sendData), $schedData, array());

//is post for relay ?
//get all relays in primary post id by b2s id & change sched_date + utc
                        if (isset($post['post_for_relay']) && (int) $post['post_for_relay'] == 1 && isset($data['date'][0]) && isset($data['time'][0]) && (int) $b2s_id > 0) {
                            $res = $this->getAllRelayByPrimaryPostId($b2s_id);
                            if (is_array($res) && !empty($res)) {
                                foreach ($res as $item) {
                                    if (isset($item->id) && (int) $item->id > 0 && isset($item->relay_delay_min) && (int) $item->relay_delay_min > 0) {
                                        $relay_sched_date = date('Y-m-d H:i:00', strtotime("+" . $item->relay_delay_min . " minutes", strtotime($data['date'][0] . ' ' . $data['time'][0])));
                                        $relay_sched_date_utc = date('Y-m-d H:i:00', strtotime(B2S_Util::getUTCForDate($relay_sched_date, $post['user_timezone'] * (-1))));
                                        $wpdb->update('b2s_posts', array(
                                            'user_timezone' => $post['user_timezone'],
                                            'publish_date' => "0000-00-00 00:00:00",
                                            'sched_date' => $relay_sched_date,
                                            'sched_date_utc' => $relay_sched_date_utc,
                                            'hook_action' => 2
                                                ), array("id" => $item->id), array('%s', '%s', '%s', '%s', '%d'));
                                    }
                                }
                            }
                        }
                    }
                }

                delete_option("B2S_PLUGIN_CALENDAR_BLOCKED_" . $b2s_id);
            }
        }
        echo json_encode(array('result' => true, 'date' => $sched_date));
        wp_die();
    }

    public function getAllRelayByPrimaryPostId($primary_post_id = 0) {
        global $wpdb;
        $sqlData = $wpdb->prepare("SELECT `id`, `relay_delay_min` FROM `b2s_posts` WHERE `hide` = 0 AND `sched_type` = 4  AND `b2s_posts`.`publish_date` = '0000-00-00 00:00:00' AND `relay_primary_post_id` = %d ", $primary_post_id);
        return $wpdb->get_results($sqlData);
    }

    public function releaseLocks() {
        require_once(B2S_PLUGIN_DIR . 'includes/Options.php');
        $options = new B2S_Options(get_current_user_id());
        $lock = $options->_getOption("B2S_PLUGIN_USER_CALENDAR_BLOCKED");

        if (isset($_POST['post_id']) && (int) $_POST['post_id'] > 0) {
            delete_option('B2S_PLUGIN_POST_META_TAGES_TWITTER_' . (int) $_POST['post_id']);
            delete_option('B2S_PLUGIN_POST_META_TAGES_OG_' . (int) $_POST['post_id']);
        }
        if ($lock) {
            delete_option("B2S_PLUGIN_CALENDAR_BLOCKED_" . $lock);
            $options->_setOption("B2S_PLUGIN_USER_CALENDAR_BLOCKED", false);
        }
    }

    public function hideRating() {
        B2S_Rating::hide(isset($_POST['forever']));
    }

    public function hidePremiumMessage() {
        update_option("B2S_HIDE_PREMIUM_MESSAGE", true,false);
    }

    public function hideTrailMessage() {
        update_option("B2S_HIDE_TRAIL_MESSAGE", true,false);
    }

    public function hideTrailEndedMessage() {
        update_option("B2S_HIDE_TRAIL_ENDED", true,false);
    }

}
