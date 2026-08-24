<?php



// Libraries

include_once 'autoloader.php';



// Other parts

include_once 'includes/installer.php';

include_once 'includes/api.php';

include_once 'includes/onboarding.php';


// Core parts

include_once 'includes/debug.php';



include_once 'includes/assets.php';

include_once 'includes/ajax.php';

include_once 'includes/crons.php';

include_once 'includes/filters.php';

include_once 'includes/functions.php';

include_once 'includes/modules.php';

include_once 'includes/menus.php';

include_once 'includes/posttypes.php';

include_once 'includes/seo.php';

include_once 'includes/settings.php';

// After settings.php — it hooks the two options that file registers.
include_once 'includes/connection-status.php';

include_once 'includes/shortcode-popup.php';

include_once 'includes/ScheduledPosts.php';



// Features

include_once 'features/keyword.php';

