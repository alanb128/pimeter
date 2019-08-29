import RPi.GPIO as GPIO
import paho.mqtt.client as mqtt
import dateutil.parser as parser
import sqlite3
import datetime
import time
import json

global pcount #counts pulses
pcount = 0
global risingstart #start time of current rising pulse
risingstart = time.time()
global risingold #previous rising pulse
global elapsed
elapsed = 0

dbpath = "/home/pi/pimeter/mywater.db"

# load values from database
conn = sqlite3.connect(dbpath)
conn.row_factory = sqlite3.Row
r = conn.cursor()
r.execute('SELECT * FROM holding WHERE rownum = 100')
settings = r.fetchone()


GPIO.setmode(GPIO.BOARD) #Pin numbering

GPIO.setup(5, GPIO.IN, pull_up_down=GPIO.PUD_UP)

timeinterval = 1 # sleep interval in seconds for main loop.
lastvalue = 0 #detects changed value
count1 = 0 #Count for oz/sec
count2 = 0 #count for oz/s decrease factor
count3 = 0 #count for gals/hr
time2 = 0 #time count for last hour value
time4 = 0 #time count for flow decreaser
af = 0 #average flow over time interval
lh = [] #array to hold last 60 readings for last hour

haip = settings['mqtt_ip'] #IP of mqtt broker
pulse = settings['pulse'] #flow per pulse
flowtopic = settings['flow_topic'] #MQTT topic 
lhtopic = settings['lh_topic'] #MQTT topic 

def reportstatus(channel):
	global risingold
	global risingstart
	global pcount
	global elapsed
	risingold = risingstart
	risingstart = time.time()
	elapsed = risingstart - risingold # seconds since last rising edge
	pcount += 1

# decreaser helps us gradually lower flow value when pulses stop
decreaser = {1: 1, 2: 1, 3: 0.5, 4: 0.5, 5: 0.25, 6: 0.25, 7: 0.1, 8: 0.1, 9: 0.1}

GPIO.add_event_detect(5, GPIO.RISING, callback=reportstatus, bouncetime=120)

conn.close()

client = mqtt.Client()
client.connect(haip, 1883, 60)
client.loop_start()

print("watermeter starting")

while True:

        count1 = pcount
        pcount = 0
        count3 += count1
        time2 += 1
        lastaf = af
        if elapsed == 0:
                af = 0
        else:
                af = round(pulse/elapsed)

        if count1 == 0:
                time4 += 1
                if time4 < 10:
                         af = round(af*decreaser.get(time4),0)
                else:
                         af = 0
        else:
                time4 = 0

        time.sleep(timeinterval)

        if af != lastvalue:
                # push the flow data to HA
                client.publish(flowtopic, af);
                lastvalue = af

        if time2 == 60: #push last hour data every 60 sec.
                time2 = 0
                if len(lh) ==  60:
                        del lh[0]
                lh.append(count3)
                j = 0
                for i in range(len(lh)):
                        j = j + lh[i]
                client.publish(lhtopic, round(j * (pulse * 0.00781),0));
                count3 = 0

