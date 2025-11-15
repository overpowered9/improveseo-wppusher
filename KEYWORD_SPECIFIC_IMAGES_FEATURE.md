# Keyword-Specific Image Upload Feature

## Overview
This feature allows users to upload specific images for each keyword when creating bulk AI posts, ensuring that each generated post has a contextually relevant image tied to its keyword.

## User Flow

### Step 1: Select Keywords
1. User selects or creates a keyword list in Step 1 (Keyword & Post Title)
2. System validates that the keyword list contains at least one keyword

### Step 2: Navigate to Add Media (Step 3)
1. User proceeds through Content Settings (Step 2) to Add Media (Step 3)
2. User sees three radio options for images:
   - **AI Image Generation** (default)
   - **Upload Your Own Images** (random assignment)
   - **Upload Images Per Keyword** (NEW - contextual assignment)

### Step 3: Upload Keyword-Specific Images
1. User selects "Upload Images Per Keyword" radio button
2. System automatically:
   - Retrieves keyword list from Step 1
   - Splits keywords by newline
   - Filters empty lines
   - Generates individual upload fields for each keyword
3. For each keyword, user sees:
   - Keyword number and text
   - "Choose Image" button
   - Image preview (after upload)
   - Remove button (X) to clear uploaded image
4. User uploads images via native file picker
5. System uploads to WordPress media library via AJAX
6. Each image is associated with its specific keyword

### Step 4: Submit Bulk Project
1. User completes remaining steps (Categories, Meta, Publish Settings, Finalize)
2. On submission, system:
   - Collects all keyword-image mappings
   - Sends as JSON to backend
   - Stores base64-encoded image URLs in database with each keyword

### Step 5: Post Generation (CRON)
1. CRON job processes each keyword task
2. For each keyword, system checks:
   - Does this keyword have a specific uploaded image?
   - If yes: Use the keyword-specific image
   - If no: Fall back to random images or AI generation

## Technical Implementation

### Files Modified

#### 1. `assets/js/custom-plugin-script.js`
**New Functions:**
- `showKeywordSpecificImageUpload()` - Main entry point
  - Validates keyword list exists
  - Splits keywords and filters empty lines
  - Calls generateKeywordImageFields()
  
- `generateKeywordImageFields(keywords)` - UI Generator
  - Creates individual upload field for each keyword
  - Generates preview containers
  - Sanitizes keyword names for HTML IDs
  
- `handleKeywordImageUpload(input, keyword)` - Upload Handler
  - Creates FormData with image file
  - Sends AJAX request to `upload_keyword_image` action
  - Shows preview on success
  - Stores image URL in hidden field
  
- `removeKeywordImage(sanitizedKeyword)` - Removal Handler
  - Clears preview
  - Resets hidden field value
  - Clears file input

**Modified Functions:**
- `$("#pop_up_multi_form").submit()` - Form Submission
  - Collects all keyword-image mappings from hidden fields
  - Serializes to JSON
  - Appends to FormData as `keyword_image_paths`

#### 2. `views/GenerateAIpopup/GenerateAIpopuphtml.php`
**HTML Changes:**
- Added third radio option in Step 3:
  ```html
  <input type="radio" name="aiImage" value="Keyword_Specific_Images"
      onclick="showKeywordSpecificImageUpload();" id="Keyword_Specific_Images">
  <label for="Keyword_Specific_Images">Upload Images Per Keyword</label>
  ```
- Added container div `#keyword_specific_image_div`
- Added dynamic list container `#keyword_image_list`

**JavaScript Changes:**
- Modified next button handler to regenerate keyword fields when entering Step 3
- Ensures fields stay in sync if user navigates back to edit keywords

#### 3. `modules/ajax_keyword_image_upload.php` (NEW FILE)
**AJAX Handler:**
- Action: `wp_ajax_upload_keyword_image`
- Security: Checks `current_user_can('upload_files')`
- Process:
  1. Validates image file and keyword parameter
  2. Uses `wp_handle_upload()` for secure upload
  3. Creates WordPress attachment with `wp_insert_attachment()`
  4. Generates thumbnail metadata
  5. Returns success with attachment ID and URL

