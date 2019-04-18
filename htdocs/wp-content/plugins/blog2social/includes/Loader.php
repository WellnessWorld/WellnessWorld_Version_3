<?php

class B2S_Loader {

    public $blogUserData;
    public $lastVersion;

    public function __construct() {
        
    }

    public function load() {
        $this->call_global_hooks();
        if (is_admin()) {
            $this->call_admin_hooks();
        }
        if (!is_admin()) {
            $this->call_public_hooks();
        }
    }

    public function call_global_hooks() {

        $this->b2s_register_custom_post_type();

        require_once(B2S_PLUGIN_DIR . 'includes/Options.php');
        require_once(B2S_PLUGIN_DIR . 'includes/B2S/AutoPost.php');
        require_once(B2S_PLUGIN_DIR . 'includes/B2S/Rating.php');
        require_once(B2S_PLUGIN_DIR . 'includes/B2S/Heartbeat.php');
        require_once(B2S_PLUGIN_DIR . 'includes/B2S/Api/Post.php');
        require_once(B2S_PLUGIN_DIR . 'includes/Util.php');
        require_once(B2S_PLUGIN_DIR . 'includes/Tools.php');

        define('B2S_PLUGIN_POSTPERPAGE', '15');
        define('B2S_PLUGIN_VERSION_TYPE', serialize(array(0 => 'Free', 1 => 'Smart', 2 => 'Pro', 3 => 'Business', 4 => 'Premium')));
        define('B2S_PLUGIN_NETWORK', serialize(array(1 => 'Facebook', 2 => 'Twitter', 3 => 'Linkedin', 4 => 'Tumblr', 5 => 'Storify', 6 => 'Pinterest', 7 => 'Flickr', 8 => 'Xing', 9 => 'Diigo', 10 => 'Google+', 11 => 'Medium', 12 => 'Instagram', 13 => 'Delicious', 14 => 'Torial', 15 => 'Reddit', 16 => 'Bloglovin', 17 => 'VKontakte', 18 => 'Google My Business', 19 => 'Xing')));
        define('B2S_PLUGIN_SCHED_DEFAULT_TIMES', serialize(array(1 => array(18, 22), 2 => array(8, 10), 3 => array(8, 10), 4 => array(16, 22), 5 => array(), 6 => array(19, 22), 7 => array(7, 9), 8 => array(7, 10), 9 => array(16, 19), 10 => array(7, 10), 11 => array(16, 19), 12 => array(19, 22), 13 => array(11, 13), 14 => array(18, 22), 15 => array(8, 11), 16 => array(16, 19), 17 => array(19, 23), 18 => array(17, 18), 19 => array(7, 10))));
        define('B2S_PLUGIN_SCHED_DEFAULT_TIMES_INFO', serialize(array(1 => array(0 => array(13, 16), 1 => array(18, 22)), 2 => array(0 => array(8, 10), 1 => array(11, 13), 2 => array(16, 19)), 3 => array(0 => array(8, 10), 1 => array(16, 18)), 4 => array(), 5 => array(), 6 => array(0 => array(12, 14), 1 => array(19, 22)), 7 => array(0 => array(7, 9), 1 => array(17, 19)), 8 => array(0 => array(7, 10), 1 => array(17, 18)), 9 => array(0 => array(8, 10), 1 => array(11, 13), 2 => array(16, 19)), 10 => array(0 => array(7, 10), 1 => array(14, 15)), 11 => array(), 12 => array(0 => array(12, 14), 1 => array(19, 22)), 13 => array(0 => array(8, 10), 1 => array(11, 13), 2 => array(16, 19)), 14 => array(), 15 => array(0 => array(8, 11)), 16 => array(0 => array(16, 19)), 17 => array(0 => array(19, 23)), 18 => array(0 => array(17, 18)), 19 => array(0 => array(7, 10), 1 => array(17, 18)))));
        define('B2S_PLUGIN_NETWORK_ALLOW_PROFILE', serialize(array(1, 2, 3, 4, 5, 6, 7, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19)));
        define('B2S_PLUGIN_NETWORK_ALLOW_PAGE', serialize(array(1, 3, 10, 17, 19)));
        define('B2S_PLUGIN_NETWORK_ALLOW_GROUP', serialize(array(1, 10, 11, 17, 19)));
        define('B2S_PLUGIN_NETWORK_CROSSPOSTING_LIMIT', serialize(array(19 => array(2 => 3)))); //2=group
        define('B2S_PLUGIN_NETWORK_ALLOW_MODIFY_BOARD_AND_GROUP', serialize(array(6 => array('TYPE' => array(0), 'TITLE' => __('Modify pin board', 'blog2social')), 8 => array('TYPE' => array(2), 'TITLE' => __('Edit group settings', 'blog2social')), 15 => array('TYPE' => array(0), 'TITLE' => __('Modify subreddit', 'blog2social')), 19 => array('TYPE' => array(2), 'TITLE' => __('Modify forum', 'blog2social')))));
        define('B2S_PLUGIN_AUTO_POST_LIMIT', serialize(array(0 => 0, 1 => 25, 2 => 50, 3 => 100, 4 => 100)));
        define('B2S_PLUGIN_NETWORK_OAUTH', serialize(array(1, 2, 3, 4, 7, 8, 11, 15, 17, 18)));


        add_filter('heartbeat_received', array(B2S_Heartbeat::getInstance(), 'init'), 10, 2);
        add_action('wp_logout', array($this, 'releaseLocks'));
        add_action('transition_post_status', array($this, 'b2s_auto_post_import'), 9999, 3); //for Auto-Posting imported + manuell
    }

    public function call_admin_hooks() {

        require_once(B2S_PLUGIN_DIR . 'includes/Meta.php');
        require_once(B2S_PLUGIN_DIR . 'includes/B2S/PostBox.php');
        require_once(B2S_PLUGIN_DIR . 'includes/Notice.php');
        require_once(B2S_PLUGIN_DIR . 'includes/PRG/Api/Post.php');
        require_once(B2S_PLUGIN_DIR . 'includes/PRG/Api/Get.php');
        require_once(B2S_PLUGIN_DIR . 'includes/Ajax/Post.php');
        require_once(B2S_PLUGIN_DIR . 'includes/Ajax/Get.php');
        require_once(B2S_PLUGIN_DIR . 'includes/B2S/AutoPost.php');
        require_once(B2S_PLUGIN_DIR . 'includes/B2S/Rating.php');
        require_once(B2S_PLUGIN_DIR . 'includes/Util.php');
        require_once(B2S_PLUGIN_DIR . 'includes/Tools.php');

        define('B2S_PLUGIN_BLOG_USER_ID', get_current_user_id());
        define('B2S_PLUGIN_ADMIN', current_user_can('edit_others_posts'));

        $this->blogUserData = get_userdata(B2S_PLUGIN_BLOG_USER_ID);

        //deactivated since 4.2.0
        //add_action('plugins_loaded', array($this, 'update_db_check'));

        $this->update_db_check();

        add_action('admin_init', array($this, 'registerAssets'));
        add_action('admin_enqueue_scripts', array($this, 'addBootAssets'));
        add_action('admin_menu', array($this, 'createMenu'));
        add_action('admin_bar_menu', array($this, 'createToolbarMenu'), 94);
        add_action('admin_notices', array('B2S_Notice', 'getProVersionNotice'));
        add_action('admin_notices', array($this, 'b2s_save_post_alert_meta_box'));
        add_action('add_meta_boxes', array($this, 'b2s_load_post_box'));
        add_action('save_post', array($this, 'b2s_save_post_box'), 1, 3);
        add_action('trash_post', array($this, 'b2s_delete_sched_post'), 10);

        add_action('admin_footer', array($this, 'plugin_deactivate_add_modal'));
        add_filter('plugin_action_links_' . B2S_PLUGIN_BASENAME, array($this, 'override_plugin_action_links'));

        Ajax_Get::getInstance();
        Ajax_Post::getInstance();

        if ((int) B2S_PLUGIN_BLOG_USER_ID > 0) {
            $this->getToken();
            $this->getUserDetails();
        }
        $this->plugin_init_language();
    }

    public function call_public_hooks() {
        add_filter('wp_footer', array($this, 'b2s_get_full_content'), 99); //for shortcodes
        add_action('wp_head', array($this, 'b2s_build_frontend_meta'), 1); // for MetaTags
    }

    public function b2s_build_frontend_meta() {
        require_once(B2S_PLUGIN_DIR . 'includes/Meta.php');
        B2S_Meta::getInstance()->_run();
    }

    private function b2s_register_custom_post_type() {
        if (post_type_exists("b2s_ex_post")) {
            return;
        }
        register_post_type('b2s_ex_post', array('public' => false, 'label' => 'Related Posts for Blog2Social'));
    }

    public function plugin_deactivate_add_modal() {
        include_once(B2S_PLUGIN_DIR . '/views/b2s/partials/plugin-deactivate-modal.php');
    }

