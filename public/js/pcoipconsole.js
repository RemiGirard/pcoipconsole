var logTable = $("#log");
var hostSelected = '';
var clientSelected = '';


$(document).ready(function()
{
	// trigger buttons associated to function
	operate($('.operation'),'');
	changeHost($('#changeHost'));
	updateInfoFromIp($('#updateInfoFromIp'), "true");
	updateIpFromHostname($('#updateIpFromHostname'), "true");
	updateConnectionInfo($('#updateConnectionInfo'), "false");
	updateConnectionInfo($('#updateAllConnectionInfo'), "true");
	selectTerminal('.terminal');
	orderTerminals($(".orderTerminals"));
	searchTerminal($(".titlerow"));
	orderConnection($(".orderConnection").parent());

	// trigger the update of lines when the window size change
	createLines($(".client"));
	$(window).resize(function() {
		createLines($(".client"));
	});

	// display order button when user mouse is hover the first row
	$('.firstRow').hover(					
		function () {
			$(this).find(".orderTerminals").css({"visibility":"visible"});
		}, 	
		function () {
			$(this).find(".orderTerminals").css({"visibility":"hidden"});
		}
	);

	//when the user type text in a input it update the terminals filtered
	$(document).on('input', 'input:text', function() {
		filterTerminals(this);
	});

	//close the filter when the arrow is clicked
	$('body').on('click', ".cancelInput", function() {
		var isClientTable = $(this).parent().closest('div').is("#client");
		closeInputs(isClientTable);	
		createLines($(".client"));
	});

	// default display : order client by label name and order host to simplify connections diplay
	// move the class .defaultterminalorder and .defaultconnectionorder to a different button to change this behavior
	$('.defaultterminalorder').trigger('click');
	$('.defaultconnectionorder').parent().trigger('click');
});

// when a terminal is clicked it sets the selected terminal, display it on the top corner and set class of the selected terminal to .selected to display it differently than others
function selectTerminal(trigger) {
	$('body').on('click', trigger, function()
	{
		var id = this.getAttribute('data-terminalId');
		var name = this.getAttribute('data-terminalName');
		var ip = this.getAttribute('data-terminalIp');
		var label = this.getAttribute('data-terminalLabel');
		if($(this).hasClass('client')){
			$('#selectedClient').attr('data-terminalId', id);
			$('#selectedClient').html("<p>Id: "+id+"</p><p>Name: "+name+"</p><p>Ip: "+ip+"</p><p>Label: "+label+"</p>");
			$('#selectedClient').addClass('selected');
			if(clientSelected){clientSelected.removeClass('selected');}
			clientSelected = $(this);
			clientSelected.addClass('selected');
		} else if($(this).hasClass('host')) {
			$('#selectedHost').attr('data-terminalId', id);
			$('#selectedHost').html("<p>Id: "+id+"</p><p>Name: "+name+"</p><p>Ip: "+ip+"</p><p>Label: "+label+"</p>");
			if(hostSelected){hostSelected.removeClass('selected');}
			hostSelected = $(this);
			$(this).addClass('selected');
		} 
	}
	);
}

// when an action button is triggered, this function send ajax request with the name of the action (specified by the id of the button) the terminal isidentified with its id in the url 
function operate(trigger,actionparameter) {
	trigger.click(function()
	{
		var action = $(this).attr('id');
		var fieldOutput = $(this).attr('data-fieldOutput');
		$.ajax({
			// url: url, // the active url is used
			type: "POST",
			data: JSON.stringify({ "action": action, "actionparameter": actionparameter }),
			contentType: "application/json; charset=utf-8",
			dataType: 'json',
			success: function(data){
				displaylog(data.log);
				if(data.data){$(fieldOutput).html(data.data);}
			},
			error: function(jqXHR,textStatus,errorThrown) {
		                displaylog(textStatus+' - '+jqXHR.status);
		        },
			timeout: 10000
		});
	}
	);
}

// this is a specific action where two terminals has to be selected ot be linked, the clientId is sent via the url and the host terminal is sent in json
function changeHost(trigger){
	trigger.click( function()
	{
		var action = 'changeHost'
		var clientId = $("#selectedClient").attr('data-terminalId');
		var hostId = $("#selectedHost").attr('data-terminalId');
		if(clientId && hostId){
			$.ajax({
				url: '/terminal/'+clientId,
				type: 'POST',
				data: JSON.stringify({ "action": action, "hostId": hostId }),
				contentType: "application/json; charset=utf=8",
				dataType: 'json',
				success: function(data){
					displaylog(data.log);
				},
				error: function(jqXHR, textStatus) {
					displaylog(textStatus);
				},
				timeout: 10000
			});
		}
	});
}

