<?php
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Đặt múi giờ GMT+7

$directory = 'sensor_values';
$filenames = [
    'a_sensor_Fire_values.txt',
];

$sensor_data = [
    'a_sensor_Fire_values.txt' => ['isFire' => '', 'current' => '00:00:00', 'time_detected' => '00:00:00', 'duration' => '00:00:00'],
];

function cal_date() {
    // Đặt múi giờ mặc định nếu cần
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    
    // Lấy thời gian hiện tại theo định dạng HH:MM:SS
    return date('H:i:s');
}

function timeToSeconds($time) {
    list($hours, $minutes, $seconds) = explode(':', $time);
    return $hours * 3600 + $minutes * 60 + $seconds;
}

function secondsToTime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
}

// Hàm đọc và xử lý file
function read_and_process_file($filename, &$sensor) {
    global $directory;
    $filepath = $directory . '/' . $filename;

    if (file_exists($filepath)) {
        $data = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!empty($data)) {
            $fireDetected = false; // Biến để kiểm tra nếu có phát hiện lửa
            foreach ($data as $line) {
                list($value, $time) = explode(' - ', $line);
                $value = floatval($value);
                $sensor['values'][] = $value;

                // Kiểm tra nếu giá trị phát hiện lửa (ví dụ: 1)
                if ($value == 1) {
                    // Lấy thời gian hiện tại trước khi kiểm tra giá trị lửa
                    $currentTime = cal_date();
                    
                    // Chuyển đổi thời gian hiện tại và thời gian phát hiện lửa sang giây kể từ nửa đêm
                    $currentSeconds = timeToSeconds($currentTime);
                    $detectedSeconds = timeToSeconds($time); // Thời gian phát hiện lửa từ file
                
                    // Tính thời gian duration
                    $durationInSeconds = $currentSeconds - $detectedSeconds;

                    // Đảm bảo rằng duration không âm
                    $durationInSeconds = max(0, $durationInSeconds);

                    if (!$fireDetected) {
                        $sensor['current'] = $currentTime; // Cập nhật thời gian hiện tại khi phát hiện lửa lần đầu
                        $sensor['time_detected'] = $time; // Thời gian phát hiện lửa từ file
                        $fireDetected = true; // Đặt biến để không cập nhật lại
                        $sensor['duration'] = secondsToTime($durationInSeconds); // Chuyển đổi duration thành định dạng HH:MM:SS
                    }
                }
                
            }
            $sensor['isFire'] = in_array(1, $sensor['values']) ? '1' : '0';
            return $sensor;
        } else {
            return ['error' => 'No data in file'];
        }
    } else {
        return ['error' => 'File not found'];
    }
}

$result = [];

foreach ($filenames as $filename) {
    $data = read_and_process_file($filename, $sensor_data[$filename]);

    if (isset($data['error'])) {
        $result[$filename] = ['error' => $data['error']];
    } else {
        $result[$filename] = $data;
    }
}

header('Content-Type: application/json');
echo json_encode($result);
?>
