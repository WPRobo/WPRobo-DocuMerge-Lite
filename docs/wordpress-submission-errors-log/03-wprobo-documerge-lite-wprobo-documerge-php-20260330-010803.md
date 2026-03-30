# Plugin Check Report

**Plugin:** WPRobo DocuMerge Lite
**Generated at:** 2026-03-30 01:08:03


## `src/Form/WPRobo_DocuMerge_Form_Submission.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 175 | 6 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$submissions_table} at &quot;SELECT COUNT(*) FROM {$submissions_table} WHERE form_id = %d AND status != &#039;error&#039;&quot; |  |
| 195 | 7 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$submissions_table} at &quot;SELECT COUNT(*) FROM {$submissions_table} WHERE form_id = %d AND submitter_email = %s AND status != &#039;error&#039;&quot; |  |
| 217 | 7 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$submissions_table} at &quot;SELECT COUNT(*) FROM {$submissions_table} WHERE form_id = %d AND ip_address = %s AND status != &#039;error&#039;&quot; |  |
| 240 | 6 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$submissions_table} at &quot;SELECT COUNT(*) FROM {$submissions_table} WHERE form_id = %d AND submitter_email = %s AND status != &#039;error&#039;&quot; |  |
| 597 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $submissions_table used in $wpdb-&gt;get_row()\n$submissions_table assigned unsafely at line 594. |  |
| 599 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$submissions_table} at &quot;SELECT * FROM {$submissions_table} WHERE id = %d&quot; |  |

## `src/Template/WPRobo_DocuMerge_Template_Manager.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 65 | 21 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_results()\n$table assigned unsafely at line 62. |  |
| 67 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at FROM {$table}\n |  |
| 93 | 17 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_row()\n$table assigned unsafely at line 90. |  |
| 95 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT * FROM {$table} WHERE id = %d&quot; |  |
| 312 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_var() |  |
| 312 | 34 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT COUNT(*) FROM {$table}&quot; |  |
| 335 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_var() |  |
| 337 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT COUNT(*) FROM {$table} WHERE template_id = %d&quot; |  |

## `src/Admin/WPRobo_DocuMerge_Templates_Page.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 84 | 38 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $forms_table used in $wpdb-&gt;get_results()\n$forms_table assigned unsafely at line 73. |  |
| 86 | 7 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$forms_table} at &quot;SELECT id, title FROM {$forms_table} WHERE template_id = %d ORDER BY title ASC&quot; |  |

## `src/Admin/WPRobo_DocuMerge_Forms_List_Table.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 339 | 32 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $count_query used in $wpdb-&gt;get_var()\n$count_query assigned unsafely at line 336. |  |
| 342 | 32 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $count_query used in $wpdb-&gt;get_var()\n$count_query assigned unsafely at line 336. |  |
| 349 | 20 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 376 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $select_query used in $wpdb-&gt;get_results()\n$select_query assigned unsafely at line 367. |  |
| 392 | 43 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $submissions_table used in $wpdb-&gt;get_var()\n$submissions_table assigned unsafely at line 383. |  |

## `src/Admin/WPRobo_DocuMerge_Submissions_Page.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 182 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_row()\n$table assigned unsafely at line 173. |  |
| 184 | 6 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT id, doc_path_docx, doc_path_pdf FROM {$table} WHERE id = %d&quot; |  |
| 369 | 26 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $count_query used in $wpdb-&gt;get_var()\n$count_query assigned unsafely at line 365. |  |
| 372 | 26 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $count_query used in $wpdb-&gt;get_var()\n$count_query assigned unsafely at line 365. |  |
| 391 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $select_query used in $wpdb-&gt;get_results()\n$select_query assigned unsafely at line 381. |  |
| 451 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $submissions_table used in $wpdb-&gt;get_row()\n$submissions_table assigned unsafely at line 446. |  |
| 454 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$submissions_table} at FROM {$submissions_table} s\n |  |
| 455 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$forms_table} at LEFT JOIN {$forms_table} f ON s.form_id = f.id\n |  |
| 456 | 1 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$templates_table} at LEFT JOIN {$templates_table} t ON s.template_id = t.id\n |  |
| 522 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $submissions_table used in $wpdb-&gt;get_row() |  |
| 524 | 6 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$submissions_table} at &quot;SELECT id, doc_path_docx, doc_path_pdf FROM {$submissions_table} WHERE id = %d&quot; |  |
| 628 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $select_query used in $wpdb-&gt;get_results()\n$select_query assigned unsafely at line 618. |  |

## `src/Admin/WPRobo_DocuMerge_Submissions_List_Table.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 345 | 32 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $count_query used in $wpdb-&gt;get_var()\n$count_query assigned unsafely at line 338. |  |
| 348 | 32 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $count_query used in $wpdb-&gt;get_var()\n$count_query assigned unsafely at line 338. |  |
| 355 | 20 | WARNING | WordPress.Security.NonceVerification.Recommended | Processing form data without nonce verification. |  |
| 386 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $select_query used in $wpdb-&gt;get_results()\n$select_query assigned unsafely at line 376. |  |

## `src/Admin/WPRobo_DocuMerge_Settings_Page.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 654 | 11 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;query() |  |
| 654 | 18 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;TRUNCATE TABLE {$table}&quot; |  |
| 667 | 38 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_col() |  |
| 667 | 47 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SHOW COLUMNS FROM {$table}&quot; |  |
| 685 | 22 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_var() |  |
| 687 | 7 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT COUNT(*) FROM {$table} WHERE id = %d&quot; |  |

