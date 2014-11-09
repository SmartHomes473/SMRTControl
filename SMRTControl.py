import serial
import MySQLdb as mdb
import sys
import pdb
import time
import subprocess


ser = serial.Serial('/dev/ttyAMA0',9600)
databases = { 
	1 : ['localhost', 'root', 'smarthouse', 'wwfSample'],
	2 : ['localhost', 'root', 'smarthouse', 'roomba']
};

deviceData = {
    1 : { 'sentTime':0,'Comms':'wwfComms.php'}, 
    2 : { 'sentTime':0,'Comms':'roombaComms.php'}
}

def parseBuff(buff):
	i = 0
	while len(buff) > i:
		print ord(buff[i]),
		i +=1
	print "END"
	beginD = buff.find(chr(0x0f))
	endD = buff.find(chr(0x04))
	while (beginD != -1 and endD != -1) :
		dev = ord(buff[beginD+1])
		# Process packet header
		status = ord(buff[beginD+2])
		length = ord(buff[beginD+3])<<8 + ord(buff[beginD+4])
		# Connect to device database 
		dbdata = databases[dev]
		db = mdb.connect(dbdata[0],dbdata[1],dbdata[2],dbdata[3])
		cur = db.cursor()
		# Check current status
		cur.execute("SELECT * FROM Communication")
		[dbstatus,dbexlength,dbexdata] = cur.fetchone()
		if((dbstatus == 2 and status==3) or ( dbstatus ==0 and status == 5)):

			# Good Packet, Update database and call php script
			cur.execute("UPDATE  `Communication` SET  `Status`="+str(status)+\
				",`ExStatusLength`="+str(length)+",`ExtendedStatus`=\""+buff[beginD+5:endD]+\
				"\" WHERE 1")
			db.commit()
			print ['php',deviceData[dev]['Comms']]
			subprocess.call(['php',deviceData[dev]['Comms']])
		# prep for next packet.
		beginD = buff.find(chr(0x0f),endD)
		endD = buff.find(chr(0x04),beginD)


def main() :
	readState = 0
	readbuffer = ''
	readpacket = ''

	while 1:
		if ser.inWaiting() != 0:
			incoming = ser.read(ser.inWaiting())
			i = 0
			while len(incoming) > i:
				print ord(incoming[i]),
				i +=1
			print "END"
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
						readpacket = incoming[i:(l+1)]
						print i,l,readpacket
						while len(readpacket) > i:
							print ord(readpacket[i]),
							i +=1
						print "END"
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
					readpacket = readbuffer + incoming[:(j+1)]
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
			if row[0] == 1 or row[0] == 6:
				if(row[0] == 1) :
					status = chr(2)
				else:
					status = chr(6)
				ser.write(chr(0x0f) +chr(key)+ status) #Writes Status
				ser.write(chr((row[1]>>8)&0xff)+chr(row[1]&0xff)) #Writes ExStatusLeng
				ser.write(str(row[2])) #Writes ExtendedStatus
				ser.write(chr(4))
				print "Sending: "+str(row[0])+" "+str(row[2])
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
				deviceData[key]['sentTime'] = time.time()
				nstatus = str(2*(row[0] == 1))
				print "Next Status ="+nstatus
				cur.execute("UPDATE  `Communication` SET  `Status` ="+nstatus+" WHERE 1")
				db.commit()
			elif row[0] == 2:
				curTime = time.time()
				if curTime - deviceData[key]['sentTime'] > 1 :
					print "Timeout of Device: "+str(key)+' At:'+str(curTime)+' Sent:'+str(deviceData[key]['sentTime'])
					cur.execute("UPDATE `Communication` SET `Status` = '4' WHERE `Communication`.`Status` =2 LIMIT 1")
					db.commit() 

	ser.close()
	return

if __name__ == "__main__":
    main()
'''
w;1;Austin, Texas;74;45;71;0#
w;2;Brooklyn, Michigan;43;31;84;20#
w;3;Ann Arbor, Michigan;43;30;83;20#
w;4;Boston, Massachusetts;48;40;70;0#
w;5;Charlotte, North Carolina;65;40;80;0#
'''