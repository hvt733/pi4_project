# d_delete_Fire.py
file_path = '/var/www/html/sensor_values/a_sensor_Fire_values.txt'

try:
    with open(file_path, 'w') as file:
        # Thực hiện các thao tác ghi vào file
        file.write('')
except PermissionError as e:
    print(f"Permission error: {e}")
except Exception as e:
    print(f"An error occurred: {e}")