// update all information of the active terminal
// this is specific to the operate page because it has to fill all the fields of the page
function updateInfoFromIp(trigger, actionparameter){
	trigger.click( function()
	{
//		$('#getName').trigger('click');
//		$('#getLabel').trigger('click');
//		$('#getIp').trigger('click');
//		$('#getConnectedTo').trigger('click');
//		$('#getConnectionState').trigger('click');

                var action = $(this).attr('id');
		//var fieldOutput = $(this).attr('data-fieldOutput');
		$.ajax({
			// url: url, // the active url is used
			type: "POST",
			data: JSON.stringify({ "action": action, "allInfo": actionparameter }),
			contentType: "application/json; charset=utf-8",
			dataType: 'json',
			success: function(data){
				displaylog(data.log);

				var dataObject = $.parseJSON(data.data);
				
				$("#nameField").html(dataObject.name);
				$("#labelField").html(dataObject.label);
				$("#ipField").html(dataObject.ip);
				$("#roleField").html(dataObject.role);
				$("#connectionStateField").html(dataObject.connectionState);
				$("#pingField").html(dataObject.ping);
				$("#loggedField").html(dataObject.logged);
				$('#connectedToField').html(dataObject.connectedToLabel);
			},
			error: function(jqXHR,textStatus,errorThrown) {
				displaylog(textStatus+' - '+jqXHR.status);
			},
			timeout: 10000
		});

	});
}

// just trigger another button, maybe it can be removed because it's the exact same purpose of the button getIp
function updateIpFromHostname(trigger){
	trigger.click( function()
	{
		$('#getIp').trigger('click');
	});
}

// fill or append the log on the bottom of the page, it's used by ajax request 
function displaylog (content) {
	var d = jQuery.format.date(new Date(), 'dd/MM/yyyy HH:mm:ss');
	if(logTable.get(0).hasAttribute('data-empty')) {
		logTable.children('tbody').html(`<tr><td>${d}</td><td>${content}</td></tr>`);
		logTable.removeAttr('data-empty');
	} else {
		logTable.children('tbody').prepend(`<tr><td>${d}</td><td>${content}</td></tr>`);
	}	
}

// change value of a connection line from the center of an item and to the center of another item
function adjustLine(from, to, line){

	  var fT = from.offsetTop  + from.offsetHeight/2;
	  var tT = to.offsetTop 	 + to.offsetHeight/2;
	  var fL = from.offsetLeft + from.offsetWidth/2;
	  var tL = to.offsetLeft 	 + to.offsetWidth/2;
	  
	  var CA   = Math.abs(tT - fT);
	  var CO   = Math.abs(tL - fL);
	  var H    = Math.sqrt(CA*CA + CO*CO);
	  var ANG  = 180 / Math.PI * Math.acos( CA/H );

	  if(tT > fT){
        	var top  = (tT-fT)/2 + fT;
	  }else{
	        var top  = (fT-tT)/2 + tT;
	  }
	  if(tL > fL){
	        var left = (tL-fL)/2 + fL;
	  }else{
	        var left = (fL-tL)/2 + tL;
	  }

	  if(( fT < tT && fL < tL) || ( tT < fT && tL < fL) || (fT > tT && fL > tL) || (tT > fT && tL > fL)){
	        ANG *= -1;
	  }
	  top-= H/2;

	  line.style["-webkit-transform"] = 'rotate('+ ANG +'deg)';
	  line.style["-moz-transform"] = 'rotate('+ ANG +'deg)';
	  line.style["-ms-transform"] = 'rotate('+ ANG +'deg)';
	  line.style["-o-transform"] = 'rotate('+ ANG +'deg)';
	  line.style["-transform"] = 'rotate('+ ANG +'deg)';
	  line.style.top    = top+'px';
	  line.style.left   = left+'px';
	  line.style.height = H + 'px';
}

// called everytime lines have to be updated, it select everyline one by one hide them if they are not necessary or display it and call adjustLine() to adjust their position
function createLines(selector) {
	selector.each(function () {
		var currentTerminal = $(this);
		var targetId = false;
		targetId = currentTerminal.data("connectedto");
		var target = $('*[data-terminalid="'+targetId+'"]');
		var myLine = $("#line-"+currentTerminal.data("terminalid"));
		if(currentTerminal.data("connectedto")){
			if(myLine.attr("id") && target.data("terminalid")){
				if(currentTerminal.data('connectionstate') == "connected"){
					myLine.addClass('connected');	
				} else {
					myLine.removeClass('connected');
				}
			
				var from = currentTerminal.find(".dot");
				var to = target.find(".dot");	
				
				//alert("send>line:"+myLine.attr("id"));
				//alert("send>target:"+target.data("terminalid"));
				if(from.is(':visible') && to.is(':visible')){	
					myLine.show();
					adjustLine(from[ 0 ], to[ 0 ], myLine[ 0 ]);
				} else {
					myLine.hide();
				}
			} else {
				myLine.hide();
			}
		} else {
			myLine.hide();
		}

	});
}

