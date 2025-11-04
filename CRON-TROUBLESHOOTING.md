# ImproveSEO Bulk Cron Troubleshooting Guide

## Problem Diagnosis

The bulk AI post cron job stopped updating debug.log approximately 9 hours ago. 

## Root Causes Found

1. **Include Path Error (FIXED)** - Files were trying to include from `dirname(__FILE__) . '/modules/...'` instead of `dirname(__FILE__) . '/...'`

2. **Missing API Credentials (LIKELY MAIN ISSUE)** - The `createAIpost2bulk()` function requires:
   - `improveseo_api_key` 
   - `improveseo_site_code`
   
   If these are not configured in WordPress settings, the function returns an error string instead of generating content.

## Solutions Applied

### 1. Fixed Include Paths ✅
- Updated `bulk_AI_post_function.php` line 3-5
- Updated `single_AI_post_function.php` line 3-5

### 2. Added Comprehensive Logging ✅
- `CronjobRequest()` now logs start/end and all function calls
- `createAIpost2bulk()` logs API credential status and connection attempts
- Better error tracking throughout the workflow

### 3. Fixed Audience Data Access ✅
- Changed `$_COOKIE['AudienceData']` to `isset($_COOKIE['AudienceData']) ? $_COOKIE['AudienceData'] : ''`
- Cron jobs don't have cookies, so this was causing issues

## How to Fix

### Option 1: Configure API Credentials (RECOMMENDED)

1. Go to WordPress Admin → ImproveSEO → Settings
2. Enter your API Key and Site Code from the ImproveSEO Dashboard
3. Click "Test Server Connection" to verify
4. Save settings

### Option 2: Fallback to Legacy Function (TEMPORARY)

If you don't have API credentials yet, you can temporarily switch back to the old direct OpenAI implementation:

1. Edit `modules/bulk_AI_post_function.php` line 303
2. Change: `$AI_Content = createAIpost2bulk(...)` 
3. To: `$AI_Content = createAIpost(...)` (the original function)

**NOTE:** This requires OpenAI API key to be configured in the old settings field.

## Testing the Fix

### Method 1: Check Debug Log
```
tail -f wp-content/debug.log
```

Look for these new log entries:
```
[2025-11-04 10:30:15] === CRON JOB STARTED ===
[2025-11-04 10:30:15] cron calling working...
[2025-11-04 10:30:15] Calling generateBulkAiContent()...
[2025-11-04 10:30:15] createAIpost2bulk called for keyword: your-keyword
[2025-11-04 10:30:15] API Key: Configured, Site Code: Configured
[2025-11-04 10:30:15] Connecting to admin server for bulk generation...
```

If you see:
```
[2025-11-04 10:30:15] API Key: MISSING, Site Code: MISSING
[2025-11-04 10:30:15] createAIpost2bulk Error: Missing API credentials!
```

Then you need to configure the API credentials in settings.

### Method 2: Manual Trigger
Visit: `https://monkeyatpeace.com/test-cron.php`

This will:
- Show cron schedule status
- Manually execute the cron job
- Display debug log entries
- Show pending task count

### Method 3: Force WordPress Cron
Visit: `https://monkeyatpeace.com/wp-cron.php?doing_wp_cron`

## Expected Behavior After Fix

✅ Debug.log updates every 2 minutes (or your cron interval)
✅ Pending bulk tasks get processed automatically
✅ Clear error messages if API credentials are missing
✅ Detailed logging shows exact point of failure

## Next Steps

1. Check if API Key and Site Code are configured in settings
2. If not configured, add them from your ImproveSEO Dashboard
3. Wait 2 minutes or manually trigger cron
4. Check debug.log for new entries
5. Verify that pending tasks are being processed

## Files Modified

- `modules/bulk_AI_post_function.php` - Fixed includes, added logging
- `modules/single_AI_post_function.php` - Fixed includes
- `test-cron.php` - Created diagnostic tool

## Important Notes

- The cron IS running, but was failing silently due to missing credentials
- The include path issue prevented helper functions from loading
- Added isset() checks for cookie access in cron context
- All logging now uses `my_plugin_log()` which writes to wp-content/debug.log

---

**Delete `test-cron.php` after testing for security!**
