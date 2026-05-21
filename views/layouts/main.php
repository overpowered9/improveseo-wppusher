<div class="wrap improveseo-page">

	<div class="Breadcrumbs">
		<?= ImproveSEO\View::section('breadcrumbs') ?>
	</div>

	<?php
		ImproveSEO\FlashMessage::handle();
	?>

	<?= ImproveSEO\View::section('content') ?>
</div>

<div id="improveseo-help-fab">
	<div id="improveseo-help-menu" class="improveseo-help-menu" aria-hidden="true">
		<a href="https://account.improveseoplugin.com/support" target="_blank" rel="noopener noreferrer" class="improveseo-help-menu-item">
			<span class="dashicons dashicons-sos"></span> Support
		</a>
		<a href="https://account.improveseoplugin.com/tutorial" target="_blank" rel="noopener noreferrer" class="improveseo-help-menu-item">
			<span class="dashicons dashicons-welcome-learn-more"></span> Tutorial
		</a>
	</div>
	<button id="improveseo-help-btn" aria-label="Help" aria-expanded="false">
		<span class="dashicons dashicons-editor-help"></span>
	</button>
</div>

<script>
(function() {
	var btn = document.getElementById('improveseo-help-btn');
	var menu = document.getElementById('improveseo-help-menu');
	if (!btn || !menu) return;
	btn.addEventListener('click', function(e) {
		e.stopPropagation();
		var open = menu.getAttribute('aria-hidden') === 'false';
		menu.setAttribute('aria-hidden', open ? 'true' : 'false');
		btn.setAttribute('aria-expanded', open ? 'false' : 'true');
		menu.classList.toggle('improveseo-help-menu--open', !open);
	});
	document.addEventListener('click', function() {
		menu.setAttribute('aria-hidden', 'true');
		btn.setAttribute('aria-expanded', 'false');
		menu.classList.remove('improveseo-help-menu--open');
	});
})();
</script>
