<?php foreach ($notices as $notice): ?>
	<div class="alert alert-info">
		<?php echo $notice ?>
	</div>
<?php endforeach; ?>
<script type="text/javascript" charset="utf-8">
	setTimeout(function(){
		$('.alert').fadeOut();
	}, 5000);
</script>