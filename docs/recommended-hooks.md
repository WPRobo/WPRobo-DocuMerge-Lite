# Recommended Hooks — WPRobo DocuMerge Lite

> Developer hooks for extending DocuMerge Lite functionality.
> All hooks use the `wprobo_documerge_` prefix.

---

## Existing Hooks (38 total)

### Actions (16)

| Hook | Parameters | File | Purpose |
|------|-----------|------|---------|
| `wprobo_documerge_before_submission` | `$form_id, $post_data` | Form_Submission.php | Fires before a form submission is processed |
| `wprobo_documerge_submission_created` | `$submission_id, $form_id` | Form_Submission.php | Fires after a submission record is inserted into the DB |
| `wprobo_documerge_submission_updated` | `$submission_id, $data` | Form_Submission.php | Fires after a submission record is updated |
| `wprobo_documerge_submission_deleted` | `$submission_id` | Form_Submission.php | Fires after a submission is deleted |
| `wprobo_documerge_before_generate` | `$submission_id, $form_id` | Document_Generator.php | Fires before document generation starts |
| `wprobo_documerge_document_generated` | `$submission_id, $docx_path, $pdf_path` | Document_Generator.php | Fires after a document is successfully generated |
| `wprobo_documerge_generation_failed` | `$submission_id, $error` | Document_Generator.php | Fires when document generation fails |
| `wprobo_documerge_before_delivery` | `$submission_id, $method` | Delivery_Engine.php | Fires before a document is delivered |
| `wprobo_documerge_document_delivered` | `$submission_id, $method, $path` | Delivery_Engine.php | Fires after successful document delivery |
| `wprobo_documerge_delivery_failed` | `$submission_id, $method, $error` | Delivery_Engine.php | Fires when delivery fails |
| `wprobo_documerge_template_scanned` | `$template_id, $tags` | Template_Scanner.php | Fires after merge tags are scanned from a DOCX |
| `wprobo_documerge_before_template_save` | `$data` | Template_Manager.php | Fires before a template record is saved |
| `wprobo_documerge_template_saved` | `$template_id` | Template_Manager.php | Fires after a template is saved |
| `wprobo_documerge_template_deleted` | `$template_id` | Template_Manager.php | Fires after a template is deleted |
| `wprobo_documerge_form_saved` | `$form_id, $data` | Form_Builder.php | Fires after a form is saved |
| `wprobo_documerge_form_deleted` | `$form_id` | Form_Builder.php | Fires after a form is deleted |

### Filters (22)

| Hook | Parameters | File | Purpose |
|------|-----------|------|---------|
| `wprobo_documerge_merge_tags` | `$tags, $file_path` | Template_Scanner.php | Modify detected merge tags |
| `wprobo_documerge_merge_data` | `$data, $submission_id, $form_id` | Document_Generator.php | Modify merge data before document generation |
| `wprobo_documerge_output_format` | `$format, $form_id` | Document_Generator.php | Override output format (pdf/docx/both) |
| `wprobo_documerge_submission_data` | `$data, $form_id` | Form_Submission.php | Modify submission data before DB insert |
| `wprobo_documerge_validate_submission` | `$errors, $data, $form_id` | Form_Submission.php | Add custom validation errors |
| `wprobo_documerge_success_message` | `$message, $form_id` | Form_Submission.php | Modify the success message shown after submission |
| `wprobo_documerge_rate_limit` | `$limit, $form_id` | Form_Submission.php | Modify rate limit threshold |
| `wprobo_documerge_form_settings` | `$settings, $form_id` | Form_Renderer.php | Modify form settings before rendering |
| `wprobo_documerge_form_classes` | `$classes, $form_id` | Form_Renderer.php | Add/remove CSS classes on the form wrapper |
| `wprobo_documerge_submit_button_label` | `$label, $form_id` | Form_Renderer.php | Modify submit button text |
| `wprobo_documerge_field_output` | `$html, $field, $form_id` | Form_Renderer.php | Modify individual field HTML output |
| `wprobo_documerge_form_output` | `$html, $form_id` | Form_Renderer.php | Modify complete form HTML |
| `wprobo_documerge_shortcode_output` | `$html, $atts` | Form_Renderer.php | Modify shortcode output |
| `wprobo_documerge_field_default_config` | `$config, $type` | Field classes | Modify default field configuration |
| `wprobo_documerge_field_types` | `$types` | Field_Registry.php | Register additional field types |
| `wprobo_documerge_docx_processor_args` | `$args, $template_path` | Docx_Processor.php | Modify DOCX processor arguments |
| `wprobo_documerge_after_merge` | `$template, $data` | Docx_Processor.php | Modify template after merge tag replacement |
| `wprobo_documerge_pdf_converter_args` | `$args` | Pdf_Converter.php | Modify mPDF configuration |
| `wprobo_documerge_pdf_html` | `$html, $docx_path` | Pdf_Converter.php | Modify HTML before PDF conversion |
| `wprobo_documerge_document_filename` | `$filename, $submission_id, $format` | Delivery_Engine.php | Customize generated document filename |
| `wprobo_documerge_download_token_expiry` | `$hours` | Delivery_Engine.php | Modify download token expiry time |
| `wprobo_documerge_admin_menu_capability` | `$capability` | Admin_Menu.php | Override the capability required for admin pages |
| `wprobo_documerge_cron_schedules` | `$schedules` | Installer.php | Modify cron schedules |

---

## Recommended New Hooks (17)

### Actions

#### 1. `wprobo_documerge_after_submission_complete`
- **Type:** Action
- **Parameters:** `$submission_id, $form_id, $document_paths`
- **Location:** `src/Form/WPRobo_DocuMerge_Form_Submission.php` — after generation + delivery both succeed
- **Why:** Currently devs must combine `submission_created` + `document_generated` + `document_delivered` to detect a fully completed flow. This single hook fires only when everything succeeded — perfect for CRM integrations, notifications, or analytics.