// start the loop of update all terminals it send an array of all terminals to updateTerminalInfo()
function updateConnectionInfo(trigger, allInfo) {
	trigger.click( function(){
		var terminals = $('.terminal.client, .terminal.host');
		terminals.addClass("torefresh");
		$('#updateConnectionInfo').html('<div style="position:relative;width:30px;height:30px;left:42%;top:-5%;"><div id=ajaxloader></div></div>');

		updateTerminalInfo(terminals, allInfo, 0, terminals.length);	
		
	});
}

// recursive ajax which loops threw the array in parameters, the array has to be filled with terminal  jquery selectors
// then for each terminal replace all information
function updateTerminalInfo(terminals, allInfo, actualListNumber, terminalLength) {
			var action = 'updateInfoFromIp';
			var actionparameter = '';
			var actualTerminal = terminals[actualListNumber];
			//terminals = terminals.filter(function(elem){
			//	return elem != terminals[0];
			//});
			var id = $(actualTerminal).data('terminalid');
			//var fieldOutput = $(this).attr('data-fieldOutput');
			$.ajax({                                                       
				url: '/terminal/'+id, // the active url is used                                      
				type: "POST",                                                            
				data: JSON.stringify({ "action": action, "actionparameter": actionparameter, "allInfo" : allInfo }),
				contentType: "application/json; charset=utf-8",
				dataType: 'json',                     
				success: function(data){
						displaylog(data.log);
						if(testJSON(data.data)){

							var dataObject = $.parseJSON(data.data);
							var content = "";	
						

							

							if(dataObject.role == 'client'){
								var stateToFill = '';                                                  
								if(dataObject.ping == 'true'){                                              
									if(dataObject.logged == 'logged'){                                  
										if(dataObject.connectionState == 'connected'){               
											stateToFill = '<span class="dot connected"></span>';
										} else {                                                    
											stateToFill = '<span class="dot free"></span>';     
										}                                                           
									} else {                                                            
										stateToFill = '<span class="dot notlogged"></span>';        
									}                                                                   
								} else {                                                                    
								        stateToFill = '<span class="dot disconnected"></span>';             
								}                                                                           
								content = '<p class="client terminal" data-terminalid="'+dataObject.id+'" data-terminalname="'+(dataObject.name||"")+'" data-terminalip="'+(dataObject.ip||"")+'" data-connectedto="'+(dataObject.connectedTo.replace('.mikros.int','')||"")+'" data-connectionstate="'+dataObject.connectionState+'"><span>'+(dataObject.name.replace('.mikros.int','')||"")+'</span><span>'+(dataObject.label||"")+'</span><span>'+(dataObject.ip||"")+'</span><span>'+dataObject.connectedToLabel.replace('.mikros.int','')+'</span><span><a href="/terminal/'+dataObject.id+'">operate</a></span><span class="connectionState">'+stateToFill+'</span></p>';
								$(actualTerminal).replaceWith(content);
							} else if (dataObject.role == 'host') {
								var stateToFill = '';                                          
								if(dataObject.ping == 'true'){                 
									if(dataObject.logged == 'logged'){
										if(dataObject.connectionState == 'connected'){
											stateToFill = '<span class="dot connected"></span>';   
										} else { 
									        	stateToFill = '<span class="dot free"></span>';   
										}
									} else { 
									        stateToFill = '<span class="dot notlogged"></span>';      
									}
								} else {                                                       
									stateToFill = '<span class="dot disconnected"></span>';
								}                                                              
	
								content = '<p class="host terminal" data-terminalid="'+dataObject.id+'" data-terminalname="'+(dataObject.name||"")+'" data-terminalip="'+(dataObject.ip||"")+'" data-connectedto="'+(dataObject.connectedTo.replace('.mikros.int','')||"")+'" data-connectionstate="'+dataObject.connectionState+'"><span class="connectionState">'+stateToFill+'</span><span>'+((dataObject.name||"").replace('.mikros.int','')||"")+'</span><span>'+(dataObject.label||"")+'</span><span>'+(dataObject.ip||"")+'</span><span>'+dataObject.connectedToLabel.replace('.mikros.int','')+'</span><span><a href="/terminal/'+dataObject.id+'">operate</a></span>';
								$(actualTerminal).replaceWith(content);
							}
	
							createLines($(".client"));
						} else {
							
						}
				},
				error: function(jqXHR,textStatus,errorThrown) {
					displaylog(textStatus+' - '+jqXHR.status);
				},
				complete: function(){
					actualListNumber = actualListNumber + 1;
					if(terminalLength > actualListNumber){
						updateTerminalInfo(terminals, allInfo, actualListNumber, terminalLength);
					} else {
						$('#updateConnectionInfo').html('Update connection Info');
					}
				},

				timeout: 100000
			});                                                                                     

}

