# LPanel: Webhosting Panel

Open Source web hosting panel based in Laravel

### Login
![Lpanel Login Screenshot](/doc/screenshoot/login.png)

### Dashboard
![Lpanel Dashboard Screenshot](/doc/screenshoot/dashboard.png)

## Features

* Virtual Host administration
  * Nginx
  * Apache
* Database administration
  * MariaDB / MySQL
  * Postgres
* Linux user administration

## Commands

### User panel create

```terminal
php artisan user:create
```

### Create a website virtual host by default create a nginx virtualhost

```terminal
php artisan website:create domain.com
```

### Create a website virtual host with apache virtualhost

```terminal
php artisan database:create domain_data server=apache
```

### Create a database by default use MariaDB

```terminal
php artisan database:create dbname
```

### Create a database by type

```terminal
php artisan database:create dbname type=postgresql
```

### Create a database user

```terminal
php artisan database:user-create
```

### Create a linux user

```terminal
php artisan linux:user-create name
```

## Tests

Run tests
```terminal
php artisan test
```

## Made with ❤️

#### Design based in StartBootstrap

https://github.com/startbootstrap/startbootstrap-sb-admin-2

#### Login photo by Brett Sayles

https://www.pexels.com/photo/white-and-blue-cables-2881233

#### TLD list

https://data.iana.org/TLD/tlds-alpha-by-domain.txt
