<div class="ccm-ui">
	<div id="errors" class="alert-message block-message error">
		<p><strong>Hold on a second.</strong> The following problems were detected:</p>
		<ul>
			<?php foreach ($errors as $error): ?>
				<li><?php echo $error ?></li>
			<?php endforeach ?>
		</ul>
	</div>
</div>

<script type="text/javascript" charset="utf-8">
	$('html,body').animate(
		{ scrollTop: $("#errors").offset().top - 80 },
		'slow'
	);
</script>

<style type="text/css" media="screen">
	#ccm-quick-nav { display: none !important; }
</style>