## `src/Admin/WPRobo_DocuMerge_Dashboard_Page.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 192 | 26 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_results() |  |
| 194 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT DATE(created_at) as day, COUNT(*) as count FROM {$table} WHERE created_at &gt;= %s GROUP BY DATE(created_at)&quot; |  |
| 218 | 22 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $table used in $wpdb-&gt;get_results() |  |
| 219 | 4 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$table} at &quot;SELECT status, COUNT(*) as count FROM {$table} GROUP BY status&quot; |  |

## `src/Document/WPRobo_DocuMerge_Document_Generator.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 103 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $submissions_table used in $wpdb-&gt;get_row()\n$submissions_table assigned unsafely at line 100. |  |
| 105 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$submissions_table} at &quot;SELECT * FROM {$submissions_table} WHERE id = %d&quot; |  |
| 122 | 18 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $forms_table used in $wpdb-&gt;get_row()\n$forms_table assigned unsafely at line 119. |  |
| 124 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$forms_table} at &quot;SELECT * FROM {$forms_table} WHERE id = %d&quot; |  |
| 144 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$templates_table} at &quot;SELECT * FROM {$templates_table} WHERE id = %d&quot; |  |

## `src/Document/WPRobo_DocuMerge_Pdf_Converter.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 78 | 3 | WARNING | Squiz.PHP.DiscouragedFunctions.Discouraged | The use of function ini_set() is discouraged |  |
| 80 | 3 | WARNING | Squiz.PHP.DiscouragedFunctions.Discouraged | The use of function set_time_limit() is discouraged |  |
| 232 | 4 | WARNING | Squiz.PHP.DiscouragedFunctions.Discouraged | The use of function ini_set() is discouraged |  |
| 234 | 4 | WARNING | Squiz.PHP.DiscouragedFunctions.Discouraged | The use of function set_time_limit() is discouraged |  |

## `src/Document/WPRobo_DocuMerge_Delivery_Engine.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 62 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $submissions_table used in $wpdb-&gt;get_row()\n$submissions_table assigned unsafely at line 59. |  |
| 64 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$submissions_table} at &quot;SELECT * FROM {$submissions_table} WHERE id = %d&quot; |  |
| 81 | 18 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $forms_table used in $wpdb-&gt;get_row()\n$forms_table assigned unsafely at line 78. |  |
| 83 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$forms_table} at &quot;SELECT * FROM {$forms_table} WHERE id = %d&quot; |  |
| 403 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $submissions_table used in $wpdb-&gt;get_row()\n$submissions_table assigned unsafely at line 400. |  |
| 405 | 5 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$submissions_table} at &quot;SELECT * FROM {$submissions_table} WHERE id = %d&quot; |  |
| 561 | 24 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $submissions_table used in $wpdb-&gt;get_row()\n$submissions_table assigned unsafely at line 558. |  |
| 562 | 20 | WARNING | WordPress.DB.PreparedSQL.InterpolatedNotPrepared | Use placeholders and $wpdb-&gt;prepare(); found interpolated variable {$submissions_table} at &quot;SELECT * FROM {$submissions_table} WHERE id = %d&quot; |  |

## `src/Form/WPRobo_DocuMerge_Form_Renderer.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 166 | 39 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 166 | 39 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |

## `src/Form/WPRobo_DocuMerge_Form_Builder.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 74 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 74 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 109 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 109 | 19 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 163 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 163 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 183 | 25 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 235 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 235 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 270 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 270 | 20 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 335 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 335 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 356 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.DirectQuery | Use of a direct database call is discouraged. |  |
| 356 | 24 | WARNING | WordPress.DB.DirectDatabaseQuery.NoCaching | Direct database call without caching detected. Consider using wp_cache_get() / wp_cache_set() or wp_cache_delete(). |  |
| 356 | 25 | WARNING | PluginCheck.Security.DirectDB.UnescapedDBParameter | Unescaped parameter $this-&gt;submissions_table_name used in $wpdb-&gt;get_var() |  |

## `readme.txt`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | WARNING | trademarked_term | The plugin name includes a restricted term. Your chosen plugin name - "WPRobo DocuMerge Lite" - contains the restricted term "wp" which cannot be used at all in your plugin name. |  |

## `wprobo-documerge.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 0 | 0 | WARNING | trademarked_term | The plugin name includes a restricted term. Your chosen plugin name - "WPRobo DocuMerge Lite" - contains the restricted term "wp" which cannot be used at all in your plugin name. |  |
| 0 | 0 | WARNING | trademarked_term | The plugin slug includes a restricted term. Your plugin slug - "wprobo-documerge-lite" - contains the restricted term "wp" which cannot be used at all in your plugin slug. |  |
| 84 | 1 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$wprobo_active_plugins&quot;. |  |

## `uninstall.php`

| Line | Column | Type | Code | Message | Docs |
| --- | --- | --- | --- | --- | --- |
| 27 | 1 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$options&quot;. |  |
| 31 | 23 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$option&quot;. |  |
| 36 | 1 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$upload_dir&quot;. |  |
| 37 | 1 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$docs_dir&quot;. |  |
| 38 | 1 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$temp_dir&quot;. |  |
| 39 | 1 | WARNING | WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound | Global variables defined by a theme/plugin should start with the theme/plugin prefix. Found: &quot;$logs_dir&quot;. |  |
