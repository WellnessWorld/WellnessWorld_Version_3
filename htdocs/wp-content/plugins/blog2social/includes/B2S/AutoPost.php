<?php

class B2S_AutoPost {

    private $title;
    private $contentHtml;
    private $postId;
    private $content;
    private $url;
    private $imageUrl;
    private $keywords;
    private $blogPostData = array();
    private $myTimeSettings = array();
    private $current_user_date;
    private $setPreFillText;
    private $optionPostFormat;
    private $allowHashTag;
    private $optionContentTwitter;

    function __construct($postId = 0, $blogPostData = array(), $current_user_date = '0000-00-00 00:00:00', $myTimeSettings = false, $title = '', $content = '', $url = '', $imageUrl = '', $keywords = '', $b2sPostLang = 'en', $optionPostFormat = array(), $allowHashTag = true, $optionContentTwitter = 0) {
        $this->postId = $postId;
        $this->blogPostData = $blogPostData;
        $this->current_user_date = $current_user_date;
        $this->myTimeSettings = $myTimeSettings;
        $this->title = $title;
        $this->content = B2S_Util::prepareContent($postId, $content, $url, false, true, $b2sPostLang);
        $this->contentHtml = B2S_Util::prepareContent($postId, $content, $url, '<p><h1><h2><br><i><b><a><img>', true, $b2sPostLang);
        $this->url = $url;
        $this->imageUrl = $imageUrl;
        $this->keywords = $keywords;
        $this->optionPostFormat = $optionPostFormat;
        $this->allowHashTag = $allowHashTag;
        $this->optionContentTwitter = $optionContentTwitter;
        $this->setPreFillText = array(0 => array(1 => 239, 2 => 255, 3 => 239, 6 => 300, 8 => 239, 9 => 200, 10 => 442, 12 => 240, 16 => 250, 17 => 442, 18 => 800, 19 => 239), 1 => array(1 => 239, 3 => 239, 8 => 1200, 10 => 442, 17 => 442, 19 => 239), 2 => array(1 => 239, 8 => 239, 10 => 442, 17 => 442, 19 => 239));
        $this->setPreFillTextLimit = array(0 => array(1 => 400, 2 => 256, 3 => 400, 6 => 400, 8 => 400, 9 => 200, 10 => 500, 12 => 400, 18 => 1000), 1 => array(1 => 400, 3 => 400, 8 => 1200, 10 => 500, 19 => 400), 2 => array(1 => 400, 8 => 400, 10 => 500, 19 => 9000));
    }

