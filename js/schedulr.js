/* 
	Schedulr JS by David Strack
	Outputs an Outlook-like scheduler for recurring events, 
	formats the result as JSON for inclusion in an HTTP request 
*/

function Schedulr(elementId, edit, opts, duration) {
	this.element = elementId; //the div to put the HTML into
	this.container = this.element + "-pickers"; //the div that holds the pickers
	this.pickers = []; //array for each picker object
	this.selected = []; //keeps what days are selected (ex: because only one monday can be chosen at a time)
	this.increment = 0;
	if (opts) {
		this.opts = opts;
	}
	else {
		this.opts = {};
	}
	this.opts.includeTime = true;
	this.opts.allowMulti = true;
	this.opts.duration = false;
	this.opts.singleRow = false;
	
	this.setup(); //run setup function
	this.allowMulti = this.opts.allowMulti; //allow multiple instances of a day in the picker (more than one monday, etc)
	if (typeof(duration) == "undefined" || duration === false) {
		this.duration = false;
	}
	else {
		this.duration = true;
	}
	
	if (!edit) {
		this.addPicker(); //add the first picker automatically
	}
	else{
		this.edit(opts);
	}
}

Schedulr.prototype = {
	
	setup: function(){
		this.increment = 0;
		//this.addPicker();
		var that = this;
		$(".add-button").click(function(event){
			that.addPicker();
			event.preventDefault();
		});
	},
	
	initHtml: function(){
		var title = '<div class="title">New Recurring Schedule</div>';
		var c5start = '<div class="schedulr-input"><span>Start</span><input id="' + this.element + '-start-date" class="ccm-input-date hasDatePicker" /></div>';
		var c5end = '<div class="schedulr-input"><span>End</span><input id="' + this.element + '-end-date" class="ccm-input-date hasDatePicker" /></div>';
		var container = '<div id="' + this.container + '" class="pickers"></div>';
		var btnAdd = '<div class="schedulr-buttons"><a href="#" class="add-button">Add row</a></div>';
		var btnSave = '<div class="schedulr-buttons"><a href="#" class="save-button">Save recurring schedule</a></div>';
		var hidden = '<input id="' + this.element + '-result" type="hidden" value="" />';
		$("#" + this.element).html(title + c5start + c5end + container + btnAdd + btnSave + hidden);
	},
	
	addPicker: function(xdays, xtime, animate){
		if (typeof(animate) == "undefined"){
			animate = true;
		}
		if (this.pickers.length < 7){
			
			//values match PHP 'w' formatting
			var days = [
				{label: "Mo", value: 1, selected: false, active: true},
				{label: "Tu", value: 2, selected: false, active: true},
				{label: "We", value: 3, selected: false, active: true},
				{label: "Th", value: 4, selected: false, active: true},
				{label: "Fr", value: 5, selected: false, active: true},
				{label: "Sa", value: 6, selected: false, active: true},
				{label: "Sn", value: 0, selected: false, active: true}
			];
			
			this.increment++; //for the id of the picker
			
			//if this is being edited, select the proper days
			if (typeof(xdays) != "undefined"){
				for (var d=0; d < xdays.length; d++) {
					for (var d2=0; d2 < days.length; d2++) {
						if (xdays[d] == days[d2].value){
							days[d2].selected = true;
						}
					}
				}				
			}
			var hr;
			var min;
			var a;
			
			if (typeof(xtime) != "undefined"){
				hr = xtime.substring(0, xtime.indexOf(":"));
				min = xtime.substring(xtime.indexOf(":") + 1, xtime.indexOf(":") + 3);
				a = xtime.substring(xtime.indexOf(" ") + 1);
			}else{
				hr = "12";
				min = "00";
				a = "am";
			}
			
			var picker = {
				index: this.increment,
				days: days,
				hour: hr, 
				minute: min, 
				amPm: a
			};
			
			//add the picker object to the main array
			this.pickers.push(picker);
			if(this.pickers.length == 7){
				this.showAddButton(false);
			}
			var daysHtml = "";

			//build the HTML for the day elements
			for (var i=0; i < days.length; i++) {
				var dayClass = "";
				
				//checks if any of the days are in the this.selected array
				if (!this.allowMulti){
					for (var y=0; y < this.selected.length; y++) {
						if (this.selected[y] == days[i].label){
							days[i].active = false;
							dayClass = "inactive";
						}
					}
				}
				
				//is it selected?
				if (days[i].selected){
					dayClass = "selected";
					this.selected.push(days[i].label);
				}
				
				daysHtml += '<div id="' + days[i].label + picker.index + '" class="day ' + dayClass + '">' + days[i].label + '</div>';
			}

			//create the hours select box
			var hoursHtml = '<select id="hours-' + picker.index + '" class="hour-select">';
			var hours = ["12","1","2","3","4","5","6","7","8","9","10","11"]; //manually set the array to get 12 first
			for (var h=0; h < hours.length; h++) {
				hoursHtml += '<option id="' + picker.index + "-" + hours[h] + '" value="' + hours[h] + '">' + hours[h] + '</option>';
			}
			hoursHtml += '</select>';

			$("#hours-" + picker.index).val(picker.hours);

			//create the minutes select box
			var minutesHtml = '<select id="minutes-' + picker.index + '" class="minute-select">';
			for (var m=0; m < 60; m++) {
				if (m%5 === 0){
					if (m < 10){
						m = "0" + m;
					}

					minutesHtml += '<option id="' + picker.index + "-" + m + '" value="' + m + '">' + m + '</option>';	
				}
			}
			minutesHtml += "</select>";

			$("#minutes-" + picker.index).val(picker.minute);

			var amPmHtml = '<select id="ampm-' + picker.index + '" class="am-pm-select"><option value="am">AM</option><option value="pm">PM</option></select>';

			$("#ampm-" + picker.index).val(picker.amPm);

			if (this.duration){
				durationHtml = '<input class="txt-duration" size="3" maxlength="3" id="duration-' + picker.index + '" type="text" style="width: 80px" /><label for="duration-'+picker.index+'">hours</label>';
			}
			else {
				durationHtml = "";
			}

			var controls;
			if (this.opts.includeTime) {
				controls = '<div class="controls">at ' + hoursHtml + ':' + minutesHtml + amPmHtml + durationHtml + '<a href="javascript:void(0)" id="remove-' + picker.index + '" class="remove">[x]</a></div>'; //need to include the select box
			}
			else {
				controls = '<div class="controls"><a href="javascript:void(0)" id="remove-' + picker.index + '" class="remove">[x]</a></div>'; //need to include the select box
			}
			
			//append to the element
			$("#" + this.container).append("<div id='picker-" + picker.index + "' class='picker'>" + daysHtml + controls + "</div>");

			//set the values of all pickers (these got reset whenever a new picker was added...strange)
			this.initPickers();
			
			//add listeners
			this.addEventHandlers();
			
			//reveal the new picker
			if(animate){
				$("#picker-" + picker.index).slideDown();
			}else{
				$("#picker-" + picker.index).css("display", "block");
			}

		}else{
			this.showAddButton(false);
		}
	},
	
	//remove a picker from the page
	removePicker: function(id){
		//remove it from the array
		var pi; //picker index
		for (var i=0; i < this.pickers.length; i++) {
			var picker = this.pickers[i];
			if (picker.index == id){
				for (var j=0; j < picker.days.length; j++) {
					if (picker.days[j].selected){
						for (var k=0; k < this.selected.length; k++) {
							if (this.selected[k] == picker.days[j].label){
								this.selected.splice(k,1); //remove the day from the global array
								this.toggleAll(true, picker.index, picker.days[j].label);
							}
						}						
					}
				}
				pi = i;
			}
		}
		this.pickers.splice(pi, 1);
		$("#picker-" + id).slideUp(function(){
			//on animation complete, remove it from the DOM
			$("#picker-" + id).remove();						
		});	
		if (this.pickers.length < 7){
			this.showAddButton(true);
		}
	},
	
	//set the <select> values for hours and minutes
	initPickers: function(){
		for (var i=0; i < this.pickers.length; i++) {
			var picker = this.pickers[i];
			$("#hours-" + picker.index).val(picker.hour);
			$("#minutes-" + picker.index).val(picker.minute);
			$("#ampm-" + picker.index).val(picker.amPm);
		}
	},
	
	//happens when a day is clicked
	toggleDaySelect: function(element){		
		var day = element.substring(0, 2);
		var index = element.substring(2);
		for (var i=0; i < this.pickers.length; i++) {
			var picker = this.pickers[i];
			if (picker.index == index){
				//we have the right picker, now find the day
				for (var j=0; j < picker.days.length; j++) {
					var xday = picker.days[j];
					if (xday.label == day){
						if (xday.selected && xday.active){
							//deselect the day
							$("#" + element).removeClass("selected");
							xday.selected = false;
							this.toggleAll(true, picker.index, xday.label);
						}
						else if (!xday.selected && xday.active){
							//select the day
							$("#" + element).addClass("selected");
							xday.selected = true;
							this.toggleAll(false, picker.index, xday.label);
						}		
					}
					this.pickers[i].days[j] = xday; //pass back the new day object
				}
			}
		}
		if (this.selected.length == 7){
			this.showAddButton(false);
		}else{
			this.showAddButton(true);
		}
	},
	
	//this odd function either enables or disables other instances of a certain day if it is already selected
	//(ex: Wednesday can only be selected in one picker at a time)	
	toggleAll: function(enable, index, day){
		if (!this.allowMulti){
			if (!enable){
				//add the selected day to global array
				this.selected.push(day);

				//disable all other currently active days
				for (var i=0; i < this.pickers.length; i++) {
					var picker = this.pickers[i];
					if (picker.index != index){
						for (var j=0; j < picker.days.length; j++) {
							var xday = picker.days[j];
							if (xday.label == day){
								xday.active = false;
								$("#" + xday.label + picker.index).addClass("inactive");
							}
							this.pickers[i].days[j] = xday;
						}

					}
				}
			}
			else{
				//remove the selected day from the global array
				for (var x=0; x < this.selected.length; x++) {
					if (this.selected[x] == day){
						this.selected.splice(x,1);	
					}
				}
				for (var k=0; k < this.pickers.length; k++) {
					var xpicker = this.pickers[k];
					if (xpicker.index != index){
						for (var m=0; m < xpicker.days.length; m++) {
							var yday = xpicker.days[m];
							if (yday.label == day){
								$("#" + yday.label + xpicker.index).removeClass("inactive");
								yday.active = true;
							}
							this.pickers[k].days[m] = yday;
						}
					}
				}
			}	
		}
	},
	
	edit: function(params){
		//pre-populates a scheduler with a pattern
		//params is a JSON object that contains the pattern info
		var start = params.start;
		var end = params.end;
		
		$("#start").val(start);
		$("#end").val(end);
		
		var pickers = params.pickers;
		
		for (var i=0; i < pickers.length; i++) {
			var picker = pickers[i];
			var days = picker.days.split(",");
			this.addPicker(days, picker.xtime, false);
			this.save();
		}
	},
	
	//TODO: combine these time setters into one function
	//set the hour for a picker
	setHour: function(index, value){
		for (var i=0; i < this.pickers.length; i++) {
			var picker = this.pickers[i];
			if (picker.index == index){
				picker.hour = value;
			}
		}
	},
	
	//set the minute for a picker
	setMinute: function(index, value){
		for (var i=0; i < this.pickers.length; i++) {
			var picker = this.pickers[i];
			if (picker.index == index){
				picker.minute = value;
			}
		}
	},
	
	//set the minute for a picker
	setAmPm: function(index, value){
		for (var i=0; i < this.pickers.length; i++) {
			var picker = this.pickers[i];
			if (picker.index == index){
				picker.amPm = value;
			}
		}
	},
	
	showAddButton: function(show){
		if (show){
			$(".add-button").removeClass("disabled");	
		}else{
			$(".add-button").addClass("disabled");
		}
	},
	
	//add event listeners/handlers
	addEventHandlers: function(){
		var that = this; //needed for scope
		
		$(".remove").click(function(event){
			var id = this.id.substring(this.id.indexOf("-") + 1);
			that.removePicker(id);
			that.save();
			event.preventDefault();
		});
		
		$(".day").unbind(); //need to unbind all listeners before refreshing this one
		
		$(".day").click(function(){
			//pass the element id
			that.toggleDaySelect(this.id);
			that.save();
		});
		
		$(".hour-select").change(function(){
			var index = this.id.substring(this.id.indexOf("-") + 1);
			that.setHour(index, this.value);
			that.save();
		});

		$(".minute-select").change(function(){
			var index = this.id.substring(this.id.indexOf("-") + 1);
			that.setMinute(index, this.value);
			that.save();
		});

		$(".am-pm-select").change(function(){
			var index = this.id.substring(this.id.indexOf("-") + 1);
			that.setAmPm(index, this.value);
			that.save();
		});
		
		$("#start").change(function(){
			that.save();
		});
		
		$("#end").change(function(){
			that.save();
		});
		
		$(".txt-duration").change(function(){
			that.save();
		});
	},
	
	save: function(){		
		// var startString = $("#start").val();
		// var endString = $("#end").val();
		var startString = $("#start").val();
		var endString = $("#end").val();
		
		var result = {
			start: startString,
			end: endString,
			patterns: []
		};

		for (var i=0; i < this.pickers.length; i++) {
			var p = this.pickers[i];
			var include = false; //should this picker be included in the JSON result? default to false
			var pattern = {
				days: [],
				time: p.hour + ":" + p.minute + " " + p.amPm
			};
			if (this.duration){
				pattern.duration = $("#duration-" + p.index).val();
			}
			for (var j=0; j < p.days.length; j++) {
				if (p.days[j].selected){
					pattern.days.push(p.days[j].value);
					include = true;
				}
			}
			if (include){
				result.patterns.push(pattern);	
			}
		}
		var strJson = JSON.stringify(result);
		strJson = strJson.replace(/\"/g,'\'');
		$("#" + this.element + "-result").val(strJson);
	}
};