var TLM_Manager = TLM_Manager || {};

TLM_Manager.models = {};
TLM_Manager.views = {};
TLM_Manager.ns = head;

TLM_Manager._templateSettings = TLM_Manager._templateSettings || {
        escape: /\{\{([^\}]+?)\}\}(?!\})/g,
        evaluate: /<#([\s\S]+?)#>/g,
        interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
        variable: 'item'
    };


TLM_Manager.ns.js(
    TLM_ManagerSettings.TLM_PUBLIC_URI + '/js/models/TLMMigrateObject.js',
    TLM_ManagerSettings.TLM_PUBLIC_URI + '/js/models/TLMMigrateObjectCollection.js',
    TLM_ManagerSettings.TLM_PUBLIC_URI + '/js/views/TLMMigrateObjectView.js',
    TLM_ManagerSettings.TLM_PUBLIC_URI + '/js/views/TLMMigrateObjectCollectionView.js'
);

TLM_Manager.Admin = function ($) {
    var self = this,
        $checkboxes = $('input[name="posts[]"]'),
        $button = $('.tlm-convert-layouts'),
        $select = $('.tlm-template-selector'),
        $column = $('.tlm-post-title'),
        $select_all = $('.cb-select-all');

    self.init = function () {
        self.events_on();
        self.handle_ajax_data_load();
    };

    self.handle_ajax_data_load = function () {
        var collection = new TLM_Manager.models.TLMMigrateObjectCollection();

        collection.listenTo(collection, 'tlm-collection-loaded-data', function (model) {
            var collection_view = new TLM_Manager.views.TLMMigrateObjectCollectionView({model: model, admin: self});
        });

    };

    self.handle_select_change_event = function (event) {
        var $checkbox = $(this).closest('tr').find('input[name="posts[]"]');

        if ($checkbox.is(':checked') === false) {
            return;
        }

        if (+$(this).val() === 0) {
            $button.prop('disabled', true);
        } else {
            self.button_react_to_all();
        }
    };

    self.events_on = function () {
        $select_all.on('change', self.all_change);
        $checkboxes.on('change', self.one_change);
        $select.on('change', self.handle_select_change_event);
        $column.on('mouseover', self.handle_edit_view_show);
        $column.on('mouseout', self.handle_edit_view_hide);
    };

    self.events_off = function () {
        $select_all.off('change', self.all_change);
        $checkboxes.off('change', self.one_change);
        $select.off('change', self.handle_select_change_event);
        $column.off('mouseover', self.handle_edit_view_show);
        $column.off('mouseout', self.handle_edit_view_hide);
    };

    self.reset_events = function () {
        $checkboxes = $('input[name="posts[]"]'),
            $button = $('.tlm-convert-layouts'),
            $select = $('.tlm-template-selector'),
            $column = $('.tlm-post-title'),
            $select_all = $('.cb-select-all');
        self.events_off();
        self.events_on();
    };

    self.handle_edit_view_show = function (event) {
        var $td = $(this),
            $div = $td.find('.tlm-row-actions');

        $div.show();
    };

    self.handle_edit_view_hide = function (event) {
        var $td = $(this),
            $div = $td.find('.tlm-row-actions');

        $div.hide();
    };

    self.all_change = function (event) {
        if ($(this).is(':checked')) {
            $checkboxes.map(function (index, item) {
                $(item).prop('checked', true).trigger('change');
            });
            $select_all.not(this).prop('checked', true);
            self.button_react_to_all();
        } else {
            $checkboxes.map(function (index, item) {
                $(item).prop('checked', false).trigger('change');
            });
            $select_all.not(this).prop('checked', false);
            $button.prop('disabled', true);
        }
    };

    self.one_change = function (event) {

        var checked_length = $('input[name="posts[]"]:checked').length;

        if ($(this).is(':checked')) {
            if ($checkboxes.length === checked_length) {
                $select_all.prop('checked', true);
            }
        } else {
            $select_all.prop('checked', false);
        }

        if (checked_length) {
            self.handle_select_on_checkbox_change($(this), $(this).is(':checked'));
        } else {
            $button.prop('disabled', true);
        }

    };

    self.get_select = function ($me) {
        return $me.closest('tr').find('select');
    };

    self.handle_select_on_checkbox_change = function ($me, selected) {
        var $select = self.get_select($me),
            value = $select.val();

        if (selected) {
            if (+value) {
                $button.prop('disabled', false);
            } else {
                $button.prop('disabled', true);
            }
        } else {
            self.button_react_to_all();
        }

    };

    self.button_react_to_all = function () {
        var all = self.all_selected();

        if (all) {
            $button.prop('disabled', false);
        } else {
            $button.prop('disabled', true);
        }
    };

    self.all_selected = function () {
        var bool = true,
            $checked = $('input[name="posts[]"]:checked'),
            len = $checked.length;

        for (var i = 0; i < len; i++) {
            var $me = $($checked[i]),
                $select = self.get_select($me),
                value = $select.val();

            if (+value === 0) {
                bool = false;
                break;
            } else {
                bool = true;
            }
        }

        return bool;
    };

    self.init();
};

