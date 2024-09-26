import RPi.GPIO as GPIO
import time
import os
from datetime import datetime

# Definition of GPIO pin
button_pin = 13  
# LED_PIN1 = 26
LED_PIN2 = 21

# Configure GPIO pin mode
GPIO.setmode(GPIO.BCM)

# Thiết lập chân GPIO làm đầu vào và kéo lên (pull-up)
GPIO.setup(button_pin, GPIO.IN, pull_up_down=GPIO.PUD_UP)
# GPIO.setup(LED_PIN1, GPIO.OUT)
GPIO.setup(LED_PIN2, GPIO.OUT)
GPIO.setwarnings(False)

# Khởi tạo đèn LED ở trạng thái tắt
# GPIO.output(LED_PIN1, GPIO.LOW)
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

    # Kiểm tra nếu file không rỗng trước khi ghi
    if os.path.getsize(filepath) > 0:
        with open(filepath, 'a') as file:
            file.write(f'{value} - {current_time}\n')

        # Giữ lại chỉ hai dòng cuối cùng trong file
        with open(filepath, 'r') as file:
            lines = file.readlines()

        if len(lines) > 2:
            with open(filepath, 'w') as file:
                file.writelines(lines[-2:])
    else:
        print("File is empty. No data written.")

def cal_time():
    now = datetime.now()
    formatted_time = f"{now.hour:02}:{now.minute:02}:{now.second:02}"
    return formatted_time

# Biến để theo dõi trạng thái lửa và trạng thái nút bấm trước đó
fire_detected = False
button_pressed = False

while True:
    # Đọc trạng thái nút bấm (GPIO pin LOW khi nút được nhấn)
    button_state = GPIO.input(button_pin)
    current_time = cal_time()

    # In ra trạng thái nút bấm để kiểm tra
    # print(f"Button state: {button_state}")

    if button_state == GPIO.LOW and not button_pressed:  # Nếu nút được nhấn và chưa ghi nhận
        print("Nút bấm đã được nhấn!")
        # if not fire_detected:
        print("Có lửa trong 10 giây liên tục")
        # GPIO.output(LED_PIN1, GPIO.LOW)  # Bật đèn LED
        GPIO.output(LED_PIN2, GPIO.LOW)  # Bật đèn LED
        write_to_file(0, current_time)  # Ghi dòng đầu tiên khi phát hiện lửa
        # fire_detected = True

        # button_pressed = True  # Ghi nhận nút đã được nhấn
        time.sleep(0.5)  # Tránh hiện quá nhiều lần khi giữ nút bấm

    # elif button_state == GPIO.HIGH and button_pressed:  # Khi nút không còn được nhấn
    #     button_pressed = False  # Reset trạng thái nút bấm

    time.sleep(0.1)  # Kiểm tra trạng thái nút sau mỗi 0.1 giây
