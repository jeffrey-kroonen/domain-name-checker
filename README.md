# domainname checker

## Docker
Use the following command to build the docker containers.
```
$ docker-compose build
```

Use the following command to start the docker containers build in the privious step.
```
$ docker-compose up -d
```

The containers are started. To use the command in the project you first have to enter the php-fpm container to call it.
```
$ docker container exec -it domain-checker-php-fpm bash
```

When you done with the execution, you can stop the container with the following command.
```
$ docker-compose down
```

## CLI
### Configuration
When you have entered the docker container php-fpm, you have to configure a couple things till it works.  

First make sure you have downloaded all dependecies from Composer. This can be done with the following command.
```
$ composer update
```

Open the **.env** file in the root directory of the project.
You will see the following variables.
```
VERSIO_USERNAME=
VERSIO_PASSWORD=
```

Fill in the your Versio username and password. Make you sure your IP adress you are running from is listed ad Versio. this can be done at the following URL https://www.versio.nl/customer/account/api.

The file in the following path must contain the domains you want to check for availability. Curruntly it contain three mock domains. Feel free to edit the file.
```
/public/target/check-domains.csv
```

After you have done all previous steps. You are ready to execute the command!

### Usage
You can find the source code in the following path.
```
/src/Command/DomainCheckCommand.php
```

This file contians all the source code used in this project. You can append more top level domains to the variable ``` $targetTopDomains ```. All the top level domains will be checked on availibility.  

Use the following command inside the php-fpm docker container to execute the script.
```
$ php bin/console app:app:domain-check
```

The outcome of this process will be saved in the following file.
```
/public/target/result-check-domains.csv
```