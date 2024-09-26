# import time
# import board
# import adafruit_dht
# import os
# from datetime import datetime

# # Định nghĩa chân GPIO
# dhtDevice = adafruit_dht.DHT11(board.D20)
# LED_PIN1 = 26
# LED_PIN2 = 21
# def write_to_file(value, time, filename, max_lines=86400):
#     directory = './sensor_values'
#     filepath = os.path.join(directory, filename)

#     # Kiểm tra và tạo thư mục nếu chưa tồn tại
#     if not os.path.exists(directory):
#         os.makedirs(directory)
#         print(f"Directory '{directory}' created.")
    
#     # Ghi giá trị vào file
#     with open(filepath, 'a') as file:
#         file.write(f'{value} - {time}\n')

#     # Đọc và kiểm tra số dòng trong file
#     with open(filepath, 'r') as file:
#         lines = file.readlines()

#     # Nếu số dòng vượt quá max_lines, giữ lại max_lines dòng cuối
#     if len(lines) > max_lines:
#         with open(filepath, 'w') as file:
#             file.writelines(lines[-max_lines:])

# def cal_time():
#     now = datetime.now()
#     formatted_time = f"{now.hour:02}:{now.minute:02}:{now.second:02}"
#     return formatted_time

# def cal_date():
#     now = datetime.now()
#     formatted_date = f"{now.day:02}/{now.month:02}/{now.year}"
#     return formatted_date

# while True:
#     try:
#         # Thử đọc dữ liệu từ cảm biến
#         for _ in range(3):  # Thử tối đa 3 lần
#             temperature_c = dhtDevice.temperature
#             humidity = dhtDevice.humidity
            
#             if humidity is not None and temperature_c is not None:
#                 break  # Thoát vòng lặp nếu đọc thành công
#             time.sleep(2)  # Ngủ thêm thời gian trước khi thử lại

#         if humidity is not None and temperature_c is not None:
#             temperature_f = (temperature_c * 9 / 5) + 32

#             print(f"Humidity: {humidity:.1f}%")
#             write_to_file(humidity, cal_time(), 'c_sensor_Humi_values.txt')
#             print(f"Temperature: {temperature_c:.1f}°C / {temperature_f:.1f}°F")
#             write_to_file(temperature_c, cal_time(), 'b_sensor_TempC_values.txt')
#             write_to_file(temperature_f, cal_time(), 'b_sensor_TempF_values.txt')
#         else:
#             print("Failed to retrieve data from sensor after multiple attempts.")

#     except RuntimeError as error:
#         # Lỗi đọc cảm biến thường xảy ra, bỏ qua lỗi và tiếp tục
#         print(f"RuntimeError: {error.args[0]}")
#         time.sleep(2)  # Ngủ thêm thời gian trước khi tiếp tục
    
#     except Exception as error:
#         dhtDevice.exit()
#         raise error

#     time.sleep(2)  # Đọc dữ liệu mỗi 2 giây
import time
import board
import adafruit_dht
import os
import RPi.GPIO as GPIO
from datetime import datetime

# Định nghĩa chân GPIO
dhtDevice = adafruit_dht.DHT11(board.D20)
LED_PIN1 = 26  # Định nghĩa chân GPIO cho LED 1
LED_PIN2 = 21  # Định nghĩa chân GPIO cho LED 2

# Cấu hình GPIO
GPIO.setmode(GPIO.BCM)
GPIO.setup(LED_PIN1, GPIO.OUT)
GPIO.setup(LED_PIN2, GPIO.OUT)

fire_detected = False
fire_time_recorded = False  # Biến kiểm soát ghi thời gian phát hiện lửa lần đầu
fire_time_recorded2 = False 
# Hàm ghi giá trị vào file
def write_to_file(value, time, filename, max_lines=86400):
    directory = './sensor_values'
    filepath = os.path.join(directory, filename)

    if not os.path.exists(directory):
        os.makedirs(directory)
    
    with open(filepath, 'a') as file:
        file.write(f'{value} - {time}\n')

    with open(filepath, 'r') as file:
        lines = file.readlines()

    if len(lines) > max_lines:
        with open(filepath, 'w') as file:
            file.writelines(lines[-max_lines:])

