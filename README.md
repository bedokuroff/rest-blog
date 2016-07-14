rest blog
=========

Simple REST blog-like API. Some highlights:
* uses Symfony 2.7 framework
* uses Doctrine as ORM
* uses Doctrine's caching mechanics to cache everything (including database results), redis is used as cache backend
* RabbitMQ is used to run sending email notification asynchronously
* 'Soft delete' is used for posts and tags - they are not actually deleted, but are marked as deleted
* Documentation is generated on the fly using NelmioApiDocBundle

Requirements
------------
1. PHP 5.6 (Extensions: redis, pdo_mysql, intl)
2. Web-server (any should do, developed with PHP built in server)
3. composer
4. MySQL 5+
5. RabbitMQ 
6. Redis

Installation instructions
-------------------------
Please run 'composer install' in the root directory of the project. Check parameters.yml and set the default parameters there
accordingly to your environment. 
To create database and tables please run the following commands:
```
app/console doctrine:database:create
app/console doctrine:schema:update --force
```
If you get any symfony-specific errors (like permission issues or something like that) please refer to symfony documentation:
http://symfony.com/doc/current/index.html

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