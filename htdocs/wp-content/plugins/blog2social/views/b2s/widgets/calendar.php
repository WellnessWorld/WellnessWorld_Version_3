<h5 class="b2s-dashboard-h5 pull-left"><?php _e('Calendar', 'blog2social') ?> 
</h5>
<div class="clearfix"></div>
<div class="b2s-widget-calendar"></div>
<script>
    var b2s_calendar_locale = '<?= strtolower(substr(get_locale(), 0, 2)); ?>';
    var b2s_plugin_url = '<?= B2S_PLUGIN_URL; ?>';
</script>