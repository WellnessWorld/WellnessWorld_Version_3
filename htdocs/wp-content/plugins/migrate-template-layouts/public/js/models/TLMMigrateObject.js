/**
 * Class TLMMigrateObject
 * represents an object candidate for migration
 */
TLM_Manager.models.TLMMigrateObject = Backbone.Model.extend({
    post_id:0,
    post_slug: '',
    post_title: '',
    post_type: 'post',
    current_layout_id: 0,
    current_layout_title: '',
    eligible_templates : [],
    permalink:''
});