    public function b2s_auto_post_import($new_status, $old_status, $post) {

//is first publish
        if ($old_status != 'publish' && $old_status != 'trash' && $new_status == 'publish' && isset($post->post_author) && (int) $post->post_author > 0) {
            if (wp_is_post_revision($post->ID)) {
                return;
            }
            //is lock if manuell Auto-Posting in form
            $isLock = get_option('B2S_LOCK_AUTO_POST_IMPORT_' . (int) $post->post_author);
            if ($isLock === false) {
                $options = new B2S_Options((int) $post->post_author);
                $autoPostData = $options->_getOption('auto_post_import');
                if ($autoPostData !== false && is_array($autoPostData)) {
                    if (isset($autoPostData['active']) && (int) $autoPostData['active'] == 1) {

//Premium
                        $tokenInfo = get_option('B2S_PLUGIN_USER_VERSION_' . (int) $post->post_author);
                        if ($tokenInfo !== false && isset($tokenInfo['B2S_PLUGIN_USER_VERSION']) && (int) $tokenInfo['B2S_PLUGIN_USER_VERSION'] >= 1) {
                            $filter = true;
                            if (isset($autoPostData['post_filter']) && (int) $autoPostData['post_filter'] == 1) {
                                if (isset($autoPostData['post_type']) && is_array($autoPostData['post_type']) && !empty($autoPostData['post_type'])) {
                                    if (isset($autoPostData['post_type_state']) && (int) $autoPostData['post_type_state'] == 0) { //include
                                        if (!in_array($post->post_type, $autoPostData['post_type'])) {
                                            $filter = false;
                                        }
                                    } else { //exclude
                                        if (in_array($post->post_type, $autoPostData['post_type'])) {
                                            $filter = false;
                                        }
                                    }
                                }
                            }
                            if ($filter && isset($autoPostData['network_auth_id']) && !empty($autoPostData['network_auth_id']) && is_array($autoPostData['network_auth_id'])) {
                                //LIMIT
                                $limit = false;
                                $ship = false;
                                $optionUserTimeZone = $options->_getOption('user_time_zone');
                                $userTimeZone = ($optionUserTimeZone !== false) ? $optionUserTimeZone : get_option('timezone_string');
                                $userTimeZoneOffset = (empty($userTimeZone)) ? get_option('gmt_offset') : B2S_Util::getOffsetToUtcByTimeZone($userTimeZone);
                                $current_utc_datetime = gmdate('Y-m-d H:i:s');
                                $current_user_date = date('Y-m-d', strtotime(B2S_Util::getUTCForDate($current_utc_datetime, $userTimeZoneOffset)));

                                $autoPostCon = $options->_getOption('auto_post_import_condition');
                                $conData = array();
                                if ($autoPostCon !== false && is_array($autoPostCon) && isset($autoPostCon['count']) && isset($autoPostCon['last_call_date'])) {
                                    $con = unserialize(B2S_PLUGIN_AUTO_POST_LIMIT);
                                    $userVersion = (int) $tokenInfo['B2S_PLUGIN_USER_VERSION'];
                                    $limitCount = (isset($con[$userVersion]) && !empty($con[$userVersion])) ? $con[$userVersion] : $con[1]; //25 default
                                    if (($autoPostCon['count'] < $limitCount) || ($current_user_date != $autoPostCon['last_call_date'])) {
                                        $limit = true;
                                        $count = ($current_user_date != $autoPostCon['last_call_date']) ? 1 : $autoPostCon['count'] + 1;
                                        $conData = array('count' => $count, 'last_call_date' => $current_user_date);
                                    }
                                } else {
                                    $limit = true;
                                    $conData = array('count' => 1, 'last_call_date' => $current_user_date);
                                }
                                if (!empty($conData)) {
                                    $options->_setOption('auto_post_import_condition', $conData);
                                }

                                if ($limit) {
                                    global $wpdb;
                                    $optionPostFormat = $options->_getOption('post_format');
                                    $image_url = wp_get_attachment_url(get_post_thumbnail_id((int) $post->ID));
                                    $keywords = wp_get_post_tags((int) $post->ID); //get_the_tags((int) $post->ID);
                                    $url = get_permalink($post->ID);
                                    $title = isset($post->post_title) ? B2S_Util::getTitleByLanguage(strip_tags($post->post_title)) : '';
                                    $content = (isset($post->post_content) && !empty($post->post_content)) ? trim($post->post_content) : '';
                                    $delay = (isset($autoPostData['ship_state']) && (int) $autoPostData['ship_state'] = 0) ? 0 : (isset($autoPostData['ship_delay_time']) ? (int) $autoPostData['ship_delay_time'] : 0);
                                    $current_user_datetime = date('Y-m-d H:i:s', strtotime(B2S_Util::getUTCForDate($current_utc_datetime, $userTimeZoneOffset)));

//ShareNow
                                    $sched_type = 3;
                                    $time = ($delay == 0) ? "-30 seconds" : "+" . $delay . " minutes";
                                    $sched_date = date('Y-m-d H:i:s', strtotime($time, strtotime($current_user_datetime)));
                                    $sched_date_utc = date('Y-m-d H:i:s', strtotime($time, strtotime($current_user_datetime)));

                                    $optionNoCache = $options->_getOption('link_no_cache');
                                    $optionContentTwitter = $options->_getOption('content_network_twitter');
                                    $optionUserHashTag = $options->_getOption('user_allow_hashtag');
                                    $allowHashTag = ($optionUserHashTag === false || $optionUserHashTag == 1) ? true : false;

                                    $defaultPostData = array('default_titel' => $title,
                                        'image_url' => ($image_url !== false) ? trim(urldecode($image_url)) : '',
                                        'lang' => trim(strtolower(substr(B2S_LANGUAGE, 0, 2))),
                                        'no_cache' => (($optionNoCache === false || $optionNoCache == 0) ? 0 : 1), //default inactive , 1=active 0=not
                                        'board' => '', 'group' => '', 'url' => $url, 'user_timezone' => $userTimeZoneOffset);

                                    $defaultBlogPostData = array('post_id' => (int) $post->ID, 'blog_user_id' => (int) $post->post_author, 'user_timezone' => $userTimeZoneOffset, 'sched_type' => $sched_type, 'sched_date' => $sched_date, 'sched_date_utc' => $sched_date_utc);

                                    $autoShare = new B2S_AutoPost((int) $post->ID, $defaultBlogPostData, $current_user_date, false, $title, $content, $url, $image_url, $keywords, trim(strtolower(substr(B2S_LANGUAGE, 0, 2))), $optionPostFormat, $allowHashTag, $optionContentTwitter);

//TOS Twitter 032018 - none multiple Accounts - User select once
                                    $networkTos = true;

                                    foreach ($autoPostData['network_auth_id'] as $k => $value) {
                                        $networkDetails = $wpdb->get_results($wpdb->prepare("SELECT postNetworkDetails.network_id, postNetworkDetails.network_type, postNetworkDetails.network_display_name FROM b2s_posts_network_details AS postNetworkDetails WHERE postNetworkDetails.network_auth_id = %s", $value));
                                        if (is_array($networkDetails) && isset($networkDetails[0]->network_id) && isset($networkDetails[0]->network_type) && isset($networkDetails[0]->network_display_name)) {

//TOS Twitter 032018 - none multiple Accounts - User select once
                                            if ((int) $networkDetails[0]->network_id != 2 || ( (int) $networkDetails[0]->network_id == 2 && $networkTos)) {
//at first: set one profile
                                                if ((int) $networkDetails[0]->network_id == 2) {
                                                    $networkTos = false;
                                                }
                                                $res = $autoShare->prepareShareData($value, $networkDetails[0]->network_id, $networkDetails[0]->network_type);
                                                if ($res !== false && is_array($res)) {
                                                    $ship = true;
                                                    $res = array_merge($res, $defaultPostData);
                                                    $autoShare->saveShareData($res, $networkDetails[0]->network_id, $networkDetails[0]->network_type, $value, 0, strip_tags($networkDetails[0]->network_display_name));
                                                }
                                            }
                                        }
                                    }
                                    if ($ship) {
                                        B2S_Heartbeat::getInstance()->postToServer();
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                //Unlock Auto-Post-Import
                delete_option('B2S_LOCK_AUTO_POST_IMPORT_' . (int) $post->post_author);
            }
        }
    }

    public function update_db_check() {
        $this->lastVersion = get_option('b2s_plugin_version');
        if ($this->lastVersion == false || (int) $this->lastVersion < B2S_PLUGIN_VERSION) {
            $this->activatePlugin();
            update_option('b2s_plugin_version', B2S_PLUGIN_VERSION,false);
        }
    }

    public function b2s_delete_sched_post($post_id) {
        wp_enqueue_script('B2SPOSTSCHEDHEARTBEATJS');
        if ((int) $post_id > 0) {
            global $wpdb;
            //Heartbeat => b2s_delete_sched_post
            $sql = "SELECT id, post_for_approve FROM b2s_posts WHERE post_id = %d AND hook_action <= %d AND hide = %d AND sched_date_utc != %s AND publish_date = %s";
            $deleteData = $wpdb->get_results($wpdb->prepare($sql, $post_id, 2, 0, "0000-00-00 00:00:00", "0000-00-00 00:00:00"), ARRAY_A);
            if (is_array($deleteData) && !empty($deleteData) && isset($deleteData[0])) {
                foreach ($deleteData as $k => $value) {
                    if ((int) $value['id'] > 0) {
                        if ((int) $value['post_for_approve'] == 1) {
                            $data = array('hook_action' => '0', 'hide' => 1);
                        } else {
                            $data = array('hook_action' => '3', 'hide' => 1);
                        }
                        $where = array('id' => (int) $value['id']);
                        $wpdb->update('b2s_posts', $data, $where, array('%d'), array('%d'));
                    }
                }
            }
        }
    }

    public function b2s_get_full_content() {
        if (isset($_GET['b2s_get_full_content'])) {
            $b2sPostContent = do_shortcode(get_the_content());
            $b2sPostId = get_the_ID();
            update_option('B2S_PLUGIN_POST_CONTENT_' . $b2sPostId, $b2sPostContent,false);
        }
    }

    public function b2s_load_post_box() {

        $post_types = get_post_types(array('public' => true));
        if (is_array($post_types) && !empty($post_types)) {
            foreach ($post_types as $post_type) {
                add_meta_box('b2s-post-meta-box-auto', '<span style="padding: 10px 0 10px 25px; background: url(\'' . plugins_url('/assets/images/b2s_icon.png', B2S_PLUGIN_FILE) . '\') no-repeat left center;"></span>' . __('Auto-Post on Social Media', 'blog2social'), array($this, 'b2s_view_post_box'), $post_type, 'side', 'high');
                add_meta_box('b2s-post-box-calendar-header', '<span style="padding: 10px 0 10px 25px; background: url(\'' . plugins_url('/assets/images/b2s_icon.png', B2S_PLUGIN_FILE) . '\') no-repeat left center;"></span>' . __('Social Media Content Calendar', 'blog2social'), array($this, 'b2s_view_post_box_calendar'), $post_type, 'normal', 'high');
            }
        }
    }

    public function b2s_view_post_box() {
        wp_enqueue_style('B2SAIRDATEPICKERCSS');
        wp_enqueue_style('B2SPOSTBOXCSS');
        wp_enqueue_script('B2SAIRDATEPICKERJS');
        wp_enqueue_script('B2SAIRDATEPICKERDEJS');
        wp_enqueue_script('B2SAIRDATEPICKERENJS');
        wp_enqueue_script('B2SPOSTBOXJS');

        wp_nonce_field("b2s-meta-box-nonce-post-area", "b2s-meta-box-nonce");
        $postId = (isset($_GET['post']) && (int) $_GET['post'] > 0) ? (int) $_GET['post'] : 0;
        $postType = (isset($_GET['post_type']) && !empty($_GET['post_type'])) ? $_GET['post_type'] : get_post_type($postId);
        $postStatus = ($postId != 0) ? get_post_status($postId) : '';
        $postBox = new B2S_PostBox();
        echo $postBox->getPostBox($postId, $postType, $postStatus);
    }

    public function b2s_view_post_box_calendar() {
        wp_enqueue_style('B2SFULLCALLENDARCSS');
        wp_enqueue_style('B2SCALENDARCSS');
        wp_enqueue_script('B2SFULLCALENDARMOMENTJS');
        wp_enqueue_script('B2SFULLCALENDARJS');
        wp_enqueue_script('B2SFULLCALENDARLOCALEJS');
        wp_enqueue_script('B2SLIB');
        echo '<div class="b2s-post-box-calendar-content"></div>';
    }

    public function b2s_save_post_box() {

        if (!isset($_POST['wphb-clear-cache'])) {  // WP-Hummingbird  BTN clear cache - protection
            if (!isset($_POST['wp-preview']) || (isset($_POST['wp-preview']) && $_POST['wp-preview'] != 'dopreview')) {
                if (isset($_POST['post_ID']) && (int) $_POST['post_ID'] > 0) {
                    //Gutenberg WP V5.0 - B2S V5.1.0 optimization
                    if (!isset($_POST['post_title']) || !isset($_POST['content'])) {
                        $content = get_post((int) $_POST['post_ID']);
                        if (!isset($_POST['post_title'])) {
                            $_POST['post_title'] = $content->post_title;
                        }
                        if (!isset($_POST['content'])) {
                            $_POST['content'] = $content->post_content;
                        }
                    }

                    $b2sPostLang = (isset($_POST['b2s-user-lang']) && !empty($_POST['b2s-user-lang'])) ? $_POST['b2s-user-lang'] : 'en';
                    //OgMeta
                    if (isset($_POST['isOgMetaChecked']) && (int) $_POST['isOgMetaChecked'] == 1 && (int) $_POST['post_ID'] > 0 && isset($_POST['content']) && isset($_POST['post_title'])) {
                        $meta = B2S_Meta::getInstance();
                        $meta->getMeta(((int) $_POST['post_ID']));
                        $title = B2S_Util::getTitleByLanguage(strip_tags($_POST['post_title']), strtolower($b2sPostLang));
                        if (has_excerpt((int) $_POST['post_ID'])) {
                            $desc = strip_tags(get_the_excerpt());
                        } else {
                            $desc = str_replace("\r\n", ' ', substr(strip_tags(strip_shortcodes($_POST['content'])), 0, 160));
                        }
                        $image_url = wp_get_attachment_url(get_post_thumbnail_id((int) $_POST['post_ID']));
                        $meta->setMeta('og_title', $title);
                        $meta->setMeta('og_desc', $desc);
                        $meta->setMeta('og_image', (($image_url !== false) ? trim(urldecode($image_url)) : ''));
                        $meta->updateMeta((int) $_POST['post_ID']);
                    }

                    //CardMeta
                    if (isset($_POST['isCardMetaChecked']) && (int) $_POST['isCardMetaChecked'] == 1 && (int) $_POST['post_ID'] > 0 && isset($_POST['content']) && isset($_POST['post_title'])) {
                        $meta = B2S_Meta::getInstance();
                        $meta->getMeta(((int) $_POST['post_ID']));
                        $title = B2S_Util::getTitleByLanguage(strip_tags($_POST['post_title']), strtolower($b2sPostLang));
                        if (has_excerpt((int) $_POST['post_ID'])) {
                            $desc = strip_tags(get_the_excerpt());
                        } else {
                            $desc = str_replace("\r\n", ' ', substr(strip_tags(strip_shortcodes($_POST['content'])), 0, 160));
                        }
                        $image_url = wp_get_attachment_url(get_post_thumbnail_id((int) $_POST['post_ID']));
                        $meta->setMeta('card_title', $title);
                        $meta->setMeta('card_desc', $desc);
                        $meta->setMeta('card_image', (($image_url !== false) ? trim(urldecode($image_url)) : ''));
                        $meta->updateMeta((int) $_POST['post_ID']);
                    }

                    if (isset($_POST['post_ID']) && isset($_POST['user_ID']) && (int) $_POST['post_ID'] > 0 && (int) $_POST['user_ID'] > 0 && !defined("B2S_SAVE_META_BOX_AUTO_SHARE") && !wp_is_post_autosave($_POST['post_ID']) && isset($_POST['b2s-meta-box-nonce']) && wp_verify_nonce($_POST['b2s-meta-box-nonce'], 'b2s-meta-box-nonce-post-area') && isset($_POST['post_status']) && isset($_POST['b2s-post-meta-box-time-dropdown'])) {
                        if (strtolower($_POST['b2s-post-meta-box-time-dropdown']) == 'publish') {
                            if ((strtolower($_POST['post_status']) == "publish" || strtolower($_POST['post_status']) == "future") && isset($_POST['b2s-post-meta-box-profil-dropdown'])) {
                                $profilId = (int) $_POST['b2s-post-meta-box-profil-dropdown'];
                                if (isset($_POST['b2s-post-meta-box-profil-data-' . $profilId]) && !empty($_POST['b2s-post-meta-box-profil-data-' . $profilId])) {
                                    $networkData = json_decode(base64_decode($_POST['b2s-post-meta-box-profil-data-' . $profilId]));
                                    if ($networkData !== false && is_array($networkData) && !empty($networkData)) {
                                        $user_timezone = isset($_POST['b2s-user-timezone']) ? $_POST['b2s-user-timezone'] : 0;
                                        $current_utc_date = gmdate('Y-m-d H:i:s');
                                        $current_user_date = date('Y-m-d H:i:s', strtotime(B2S_Util::getUTCForDate($current_utc_date, $user_timezone)));

//WP User Sched Post + B2S Share NOW FRIST SAVE
                                        $post_date = '';
                                        if (isset($_POST['post_date']) && !empty($_POST['post_date'])) {
                                            $post_date = date('Y-m-d H:i:s', strtotime($_POST['post_date']));
                                        }

//WP User Sched Post + B2S Share NOW  SECOND SAVE
                                        if (empty($post_date) && strtolower($_POST['post_status']) == "future") {
                                            if (isset($_POST['mm']) && isset($_POST['jj']) && isset($_POST['aa']) && isset($_POST['hh']) && isset($_POST['mn']) && isset($_POST['ss'])) {
                                                $wp_user_sched_post_date = $_POST['aa'] . '-' . $_POST['mm'] . '-' . $_POST['jj'] . ' ' . $_POST['hh'] . ':' . $_POST['mn'] . ':' . $_POST['ss'];
                                            } else {
                                                //V5.0.0 Gutenberg Editor
                                                $wp_user_sched_post_date = get_the_date('d-m-Y', $_POST['post_ID']) . ' ' . get_the_time('H:m:s', $_POST['post_ID']);
                                            }
                                            $post_date = date('Y-m-d H:i:s', strtotime($wp_user_sched_post_date));
                                        }

//ShareNow
                                        $sched_type = 3;
                                        $sched_date = $current_user_date;
                                        $sched_date_utc = date('Y-m-d H:i:s', strtotime("-30 seconds", strtotime($current_utc_date)));
                                        $myTimeSettings = false;

//allow for User Post Date (Schedule)
                                        if (!empty($post_date) && $current_user_date <= $post_date) {
                                            $sched_type = 2;

                                            if (date('i', strtotime($post_date)) <= 30) {
                                                $sched_date = date('Y-m-d H:30:00', strtotime($post_date));
                                            } else {
                                                $sched_date = date('Y-m-d H:00:00', strtotime('+1 hours', strtotime($post_date)));
                                            }
                                            $sched_date_utc = date('Y-m-d H:i:s', strtotime(B2S_Util::getUTCForDate($sched_date, $user_timezone * (-1))));
                                        }

//Schedule post once
                                        if (isset($_POST['b2s-post-meta-box-sched-select']) && $_POST['b2s-post-meta-box-sched-select'] == 1) {
                                            $user_sched_date = (isset($_POST['b2s-post-meta-box-sched-date']) && strtotime($_POST['b2s-post-meta-box-sched-date']) !== false) ? date('Y-m-d H:i:s', strtotime($_POST['b2s-post-meta-box-sched-date'])) : date('Y-m-d H:i:00', current_time('timestamp'));
//Check User Schedule Date in past!
                                            if ($user_sched_date >= $sched_date) {
                                                $sched_type = 2;
                                                $sched_date = $user_sched_date;
                                                $sched_date_utc = date('Y-m-d H:i:s', strtotime(B2S_Util::getUTCForDate($sched_date, $user_timezone * (-1))));
                                            }
//allow for my Time Settings
                                            if (isset($_POST['b2s-post-meta-box-best-time-settings'])) {
                                                $myTimeSettings = unserialize(stripslashes($_POST['b2s-post-meta-box-best-time-settings']));
                                                if ($myTimeSettings !== false && is_array($myTimeSettings) && isset($myTimeSettings['times']) && isset($myTimeSettings['type'])) {
                                                    $myTimeSettings = $myTimeSettings;
                                                }
                                            }
                                        }

                                        delete_option('B2S_PLUGIN_POST_CONTENT_' . (int) $_POST['post_ID']);
                                        $image_url = wp_get_attachment_url(get_post_thumbnail_id((int) $_POST['post_ID']));
                                        $keywords = get_the_tags((int) $_POST['post_ID']);
                                        $url = get_permalink($_POST['post_ID']);
                                        $title = isset($_POST['post_title']) ? B2S_Util::getTitleByLanguage(strip_tags($_POST['post_title']), strtolower($b2sPostLang)) : '';
                                        $content = (isset($_POST['content']) && !empty($_POST['content'])) ? trim($_POST['content']) : '';

                                        $options = new B2S_Options((int) $_POST['user_ID']);
                                        $optionPostFormat = $options->_getOption('post_format');
                                        $optionNoCache = $options->_getOption('link_no_cache');
                                        $optionContentTwitter = $options->_getOption('content_network_twitter');
                                        $optionUserHashTag = $options->_getOption('user_allow_hashtag');
                                        $allowHashTag = ($optionUserHashTag === false || $optionUserHashTag == 1) ? true : false;

                                        $defaultPostData = array('default_titel' => $title,
                                            'image_url' => ($image_url !== false) ? trim(urldecode($image_url)) : '',
                                            'lang' => trim(strtolower(substr(B2S_LANGUAGE, 0, 2))),
                                            'no_cache' => (($optionNoCache === false || $optionNoCache == 0) ? 0 : 1), //default inactive , 1=active 0=not
                                            'board' => '', 'group' => '', 'url' => $url, 'user_timezone' => $user_timezone); // 'publish_date' => $sched_date, OLD FOR Share Now?

                                        $defaultBlogPostData = array('post_id' => (int) $_POST['post_ID'], 'blog_user_id' => (int) $_POST['user_ID'], 'user_timezone' => $user_timezone, 'sched_type' => $sched_type, 'sched_date' => $sched_date, 'sched_date_utc' => $sched_date_utc);

                                        $autoShare = new B2S_AutoPost((int) $_POST['post_ID'], $defaultBlogPostData, $current_user_date, $myTimeSettings, $title, $content, $url, $image_url, $keywords, $b2sPostLang, $optionPostFormat, $allowHashTag, $optionContentTwitter);
                                        define('B2S_SAVE_META_BOX_AUTO_SHARE', $_POST['post_ID']);
                                        if (isset($_POST['b2s-user-last-selected-profile-id']) && (int) $_POST['b2s-user-last-selected-profile-id'] != (int) $_POST['b2s-post-meta-box-profil-dropdown'] && (int) $_POST['b2s-post-meta-box-profil-dropdown'] != 0) {
                                            update_option('B2S_PLUGIN_SAVE_META_BOX_AUTO_SHARE_PROFILE_USER_' . $_POST['user_ID'], (int) $_POST['b2s-post-meta-box-profil-dropdown'],false);
                                        }

                                        $metaOg = false;
                                        $metaCard = false;
                                        $tosCrossPosting = unserialize(B2S_PLUGIN_NETWORK_CROSSPOSTING_LIMIT);

                                        //TOS Twitter 032018 - none multiple Accounts - User select once
                                        $selectedTwitterProfile = (isset($_POST['b2s-post-meta-box-profil-dropdown-twitter']) && !empty($_POST['b2s-post-meta-box-profil-dropdown-twitter'])) ? (int) $_POST['b2s-post-meta-box-profil-dropdown-twitter'] : '';
                                        foreach ($networkData as $k => $value) {
                                            if (isset($value->networkAuthId) && (int) $value->networkAuthId > 0 && isset($value->networkId) && (int) $value->networkId > 0 && isset($value->networkType)) {
                                                //TOS Twitter 032018 - none multiple Accounts - User select once
                                                if ((int) $value->networkId != 2 || ((int) $value->networkId == 2 && (empty($selectedTwitterProfile) || ((int) $selectedTwitterProfile == (int) $value->networkAuthId)))) {

                                                    //TOS Crossposting ignore
                                                    //Filter: TOS Crossposting ignore
                                                    if (isset($tosCrossPosting[$value->networkId][$value->networkType])) {
                                                        continue;
                                                    }

                                                    $res = $autoShare->prepareShareData($value->networkAuthId, $value->networkId, $value->networkType);
                                                    if ($res !== false && is_array($res)) {
                                                        $res = array_merge($res, $defaultPostData);
                                                        $shareApprove = (isset($value->instant_sharing) && (int) $value->instant_sharing == 1) ? 1 : 0;
                                                        $autoShare->saveShareData($res, $value->networkId, $value->networkType, $value->networkAuthId, $shareApprove, strip_tags($value->networkUserName));

                                                        //Start - Change/Set MetaTags
                                                        //TODO Check Enable Feature
                                                        if ((int) $value->networkId == 1 && $metaOg == false && (int) $_POST['post_ID'] > 0 && isset($res['post_format']) && (int) $res['post_format'] == 0) {  //LinkPost
                                                            $metaOg = true;
                                                            $meta = B2S_Meta::getInstance();
                                                            $meta->getMeta((int) $_POST['post_ID']);
                                                            if (isset($res['image_url']) && !empty($res['image_url'])) {
                                                                $meta->setMeta('og_image', trim($res['image_url']));
                                                                $meta->updateMeta((int) $_POST['post_ID']);
                                                            }
                                                        }
                                                        if ((int) $value->networkId == 2 && $metaCard == false && (int) $_POST['post_ID'] > 0 && isset($res['post_format']) && (int) $res['post_format'] == 0) {  //LinkPost
                                                            $metaCard = true;
                                                            $meta = B2S_Meta::getInstance();
                                                            $meta->getMeta((int) $_POST['post_ID']);
                                                            if (isset($res['image_url']) && !empty($res['image_url'])) {
                                                                $meta->setMeta('card_image', trim($res['image_url']));
                                                                $meta->updateMeta((int) $_POST['post_ID']);
                                                            }
                                                        }
//END MetaTags
                                                    }
                                                }
                                            }
                                        }
                                        if ($sched_type != 3) {
                                            if (isset($_POST['b2s-user-lang']) && !empty($_POST['b2s-user-lang'])) {
                                                $dateFormat = ($_POST['b2s-user-lang'] == 'de') ? 'd.m.Y' : 'Y-m-d';
                                                $_POST['b2s_update_publish_date'] = date($dateFormat, strtotime($sched_date));
                                            }
                                        }
                                        add_filter('redirect_post_location', array($this, 'b2s_add_param_auto_share_meta_box'));
                                    }
                                } else {
                                    add_filter('redirect_post_location', array($this, 'b2s_add_param_auto_share_error_data_meta_box'));
                                }
                            } else {
                                if (strtolower($_POST['post_status']) == "publish" || strtolower($_POST['post_status']) == "future") {
                                    add_filter('redirect_post_location', array($this, 'b2s_add_param_auto_share_error_meta_box'));
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public function b2s_add_param_auto_share_meta_box($location) {
        remove_filter('redirect_post_location', array($this, 'b2s_add_param_auto_share_meta_box'));
        if (isset($_POST['b2s_update_publish_date'])) {
            return add_query_arg(array('b2s_action' => 1, 'b2s_update_publish_date' => $_POST['b2s_update_publish_date']), $location);
        }
        return add_query_arg(array('b2s_action' => 1), $location);
    }

    public function b2s_add_param_auto_share_error_meta_box($location) {
        remove_filter('redirect_post_location', array($this, 'b2s_add_param_auto_share_error_meta_box'));
        return add_query_arg(array('b2s_action' => 2), $location);
    }

    public function b2s_add_param_auto_share_error_data_meta_box($location) {
        remove_filter('redirect_post_location', array($this, 'b2s_add_param_auto_share_error_data_meta_box'));
        return add_query_arg(array('b2s_action' => 3), $location);
    }

    public function b2s_save_post_alert_meta_box() {
        if (isset($_GET['b2s_action'])) {
            $b2sAction = $_GET['b2s_action'];
            if ((int) $b2sAction == 1) {
                $b2sLink = get_option('siteurl') . ((substr(get_option('siteurl'), -1, 1) == '/') ? '' : '/') . 'wp-admin/admin.php?page=';
                if (isset($_GET['b2s_update_publish_date']) && !empty($_GET['b2s_update_publish_date'])) {
                    $publishDate = htmlspecialchars($_GET['b2s_update_publish_date']);
                    echo '<div class="updated"><p>' . __('This post will be shared into your social media from', 'blog2social') . ' ' . $publishDate . ' <a target="_blank" href="' . $b2sLink . 'blog2social-sched">' . __('show details', 'blog2social') . '</a></p></div>';
                } else {
                    echo '<div class="updated"><p>' . __('This post will be shared on social media in 2-3 minutes!', 'blog2social') . ' <a target="_blank" href="' . $b2sLink . 'blog2social-publish">' . __('show details', 'blog2social') . '</a></p></div>';
                }
            }
            if ((int) $b2sAction == 2) {
                echo '<div class="error"><p>' . __('Please, make sure that your post are publish on this blog on this moment. Then you can auto post your post with Blog2social.', 'blog2social') . '</p></div>';
            }
            if ((int) $b2sAction == 3) {
                echo '<div class="error"><p>' . __('There are no authorizations for your selected profile. Please, authorize with a social network or select a other profile.', 'blog2social') . '</p></div>';
            }
        }
    }

    public function plugin_init_language() {
        load_plugin_textdomain('blog2social', false, B2S_PLUGIN_LANGUAGE_PATH);
        $this->defineText();
    }

    public function override_plugin_action_links($links) {
        if (defined("B2S_PLUGIN_USER_VERSION") && B2S_PLUGIN_USER_VERSION == 0) {
            $added_link = array('<a target="_blank" style="color: rgba(10, 154, 62, 1); font-weight: bold; font-size: 13px;" href="' . B2S_Tools::getSupportLink('affiliate') . '">' . __('Upgrade to Premium', 'blog2social') . '</a>');
            $links = array_merge($added_link, $links);
        }

        if (!isset($links['deactivate'])) {
            return $links;
        }//end if

        if (is_network_admin()) {
            return $links;
        }//end if

        preg_match_all('/<a[^>]+href="(.+?)"[^>]*>/i', $links['deactivate'], $matches);
        if (empty($matches) || !isset($matches[1][0])) {
            return $links;
        }//end if

        if (isset($matches[1][0])) {
            $links['deactivate'] = sprintf(
                    '<a id="b2s-deactivate" href="%s">%s</a>', $matches[1][0], // @codingStandardsIgnoreLine
                    esc_html(_x('Deactivate', 'command (plugins)', 'Blog2Social'))
            );
        }
        wp_enqueue_style('B2SPOSTBOXCSS');
        wp_enqueue_script('B2SPOSTBOXJS');

        return $links;
    }

    public function defineText() {
        define('B2S_PLUGIN_NETWORK_TYPE', serialize(array(__('Profile', 'blog2social'), __('Page', 'blog2social'), __('Group', 'blog2social'))));
        define('B2S_PLUGIN_NETWORK_KIND', serialize(array(__('Company', 'blog2social'), __('Business', 'blog2social'))));
        define('B2S_PLUGIN_NETWORK_ERROR', serialize(array('DEFAULT' => __('Your post could not be posted.', 'blog2social'),
            'TOKEN' => __('Your authorization has expired. Please reconnect your account in the Blog2Social network settings.', 'blog2social'),
            'CONTENT' => __('The network has marked the post as spam or abusive.', 'blog2social'),
            'RIGHT' => __('We don\'t have the permission to publish your post. Please check your authorization.', 'blog2social'),
            'LOGIN' => __('Your authorization is interrupted. Please check your authorization. Please see <a target="_blank" href="https://www.blog2social.com/en/faq/category/9/troubleshooting-for-error-messages.html">FAQ</a>.', 'blog2social'),
            'LIMIT' => __('Your daily limit has been reached.', 'blog2social'),
            'IMAGE' => __('Your post could not be posted, because your image is not available.', 'blog2social'),
            'PROTECT' => __('The network has blocked your account. Please see <a target="_blank" href="https://www.blog2social.com/en/faq/category/9/troubleshooting-for-error-messages.html">FAQ</a>.', 'blog2social'),
            'IMAGE_LIMIT' => __('The number of images is reached. Please see <a target="_blank" href="https://www.blog2social.com/en/faq/category/9/troubleshooting-for-error-messages.html">FAQ</a>.', 'blog2social'),
            'RATE_LIMIT' => __('Your daily limit for this network has been reached. Please try again later.', 'blog2social'),
            'INVALID_CONTENT' => __('The network can not publish special characters such as Emoji. Please see <a target="_blank" href="https://www.blog2social.com/en/faq/category/9/troubleshooting-for-error-messages.html">FAQ</a>.', 'blog2social'),
            'EXISTS_CONTENT' => __('Your post is a duplicate.', 'blog2social'),
            'URL_CONTENT' => __('The network requires a public url.', 'blog2social'),
            'EXISTS_RELAY' => __('You have already retweeted this post.', 'blog2social'),
            'DEPRECATED_NETWORK_8' => __('This XING API is no longer supported by XING. Please connect your XING accounts with the new XING interface to reschedule your posts. <a target="_blank" href="https://www.blog2social.com/en/faq/index.php?action=artikel&cat=2&id=146">Learn more</a>', 'blog2social'),
            'IMAGE_FOR_CURATION' => __('There was no image in the meta data of the linked post. Posts without images cannot be shared on image networks.', 'blog2social'), // special for content curation V.5.0.0
            'GROUP_CONTENT' => __('Your group can not be found by the network.', 'blog2social'))));
    }

    public function getToken() {
        global $wpdb;
        $sql = $wpdb->prepare("SELECT token FROM `b2s_user` WHERE `blog_user_id` = %d", $this->blogUserData->ID);
        $userExist = $wpdb->get_row($sql);
        if (empty($userExist) || !isset($userExist->token)) {
            $postData = array('action' => 'getToken', 'blog_user_id' => $this->blogUserData->ID, 'blog_url' => get_option('home'), 'email' => $this->blogUserData->user_email);
            $result = json_decode(B2S_Tools::getToken($postData));
            if (isset($result->result) && (int) $result->result == 1 && isset($result->token)) {
                $state_url = (isset($result->state_url)) ? (int) $result->state_url : 0;
                $sqlInsertToken = $wpdb->prepare("INSERT INTO `b2s_user` (`token`, `blog_user_id`,`register_date`,`state_url`) VALUES (%s,%d,%s,%d);", $result->token, (int) $this->blogUserData->ID, date('Y-m-d H:i:s'), $state_url);
                $wpdb->query($sqlInsertToken);
                define('B2S_PLUGIN_TOKEN', $result->token);
            } else {
                define('B2S_PLUGIN_NOTICE', 'CONNECTION');
            }
        } else {
            define('B2S_PLUGIN_TOKEN', $userExist->token);
        }
    }

    public function getUserDetails() {
        $tokenInfo = get_option('B2S_PLUGIN_USER_VERSION_' . B2S_PLUGIN_BLOG_USER_ID);
        if ($tokenInfo == false || !isset($tokenInfo['B2S_PLUGIN_USER_VERSION']) || !isset($tokenInfo['B2S_PLUGIN_VERSION']) || $tokenInfo['B2S_PLUGIN_USER_VERSION_NEXT_REQUEST'] < time() || (isset($tokenInfo['B2S_PLUGIN_VERSION']) && (int) $tokenInfo['B2S_PLUGIN_VERSION'] < (int) B2S_PLUGIN_VERSION) || (isset($tokenInfo['B2S_PLUGIN_TRAIL_END']) && strtotime($tokenInfo['B2S_PLUGIN_TRAIL_END']) < strtotime(gmdate('Y-m-d H:i:s')))) {
            B2S_Tools::setUserDetails();
            $this->checkUpdate();
        } else {
            define('B2S_PLUGIN_USER_VERSION', $tokenInfo['B2S_PLUGIN_USER_VERSION']);
            if (isset($tokenInfo['B2S_PLUGIN_TRAIL_END'])) {
                define('B2S_PLUGIN_TRAIL_END', $tokenInfo['B2S_PLUGIN_TRAIL_END']);
            }
        }
    }

    private function checkUpdate() {
        $args = array(
            'timeout' => '15',
            'redirection' => '5',
            'user-agent' => "Blog2Social/" . B2S_PLUGIN_VERSION . " (Wordpress/Plugin)"
        );
        $result = wp_remote_retrieve_body(wp_remote_get(B2S_PLUGIN_API_ENDPOINT . 'update.txt', $args));
        $currentVersion = explode('#', $result);
        if (isset($currentVersion[0]) && (int) $currentVersion[0] > (int) B2S_PLUGIN_VERSION) {
            define('B2S_PLUGIN_NOTICE', 'UPDATE');
        }
    }

    public function createMenu() {
        $subPages = array();
//pageTitle,menutitle,$capability, $menu_slug, $function, $icon_url, $position
        add_menu_page('Blog2Social', 'Blog2Social', 'read', 'blog2social', null, plugins_url('/assets/images/b2s_icon.png', B2S_PLUGIN_FILE));
//$parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function
        $subPages[] = add_submenu_page('blog2social', __('Dashboard', 'blog2social'), __('Dashboard', 'blog2social'), 'read', 'blog2social', array($this, 'b2sstart'));
        $subPages[] = add_submenu_page('blog2social', __('Posts & Sharing', 'blog2social'), __('Posts & Sharing', 'blog2social'), 'read', 'blog2social-post', array($this, 'b2sPost'));
        $subPages[] = add_submenu_page('blog2social', __('Content Curation', 'blog2social'), __('Content Curation', 'blog2social'), 'read', 'blog2social-curation', array($this, 'b2sContentCuration'));
        $subPages[] = add_submenu_page('blog2social', __('Networks', 'blog2social'), __('Networks', 'blog2social'), 'read', 'blog2social-network', array($this, 'b2sNetwork'));
        $subPages[] = add_submenu_page('blog2social', __('Settings', 'blog2social'), __('Settings', 'blog2social'), 'read', 'blog2social-settings', array($this, 'b2sSettings'));
        $subPages[] = add_submenu_page('blog2social', __('PR-Service', 'blog2social'), __('PR-Service', 'blog2social'), 'read', 'prg-post', array($this, 'prgPost'));
        $subPages[] = add_submenu_page('blog2social', __('How to & FAQ', 'blog2social'), __('How to & FAQ', 'blog2social'), 'read', 'blog2social-howto', array($this, 'b2sHowTo'));
        $subPages[] = add_submenu_page('blog2social', __('Premium', 'blog2social'), '<span class="dashicons dashicons-star-filled"></span> ' . __('PREMIUM', 'blog2social'), 'read', 'blog2social-premium', array($this, 'b2sPremium'));
        $subPages[] = add_submenu_page(null, 'B2S Post Sched', 'B2S Post Sched', 'read', 'blog2social-sched', array($this, 'b2sPostSched'));
        $subPages[] = add_submenu_page(null, 'B2S Post Approve', 'B2S Post Approve', 'read', 'blog2social-approve', array($this, 'b2sPostApprove'));
        $subPages[] = add_submenu_page(null, 'B2S Post Calendar', 'B2S Post Calendar', 'read', 'blog2social-calendar', array($this, 'b2sPostCalendar'));
        $subPages[] = add_submenu_page(null, 'B2S Post Publish', 'B2S Post Publish', 'read', 'blog2social-publish', array($this, 'b2sPostPublish'));
        $subPages[] = add_submenu_page(null, 'B2S Post Notice', 'B2S Post Notice', 'read', 'blog2social-notice', array($this, 'b2sPostNotice')); //Error post page since 4.8.0
        $subPages[] = add_submenu_page(null, 'B2S Ship', 'B2S Ship', 'read', 'blog2social-ship', array($this, 'b2sShip'));
        $subPages[] = add_submenu_page(null, 'PRG Login', 'PRG Login', 'read', 'prg-login', array($this, 'prgLogin'));
        $subPages[] = add_submenu_page(null, 'PRG Ship', 'PRG Ship', 'read', 'prg-ship', array($this, 'prgShip'));
        foreach ($subPages as $var) {
            add_action($var, array($this, 'addAssets'));
        }
    }

    public function createToolbarMenu() {
        if (!current_user_can('edit_posts')) {
            return;
        }
        global $wp_admin_bar;
        $seo_url = strtolower(get_admin_url(null, 'admin.php?page='));
        $title = '<div id="blog2social-ab-icon" class="ab-item" style="padding-left: 25px; background-repeat: no-repeat; background-size: 16px auto; background-position: left center; background-image: url(\'' . plugins_url('/assets/images/b2s_icon.png', B2S_PLUGIN_FILE) . '\');">' . __('Blog2Social', 'blog2social') . '</div>';
        $wp_admin_bar->add_node(array(
            'id' => 'blog2social',
            'title' => $title,
            'href' => $seo_url . 'blog2social'
        ));

        $wp_admin_bar->add_node(array(
            'id' => 'blog2social-dashboard',
            'title' => __('Dashboard', 'blog2social'),
            'href' => $seo_url . 'blog2social',
            'parent' => 'blog2social'
        ));

        $wp_admin_bar->add_node(array(
            'id' => 'blog2social-post',
            'title' => __('Posts & Sharing', 'blog2social'),
            'href' => $seo_url . 'blog2social-post',
            'parent' => 'blog2social'
        ));

        $wp_admin_bar->add_node(array(
            'id' => 'blog2social-curation',
            'title' => __('Content Curation', 'blog2social'),
            'href' => $seo_url . 'blog2social-curation',
            'parent' => 'blog2social'
        ));

        $wp_admin_bar->add_node(array(
            'id' => 'blog2social-network',
            'title' => __('Networks', 'blog2social'),
            'href' => $seo_url . 'blog2social-network',
            'parent' => 'blog2social'
        ));
        $wp_admin_bar->add_node(array(
            'id' => 'blog2social-settings',
            'title' => __('Settings', 'blog2social'),
            'href' => $seo_url . 'blog2social-settings',
            'parent' => 'blog2social'
        ));
        $wp_admin_bar->add_node(array(
            'id' => 'blog2social-prg-post',
            'title' => __('PR-Service', 'blog2social'),
            'href' => $seo_url . 'prg-post',
            'parent' => 'blog2social'
        ));

        $wp_admin_bar->add_node(array(
            'id' => 'blog2social-howto',
            'title' => __('How to & FAQ', 'blog2social'),
            'href' => $seo_url . 'blog2social-howto',
            'parent' => 'blog2social'
        ));
        $wp_admin_bar->add_node(array(
            'id' => 'blog2social-premium',
            'title' => '<span class="ab-icon dashicons dashicons-star-filled"></span> ' . __('PREMIUM', 'blog2social'),
            'href' => $seo_url . 'blog2social-premium',
            'parent' => 'blog2social'
        ));
    }

//PageFunktion
    public function b2sstart() {

        if (B2S_Tools::showNotice() == false) {
            wp_enqueue_style('B2SFULLCALLENDARCSS');
            wp_enqueue_style('B2SCALENDARCSS');
            wp_enqueue_script('B2SFULLCALENDARMOMENTJS');
            wp_enqueue_script('B2SFULLCALENDARJS');
            wp_enqueue_script('B2SFULLCALENDARLOCALEJS');
            wp_enqueue_script('B2SLIB');

            wp_enqueue_script('B2SMOMENT');
            wp_enqueue_script('B2SCHARTJS');
            wp_enqueue_style('B2SSTARTCSS');
            wp_enqueue_script('B2SSTARTJS');
            wp_enqueue_style('B2SPOSTCSS');
            wp_enqueue_script('B2SPOSTJS');
            wp_enqueue_style('B2SAIRDATEPICKERCSS');
            wp_enqueue_script('B2SAIRDATEPICKERJS');
            wp_enqueue_script('B2SAIRDATEPICKERDEJS');
            wp_enqueue_script('B2SAIRDATEPICKERENJS');
            require_once( B2S_PLUGIN_DIR . 'views/b2s/dashboard.php');
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

//PageFunktion
    public function b2sPost() {
        if (B2S_Tools::showNotice() == false) {
            wp_enqueue_style('B2SPOSTCSS');
            wp_enqueue_script('B2SPOSTJS');
            require_once( B2S_PLUGIN_DIR . 'views/b2s/post.php');
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

    //Page Curation
    public function b2sContentCuration() {
        if (B2S_Tools::showNotice() == false) {
            wp_enqueue_style('B2SCURATIONCSS');
            wp_enqueue_script('B2SCURATIONJS');
            wp_enqueue_style('B2SAIRDATEPICKERCSS');
            wp_enqueue_script('B2SAIRDATEPICKERJS');
            wp_enqueue_script('B2SAIRDATEPICKERDEJS');
            wp_enqueue_script('B2SAIRDATEPICKERENJS');
            require_once( B2S_PLUGIN_DIR . 'views/b2s/curation.php');
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

//PageFunktion
    public function b2sNetwork() {
        if (B2S_Tools::showNotice() == false) {
            wp_enqueue_style('B2SNETWORKCSS');
            wp_enqueue_script('B2SNETWORKJS');
            wp_enqueue_style('B2STIMEPICKERCSS');
            wp_enqueue_script('B2STIMEPICKERJS');
            require_once( B2S_PLUGIN_DIR . 'views/b2s/network.php');
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

//PageFunktion
    public function b2sSettings() {
        if (B2S_Tools::showNotice() == false) {
            wp_enqueue_style('B2SSETTINGSCSS');
            wp_enqueue_script('B2SSETTINGSJS');
            wp_enqueue_style('B2STIMEPICKERCSS');
            wp_enqueue_script('B2STIMEPICKERJS');
            wp_enqueue_style('B2SBTNTOOGLECSS');
            wp_enqueue_script('B2SBTNTOOGLEJS');
            wp_enqueue_style('B2SCHOSENCSS');
            wp_enqueue_script('B2SCHOSENJS');

            if (current_user_can('upload_files')) {
//Capability by Super Admin ,Administrator ,Editor ,Author
                wp_enqueue_media();
            }
            require_once( B2S_PLUGIN_DIR . 'views/b2s/settings.php');
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

//PageFunktion
    public function b2sShip() {
        if (B2S_Tools::showNotice() == false) {

            wp_enqueue_style('B2SFULLCALLENDARCSS');
            wp_enqueue_style('B2SCALENDARCSS');
            wp_enqueue_script('B2SFULLCALENDARMOMENTJS');
            wp_enqueue_script('B2SFULLCALENDARJS');
            wp_enqueue_script('B2SFULLCALENDARLOCALEJS');
            wp_enqueue_script('B2SLIB');
            wp_enqueue_style('B2SSHIPCSS');
            wp_enqueue_style('B2SDATEPICKERCSS');
            wp_enqueue_style('B2STIMEPICKERCSS');
            wp_enqueue_style('B2SWYSIWYGCSS');
            wp_enqueue_script('B2SWYSIWYGJS');
            if (substr(B2S_LANGUAGE, 0, 2) == 'de') {
                wp_enqueue_script('B2SWYSIWYGLANGDEJS');
            } else {
                wp_enqueue_script('B2SWYSIWYGLANGENJS');
            }
            wp_enqueue_script('B2SDATEPICKERJS');
            wp_enqueue_script('B2SDATEPICKERDEJS');
            wp_enqueue_script('B2SDATEPICKERENJS');
            wp_enqueue_script('B2STIMEPICKERJS');
            wp_enqueue_script('B2SSHIPJS');
            if (current_user_can('upload_files')) {
//Capability by Super Admin ,Administrator ,Editor ,Author
                wp_enqueue_media();
            }
            require_once( B2S_PLUGIN_DIR . 'views/b2s/ship.php');
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

//PageFunktion
    public function prgLogin() {
        if (B2S_Tools::showNotice() == false) {
            $prgInfo = get_option('B2S_PLUGIN_PRG_' . B2S_PLUGIN_BLOG_USER_ID);
            if ($prgInfo != false && isset($prgInfo['B2S_PRG_ID']) && (int) $prgInfo['B2S_PRG_ID'] > 0 && isset($prgInfo['B2S_PRG_TOKEN']) && !empty($prgInfo['B2S_PRG_TOKEN'])) {
                $postId = (int) $_GET['postId'];
                echo'<script> window.location="' . admin_url('/admin.php?page=prg-ship&postId=' . $postId, 'http') . '"; </script> ';
//wp_redirect(admin_url('/admin.php?page=prg-ship&postId=' . $postId, 'http'), 301);
                wp_die();
            } else {
                wp_enqueue_style('PRGLOGINCSS');
                wp_enqueue_script('PRGLOGINJS');
                require_once( B2S_PLUGIN_DIR . 'views/prg/login.php');
            }
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

//PageFunktion
    public function prgShip() {
        if (B2S_Tools::showNotice() == false) {
            wp_enqueue_style('PRGSHIPCSS');
            wp_enqueue_script('PRGSHIPJS');
            wp_enqueue_script('PRGGENERALJS');
            require_once( B2S_PLUGIN_DIR . 'views/prg/ship.php');
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

//PageFunktion
    public function b2sPostSched() {
        if (B2S_Tools::showNotice() == false) {
            wp_enqueue_style('B2SPOSTSCHEDCSS');
            wp_enqueue_style('B2SDATEPICKERCSS');
            wp_enqueue_style('B2STIMEPICKERCSS');
            wp_enqueue_script('B2SDATEPICKERJS');
            wp_enqueue_script('B2SDATEPICKERDEJS');
            wp_enqueue_script('B2SDATEPICKERENJS');
            wp_enqueue_script('B2STIMEPICKERJS');
            wp_enqueue_script('B2SPOSTJS');

            wp_enqueue_style('B2SWYSIWYGCSS');
            wp_enqueue_script('B2SLIB');
            wp_enqueue_script('B2SWYSIWYGJS');
            wp_enqueue_script('B2SSHIPJS');
            if (substr(B2S_LANGUAGE, 0, 2) == 'de') {
                wp_enqueue_script('B2SWYSIWYGLANGDEJS');
            } else {
                wp_enqueue_script('B2SWYSIWYGLANGENJS');
            }
            require_once( B2S_PLUGIN_DIR . 'views/b2s/post.sched.php');
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

    //PageFunktion
    public function b2sPostApprove() {
        if (B2S_Tools::showNotice() == false) {
            wp_enqueue_style('B2SPOSTAPPROVECSS');
            wp_enqueue_script('B2SPOSTJS');
            require_once( B2S_PLUGIN_DIR . 'views/b2s/post.approve.php');
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

//PageFunktion
    public function b2sPostCalendar() {
        if (B2S_Tools::showNotice() == false) {
            wp_enqueue_style('B2SFULLCALLENDARCSS');
            wp_enqueue_style('B2SCALENDARCSS');
            wp_enqueue_style('B2STIMEPICKERCSS');
            wp_enqueue_style('B2SWYSIWYGCSS');
            wp_enqueue_script('B2SLIB');
            wp_enqueue_script('B2SFULLCALENDARMOMENTJS');
            wp_enqueue_script('B2SFULLCALENDARJS');
            wp_enqueue_script('B2SFULLCALENDARLOCALEJS');
            wp_enqueue_script('B2SDATEPICKERJS');
            wp_enqueue_script('B2SDATEPICKERDEJS');
            wp_enqueue_script('B2STIMEPICKERJS');
            wp_enqueue_script('B2SWYSIWYGJS');
            if (substr(B2S_LANGUAGE, 0, 2) == 'de') {
                wp_enqueue_script('B2SWYSIWYGLANGDEJS');
            } else {
                wp_enqueue_script('B2SWYSIWYGLANGENJS');
            }
            if (current_user_can('upload_files')) {
//Capability by Super Admin ,Administrator ,Editor ,Author
                wp_enqueue_media();
            }
            wp_enqueue_script('B2SSHIPJS');
            wp_enqueue_script("B2SCALENDARJS");

            require_once( B2S_PLUGIN_DIR . 'views/b2s/post.calendar.php');
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

//PageFunktion
    public function b2sHowTo() {
        wp_enqueue_style('B2SHOWTO');
        wp_enqueue_script('B2SHOWTOJS');
        require_once( B2S_PLUGIN_DIR . 'views/b2s/howto.php');
    }

//PageFunktion
    public function b2sPremium() {
        wp_enqueue_style('B2SPREMIUM');
        require_once( B2S_PLUGIN_DIR . 'views/b2s/premium.php');
    }

//PageFunktion
    public function b2sPostPublish() {
        if (B2S_Tools::showNotice() == false) {
            wp_enqueue_style('B2SPOSTPUBLISHCSS');
            wp_enqueue_script('B2SPOSTJS');
            wp_enqueue_script('PRGGENERALJS');
            require_once( B2S_PLUGIN_DIR . 'views/b2s/post.publish.php');
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

//PageFunktion
    public function b2sPostNotice() {
        if (B2S_Tools::showNotice() == false) {
            wp_enqueue_style('B2SPOSTNOTICECSS');
            wp_enqueue_script('B2SPOSTJS');
            wp_enqueue_script('PRGGENERALJS');
            require_once( B2S_PLUGIN_DIR . 'views/b2s/post.notice.php');
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

//PageFunktion
    public function prgPost() {
        if (B2S_Tools::showNotice() == false) {
            wp_enqueue_style('PRGPOSTCSS');
            wp_enqueue_script('PRGPOSTJS');
            wp_enqueue_script('PRGGENERALJS');
            require_once( B2S_PLUGIN_DIR . 'views/prg/post.php');
        } else {
            require_once( B2S_PLUGIN_DIR . 'views/notice.php');
        }
    }

    public function addBootAssets($hook) {
        wp_enqueue_script('B2SVALIDATEJS');
        if ($hook == 'edit.php') {
            wp_enqueue_script('B2SPOSTSCHEDHEARTBEATJS');
        }

        if ($hook == 'plugins.php') {
            wp_enqueue_script('B2SPLUGINDEACTIVATEJS');
            wp_enqueue_style('B2SPLUGINDEACTIVATECSS');
        }
    }

    public function addAssets() {
        wp_enqueue_style('B2SBOOTCSS');
        wp_enqueue_script('B2SBOOTJS');
        wp_enqueue_script('B2SGENERALJS');
    }

    public function registerAssets() {
        wp_register_style('B2SBOOTCSS', plugins_url('assets/css/general.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SSTARTCSS', plugins_url('assets/css/b2s/start.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SPOSTCSS', plugins_url('assets/css/b2s/post.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SSHIPCSS', plugins_url('assets/css/b2s/ship.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SCURATIONCSS', plugins_url('assets/css/b2s/curation.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);

        wp_register_style('B2SPOSTSCHEDCSS', plugins_url('assets/css/b2s/post.sched.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SPOSTAPPROVECSS', plugins_url('assets/css/b2s/post.approve.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SPOSTPUBLISHCSS', plugins_url('assets/css/b2s/post.publish.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SPOSTNOTICECSS', plugins_url('assets/css/b2s/post.notice.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SNETWORKCSS', plugins_url('assets/css/b2s/network.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SHOWTO', plugins_url('assets/css/b2s/howto.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SPREMIUM', plugins_url('assets/css/b2s/premium.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SSETTINGSCSS', plugins_url('assets/css/b2s/settings.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('PRGSHIPCSS', plugins_url('assets/css/prg/ship.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('PRGLOGINCSS', plugins_url('assets/css/prg/login.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SDATEPICKERCSS', plugins_url('assets/lib/datepicker/css/bootstrap-datepicker3.min.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SAIRDATEPICKERCSS', plugins_url('assets/lib/air-datepicker/css/datepicker.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2STIMEPICKERCSS', plugins_url('assets/lib/timepicker/timepicker.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('PRGPOSTCSS', plugins_url('assets/css/prg/post.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SWYSIWYGCSS', plugins_url('assets/lib/wysiwyg/square.min.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SPOSTBOXCSS', plugins_url('assets/css/b2s/wp/post-box.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SNOTICECSS', plugins_url('assets/css/notice.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SFULLCALLENDARCSS', plugins_url('assets/lib/fullcalendar/fullcalendar.min.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SCALENDARCSS', plugins_url('assets/css/b2s/calendar.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SBTNTOOGLECSS', plugins_url('assets/lib/btn-toogle/bootstrap-toggle.min.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SCHOSENCSS', plugins_url('assets/lib/chosen/chosen.min.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_style('B2SPLUGINDEACTIVATECSS', plugins_url('assets/css/b2s/wp/plugin-deactivate.css', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);

        wp_register_script('B2SNETWORKJS', plugins_url('assets/js/b2s/network.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SSETTINGSJS', plugins_url('assets/js/b2s/settings.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SSTARTJS', plugins_url('assets/js/b2s/start.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SPOSTJS', plugins_url('assets/js/b2s/post.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SSHIPJS', plugins_url('assets/js/b2s/ship.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SCURATIONJS', plugins_url('assets/js/b2s/curation.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('PRGSHIPJS', plugins_url('assets/js/prg/ship.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('PRGLOGINJS', plugins_url('assets/js/prg/login.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SBOOTJS', plugins_url('assets/js/general.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SVALIDATEJS', plugins_url('assets/js/validate.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);

        wp_register_script('B2SHOWTOJS', plugins_url('assets/js/b2s/howto.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SGENERALJS', plugins_url('assets/js/b2s/general.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SDATEPICKERJS', plugins_url('assets/lib/datepicker/js/bootstrap-datepicker.min.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SDATEPICKERDEJS', plugins_url('assets/lib/datepicker/locales/bootstrap-datepicker.de_DE.min.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SDATEPICKERENJS', plugins_url('assets/lib/datepicker/locales/bootstrap-datepicker.en_US.min.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SAIRDATEPICKERJS', plugins_url('assets/lib/air-datepicker/js/datepicker.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SAIRDATEPICKERDEJS', plugins_url('assets/lib/air-datepicker/js/locales/datepicker.de.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SAIRDATEPICKERENJS', plugins_url('assets/lib/air-datepicker/js/locales/datepicker.en.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SCHARTJS', plugins_url('assets/lib/chartjs/Chart.bundle.min.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SMOMENT', plugins_url('assets/lib/moment/moment-with-locales.min.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2STIMEPICKERJS', plugins_url('assets/lib/timepicker/timepicker.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SNOTICEJS', plugins_url('assets/js/notice.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('PRGPOSTJS', plugins_url('assets/js/prg/post.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('PRGGENERALJS', plugins_url('assets/js/prg/general.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SWYSIWYGJS', plugins_url('assets/lib/wysiwyg/jquery.sceditor.xhtml.min.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SWYSIWYGLANGDEJS', plugins_url('assets/lib/wysiwyg/languages/de.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SWYSIWYGLANGENJS', plugins_url('assets/lib/wysiwyg/languages/en.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SPOSTBOXJS', plugins_url('assets/js/b2s/wp/post-box.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SPOSTSCHEDHEARTBEATJS', plugins_url('assets/js/b2s/wp/post-sched-heartbeat.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SCALENDARJS', plugins_url('assets/js/b2s/calendar.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SFULLCALENDARMOMENTJS', plugins_url('assets/lib/fullcalendar/lib/moment.min.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SFULLCALENDARJS', plugins_url('assets/lib/fullcalendar/fullcalendar.min.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SFULLCALENDARLOCALEJS', plugins_url('assets/lib/fullcalendar/locale-all.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SBTNTOOGLEJS', plugins_url('assets/lib/btn-toogle/bootstrap-toggle.min.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SCHOSENJS', plugins_url('assets/lib/chosen/chosen.jquery.min.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SLIB', plugins_url('assets/js/b2s/lib.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
        wp_register_script('B2SPLUGINDEACTIVATEJS', plugins_url('assets/js/b2s/wp/plugin-deactivate.js', B2S_PLUGIN_FILE), array(), B2S_PLUGIN_VERSION);
    }

    public function activatePlugin() {
        require_once (B2S_PLUGIN_DIR . 'includes/System.php');
        require_once(B2S_PLUGIN_DIR . 'includes/Options.php');
        $b2sSystem = new B2S_System();
        $b2sCheckBefore = $b2sSystem->check('before');
        if (is_array($b2sCheckBefore)) {
            $b2sSystem->deactivatePlugin();
            wp_die($b2sSystem->getErrorMessage($b2sCheckBefore) . ' ' . __('or', 'blog2social') . '  <a href="' . admin_url("/plugins.php", "http") . '/">' . __('back to install plugins', 'blog2social') . '</a>');
        }

        global $wpdb;
//Start Old Plugin
        $sqlDeleteFirst = 'DROP TABLE IF EXISTS `prg_connect_sent`';
        $wpdb->query($sqlDeleteFirst);
        $sqlDeleteSecond = 'DROP TABLE IF EXISTS `prg_connect_config`';
        $wpdb->query($sqlDeleteSecond);
//END Old Plugin
        $sqlCreateUser = "CREATE TABLE IF NOT EXISTS `b2s_user` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `token` varchar(255) NOT NULL,
          `blog_user_id` int(11) NOT NULL,
          `feature` TINYINT(2) NOT NULL,
          `state_url` TINYINT(2) NOT NULL,
          `register_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`id`), INDEX `blog_user_id` (`blog_user_id`), INDEX `token` (`token`), INDEX `feature` (`feature`)
          ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1;";
        $wpdb->query($sqlCreateUser);

        $b2sUserCols = $wpdb->get_results('SHOW COLUMNS FROM b2s_user');
        if (is_array($b2sUserCols) && isset($b2sUserCols[0])) {
            $b2sUserColsData = array();
            foreach ($b2sUserCols as $key => $value) {
                if (isset($value->Field) && !empty($value->Field)) {
                    $b2sUserColsData[] = $value->Field;
                }
            }
            if (!in_array("register_date", $b2sUserColsData)) {
                $wpdb->query("ALTER TABLE b2s_user ADD register_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'");
            }
            if (!in_array("state_url", $b2sUserColsData)) {
                $wpdb->query("ALTER TABLE b2s_user ADD state_url TINYINT(2) NOT NULL DEFAULT '1'");
            }
        }

//Notice Rating - stop rating remember again
//$wpdb->query('UPDATE `b2s_user` SET `feature` = 0');

        $keys = $wpdb->get_results('SHOW INDEX FROM `b2s_user`');
        $allowIndexUser = array('PRIMARY', 'blog_user_id', 'token', 'feature');
        foreach ($keys as $k => $value) {
            if (!in_array($value->Key_name, $allowIndexUser)) {
                $wpdb->query('ALTER TABLE `b2s_user` DROP INDEX ' . $value->Key_name);
            }
        }

        $sqlCreateUserPosts = "CREATE TABLE IF NOT EXISTS `b2s_posts` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `post_id` int(11) NOT NULL,
          `blog_user_id` int(11) NOT NULL,
          `last_edit_blog_user_id` int(11) NOT NULL,
          `user_timezone` TINYINT NOT NULL DEFAULT '0',
          `sched_details_id` INT NOT NULL,
          `sched_type` TINYINT NOT NULL DEFAULT '0',
          `sched_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
          `sched_date_utc` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
          `publish_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
          `publish_link` varchar(255) NOT NULL,
          `publish_error_code` varchar(100) NOT NULL,
          `network_details_id` int(11) NOT NULL,
          `post_for_relay` TINYINT NOT NULL DEFAULT '0',
          `post_for_approve` TINYINT NOT NULL DEFAULT '0',
          `relay_primary_post_id` int(11) NOT NULL DEFAULT '0',
          `relay_delay_min` int(11) NOT NULL DEFAULT '0',
          `hook_action` TINYINT NOT NULL DEFAULT '0',
          `hide` TINYINT NOT NULL DEFAULT '0',
          `v2_id` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`), INDEX `post_id` (`post_id`), INDEX `blog_user_id` (`blog_user_id`) , INDEX `sched_details_id` (`sched_details_id`),
            INDEX `sched_date` (`sched_date`), INDEX `sched_date_utc` (`sched_date_utc`), INDEX `publish_date` (`publish_date`) , INDEX `relay_primary_post_id` (`relay_primary_post_id`) ,
            INDEX `hook_action` (`hook_action`), INDEX `hide` (`hide`)
          ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1 ;";
        $wpdb->query($sqlCreateUserPosts);

        //since V4.8.0
        $b2sPostsCols = $wpdb->get_results('SHOW COLUMNS FROM b2s_posts');
        if (is_array($b2sPostsCols) && isset($b2sPostsCols[0])) {
            $b2sPostsColsData = array();
            foreach ($b2sPostsCols as $key => $value) {
                if (isset($value->Field) && !empty($value->Field)) {
                    $b2sPostsColsData[] = $value->Field;
                }
            }
            if (!in_array("last_edit_blog_user_id", $b2sPostsColsData)) {
                $wpdb->query("ALTER TABLE b2s_posts ADD last_edit_blog_user_id INT NOT NULL DEFAULT '0'");
            }
            if (!in_array("post_for_relay", $b2sPostsColsData)) {
                $wpdb->query("ALTER TABLE b2s_posts ADD post_for_relay TINYINT NOT NULL DEFAULT '0'");
            }
            if (!in_array("post_for_approve", $b2sPostsColsData)) {
                $wpdb->query("ALTER TABLE b2s_posts ADD post_for_approve TINYINT NOT NULL DEFAULT '0'");
            }
            if (!in_array("relay_primary_post_id", $b2sPostsColsData)) {
                $wpdb->query("ALTER TABLE b2s_posts ADD relay_primary_post_id int(11) NOT NULL DEFAULT '0'");
                $wpdb->query('ALTER TABLE `b2s_posts` ADD INDEX(`relay_primary_post_id`)');
            }
            if (!in_array("relay_delay_min", $b2sPostsColsData)) {
                $wpdb->query("ALTER TABLE b2s_posts ADD relay_delay_min int(11) NOT NULL DEFAULT '0'");
            }
        }

        $keys = $wpdb->get_results('SHOW INDEX FROM `b2s_posts`');
        $allowIndexPosts = array('PRIMARY', 'post_id', 'blog_user_id', 'sched_details_id', 'sched_date', 'sched_date_utc', 'publish_date', 'relay_primary_post_id', 'hook_action', 'hide');
        foreach ($keys as $k => $value) {
            if (!in_array($value->Key_name, $allowIndexPosts)) {
                $wpdb->query('ALTER TABLE `b2s_posts` DROP INDEX ' . $value->Key_name);
            }
        }

        //Change Collation >=V4.0 Emoji
        $existsTable = $wpdb->get_results('SHOW TABLES LIKE "b2s_posts_sched_details"');
        if (is_array($existsTable) && !empty($existsTable)) {
            $wpdb->query('ALTER TABLE `b2s_posts_sched_details` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
            $wpdb->query('ALTER TABLE `b2s_posts_sched_details` CHANGE sched_data sched_data TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
            $wpdb->query('REPAIR TABLE `b2s_posts_sched_details`');
            $wpdb->query('OPTIMIZE TABLE `b2s_posts_sched_details`');
        } else {
            $sqlCreateUserSchedDetails = "CREATE TABLE IF NOT EXISTS `b2s_posts_sched_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `sched_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            `image_url` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
            ) DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";
            $wpdb->query($sqlCreateUserSchedDetails);
        }

        $sqlCreateUserNetworkDetails = "CREATE TABLE IF NOT EXISTS `b2s_posts_network_details` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `network_id` TINYINT NOT NULL,
            `network_type` TINYINT NOT NULL,
            `network_auth_id` int(11) NOT NULL,
            `network_display_name` varchar(100) NOT NULL,
            PRIMARY KEY (`id`)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1 ;";
        $wpdb->query($sqlCreateUserNetworkDetails);

        $sqlCreateUserContact = "CREATE TABLE IF NOT EXISTS `b2s_user_contact`(
          `id` int(5) NOT  NULL  AUTO_INCREMENT ,
          `blog_user_id` int(11)  NOT  NULL ,
          `name_mandant` varchar(100)  NOT  NULL ,
          `created` datetime NOT  NULL DEFAULT  '0000-00-00 00:00:00',
          `name_presse` varchar(100)  NOT  NULL ,
          `anrede_presse` enum('0','1','2')  NOT  NULL DEFAULT  '0' COMMENT  '0=Frau,1=Herr 2=keine Angabe',
          `vorname_presse` varchar(50)  NOT  NULL ,
          `nachname_presse` varchar(50)  NOT  NULL ,
          `strasse_presse` varchar(100)  NOT  NULL ,
          `nummer_presse` varchar(5)  NOT  NULL DEFAULT  '',
          `plz_presse` varchar(10)  NOT  NULL ,
          `ort_presse` varchar(75)  NOT  NULL ,
          `land_presse` varchar(3)  NOT  NULL DEFAULT  'DE',
          `email_presse` varchar(75)  NOT  NULL ,
          `telefon_presse` varchar(30)  NOT  NULL ,
          `fax_presse` varchar(30)  NOT  NULL ,
          `url_presse` varchar(150)  NOT  NULL ,
          PRIMARY  KEY (`id`) ,
          KEY `blog_user_id`(`blog_user_id`)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1";
        $wpdb->query($sqlCreateUserContact);


        $sqlCreateNetworkSettings = 'CREATE TABLE IF NOT EXISTS `b2s_user_network_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `blog_user_id` int(11) NOT NULL,
            `mandant_id` int(11) NOT NULL,
            `network_auth_id` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            INDEX `blog_user_id` (`blog_user_id`), INDEX `mandant_id` (`mandant_id`)
          ) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci AUTO_INCREMENT=1;';
        $wpdb->query($sqlCreateNetworkSettings);

        $b2sCheckAfter = $b2sSystem->check('after');
        if (is_array($b2sCheckAfter)) {
            $b2sSystem->deactivatePlugin();
            wp_die($b2sSystem->getErrorMessage($b2sCheckAfter) . ' ' . __('or', 'blog2social') . '  <a href="' . admin_url("/plugins.php", "http") . '/">' . __('back to install plugins', 'blog2social') . '</a>');
        }

        //Activate Social Meta Tags
        $options = new B2S_Options(0, 'B2S_PLUGIN_GENERAL_OPTIONS');
        if ($options->_getOption('og_active') === false || $options->_getOption('card_active') === false) {
            $options->_setOption('og_active', 1);
            $options->_setOption('card_active', 1);
        }
    }

    public function deactivatePlugin() {
        $optionDeleteSchedPosts = get_option('B2S_PLUGIN_DEACTIVATE_SCHED_POST');
        if ($optionDeleteSchedPosts !== false && (int) $optionDeleteSchedPosts == 1) {
            global $wpdb;
            $sqlPosts = "SELECT a.token FROM `b2s_user` a INNER JOIN b2s_posts ON a.`blog_user_id` = b2s_posts.`blog_user_id` where b2s_posts.`hide` = 0 AND b2s_posts.`sched_type` != 3 AND b2s_posts.`publish_date`= '0000-00-00 00:00:00' GROUP by a.blog_user_id";
            $results = $wpdb->get_results($sqlPosts, ARRAY_A);
            if (is_array($results) && !empty($results)) {
                $tempData = array('action' => 'deleteBlogSchedPost', 'host' => get_site_url(), 'data' => serialize($results));
                $result = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, $tempData));
                if (isset($result->result) && $result->result == true) {
                    $data = array('hide' => '1', 'hook_action' => '0');
                    $where = array('publish_date' => '0000-00-00 00:00:00', 'hide' => '0');
                    $wpdb->update('b2s_posts', $data, $where, array('%d', '%d'), array('%d', '%d'));
                    delete_option('B2S_PLUGIN_DEACTIVATE_SCHED_POST');
                }
            }
        }
    }

    public function releaseLocks() {
        require_once(B2S_PLUGIN_DIR . 'includes/Options.php');
        $options = new B2S_Options(get_current_user_id());
        $lock = $options->_getOption("B2S_PLUGIN_USER_CALENDAR_BLOCKED");

        if ($lock) {
            delete_option("B2S_PLUGIN_CALENDAR_BLOCKED_" . $lock);
            $options->_setOption("B2S_PLUGIN_USER_CALENDAR_BLOCKED", false);
        }
    }

}
