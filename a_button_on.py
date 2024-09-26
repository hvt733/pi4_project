import RPi.GPIO as GPIO
import time
import os
from datetime import datetime

# Definition of GPIO pin
button_pin = 19  # Chân GPIO 19 của Raspberry Pi
LED_PIN2 = 21

# Configure GPIO pin mode
GPIO.setmode(GPIO.BCM)

# Thiết lập chân GPIO làm đầu vào và kéo lên (pull-up)
GPIO.setup(button_pin, GPIO.IN, pull_up_down=GPIO.PUD_UP)
GPIO.setup(LED_PIN2, GPIO.OUT)
GPIO.setwarnings(False)

# Khởi tạo đèn LED ở trạng thái tắt
GPIO.output(LED_PIN2, GPIO.LOW)

print("Ready")

def write_to_file(value, current_time):
    directory = './sensor_values'
    filename = 'a_sensor_Fire_values.txt'
    filepath = os.path.join(directory, filename)

    # Kiểm tra và tạo thư mục nếu chưa tồn tại
    if not os.path.exists(directory):
        os.makedirs(directory)
        print(f"Directory '{directory}' created.")

    # Ghi thời gian vào file
    with open(filepath, 'a') as file:
        file.write(f'{value} - {current_time}\n')

    # Giữ lại chỉ hai dòng cuối cùng trong file
    with open(filepath, 'r') as file:
        lines = file.readlines()

    if len(lines) > 2:
        with open(filepath, 'w') as file:
            file.writelines(lines[-2:])

def cal_time():
    now = datetime.now()
    formatted_time = f"{now.hour:02}:{now.minute:02}:{now.second:02}"
    return formatted_time

def file_contains_value(value):
    directory = './sensor_values'
    filename = 'a_sensor_Fire_values.txt'
    filepath = os.path.join(directory, filename)
    
    # Kiểm tra xem file có chứa giá trị cụ thể không
    if os.path.exists(filepath):
        with open(filepath, 'r') as file:
            lines = file.readlines()
            for line in lines:
                if line.startswith(f"{value} -"):
                    return True
    return False

# Biến để theo dõi trạng thái lửa và trạng thái nút bấm trước đó
button_pressed = False
def blink_led_30_times():
    for i in range(30):
        GPIO.output(LED_PIN2, GPIO.HIGH)
        time.sleep(0.3) 
        GPIO.output(LED_PIN2, GPIO.LOW)
        time.sleep(0.3)  

while True:
    # Đọc trạng thái nút bấm (GPIO pin LOW khi nút được nhấn)
    button_state = GPIO.input(button_pin)
    current_time = cal_time()

    if button_state == GPIO.LOW and not button_pressed:  # Nếu nút được nhấn và chưa ghi nhận
        
        if not file_contains_value(1):  # Nếu file chưa chứa giá trị 1
            print("Fire")
            write_to_file(1, current_time)
            blink_led_30_times()  
            
            print("Clicked!")

        button_pressed = True  # Ghi nhận nút đã được nhấn
        time.sleep(0.5)  # Tránh hiện quá nhiều lần khi giữ nút bấm

    elif button_state == GPIO.HIGH and button_pressed:  # Khi nút không còn được nhấn
        button_pressed = False  # Reset trạng thái nút bấm

    time.sleep(0.1)  # Kiểm tra trạng thái nút sau mỗi 0.1 giây
