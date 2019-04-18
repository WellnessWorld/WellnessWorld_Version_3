<?php

class B2S_Network_Item {

    private $authurl;
    private $allowProfil;
    private $allowPage;
    private $allowGroup;
    private $modifyBoardAndGroup;
    private $networkKindName;
    private $oAuthPortal;
    private $mandantenId;
    private $bestTimeInfo;
    private $lang;
    private $options;
    private $userSchedData; // >5.1.0
    private $userSchedDataOld; // <5.1.0

    public function __construct($load = true) {
        $this->mandantenId = array(-1, 0); //All,Default
        if ($load) {
            $this->options = new B2S_Options(B2S_PLUGIN_BLOG_USER_ID);
            $this->userSchedData = $this->options->_getOption('auth_sched_time');
            if (!isset($this->userSchedData['time'])) {
                $this->userSchedDataOld = $this->getSchedDataByUser();
            }
            $this->authurl = B2S_PLUGIN_API_ENDPOINT_AUTH . '?b2s_token=' . B2S_PLUGIN_TOKEN . '&sprache=' . substr(B2S_LANGUAGE, 0, 2) . '&unset=true';
            $this->allowProfil = unserialize(B2S_PLUGIN_NETWORK_ALLOW_PROFILE);
            $this->allowPage = unserialize(B2S_PLUGIN_NETWORK_ALLOW_PAGE);
            $this->allowGroup = unserialize(B2S_PLUGIN_NETWORK_ALLOW_GROUP);
            $this->oAuthPortal = unserialize(B2S_PLUGIN_NETWORK_OAUTH);
            $this->bestTimeInfo = unserialize(B2S_PLUGIN_SCHED_DEFAULT_TIMES_INFO);
            $this->modifyBoardAndGroup = unserialize(B2S_PLUGIN_NETWORK_ALLOW_MODIFY_BOARD_AND_GROUP);
            $this->networkKindName = unserialize(B2S_PLUGIN_NETWORK_KIND);
            $this->lang = substr(B2S_LANGUAGE, 0, 2);
        }
    }

    public function getData() {
        $result = json_decode(B2S_Api_Post::post(B2S_PLUGIN_API_ENDPOINT, array('action' => 'getUserAuth', 'view_mode' => 'all', 'auth_count' => true, 'token' => B2S_PLUGIN_TOKEN, 'version' => B2S_PLUGIN_VERSION)));
        return array('mandanten' => isset($result->mandanten) ? $result->mandanten : '',
            'auth' => isset($result->auth) ? $result->auth : '',
            'auth_count' => isset($result->auth_count) ? $result->auth_count : false,
            'portale' => isset($result->portale) ? $result->portale : '');
    }

    public function getCountSchedPostsByUserAuth($networkAuthId = 0) {
        global $wpdb;
        $countSched = $wpdb->get_results($wpdb->prepare("SELECT COUNT(b.id) AS count FROM b2s_posts b LEFT JOIN b2s_posts_network_details d ON (d.id = b.network_details_id) WHERE d.network_auth_id= %d AND b.hide = %d AND b.sched_date !=%s", $networkAuthId, 0, '0000-00-00 00:00:00'));
        if (is_array($countSched) && !empty($countSched) && isset($countSched[0]->count)) {
            if ((int) $countSched[0]->count > 0) {
                return (int) $countSched[0]->count;
            }
        }
        return false;
    }

    public function getSelectMandantHtml($data) {
        $select = '<select class="form-control b2s-network-mandant-select b2s-select">';
        $select .= '<optgroup label="' . __("Default", "blog2social") . '"><option value="-1" selected="selected">' . __('Show all', 'blog2social') . '</option>';
        $select .= '<option value="0">' . __('My profile', 'blog2social') . '</option></optgroup>';
        if (!empty($data)) {
            $select .='<optgroup id="b2s-network-select-more-client" label="' . __("Your profiles:", "blog2social") . '">';
            foreach ($data as $id => $name) {
                $select .= '<option value="' . $id . '">' . stripslashes($name) . '</option>';
            }
            $select .='</optgroup>';
        }
        $select .= '</select>';
        return $select;
    }

    public function getPortale($mandanten, $auth, $portale, $auth_count) {
        $convertAuthData = $this->convertAuthData($auth);

        foreach ($mandanten as $k => $v) {
            $this->mandantenId[] = $k;
        }

        $html = '<div class="col-md-12 b2s-network-details-container">';
        $html .= '<form id = "b2sSaveTimeSettings" method = "post">';
        $html .= '<input id = "action" type = "hidden" value = "b2s_save_user_time_settings" name = "action">';

        foreach ($this->mandantenId as $k => $mandant) {
            $html .= $this->getItemHtml($mandant, $mandanten, $convertAuthData, $portale, $auth_count);
        }
        $html .='</form>';
        $html .= '</div>';
        return $html;
    }

