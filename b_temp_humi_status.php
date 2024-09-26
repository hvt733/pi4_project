<?php
    $directory = 'sensor_values'; 
    $filenames = [
        'b_sensor_TempC_values.txt',
        // 'b_sensor_TempF_values.txt',
        'c_sensor_Humi_values.txt'
    ];

    $results = [];

    foreach ($filenames as $filename) {
        $filepath = $directory . '/' . $filename;

        if (file_exists($filepath)) {
            $lines = file($filepath);

            $lastLine = trim(end($lines));

            $results[$filename] = $lastLine;
        } else {
            $results[$filename] = 'File does not exist.';
        }
    }

    echo json_encode($results);
?>