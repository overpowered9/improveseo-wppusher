<?php





include_once 'includes/ImproveSEO/Autoloader.php';





new ImproveSEO\Autoloader();





// session_start();
// Add this hook instead:
add_action('init', 'improveseo_start_session');

function improveseo_start_session() {
    if (!session_id()) {
        session_start();
    }
}