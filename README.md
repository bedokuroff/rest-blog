rest blog
=========

Simple REST blog-like API.

Requirements
------------
1. PHP 5.6 (Extensions: redis, pdo_mysql, intl)
2. Web-server (PHP )
3. composer
4. MySQL 5+
5. RabbitMQ 

Installation instructions
-------------------------
Please run 'composer install' in the root directory of the project. Check parameters.yml and set the default parameters there
accordingly to your environment. 

Sending mail via queues
-----------------------
Sending mail is implemented via queues. Queues are implemented using php-amqp library. To start the consumer sender process,
please run:
```
app/console rabbitmq:consumer rest_blog.amqp.send_mail
```
API Documentation
-----------------
API documentation can be accessed via /api/doc route. 
That's pretty much it!