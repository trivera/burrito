<?php $th = Loader::helper('text') ?>
<style type="text/css" media="screen">
	a.remove {
		margin-left: 5px;
	}
</style>
<div id="<?php echo $key ?>-multi" class="multi">
	<div id="<?php echo $key ?>-items"></div>
	<a href="#" id="add-<?php echo $key ?>">+ Add another <?php echo $field['label'] ?></a>	
</div>
<script type="text/javascript" charset="utf-8">
	// scoping these in functions in case there are multiples on the page
	var <?php echo $th->camelcase($key) ?>Multi = function() {
		var i = 1;
		var key = '<?php echo $key ?>';
		var rootElement = '#' + key + '1';
		var container = '#' + key + '-items';
		
		var addRow = function() {
			i++;
			var newElement = $(rootElement).clone().attr('id', key + i).val('');
			var elWrapper = $('<div class="multi-clone">');
			elWrapper.append(newElement);
			$(container).append(elWrapper.append($('<a href="#" class="remove">[x]</a>')));
			listenRemove();
		};
		
		var listenRemove = function() {
			$('#' + key + '-multi .remove').unbind('click');
			$('#' + key + '-multi .remove').click(function(e){
			    e.preventDefault();
			    $(this).parent('div').remove();
			});
		};
		
		$('#add-<?php echo $key ?>').click(function(e){
			e.preventDefault();
			addRow();
		});
		
		<?php if (!empty($data[$key])): ?>
			$('#' + key + '-fieldContainer .multi-clone').each(function(j) {
				if (j > 0) {
					var removeLink = $('<a href="#" class="remove">[x]</a>');
					$(this).append(removeLink);
					$(removeLink).click(function(e){
						e.preventDefault();
						$(this).parent('div').remove();
					});
				}
				
			});
		<?php endif; ?>
		
	};
	
	<?php echo $th->camelcase($key) ?>Multi();
</script>