    public function prepareShareData($networkAuthId = 0, $networkId = 0, $networkType = 0) {
        if ((int) $networkId > 0 && (int) $networkAuthId > 0) {
            $postData = array('content' => '', 'custom_title' => '', 'tags' => array(), 'network_auth_id' => (int) $networkAuthId);

            //PostFormat
            if (in_array($networkId, array(1, 2, 10, 12))) {
                $postData['post_format'] = ((isset($this->optionPostFormat[$networkId]) && is_array($this->optionPostFormat[$networkId]) && ((isset($this->optionPostFormat[$networkId]['all']) && (int) $this->optionPostFormat[$networkId]['all'] == 0) || (isset($this->optionPostFormat[$networkId][$networkType]) && (int) $this->optionPostFormat[$networkId][$networkType] == 0)) ) ? 0 : (!isset($this->optionPostFormat[$networkId]) ? 0 : 1 ));
            }

            //Special
            if ($networkId == 1 || $networkId == 3) {
                $postData['content'] = (isset($this->setPreFillText[$networkType][$networkId])) ? B2S_Util::getExcerpt($this->content, (int) $this->setPreFillText[$networkType][$networkId], (int) $this->setPreFillTextLimit[$networkType][$networkId]) : $this->content;
                if ($this->allowHashTag) {
                    $postData['content'] .= $this->getHashTagsString();
                }
            }
            if ($networkId == 2) {
                if (isset($this->setPreFillText[$networkType][$networkId])) {
                    $postData['content'] = strip_tags($this->title);
                    if ($this->optionContentTwitter !== false && $this->optionContentTwitter == 1) { //append
                        $postData['content'] .= ' ' . $this->content;
                    }
                    if ($this->optionContentTwitter !== false && $this->optionContentTwitter == 2) { //only
                        $postData['content'] = $this->content;
                    }
                    if ($this->allowHashTag) {
                        $postData['content'] .= $this->getHashTagsString('');
                    }
                    $postData['content'] = B2S_Util::getExcerpt($postData['content'], (int) $this->setPreFillText[$networkType][$networkId], (int) $this->setPreFillTextLimit[$networkType][$networkId]);
                } else {
                    $postData['content'] = strip_tags($this->title);
                    if ($this->allowHashTag) {
                        $postData['content'] .= $this->getHashTagsString('');
                    }
                }
            }
            if ($networkId == 4) {
                $postData['custom_title'] = strip_tags($this->title);
                $postData['content'] = $this->contentHtml;
                if ($this->allowHashTag) {
                    if (is_array($this->keywords) && !empty($this->keywords)) {
                        foreach ($this->keywords as $tag) {
                            $postData['tags'][] = str_replace(" ", "", $tag->name);
                        }
                    }
                }
            }

            if ($networkId == 6 || $networkId == 12) {
                if ($this->imageUrl !== false) {
                    $postData['content'] = (isset($this->setPreFillText[$networkType][$networkId])) ? B2S_Util::getExcerpt($this->content, (int) $this->setPreFillText[$networkType][$networkId], (int) $this->setPreFillTextLimit[$networkType][$networkId]) : $this->content;
                    if ($this->allowHashTag) {
                        $postData['content'] .= $this->getHashTagsString();
                    }
                } else {
                    return false;
                }
            }

            if ($networkId == 7) {
                if ($this->imageUrl !== false) {
                    $postData['custom_title'] = strip_tags($this->title);
                } else {
                    return false;
                }
            }
            if ($networkId == 8 && $networkId == 19) {
                $postData['content'] = (isset($this->setPreFillText[$networkType][$networkId])) ? B2S_Util::getExcerpt($this->content, (int) $this->setPreFillText[$networkType][$networkId], (int) $this->setPreFillTextLimit[$networkType][$networkId]) : $this->content;
                if ($networkType != 0) {
                    $postData['custom_title'] = strip_tags($this->title);
                }
            }
            if ($networkId == 9 || $networkId == 16) {
                $postData['custom_title'] = $this->title;
                $postData['content'] = (isset($this->setPreFillText[$networkType][$networkId])) ? B2S_Util::getExcerpt($this->content, (int) $this->setPreFillText[$networkType][$networkId], (int) $this->setPreFillTextLimit[$networkType][$networkId]) : $this->content;
                if ($this->allowHashTag) {
                    if (is_array($this->keywords) && !empty($this->keywords)) {
                        foreach ($this->keywords as $tag) {
                            $postData['tags'][] = str_replace(" ", "", $tag->name);
                        }
                    }
                }
            }

            if ($networkId == 10 || $networkId == 17 || $networkId == 18) {
                $postData['content'] = (isset($this->setPreFillText[$networkType][$networkId])) ? B2S_Util::getExcerpt($this->content, (int) $this->setPreFillText[$networkType][$networkId], (isset($this->setPreFillTextLimit[$networkType][$networkId]) ? (int) $this->setPreFillTextLimit[$networkType][$networkId] : false)) : $this->content;
                if ($this->allowHashTag) {
                    $postData['content'] .= $this->getHashTagsString();
                }
            }

            if ($networkId == 11 || $networkId == 14) {
                $postData['custom_title'] = strip_tags($this->title);
                $postData['content'] = $this->contentHtml;
            }

            if ($networkId == 11) {
                if ($this->allowHashTag) {
                    if (is_array($this->keywords) && !empty($this->keywords)) {
                        foreach ($this->keywords as $tag) {
                            $postData['tags'][] = str_replace(" ", "", $tag->name);
                        }
                    }
                }
            }

            if ($networkId == 13 || $networkId == 15) {
                $postData['content'] = strip_tags($this->title);
            }
            return $postData;
        }
        return false;
    }

    private function getHashTagsString($add = "\n\n") {
        $hashTags = '';
        if (is_array($this->keywords) && !empty($this->keywords)) {
            foreach ($this->keywords as $tag) {
                $hashTags .= ' #' . str_replace(array(" ", "-"), "", $tag->name);
            }
        }
        return (!empty($hashTags) ? (!empty($add) ? $add . $hashTags : $hashTags) : '');
    }