TLM_Manager.CreateLayoutForPostType = function ($) {
    var self = this,
        $this_notice = $('.tlm-no-template-notice'),
        $current_target = null,
        post_type = null,
        $link = $('.create-layout-for-post-type'),
        defaults = {
            action: 'ddl_create_layout',
            wpnonce: TLM_ManagerSettings.create_layout_nonce,
            single_data: {
                who: 'all',
                for_whom: 'new',
                post_type: '',
                post_type_label: ''
            }
        };

    self.init = function () {
        self.events_on();
    };

    self.events_on = function () {
        $link.on('click', self.handle_click_creation_link);
    };

    self.handle_click_creation_link = function (event) {
        event.preventDefault();

        $current_target = $(this);
        post_type = $(this).data('post_type');

        defaults.single_data.post_type = post_type;
        defaults.single_data.post_type_label = $(this).data('post_type_label');

        $.post(ajaxurl, defaults, self.handle_success, 'json')
            .fail(self.handle_fail)
            .always(self.always_handler);
    };

    self.handle_success = function (response, params) {
        if (response && response.hasOwnProperty('id')) {
            var redirect = TLM_ManagerSettings.edit_link.replace('%LAYOUT_ID%', response.id);

            self.handle_hide_elements_and_remove();
            self.append_new_options_and_select(response);

            self.redirect(redirect, response.id);
        } else if (response && response.hasOwnProperty('error')) {
            self.handle_error(response);
        }
    };

    self.redirect = function (url, id) {
        var w = window.open(url, 'layout_' + id);
        w.focus();
        w.location.href = url;
        return false;
    };

    self.append_new_options_and_select = function (data) {
        var $post_type = $('input[value="' + post_type + '"]');

        $post_type.each(function () {
            var $tr = $(this).closest('tr'),
                $new_option = $('<option selected value="' + data.id + '" class="tlm-template-option" >' + data.name + '</option>'),
                $select = $tr.find('select');

            $select.append($new_option);
            $($new_option).prop('selected', true);
            $select.trigger('change');
        });

    };

    self.always_handler = function () {
        post_type = null;
        $current_target = null;
    };

    self.handle_hide_elements_and_remove = function () {

        $current_target.hide(400, function (event) {
            $(this).remove();

            if ($('.create-layout-for-post-type').length === 0) {
                $this_notice.fadeOut(400, function (event) {
                    $(this).remove();
                });
            }
        });
    };

    self.handle_error = function (response, params) {
        console.log('Something went wrong', response.error);
    };

    self.handle_fail = function (response, params) {
        console.table(response);
    };

    self.init();
};

