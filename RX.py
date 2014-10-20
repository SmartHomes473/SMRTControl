import serial
import MySQLdb as mdb
import sys

ser = serial.Serial('/dev/ttyAMA0',9600)

while 1:
	s = ser.read()
	if s:
		print s

ser.close()


#	if ser.inWaiting():
#		s = ser.read(ser.inWaiting())
#		print s

#ser.close()
