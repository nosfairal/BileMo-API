# BileMo-API
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/ed2343f88a9345959d124276059a6ddd)](https://www.codacy.com/gh/nosfairal/BileMo-API/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=nosfairal/BileMo-API&amp;utm_campaign=Badge_Grade)

B2B API made with Symfony 5.

This is the seventh project of the formation Application Developer - PHP / Symfony on Openclassrooms.

## Table of contents
1.  Prerequisites and technologies
    -   Server
    -   Framework, languages and libraries

2.  Installation
    -   Download or clone
    -   Configure environment variables
    -   Install the project
    -   Create the database
    -   Become an administrator

3.  Documentation


## Prerequisites and technologies

**Server:**

You need a web server with PHP7 (>=7.2.5) and MySQL DBMS.

Versions used in this project:

    Apache 2.4.46
    PHP 7.4.14
    MariaDB 10.4.14

You can obtainmore informations on technicals requirement in the [symfony official documentation.](https://symfony.com/doc/5.4/setup.html)

**Framework and libraries:**

Framework: Symfony ^5.4.1(LTS)

Libraries included via Composer (used in fixtures):

    . doctrine/doctrine-fixtures-bundle: ^3.4,
    . FakerPHP/Faker: ^1.19

Libraries included via Composer:

    . lexik/jwt-authentication-bundle: ^2.15,
    . nelmio/api-doc-bundle: ^4.9,
    . pagerfanta/pagerfanta: ^3.6,
    . willdurand/hateoas-bundle: ^2.4

## Installation

**Download or clone**

Download zip files or clone the project repository with github [see GitHub documentation.](https://docs.github.com/en/repositories/creating-and-managing-repositories/cloning-a-repository)

**Configure environment variables**

You need to configure at least these lines in .env file with yours own datas:

```
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
``` 

**Install the project**

1.   If needed, install Composer by [following the official instructions.](https://getcomposer.org/download/)

2.   In your cmd, go to the directory where you want to install the project and install dependencies with composer:
   
   ``$ cd some\directory ``
   
   ``$ composer install``

Dependencies should be installed in your project (check vendor directory).

To generate your own SSL keys for LexikJWTAuthentication bundle, [see the bundle documentation.](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst#prerequisites)

**Create the database**

If you are in a dev environment, you can create the database and fill it with fake contents with the following command:
    ``$ composer prepare``

Else follow this steps:

1.  If the database does not exist, create it with the following command in the project directory:

    ``$ php bin/console doctrine:database:create``

2.  Create database structure thanks to migrations:

    ``$ php bin/console doctrine:migrations:migrate``

3. Install fixtures to have first contents

    ``$ php bin/console doctrine:fixtures:load``

Your database should be updated with contents.

**Become an administrator**


1.  Go to your database, table user, and  change the "roles" field from ["ROLE_USER"] to ["ROLE_ADMIN"].
2.  Your password is defined in the [UserFixtures.php.](https://github.com/nosfairal/BileMo-API/blob/main/src/DataFixtures/UserFixtures.php)

You are now administrator of this API and can manage it.

## Documentation

There are 2 availables documentation formats:

    . Json documentation: /api/doc.json
    . Html interactive documentation: /api/doc