// change the order of terminals depending of the parameter data-orderby
function orderTerminals(trigger){
	trigger.click( function(){
		var orderBy = $(this).data("orderby");
		var elementsParent = $(this).parent().closest('div');
		var orderInverse = $(this).data("orderinverse");
		if(orderInverse == "false"){
			orderInverse = false;
		} else {
			orderInverse = true;
		}
		elementsParent.children().sort(function(a, b) {
			if($(a).attr(orderBy) == "firstrow"){
				return false;
			} else if ($(b).attr(orderBy) == "firstrow"){
				return true;
			} else if ($(a).attr(orderBy) === "" || $(a).attr(orderBy) === null){
				return orderInverse;
			} else if($(b).attr(orderBy) === "" || $(b).attr(orderBy) === null){
				return !orderInverse;
			} else {

				return $(a).attr(orderBy).localeCompare($(b).attr(orderBy));
			}
		}).appendTo(elementsParent);
		createLines($(".client"));
	});
}

// reset filters and create an input where the user can type text that will be used to filter terminals 
function searchTerminal(trigger){
	trigger.click( function(){
		if($(this).find("input").length){
		} else {
			var isClientTable = $(this).parent().closest('div').is("#client");
			closeInputs(isClientTable);
			$(this).html("<input type='text' size='4'></input><img class='cancelInput' src='/pictures/cross.png' width='15' alt='close'></img>");
			$(this).find(">:first-child").focus();
			createLines($(".client"));
		}
	});
}

//hide or show terminal depending of the content of an filter input
function filterTerminals(triggerred){
	var filterBy = $(triggerred).parent().data("filterby");
	var filterByColum = $(triggerred).parent().data("filterbycolumn");
	var content = $(triggerred).val();
	var elementsParent = $(triggerred).parent().closest('div');

	elementsParent.children().each( function (){
		if($(this).attr(filterBy).toLowerCase().includes(content.toLowerCase()) || $(this).attr(filterBy) == "firstrow"){
			$(this).show();

		} else {
			$(this).hide();
		}
	});
	createLines($(".client"));
}

// display all terminal when a filter is closed
function closeInputs(isClientTable) {
	var whichTable;
	if(isClientTable){
		whichTable = $("#client");
	} else {
		whichTable = $("#host");
	}
	whichTable.children().each( function(){
		$(this).show();
	});
	whichTable.find(".titlerow").each( function() {
		if($(this).find("input").length){
			$(this).html($(this).data("rowtitle"))

		}
	});
}

// change the order of terminals to simplify connection display
function orderConnection(trigger){
	trigger.click( function() {
		var hostOrClient = $(this).find("img").data("orderby");
		var elementsParent = $(hostOrClient);

		elementsParent.children().sort(function(a, b) {
			if($(a).hasClass("firstRow")){
				return false;
			} else if ($(b).hasClass("firstRow")){
				return true;
			} else if ($(a).attr("data-connectionstate") === "disconnected" || $(a).attr("data-connectionstate") === ""){
				return true;
			} else if($(b).attr("data-connectionstate") === "disconnected" || $(b).attr("data-connectionstate") === ""){
				return false;
			} else {
				var targetIdA = $(a).data("connectedto");
				var targetA = $('*[data-terminalid="'+targetIdA+'"]');
				var targetIdB = $(b).data("connectedto");
				var targetB = $('*[data-terminalid="'+targetIdB+'"]');
				if(targetA.index() == "-1"){
					return true;
				} else if (targetB.index() == "-1"){
					return false;
				} else {
					return targetA.index() > targetB.index();
				}
			}
		}).appendTo(elementsParent);
		createLines($(".client"));
	});
}

//function for validating json string
function testJSON(text){
	try{
		if (typeof text!=="string"){
			return false;
		}else{
			JSON.parse(text);
			return true;                            
		}
	}
	catch (error){
		return false;
	}
}

