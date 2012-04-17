<div class="ccm-ui">
	<div id="<?php echo $data['id'] ?>" class="modal fade hide">
		<div class="modal-header">
			<a href="#" class="close">×</a>
			<h3><?php echo $data['title'] ?></h3>
		</div>
		<div class="modal-body">
			<p><?php echo $data['body'] ?></p>
		</div>
		<div class="modal-footer">
			<?php foreach ($data['buttons'] as $btn): ?>
				<a id="<?php echo $btn['id'] ?>" href="<?php echo $btn['href'] ?>" class="btn <?php echo (isset($btn['class'])) ? $btn['class'] : 'secondary' ?>"><?php echo $btn['label'] ?></a>
			<?php endforeach ?>
		</div>
	</div>
</div>
<script type="text/javascript" charset="utf-8">
	$(document).ready(function(){
		
		if ($('#modalContainer').length === 0) {
			// #modalContainer needs to exist on this page
			$('<div>').attr('id', 'modalContainer').addClass('ccm-ui');
		}
		
		$('#<?php echo $data["id"] ?>').modal({
			backdrop: true,
			keyboard: true
		});
	});
</script>