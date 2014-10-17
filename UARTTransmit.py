import serial
import MySQLdb as mdb
import sys

ser = serial.Serial('/dev/ttyAMA0',9600)
databases = { 1 : mdb.connect('localhost', 'root', 'smarthouse', 'wwfSample')
};

for key in databases.keys() :
	db = databases[key]
	cur = db.cursor()
	cur.execute("SELECT * FROM Communication T")
	
	row = cur.fetchone()
	ser.write("PPDU1: ")
	ser.write(str(row[0])) #Writes Status
	ser.write(" PPDU2: ")
	ser.write(str(row[1])) #Writes ExStatusLength
	ser.write(" PPDU3: ")
	ser.write(str(row[2])) #Writes ExtendedStatus
	ser.write("\n")
	
ser.close()
