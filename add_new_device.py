from subprocess import call
import MySQLdb as mdb
import sys
import pdb


def main():
	#should be of form http://WEBSITE/PRODUCT_NAME.tar.gz
	file_url = sys.argv[1]
	file_name = file_url[file_url.rfind("/")+1:]
	folder_name = file_name[:file_name.find(".tar.gz")]

	#Put the files into the PRODUCT_NAME folder
	call(["mkdir", folder_name])
	call(["wget", file_url])
	call(["tar", "-C", folder_name, "-zxf", file_name])
	call(["rm", file_name])

	#read config data
	config_info = dict()
	with open(folder_name + "/config.txt") as config_file:
		for line in config_file:
			category = line[0:line.find(":")]
			name = line[line.find(" ")+1:len(line)-1]
			config_info[category] = name

	#add the new device to the device list
	#Organization of each line in devices.txt:
	#folder name, displayed name, homepage, rxpage, database name
	with open("devices.txt", "a") as device_file:
		csv = folder_name + ","
		csv += config_info["NAME"] + ","
		csv += config_info["HOMEPAGE"] + ","
		csv += config_info["RXPAGE"] + ","
		csv += config_info["DATABASE"]
		csv += "\n"
		device_file.write(csv)

	#create database 
	db = mdb.connect("localhost", "root", "smarthouse")
	cursor = db.cursor()

	sql = None
	with open(folder_name + "/" + config_info["SQL"], "r") as sql_file:
		sql = sql_file.read()
		#delimit the file on semicolons without removing them
		sql_queries = [e+";" for e in sql.split(";") if e != ""]

		#remove last delimited section due to python split semantics
		sql_queries = sql_queries[:-1]
		for query in sql_queries:
			query = query.replace("\n", "")
			cursor.execute(query)
			db.commit()


if __name__ == "__main__":
	main()
