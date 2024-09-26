<?php
date_default_timezone_set('Asia/Ho_Chi_Minh'); // Đặt múi giờ GMT+7

// Đường dẫn đến thư mục chứa các file dữ liệu
$directory = 'sensor_values';

// Danh sách các file dữ liệu
$filenames = [
    'b_sensor_TempC_values.txt',
    'b_sensor_TempF_values.txt',
    'c_sensor_Humi_values.txt',
    'a_sensor_Fire_values.txt',
];

// Dữ liệu cảm biến
$sensor_data = [
    'b_sensor_TempC_values.txt' => ['min' => PHP_FLOAT_MAX, 'max' => PHP_FLOAT_MIN, 'min_time' => '', 'max_time' => '', 'values' => [], 'time_min' =>'', 'time_max'=>''],
    'b_sensor_TempF_values.txt' => ['min' => PHP_FLOAT_MAX, 'max' => PHP_FLOAT_MIN, 'min_time' => '', 'max_time' => '', 'values' => [], 'time_min' =>'', 'time_max'=>''],
    'c_sensor_Humi_values.txt' => ['min' => PHP_FLOAT_MAX, 'max' => PHP_FLOAT_MIN, 'min_time' => '', 'max_time' => '', 'values' => [], 'time_min' =>'', 'time_max'=>''],
    'a_sensor_Fire_values.txt' => ['isFire' => '', 'current' => '00:00:00', 'time_detected' => '00:00:00', 'duration' => '00:00:00'],
];

// Hàm cập nhật giá trị nhỏ nhất
function updateMin($newValue, $time, &$sensor) {
    if ($newValue < $sensor['min']) {
        $sensor['min'] = $newValue;
        $sensor['min_time'] = $time;
        $sensor['time_min'] = $time;
    }
}

// Hàm cập nhật giá trị lớn nhất
function updateMax($newValue, $time, &$sensor) {
    if ($newValue > $sensor['max']) {
        $sensor['max'] = $newValue;
        $sensor['max_time'] = $time;
        $sensor['time_max'] = $time;
    }
}

// Hàm chuyển đổi thời gian sang giây
function timeToSeconds($time) {
    list($hours, $minutes, $seconds) = explode(':', $time);
    return $hours * 3600 + $minutes * 60 + $seconds;
}

// Hàm chuyển đổi giây sang thời gian
function secondsToTime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
}

// Hàm lấy thời gian hiện tại theo định dạng HH:MM:SS
function cal_time() {
    return date('H:i:s');
}
function cal_date() {
    return date('Y-m-d');
}
// Hàm đọc và xử lý file dữ liệu
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

                if (strpos($filename, 'Fire') === false) { // Xử lý các cảm biến không phải lửa
                    $sensor['values'][] = $value;

                    updateMin($value, $time, $sensor);
                    updateMax($value, $time, $sensor);

                    $average = array_sum($sensor['values']) / count($sensor['values']);
                    $sensor['average'] = $average;

                    $result = [
                        'min' => $sensor['min'],
                        'max' => $sensor['max'],
                        'average' => $average,
                        'min_time' => $sensor['min_time'],
                        'max_time' => $sensor['max_time'],
                        'time_min' => $sensor['time_min'],
                        'time_max' => $sensor['time_max']
                    ];
                } else { // Xử lý cảm biến lửa
                    $sensor['values'][] = $value;
                    if ($value == 1) {
                        $date = cal_date();
                        $currentTime = cal_time();
                        $currentSeconds = timeToSeconds($currentTime);
                        $detectedSeconds = timeToSeconds($time);
                        $durationInSeconds = $currentSeconds - $detectedSeconds;
                        $durationInSeconds = max(0, $durationInSeconds);

                        if (!$fireDetected) {
                            $sensor['date_detected'] = $date;
                            $sensor['current'] = $currentTime;
                            $sensor['time_detected'] = $time;
                            $fireDetected = true;
                            $sensor['duration'] = secondsToTime($durationInSeconds);
                        }
                    }
                    $sensor['isFire'] = in_array(1, $sensor['values']) ? '1' : '0';
                    $result = $sensor;
                }
            }

            return $result;
        } else {
            return ['error' => 'No data in file'];
        }
    } else {
        return ['error' => 'File not found'];
    }
}

// Khởi tạo kết quả
$result = [];
$min_time_all = PHP_INT_MAX;
$max_time_all = PHP_INT_MIN;

// Đọc và xử lý tất cả các file dữ liệu
foreach ($filenames as $filename) {
    $data = read_and_process_file($filename, $sensor_data[$filename]);

    if (isset($data['error'])) {
        $result[$filename] = ['error' => $data['error']];
    } else {
        $result[$filename] = $data;

        if ($filename !== 'a_sensor_Fire_values.txt') { // Cập nhật thời gian nhỏ nhất và lớn nhất cho cảm biến khác lửa
            if (!empty($sensor_data[$filename]['time_min']) && strtotime($sensor_data[$filename]['time_min']) < $min_time_all) {
                $min_time_all = strtotime($sensor_data[$filename]['time_min']);
            }
            if (!empty($sensor_data[$filename]['time_max']) && strtotime($sensor_data[$filename]['time_max']) > $max_time_all) {
                $max_time_all = strtotime($sensor_data[$filename]['time_max']);
            }
        }
    }
}

// Chuyển đổi thời gian nhỏ nhất và lớn nhất sang định dạng Y-m-d H:i:s
$min_time_all = date('Y-m-d H:i:s', $min_time_all);
$max_time_all = date('Y-m-d H:i:s', $max_time_all);

$result['min_time_all'] = $min_time_all;
$result['max_time_all'] = $max_time_all;

// Đưa kết quả ra định dạng JSON
header('Content-Type: application/json');
echo json_encode($result);
?>
