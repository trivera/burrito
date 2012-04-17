<?php 
	/*
		Add a search and date filter to any dashboard item list
	*/
	$form = Loader::helper('form'); 
	$dtt = Loader::helper('form/date_time');
?>
<div class="well">
	<?php if (!$list->isFiltered()): ?>
		<form style="margin-bottom: 0px;">
			Search <?php echo $form->text('q') ?>	
			Start Date <?php echo $dtt->date('d1', '') ?>
			End Date <?php echo $dtt->date('d2', '') ?>
			<?php echo $form->submit('search', 'Filter Results', array('class' => 'primary')) ?>
		</form>
	<?php else: ?>
		<strong>Search results</strong> Showing <?php echo $list->getTotal() ?> of <?php echo $list->getUnfilteredTotal() ?> total results.
		<a href="<?php echo $this->action('resetFilters') ?>" class="btn">Remove filters</a>
	<?php endif; ?>
</div>