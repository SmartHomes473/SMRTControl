import serial
import MySQLdb as mdb
import sys

ser = serial.Serial('/dev/ttyAMA0',9600)
databases = { 1 : mdb.connect('localhost', 'root', 'smarthouse', 'wwfSample'),
			  2 : mdb.connect('localhost', 'root', 'smarthouse', 'roomba');
};

for key in databases.keys() :
	db = databases[key]
	cur = db.cursor()
	cur.execute("SELECT * FROM Communication T")
	
	row = cur.fetchone()
	if row[0] == 1:
		ser.write(chr(0x00) + chr(row[0]&0xff)) #Writes Status
		ser.write(chr((row[1]>>8)&0xff)+chr(row[1]&0xff)) #Writes ExStatusLeng
		ser.write(str(row[2])) #Writes ExtendedStatus
		ser.write(chr(4))
		cur.execute("UPDATE  `Communication` SET  `Status` = '0' WHERE  `Communication`.`Status` =1 LIMIT 1")
		db.commit()
ser.close()
