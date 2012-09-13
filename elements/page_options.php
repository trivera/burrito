<?php $edit = (isset($data['id'])) ?>
<style type="text/css" media="screen">
	#pageOptions label {
		width: auto !important;
		padding: 0 !important;
		margin-right: 15px;
	}
	#pageSelector {
		clear: both;
		padding-top: 10px;
	}
</style>
<div id="pageOptions">
	<?php
		$form = Loader::helper('form');
		$ps = Loader::helper('form/page_selector');
	?>
	<?php if (!$edit): ?>
		<label><?php echo $form->radio('page_option', 'create', 'create') ?> Create new page</label>
		<label><?php echo $form->radio('page_option', 'existing', 'create') ?> Attach to existing page</label>
		<label><?php echo $form->radio('page_option', 'none', 'create') ?> Don't attach to a page</label>
	<?php endif ?>
	<div id="pageSelector" style="display: <?php echo ($edit) ? 'block': 'none' ?>">
		<?php echo $ps->selectPage($key, $data['page_id']); ?>
	</div>
</div>
<script type="text/javascript" charset="utf-8">
	$('#pageOptions input[type=radio]').bind('change', function(){
		if ($(this).val() == 'existing') {
			$('#pageSelector').show();
		}
		else {
			$('#pageSelector').hide();
		}
	});
</script>