def write_to_file_once(value, current_time):
    global fire_time_recorded,fire_time_recorded2  # Sử dụng biến toàn cục
    if not fire_time_recorded:  # Chỉ ghi lần đầu khi lửa được phát hiện
        directory = './sensor_values'
        filename = 'a_sensor_Fire_values.txt'
        filepath = os.path.join(directory, filename)

        if not os.path.exists(directory):
            os.makedirs(directory)

        with open(filepath, 'a') as file:
            file.write(f'{value} - {current_time}\n')

        # Giữ lại chỉ hai dòng cuối cùng trong file
        with open(filepath, 'r') as file:
            lines = file.readlines()

        
        if len(lines) > 2:
            with open(filepath, 'w') as file:
                file.writelines(lines[-2:])

        fire_time_recorded = True  # Đánh dấu là đã ghi thời gian phát hiện lửa
    else:
        fire_time_recorded2 = True  # Đánh dấu là đã ghi thời gian phát hiện lửa
def cal_time():
    now = datetime.now()
    formatted_time = f"{now.hour:02}:{now.minute:02}:{now.second:02}"
    return formatted_time

def cal_date():
    now = datetime.now()
    formatted_date = f"{now.day:02}/{now.month:02}/{now.year}"
    return formatted_date

# Hàm điều khiển LED dựa trên điều kiện nhiệt độ và độ ẩm
def control_leds(temperature, humidity, current_time):
    global fire_detected, fire_time_recorded  # Sử dụng biến toàn cục
    if temperature > 35 or humidity < 20:
        fire_detected = True
        GPIO.output(LED_PIN1, GPIO.HIGH)  # Bật LED 1
        GPIO.output(LED_PIN2, GPIO.HIGH)  # Bật LED 2
        print("LEDs ON: Temperature > 35°C or Humidity < 60%")
        write_to_file_once(1, current_time)  # Ghi thời gian phát hiện lửa lần đầu
        
    else:
        if temperature > 35 or humidity < 20:
            GPIO.output(LED_PIN1, GPIO.HIGH)  # Bật LED 1
            GPIO.output(LED_PIN2, GPIO.HIGH)  # Bật LED 2
        else:
            fire_detected = True
            GPIO.output(LED_PIN1, GPIO.LOW)   # Tắt LED 1
            GPIO.output(LED_PIN2, GPIO.LOW)   # Tắt LED 2
            print("LEDs OFF: Temperature <= 35°C and Humidity >= 60%")
            write_to_file_once(0, current_time)  # Ghi thời gian ngừng phát hiện lửa
try:
    while True:
        current_time = cal_time()
        try:
            # Thử đọc dữ liệu từ cảm biến
            for _ in range(3):  # Thử tối đa 3 lần
                temperature_c = dhtDevice.temperature
                humidity = dhtDevice.humidity
                
                if humidity is not None and temperature_c is not None:
                    break  # Thoát vòng lặp nếu đọc thành công
                time.sleep(2)  # Ngủ thêm thời gian trước khi thử lại

            if humidity is not None and temperature_c is not None:
                temperature_f = (temperature_c * 9 / 5) + 32

                print(f"Humidity: {humidity:.1f}%")
                write_to_file(humidity, current_time, 'c_sensor_Humi_values.txt')
                print(f"Temperature: {temperature_c:.1f}°C / {temperature_f:.1f}°F")
                write_to_file(temperature_c, current_time, 'b_sensor_TempC_values.txt')
                # write_to_file(temperature_f, current_time, 'b_sensor_TempF_values.txt')
                
                # Kiểm tra điều kiện và điều khiển LED
                control_leds(temperature_c, humidity, current_time)
            else:
                print("Failed to retrieve data from sensor after multiple attempts.")

        except RuntimeError as error:
            print(f"RuntimeError: {error.args[0]}")
            time.sleep(2)
        
        time.sleep(2)  # Đọc dữ liệu mỗi 2 giây
finally:
    dhtDevice.exit()
    GPIO.cleanup()  # Đặt lại các chân GPIO khi kết thúc chương trình