    public function getItemHtml($mandant, $mandantenData, $convertAuthData, $portale, $auth_count) {

        $html = '<ul class="list-group b2s-network-details-container-list" data-mandant-id="' . $mandant . '" style="display:' . ($mandant > 0 ? "none" : "block" ) . '">';
        foreach ($portale as $k => $portal) {
            if (!isset($convertAuthData[$mandant][$portal->id]) || empty($convertAuthData[$mandant][$portal->id])) {
                $convertAuthData[$mandant][$portal->id] = array();
            }
            $maxNetworkAccount = ($auth_count !== false && is_array($auth_count)) ? ((isset($auth_count[$portal->id])) ? $auth_count[$portal->id] : $auth_count[0]) : false;

            if ($mandant == -1) { //all
                $html .= $this->getPortaleHtml($portal->id, $portal->name, $mandant, $mandantenData, $convertAuthData, $maxNetworkAccount, true);
            } else {
                $html .= $this->getPortaleHtml($portal->id, $portal->name, $mandant, $mandantenData, $convertAuthData[$mandant][$portal->id], $maxNetworkAccount);
            }
        }
        $html .= '</ul>';

        return $html;
    }

    private function getPortaleHtml($networkId, $networkName, $mandantId, $mandantenData, $networkData, $maxNetworkAccount = false, $showAllAuths = false) {
        $containerMandantId = $mandantId;
        $mandantId = ($mandantId == -1) ? 0 : $mandantId;
        $sprache = substr(B2S_LANGUAGE, 0, 2);
        $html = '<li class="list-group-item" data-network-id="' . $networkId . '">';
        $html .='<div class="media">';
        if ($networkId != 8) {
            $html .='<img class="pull-left hidden-xs b2s-img-network" alt="' . $networkName . '" src="' . plugins_url('/assets/images/portale/' . $networkId . '_flat.png', B2S_PLUGIN_FILE) . '">';
        } else {
            $html .='<span class="pull-left hidden-xs b2s-img-network"></span>';
        }
        $html .='<div class="media-body network">';

        if ($networkId != 8) {
            $html .= '<h4>' . ucfirst($networkName);

            if ($maxNetworkAccount !== false) {
                if ($networkId == 18) {
                    $html .=' <a class="b2s-info-btn" data-target="#b2sInfoNetwork18" data-toggle="modal" href="#">Info</a>';
                }
            }
            if (isset($this->bestTimeInfo[$networkId]) && !empty($this->bestTimeInfo[$networkId]) && is_array($this->bestTimeInfo[$networkId]) && $networkId != 8) {
                $time = '';
                $slug = ($this->lang == 'de') ? __('Uhr', 'blog2social') : '';
                foreach ($this->bestTimeInfo[$networkId] as $k => $v) {
                    $time .= B2S_Util::getTimeByLang($v[0], $this->lang) . '-' . B2S_Util::getTimeByLang($v[1], $this->lang) . $slug . ', ';
                }
                $html .= '<span class="hidden-xs hidden-sm b2s-sched-manager-best-time-info">(' . __('Best times', 'blog2social') . ': ' . substr($time, 0, -2) . ')</span>';
            }

            $html .= '<span class="pull-right">';

            $b2sAuthUrl = $this->authurl . '&portal_id=' . $networkId . '&transfer=' . (in_array($networkId, $this->oAuthPortal) ? 'oauth' : 'form' ) . '&mandant_id=' . $mandantId . '&version=3&affiliate_id=' . B2S_Tools::getAffiliateId();

            if (in_array($networkId, $this->allowProfil)) {
                $html .= ($networkId != 18 || (B2S_PLUGIN_USER_VERSION >= 2 && $networkId == 18)) ? '<a href="#" onclick="wop(\'' . $b2sAuthUrl . '&choose=profile\', \'Blog2Social Network\'); return false;" class="btn btn-primary btn-sm b2s-network-auth-btn">+ ' . __('Profile', 'blog2social') . '</a>' : '<a href="#" class="btn btn-primary btn-sm b2s-network-auth-btn b2s-btn-disabled" data-title="' . __('You want to connect a network profile?', 'blog2social') . '" data-toggle="modal"  data-type="auth-network" data-target="#b2sProFeatureModal">+ ' . __('Profile', 'blog2social') . ' <span class="label label-success">' . __("PREMIUM", "blog2social") . '</a>';
            }
            if (in_array($networkId, $this->allowPage)) {
                $html .= (B2S_PLUGIN_USER_VERSION > 1 || (B2S_PLUGIN_USER_VERSION == 0 && $networkId == 1) || (B2S_PLUGIN_USER_VERSION == 1 && ($networkId == 1 || $networkId == 10))) ? '<button onclick="wop(\'' . $b2sAuthUrl . '&choose=page\', \'Blog2Social Network\'); return false;" class="btn btn-primary btn-sm b2s-network-auth-btn">+ ' . __('Page', 'blog2social') . '</button>' : '<a href="#" class="btn btn-primary btn-sm b2s-network-auth-btn b2s-btn-disabled" data-title="' . __('You want to connect a network page?', 'blog2social') . '" data-toggle="modal"  data-type="auth-network" data-target="#' . ((B2S_PLUGIN_USER_VERSION == 0) ? 'b2sPreFeatureModal' : 'b2sProFeatureModal') . '">+ ' . __('Page', 'blog2social') . ' <span class="label label-success">' . __("PREMIUM", "blog2social") . '</a>';
            }
            if (in_array($networkId, $this->allowGroup)) {
                $html .= (B2S_PLUGIN_USER_VERSION > 1 || (B2S_PLUGIN_USER_VERSION == 1 && $networkId != 8)) ? '<button  onclick="wop(\'' . $b2sAuthUrl . '&choose=group\', \'Blog2Social Network\'); return false;" class="btn btn-primary btn-sm b2s-network-auth-btn">+ ' . __('Group', 'blog2social') . '</button>' : '<a href="#" class="btn btn-primary btn-sm b2s-network-auth-btn b2s-btn-disabled" data-toggle="modal" data-title="' . __('You want to connect a social media group?', 'blog2social') . '" data-type="auth-network" data-target="#' . ((B2S_PLUGIN_USER_VERSION == 0) ? 'b2sPreFeatureModal' : 'b2sProFeatureModal') . '">+ ' . __('Group', 'blog2social') . ' <span class="label label-success">' . __("PREMIUM", "blog2social") . '</span></a>';
            }

            $html .= '</span></h4>';
        }
        $html .= '<div class="clearfix"></div>';
        
        if ($networkId == 8) { //is deprecated since in 5.2.0
            $html.='<div class="alert alert-info"><span class="glyphicon glyphicon-exclamation-sign glyphicon-info"></span> <span class="b2s-text-bold">' . __('Action required', 'blog2social') . '</span> <br>' . __('The current XING API will be replaced by the new XING interface. Please connect your XING profile, as well as your XING company pages (Employer branding profiles) and business pages with the new XING interface in the Blog2Social network settings.', 'blog2social') . '</div>';
        }
        if ($networkId == 10) { //is deprecated since in 5.2.0
            $html.='<br><div class="alert alert-info"><span class="glyphicon glyphicon-exclamation-sign glyphicon-info"></span> <span class="b2s-text-bold">' . __('Please notice', 'blog2social') . '</span> <br>' . __('Google will shut down Google+ for all private accounts (profiles, pages, groups) on 2nd April 2019. You can find further information and the next steps, including how to download your photos and other content here:', 'blog2social') . ' <a target="_blank" href="https://support.google.com/plus/answer/9195133">https://support.google.com/plus/answer/9195133</a><br>'.__('Please check whether you have scheduled social media posts for Google+ beyond 2nd April 2019. Consider to reschedule these posts for other social networks.','blog2social').'<br><a href="admin.php?page=blog2social-sched&b2sShowByNetwork=10">'.__('Click here to find all posts for your Google+ accounts that are scheduled beyond 2nd April 2019','blog2social').'</a></div>';
        }
        
        $html .= '<div class="clearfix"></div>';

        $html .= '<ul class="b2s-network-item-auth-list" data-network-mandant-id="' . $mandantId . '" data-network-id="' . $networkId . '" ' . (($showAllAuths) ? 'data-network-count="true"' : '') . '>';

        //First Line
        $html.='<li class="b2s-network-item-auth-list-li"  data-network-mandant-id="' . $mandantId . '" data-network-id="' . $networkId . '" data-view="' . (($containerMandantId == -1) ? 'all' : 'selected') . '">';
        $html.='<span class="b2s-network-auth-count">' . __("Connections", "blog2social") . ' <span class="b2s-network-auth-count-current" ' . (($showAllAuths) ? 'data-network-count-trigger="true"' : '') . '  data-network-id="' . $networkId . '"></span>/' . $maxNetworkAccount . '</span>';
        $html.='<span class="pull-right b2s-sched-manager-title hidden-xs"  data-network-mandant-id="' . $mandantId . '" data-network-id="' . $networkId . '">' . __("Best Time Manager", "blog2social") . ' <a href="#" data-toggle="modal" data-target="#b2sInfoSchedTimesModal" class="b2s-info-btn b2s-load-settings-sched-time-default-info">' . __('Info', 'blog2social') . '</a></span>';
        $html.='</li>';


        if ($showAllAuths) {
            foreach ($this->mandantenId as $ka => $mandantAll) {
                $mandantName = isset($mandantenData->{$mandantAll}) ? ($mandantenData->{$mandantAll}) : __("My profile", "blog2social");
                if (isset($networkData[$mandantAll][$networkId]) && !empty($networkData[$mandantAll][$networkId])) {
                    $html .= $this->getAuthItemHtml($networkData[$mandantAll][$networkId], $mandantAll, $mandantName, $networkId, $b2sAuthUrl, $containerMandantId, $sprache);
                }
            }
        } else {
            $html .= $this->getAuthItemHtml($networkData, $mandantId, "", $networkId, $b2sAuthUrl, $containerMandantId, $sprache);
        }

        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</li>';
        return $html;
    }

