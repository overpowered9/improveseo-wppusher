<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


use ImproveSEO\View;

?>

<?php View::startSection('breadcrumbs') ?>

<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_dashboard') ); ?>">Improve SEO</a>

&raquo;

<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_bulkprojects') ); ?>">Bulk Projects List</a>

&raquo;

<span><?php echo esc_html($project_name); ?></span>

<?php

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
	$url = "https://";
else
	$url = "http://";

// Append the host(domain name, ip) to the URL.   
$url .= $_SERVER['HTTP_HOST'];

// Append the requested resource location to the URL   
$url .= $_SERVER['REQUEST_URI'];

?>

<?php View::endSection('breadcrumbs') ?>

<?php View::startSection('content') ?>

<?php View::render('import/import') ?>

<h1 class="hidden">All Keywords of <?php echo $project_name; ?></h1>
<div class="show_loading alert-modal">
	<h1 class="hidden">All Keywords List</h1>
	<h2 id="mid_notice"></h2>

</div>



<div class="global-wrap">

	<div class="head-bar">
		<img src="<?php echo esc_url( improveseo_logo_url() ); ?>" alt="ImproveSEO logo">
		<h1>ImproveSEO | <?php echo esc_html( IMPROVESEO_VERSION ); ?></h1>
	</div>
	<div class="box-top">
		<ul class="breadcrumb-seo">
			<li><a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_bulkprojects') ); ?>">Improve SEO</a></li>
			<li><a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_bulkprojects') ); ?>">Bulk Projects</a></li>
			<li><?php echo esc_html($project_name); ?></li>
		</ul>
	</div>
	<?php $search = isset($search) ? $search : ''; ?>
	<div class="iseo-search-sort-row">
		<form method="GET" action="" class="iseo-search-form">
			<input type="hidden" name="page" value="improveseo_bulkprojects">
			<input type="hidden" name="action" value="viewAllTasks">
			<input type="hidden" name="id" value="<?php echo  esc_attr($id) ?>">
			<input type="text" name="search" value="<?php echo  esc_attr($search) ?>"
				class="iseo-search-input" placeholder="Search Here">
			<button type="submit" class="iseo-search-btn">
				<img src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/clarity_search-line.svg' ); ?>" alt="search">
			</button>
		</form>
		<?php if ($search): ?>
			<a href="<?php echo esc_url( admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . (int) $id) ); ?>" class="iseo-clear-btn">&#x2715; Clear</a>
		<?php endif; ?>
		<?php
		$_atsbase        = admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $id . '&paged=1' . ($search ? '&search=' . urlencode($search) : ''));
		$_atkw_order     = ($orderBy === 'keyword_name' && $order === 'ASC') ? 'DESC' : 'ASC';
		$_atdate_order   = ($orderBy === 'created_at'   && $order === 'ASC') ? 'DESC' : 'ASC';
		$_atstatus_order = ($orderBy === 'status'       && $order === 'ASC') ? 'DESC' : 'ASC';
		$_atkw_arrow     = $orderBy === 'keyword_name' ? ($order === 'ASC' ? ' ↑' : ' ↓') : '';
		$_atdate_arrow   = $orderBy === 'created_at'   ? ($order === 'ASC' ? ' ↑' : ' ↓') : '';
		$_atstatus_arrow = $orderBy === 'status'       ? ($order === 'ASC' ? ' ↑' : ' ↓') : '';
		?>
		<div class="iseo-sort-controls">
			<span class="iseo-sort-label">Sort by</span>
			<a href="<?php echo  esc_url($_atsbase . '&orderBy=keyword_name&order=' . $_atkw_order) ?>"
				class="iseo-sort-pill<?php echo  $orderBy === 'keyword_name' ? ' iseo-sort-on' : '' ?>">Keyword<?php echo  $_atkw_arrow ?></a>
			<a href="<?php echo  esc_url($_atsbase . '&orderBy=created_at&order=' . $_atdate_order) ?>"
				class="iseo-sort-pill<?php echo  $orderBy === 'created_at' ? ' iseo-sort-on' : '' ?>">Date<?php echo  $_atdate_arrow ?></a>
			<a href="<?php echo  esc_url($_atsbase . '&orderBy=status&order=' . $_atstatus_order) ?>"
				class="iseo-sort-pill<?php echo  $orderBy === 'status' ? ' iseo-sort-on' : '' ?>">Status<?php echo  $_atstatus_arrow ?></a>
		</div>
		<div class="import-export-btn">
			<button type="button" onclick="window.location.href='<?php echo esc_url( admin_url('admin.php?page=improveseo_posting&action=create_post_bulk') ); ?>';"
				class="active">+ New Bulk Project</button>
		</div>
	</div>
	<?php
	$_atbase = admin_url('admin.php?page=improveseo_bulkprojects&action=viewAllTasks&id=' . $id . ($search ? '&search=' . urlencode($search) : '') . ($orderBy !== 'created_at' ? '&orderBy=' . urlencode($orderBy) : '') . ($order !== 'DESC' ? '&order=' . urlencode($order) : ''));
	$is_preview = isset($_GET['post_preview']) && $_GET['post_preview'] == 'true' ? 'yes' : 'no';
	?>
	<form method="post">
		<input type="hidden" name="is_preview_available" id="is_preview_available" value="<?php echo $is_preview; ?>" />
		<?php wp_nonce_field('bulk_delete_tasks', 'bulk_delete_nonce'); ?>
		<div class="actions">
			<div>
				<input type="hidden" name="page" value="improveseo_bulkprojects" />
				<input type="hidden" name="noheader" value="true" />
				<input type="hidden" name="main_id" value="<?php echo esc_attr($_GET['id']); ?>" />
				<input type="hidden" value="bulk-delete-tasks" name="action">
				<button type="submit" id="doaction" class="btn_delete action" disabled style="opacity: 0.5;">Delete Selected Posts</button>
			</div>
			<div class="pagination">
				<?php if ($page > 1): ?>
					<button type="button" class="prev pagination-btn"
						onclick="window.location.href='<?php echo  esc_js($_atbase . '&paged=' . ($page - 1) . ($highlight ? '&highlight=' . $highlight : '')) ?>'">
						&lt; Prev
					</button>
				<?php else: ?>
					<button type="button" class="prev pagination-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
						&lt; Prev
					</button>
				<?php endif; ?>
				<?php for ($i = 1; $i <= $pages; $i++): ?>
					<?php if ($i == $page): ?>
						<button type="button" class="active"><?php echo esc_html( $i ); ?></button>
					<?php else: ?>
						<button type="button"
							onclick="window.location.href='<?php echo  esc_js($_atbase . '&paged=' . $i . ($highlight ? '&highlight=' . $highlight : '')) ?>'"><?php echo esc_html( $i ); ?></button>
					<?php endif; ?>
				<?php endfor; ?>
				<?php if ($page < $pages): ?>
					<button type="button" class="next pagination-btn"
						onclick="window.location.href='<?php echo  esc_js($_atbase . '&paged=' . ($page + 1) . ($highlight ? '&highlight=' . $highlight : '')) ?>'">
						Next &gt;
					</button>
				<?php else: ?>
					<button type="button" class="next pagination-btn" disabled style="opacity: 0.5; cursor: not-allowed;">
						Next &gt;
					</button>
				<?php endif; ?>
			</div>
			<div class="import-export">
				<p><?php echo esc_html( $total ); ?> Items</p>
			</div>
			<div class="import-refrsh-seo">
				<button type="button" class="toggle-row pull-right" onclick="return refreshPage()"> Refresh List
				</button>
			</div>
		</div>
		<?php function generate_slug_replace_quotes($title)
		{
			$slug = strtolower($title); // Convert to lowercase
			$slug = str_replace('"', '-', $slug); // Replace double quotes with "-"
			$slug = preg_replace('/[^a-z0-9\s-]/', '', $slug); // Remove other special characters
			$slug = preg_replace('/[\s-]+/', ' ', $slug); // Replace multiple spaces or hyphens
			$slug = trim($slug); // Trim leading/trailing spaces
			$slug = str_replace(' ', '-', $slug); // Replace spaces with hyphens
			return $slug;
		}
		?>
		<div class="improve-seo-container">
			<div class="project-lists">
				<div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #0073aa;">
					<h2 style="margin: 0; font-size: 18px; color: #23282d;">
						Bulk Project: <strong><?php echo esc_html($project_name); ?></strong>
					</h2>
					<p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">
						Viewing all posts/pages for this bulk project
					</p>
				</div>
				<div class="table-responsive">
					<table class="table ">
						<thead>
							<tr>
								<th>
									<label class="checkbox style-c">
										<input id="cb-select-all" type="checkbox">
										<div class="checkbox__checkmark"></div>
									</label>
									<h4> Keyword Name </h4>
								</th>
								<th>Language</th>
								<th>Size</th>
								<th>Processing</th>
								<th>Publish Date</th>
								<th>Post Status</th>
								<th> </th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($projects as $key => $project): ?>
								<tr <?php echo  $highlight == $project->id ? ' class="WHProject--highlight"' : '' ?>>
									<td data-label="Name" style="vertical-align: middle; padding: 15px 10px;">
										<div style="display: flex; align-items: flex-start; gap: 0px;">
											<label class="checkbox style-c" style="margin: 0;">
												<input id="cb-select-<?php echo $project->id; ?>" type="checkbox"
													name="project_ids[]" value="<?php echo $project->id; ?>">
												<div class="checkbox__checkmark"></div>
											</label>
											<h4 style="margin: 0; word-break: break-word; white-space: pre-line;"> <?php echo  $project->keyword_name ?> </h4>
										</div>
									</td>
									<td data-label="Language"><?php echo  $project->content_lang ?></td>
									<td data-label="Size"><?php echo  $project->nos_of_words ?></td>
									<td data-label="Processing" class="status finished"><?php
									if ($project->status == 'Processing') {
										echo 'Generating';
									} else if ($project->status == 'Done') {
										echo 'Completed';
								} else if ($project->status == 'Stoped') {
									echo '<span style="color: #ff4d4f; font-weight: 600;">Canceled</span>';
									} else {
										echo 'Queued';
									}
									?> </td>
									<td data-label="Publish Date"> <?php
									$iseo_pub = trim((string) $project->published_on);
									if ($project->status == 'Stoped' || $iseo_pub === '' || strpos($iseo_pub, '0000-00-00') === 0) {
										echo 'N/A';
									} elseif (strlen($iseo_pub) < 19) {
										// Date-only value (scheduled date, or legacy truncated row):
										// show just the date — never a fabricated 00:00:00.
										echo esc_html(mysql2date('m/d/Y', substr($iseo_pub, 0, 10) . ' 00:00:00'));
									} else {
										// Full datetime recorded at the actual publish transition.
										echo esc_html(mysql2date('m/d/Y H:i:s', $iseo_pub));
									}
									?> </td>
									<td data-label="Post Status" class="status paused">									
										<?php if($project->status == 'Stoped') {
											echo '<span style="color: #ff4d4f; font-weight: 600;">Canceled</span>';
										} else if($project->status != 'Done') {
											echo 'Pending Creation';
										} else if($project->state == 'Published' && $project->post_id) {
											$iseo_post_url = get_permalink($project->post_id);
											if ($iseo_post_url) {
												echo '<a href="' . esc_url($iseo_post_url) . '" target="_blank" rel="noopener" title="View published post">Published</a>';
											} else {
												echo 'Published';
											}
										} else if($project->state == 'Draft') {
											echo 'Draft';
										} else if($project->state == 'Scheduled') {
											echo 'Scheduled';
										} else {
											echo 'Pending Creation';
										}
										?>
									</td>
									<td scope="col" data-label="Action" class="actions-btn" style="width: 4%;">
										<a href="#" class="action-btn-pop"> <img
												src="<?php echo esc_url( WT_URL . '/assets/images/latest-images/ri_more-2-fill.svg' ); ?>"
												alt="ri_more-2-fill"> </a>
										<div class="actionpopup">
											<div class="popup-arrow"></div>
											<ul class="popup-menu">
												<div class="row-actions"
													style="display: flex; flex-direction: column !important;">
											<?php
											// ── Action menu, built as an ORDERED list ────────────────────
											// The order differs by post status and is part of the spec, so
											// the items are assembled into an array first and rendered in
											// one place — previously the order was an accident of which
											// if-block happened to come first in the markup.
											//
											//   Published / Scheduled : View Post, Edit Post, View Details,
											//                           Re-Generate Content
											//   Draft                 : Publish, Preview Post,
											//                           Edit Post Content, View Details,
											//                           Re-Generate Content
											//   Still generating      : Cancel Process, View Details,
											//                           Re-Generate Content
											//   Canceled              : View Details
											//
											// Nothing that used to be reachable was dropped: an item is
											// still emitted whenever it applies to the row.
											$acts     = array();
											$parent_id_for_row = $id;
											$is_stoped = ($project->status == 'Stoped');
											$is_done   = ($project->status == 'Done');
											$live_url  = !empty($project->post_id) ? get_permalink($project->post_id) : '';

											$act_view_post = array(
												'label'  => 'View Post',
												'href'   => $live_url,
												'target' => '_blank',
											);
											// Editing a row that already has a WordPress post means editing
											// the post; a draft has no post yet, so it edits the generated
											// content in place (the same screen Publish builds from).
											$act_edit_post = array(
												'label' => 'Edit Post',
												'href'  => admin_url('post.php?action=edit&post=' . $project->post_id),
												'target' => '_blank',
											);
											$act_edit_content = array(
												'label' => 'Edit Post Content',
												'href'  => admin_url('admin.php?page=improveseo_bulkprojects&action=edit_ai_content&id=' . $project->id),
											);
											$act_edit_content_pending = array(
												'label'   => 'Edit Post Content',
												'href'    => '#',
												'onclick' => "alert('Content is not generated yet. Please wait'); return false;",
											);
											$act_view_details = array(
												'label' => 'View Details',
												'href'  => admin_url('admin.php?page=improveseo_bulkprojects&action=view_task_details&id=' . $project->id . '&parent_id=' . $parent_id_for_row),
											);
											$act_regenerate = array(
												'label'   => 'Re-Generate Content',
												'href'    => 'javascript:re_generatepost(' . intval($project->id) . ')',
												'target'  => '_self',
												'onclick' => "return confirm('This will delete the existing content and regenerate from scratch. Continue?')",
											);
											// Same publish action the redesigned draft-edit screen posts to,
											// so there is exactly one publish path.
											$act_publish = array(
												'label' => 'Publish',
												'href'  => admin_url('admin.php?page=improveseo_bulkprojects&action=publish&mainid=' . $parent_id_for_row . '&id=' . $project->id),
											);
											// Opens the same in-modal instant preview every other "Preview
											// Post" button uses (views/posting/form.php,
											// views/bulkprojects/edit-ai-content.php) instead of navigating
											// to the full admin.php?action=viewAiContent page in a new tab —
											// all "Preview Post" entry points now behave identically.
											$act_view_ai = array(
												'label' => 'Preview Post',
												'href'  => 'javascript:iseoPreviewBulkTask(' . intval($project->id) . ')',
											);
											$act_cancel = array(
												'label'   => 'Cancel Process',
												'href'    => admin_url('admin.php?page=improveseo_bulkprojects&action=stop&mainid=' . $parent_id_for_row . '&id=' . $project->id),
												'onclick' => "return confirm('Are you sure you want to cancel this task? Content generation will be halted.')",
											);

											if ($is_stoped) {
												$acts[] = $act_view_details;
											} elseif (!$is_done) {
												// Content generation still in flight — nothing to view or
												// publish yet, but it can be cancelled.
												$acts[] = $act_cancel;
												$acts[] = $act_view_details;
												$acts[] = $act_regenerate;
											} elseif ($project->state == 'Published' || $project->state == 'Scheduled') {
												if ($live_url)                 $acts[] = $act_view_post;
												if (!empty($project->post_id)) $acts[] = $act_edit_post;
												$acts[] = $act_view_details;
												$acts[] = $act_regenerate;
											} elseif ($project->state == 'Draft') {
												$acts[] = $act_publish;
												if (!empty($project->ai_content)) $acts[] = $act_view_ai;
												$acts[] = !empty($project->post_id)
													? array_merge($act_edit_post, array('label' => 'Edit Post Content'))
													: (!empty($project->ai_content) ? $act_edit_content : $act_edit_content_pending);
												$acts[] = $act_view_details;
												$acts[] = $act_regenerate;
											} else {
												// Generated but no state yet (legacy rows).
												if ($live_url)                 $acts[] = $act_view_post;
												if (!empty($project->post_id)) $acts[] = $act_edit_post;
												elseif (!empty($project->ai_content)) $acts[] = $act_edit_content;
												$acts[] = $act_view_details;
												$acts[] = $act_regenerate;
											}

											foreach ($acts as $act):
												$act_href = (strpos($act['href'], 'javascript:') === 0 || $act['href'] === '#')
													? $act['href']
													: esc_url($act['href']);
											?>
												<span class="primary">
													<a class="popup-link" href="<?php echo $act_href; ?>"<?php
														if (!empty($act['target'])) echo ' target="' . esc_attr($act['target']) . '"';
														if (!empty($act['target']) && $act['target'] === '_blank') echo ' rel="noopener"';
														if (!empty($act['onclick'])) echo ' onclick="' . esc_attr($act['onclick']) . '"';
													?>><?php echo esc_html($act['label']); ?></a>
												</span>
											<?php endforeach; ?>
												</div>
											</ul>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</form>
