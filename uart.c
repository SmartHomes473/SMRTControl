#include <stdio.h>
#include <unistd.h>
#include <fcntl.h>
#include <termios.h>
#include <mysql.h>
#include <stdint.h>
#include <string.h>

#define DATABASE_NAME		"uart"
#define DATABASE_USERNAME	"root"
#define DATABASE_PASSWORD	"smarthouse"

MYSQL *mysql1;

void mysql_connect (void)
{
    //initialize MYSQL object for connections
	mysql1 = mysql_init(NULL);

    if(mysql1 == NULL)
    {
        fprintf(stderr, "%s\n", mysql_error(mysql1));
        return;
    }

    //Connect to the database
    if(mysql_real_connect(mysql1, "localhost", DATABASE_USERNAME, DATABASE_PASSWORD, DATABASE_NAME, 0, NULL, 0) == NULL)
    {
    	fprintf(stderr, "%s\n", mysql_error(mysql1));
    }
    else
    {
        printf("Database connection successful.\n");
    }
}


void mysql_disconnect (void)
{
    mysql_close(mysql1);
    printf( "Disconnected from database.\n");
}

int main() {

mysql_connect();

int uart0_filestream = -1;
uart0_filestream = open("/dev/ttyAMA0", O_RDWR | O_NOCTTY);

if(uart0_filestream == -1)
{
	printf("Error - Unable to open UART. Ensure it is not in use by another application\n");
}

struct termios options;
tcgetattr(uart0_filestream, &options);
cfsetispeed(&options, B9600);
cfsetospeed(&options, B9600);
options.c_cflag = B9600 | CS8 | CLOCAL | CREAD;
options.c_iflag = IGNPAR | ICRNL;
options.c_oflag = 0;
tcflush(uart0_filestream, TCIFLUSH);
tcsetattr(uart0_filestream, TCSANOW, &options);

//Transmitter (TX)
char *tx_buffer;
while(1){
if (mysql1 != NULL)
    {
    	//printf("mysql is not null\n");
        if (!mysql_query(mysql1, "SELECT * FROM textBoxCommand T WHERE T.key=(SELECT MAX(A.key) FROM textBoxCommand A)"))
        {
        	//printf("query is not null\n");
        	MYSQL_RES *result = mysql_store_result(mysql1);
        	if (result != NULL)
        	{
        		//printf("result is not empty\n");
        		//Get the number of columns
        		int num_rows = mysql_num_rows(result);
        		int num_fields = mysql_num_fields(result);

        		//printf("number of rows is %d\n", num_rows);

        		MYSQL_ROW row;			//An array of strings
        		while( (row = mysql_fetch_row(result)) )
        		{
        			if(num_fields >= 2)
        			{
        				char *value_int = row[0];
        				tx_buffer = row[1];

        				printf( "Got value:::::: %s\n", tx_buffer);
        	        }
        		}
   	            mysql_free_result(result);
        	}
        	else
        	{
        		//printf("result is null.\n");
        	}
        }
    }

if(uart0_filestream != -1)
{
	int count = write(uart0_filestream, tx_buffer, strlen(tx_buffer));

	if(count < 0)
	{
		printf("UART TX error\n");
	}
}

//Receiver (RX)

if(uart0_filestream != -1)
{
	unsigned char rx_buffer[256];
	int rx_length = read(uart0_filestream, (void*)rx_buffer, 256);
	if(rx_length < 0)
	{
		printf("UART RX error\n");
	}
	else if(rx_length == 0)
	{
		//No data waiting
	}
	else
	{
		rx_buffer[rx_length] = '\0';
		printf("%i bytes read : %s\n", rx_length, rx_buffer);
	}
}
}
close(uart0_filestream);

return 0;
}