#### 2. `wprobo_documerge_before_template_scan`
- **Type:** Action
- **Parameters:** `$file_path`
- **Location:** `src/Template/WPRobo_DocuMerge_Template_Scanner.php` — before scan logic begins
- **Why:** Lets developers log, validate, or pre-process the DOCX file before tag scanning. Useful for checking file integrity or custom preprocessing.

#### 3. `wprobo_documerge_before_email_send`
- **Type:** Action
- **Parameters:** `$to, $subject, $body, $attachments, $submission_id`
- **Location:** `src/Document/WPRobo_DocuMerge_Delivery_Engine.php` — before `wp_mail()` call
- **Why:** Allows logging, external notification, or blocking email delivery. Pro feature but hook should exist for when user upgrades.

#### 4. `wprobo_documerge_after_email_send`
- **Type:** Action
- **Parameters:** `$success, $to, $submission_id`
- **Location:** `src/Document/WPRobo_DocuMerge_Delivery_Engine.php` — after `wp_mail()` returns
- **Why:** Track delivery success/failure in external systems. Essential for email deliverability monitoring.

#### 5. `wprobo_documerge_before_form_save`
- **Type:** Action
- **Parameters:** `$form_data, $form_id`
- **Location:** `src/Form/WPRobo_DocuMerge_Form_Builder.php` — before DB insert/update
- **Why:** Validate or modify form configuration before it's persisted. Useful for enforcing custom business rules on form structure.

#### 6. `wprobo_documerge_settings_saved`
- **Type:** Action
- **Parameters:** `$tab, $settings`
- **Location:** `src/Admin/WPRobo_DocuMerge_Settings_Page.php` — after settings are saved
- **Why:** Trigger cache busting, external sync, or logging after settings change. Important for sites using object caching.

### Filters

#### 7. `wprobo_documerge_email_recipients`
- **Type:** Filter
- **Parameters:** `$recipients, $submission_id, $form_id`
- **Location:** `src/Document/WPRobo_DocuMerge_Delivery_Engine.php` — before building email
- **Why:** Add CC/BCC recipients or change the delivery address dynamically. Critical for workflows where documents go to multiple people.

#### 8. `wprobo_documerge_email_subject`
- **Type:** Filter
- **Parameters:** `$subject, $submission_id, $form_id`
- **Location:** `src/Document/WPRobo_DocuMerge_Delivery_Engine.php` — email subject construction
- **Why:** Customize email subject per form or submission. Different forms often need different subject lines.

#### 9. `wprobo_documerge_email_body`
- **Type:** Filter
- **Parameters:** `$html_body, $submission_id, $form_id`
- **Location:** `src/Document/WPRobo_DocuMerge_Delivery_Engine.php` — email body construction
- **Why:** Customize email content, add branding, insert dynamic content. Essential for white-label solutions.

#### 10. `wprobo_documerge_email_attachments`
- **Type:** Filter
- **Parameters:** `$attachments, $submission_id`
- **Location:** `src/Document/WPRobo_DocuMerge_Delivery_Engine.php` — before `wp_mail()`
- **Why:** Add extra attachments (terms of service, additional documents) or remove the generated document from the email.

#### 11. `wprobo_documerge_download_url`
- **Type:** Filter
- **Parameters:** `$url, $submission_id, $format`
- **Location:** `src/Document/WPRobo_DocuMerge_Delivery_Engine.php` — when building download URL
- **Why:** Override download URL for CDN delivery, signed URLs, or custom access control.

#### 12. `wprobo_documerge_admin_dashboard_stats`
- **Type:** Filter
- **Parameters:** `$stats`
- **Location:** `src/Admin/WPRobo_DocuMerge_Dashboard_Page.php` — before rendering stat cards
- **Why:** Add custom stat cards or modify dashboard values. Useful for add-ons that track additional metrics.

#### 13. `wprobo_documerge_system_tags`
- **Type:** Filter
- **Parameters:** `$system_tags, $submission_id`
- **Location:** `src/Template/WPRobo_DocuMerge_Merge_Engine.php` — after building system tags
- **Why:** Add custom auto-populated tags like `{order_number}`, `{invoice_id}`, or `{custom_date}`. Developers frequently need custom system-level merge tags.

#### 14. `wprobo_documerge_modifier_value`
- **Type:** Filter
- **Parameters:** `$value, $modifier, $field_name`
- **Location:** `src/Template/WPRobo_DocuMerge_Merge_Engine.php` — after applying modifier
- **Why:** Support custom format modifiers beyond the built-in `upper`, `lower`, `format`. Example: `{price|currency}` for custom currency formatting.

#### 15. `wprobo_documerge_settings_tabs`
- **Type:** Filter
- **Parameters:** `$tabs`
- **Location:** `templates/admin/settings/main.php` — tab registration
- **Why:** Let add-ons register custom settings tabs. Standard pattern for extensible WordPress plugin settings.

#### 16. `wprobo_documerge_form_fields_before_render`
- **Type:** Filter
- **Parameters:** `$fields, $form_id`
- **Location:** `src/Form/WPRobo_DocuMerge_Form_Renderer.php` — before field loop
- **Why:** Reorder, remove, or inject fields dynamically before rendering. Enables context-aware forms (e.g., different fields for logged-in users).

#### 17. `wprobo_documerge_document_output_path`
- **Type:** Filter
- **Parameters:** `$path, $submission_id, $format`
- **Location:** `src/Document/WPRobo_DocuMerge_Document_Generator.php` — before saving file
- **Why:** Override where generated documents are stored. Needed for custom storage backends (S3, Google Cloud Storage) or multisite configurations.
