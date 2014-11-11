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

	device_name = None
	homepage = None
	commpage = None
	sql_file = None
	with open(product_name + "/config.txt") as config_file:
		lines = config_file.readlines();
		device_name = lines[0][lines[0].find(": "):]
		homepage = lines[1][lines[1].find(": "):]
		commpage = lines[2][lines[2].find(": "):]
		sql_file = lines[3][lines[3].find(": "):]

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
