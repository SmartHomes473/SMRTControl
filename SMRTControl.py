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
				#Found the start delimiter
				if i != -1:
					#looking for an end delimiter
					l = incoming.rfind(chr(0x04))
					#Found the end delimiter
					if l != -1:
						#Sending packet which starts with start delimiter and ends with end delimiter
						#parseBuff() will figure out how many packets are in between those delimiters
						readpacket = incoming[i:l]
						#Checking to see if start delimiter received after last end delimiter
						k = incoming[l:].find(chr(0x0f))
						#Found Start Delimiter After last end delimiter
						if k != -1:
							readbuffer = incoming[k:]
							readState = 1
					else:
						readbuffer = incoming[i:]
						readState = 1
			#Found start delimiter but haven't found end delimiter yet
			elif readState == 1:
				#looking for an end delimiter
				j = incoming.rfind(chr(0x04))
				if j != -1:
					#Setting packet with start and end delimiter, as before parseBuff() will determine 
					#the number of packets received between those delimiters
					readpacket = readbuffer + incoming[:j]
					readState = 0
					#Searching for start delimiter after last received end delimiter
					k = incoming[j:].find(chr(0x0f))
					#Found start delimiter after end delimiter
					if k != -1:
						readbuffer = incoming[k:]
						readState = 1
				#Did not find an end delimiter
				#Writes contents of read buffer and continues to search for end delimiter
				else:
					readbuffer = readbuffer + incoming

			parseBuff(readpacket)
			readpacket = ''
		for key in databases.keys() :
			dbdata = databases[key]
			db = mdb.connect(dbdata[0],dbdata[1],dbdata[2],dbdata[3])
			cur = db.cursor()
			cur.execute("SELECT * FROM Communication T")
			row = cur.fetchone()
			if row[0] == 1:

				ser.write(chr(0x0f) +chr(key)+ chr(2)) #Writes Status
				ser.write(chr((row[1]>>8)&0xff)+chr(row[1]&0xff)) #Writes ExStatusLeng
				ser.write(str(row[2])) #Writes ExtendedStatus
				ser.write(chr(4))
				'''
				TxData = ''
				#Status
				TxData = TxData + chr(0x0f) + chr((row[0]+1)&0xff)
				print TxData
				#ExStatusLength
				TxData = TxData + chr((row[1]>>8)&0xff)+chr(row[1]&0xff)
				print TxData
				#ExtendedStatus
				TxData = TxData + str(row[2])
				print TxData
				#End Delimeter
				TxData = TxData + chr(4)
				print TxData
				ser.write(TxData)
				'''
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