    public function saveShareData($shareData = array(), $network_id = 0, $network_type = 0, $network_auth_id = 0, $shareApprove = 0, $network_display_name = '') {

        $sched_type = $this->blogPostData['sched_type'];
        $sched_date = $this->blogPostData['sched_date'];
        $sched_date_utc = $this->blogPostData['sched_date_utc'];

        //Scheduling post once with user times 
        if ($sched_type == 2 && $this->myTimeSettings !== false && is_array($this->myTimeSettings) && isset($this->myTimeSettings['times']) && is_array($this->myTimeSettings['times']) && isset($this->myTimeSettings['type'])) {
            //V 5.1.0 Seeding
            //0=default(best time), 1= special per account (seeding), 2= per network (old)
            //Check My Time Setting in Past
            //new
            if ($this->myTimeSettings['type'] == 1) {
                if (isset($this->myTimeSettings['times']['delay_day'][$network_auth_id]) && isset($this->myTimeSettings['times']['time'][$network_auth_id]) && !empty($this->myTimeSettings['times']['time'][$network_auth_id])) {
                    $tempSchedDate = date('Y-m-d', strtotime($sched_date));
                    $networkSchedDate = date('Y-m-d H:i:00', strtotime($tempSchedDate . ' ' . $this->myTimeSettings['times']['time'][$network_auth_id]));
                    if ($this->myTimeSettings['times']['delay_day'][$network_auth_id] > 0) {
                        $sched_date = date('Y-m-d H:i:s', strtotime('+' . $this->myTimeSettings['times']['delay_day'][$network_auth_id] . ' days', strtotime($networkSchedDate)));
                        $sched_date_utc = date('Y-m-d H:i:s', strtotime(B2S_Util::getUTCForDate($sched_date, $this->blogPostData['user_timezone'] * (-1))));
                    } else {
                        if ($networkSchedDate >= $sched_date) {
                            //Scheduling
                            $sched_date = $networkSchedDate;
                            $sched_date_utc = date('Y-m-d H:i:s', strtotime(B2S_Util::getUTCForDate($sched_date, $this->blogPostData['user_timezone'] * (-1))));
                        } else {
                            //Scheduling on next Day by Past
                            $sched_date = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($networkSchedDate)));
                            $sched_date_utc = date('Y-m-d H:i:s', strtotime(B2S_Util::getUTCForDate($sched_date, $this->blogPostData['user_timezone'] * (-1))));
                        }
                    }
                }
                //old  or default (best time)   
            } else {
                foreach ($this->myTimeSettings['times'] as $k => $v) {
                    if ($v->network_id == $network_id && $v->network_type == $network_type) {
                        if (isset($v->sched_time) && !empty($v->sched_time)) {
                            $tempSchedDate = date('Y-m-d', strtotime($sched_date));
                            $networkSchedDate = date('Y-m-d H:i:00', strtotime($tempSchedDate . ' ' . $v->sched_time));
                            if ($networkSchedDate >= $sched_date) {
                                //Scheduling
                                $sched_date = $networkSchedDate;
                                $sched_date_utc = date('Y-m-d H:i:s', strtotime(B2S_Util::getUTCForDate($sched_date, $this->blogPostData['user_timezone'] * (-1))));
                            } else {
                                //Scheduling on next Day by Past
                                $sched_date = date('Y-m-d H:i:s', strtotime('+1 days', strtotime($networkSchedDate)));
                                $sched_date_utc = date('Y-m-d H:i:s', strtotime(B2S_Util::getUTCForDate($sched_date, $this->blogPostData['user_timezone'] * (-1))));
                            }
                        }
                    }
                }
            }
        }

        global $wpdb;
        $networkDetailsId = 0;
        $schedDetailsId = 0;
        $networkDetailsIdSelect = $wpdb->get_col($wpdb->prepare("SELECT postNetworkDetails.id FROM b2s_posts_network_details AS postNetworkDetails WHERE postNetworkDetails.network_auth_id = %s", $network_auth_id));
        if (isset($networkDetailsIdSelect[0])) {
            $networkDetailsId = (int) $networkDetailsIdSelect[0];
        } else {
            $wpdb->insert('b2s_posts_network_details', array(
                'network_id' => (int) $network_id,
                'network_type' => (int) $network_type,
                'network_auth_id' => (int) $network_auth_id,
                'network_display_name' => $network_display_name), array('%d', '%d', '%d', '%s'));
            $networkDetailsId = $wpdb->insert_id;
        }

        if ($networkDetailsId > 0) {
            //DeprecatedNetwork-8 31 march
            if ($network_id == 8 && $sched_date_utc >= '2019-03-30 23:59:59') {
                $wpdb->insert('b2s_posts', array(
                    'post_id' => $this->postId,
                    'blog_user_id' => $this->blogPostData['blog_user_id'],
                    'user_timezone' => $this->blogPostData['user_timezone'],
                    'publish_date' => date('Y-m-d H:i:s', strtotime(B2S_Util::getUTCForDate(gmdate('Y-m-d H:i:s'), $this->blogPostData['user_timezone'] * (-1)))),
                    'publish_error_code' => 'DEPRECATED_NETWORK_8',
                    'network_details_id' => $networkDetailsId), array('%d', '%d', '%s', '%s', '%s', '%d'));
            } else {
                $wpdb->insert('b2s_posts_sched_details', array('sched_data' => serialize($shareData), 'image_url' => (isset($shareData['image_url']) ? $shareData['image_url'] : '')), array('%s', '%s'));
                $schedDetailsId = $wpdb->insert_id;
                $wpdb->insert('b2s_posts', array(
                    'post_id' => $this->postId,
                    'blog_user_id' => $this->blogPostData['blog_user_id'],
                    'user_timezone' => $this->blogPostData['user_timezone'],
                    'publish_date' => (($sched_type == 3) ? $sched_date : "0000-00-00 00:00:00"),
                    'sched_details_id' => $schedDetailsId,
                    'sched_type' => $sched_type,
                    'sched_date' => $sched_date,
                    'sched_date_utc' => $sched_date_utc,
                    'network_details_id' => $networkDetailsId,
                    'post_for_approve' => (int) $shareApprove,
                    'hook_action' => (((int) $shareApprove == 0) ? 1 : 0)), array('%d', '%d', '%s', '%s', '%d', '%d', '%s', '%s', '%d', '%d', '%d'));
                B2S_Rating::trigger();
            }
        }
    }

}
