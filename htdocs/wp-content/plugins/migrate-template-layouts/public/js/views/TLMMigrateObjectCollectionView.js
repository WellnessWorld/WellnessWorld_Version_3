/**
 * Class TLMMigrateObjectCollectionView displays a Collection of migration objects in a table body
 */
TLM_Manager.views.TLMMigrateObjectCollectionView = Backbone.View.extend({
    el: 'tbody#tlm-items-list-table-body',
    fragment:null,
    options: null,
    initialize:function(options)
    {
        var self = this;

        self.options = options;

        self.$el.data( 'view', self );

        self.render( options );
    },
    render: function (options) {
        var self = this,
            option = _.extend({}, options);

        self.fragment = document.createDocumentFragment();

        self.appendModelElement( option );

        self.$el.append( self.fragment );

        option.admin.reset_events();

        return self;
    }
    ,appendModelElement:function( opt )
    {

        var self = this, view, el, options;

        self.model.each(function(model){

            try{

                options = {
                    model:model
                };

                view = new TLM_Manager.views.TLMMigrateObjectView( options );

                el = view.render( options ).el;

                self.fragment.appendChild( el );

            }
            catch( e )
            {
                console.error( e.message );
            }
        }, self);

        return this;
    }
});