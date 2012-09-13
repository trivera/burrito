<?php 
	/* 
		This element will allow for multiple instances of a file or image picker
		on a form. This will generate all of the HTML, CSS, and JS needed to accomplish this.
	*/
	$th = Loader::helper('text'); 
	$uh = Loader::helper('concrete/urls');
	
	if ($data[$key]) {
		$values = array();
		foreach ($data[$key] as $item) {
			$values[] = $item->{$key};
		}
	}
?>
<div id="<?php echo $key ?>-multi" class="multi">
	<div id="<?php echo $key ?>-items" class="file-pickers"></div>
	<a href="#" class="addFile" id="add-<?php echo $key ?>">+ Add another <?php echo $field['label'] ?></a>
</div>
<script type="text/javascript" charset="utf-8">
	/* Lots of injected PHP variables here. Not a fan but it was the best way to accomplish this with file/image widgets. */
	
	// giving this a unique name based on a camelCased version of the $key variable
	// the functions contained are scoped so they shouldn't conflict with multiple instances on a page
	var <?php echo $th->camelcase($key) ?>Multi = function(edit) {
		var edit = (edit) ? true : false; // set default for parameter
		var key = '<?php echo $key ?>';
		var container = '#' + key + '-items';
		var removeText = '&uarr; Remove this <?php echo $field["type"] ?>';
		var loadedCount = 0;
		
		<?php if ($values): ?>
			var values = <?php echo json_encode($values) ?>;
		<?php else: ?>
			var values = [];
		<?php endif; ?>
		
		var addEditRows = function(){
			if (values.length > 0) {
				for (var j=0; j < values.length; j++) {
					addRow();
				}
			}
		};
	
		// gets the HTML/JS for a new widget from a special tool (see burrito/tools/generate_file_widget.php) and adds it to the form
		var addRow = function(fileId) {
			
			// these are the GET parameters sent to the widget generator tool
			var parameters = {
				key: "<?php echo $key ?>",
				type: "<?php echo $field['type'] ?>"
			};
			
			$.ajax({
				type: 'GET',
				url: '<?php echo $uh->getToolsURL("generate_file_widget", "burrito") ?>',
				data: parameters,
				success: function(html) {
					
					// create a jQuery object out of the HTML text that we received
					var newElement = $('<div class="file-picker-multi-item">').html(html);
					
					// need to adjust IDs so JS stuff actually works
					newElement.find('*').each(function(){
						fixIds(this);
					});
					
					// create some custom HTML around the generated HTML
					var elWrapper = $('<div class="multi-clone">');
					elWrapper.append(newElement);
					
					// add HTML elements to the main items container
					$(container).append(elWrapper.append($('<a href="#" class="remove">' + removeText + '</a>')));
					
					// add listeners to the new remove buttons
					listenRemove();
					
					// increment the loaded count
					loadedCount++;
					
					// if we are all done loading, populate the images (if in edit mode)
					if (edit && loadedCount == values.length) {
						$(document).ready(function(){
							// need these in a document.ready because the C5 function 
							$('#' + key + '-multi .file-picker-multi-item').each(function(ii){
								var itemKey = $(this).find('.ccm-file-selected-wrapper').get(0).id;
								itemKey = itemKey.substr(0, itemKey.indexOf('-'));
								ccm_triggerSelectFile(values[ii], itemKey);
							});							
						});
					}
				}
			});
		};
		
		// Adjusts IDs and attributes of elements so they have proper unique IDs
		var fixIds = function(element) {
			var uniqueId = key + loadedCount;
			// replace the element's ID with the new, unique one
			$(element).attr('id', element.id.replace(key, uniqueId));
			
			// update the custom attribute for the display key
			var displayId = uniqueId + '-fm-display';
			if (element.id == displayId) {
				$(element).attr('ccm-file-manager-field', uniqueId);
			}
			
			// replace the name of the element in the inline JS call
			if ($(element).is('a')) {
				$(element).attr('onclick', $(element).attr('onclick').replace(key, uniqueId));
			}
			
			// add the square brackets to the hidden input so these get dumped into an array in the POST request
			var hiddenInputId = uniqueId + '-fm-value';
			if (element.id == hiddenInputId) {
				$(element).attr('name', key + '[]');
			}
			
			return uniqueId;
		};
		
		// adds listeners for the remove links
		var listenRemove = function() {
			$('#' + key + '-multi .remove').unbind('click');
			$('#' + key + '-multi .remove').click(function(e){
			    e.preventDefault();
			    $(this).parent('div').remove();
			});
		};
		
		// add square brackets to default hidden input, so it gets added to a POST array
		if (!edit || values.length === 0) {
			$('#' + key + '-fm-value').attr('name', key + '[]');
		}
		else {
			// in edit mode, generate a widget for every item that was saved
			addEditRows();
		}
		
		// listen to the add link
		$('#add-<?php echo $key ?>').click(function(e){
			e.preventDefault();
			addRow();
		});
	};
	
	// invoke the unique function when ready
	<?php echo $th->camelcase($key) ?>Multi(<?php echo (isset($data[$key])) ? 'true' : 'false' ?>);
</script>