import RPi.GPIO as GPIO
import time
import subprocess

# Định nghĩa chân GPIO
button_pin = 14  # Chân GPIO 14 của Raspberry Pi
LED_PIN2 = 21

# Cấu hình chế độ pin GPIO
GPIO.setmode(GPIO.BCM)
GPIO.setup(button_pin, GPIO.IN, pull_up_down=GPIO.PUD_UP)
GPIO.setup(LED_PIN2, GPIO.OUT)
GPIO.setwarnings(False)

# Khởi tạo đèn LED ở trạng thái tắt
GPIO.output(LED_PIN2, GPIO.LOW)

# Đường dẫn đến các file
file1 = "/var/www/html/a_fire_sensor.py"
file2 = "/var/www/html/b_temp_humi_sensor.py"
file3 = "/var/www/html/a_button_on.py"
file4 = "/var/www/html/a_button_off.py"

print("Ready")

try:
    while True:
        button_state = GPIO.input(button_pin)

        if button_state == GPIO.LOW:  # Nếu nút được nhấn
            # Kích hoạt môi trường ảo và chạy các script
            subprocess.run(f"source /var/www/html/myenv/bin/activate && python3 {file1} & python3 {file2} & python3 {file3} & python3 {file4}", shell=True, check=True)
            GPIO.output(LED_PIN2, GPIO.HIGH)  # Bật đèn LED
            time.sleep(0.5)  # Tránh hiện quá nhiều lần khi giữ nút bấm

        elif button_state == GPIO.HIGH:
            GPIO.output(LED_PIN2, GPIO.LOW)  # Tắt đèn LED

        time.sleep(0.1)  # Kiểm tra trạng thái nút sau mỗi 0.1 giây

except KeyboardInterrupt:
    pass
finally:
    GPIO.cleanup()