    private function getAuthItemHtml($networkData = array(), $mandantId, $mandantName, $networkId, $b2sAuthUrl = '', $containerMandantId = 0, $sprache = 'en') {
        $isEdit = false;
        $html = '';
        if (isset($networkData[0])) {
            foreach ($networkData[0] as $k => $v) {

                $isDeprecated = ($networkId == 8 || $networkId == 10) ? true : false;
                $notAllow = ($v['notAllow'] !== false) ? true : false;
                $isInterrupted = ($v['expiredDate'] != '0000-00-00' && $v['expiredDate'] <= date('Y-m-d')) ? true : false;


                $html .= '<li class="b2s-network-item-auth-list-li ' . (($isDeprecated) ? 'b2s-label-info-border-left deprecated' : (($notAllow) ? 'b2s-label-warning-border-left' : (($isInterrupted) ? 'b2s-label-danger-border-left' : ''))) . ' " data-network-auth-id="' . $v['networkAuthId'] . '" data-network-mandant-id="' . $mandantId . '" data-network-id="' . $networkId . '" data-network-type="0">';
                $html .='<div class="pull-left">';

                if ($notAllow) {
                    $html.= '<div class="b2s-network-auth-list-info"><span class="glyphicon glyphicon-remove-circle"></span> ' . __('To reactivate this connection,', 'blog2social') . ' <a href="' . B2S_Tools::getSupportLink('affiliate') . '"target="_blank">' . __('please upgrade', 'blog2social') . '</a></div>';
                }

                if ($isInterrupted && !$notAllow) {
                    $html.= '<div class="b2s-network-auth-list-info"><span class="glyphicon glyphicon-remove-circle"></span> ' . __('Authorization is interrupted since', 'blog2social') . ' ' . ($sprache == 'en' ? $v['expiredDate'] : date('d.m.Y', strtotime($v['expiredDate']))) . '</div>';
                }

                $html .= '<span class="b2s-network-item-auth-type">' . (($isDeprecated) ? '<span class="glyphicon glyphicon-exclamation-sign glyphicon-info"></span> ' : '') . __('Profile', 'blog2social') . '</span>: <span class="b2s-network-item-auth-user-name">' . stripslashes($v['networkUserName']) . '</span> ';

                if (!empty($mandantName)) {
                    $html .='<span class="b2s-network-mandant-name">(' . $mandantName . ')</span> ';
                }
                $html .='</div>';

                $html .='<div class="pull-right">';
                $html .= '<a class="b2s-network-item-auth-list-btn-delete b2s-add-padding-network-delete pull-right" data-network-type="0" data-network-id="' . $networkId . '" data-network-auth-id="' . $v['networkAuthId'] . '" href="#"><span class="glyphicon  glyphicon-trash glyphicon-grey"></span></a>';
                if (!$notAllow && !$isDeprecated) {
                    $html .= '<a href="#" onclick="wop(\'' . $b2sAuthUrl . '&choose=profil&update=' . $v['networkAuthId'] . '\', \'Blog2Social Network\'); return false;" class="b2s-network-auth-btn b2s-network-auth-update-btn b2s-add-padding-network-refresh pull-right" data-network-auth-id="' . $v['networkAuthId'] . '"><span class="glyphicon  glyphicon-refresh glyphicon-grey"></span></a>';
                    if ($v['expiredDate'] == '0000-00-00' || $v['expiredDate'] > date('Y-m-d')) {
                        if (isset($this->modifyBoardAndGroup[$networkId])) {
                            if (in_array(0, $this->modifyBoardAndGroup[$networkId]['TYPE'])) {
                                $html .='<a href="#" class="pull-right b2s-modify-board-and-group-network-btn b2s-add-padding-network-edit" data-modal-title="' . $this->modifyBoardAndGroup[$networkId]['TITLE'] . '" data-network-auth-id="' . $v['networkAuthId'] . '" data-network-id="' . $networkId . '" data-network-type="0"><span class="glyphicon glyphicon-pencil glyphicon-grey"></span></a>';
                                $isEdit = true;
                            }
                        }
                    }
                }
                //Sched Manager since V 5.1.0
                if (B2S_PLUGIN_USER_VERSION > 0) {
                    $html .='<span class="b2s-sched-manager-time-area pull-right ' . (!$isEdit ? 'b2s-sched-manager-add-padding' : '') . ' hidden-xs" style="' . (($isInterrupted || $notAllow) ? 'display:none;' : '') . '">
                        <input class="form-control b2s-box-sched-time-input b2s-settings-sched-item-input-time" type="text" value="' . $this->getUserSchedTime($v['networkAuthId'], $networkId, 0, 'time') . '" readonly="" data-network-auth-id="' . $v['networkAuthId'] . '" data-network-mandant-id="' . $mandantId . '" data-network-id="' . $networkId . '" data-network-type="0" data-network-container-mandant-id="' . $containerMandantId . '" name="b2s-user-sched-data[time][' . $v['networkAuthId'] . ']">
                        </span>';
                    $html .='<span class="b2s-sched-manager-day-area pull-right hidden-xs" style="' . (($isInterrupted || $notAllow) ? 'display:none;' : '') . '"><span class="b2s-sched-manager-item-input-day-btn-minus" data-network-auth-id="' . $v['networkAuthId'] . '">-</span> <span class="b2s-text-middle">+</span> <input type="text" class="b2s-sched-manager-item-input-day" data-network-auth-id="' . $v['networkAuthId'] . '" data-network-mandant-id="' . $mandantId . '" data-network-id="' . $networkId . '" data-network-type="0"  data-network-container-mandant-id="' . $containerMandantId . '" name="b2s-user-sched-data[delay_day][' . $v['networkAuthId'] . ']" value="' . $this->getUserSchedTime($v['networkAuthId'], $networkId, 0, 'day') . '" readonly> <span class="b2s-text-middle">' . __('Days', 'blog2social') . '</span> <span class="b2s-sched-manager-item-input-day-btn-plus" data-network-auth-id="' . $v['networkAuthId'] . '">+</span></span>';
                } else {
                    $html .='<span class="b2s-sched-manager-premium-area pull-right hidden-xs"><span class="label label-success"><a href="#" class="btn-label-premium" data-toggle="modal" data-target="#b2sInfoSchedTimesModal">' . __('PREMIUM', 'blog2social') . '</a></span></span>';
                }

                $html .='</div>';

                $html .= '<div class="clearfix"></div></li>';
            }
        }
        if (isset($networkData[1])) {
            foreach ($networkData[1] as $k => $v) {

                $isDeprecated = ($networkId == 8 || $networkId == 10) ? true : false;
                $notAllow = ($v['notAllow'] !== false) ? true : false;
                $isInterrupted = ($v['expiredDate'] != '0000-00-00' && $v['expiredDate'] <= date('Y-m-d')) ? true : false;

                $html .= '<li class="b2s-network-item-auth-list-li ' . (($isDeprecated) ? 'b2s-label-info-border-left deprecated' : (($notAllow) ? 'b2s-label-warning-border-left' : (($isInterrupted) ? 'b2s-label-danger-border-left' : ''))) . '" data-network-auth-id="' . $v['networkAuthId'] . '" data-network-mandant-id="' . $mandantId . '" data-network-id="' . $networkId . '" data-network-type="1">';
                $html .='<div class="pull-left">';

                if ($notAllow) {
                    $html.= '<div class="b2s-network-auth-list-info"><span class="glyphicon glyphicon-remove-circle"></span> ' . __('To reactivate this connection,', 'blog2social') . ' <a href="' . B2S_Tools::getSupportLink('affiliate') . '"target="_blank">' . __('please upgrade', 'blog2social') . '</a></div>';
                }

                if ($isInterrupted && !$notAllow) {
                    $html.= '<div class="b2s-network-auth-list-info">' . __('Authorization is interrupted since', 'blog2social') . ' ' . ($sprache == 'en' ? $v['expiredDate'] : date('d.m.Y', strtotime($v['expiredDate']))) . '</div>';
                }

                $html .= '<span class="b2s-network-item-auth-type">' . (($isDeprecated) ? '<span class="glyphicon glyphicon-exclamation-sign glyphicon-info"></span> ' : '') . ($networkId == 19 && isset($this->networkKindName[$v['networkKind']]) ? $this->networkKindName[$v['networkKind']].'-' :'') . __('Page', 'blog2social') . (($networkId == 19 && (int) $v['networkKind'] == 0) ? ' <span class="hidden-xs">('.__('Employer Branding','blog2social').')</span>' : '').'</span>: <span class="b2s-network-item-auth-user-name">' . stripslashes($v['networkUserName']) . '</span> ';

                if (!empty($mandantName)) {
                    $html .='<span class="b2s-network-mandant-name">(' . $mandantName . ')</span> ';
                }
                $html .='</div>';
                $html .='<div class="pull-right">';
                $html .= '<a class="b2s-network-item-auth-list-btn-delete b2s-add-padding-network-delete pull-right" data-network-type="1" data-network-id="' . $networkId . '" data-network-auth-id="' . $v['networkAuthId'] . '" href="#"><span class="glyphicon  glyphicon-trash glyphicon-grey"></span></a>';
                if (!$notAllow && !$isDeprecated) {
                    $html .= '<a href="#" onclick="wop(\'' . $b2sAuthUrl . '&choose=page&update=' . $v['networkAuthId'] . '\', \'Blog2Social Network\'); return false;" class="b2s-network-auth-btn b2s-network-auth-update-btn b2s-add-padding-network-refresh pull-right" data-network-auth-id="' . $v['networkAuthId'] . '"><span class="glyphicon  glyphicon-refresh glyphicon-grey"></span></a>';
                    if ($v['expiredDate'] == '0000-00-00' || $v['expiredDate'] > date('Y-m-d')) {
                        if (isset($this->modifyBoardAndGroup[$networkId])) {
                            if (in_array(1, $this->modifyBoardAndGroup[$networkId]['TYPE'])) {
                                $html .='<a href="#" class="pull-right b2s-modify-board-and-group-network-btn b2s-add-padding-network-edit" data-modal-title="' . $this->modifyBoardAndGroup[$networkId]['TITLE'] . '" data-network-auth-id="' . $v['networkAuthId'] . '" data-network-id="' . $networkId . '" data-network-type="1"><span class="glyphicon glyphicon-pencil glyphicon-grey"></span></a>';
                                $isEdit = true;
                            }
                        }
                    }
                }

                //Sched Manager since V 5.1.0
                if (B2S_PLUGIN_USER_VERSION > 0) {
                    $html .='<span class="b2s-sched-manager-time-area pull-right ' . (!$isEdit ? 'b2s-sched-manager-add-padding' : '') . ' hidden-xs" style="' . (($isInterrupted || $notAllow) ? 'display:none;' : '') . '">
                        <input class="form-control b2s-box-sched-time-input b2s-settings-sched-item-input-time" type="text" value="' . $this->getUserSchedTime($v['networkAuthId'], $networkId, 1, 'time') . '" readonly=""  data-network-auth-id="' . $v['networkAuthId'] . '" data-network-mandant-id="' . $mandantId . '" data-network-id="' . $networkId . '" data-network-type="1" data-network-container-mandant-id="' . $containerMandantId . '" name="b2s-user-sched-data[time][' . $v['networkAuthId'] . ']">
                        </span>';
                    $html .='<span class="b2s-sched-manager-day-area pull-right hidden-xs" style="' . (($isInterrupted || $notAllow) ? 'display:none;' : '') . '"><span class="b2s-sched-manager-item-input-day-btn-minus" data-network-auth-id="' . $v['networkAuthId'] . '">-</span> <span class="b2s-text-middle">+</span> <input type="text" class="b2s-sched-manager-item-input-day" data-network-auth-id="' . $v['networkAuthId'] . '" data-network-mandant-id="' . $mandantId . '" data-network-id="' . $networkId . '" data-network-type="1" data-network-container-mandant-id="' . $containerMandantId . '"  name="b2s-user-sched-data[delay_day][' . $v['networkAuthId'] . ']" value="' . $this->getUserSchedTime($v['networkAuthId'], $networkId, 1, 'day') . '" readonly> <span class="b2s-text-middle">' . __('Days', 'blog2social') . '</span> <span class="b2s-sched-manager-item-input-day-btn-plus" data-network-auth-id="' . $v['networkAuthId'] . '">+</span></span>';
                } else {
                    $html .='<span class="b2s-sched-manager-premium-area pull-right hidden-xs"><span class="label label-success"><a href="#" class="btn-label-premium" data-toggle="modal" data-target="#b2sInfoSchedTimesModal">' . __('PREMIUM', 'blog2social') . '</a></span></span>';
                }

                $html .='</div>';

                $html .= '<div class="clearfix"></div></li>';
            }
        }
        if (isset($networkData[2])) {
            foreach ($networkData[2] as $k => $v) {

                $isDeprecated = ($networkId == 8 || $networkId == 10) ? true : false;
                $notAllow = ($v['notAllow'] !== false) ? true : false;
                $isInterrupted = ($v['expiredDate'] != '0000-00-00' && $v['expiredDate'] <= date('Y-m-d')) ? true : false;

                $html .= '<li class="b2s-network-item-auth-list-li ' . (($isDeprecated) ? 'b2s-label-info-border-left deprecated' : (($notAllow) ? 'b2s-label-warning-border-left' : (($isInterrupted) ? 'b2s-label-danger-border-left' : ''))) . '" data-network-auth-id="' . $v['networkAuthId'] . '" data-network-mandant-id="' . $mandantId . '" data-network-id="' . $networkId . '" data-network-type="2">';

                $html .='<div class="pull-left">';

                if ($notAllow) {
                    $html.= '<div class="b2s-network-auth-list-info"><span class="glyphicon glyphicon-remove-circle"></span> ' . __('To reactivate this connection,', 'blog2social') . ' <a href="' . B2S_Tools::getSupportLink('affiliate') . '"target="_blank">' . __('please upgrade', 'blog2social') . '</a></div>';
                }

                if ($isInterrupted && !$notAllow) {
                    $html.= '<div class="b2s-network-auth-list-info">' . __('Authorization is interrupted since', 'blog2social') . ' ' . ($sprache == 'en' ? $v['expiredDate'] : date('d.m.Y', strtotime($v['expiredDate']))) . '</div>';
                }

                $html .= '<span class="b2s-network-item-auth-type">' . (($isDeprecated) ? '<span class="glyphicon glyphicon-exclamation-sign glyphicon-info"></span> ' : '') . __('Group', 'blog2social') . '</span>: <span class="b2s-network-item-auth-user-name">' . stripslashes($v['networkUserName']) . '</span> ';

                if (!empty($mandantName)) {
                    $html .='<span class="b2s-network-mandant-name">(' . $mandantName . ')</span> ';
                }
                $html .='</div>';
                $html .='<div class="pull-right">';
                $html .= '<a class="b2s-network-item-auth-list-btn-delete b2s-add-padding-network-delete pull-right" data-network-type="2" data-network-id="' . $networkId . '" data-network-auth-id="' . $v['networkAuthId'] . '" href="#"><span class="glyphicon  glyphicon-trash glyphicon-grey"></span></a>';
                if (!$notAllow && !$isDeprecated) {
                    $html .= '<a href="#" onclick="wop(\'' . $b2sAuthUrl . '&choose=group&update=' . $v['networkAuthId'] . '\', \'Blog2Social Network\'); return false;" class="b2s-network-auth-btn b2s-network-auth-update-btn b2s-add-padding-network-refresh pull-right" data-network-auth-id="' . $v['networkAuthId'] . '"><span class="glyphicon  glyphicon-refresh glyphicon-grey"></span></a>';
                    if ($v['expiredDate'] == '0000-00-00' || $v['expiredDate'] > date('Y-m-d')) {
                        if (isset($this->modifyBoardAndGroup[$networkId])) {
                            if (in_array(2, $this->modifyBoardAndGroup[$networkId]['TYPE'])) {
                                $html .='<a href="#" class="pull-right b2s-modify-board-and-group-network-btn b2s-add-padding-network-edit" data-modal-title="' . $this->modifyBoardAndGroup[$networkId]['TITLE'] . '" data-network-auth-id="' . $v['networkAuthId'] . '" data-network-id="' . $networkId . '" data-network-type="2"><span class="glyphicon glyphicon-pencil glyphicon-grey"></span></a>';
                                $isEdit = true;
                            }
                        }
                    }
                }

                //Sched Manager since V 5.1.0
                if (B2S_PLUGIN_USER_VERSION > 0) {
                    $html .='<span class="b2s-sched-manager-time-area pull-right ' . (!$isEdit ? 'b2s-sched-manager-add-padding' : '') . ' hidden-xs" style="' . (($isInterrupted || $notAllow) ? 'display:none;' : '') . '">
                        <input class="form-control b2s-box-sched-time-input b2s-settings-sched-item-input-time" type="text" value="' . $this->getUserSchedTime($v['networkAuthId'], $networkId, 2, 'time') . '" readonly="" data-network-auth-id="' . $v['networkAuthId'] . '" data-network-mandant-id="' . $mandantId . '" data-network-id="' . $networkId . '" data-network-type="2" data-network-container-mandant-id="' . $containerMandantId . '" name="b2s-user-sched-data[time][' . $v['networkAuthId'] . ']">
                        </span>';
                    $html .='<span class="b2s-sched-manager-day-area pull-right hidden-xs" style="' . (($isInterrupted || $notAllow) ? 'display:none;' : '') . '"><span class="b2s-sched-manager-item-input-day-btn-minus" data-network-auth-id="' . $v['networkAuthId'] . '">-</span> <span class="b2s-text-middle">+</span> <input type="text" class="b2s-sched-manager-item-input-day" data-network-auth-id="' . $v['networkAuthId'] . '" data-network-mandant-id="' . $mandantId . '" data-network-id="' . $networkId . '" data-network-type="2" data-network-container-mandant-id="' . $containerMandantId . '"  name="b2s-user-sched-data[delay_day][' . $v['networkAuthId'] . ']" value="' . $this->getUserSchedTime($v['networkAuthId'], $networkId, 2, 'day') . '" readonly> <span class="b2s-text-middle">' . __('Days', 'blog2social') . '</span> <span class="b2s-sched-manager-item-input-day-btn-plus" data-network-auth-id="' . $v['networkAuthId'] . '">+</span></span>';
                } else {
                    $html .='<span class="b2s-sched-manager-premium-area pull-right hidden-xs"><span class="label label-success"><a href="#" class="btn-label-premium" data-toggle="modal" data-target="#b2sInfoSchedTimesModal">' . __('PREMIUM', 'blog2social') . '</a></span></span>';
                }

                $html .='</div>';

                $html .= '<div class="clearfix"></div></li>';
            }
        }
        return $html;
    }

