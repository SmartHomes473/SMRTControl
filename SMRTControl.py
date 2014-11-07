import serial
import MySQLdb as mdb
import sys
import pdb
import time

ser = serial.Serial('/dev/ttyAMA0',9600)
databases = { 1 : ['localhost', 'root', 'smarthouse', 'wwfSample'],
	      2 : ['localhost', 'root', 'smarthouse', 'roomba']
};

deviceData = {
              1 : { 'sentTime':0},
              2 : { 'sentTime':0}
}

def parseBuff(buffer):

	return

def main() :
	readState = 0
	readbuffer = ''
	readpacket = ''

	while 1:
		if ser.inWaiting() != 0:
			incoming = ser.read(ser.inWaiting())
			if readState == 0:
				i = incoming.find(chr(0x0f))
				if i != -1:
					l = incoming.rfind(chr(0x04))
					if l != -1:
						readpacket = incoming[i:l]
						k = incoming[l:].find(chr(0x0f))
						if k != -1:
							readbuffer = incoming[k:]
							readState = 1
					else:
						readbuffer = incoming[i:]
						readState = 1
			elif readState == 1:
				j = incoming.rfind(chr(0x04))
				if j != -1:
					readpacket = readbuffer + incoming[:j]
					readState = 0
					k = incoming[j:].find(chr(0x0f))
					if k != -1:
						readbuffer = incoming[k:]
						readState = 1
				else:
					readbuffer = readbuffer + incoming
			parseBuff(readpacket)
			readpacket = ''
		for key in databases.keys() :
			db = databases[key]
			cur = mdb.connect(db[0],db[1],db[2],db[3]).cursor()
			cur = db.cursor()
			cur.execute("SELECT * FROM Communication T")
			row = cur.fetchone()
			if row[0] == 1:
				#Writes Status
				ser.write(chr(0x0f) + chr(row[0]&0xff))
				#WritesExStatusLength
				ser.write(chr((row[1]>>8)&0xff)+chr(row[1]&0xff))
				#Write ExtendedStatus
				ser.write(str(row[2]))
				ser.write(chr(4))
				#deviceData[key]['sentTime'] = time.time()
				cur.execute("UPDATE  `Communication` SET  `Status` = '2' WHERE  `Communication`.`Status` =1 LIMIT 1")
				db.commit()
			elif row[0] == 2:
				curTime = time.time()
				if curTime - deviceData[key]['sentTime'] > 1 :
					cur.execute("UPDATE `Communication` SET `Status` = '4' WHERE `Communication`.`Status` =2 LIMIT 1")
					db.commit() 

	ser.close()
	return

if __name__ == "__main__":
    main()
