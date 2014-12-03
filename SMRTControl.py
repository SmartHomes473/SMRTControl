import serial
import MySQLdb as mdb
import sys
import pdb
import time
import subprocess
from subprocess import call


ser = serial.Serial('/dev/ttyAMA0',9600)
databases = { 
}

deviceData = {
}

device_count = 0

def parseBuff(buff):
	i = 0
	#while len(buff) > i:
	#	print ord(buff[i]),
	#		i +=1
	#print "END"
	beginD = buff.find(chr(0x0f))
	endD = buff.find(chr(0x04))
        # make sure END D is not apart of the header
	while endD != -1 and endD - beginD < 5 :
		endD = buff.find(chr(0x04),endD+1)

	while (beginD != -1 and endD != -1) :
		# Process packet header
		dev = ord(buff[beginD+1])
		status = ord(buff[beginD+2])
		length = ord(buff[beginD+3])<<8 + ord(buff[beginD+4])
		print dev,status,length
		# Check for device registration
		if dev == 0:
			# Call add new device with the device URL
			call(["python", "add_new_device.py", buff[beginD+5:endD], str(device_count)]) 
			with open("devices.txt", "r") as device_file:
				for line in device_file:
					pass
				update_database(line)
			# Send Packet with assigned device ID
			ser.write(chr(0x0f) + chr(device_count-1) + chr(0)) 
			ser.write(chr(0)+chr(0)) 
			ser.write(chr(4))
			print "Sending device registration: " + str(device_count-1)
		else:
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
				subprocess.call(['php',deviceData[dev]['Comms']])

		# prep for next packet.
		beginD = buff.find(chr(0x0f),endD)
		endD = buff.find(chr(0x04),beginD)
		while (endD != -1 and endD -beginD < 5):
			endD = buff.find(ch(0x04),endD+1)

# Given a devices.txt line, update the database lists accordingly
def update_database(line):
	# Organization of each line in devices.txt:
	# folder name, displayed name, homepage, rxpage, database name, device_ID
	global device_count 
	global databases
	device_line = line.split(',')
	device_line[5] = device_line[5].replace("\n", "")

	#update database list
	databases_list = ['localhost', 'root', 'smarthouse']
	databases_list.append(device_line[4])
	databases[int(device_line[5])] = databases_list

	#update device_data list
	device_data_item = {'prevCommStatus':0,'sentTime':0,'WatchDog':0} 
	device_data_item["Comms"] = device_line[0] + "/" + device_line[3]
	deviceData[int(device_line[5])] = device_data_item
	device_count += 1

# Iterate the devices.txt file, update the database for each line
def get_devices():
	# Empty the databases
	databases.clear()
	deviceData.clear()

	# Refill with current device list
	with open("devices.txt", "r") as device_file:
		for line in device_file:
			print line
			update_database(line)

def main() :
	readState = 0
	readbuffer = ''
	readpacket = ''

	get_devices()
	while 1:
		if ser.inWaiting() != 0:
			incoming = ser.read(ser.inWaiting())
			i = 0
			#while len(incoming) > i:
			#	print ord(incoming[i]),
			#	i +=1
			#print "END"
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
						#print i,l,readpacket
						#while len(readpacket) > i:
						#	print ord(readpacket[i]),
						#	i +=1
						#print "END"
						#Checking to see if start delimiter received after last end delimiter
						k = incoming.find(chr(0x0f),l)
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
					k = incoming.find(chr(0x0f),j)
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
			cur.execute("SELECT * FROM `Communication` WHERE 1")
			row = cur.fetchone()
			# Comms Watchdog: prevents non zero state for more than 5 seconds.
			if row[0] == 0 and deviceData[key]['prevCommStatus'] != 0:
				deviceData[key]['Watchdog'] = 0
				deviceData[key]['prevCommStatus'] = 0
			elif row[0] != 0: 
				if deviceData[key]['prevCommStatus'] != row[0]:
					deviceData[key]['Watchdog'] = time.time()
					deviceData[key]['prevCommStatus'] = row[0]
				else:
					curtime = time.time()
					if (curtime - deviceData[key]['Watchdog']) > 5 :
						cur.execute("UPDATE  `Communication` SET  `Status` =0 WHERE 1")
						db.commit()
					
			# Check for Send Packet or Send Reply
			if row[0] == 1 or row[0] == 6:
				# Check if request to delete database
				if row[1] == 0 and row[2] == "0":
					ser.write(chr(0x0f) + chr(0) + chr(1)) 
					ser.write(chr(0)+chr(0)) 
					ser.write(chr(4))
					print "Deleting device: " + dbdata[3]
					cur.execute("DROP DATABASE `XXX`".replace("XXX", dbdata[3]))
					db.commit()
					get_devices()
				else:
					# Is a request to send a packet

					# Prep send status
					if(row[0] == 1) :
						status = chr(2)
					else:
						status = chr(6)

					# Send Packet
					ser.write(chr(0x0f) +chr(key)+ status) #Writes Status
					ser.write(chr((row[1]>>8)&0xff)+chr(row[1]&0xff)) #Writes ExStatusLeng
					ser.write(str(row[2])) #Writes ExtendedStatus
					ser.write(chr(0x04))
					print "Sending: "+str(row[0])+" "+str(row[2])

					# Update sent time and next status
					deviceData[key]['sentTime'] = time.time()
					nstatus = str(2*(row[0] == 1))
					print "Status "+nstatus
					cur.execute("UPDATE  `Communication` SET  `Status` ="+nstatus+" WHERE 1")
					db.commit()
			# Check for packet delay
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
