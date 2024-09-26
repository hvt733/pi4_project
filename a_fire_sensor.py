# import RPi.GPIO as GPIO
# import time
# import os
# from datetime import datetime

# # Định nghĩa GPIO pin
# SENSOR = 16
# LED_PIN1 = 26
# LED_PIN2 = 21

# # Cấu hình GPIO
# GPIO.setmode(GPIO.BCM)
# GPIO.setup(SENSOR, GPIO.IN)
# GPIO.setup(LED_PIN1, GPIO.OUT)
# GPIO.setup(LED_PIN2, GPIO.OUT)
# GPIO.setwarnings(False)

# print("Ready")

# # Hàm ghi dữ liệu vào file
# def write_to_file(value, time):
#     directory = './sensor_values'
#     filename = 'a_sensor_Fire_values.txt'
#     filepath = os.path.join(directory, filename)

#     # Kiểm tra và tạo thư mục nếu chưa tồn tại
#     if not os.path.exists(directory):
#         os.makedirs(directory)
#         print(f"Directory '{directory}' created.")

#     # Ghi thời gian vào file
#     with open(filepath, 'a') as file:
#         file.write(f'{value} - {time}\n')

#     # Giữ lại chỉ hai dòng cuối cùng trong file
#     with open(filepath, 'r') as file:
#         lines = file.readlines()

#     if len(lines) > 2:
#         with open(filepath, 'w') as file:
#             file.writelines(lines[-2:])

# # Hàm đọc nhiệt độ từ file
# def read_temperature():
#     filepath = './sensor_values/b_sensor_TempC_values.txt'
#     if os.path.exists(filepath):
#         with open(filepath, 'r') as file:
#             lines = file.readlines()
#             if lines:
#                 last_line = lines[-1].strip()
#                 temp, time_logged = last_line.split(' - ')
#                 return float(temp), time_logged
#     return None, None

# # Hàm lấy thời gian hiện tại
# def cal_time():
#     now = datetime.now()
#     formatted_time = f"{now.hour:02}:{now.minute:02}:{now.second:02}"
#     return formatted_time

# # Biến để theo dõi trạng thái lửa
# fire_detected = False

# while True:
#     # Đọc giá trị từ cảm biến lửa
#     sensor_value = GPIO.input(SENSOR)
#     current_time = cal_time()

#     # Đọc giá trị nhiệt độ từ file
#     temperature, temp_time = read_temperature()

#     if sensor_value == GPIO.LOW:
#         if not fire_detected:
#             if temperature is not None and temperature > 29:
#                 print(f"Có lửa và nhiệt độ {temperature}°C vượt ngưỡng 29°C")
                
#                 # Điều kiện kiểm tra nhiệt độ để điều khiển đèn LED
#                 if temperature > 29 and temperature < 35:
#                     print(f"Nhiệt độ {temperature}°C: LED_PIN1 nháy.")
#                     while(1):  # Nháy LED trong khoảng thời gian (5 lần)
#                         GPIO.output(LED_PIN1, GPIO.HIGH)
#                         time.sleep(0.5)
#                         GPIO.output(LED_PIN1, GPIO.LOW)
#                         time.sleep(0.5)
#                 elif temperature >= 35:
#                     print(f"Nhiệt độ {temperature}°C: Cả LED_PIN1 và LED_PIN2 đều sáng.")
#                     GPIO.output(LED_PIN1, GPIO.HIGH)
#                     GPIO.output(LED_PIN2, GPIO.HIGH)
#                     # Ghi trạng thái lửa vào file
#                     write_to_file(1, current_time)
#                     fire_detected = True
                
                
#     else:
#         if fire_detected:
#             print("Lửa đã tắt")
#             GPIO.output(LED_PIN1, GPIO.LOW)  # Tắt đèn LED
#             GPIO.output(LED_PIN2, GPIO.LOW)  # Tắt đèn LED
#             write_to_file(0, current_time)  # Ghi dòng thứ hai khi lửa tắt
#             fire_detected = False
#             time.sleep(6)  # Thêm thời gian chờ 6 giây

#     time.sleep(2)  # Kiểm tra cảm biến thường xuyên hơn
import RPi.GPIO as GPIO
import time
import os
from datetime import datetime

# Definition of GPIO pin
SENSOR = 16

LED_PIN1 = 26
# LED_PIN2 = 21

# Configure GPIO pin mode
GPIO.setmode(GPIO.BCM)
GPIO.setup(SENSOR, GPIO.IN)
GPIO.setup(LED_PIN1, GPIO.OUT)
# GPIO.setup(LED_PIN2, GPIO.OUT)
GPIO.setwarnings(False)

print("Ready")

def write_to_file(value, time):
    directory = './sensor_values'
    filename = 'a_sensor_Fire_values.txt'
    filepath = os.path.join(directory, filename)
    
    # Kiểm tra và tạo thư mục nếu chưa tồn tại
    if not os.path.exists(directory):
        os.makedirs(directory)
        print(f"Directory '{directory}' created.")

    # Ghi thời gian vào file
    with open(filepath, 'a') as file:
        file.write(f'{value} - {time}\n')

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

# Biến để theo dõi trạng thái lửa
fire_detected = False
def blink_led_30_times():
    for i in range(30):
        GPIO.output(LED_PIN1, GPIO.HIGH)
        time.sleep(0.3)  
        GPIO.output(LED_PIN1, GPIO.LOW)
        time.sleep(0.3) 
while True:
    # Đọc giá trị từ cảm biến
    sensor_value = GPIO.input(SENSOR)
    current_time = cal_time()

    if sensor_value == GPIO.LOW:
        if not fire_detected:
            print(f"Fire detected at {current_time}, waiting 5 seconds for confirmation...")

            # Chờ 5 giây rồi kiểm tra lại
            time.sleep(5)
            sensor_value_after_wait = GPIO.input(SENSOR)
            
            if sensor_value_after_wait == GPIO.LOW:  # Nếu sau 5 giây vẫn có lửa
                print(f"Confirmed fire at {current_time}, blinking LED and logging.")
                write_to_file(1, current_time)
                # Nháy đèn 30 lần
                blink_led_30_times()
                
                # Ghi vào file
                
                
                fire_detected = True
    else:
        if fire_detected:
            print(f"Fire extinguished at {current_time}")
            GPIO.output(LED_PIN1, GPIO.LOW)  # Tắt đèn LED
            write_to_file(0, current_time)  # Ghi dòng khi lửa tắt
            fire_detected = False
            time.sleep(6)  # Thời gian chờ sau khi tắt lửa

    time.sleep(2)  # Kiểm tra cảm biến sau mỗi 2 giây