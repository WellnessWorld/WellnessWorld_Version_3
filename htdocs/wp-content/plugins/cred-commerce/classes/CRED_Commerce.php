<?php

/**
 * MainClass
 *
 * Main class of the plugin
 * Class encapsulates all hook handlers
 *
 */
final class CRED_Commerce {

    private static $formdata = false;
    private static $handler = false;
    private static $forced_handler = false;

    /*
     * Initialize plugin enviroment
     */
    public static function init($forced_handler = false, $bypass_init_hook = false) {
        // force a handler, eg for import/export using Views Demo downloader
        self::$forced_handler = $forced_handler;

        if ($bypass_init_hook)
            self::_init_();
        else
        // NOTE Early Init, in order to catch up with ecommerce hooks
            add_action('init', array(__CLASS__, '_init_'), 3); // early init in order to have all custom post types and taxonomies registered
    }

    public static function _init_() {
		
		
		
        global $wp_version, $post, $pagenow;

        // load translations from locale
        load_plugin_textdomain('wp-cred-pay', false, CRED_COMMERCE_LOCALE_PATH);

        if (is_admin()) {
            if ('1' === get_option('cred_commerce_activated')) {
                delete_option('cred_commerce_activated');
                // add a notice
                add_action('admin_notices', array(__CLASS__, 'configureNotice'), 5);
            }
            // setup js, css assets
            add_action('admin_enqueue_scripts', array(__CLASS__, 'onAdminEnqueueScripts'));
            // Add settings link on plugin page
            add_filter("plugin_action_links_" . CRED_COMMERCE_PLUGIN_BASENAME, array(__CLASS__, 'addSettingsLink'));
        }

        // exit if Forms  not active or installed
        if (!defined('CRED_FE_VERSION')) {
            if (is_admin()) {
                if ('plugins.php' == $pagenow) {
                    // add a notice
                    add_action('admin_notices', array(__CLASS__, 'cred_commerce_display_notice'), 3);
                }
                // add dummy menu
                add_action('admin_menu', array(__CLASS__, 'addDummyMenuItems'));
            }
            return;
        }

        if (is_admin()) {

            add_action('cred_pe_general_settings', array(__CLASS__, 'cred_delete_draft_users'), 10, 2);

            // add this menu to  menus panel
            add_action('cred_admin_menu_after_forms', array(__CLASS__, 'addCREDMenuItems'), 3, 1);
            // After Forms 2.0
            add_filter('toolset_filter_register_menu_pages', array(__CLASS__, 'toolset_register_menu_pages'), 55);

            // add custom meta boxes for forms
            add_filter('cred_admin_register_meta_boxes', array(__CLASS__, 'registerMetaBoxes'), 20, 1);
            add_action('cred_admin_add_meta_boxes', array(__CLASS__, 'addMetaBoxes'), 20, 1);
            add_action('cred_user_admin_add_meta_boxes', array(__CLASS__, 'addUserMetaBoxes'), 20, 1);
            // hook to add extra data/fields in admin screen
            // add extra fields on notifications
            //add_action('cred_admin_notification_fields_before', array(__CLASS__, 'addCommerceExtraNotificationFields'), 10, 3);
            add_action('cred_admin_notification_notify_event_options_before', array(__CLASS__, 'addCommerceExtraNotificationEventsBefore'), 1, 3);
            add_action('cred_admin_notification_notify_event_options_before', array(__CLASS__, 'addCommerceExtraNotificationEventsBefore2'), 1, 3);
            add_action('cred_admin_notification_notify_event_options', array(__CLASS__, 'addCommerceExtraNotificationEvents'), 1, 3);
            add_action('cred_admin_notification_recipient_options_before', array(__CLASS__, 'addCommerceExtraNotificationRecipients'), 1, 3);
            add_action('cred_admin_notification_recipient_options_before', array(__CLASS__, 'addCommerceExtraNotificationRecipients2'), 1, 3);

            // add extra options on after submit action
            add_filter('cred_admin_submit_action_options', array(__CLASS__, 'addCommerceExtraPageOptions'), 10, 3);
            // add extra options on Notification codes
            add_filter('cred_admin_notification_subject_codes', array(__CLASS__, 'addCommerceExtraNotificationCodes'), 10, 4);
            add_filter('cred_admin_notification_body_codes', array(__CLASS__, 'addCommerceExtraNotificationCodes'), 10, 4);
            // add extra table columns to forms
            add_filter('manage_' . CRED_FORMS_CUSTOM_POST_NAME . '_posts_columns', array(__CLASS__, 'addCommerceExtraColumns'), 10, 1);
            add_filter('manage_' . CRED_USER_FORMS_CUSTOM_POST_NAME . '_posts_columns', array(__CLASS__, 'addCommerceExtraColumns'), 10, 1);
            // render extra table columns to forms
            add_filter('manage_' . CRED_FORMS_CUSTOM_POST_NAME . '_posts_custom_column', array(__CLASS__, 'renderCommerceExtraColumns'), 10, 2);
            add_filter('manage_' . CRED_USER_FORMS_CUSTOM_POST_NAME . '_posts_custom_column', array(__CLASS__, 'renderCommerceExtraColumns'), 10, 2);

            // save custom fields of forms
            add_action('cred_admin_save_form', array(__CLASS__, 'saveFormCustomFields'), 2, 2);
        }
        // localize custom fields of forms
        add_action('cred_localize_form', array(__CLASS__, 'localizeCommerceForm'), 2, 1);

        // setup extra admin hooks for other plugins
        self::setupExtraHooks();
    }

