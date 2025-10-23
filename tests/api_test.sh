#!/bin/bash

# Load environment variables
if [ -f .env ]; then
    export $(cat .env | xargs)
fi

# Clear the log file
> output.log

# Truncate the users table
mysql -u $DB_USER -p$DB_PASS $DB_NAME -e "TRUNCATE TABLE users;"

# Start the PHP built-in web server
php -S localhost:8000 -t public > /dev/null 2>&1 &
SERVER_PID=$!

# Wait for the server to start
sleep 2

# Test registration
echo "Testing registration..." >> output.log
curl -s -w "%{http_code}\n" -X POST -d "name=Test%20User&email=test@example.com&password=password" http://localhost:8000/api/auth/register >> output.log
echo "" >> output.log

# Test login
echo "Testing login..." >> output.log
curl -s -w "%{http_code}\n" -X POST -d "email=test@example.com&password=password" http://localhost:8000/api/auth/login >> output.log
echo "" >> output.log

# Test logout
echo "Testing logout..." >> output.log
curl -s -w "%{http_code}\n" -X POST http://localhost:8000/api/auth/logout >> output.log
echo "" >> output.log

# Kill the server
kill $SERVER_PID