</div>
<script>
	var iseoBulkPreviewNonce = '<?php echo esc_js( wp_create_nonce('improveseo_bulk_preview_by_id') ); ?>';

	// Row-level "Preview Post" (Draft rows): same in-modal instant preview as
	// every other "Preview Post" button — see improveseo_bulk_preview_by_id()
	// in modules/ajax.php. #iseo_preview_cancel/closeWin() and the loading
	// spinner markup/CSS are the shared ones from assets/js/form.js +
	// assets/css/made_by_me.css (form.js is enqueued on this screen already).
	function iseoPreviewBulkTask(id) {
		jQuery('#iseo_preview_error').hide();
		jQuery('#iseo_preview_loading').show();
		jQuery('#wh_prev_modal_1').show();
		jQuery('#wh_prev_modal_2').hide();
		jQuery('#preview_popup').modal({
			escapeClose: false,
			clickClose: false,
			showClose: false,
			fadeDuration: 150,
			fadeDelay: 0.35
		});

		_iseoPreviewXhr = jQuery.ajax({
			url: "<?php echo esc_url( admin_url("admin-ajax.php") ); ?>",
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'improveseo_bulk_preview_by_id',
				id: id,
				nonce: iseoBulkPreviewNonce
			},
			success: function (res) {
				_iseoPreviewXhr = null;
				if (!res || !res.success || !res.data) {
					_iseoInstantPreviewFailed(res && res.data ? res.data.message : '');
					return;
				}
				var html = '<article class="iseo-aicontent-article">';
				html += '<h1 class="iseo-aicontent-title">' + jQuery('<div>').text(res.data.title).html() + '</h1>';
				html += '<div class="iseo-aicontent-body">' + res.data.html + '</div>';
				html += '</article>';
				jQuery('#preview_content_area').html(html);
				jQuery('#wh_prev_modal_1').hide();
				jQuery('#wh_prev_modal_2').show();
			},
			error: function () {
				_iseoPreviewXhr = null;
				_iseoInstantPreviewFailed();
			}
		});
	}

	function re_generatepost(id) {
		jQuery('.show_loading').css("display", "block");
		jQuery(".show_loading h2").html("Post is re-generating, Please wait ...<br><strong style='color: #d63638; margin-top: 10px; display: inline-block;'>Do not close this page!</strong>");
		re_generate(id);
	}

	function re_generate(ids) {
		jQuery
			.ajax({
				url: "<?php echo esc_url( admin_url("admin-ajax.php") ); ?>",
				data: ({
					action: 're_generate_post',
					nonce: '<?php echo esc_js( wp_create_nonce( 'improveseo_ajax' ) ); ?>',
					id: ids
				}),
				success: function (data) {
					console.log(data);
					jQuery('.show_loading').css("display", "none");
					alert("Content has been Re-Generated successfully.");
					location.reload(true);
				},
				error: function(xhr, status, error) {
					jQuery('.show_loading').css("display", "none");
					alert("Failed to regenerate content. Please try again.");
					console.error(error);
				}
			});
	}

	function build_project(id) {
		jQuery('.show_loading').css("display", "block");
		jQuery(".show_loading h1")
			.html("Building project.... in progress");
		start_build(id);
	}
	var numm;

	function start_build(ids) {
		var max_iterations = parseInt(jQuery('#max-iterations').val());
		// wp_json_encode() rather than esc_url(): this lands inside a <script> block,
		// where there is no HTML entity decoding, so esc_url() would leave a literal
		// &amp; in the query string and the built URL would 404. wp_json_encode()
		// emits a complete, correctly quoted JS string literal.
		var export_url = <?php echo wp_json_encode( admin_url( 'admin.php?page=improveseo_projects&action=export_preview_url&id=' ) ); ?>;
		jQuery
			.ajax({
				url: "<?php echo esc_url( admin_url("admin-ajax.php") ); ?>",
				data: ({
					action: 'workdex_builder_ajax',
					nonce: '<?php echo esc_js( wp_create_nonce( 'improveseo_ajax' ) ); ?>',
					page: 100,
					ajax: 1,
					id: ids
				}),
				success: function (data) {
					jQuery(".show_loading h1")
						.html("Building project.... in progress");
					jQuery(".show_loading h2")
						.html("Posts generated by now " + data);
					var is_preview_available = jQuery('#is_preview_available').val();
					if (max_iterations > 100) {
						if (numm == data) {
							jQuery('.show_loading').css("display", "none");
							if (is_preview_available == "yes") {
								window.location.href = export_url + ids + '&noheader=true';
							}
						} else {
							numm = data;
							setTimeout("start_build(" + ids + ")", 100);
						}
						location.reload(true);
					} else {
						if (is_preview_available == "yes") {
							jQuery(".show_loading h1").html("Exporting posts. Please wait");
							jQuery(".show_loading h2").html("");
							window.location.href = export_url + ids + '&noheader=true';
						} else {
							setTimeout(function () {
								jQuery('.show_loading').css("display", "none");
								location.reload(true);
							}, 100);
						}
					}
				}
			});
	}

	function update_project(id) {
		jQuery('.show_loading').css("display", "block");
		jQuery(".show_loading h1")
			.html("Updating project.... in progress");
		start_update(id);
	}
	var numm_update;

	function start_update(ids) {
		var new_location = "<?php echo esc_url( admin_url('admin.php?page=improveseo_projects') ); ?>";
		var max_iterations = parseInt(jQuery('#max-iterations[data-project="' + ids + '"]').val());
		jQuery.ajax({
			url: "<?php echo esc_url( admin_url("admin-ajax.php") ); ?>",
			data: ({
				action: 'workdex_builder_update_ajax',
				nonce: '<?php echo esc_js( wp_create_nonce( 'improveseo_ajax' ) ); ?>',
				page: 100,
				ajax: 1,
				id: ids
			}),
			success: function (data) {
				jQuery(".show_loading h1")
					.html("Updating posts.... in progress");
				jQuery(".show_loading h2")
					.html("Posts generated by now " + data + '/' + max_iterations);

				if (numm_update == data) {
					jQuery('.show_loading').css("display", "none");
				} else {
					numm_update = data;
					if (max_iterations < data) {
						setTimeout("start_update(" + ids + ")", 500);
					}
				}
				if (max_iterations == data) {
					window.location.href = new_location;
				} else {
					location.reload(true);
				}
			}
		});
	}
	
	function refreshPage() {
		location.reload();
		return false;
	}
	
	jQuery(document).ready(function ($) {
		$('#cb-select-all').on('change', function () {
			var isChecked = $(this).prop('checked');
			$('input[name="project_ids[]"]').prop('checked', isChecked);
			updateDeleteButtonState();
		});

		$('input[name="project_ids[]"]').on('change', function () {
			var totalCheckboxes = $('input[name="project_ids[]"]').length;
			var checkedCheckboxes = $('input[name="project_ids[]"]:checked').length;

			if (checkedCheckboxes === 0) {
				$('#cb-select-all').prop('indeterminate', false).prop('checked', false);
			} else if (checkedCheckboxes === totalCheckboxes) {
				$('#cb-select-all').prop('indeterminate', false).prop('checked', true);
			} else {
				$('#cb-select-all').prop('indeterminate', true);
			}
			updateDeleteButtonState();
		});

		function updateDeleteButtonState() {
			var checkedCount = $('input[name="project_ids[]"]:checked').length;
			if (checkedCount > 0) {
				$('#doaction').prop('disabled', false).css('opacity', '1').text('Delete ' + checkedCount + ' Selected Post' + (checkedCount > 1 ? 's' : ''));
			} else {
				$('#doaction').prop('disabled', true).css('opacity', '0.5').text('Delete Selected Posts');
			}
		}

		updateDeleteButtonState();

		$('#doaction').on('click', function (e) {
			var checkedItems = $('input[name="project_ids[]"]:checked').length;
			if (checkedItems === 0) {
				e.preventDefault();
				alert('Please select at least one task to delete.');
				return false;
			}

			return confirm('Are you sure you want to delete ' + checkedItems + ' selected post(s) and their associated data? This action cannot be undone.');
		});
	});
