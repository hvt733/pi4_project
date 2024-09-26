<?php
// Đường dẫn đến file TXT
$file_path = '/var/www/html/sensor_values/a_sensor_Fire_values.txt';

// Đường dẫn đến file Python
$python_script_path = '/var/www/html/d_delete_Fire.py';

// Hàm đọc file TXT và xử lý dữ liệu
function checkFire($file_path, $python_script_path) {
    $response = array('error' => null, 'start_time' => null, 'end_time' => null);

    // Kiểm tra nếu file tồn tại
    if (file_exists($file_path)) {
        // Đọc tất cả các dòng trong file
        $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Kiểm tra xem có ít nhất 1 dòng hay không
        if (count($lines) >= 1) {
            // Lấy dòng đầu tiên
            $first_line = $lines[0];
            list($first_status, $first_time) = explode(' - ', $first_line);

            // Nếu dòng đầu tiên là "1 - time", xác nhận có lửa ngay lập tức
            if ($first_status == '1') {
                $response['start_time'] = $first_time;
                $response['message'] = "Fire started at: $first_time";

                // Kiểm tra nếu có dòng thứ hai
                if (count($lines) >= 2) {
                    $second_line = $lines[1];
                    list($second_status, $second_time) = explode(' - ', $second_line);

                    // Nếu dòng thứ hai là "0 - time", ghi nhận end_time
                    if ($second_status == '0') {
                        $response['end_time'] = $second_time;
                        $response['message'] .= "\nFire ended at: $second_time";
                        sleep(3);
                        // Chạy file Python
                        exec("python3 $python_script_path", $output, $return_var);
                        if ($return_var === 0) {
                            $response['message'] .= "\nPython script executed successfully.";
                        } else {
                            $response['error'] = "Failed to execute Python script.";
                        }

                        // Xóa nội dung file sau khi thực hiện script Python
                        file_put_contents($file_path, '');
                    }
                } else {
                    // Nếu chưa có dòng thứ hai, chỉ hiển thị start_time
                    $response['end_time'] = '00:00:00'; // Đặt mặc định nếu chưa có dòng 2
                }
            } else {
                // Nếu dòng đầu không phải là "1", báo lỗi
                $response['error'] = "No fire detected in the first line.";
            }
        } else {
            $response['error'] = "File does not contain enough data.";
        }
    } else {
        $response['error'] = "File not found.";
    }

    // Trả về dữ liệu dưới dạng JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}

// Gọi hàm để kiểm tra file
checkFire($file_path, $python_script_path);
?>
