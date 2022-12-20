# PHP_Chat_App

To run, open a terminal in the `app` directory and run `php -S localhost:<ENTER_PORT_NUMBER>`

This is a simple 2 API Chat App backend. One API sends messages while one is hit to get messages. To send a message, hit the /messages route with a POST method call and send fromUserId, toUserId and message in the query parameters.

To get messages hit the /messages route with a GET method call and send userId as a query parameter.