	/**
	 * Delete Draft Users HTML View
	 */
    public static function cred_delete_draft_users() {
        $confirm = __("Are you sure ?", 'wp-cred');
        $done = __("All draft users have been deleted succesfully", 'wp-cred');
        $error = __("Error in deleting draft users", 'wp-cred');
        echo '<div style="clear:both;margin-top:10px;"></div><label class="cred-label">'
        . '<a onclick="if (confirm(\'' . $confirm . '\')) { jQuery.get(\''
        . admin_url('admin-ajax.php?action=cred_delete_draft_users&amp;_wpnonce='
                . wp_create_nonce('cred-delete-draft-users')) . '\', function( data ) {
        if (data==\'OK\') alert(\'' . $done . '\'); else alert(\'' . $error . '\'); })} return false;"'
        . 'class="button-secondary" href="javascript:void(0);">'
        . __("Delete all Users Draft", 'wp-cred') . '</a></label>';
    }

	/**
	 * Commerce Display Notice
	 */
    public static function cred_commerce_display_notice() {
        ?>
        <div class="error">
            <p><?php _e('Toolset Forms Commerce plugin needs <a href="https://toolset.com/home/toolset-components/#cred" target="_blank"><strong>Toolset Forms</strong></a> to be installed and activated.', 'wp-cred-pay'); ?></p>
        </div>
        <?php
    }

    public static function addDummyMenuItems() {
        $menu_label = 'CRED';

        $cred_index = 'CRED_Commerce';
        add_menu_page($menu_label, $menu_label, CRED_COMMERCE_CAPABILITY, $cred_index, array(__CLASS__, 'CommerceSettingsPage'), '');
        // allow 3rd-party menu items to be included
        add_submenu_page($cred_index, __('Toolset Forms Commerce', 'wp-cred-pay'), __('Toolset Forms Commerce', 'wp-cred-pay'), CRED_COMMERCE_CAPABILITY, 'CRED_Commerce', array(__CLASS__, 'CommerceSettingsPage'));
    }

	/**
	 * Notice Configuration
	 */
    public static function configureNotice() {
        $settings_link = '<a href="' . admin_url('admin.php') . '?page=CRED_Commerce' . '">' . __('Configure', 'wp-cred-pay') . '</a>';
        ob_start();
        ?>
        <div class="updated"><p>
                <?php printf(__("Toolset Forms Commerce has been activated. %s", 'wp-cred-pay'), $settings_link); ?>
                <?php
                if (!defined('CRED_FE_VERSION') || version_compare(CRED_FE_VERSION, '1.2', '<'))
                    printf('<br />' . __("Toolset Forms Commerce requires Toolset Forms version %s or higher, to work correctly", 'wp-cred-pay'), '1.2');
                ?>
            </p></div>
        <?php
        echo ob_get_clean();
    }

	/**
	 * @return bool|string
	 */
    public static function getCurrentCommercePlugin() {
        global $woocommerce;

        if (class_exists('Woocommerce') && $woocommerce && isset($woocommerce->version) && version_compare($woocommerce->version, '2.0', '>=')) {
            return 'woocommerce';
        }

        return false;
    }

    public static function setupExtraHooks() {
        // init handler
	    if ( self::$forced_handler ) {
		    $handler = self::$forced_handler;
	    } else {
		    $handler = self::getCurrentCommercePlugin();
	    }

        if ($handler) {
            self::$handler = CREDC_Loader::get('CLASS/Form_Handler');
            self::$handler->init(
                    CRED_Commerce_Plugin_Factory::getPlugin($handler), CREDC_Loader::get('MODEL/Main')
            );

	        /**
             * [cred_checkout_message] shortcode
	         * @deprecated since 1.0
	         */
            //add_shortcode('cred_checkout_message', array(__CLASS__, 'checkoutMessage'));
	        /**
             * [cred_thankyou_message] shortcode
	         * @deprecated since 1.0
	         */
            //add_shortcode('cred_thankyou_message', array(__CLASS__, 'thankyouMessage'));

	        /**
	         * WooCommerce Thankyou Message
	         */
            add_action('woocommerce_thankyou', 'CRED_Commerce::cred_thankyou_message', 10);
	        /**
	         * WooCommerce Checkout Message
	         */
            add_action('woocommerce_checkout_before_customer_details', 'CRED_Commerce::cred_checkout_message', 10);

	        /**
	         * add extra plceholder codes
	         */
            add_filter('cred_subject_notification_codes', array(__CLASS__, 'extraNotificationCodes'), 10, 3);
            add_filter('cred_body_notification_codes', array(__CLASS__, 'extraNotificationCodes'), 10, 3);

	        /**
	         * add extra notification recipient options
	         */
            add_filter('cred_notification_recipients', array(__CLASS__, 'extraNotificationRecipients'), 10, 4);

	        /**
	         * add commerce data on export/import process
	         */
            add_filter('cred_export_forms', array(__CLASS__, 'exportCommerceForms'), 1, 1);
            add_filter('cred_import_form', array(__CLASS__, 'importCommerceForm'), 1, 3);
        }
    }

	/**
	 * @param array $data
	 */
    public static function localizeCommerceForm($data) {
        if (!isset($data['post']))
            return;
        $model = CREDC_Loader::get('MODEL/Main');
        $form_id = $data['post']->ID;
        $pt = get_post_type($form_id);
        $is_user_form = ($pt == CRED_USER_FORMS_CUSTOM_POST_NAME);
        $prefix = $is_user_form ? 'cred-user-form-' : 'cred-form-';
        $midfix = $data['post']->post_title . "-";
        $form = $model->getForm($form_id, false);

        if ($form->isCommerce) {
            // localise messages
            if (isset($form->commerce['messages']) && isset($form->commerce['messages']['checkout'])) {
                cred_translate_register_string($prefix . $midfix . $form_id, 'cred_commerce_checkout_message', $form->commerce['messages']['checkout'], false);
            }
            if (isset($form->commerce['messages']) && isset($form->commerce['messages']['thankyou'])) {
                cred_translate_register_string($prefix . $midfix . $form_id, 'cred_commerce_thankyou_message', $form->commerce['messages']['thankyou'], false);
            }
        }
    }

	/**
	 * @param WP_Post[] $forms
	 *
	 * @return WP_Post[]
	 */
    public static function exportCommerceForms($forms) {
        $model = CREDC_Loader::get('MODEL/Main');
        foreach (array_keys($forms) as $k) {
            $data = $model->getFormCustomField($forms[$k]->ID, 'commerce');
            if ($data) {
                if (!isset($forms[$k]->meta))
                    $forms[$k]->meta = array();

                if (isset($data['product']))
                    $data['product'] = self::$handler->getRelativeProduct($data['product']);

                $forms[$k]->meta['commerce'] = $data;
            }
        }
        return $forms;
    }

	/**
	 * @param array $results
	 * @param int $form_id
	 * @param array $data
	 *
	 * @return array
	 */
    public static function importCommerceForm($results, $form_id, $data) {
        if (isset($data['meta']) && isset($data['meta']['commerce'])) {
            $model = CREDC_Loader::get('MODEL/Main');
            $cdata = $data['meta']['commerce'];
            if (isset($cdata['product'])) {
                $product = self::$handler->getAbsoluteProduct($cdata['product']);
                if (!$product && isset($results['errors'])) {
                    $results['errors'][] = sprintf(__('Product %s does not exist on this site. You will need to set the Toolset Forms Commerce settings for <a href="%s">form %s</a> manually.', 'wp-cred-pay'), $cdata['product'], CRED_CRED::getFormEditLink($form_id), $form_id);
                }
                $cdata['product'] = $product;
            }
            $model->updateFormCustomField($form_id, 'commerce', $cdata);
        }
        return $results;
    }

	/**
     * When form is submitted from admin, save the custom fields which describe the form configuration to DB
     *
	 * @param int $form_id
	 * @param WP_Post $form
	 */
    public static function saveFormCustomFields($form_id, $form) {
	    if ( isset( $_POST[ '_cred_commerce' ] ) ) {
		    $data = $_POST[ '_cred_commerce' ];
		    if ( isset( $data[ 'notification' ][ 'notifications' ] )
                && is_array( $data[ 'notification' ][ 'notifications' ] ) ) // normalize order of fields
		    {
			    $data[ 'notification' ][ 'notifications' ] = array_values( $data[ 'notification' ][ 'notifications' ] );
		    }
		    $model = CREDC_Loader::get( 'MODEL/Main' );
		    $model->updateForm( $form_id, $data );
	    }
    }

	/**
     * Add custom classes to our metaboxes, so they can be handled as needed
     *
	 * @param array $cred_meta_boxes
	 *
	 * @return array
	 */
    public static function registerMetaboxes($cred_meta_boxes) {
        array_push($cred_meta_boxes, 'credcommercediv');
        return $cred_meta_boxes;
    }

	/**
     * Add meta boxes in admin pages which manipulate forms
     *
	 * @param WP_Post $form
	 */
    public static function addMetaBoxes($form) {
        global $pagenow;
        // commerce meta box
        add_meta_box('credcommercediv', __('Toolset Forms Commerce', 'wp-cred'), array(__CLASS__, 'addCommerceMetaBox'), null, 'normal', 'high', array());
    }

	/**
	 * @param WP_Post $form
	 */
    public static function addUserMetaBoxes($form) {
        global $pagenow;
        // commerce meta box
        add_meta_box('credcommercediv', __('Toolset Forms Commerce', 'wp-cred'), array(__CLASS__, 'addUserCommerceMetaBox'), null, 'normal', 'high', array());
    }

	/**
     * Functions to display actual meta boxes (better to use templates here.., done using template snipetts to separate the code a bit)
     *
	 * @param $form
	 */
    public static function addCommerceMetaBox($form) {
        if (!self::$formdata) {
            $model = CREDC_Loader::get('MODEL/Main');
            self::$formdata = $model->getForm($form->ID, false);
        }
        if (isset(self::$formdata->commerce))
            $data = self::$formdata->commerce;
        else
            $data = array();

        $ecommerce = true;
        $productlink = '';
        $commerceplugin = 'Woocommerce';
        if (self::$handler) {
            $products = self::$handler->getProducts();
            $productlink = '<a href="' . self::$handler->getNewProductLink() . '">' . __('Add products', 'wp-cred-pay') . '</a>';
            $producthref = self::$handler->getNewProductLink();
        } else {
            $ecommerce = false;
            $productlink = '<a href="' . admin_url('admin.php') . '?page=CRED_Commerce' . '">' . __('Compatible e-commerce plugins', 'wp-cred-pay') . '</a>';
            $producthref = admin_url('admin.php') . '?page=CRED_Commerce';
            $products = array();
        }
        echo CREDC_Loader::tpl('commerce-settings-meta-box', array(
            'data' => $data,
            'codes' => array_keys(self::getExtraCommerceCodes()),
            'products' => $products,
            'productlink' => $productlink,
            'producthref' => $producthref,
            'commerceplugin' => $commerceplugin,
            'ecommerce' => $ecommerce
        ));
    }

	/**
	 * @param WP_Post $form
	 */
    public static function addUserCommerceMetaBox($form) {
        if (!self::$formdata) {
            $model = CREDC_Loader::get('MODEL/Main');
            self::$formdata = $model->getForm($form->ID, false);
        }
        if (isset(self::$formdata->commerce))
            $data = self::$formdata->commerce;
        else
            $data = array();

        $ecommerce = true;
        $productlink = '';
        $commerceplugin = 'Woocommerce';
        if (self::$handler) {
            $products = self::$handler->getProducts();
            $productlink = '<a href="' . self::$handler->getNewProductLink() . '">' . __('Add products', 'wp-cred-pay') . '</a>';
            $producthref = self::$handler->getNewProductLink();
        } else {
            $ecommerce = false;
            $productlink = '<a href="' . admin_url('admin.php') . '?page=CRED_Commerce' . '">' . __('Compatible e-commerce plugins', 'wp-cred-pay') . '</a>';
            $producthref = admin_url('admin.php') . '?page=CRED_Commerce';
            $products = array();
        }
        echo CREDC_Loader::tpl('commerce-settings-user-meta-box', array(
            'data' => $data,
            'codes' => array_keys(self::getExtraCommerceCodes()),
            'products' => $products,
            'productlink' => $productlink,
            'producthref' => $producthref,
            'commerceplugin' => $commerceplugin,
            'ecommerce' => $ecommerce
        ));
    }

	/**
	 * @param array $recipients
	 * @param array $notification
	 * @param int $form_id
	 * @param int $post_id
	 *
	 * @return array
	 */
    public static function extraNotificationRecipients($recipients, $notification, $form_id, $post_id) {
        $model = CREDC_Loader::get('MODEL/Main');
        $form = $model->getForm($form_id, false);

        if ($form->isCommerce) {
            if (in_array('customer', $notification['to']['type'])) {
                $customer = self::$handler->getCustomer($post_id, $form_id);
                if ($customer) {
                    $to = (isset($notification['to']['customer']) && isset($notification['to']['customer']['to_type'])) ? $notification['to']['customer']['to_type'] : 'to';
                    $recipients[] = array(
                        'to' => $to,
                        'address' => isset($customer->user_email) ? $customer->user_email : false,
                        'name' => (isset($customer->user_firstname) && !empty($customer->user_firstname)) ? $customer->user_firstname : false,
                        'lastname' => (isset($customer->user_lasttname) && !empty($customer->user_lasttname)) ? $customer->user_lastname : false
                    );
                }
            }
        }

        return $recipients;
    }

	/**
	 * @param array $codes
	 * @param int $form_id
	 * @param int $post_id
	 *
	 * @return array
	 */
    public static function extraNotificationCodes($codes, $form_id, $post_id) {
        $model = CREDC_Loader::get('MODEL/Main');
        $form = $model->getForm($form_id, false);
        
        $post_type = get_post_type($form_id);

        if ($form->isCommerce) {
            $product = false;
            if ('post' == $form->associateProduct && isset($form->productField))
                $product = $model->getPostMeta($post_id, $form->productField);
            elseif (isset($form->product))
                $product = $form->product;

            $product = self::$handler->getProduct($product);
            
            if ($product) {
                $codes['%%PRODUCT_ID%%'] = $product->ID;
                $codes['%%PRODUCT_NAME%%'] = $product->title;
                $codes['%%PRODUCT_PRICE%%'] = $product->price;
            }

            $customer = self::$handler->getCustomer($post_id, $form_id);
            
            if ($customer) {
                $codes['%%CUSTOMER_ID%%'] = $customer->ID;
                $codes['%%CUSTOMER_EMAIL%%'] = $customer->user_email;
                $codes['%%CUSTOMER_DISPLAYNAME%%'] = $customer->display_name;
                $codes['%%CUSTOMER_FIRSTNAME%%'] = $customer->user_firstname;
                $codes['%%CUSTOMER_LASTNAME%%'] = $customer->user_lastname;
            }
        }
        return $codes;
    }

	/**
	 * @param array $options
	 * @param string $action
	 * @param WP_Post $form
	 *
	 * @return array
	 */
    public static function addCommerceExtraPageOptions($options, $action, $form) {
        $options['cart'] = __('Go to cart page', 'wp-cred-pay');
        $options['checkout'] = __('Go to checkout page', 'wp-cred-pay');
        return $options;
    }

	/**
	 * @return array
	 */
    public static function getExtraCommerceCodes() {
        return array(
            // product
            '%%PRODUCT_ID%%' => __('Product ID', 'wp-cred-pay'),
            '%%PRODUCT_NAME%%' => __('Product Name', 'wp-cred-pay'),
            '%%PRODUCT_PRICE%%' => __('Product Price', 'wp-cred-pay'),
            // customer
            '%%CUSTOMER_ID%%' => __('Customer ID', 'wp-cred-pay'),
            '%%CUSTOMER_EMAIL%%' => __('Customer Email', 'wp-cred-pay'),
            '%%CUSTOMER_DISPLAYNAME%%' => __('Customer Display Name', 'wp-cred-pay'),
            '%%CUSTOMER_FIRSTNAME%%' => __('Customer First Name', 'wp-cred-pay'),
            '%%CUSTOMER_LASTNAME%%' => __('Customer Last Name', 'wp-cred-pay')
        );
    }

	/**
	 * @param array $options
	 * @param WP_Post $form
	 * @param int $ii
	 * @param array $notif
	 *
	 * @return array
	 */
    public static function addCommerceExtraNotificationCodes($options, $form, $ii, $notif) {
        $options = array_merge($options, self::getExtraCommerceCodes());
        return $options;
    }

	/**
	 * @param WP_Post $form
	 * @param array $data
	 * @param array $notification
	 */
    public static function addCommerceExtraNotificationRecipients($form, $data, $notification) {
        $is_user_form = $form->post_type == CRED_USER_FORMS_CUSTOM_POST_NAME;
        if ($is_user_form)
            return;
        ob_start();
        if (!$notification || empty($notification)) {
            // used for template, return dummy
            ?>
            <p>
                <label class='cred-label'>
                    <input data-cred-bind="{ 
                           validate: { 
                           required: {
                           actions: [
                           {action: 'validationMessage', domRef: '#notification_recipient_required-<?php echo $data[0]; ?>' },
                           {action: 'validateSection' }
                           ]
                           } 
                           } 
                           }" type='checkbox' class='cred-checkbox-10' name="<?php echo $data[1]; ?>" value="customer" />                           
                    <span><?php _e('Send this notification to the billing email', 'wp-cred-pay'); ?></span>
                </label>
                <span data-cred-bind="{ action: 'show', condition: '_cred[notification][notifications][<?php echo $data[0]; ?>][to][type] has customer' }">
                    <select style="width:60px" name="_cred[notification][notifications][<?php echo $data[0]; ?>][to][customer][to_type]">
                        <option value="to"><?php _e('To:', 'wp-cred'); ?></option>
                        <option value="cc"><?php _e('Cc:', 'wp-cred'); ?></option>
                        <option value="bcc"><?php _e('Bcc:', 'wp-cred'); ?></option>
                    </select><br />
                    <!--<input value="to" style="width:60px" name="_cred[notification][notifications][<?php echo $data[0]; ?>][to][customer][to_type]" type="hidden">-->
                </span>
            </p>
            <?php
        } else {
            // actual notification data
            $to_type = 'to';
            if (isset($notification['to']['customer']) && isset($notification['to']['customer']['to_type']))
                $to_type = $notification['to']['customer']['to_type'];
            ?>
            <p>
                <label class='cred-label'>
                    <input data-cred-bind="{ 
                           validate: { 
                           required: {
                           actions: [
                           {action: 'validationMessage', domRef: '#notification_recipient_required-<?php echo $data[0]; ?>' },
                           {action: 'validateSection' }
                           ]
                           } 
                           } 
                           }" type='checkbox' class='cred-checkbox-10' name="<?php echo $data[1]; ?>" value="customer" <?php if ($data[2] == 'customer') echo 'checked="checked"'; ?>/>
                    <span><?php _e('Send this notification to the billing email', 'wp-cred-pay'); ?></span>
                </label>
                <span data-cred-bind="{ action: 'show', condition: '_cred[notification][notifications][<?php echo $data[0]; ?>][to][type] has customer' }">
                    <select style="width:60px" name="_cred[notification][notifications][<?php echo $data[0]; ?>][to][customer][to_type]">
                        <option value="to" <?php if ('to' == $to_type) echo 'selected="selected"'; ?>><?php _e('To:', 'wp-cred'); ?></option>
                        <option value="cc" <?php if ('cc' == $to_type) echo 'selected="selected"'; ?>><?php _e('Cc:', 'wp-cred'); ?></option>
                        <option value="bcc" <?php if ('bcc' == $to_type) echo 'selected="selected"'; ?>><?php _e('Bcc:', 'wp-cred'); ?></option>
                    </select><br />
                    <!--<input value="to" style="width:60px" name="_cred[notification][notifications][<?php echo $data[0]; ?>][to][customer][to_type]" type="hidden">-->
                </span>
            </p>
            <?php
        }
        echo ob_get_clean();
    }

	/**
	 * @param WP_Post $form
	 * @param array $data
	 * @param array $notification
	 */
    public static function addCommerceExtraNotificationRecipients2($form, $data, $notification) {
        $is_user_form = $form->post_type == CRED_USER_FORMS_CUSTOM_POST_NAME;

        ob_start();
        if (!$notification || empty($notification)) {
            // used for template, return dummy
            ?>

            <?php
        } else {
            // actual notification data
            $to_type = 'to';
            if (isset($notification['to']['customer']) && isset($notification['to']['customer']['to_type']))
                $to_type = $notification['to']['customer']['to_type'];

            if ($is_user_form) {
                ?>
                <p>
                    <label class='cred-label'>
                        <input data-cred-bind="{ validate: {
                               required: {
                               actions: [
                               {action: 'validationMessage', domRef: '#notification_recipient_required-<?php echo $data[0]; ?>' },
                               {action: 'validateSection' }
                               ]
                               }
                               } }" type='checkbox' class='cred-checkbox-10' name='<?php echo $data[1]; ?>' value='customer' <?php if ($data[2] == 'customer') echo 'checked="checked"'; ?> />

                        <span><?php _e('Send this notification to the billing email', 'wp-cred-pay'); ?></span>
                    </label>
                </p>           
                <?php
            }
        }
        echo ob_get_clean();
    }

	/**
	 * @param WP_Post $form
	 * @param array $data
	 * @param array $notification
	 */
    public static function addCommerceExtraNotificationEventsBefore($form, $data, $notification) {
        ob_start();
        if (!$notification) {
            // used for template, return dummy
            ?>
            <p>
                <label>
                    <input data-cred-bind="{ 
                           validate: { 
                           required: {
                           actions: [
                           {action: 'validationMessage', domRef: '#notification_event_required-<?php echo $data[0]; ?>' },
                           {action: 'validateSection' }
                           ]
                           } 
                           } 
                           }" type="radio" class="cred-radio-10 cred-commerce-event-type" name="<?php echo $data[1]; ?>" value="order_created" />
                    <span><?php _e('When submitting the form with payment details', 'wp-cred-pay'); ?></span>
                </label>
            </p>
            <?php
        } else {
            // actual notification data
            ?>
            <p>
                <label>
                    <input data-cred-bind="{ 
                           validate: { 
                           required: {
                           actions: [
                           {action: 'validationMessage', domRef: '#notification_event_required-<?php echo $data[0]; ?>' },
                           {action: 'validateSection' }
                           ]
                           } 
                           } 
                           }" type="radio" class="cred-radio-10 cred-commerce-event-type" name="<?php echo $data[1]; ?>" value="order_created" <?php if ($data[2] == 'order_created') echo 'checked="checked"'; ?> />
                    <span><?php _e('When submitting the form with payment details', 'wp-cred-pay'); ?></span>
                </label>
            </p>
            <?php
        }
        echo ob_get_clean();
    }

	/**
	 * @param WP_Post $form
	 * @param array $data
	 * @param array $notification
	 */
    public static function addCommerceExtraNotificationEventsBefore2($form, $data, $notification) {
        if ($form->post_type == CRED_FORMS_CUSTOM_POST_NAME)
            return;
        ob_start();
        if (!$notification) {
            // used for template, return dummy
            ?>
            <p>
                <label>
                    <input data-cred-bind="{ 
                           validate: { 
                           required: {
                           actions: [
                           {action: 'validationMessage', domRef: '#notification_event_required-<?php echo $data[0]; ?>' },
                           {action: 'validateSection' }
                           ]
                           } 
                           } 
                           }" type="radio" class="cred-radio-10 cred-commerce-event-type" name="<?php echo $data[1]; ?>" value="payment_complete" />
                    <span><?php _e('When payment is complete', 'wp-cred-pay'); ?></span>
                </label>
            </p>
            <?php
        } else {
            // actual notification data
            ?>
            <p>
                <label>
                    <input data-cred-bind="{ 
                           validate: { 
                           required: {
                           actions: [
                           {action: 'validationMessage', domRef: '#notification_event_required-<?php echo $data[0]; ?>' },
                           {action: 'validateSection' }
                           ]
                           } 
                           } 
                           }" type="radio" class="cred-radio-10 cred-commerce-event-type" name="<?php echo $data[1]; ?>" value="payment_complete" <?php if ($data[2] == 'payment_complete') echo 'checked="checked"'; ?> />
                    <span><?php _e('When payment is complete', 'wp-cred-pay'); ?></span>
                </label>
            </p>
            <?php
        }
        echo ob_get_clean();
    }

	/**
	 * @param WP_Post $form
	 * @param array $data
	 * @param array $notification
	 */
    public static function addCommerceExtraNotificationEvents($form, $data, $notification) {
        if ($form->post_type == CRED_USER_FORMS_CUSTOM_POST_NAME)
            return;
        ob_start();
        if (!$notification) {
            // used for template, return dummy
            ?>
            <p>
                <label>
                    <input data-cred-bind="{ 
                           validate: { 
                           required: {
                           actions: [
                           {action: 'validationMessage', domRef: '#notification_event_required-<?php echo $data[0]; ?>' },
                           {action: 'validateSection' }
                           ]
                           } 
                           } 
                           }" type="radio" class="cred-radio-10 cred-commerce-event-type" name="<?php echo $data[1]; ?>" value="order_modified" />
                    <span><?php _e('When the purchase status changes to:', 'wp-cred-pay'); ?></span>
                </label>
                <span data-cred-bind="{ action: 'show', condition: '<?php echo $data[1]; ?>=order_modified' }">
                    <select class="cred_commerce_when_order_status_changes" name="_cred[notification][notifications][<?php echo $data[0]; ?>][event][order_status]">
                        <option value='pending'><?php _e('Pending', 'wp-cred-pay'); ?></option>
                        <option value='failed'><?php _e('Failed', 'wp-cred-pay'); ?></option>
                        <option value='processing'><?php _e('Processing', 'wp-cred-pay'); ?></option>
                        <option value='completed'><?php _e('Completed', 'wp-cred-pay'); ?></option>
                        <option value='on-hold'><?php _e('On-Hold', 'wp-cred-pay'); ?></option>
                        <option value='cancelled'><?php _e('Cancelled', 'wp-cred-pay'); ?></option>
                        <option value='refunded'><?php _e('Refunded', 'wp-cred-pay'); ?></option>
                    </select>
                </span>
            </p>
            <?php
        } else {
            if (!self::$formdata) {
                $model = CREDC_Loader::get('MODEL/Main');
                self::$formdata = $model->getForm($form->ID, false);
            }
            $order_status = null;
            if (
                    self::$formdata->isCommerce &&
                    isset($notification['event']['order_status'])
            )
                $order_status = $notification['event']['order_status'];
            // actual notification data
            ?>
            <p>
                <label>
                    <input data-cred-bind="{ 
                           validate: { 
                           required: {
                           actions: [
                           {action: 'validationMessage', domRef: '#notification_event_required-<?php echo $data[0]; ?>' },
                           {action: 'validateSection' }
                           ]
                           } 
                           } 
                           }" type="radio" class="cred-radio-10 cred-commerce-event-type" name="<?php echo $data[1]; ?>" value="order_modified" <?php if ($data[2] == 'order_modified') echo 'checked="checked"'; ?> />
                    <span><?php _e('When the purchase status changes to:', 'wp-cred-pay'); ?></span>
                </label>
                <span data-cred-bind="{ action: 'show', condition: '<?php echo $data[1]; ?>=order_modified' }">
                    <select class="cred_commerce_when_order_status_changes" name="_cred[notification][notifications][<?php echo $data[0]; ?>][event][order_status]">
                        <option value='pending' <?php if ('pending' == $order_status) echo 'selected="selected"'; ?>><?php _e('Pending', 'wp-cred-pay'); ?></option>
                        <option value='failed' <?php if ('failed' == $order_status) echo 'selected="selected"'; ?>><?php _e('Failed', 'wp-cred-pay'); ?></option>
                        <option value='processing' <?php if ('processing' == $order_status) echo 'selected="selected"'; ?>><?php _e('Processing', 'wp-cred-pay'); ?></option>
                        <option value='completed' <?php if ('completed' == $order_status) echo 'selected="selected"'; ?>><?php _e('Completed', 'wp-cred-pay'); ?></option>
                        <option value='on-hold' <?php if ('on-hold' == $order_status) echo 'selected="selected"'; ?>><?php _e('On-Hold', 'wp-cred-pay'); ?></option>
                        <option value='cancelled' <?php if ('cancelled' == $order_status) echo 'selected="selected"'; ?>><?php _e('Cancelled', 'wp-cred-pay'); ?></option>
                        <option value='refunded' <?php if ('refunded' == $order_status) echo 'selected="selected"'; ?>><?php _e('Refunded', 'wp-cred-pay'); ?></option>
                    </select>
                </span>
            </p>
            <?php
        }
        echo ob_get_clean();
    }

	/**
	 * @param int $order_id
	 */
    public static function cred_thankyou_message($order_id) {
        $added = array();
        $message = '';

        $cred_meta = get_post_meta($order_id, '_cred_meta', true);
        if ($cred_meta && '' != $cred_meta)
            $cred_meta = maybe_unserialize($cred_meta);
        else
            $cred_meta = false;

        //$cred_data = self::$handler->getCredData();
        $data = @$cred_meta[0];

        $model = CREDC_Loader::get('MODEL/Main');
        $form = $model->getForm($data['cred_form_id'], false);

        if (isset($data['cred_form_id'])) {
            if (isset($form->commerce['messages']['thankyou']) &&
                    !in_array($form->commerce['messages']['thankyou'], $added)) {
                $added[] = $form->commerce['messages']['thankyou'];
                // allow WPML string localization
                $message.=do_shortcode(cred_translate(
                                'cred_commerce_thankyou_message', $form->commerce['messages']['thankyou'], 'cred-form-' . $form->ID
                ));
                $message.=" ";
            }
        }
        //return '<pre>'.print_r($cred_data, true).'</pre>';
        echo (!empty($message)) ? stripslashes($message) : $message;
    }

	/**
	 * @param string $checkout
	 */
    public static function cred_checkout_message($checkout) {
        $added = array();
        $message = '';

        $cred_data = self::$handler->getCredData();
        $data = @$cred_data[0];

        $model = CREDC_Loader::get('MODEL/Main');
        $form = $model->getForm($data['cred_form_id'], false);

        if (isset($data['cred_form_id'])) {
            if (isset($form->commerce['messages']['checkout']) &&
                    !in_array($form->commerce['messages']['checkout'], $added)) {
                $added[] = $form->commerce['messages']['checkout'];
                // allow WPML string localization
                $message.=do_shortcode(cred_translate(
                                'cred_commerce_checkout_message', $form->commerce['messages']['checkout'], 'cred-form-' . $form->ID
                ));
                $message.=" ";
            }
        }
        //return '<pre>'.print_r($cred_data, true).'</pre>';
        echo (!empty($message)) ? stripslashes($message) : $message;
    }

	/**
	 * @param array $atts
	 * @param string $content
	 *
	 * @return string
	 */
    public static function thankyouMessage($atts, $content) {
        $added = array();
        $cred_data = self::$handler->getCredData();
        $message = '';

        $model = CREDC_Loader::get('MODEL/Main');
        foreach ($cred_data as $ii => $data) {
            if (isset($data['cred_form_id'])) {
                $form = $model->getForm($data['cred_form_id'], false);
                if (isset($form->commerce['messages']['thankyou']) &&
                        !in_array($form->commerce['messages']['thankyou'], $added)) {
                    $added[] = $form->commerce['messages']['thankyou'];
                    // allow WPML string localization
                    $message.=do_shortcode(cred_translate(
                                    'cred_commerce_thankyou_message', $form->commerce['messages']['thankyou'], 'cred-form-' . $form->ID
                    ));
                    $message.=" ";
                }
            }
        }
        //return '<pre>'.print_r($cred_data, true).'</pre>';
        return $message;
    }

	/**
	 * @param array $atts
	 * @param string $content
	 *
	 * @return string
	 */
    public static function checkoutMessage($atts, $content) {
        $added = array();
        $cred_data = self::$handler->getCredData();
        $message = '';

        $model = CREDC_Loader::get('MODEL/Main');
        //echo "<pre>";print_r($cred_data);echo "</pre>";
        foreach ($cred_data as $ii => $data) {
            if (isset($data['cred_form_id'])) {
                $form = $model->getForm($data['cred_form_id'], false);
                if (isset($form->commerce['messages']['checkout']) && !in_array($form->commerce['messages']['checkout'], $added)) {
                    $added[] = $form->commerce['messages']['checkout'];
                    // allow WPML string localization
                    $message.=do_shortcode(cred_translate(
                                    'cred_commerce_checkout_message', $form->commerce['messages']['checkout'], 'cred-form-' . $form->ID
                    ));
                    $message.=" ";
                }
            }
        }
        //return '<pre>'.print_r($cred_data, true).'</pre>';
        return $message;
    }

	/**
	 * @param array $columns
	 *
	 * @return array
	 */
    public static function addCommerceExtraColumns($columns) {
        $columns['cred_commerce'] = __('E-Commerce', 'wp-cred-pay');
        return $columns;
    }

	/**
	 * @param array $column_name
	 * @param int $post_ID
	 */
    public static function renderCommerceExtraColumns($column_name, $post_ID) {
        if ('cred_commerce' == $column_name) {
            $data = CREDC_Loader::get('MODEL/Main')->getForm($post_ID, false);
            if (isset($data->commerce) && $data->isCommerce == 1) {
                $data = $data->commerce;
                if (isset($data['associate_product']) && 'form' == $data['associate_product'] && isset($data['product'])) {
                    $product = (self::$handler) ? self::$handler->getProduct($data['product']) : false;
                    if ($product) {
                        printf(__('Product: %s', 'wp-cred-pay'), $product->title);
                    } else {
                        echo '<strong>' . __('Not Set', 'wp-cred-pay') . '</strong>';
                    }
                } elseif (isset($data['associate_product']) && 'post' == $data['associate_product'] && isset($data['product_field'])) {
                    printf(__('Product Field: %s', 'wp-cred-pay'), $data['product_field']);
                } else {
                    echo '<strong>' . __('Not Set', 'wp-cred-pay') . '</strong>';
                }
            } else {
                echo '<strong>' . __('Not Set', 'wp-cred-pay') . '</strong>';
            }
        }
    }

	/**
	 * @param array $custom_data
	 *
	 * @return object
	 */
    public static function getAdminPage($custom_data = array()) {
        global $pagenow, $post, $post_type;
        static $pageData = null;
        static $_custom_data = null;

        if (null == $pageData
            || (!empty($custom_data)
                && $_custom_data != $custom_data)
        ) {
            $_custom_data != $custom_data;

            $pageData = (object) array(
                        'isAdmin' => false,
                        'isAdminAjax' => false,
                        'isPostEdit' => false,
                        'isPostNew' => false,
                        'isCustomPostEdit' => false,
                        'isCustomPostNew' => false,
                        'isCustomAdminPage' => false
            );

	        if ( ! is_admin() ) {
		        return $pageData;
	        }

            $pageData->isAdmin = true;
            $pageData->isPostEdit = (bool) ('post.php' === $pagenow);
            $pageData->isPostNew = (bool) ('post-new.php' === $pagenow);
            if (!empty($custom_data)) {
                $custom_post_type = isset($custom_data['post_type']) ? $custom_data['post_type'] : false;
                $pageData->isCustomPostEdit = (bool) ($pageData->isPostEdit && $custom_post_type === $post_type);
                $pageData->isCustomPostNew = (bool) ($pageData->isPostNew && isset($_GET['post_type']) && $custom_post_type === $_GET['post_type']);
            }
            if (!empty($custom_data)) {
                $custom_admin_base = isset($custom_data['base']) ? $custom_data['base'] : false;
                $custom_admin_pages = isset($custom_data['pages']) ? (array) $custom_data['pages'] : array();
                $pageData->isCustomAdminPage = (bool) ($custom_admin_base === $pagenow && isset($_GET['page']) && in_array($_GET['page'], $custom_admin_pages));
            }
        }

        return $pageData;
    }

	/**
	 * Enqueuer scripts on Admin
	 */
    public static function onAdminEnqueueScripts() {
        // setup css js
        global $pagenow;

        $pageData = self::getAdminPage(array(
                    'post_type' => defined('CRED_FORMS_CUSTOM_POST_NAME') ? CRED_FORMS_CUSTOM_POST_NAME : false,
                    'base' => 'admin.php',
                    'pages' => array('CRED_Commerce')
        ));

	    if ( $pageData->isCustomPostEdit
		    || $pageData->isCustomPostNew
		    || $pageData->isCustomAdminPage
	    ) {
		    if ( $pageData->isCustomAdminPage ) {
			    if ( defined( 'CRED_ASSETS_URL' ) ) {
				    wp_enqueue_style( 'cred_cred_style', CRED_ASSETS_URL . '/css/cred.css', null, CRED_FE_VERSION );
			    }
		    }
		    wp_register_style( 'font-awesome', CRED_COMMERCE_ASSETS_URL . '/css/font-awesome.min.css', null, CRED_COMMERCE_VERSION );
		    wp_register_style( 'cred_commerce_style', CRED_COMMERCE_ASSETS_URL . '/css/cred-commerce.css', null, CRED_COMMERCE_VERSION );
		    wp_enqueue_style( 'font-awesome' );
		    wp_enqueue_style( 'cred_commerce_style' );
	    }
    }

	/**
     * Setup menus in admin, before Toolset Forms 2.0
     *
	 * @param int $cred_menu_index
	 */
    public static function addCREDMenuItems($cred_menu_index) {
        add_submenu_page($cred_menu_index, __('Toolset Forms Commerce', 'wp-cred-pay'), __('Toolset Forms Commerce', 'wp-cred-pay'), CRED_CAPABILITY, 'CRED_Commerce', array(__CLASS__, 'CommerceSettingsPage'));
    }

	/**
     * Setup menus in admin, after Toolset Forms 2.0
     *
	 * @param array $pages
	 *
	 * @return array
	 */
    public static function toolset_register_menu_pages($pages) {
	    if (
		    isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'CRED_Commerce'
	    ) {
		    $pages[] = array(
			    'slug' => 'CRED_Commerce',
			    'menu_title' => __( 'Toolset Forms Commerce', 'wp-cred-pay' ),
			    'page_title' => __( 'Toolset Forms Commerce', 'wp-cred-pay' ),
			    'callback' => array( 'CRED_Commerce', 'CommerceSettingsPage' ),
		    );
	    }
        return $pages;
    }

	/**
     * Setup settings menu link on plugins page
     *
	 * @param array $links
	 *
	 * @return array
	 */
    public static function addSettingsLink($links) {
	    if (
		    ( defined( 'CRED_CAPABILITY' ) && current_user_can( CRED_CAPABILITY ) ) ||
		    current_user_can( CRED_COMMERCE_CAPABILITY )
	    ) {
		    $settings_link = '<a href="' . admin_url( 'admin.php' ) . '?page=CRED_Commerce' . '">' . __( 'Settings', 'wp-cred-pay' ) . '</a>';
		    array_unshift( $links, $settings_link );
	    }
        return $links;
    }

    public static function CommerceSettingsPage() {
        CREDC_Loader::load('VIEW/settings');
    }
}
