# Drink Water API
## About
This a basic API with authenticated CRUD operations, receives and outputs JSON, and was developed with PHP, without frameworks.

## How to run in localhost
* Clone this repository ```git clone https://github.com/Romanti-Ezer/DrinkWaterAPI.git```
* Create a database
* Create a table called **```user```**. If you change this name, will be necessary to update ````classes/user.php```
```
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(191) NOT NULL,
  `name` varchar(191) NOT NULL,
  `password` varchar(191) NOT NULL,
  `drink_counter` int(11) NOT NULL DEFAULT 0,
  `token` varchar(191) NOT NULL,

  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```
* Configure the database in ```config.php```
* Configure a VirtualHost if necessary
```
<VirtualHost drinkwater.api:80>
    ServerAdmin romantigds@gmail.com
    DocumentRoot "D:/xampp/htdocs/drinkwaterapi"
    ServerName drinkwater.api
    ServerAlias drinkwater.api
    ErrorLog "logs/drinkwater.api-error.log"
</VirtualHost>
```
* **Important**: keep the ```.htaccess``` setting. This file is very important to controll routes.
* **Important²**: this API is suposed to run in Apache servers. If necessary to run in Nginx, ```nginx.conf``` will need settings
## Testing
I recommend using [Postman](https://www.getpostman.com/) for testing the endpoints.
Using this program, is very easy to set the HTTP method, header, body and create multiple requests.