/* Visit https://firebase.google.com/docs/database/security to learn more about security rules. */
python a_fire_sensor.py & python b_temp_humi_sensor.py


sudo apt-get update
sudo apt-get upgrade

source /var/www/html/myenv/bin/activate
source /var/www/html/myenv/bin/deactivate

sudo python3 a_fire_sensor.py & python3 b_temp_humi_sensor.py
sudo chown admin:admin /var/www/html/sensor_values
python3 a_button_off.py
python3 a_button_on.py
python3 /var/www/html/d_delete_Fire.py

php /var/www/html/turn_on_Fire.php
php /var/www/html/turn_off_Fire.php


window.onload = function()

source /var/www/html/myenv/bin/activate & sudo python3 a_fire_sensor.py & python3 b_temp_humi_sensor.py & python3 a_button_off.py& python3 a_button_on.py


php /var/www/html/turn_on_Fire.php
php /var/www/html/turn_off_Fire.php