# PHPFusion Docker Container (Virtual Server)

PHPFusion Docker Container is a set of docker images that includes all necessary stacks in one handy packages to use with your PHPFusion projects.
Our objective is to get active developers who may need to make a set up a running PHPFusion within minutes where
they can hotswap the deployment environment and isolate different builds effectively according to requirements and needs.

###General information
**Important:** PHPFusion Docker stack is build for local development and not for production usage.

**Package Info:**
This package comes with a default configuration option. You can modify to replicate to your development environment to mimic the final production server configurations by editing the docker-compose.yml to select other images to build the server to fit your purposes

By default, this package consists of the following:
- PHP 8.1
- Apache
- MySQL 8.0
- Redis
- Mailhog

We know that most of PHPFusion developers here, there might not be keen to try out new things or change the way they do things, and there are also some learning curve involved that some people might not have the time to adapt. Therefore, we kept everything as simple as it can be with the few steps to get things done x10 times faster than what used to be.


Installation Guide
---

### System Requirements

1. Download and install Docker Desktop (https://www.docker.com/products/docker-desktop/)
2. Download and install Node.js (https://nodejs.org/en/download/current)
3. Download and install Git (https://git-scm.com/downloads)

### Installation

- Clone this repository on your local computer. You can either use the download .zip files and extract it to your computer
  or use git in windows command prompt / terminal with the following command in Windows:

```git
C:\Users\username > git clone https://github.com/PHPFusion/PHPFusionDocker.git 
```

- Browse to your newly cloned directory to start the docker with the following command
  
```git
C:\Users\username\PHPFusionDocker > docker-compose up
```

![1](https://github.com/PHPFusion/PHPFusionDocker/assets/4078041/af3a08fd-cd5a-4cd7-8c64-f6035986818f)

![2](https://github.com/PHPFusion/PHPFusionDocker/assets/4078041/b5104bb9-c1af-441f-9bf4-7769e8e80aa2)

![3](https://github.com/PHPFusion/PHPFusionDocker/assets/4078041/2fff0a8c-ee8d-467e-b06b-4be7a77e0a40)


PHPFusion is now ready. You can access it via ```http://localhost```

### Advanced Configuration

You can configure the configuration variables of the docker container in the included .env file. This is for example port numbers, default usernames and passwords.

For advance configurations, edit the docker images to suit your needs in the docker-compose.yml file. Here you can find the setup required to build the docker container and change the images that you want to use.

### Connect via SSH
You can connect to web server using docker-compose exec command to perform various operation on it. Use below command to login to container via ssh.
```docker-file
docker-compose exec webserver bash
```

PHP
---
The installed version of php depends on your docker images in docker-compose.yml file

BY default following extensiosn are installed.

- mysqli
- pdo_sqlite
- pdo_mysql
- mbstring
- zip
- intl
- mcrypt
- curl
- json
- iconv
- xml
- xmlrpc
- gd

PHPMyAdmin
---
phpMyAdmin is configured to run on port ```http://localhost:8001```. Use following default credentials by default or edit the .env file to change them

```
username: phpfusion
password: phpfusion
```

```docker
database created: phpfusion_docker
```

Redis
---
It comes with Redis. It runs on default port 6379, and has not password

Contributing
---
We are happy if you want to create a pull request or help people with their issues. However, please bear in mind that
this stack is not built for production use and your changes should be made as simple as possible to follow and not specialized or custom to a specific situation.

Please also note that your pull requests are always need to be made against the ``master`` branch only.

License
---
The PHPFusionDocker is dual licensed under GPL and/or MIT depending on what is fitting for you. But the CMS itself remains licensed under AGPL v3. 
