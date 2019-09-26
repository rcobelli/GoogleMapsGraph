<?php

$ini = parse_ini_file("config.ini", true)["tg"];
$key = $ini['api_key'];

function getTime($time)
{
    global $origin;
    global $destination;
    global $algorithm;
    global $ch;
    global $key;

    $tomorrow = date('Y-m-d', strtotime("+1 days"));
    $time = strtotime($tomorrow . " " . $time . ":00:00 EST");

    curl_setopt($ch, CURLOPT_URL, "https://maps.googleapis.com/maps/api/directions/json?origin=$origin&destination=$destination&key=$key&departure_time=$time&traffic_model=$algorithm");

    $output = curl_exec($ch);
    $output = json_decode($output);
    return $output->routes[0]->legs[0]->duration_in_traffic->value;
}

$origin = urlencode($_POST['origin']);
$destination = urlencode($_POST['destination']);
$algorithm = $_POST['algorithm'];

// create curl resource
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

$output = array();

for ($i=0; $i < 24; $i++) {
    $output[$i] = number_format(getTime($i) / 3600, 2);
}

// close curl resource to free up system resources
curl_close($ch);

?>

<html>
<head>
    <style>
    body, html {
        background-color: #E5EEEF;
        color: #7A8C94;
    }
    </style>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.bundle.js"></script>
</head>
<body>
    <div class="container pt-3">
        <h3><?php echo $_POST['origin'] . " to " . $_POST['destination']?></h3>
        <canvas id="myChart" width="125" height="25"></canvas>
        <script>
            var ctx = document.getElementById('myChart');
            Chart.defaults.global.defaultFontColor = "#7A8C94";
            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['12 AM', '1 AM', '2 AM', '3 AM', '4 AM', '5 AM', '6 AM', '7 AM', '8 AM', '9 AM', '10 AM', '11 AM', '12 PM', '1 PM', '2 PM', '3 PM', '4 PM', '5 PM', '6 PM', '7 PM', '8 PM', '9 PM', '10 PM', '11 PM'],
                    datasets: [{
                        label: 'Travel Time (hrs)',
                        data: <?php echo json_encode($output); ?>,
                        backgroundColor: "#6C848D",
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }],
                        xAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: 'Departure Time'
                            }
                        }]
                    },
                }
            });
        </script>
        <p><a href="index.html">New Route</a></p>
    </div>
</body>
</html>
