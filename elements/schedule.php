<?php
	$form = Loader::helper('form');
	$dtt = Loader::helper('form/date_time');
	$editSeries = (isset($data['schedule']));
?>
<style type="text/css" media="screen">
	.tabs { margin-bottom: 0px !important; }
	.tab-content { 
		background: #fff;
		padding: 20px 15px;
		clear: both;
		border-left: 1px solid #ddd;
		border-right: 1px solid #ddd;
		border-bottom: 1px solid #ddd;
	}
	.ccm-ui .tab-content #recurring select{ width: auto !important; }
	.ccm-ui .tab-content #recurring label { float: none; }
	.txt-duration { margin-left: 10px !important; }
</style>
<div class="ccm-ui">
	<ul class="tabs">		
		<li id="tab-single">
			<a href="#single">Single Event</a>
		</li>
		<li id="tab-recurring">
			<a href="#recurring">Recurring Event</a>
		</li>
	</ul>
	<div class="tab-content">
		<div id="single" class="active">
			<?php echo $dtt->datetime('date', $data['date']) ?>
		</div>
		<div id="recurring">
			<div style="margin-bottom: 20px; ">
				<label>Start <?php echo $dtt->date('start') ?></label>
				<label>End <?php echo $dtt->date('end') ?></label>
			</div>
			<div id="schedule-pickers" class="pickers well"></div>
			<?php echo $form->hidden('schedule-result'); ?>
			<a href="javascript:void(0)" class="add-button btn primary">Add pattern row</a>									
		</div>
	</div>
	<label class="checkbox-item"><?php echo $form->checkbox('all_day', 'all_day', isset($data['all_day'])) ?> All day event (times are ignored)</label>
</div>
<input type="hidden" id="mode" name="mode" value="single">
<?php
	$html = Loader::helper('html');
	echo $html->javascript('tabs.js');
	echo $html->javascript('json2.js');
	echo $html->javascript('schedulr.js');
	echo $html->css('schedulr.css');
?>
<script type="text/javascript" charset="utf-8">
	$('.tabs').tabs();
	
	function activateTab(tab) {
		$('.tabs li, .tab-content div').removeClass('active');
		$('#tab-' + tab + ', #' + tab).addClass('active');
		$('#mode').val(tab);
	}
	
	$('.tabs li a').click(function(e){
		e.preventDefault();
		$('#mode').val($(this).attr('href').substr(1));
	});
	
	$(document).ready(function(){
		<?php if ($editSeries): ?>
			<?php $series = $data['schedule'] ?>
			var opts = {
				start: "<?php echo $series['start']; ?>",
				end: "<?php echo $series['end']; ?>",
				pickers: <?php echo json_encode($series['pickers'], true); ?>
			}
			s = new Schedulr('schedule', true, opts);
			activateTab('recurring');
		<?php else: ?>
			s = new Schedulr("schedule", false);
			activateTab('single');
		<?php endif; ?>
	});
</script>