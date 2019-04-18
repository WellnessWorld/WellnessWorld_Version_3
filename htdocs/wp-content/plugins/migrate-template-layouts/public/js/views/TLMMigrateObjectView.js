/**
 * Class TLMMigrateObjectView displays a migration object in a <tr> element
 */
TLM_Manager.views.TLMMigrateObjectView = Backbone.View.extend({
    tagName:'tr',
    initialize:function(){
        var self = this;
        self.$el.addClass('edit format-standard entry');
    },
    render: function( options ){
        var self = this,
        template = _.template( jQuery('#tlm-js-row-template').html(), TLM_Manager._templateSettings );

        self.$el.html( template( self.model.toJSON() ) );

        return self;
    }
});