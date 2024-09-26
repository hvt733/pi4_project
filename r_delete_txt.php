<?php
    // Đường dẫn đến các tệp tin
    $Fire_txt  = '/var/www/html/sensor_values/a_sensor_Fire_values.py';

    // Xóa các tệp tin
    $files = [ $Fire_txt];

    foreach ($files as $file) {
        if (file_exists($file)) {
            if (unlink($file)) {
                echo "Successfully deleted $file.<br>";
            } else {
                echo "Failed to delete $file.<br>";
            }
        } else {
            echo "$file does not exist.<br>";
        }
    }
?>
