<?php
    // Đường dẫn đến thư mục và tên tệp tin
    $directory = 'sensor_values';
    $filename = 'a_sensor_Fire_values.txt';
    $filepath = $directory . '/' . $filename;

    $lastLine = '';

    if (file_exists($filepath)) {
        $lines = file($filepath);
        $lastLine = trim(end($lines));
    }

    echo $lastLine;

?>

