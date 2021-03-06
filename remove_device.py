from subprocess import call
import MySQLdb as mdb
import sys
import pdb

def main():

	log = open("py_remove_log.txt", "w")

	device_to_remove = sys.argv[1]	
	log.write(device_to_remove)

	#find device line, get folder/database name and remove the line
	folder = None
	database = None

	with open("devices.txt", "r") as device_file:
		for line in device_file:
			device_line = line.split(",")	

			if device_line[2] == device_to_remove:
				folder = device_line[0]
				database = device_line[4].replace("\n", "")
			else:
				with open("devices_new.txt", "a") as newdevice_file:
					newdevice_file.write(line)

	call(["chmod", "777", "devices_new.txt"])
	call(["mv", "devices_new.txt", "devices.txt"])	

	#tell smrtcontrol to drop database
	db = mdb.connect("localhost", "root", "smarthouse", database)
	cursor = db.cursor()
	sql = "UPDATE `Communication` SET `Status`= 1, `ExStatusLength`= 0, `ExtendedStatus`= 0"
	cursor.execute(sql)
	db.commit()

	#delete folder
	call(["rm", "-rf", folder])


if __name__ == "__main__":
	main()
