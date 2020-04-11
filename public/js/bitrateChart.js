$(document).ready(function()
{                           
	// create the empty chart with Chart.js
	var watchBitrateStarted = false;
	var ctx = document.getElementById('myChart');
	var myChart = new Chart(ctx, {
		type: 'line',
		data: {
			labels: [],
			datasets: [{
				label: 'rx',
				data: [],
				backgroundColor: [
					'rgba(105, 145, 205, 0.5)'
				],
			},
			{
				label: 'tx',
				        data: [],
				        backgroundColor: [
						'rgba(105, 205, 145, 0.5)'
	                                ],
			},
			]
		},
		options: {
			scales: {
				yAxes: [{
					ticks: {
						beginAtZero: true
					},
					scaleLabel: {
						display: true,
						labelString: 'kbps'
					}
				}]
			}
		}
	});

	// add data.rx and data.tx to chart existing data and set X value to the actual time
	function add_data(data) 
	{
	      var myLineChart = myChart;
	      var today = new Date();
	      var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
	      myLineChart.data.datasets[0].data.push(data.rx);
	      myLineChart.data.datasets[1].data.push(data.tx);
	      myLineChart.data.labels.push(time)
	      myLineChart.update();
	}
	
	//get the bitrate information from the terminal
	function getBitRate()
	{
		var action = 'getBitRate'
		$.ajax({
			//url: url active url,
			type: 'POST',
			data: JSON.stringify({ "action": action }),
			contentType: "application/json; charset=utf=8",
			dataType: 'json',
			success: function(data){
				displaylog(data.log);
				var dataObject = data.data;

				add_data(dataObject);


			},
			error: function(jqXHR, textStatus) {
				displaylog(textStatus);
			},
			timeout: 10000
		});
	}

	var bitRateInterval;

	// start and stop the ajax loop getting the bitrate
	$('body').on('click', "#getBitRate", function() {
		var stopContent = "STOP watch bitrate";
		if(watchBitrateStarted){
			clearInterval(bitRateInterval);	
			$("#getBitRate").text("Watch bitrate");
			watchBitrateStarted = false;
		} else {
			getBitRate();
			bitRateInterval = setInterval(getBitRate, 5000); //milisecond
			$("#getBitRate").text(stopContent);
			watchBitrateStarted = true;
		}
	});
});
