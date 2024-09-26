<?php
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Đặt múi giờ GMT+7
$logFile = '/var/www/html/log_fire.txt';

// Nhận dữ liệu JSON từ yêu cầu POST
$data = json_decode(file_get_contents('php://input'), true);

function cal_time() {
    return date('H:i:s');
}

// Kiểm tra lỗi JSON và dữ liệu cần thiết
if (json_last_error() === JSON_ERROR_NONE && isset($data['start_time']) && isset($data['isFire'])) {
    $startTime = $data['start_time'];
    $isFire = $data['isFire'];
    $endTime = cal_time();

    $logEntry = "Start Time: $startTime, End Time: $endTime, Is Fire: $isFire\n";
    
    if ($file = fopen($logFile, 'a')) {
        // Đọc toàn bộ nội dung file để kiểm tra nếu startTime đã tồn tại
        $fileContent = file_get_contents($logFile);
        if (strpos($fileContent, $startTime) === false) {
            // Nếu startTime chưa tồn tại, ghi log mới
            fwrite($file, $logEntry);
        }

        fclose($file);

        echo json_encode(['status' => 'success', 'message' => 'Log updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to open log file.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
}
?>
