<?php
	Loader::element('editor_init');
	Loader::element('editor_config');
	$fh = Loader::helper('field', 'burrito');
	$edit = (isset($data['id']));
?>

<div class="ccm-ui">
	<?php FlashHelper::render() ?>
	<div class="ccm-pane">
		<?php
			$title = ($edit) ? 'Edit ' . $title : 'Add ' . $title; 
			$dashboard = Loader::helper('concrete/dashboard');
			echo $dashboard->getDashboardPaneHeader($title);
		?>
		<div class="ccm-pane-body">
			<form id="dataForm" action="<?php echo $this->action('save') ?>" method="POST">
				<table class="table table-striped table-bordered zebra-striped">
					<tbody>
						<?php foreach ($fields as $key => $field): ?>
							<?php if ((isset($field['minimum_level']) && AccessHelper::canAccess($field['minimum_level'])) || !isset($field['minimum_level'])): ?>
								
								<tr id="row-<?php echo $key ?>">
									<td class="form-label">
										<?php echo $field['label'] ?>
										<?php if ($field['required']): ?>
											<span class="req">*</span>
										<?php endif; ?>
									</td>
									<td>
										<div id="<?php echo $key ?>-fieldContainer" class="field-container">
											<?php $fh->output($key, $field, $data, true); ?>
										</div>
									</td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php if ($edit): ?>
					<?php
						$form = Loader::helper('form');
						echo $form->hidden('id', $data['id']);
					?>
				<?php endif; ?>
			</form>
		</div>
		<div class="ccm-pane-footer" style="text-align: right;">
			<a id="submit" href="#" class="btn primary">Save</a>
			<a href="<?php echo $backUrl ?>" class="btn">Cancel</a>
		</div>
	</div>
</div>
<script type="text/javascript" charset="utf-8">
	$('#submit').click(function(){
		$('#dataForm').submit();
	});
</script>