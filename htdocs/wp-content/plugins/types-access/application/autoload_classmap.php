<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(dirname(__FILE__));
$baseDir = $vendorDir;

return array(
    'OTGS\\Toolset\\Access\\Ajax' => $baseDir . '/application/controllers/ajax.php',
    'OTGS\\Toolset\\Access\\Controllers\\AccessApi' => $baseDir . '/application/controllers/access_api.php',
    'OTGS\\Toolset\\Access\\Controllers\\Actions\\ErrorPreview' => $baseDir . '/application/controllers/actions/error_preview.php',
    'OTGS\\Toolset\\Access\\Controllers\\Actions\\FrontendActions' => $baseDir . '/application/controllers/actions/frontend_actions.php',
    'OTGS\\Toolset\\Access\\Controllers\\Backend' => $baseDir . '/application/controllers/backend.php',
    'OTGS\\Toolset\\Access\\Controllers\\Blocks' => $baseDir . '/application/controllers/Blocks.php',
    'OTGS\\Toolset\\Access\\Controllers\\CommentsPermissions' => $baseDir . '/application/controllers/comments_permissions.php',
    'OTGS\\Toolset\\Access\\Controllers\\CustomErrorSettings' => $baseDir . '/application/controllers/custom_error_settings.php',
    'OTGS\\Toolset\\Access\\Controllers\\CustomErrors' => $baseDir . '/application/controllers/custom_errors.php',
    'OTGS\\Toolset\\Access\\Controllers\\Filters\\Access_Menu_Permissions' => $baseDir . '/application/controllers/filters/menu_permisions.php',
    'OTGS\\Toolset\\Access\\Controllers\\Filters\\BackendFilters' => $baseDir . '/application/controllers/filters/backend_filters.php',
    'OTGS\\Toolset\\Access\\Controllers\\Filters\\CommonFilters' => $baseDir . '/application/controllers/filters/common_filters.php',
    'OTGS\\Toolset\\Access\\Controllers\\Filters\\ErrorPreview' => $baseDir . '/application/controllers/filters/post_preview.php',
    'OTGS\\Toolset\\Access\\Controllers\\Filters\\FeedPermissions' => $baseDir . '/application/controllers/filters/feed_permissions.php',
    'OTGS\\Toolset\\Access\\Controllers\\Filters\\FrontendFilters' => $baseDir . '/application/controllers/filters/frontend_filters.php',
    'OTGS\\Toolset\\Access\\Controllers\\Filters\\NextPrevLinksPermissions' => $baseDir . '/application/controllers/filters/next_prev_links_permissions.php',
    'OTGS\\Toolset\\Access\\Controllers\\Frontend' => $baseDir . '/application/controllers/frontend.php',
    'OTGS\\Toolset\\Access\\Controllers\\Import' => $baseDir . '/application/controllers/import.php',
    'OTGS\\Toolset\\Access\\Controllers\\PermissionsPostTypes' => $baseDir . '/application/controllers/permissions_post_types.php',
    'OTGS\\Toolset\\Access\\Controllers\\PermissionsRead' => $baseDir . '/application/controllers/permissions_read.php',
    'OTGS\\Toolset\\Access\\Controllers\\PermissionsTaxonomies' => $baseDir . '/application/controllers/permissions_taxonomies.php',
    'OTGS\\Toolset\\Access\\Controllers\\PermissionsThirdParty' => $baseDir . '/application/controllers/permissions_third_party.php',
    'OTGS\\Toolset\\Access\\Controllers\\Shortcodes' => $baseDir . '/application/controllers/shortcodes.php',
    'OTGS\\Toolset\\Access\\Controllers\\UploadPermissions' => $baseDir . '/application/controllers/upload_permissions.php',
    'OTGS\\Toolset\\Access\\Main' => $baseDir . '/application/controllers/main.php',
    'OTGS\\Toolset\\Access\\Models\\Capabilities' => $baseDir . '/application/models/capabilities.php',
    'OTGS\\Toolset\\Access\\Models\\ExportImport' => $baseDir . '/application/models/export_import.php',
    'OTGS\\Toolset\\Access\\Models\\Settings' => $baseDir . '/application/models/access_settings.php',
    'OTGS\\Toolset\\Access\\Models\\UserRoles' => $baseDir . '/application/models/user_roles.php',
    'OTGS\\Toolset\\Access\\Models\\WPMLSettings' => $baseDir . '/application/models/wpml_settings.php',
    'OTGS\\Toolset\\Access\\Viewmodels\\PostMetabox' => $baseDir . '/application/viewmodels/post_metabox.php',
    'Access_Ajax_Handler_Add_New_Cap' => $baseDir . '/application/controllers/ajax/handler/add_new_cap.php',
    'Access_Ajax_Handler_Add_New_Group' => $baseDir . '/application/controllers/ajax/handler/add_new_group.php',
    'Access_Ajax_Handler_Add_New_Group_Process' => $baseDir . '/application/controllers/ajax/handler/add_new_group_process.php',
    'Access_Ajax_Handler_Add_Role' => $baseDir . '/application/controllers/ajax/handler/add_role.php',
    'Access_Ajax_Handler_Add_Wpml_Group_Form' => $baseDir . '/application/controllers/ajax/handler/add_wpml_group_form.php',
    'Access_Ajax_Handler_Add_Wpml_Group_Process' => $baseDir . '/application/controllers/ajax/handler/add_wpml_group_process.php',
    'Access_Ajax_Handler_Change_Role_Caps_Form' => $baseDir . '/application/controllers/ajax/handler/change_role_caps_form.php',
    'Access_Ajax_Handler_Change_Role_Caps_Process' => $baseDir . '/application/controllers/ajax/handler/change_role_caps_process.php',
    'Access_Ajax_Handler_Clean_Up_Database' => $baseDir . '/application/controllers/ajax/handler/clean_up_database.php',
    'Access_Ajax_Handler_Clone_Role' => $baseDir . '/application/controllers/ajax/handler/clone_role.php',
    'Access_Ajax_Handler_Delete_Role' => $baseDir . '/application/controllers/ajax/handler/delete_role.php',
    'Access_Ajax_Handler_Delete_Role_Form' => $baseDir . '/application/controllers/ajax/handler/delete_role_form.php',
    'Access_Ajax_Handler_Enable_Advanced_Mode' => $baseDir . '/application/controllers/ajax/handler/enable_advanced_mode.php',
    'Access_Ajax_Handler_Hide_Max_Fields_Message' => $baseDir . '/application/controllers/ajax/handler/hide_max_fields_message.php',
    'Access_Ajax_Handler_Import_Export' => $baseDir . '/application/controllers/ajax/handler/import_export.php',
    'Access_Ajax_Handler_Load_Permissions_Table' => $baseDir . '/application/controllers/ajax/handler/load_permissions_table.php',
    'Access_Ajax_Handler_Modify_Group_Process' => $baseDir . '/application/controllers/ajax/handler/modify_group_process.php',
    'Access_Ajax_Handler_Remove_Assignment_Post_Group' => $baseDir . '/application/controllers/ajax/handler/remove_assignment_post_group.php',
    'Access_Ajax_Handler_Remove_Custom_Cap' => $baseDir . '/application/controllers/ajax/handler/remove_custom_cap.php',
    'Access_Ajax_Handler_Remove_Post_Group_Form' => $baseDir . '/application/controllers/ajax/handler/remove_post_group_form.php',
    'Access_Ajax_Handler_Remove_Post_Group_Process' => $baseDir . '/application/controllers/ajax/handler/remove_post_group_process.php',
    'Access_Ajax_Handler_Save_Section_Status' => $baseDir . '/application/controllers/ajax/handler/save_section_status.php',
    'Access_Ajax_Handler_Save_Settings' => $baseDir . '/application/controllers/ajax/handler/save_settings.php',
    'Access_Ajax_Handler_Search_Posts' => $baseDir . '/application/controllers/ajax/handler/search_posts.php',
    'Access_Ajax_Handler_Select_Post_Group_For_Post_Form' => $baseDir . '/application/controllers/ajax/handler/select_post_group_for_post_form.php',
    'Access_Ajax_Handler_Select_Post_Group_For_Post_Process' => $baseDir . '/application/controllers/ajax/handler/select_post_group_for_post_process.php',
    'Access_Ajax_Handler_Show_Error_List' => $baseDir . '/application/controllers/ajax/handler/show_error_list.php',
    'Access_Ajax_Handler_Show_Role_Caps' => $baseDir . '/application/controllers/ajax/handler/show_role_caps.php',
    'Access_Ajax_Handler_Specific_Users_Form' => $baseDir . '/application/controllers/ajax/handler/specific_users_form.php',
    'Access_Ajax_Handler_Specific_Users_Process' => $baseDir . '/application/controllers/ajax/handler/specific_users_process.php',
    'Access_Ajax_Handler_Suggest_Users' => $baseDir . '/application/controllers/ajax/handler/suggest_users.php',
);
