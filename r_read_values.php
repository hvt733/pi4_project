<?php
    date_default_timezone_set('Asia/Ho_Chi_Minh'); // Đặt múi giờ GMT+7

    // Đường dẫn đến thư mục chứa các file dữ liệu
    $directory = 'sensor_values';

    // Danh sách các file dữ liệu
    $filenames = [
        'b_sensor_TempC_values.txt',
        // 'b_sensor_TempF_values.txt',
        'c_sensor_Humi_values.txt',
        'a_sensor_Fire_values.txt',
    ];

    // Dữ liệu cảm biến
    $sensor_data = [
        'b_sensor_TempC_values.txt' => [
            'min' => PHP_FLOAT_MAX,
            'max' => PHP_FLOAT_MIN,
            'min_time' => '',
            'max_time' => '',
            'values' => [],
            'time_min' => '',
            'time_max' => ''
            // 'high_threshold' => 60,
            // 'low_threshold' => 0
        ],
        // 'b_sensor_TempF_values.txt' => [
        //     'min' => PHP_FLOAT_MAX,
        //     'max' => PHP_FLOAT_MIN,
        //     'min_time' => '',
        //     'max_time' => '',
        //     'values' => [],
        //     'time_min' => '',
        //     'time_max' => ''
        //     // 'high_threshold' => 100,
        //     // 'low_threshold' => 0
        // ],
        'c_sensor_Humi_values.txt' => [
            'min' => PHP_FLOAT_MAX,
            'max' => PHP_FLOAT_MIN,
            'min_time' => '',
            'max_time' => '',
            'values' => [],
            'time_min' => '',
            'time_max' => ''
            // 'high_threshold' => 100,
            // 'low_threshold' => 10
        ],
        'a_sensor_Fire_values.txt' => [
            'isFire' => '',
            'current' => '00:00:00',
            'time_detected' => '00:00:00',
            'duration' => '00:00:00'
        ],
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

    function calculateAvgDuringFire($fireStartTime, $fireEndTime, &$sensorTemp, $tolerance = 2) {
        $filePath1 = 'sensor_values/b_sensor_TempC_values.txt'; // Đường dẫn tới file nhiệt độ
        $filePath2 = 'sensor_values/a_sensor_Fire_values.txt'; // Đường dẫn tới file lửa
        
        // Khởi tạo mảng lưu kết quả và biến tổng nhiệt độ
        $results = [];
        $totalTemperature = 0;
        $count = 0; // Đếm số lần phát hiện nhiệt độ trong thời gian cháy
        $temp_min = PHP_FLOAT_MAX;
        $temp_max = PHP_FLOAT_MIN;
        $min_time = '';
        $max_time = '';
        
        // Kiểm tra sự tồn tại của file
        if (file_exists($filePath1) && file_exists($filePath2)) {
            // Đọc nội dung file
            $fileContent1 = file_get_contents($filePath1);
            $fileContent2 = file_get_contents($filePath2);
            
            // Tách các dòng của nội dung file
            $lines1 = explode("\n", $fileContent1);
            $lines2 = explode("\n", $fileContent2);
    
            // Tạo mảng để lưu thời gian từ file lửa
            $fireTimes = [];
            foreach ($lines2 as $line2) {
                if (empty($line2)) continue; // Bỏ qua dòng trống
                list($value, $time2) = explode(' - ', $line2);
                if (trim($value) == '1') {
                    $fireTimes[] = $time2;
                }
            }
            
            // Duyệt qua từng dòng của file nhiệt độ
            foreach ($lines1 as $line1) {
                if (empty($line1)) continue; // Bỏ qua dòng trống
                list($temp1, $time1) = explode(' - ', $line1);
                $temp1 = floatval($temp1);
                $time1 = trim($time1);
                
                // Tìm thời gian gần nhất trong khoảng cho phép
                $timeWithinTolerance = false;
                foreach ($fireTimes as $fireTime) {
                    $fireTimeSeconds = timeToSeconds($fireTime);
                    $time1Seconds = timeToSeconds($time1);
                    
                    if (abs($fireTimeSeconds - $time1Seconds) <= $tolerance) {
                        $timeWithinTolerance = true;
                        break;
                    }
                }
                
                if ($timeWithinTolerance) {
                    $totalTemperature += $temp1; 
                    $count++; // Tăng số lần phát hiện nhiệt độ
                    
                    // Cập nhật giá trị nhiệt độ thấp nhất và cao nhất
                    if ($temp1 < $temp_min) {
                        $temp_min = $temp1;
                        $min_time = $time1;
                    }
                    if ($temp1 > $temp_max) {
                        $temp_max = $temp1;
                        $max_time = $time1;
                    }
                }
            }
            
            if ($count > 0) {
                $avgTemperature = $totalTemperature / $count;
                $results['avg'] = $avgTemperature;
                $results['min'] = $temp_min;
                $results['max'] = $temp_max;
                $results['min_time'] = $min_time;
                $results['max_time'] = $max_time;
            } else {
                $results['avg'] = 0; // Không có nhiệt độ phát hiện
                $results['min'] = 0;
                $results['max'] = 0;
                $results['min_time'] = '';
                $results['max_time'] = '';
            }
    
        } else {
            $results['error'] = "2 File không tồn tại.";
        }
        
        return $results;
    }
    
    function read_and_process_file($filename, &$sensor) {
        global $directory;
        $filepath = $directory . '/' . $filename;

        if (file_exists($filepath)) {
            $data = file($filepath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            if (!empty($data)) {
                foreach ($data as $index => $line) {
                    list($value, $time) = explode(' - ', $line);
                    $value = floatval($value);
                    
                    if (strpos($filename, 'Fire') === false) {
                        // Xử lý cảm biến nhiệt độ/độ ẩm
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
                            'max_time' => $sensor['max_time']
                        ];
                    } else {
                        if ($value == 1) {
                            if (!$fireDetected) {
                                // Ghi nhận thời gian phát hiện lửa
                                $start_time = $time;
                                $sensor['date_detected'] = cal_date();
                                $sensor['time_detected'] = $start_time;
                        
                                $fireDetected = true; // Đã phát hiện lửa
                                $sensor['isFire'] = '1'; 
                            }
                        } elseif ($value == 0) {
                            // Ghi nhận thời gian kết thúc lửa
                            $end_time = $time;
                            
                            // Tính toán thời gian cháy
                            $currentSeconds = timeToSeconds($end_time);
                            $startSeconds = timeToSeconds($start_time);
                            $durationInSeconds = $currentSeconds - $startSeconds;
                            $sensor['duration'] = secondsToTime($durationInSeconds);
                        
                            // Tính toán các giá trị nhiệt độ trong thời gian cháy
                            $fireData = calculateAvgDuringFire($start_time, $end_time, $sensor_data['b_sensor_TempC_values.txt'],2);
                            $sensor['avg_temp_during_fire'] = $fireData['avg'];
                            $sensor['temp_min_in_fire'] = $fireData['min'];
                            $sensor['temp_max_in_fire'] = $fireData['max'];
                            $sensor['time_min_in_fire'] = $fireData['min_time'];
                            $sensor['time_max_in_fire'] = $fireData['max_time'];
                            
                            // Cập nhật trạng thái không còn lửa nữa
                            // $sensor['isFire'] = '1'; 
                            // $fireDetected = true;
                        }     
                        // echo 'Start Time' . $start_time;
                        // echo 'End Time' . $end_time;
                                    
                    }
                }

                $result = [
                    'min' => $sensor['min'] ?? null,
                    'max' => $sensor['max'] ?? null,
                    'average' => $sensor['average'] ?? null,
                    'min_time' => $sensor['min_time'] ?? null,
                    'max_time' => $sensor['max_time'] ?? null,
                    'date_detected' => $sensor['date_detected'] ?? null,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'duration' => $sensor['duration'] ?? '0',
                    'isFire' => $sensor['isFire'] ?? '0',
                    'avg_temp_during_fire' => $sensor['avg_temp_during_fire'] ?? null,
                    'temp_min_in_fire' => $sensor['temp_min_in_fire'] ?? null,
                    'temp_max_in_fire' => $sensor['temp_max_in_fire'] ?? null,
                    'time_min_in_fire' => $sensor['time_min_in_fire'] ?? null,
                    'time_max_in_fire' => $sensor['time_max_in_fire'] ?? null,
                ];
            } else {
                $result = ['error' => 'No data in file'];
            }

            return $result;
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