#### 4. `modules/bulk_AI_post_function.php`
**Modified Function: `multiPostData()`**

**Added at Start:**
```php
// Parse keyword-specific image mappings
$keyword_image_mappings = array();
if (!empty($_POST['keyword_image_paths'])) {
    $decoded = json_decode(stripslashes($_POST['keyword_image_paths']), true);
    if (is_array($decoded)) {
        $keyword_image_mappings = $decoded;
    }
}
```

**Modified Image Assignment Logic:**
```php
// Check for keyword-specific images first
$ai_image = '';
$keyword_trimmed = trim($value);

if (!empty($keyword_image_mappings) && isset($keyword_image_mappings[$keyword_trimmed])) {
    $image_url = $keyword_image_mappings[$keyword_trimmed];
    if (!empty($image_url)) {
        $ai_image = base64_encode($image_url);
    }
}

// Fall back to manually uploaded images if no keyword-specific image
if (empty($ai_image) && $uploaded_images_count > 0) {
    // ... existing random image logic
}
```

#### 5. `includes/modules.php`
**Added Module:**
```php
include_once IMPROVESEO_ROOT .'/modules/ajax_keyword_image_upload.php';
```

## Data Flow

### Frontend to Backend
1. **User uploads image:**
   - File → `FormData` → AJAX → `upload_keyword_image` action
   - Returns: `{success: true, data: {attachment_id, url, keyword}}`

2. **User submits bulk form:**
   - Collect all `#keyword_image_path_{sanitizedKeyword}` hidden fields
   - Create object: `{keyword1: url1, keyword2: url2, ...}`
   - Serialize to JSON
   - Append to FormData as `keyword_image_paths`

### Backend Storage
1. **Parse JSON:**
   ```php
   $keyword_image_mappings = json_decode(stripslashes($_POST['keyword_image_paths']), true);
   ```

2. **For each keyword in loop:**
   ```php
   $keyword_trimmed = trim($value);
   if (isset($keyword_image_mappings[$keyword_trimmed])) {
       $ai_image = base64_encode($keyword_image_mappings[$keyword_trimmed]);
   }
   ```

3. **Store in database:**
   - Field: `improveseo_bulktasksdetails.ai_image`
   - Format: base64-encoded image URL
   - Each keyword row gets its specific image

### Post Generation (CRON)
- When CRON processes a task, it decodes `ai_image` field
- If present, downloads and attaches to post
- Falls back to AI generation if `aiImage` field is set to AI mode

## Database Schema

### Table: `improveseo_bulktasksdetails`
**Existing Columns Used:**
- `keyword_name` (VARCHAR) - The keyword text
- `ai_image` (LONGTEXT) - Base64-encoded image URL
- `aiImage` (VARCHAR) - Image mode: "AI_image", "Multiple_images", or "Keyword_Specific_Images"

**No Schema Changes Required** - Uses existing `ai_image` column

## Error Handling

### Frontend Validation
1. **No Keywords:**
   - Displays notification: "Please select or add keywords in Step 1"
   - Resets radio to AI Image option
   - Hides keyword-specific upload div

2. **Empty Keyword List:**
   - Displays notification: "No valid keywords found"
   - Does not generate upload fields

3. **Upload Failure:**
   - Shows notification with error message
   - Clears file input for retry

### Backend Validation
1. **Permission Check:**
   - Verifies `current_user_can('upload_files')`
   - Returns error if unauthorized

2. **File Validation:**
   - Checks file exists in `$_FILES`
   - Validates MIME types (jpg, png, gif, webp)
   - Uses WordPress security functions

3. **Keyword Validation:**
   - Ensures keyword parameter is not empty
   - Sanitizes keyword text

## User Experience Enhancements

### Visual Feedback
1. **Loading States:**
   - Shows "Uploading Image..." message during upload
   - Displays loading spinner via `showImproveSEOLoading()`