</script>

<?php

if (isset($_GET['post_preview'])) {
	if ($_GET['post_preview'] == 'true') {
		$project = $projects[0];
		if ($project->state == 'Published' && $project->iteration < $project->max_iterations) { ?>
			<script type="text/javascript">
				build_project(<?php echo $project->id ?>);
			</script>
			<?php
		} elseif ($project->state == 'Published' && $project->iteration == $project->max_iterations) {
			$export_url = admin_url("admin.php?page=improveseo_projects&action=export_preview_url&id={$project->id}&noheader=true");
		}
	}
}

if (isset($_GET['build_posts_id'])) { ?>
	<script type='text/javascript'>
		update_project(<?php echo  $_GET['build_posts_id'] ?>);
	</script>
<?php } ?>

<style>
/* Ensure the table stays visible and doesn't collapse when the modal opens */
body.modal-open .table-responsive,
.table-responsive {
    min-height: 200px;
}
/* Ensure the modal overlay dims the background properly */
.blocker {
    background-color: rgba(0,0,0,0.75) !important;
}
</style>

<?php // Post preview: same in-modal instant preview as the single/bulk draft
// editors (views/posting/form.php, views/bulkprojects/edit-ai-content.php) —
// renders through improveseo_bulk_build_post_content(), the SAME function
// the "View AI Content" page itself uses. Styles live in
// assets/css/made_by_me.css, scoped under #preview_content_area. ?>
<div id="preview_popup" class="modal" style="text-align:center; width:90%; max-width:1100px;">
	<div id="wh_prev_modal_1">
		<div id="iseo_preview_loading" class="iseo-preview-loading">
			<div class="iseo-preview-spinner" role="status" aria-label="Generating preview"></div>
			<b class="iseo-preview-loading-title">Generating preview</b>
			<span class="iseo-preview-loading-note">Rendering this post.</span>
			<button type="button" id="iseo_preview_cancel" class="button iseo-preview-action">Cancel</button>
		</div>
		<div id="iseo_preview_error" class="iseo-preview-error" style="display:none;">
			<p id="iseo_preview_error_text"></p>
			<button type="button" class="button iseo-preview-action iseo-preview-action--primary"
				onclick="closeWin()">Close</button>
		</div>
	</div>
	<div id="wh_prev_modal_2" style="display:none;">
		<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
			<b style="font-size:18px">Post preview</b>
			<button type="button" id="open_win"
				class="button button-primary iseo-preview-action iseo-preview-action--primary"
				onclick="closeWin()">Close preview</button>
		</div>
		<div id="preview_content_area" class="iseo-aicontent-wrap"></div>
	</div>
</div>

<?php View::endSection('content') ?>
<?php View::make('layouts.main') ?>