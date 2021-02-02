# Larahpanel: Lara Host Panel

Open Source web hosting panel based in laravel



## Features

* Virtual Host administration
    * Nginx
    * Apache
* Database administration
    * MariaDB / MySQL
    * Postgres
* Linux user administration

## Commands

### Create a website virtual host by default create a nginx virtualhost.

```terminal
php artisan website:create domain.com
```

### Create a website virtual host with apache virtualhost.
```terminal
php artisan database:create domain_data server=apache
```

### Create a database

```terminal
php artisan database:create domain_data
```

### Create a database user

```terminal
php artisan database:user-create
```

### Create a linux user

```terminal
php artisan linux:user-create
```

## Tests
Run tests
```terminal
php artisan test
```

## Made with ❤️

Design based in StartBootstrap

https://github.com/startbootstrap/startbootstrap-sb-admin-2
https://data.iana.org/TLD/tlds-alpha-by-domain.txt
