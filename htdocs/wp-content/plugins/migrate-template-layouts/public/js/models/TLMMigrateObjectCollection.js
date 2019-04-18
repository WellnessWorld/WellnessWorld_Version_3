/**
 * Class TLMMigrateObjectCollection represents a Collection of migration candidates objects
 *
 */
TLM_Manager.models.TLMMigrateObjectCollection = Backbone.Collection.extend({
    model:TLM_Manager.models.TLMMigrateObject,
    url:ajaxurl,
    DEFAULT_BATCH_SIZE : TLM_ManagerSettings.DEFAULT_BATCH_SIZE,
    PAGE : 1,
    initialize:function(  ){
        var self = this;
        self.get_data_from_server( {} );
    },
    get_data_from_server:function( params ){
        var self = this,
            defaults =  {
                action: 'tlm_populate_table',
                tlm_populate_nonce: TLM_ManagerSettings.tlm_populate_nonce,
                paged: self.PAGE
            }, send;

        self.trigger('tlm-get_data_from_server');

        send = _.extend( {}, defaults, params );

        self.fetch({
            contentType:'application/x-www-form-urlencoded; charset=UTF-8',
            data: jQuery.param(send),
            type: 'POST',
            success: function ( model, response, object ) {

                if( model.models.length < self.DEFAULT_BATCH_SIZE ){
                    self.PAGE = 0;
                } else {
                    self.PAGE++;
                    self.get_data_from_server( {} );
                }

                self.trigger( 'tlm-collection-loaded-data', model );
            },
            error: function () {
                console.error(arguments);
            }
        });
    },
    parse:function( data, attrs )
    {
        return data.data;
    }
});