TLM_Manager.MigratedItemsHandler = function ($) {
    var self = this,
        $cancel = $('.js-tlm-cancel-migration'),
        $button = $('.js-tlm-finalize-migration');

    self.handle = {};
    self.action = {};

    self.init = function () {
        self.events_on();
    };

    self.events_on = function () {
        $button.on('click', self.button_click_handler);
        $cancel.on('click', self.cancel_click_handler);
    };

    self.button_click_handler = function (event) {
        var id = $(this).prop('id').split('button_')[1];

        self.handle[id] = $(this) ;
        self.action[id] = 'finalise_migration_process';
        $(this).parent().css('position', 'relative');
        TLM_Manager.loader.loadShow($(this), false).css({
            position: 'absolute',
            right: '200px'
        });

        if (id) {
            self.handle_ajax_request(id);
        }

    };

    self.cancel_click_handler = function (event) {
        var id = $(this).prop('id').split('button_')[1];

        self.handle[id] = $(this) ;
        self.action[id] = 'cancel_migration_process';
        $(this).parent().css('position', 'relative');
        TLM_Manager.loader.loadShow($(this), false).css({
            position: 'absolute',
            right: '200px'
        });

        if (id) {
            self.handle_ajax_request(id);
        }

    };

    self.handle_ajax_request = function (id) {
        var params = {
            'finalise_migration_nonce': TLM_ManagerSettings.finalise_migration_nonce,
            'post_id': id,
            'action': self.action[id]
        };

        $.post(ajaxurl, params, self.handle_success, 'json')
            .fail(self.handle_fail)
            .always(self.always_handler);
    };


    self.always_handler = function (response, xhr) {
        //console.table( arguments );
        TLM_Manager.loader.loadHide();
    };

    self.handle_error = function (response, params) {
        TLM_Manager.open_info_dialog({
            callback: function () {
                console.table(arguments);
            }
        }, response, '#tlm-error-dialog');
    };

    self.handle_fail = function (jqXHR, textStatus, errorThrown) {
        TLM_Manager.open_info_dialog({
            callback: function () {
                console.table(arguments);
            }
        }, {error: textStatus}, '#tlm-error-dialog');
    };

    self.handle_success = function (response, params) {
        if (response && response.data) {
            if (self.action[response.id] == 'cancel_migration_process') {
                self.handle[response.id].closest('tr').fadeOut(400, function () {
                    self.handle[response.id] = null;
                    self.action[response.id] = '';
                    self.handle_remove_in_case( jQuery(this)  );
                    jQuery(this).remove();
                });
            } else {
                TLM_Manager.open_info_dialog({
                    callback: function () {
                        self.handle[response.id].closest('tr').fadeOut(400, function () {
                            self.handle_disable_in_case( jQuery(this) );
                            self.handle[response.id] = null;
                            self.action[response.id] = '';
                        });
                    }
                }, response.data, '#tlm-finish-dialog');
            }
        }
    };

    self.handle_disable_in_case = function( $tr ){
        var attr = $tr.data('translate');

        if( attr ){
            var $trs = jQuery('table.js-table-migration-items').find( "tr[data-translate='" + attr +"']" );
            $trs.each(function(){
                var $button = jQuery(this).find( 'input.js-tlm-cancel-migration' );
                $button.each(function(){
                    jQuery(this).prop('disabled', true);
                });
            });
        }
    };

    self.handle_remove_in_case = function( $tr ){
        var attr = $tr.data('translate');

        if( attr ){
            var $trs = jQuery('table.js-table-migration-items').find( "tr[data-translate='" + attr +"']" );
            $trs.each(function(){
                jQuery(this).fadeOut(400,function(){
                    jQuery(this).remove();
                });
            });
        }
    };

    self.init();
};

TLM_Manager.open_info_dialog = function (options, model, selector) {

    model = _.defaults( model, { is_wpml_active : TLM_ManagerSettings.is_wpml_active_and_configured } );

    var dialog = new DDLayout.DialogView({
        title: TLM_ManagerSettings.strings.summary,
        modal: true,
        resizable: false,
        draggable: false,
        position: {my: "center", at: "center", of: window},
        width: options.width ? options.width : 250,
        selector: selector,
        template_object: model,
        dialogClass: 'ddl-dialogs-container wp-core-ui',
        buttons: [
            {
                text: "Close",
                icons: {},
                class: 'close button',
                click: function () {

                    dialog.dialog_close();

                }
            }
        ],
    });


    dialog.$el.on('ddldialogclose', function (event) {

        dialog.remove();

        if (options.callback instanceof Function) {
            options.callback.call(self, event, options, model, self);
        }

    });

    dialog.dialog_open();
};

(function () {
    TLM_Manager.ns.ready(function () {
        TLM_Manager.loader = TLM_Manager.loader || new WPV_Toolset.Utils.Loader;
        TLM_Manager.admin = new TLM_Manager.Admin(jQuery);
        TLM_Manager.notices = new TLM_Manager.CreateLayoutForPostType(jQuery);
        TLM_Manager.items_handler = new TLM_Manager.MigratedItemsHandler(jQuery);
    });
}());