2. **Success Confirmation:**
   - Shows green notification: "Image for {keyword} uploaded successfully"
   - Displays image preview immediately

3. **Preview System:**
   - Thumbnail preview (80x80px)
   - Remove button positioned at top-right corner
   - Hover effects on buttons

### Field Styling
- White background with subtle border
- Responsive grid layout
- Clear keyword labels with numbering
- Consistent button styling matching plugin theme

## Fallback Behavior

### Priority Order for Images:
1. **Keyword-Specific Image** (if uploaded)
2. **Random Upload** (if Multiple_images mode selected)
3. **AI Generation** (if AI_image mode selected)
4. **No Image** (if none available)

### Navigation Behavior:
- If user navigates back to Step 1 and changes keywords:
  - Old keyword-image mappings preserved
  - When returning to Step 3, fields regenerate
  - Only matching keywords retain their images
  
- If user switches from "Keyword_Specific_Images" to other modes:
  - Uploaded images remain in WordPress media library
  - Mappings are ignored during submission
  - No data loss occurs

## Testing Checklist

### Functional Tests
- [ ] Upload image for single keyword
- [ ] Upload images for multiple keywords
- [ ] Remove uploaded image
- [ ] Navigate back and change keywords
- [ ] Submit form with keyword-specific images
- [ ] Submit form with mixed images (some keywords with images, others without)
- [ ] Verify CRON uses correct images during post generation

### Edge Cases
- [ ] Empty keyword list
- [ ] Special characters in keywords
- [ ] Large image files
- [ ] Invalid image formats
- [ ] Upload failure scenarios
- [ ] Browser file picker cancellation

### Security Tests
- [ ] Unauthorized user cannot upload
- [ ] XSS prevention in keyword display
- [ ] SQL injection prevention in keyword storage
- [ ] File type validation

## Future Enhancements

### Potential Improvements:
1. **Bulk Upload:** Allow uploading multiple images at once with auto-matching
2. **Image Library:** Show previously uploaded images for reuse
3. **Drag & Drop:** Enable drag-drop file upload
4. **Image Editing:** Add crop/resize functionality
5. **Stock Image Search:** Integrate stock photo APIs for keyword-based search
6. **AI Image Generation Per Keyword:** Automatically generate AI images for each keyword
7. **Preview Before Submit:** Show preview of all posts with their images

## Troubleshooting

### Common Issues:

**Issue 1: Upload button not responding**
- **Cause:** JavaScript not loaded or errors in console
- **Fix:** Check browser console for errors, ensure custom-plugin-script.js is enqueued

**Issue 2: Images not appearing in posts**
- **Cause:** CRON not reading keyword_image_mappings correctly
- **Fix:** Verify JSON decoding in multiPostData(), check ai_image field encoding

**Issue 3: Keywords not showing in upload list**
- **Cause:** Keyword list not loaded or empty
- **Fix:** Ensure keyword_list textarea has content, check Step 1 validation

**Issue 4: Upload returns 404 error**
- **Cause:** AJAX action not registered
- **Fix:** Verify ajax_keyword_image_upload.php is included in modules.php

## Support & Maintenance

### Files to Monitor:
- `custom-plugin-script.js` - Frontend logic
- `ajax_keyword_image_upload.php` - Upload handler
- `bulk_AI_post_function.php` - Backend processing
- `GenerateAIpopuphtml.php` - UI structure

### Dependencies:
- WordPress Media Library functions
- jQuery for AJAX and DOM manipulation
- WordPress AJAX (wp_ajax_*)
- improveseo database tables

### Performance Considerations:
- Each image upload is a separate AJAX call (sequential, not parallel)
- Large keyword lists (100+) may result in many upload fields
- Consider pagination or lazy loading for massive lists
- Image files are stored in WordPress uploads directory
- Base64 encoding increases database storage size

## Conclusion

This feature provides a powerful enhancement to the bulk post creation workflow by allowing users to upload contextually relevant images for each keyword, ensuring that generated posts have appropriate visual content that matches the keyword's intent.
