function updateFirebaseData() {
    fetch('/html/dtemp_humi_status.php')  // Đảm bảo đường dẫn chính xác
        .then(response => response.json())
        .then(data => {
            const tempC = parseFloat(data.temp);   // Đọc dữ liệu nhiệt độ
            const humi = parseFloat(data.humi);    // Đọc dữ liệu độ ẩm
            const fire = data.fire === '1';        // Đọc dữ liệu trạng thái lửa
            const now = new Date().toISOString();

            // Điều kiện lấy giá trị
            if (tempC < tempC_low) {
                tempC_low = tempC;
                tempC_low_time = now;
            }
            if (tempC > tempC_high) {
                tempC_high = tempC;
                tempC_high_time = now;
            }
            if (humi < humi_low) {
                humi_low = humi;
                humi_low_time = now;
            }
            if (humi > humi_high) {
                humi_high = humi;
                humi_high_time = now;
            }

            // Nhập dữ liệu vào database
            set(ref(db, 'Temperature'), {
                low_temp: tempC_low,
                low_temp_time: tempC_low_time,
                high_temp: tempC_high,
                high_temp_time: tempC_high_time,
            });

            set(ref(db, 'Humidity'), {
                low_humi: humi_low,
                low_humi_time: humi_low_time,
                high_humi: humi_high,
                high_humi_time: humi_high_time,
            });

            set(ref(db, 'Fire'), {
                detected: fire,
                timestamp: now
            });
        })
        .catch(error => console.error('Error:', error));
}
