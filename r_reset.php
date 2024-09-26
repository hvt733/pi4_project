
<?php



    // Đường dẫn đến tập lệnh Python
    $pythonScript1 = '/var/www/html/a_fire_sensor.py';
    $pythonScript2 = '/var/www/html/d_delete_Fire.py';
    $sensorFilePath = '/var/www/html/sensor_values/a_sensor_Fire_values.txt';

    // Chạy tập lệnh Python đầu tiên
    exec("python3 $pythonScript1 2>&1", $output1, $return_var1);

    // Hiển thị kết quả của tập lệnh đầu tiên
    if ($return_var1 === 0) {
        echo 'a_fire_sensor.py executed successfully.<br>';
    } else {
        echo 'Error executing a_fire_sensor.py: ' . implode("\n", $output1) . '<br>';
    }

    // Chạy tập lệnh Python thứ hai
    exec("python3 $pythonScript2 2>&1", $output2, $return_var2);

    // Hiển thị kết quả của tập lệnh thứ hai
    if ($return_var2 === 0) {
        echo 'd_delete_Fire.py executed successfully.<br>';
    } else {
        echo 'Error executing d_delete_Fire.py: ' . implode("\n", $output2) . '<br>';
    }



?>


