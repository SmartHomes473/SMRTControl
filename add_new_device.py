from subprocess import call
import MySQLdb as mdb
import sys


def main():
	#should be of form http://WEBSITE/PRODUCT_NAME.tar.gz
	file_url = sys.argv[1]
	file_name = file_url[file_url.rfind("/")+1:]
	product_name = file_name[:file_name.find(".tar.gz")]

	#Put the files into the PRODUCT_NAME folder
	call(["mkdir", product_name])
	call(["wget", file_url])
	call(["tar", "-C", product_name, "-zxf", file_name])
	call(["rm", file_name])

	#get the device name
	device_name = None
	with open(product_name + "/NAME.txt") as name_file:
		device_name = name_file.read()

	#remove unnecessary file
	call(["rm", product_name + "/NAME.txt"])

	#add the new device to the device list
	with open("devices.txt", "a") as device_file:
		device_file.write(product_name + "," + device_name + "\n")

	#create database 
	db = mdb.connect("localhost", "root", "smarthouse")
	cursor = db.cursor()

	sql = None
	with open(product_name + "/" + product_name + ".sql", "r") as sql_file:
		sql = sql_file.read().replace('\n', '')
	cursor.execute(sql)	


if __name__ == "__main__":
    main()