    private function convertAuthData($auth) {
        $convertAuth = array();
        foreach ($auth as $k => $value) {
            $convertAuth[$value->mandantId][$value->networkId][$value->networkType][] = array(
                'networkAuthId' => $value->networkAuthId,
                'networkUserName' => $value->networkUserName,
                'expiredDate' => $value->expiredDate,
                'networkKind' => $value->networkKind,
                'notAllow' => (isset($value->notAllow) ? $value->notAllow : false)
            );
        }
        return $convertAuth;
    }

    //New >V5.1.0 Seeding
    private function getUserSchedTime($network_auth_id = 0, $network_id = 0, $network_type = 0, $type = 'time') { //type = time,day
        //new > v5.1.0
        if ($this->userSchedData !== false) {
            if (is_array($this->userSchedData) && isset($this->userSchedData['delay_day'][$network_auth_id]) && isset($this->userSchedData['time'][$network_auth_id]) && !empty($this->userSchedData['time'][$network_auth_id])) {
                if ($type == 'time') {
                    $slug = ($this->lang == 'en') ? 'h:i A' : 'H:i';
                    return date($slug, strtotime(date('Y-m-d ' . $this->userSchedData['time'][$network_auth_id] . ':00')));
                }
                if ($type == 'day') {
                    return (int) $this->userSchedData['delay_day'][$network_auth_id];
                }
            }
        }
        //old < 5.1.0 load data
        if (!empty($this->userSchedDataOld) && is_array($this->userSchedDataOld)) {
            if ($type == 'time') {
                foreach ($this->userSchedDataOld as $k => $v) {
                    if ((int) $network_id == (int) $v->network_id && (int) $network_type == (int) $v->network_type) {
                        $slug = ($this->lang == 'en') ? 'h:i A' : 'H:i';
                        return date($slug, strtotime(date('Y-m-d ' . $v->sched_time . ':00')));
                    }
                }
            }
        }
        if ($type == 'day') {
            return 0;
        }
        return null;
    }

    //Old < 5.1.0
    private function getSchedDataByUser() {
        global $wpdb;
        $saveSchedData = null;
        //if exists
        if ($wpdb->get_var("SHOW TABLES LIKE 'b2s_post_sched_settings'") == 'b2s_post_sched_settings') {
            $saveSchedData = $wpdb->get_results($wpdb->prepare("SELECT network_id, network_type, sched_time FROM b2s_post_sched_settings WHERE blog_user_id= %d", B2S_PLUGIN_BLOG_USER_ID));
        }
        return $saveSchedData;
    }

}
