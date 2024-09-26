<?php
    // Đường dẫn đến tập lệnh Python
    $pythonScript2 = '/var/www/html/delete_Fire.py';

    // Chạy tập lệnh Python thứ hai
    exec("python3 $pythonScript2 2>&1", $output2, $return_var2);

    // Hiển thị kết quả của tập lệnh thứ hai
    if ($return_var2 === 0) {
        echo 'delete_Fire.py executed successfully.<br>';
    } else {
        echo 'Error executing delete_Fire.py: ' . implode("\n", $output2) . '<br>';
    }

    // Hiển thị lỗi (nếu có)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Thông báo thành công của PHP script
    echo "PHP script executed successfully.";
?>
