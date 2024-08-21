<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and
creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in
many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache)
  storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all
modern web application frameworks, making it a breeze to get started with the framework.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 1500 video
tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging
into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in
becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in
the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by
the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell
via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# hrms-attendance

## Prerequisites of deleting any data row from crud

~~~
**Some data must not be deleted if these are used on another tables.That is why when deleting a data from crud or another
panel we should check whether it is used on another table or not. In this case we define two methods on
app/Http/Controllers/FilterController.php** 
Initially we have implemented this feature on designation listing. If it is tested then it will be implemented on other 
desired modules. So currently it can be tested from designation listing page.   
~~~

1.getUsedTableByColumn-- It returns an array of used table
```php
    public function getUsedTableByColumn($columName = "")
    {
        $usedTbls = [];
        $tables = DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            $key = "Tables_in_" . DB::connection()->getDatabaseName();
            $tblName = $table->$key;
            if (Schema::hasColumn($tblName, $columName)) {
                $usedTbls[] = $tblName;
            }
        }
        return $usedTbls;
    }
```
2.getUsedTableById-- It returns an array of used table with specific row
```php
    public function getUsedTableById($usedTables = [], $columName = "", $val = null)
    {
        $ut = [];
        foreach ($usedTables as $tbl) {
            if (DB::table($tbl)->where($columName, $val)->count('id') > 0) {
                $ut [] = $tbl;
            }
        }
        return $ut;
    }
```

### Usage

```php
    public function delete(Designation $designation)
    {
        try {
            if (auth()->user()->can('Delete Designation')) {

                $col = "designation_id";
                $usedTables = FilterController::getUsedTableByColumn($col);
                $ut = FilterController::getUsedTableById($usedTables, $col, $designation->id);

                if (count($ut) > 0) {
                    $feedback['status'] = false;
                    $feedback['message'] = "Unable to delete because of used on " . implode(", ", $ut) . " tables.";
                } else {
                    $feedback['status'] = $designation->delete();
                }
            }
        } catch (Exception $exception) {
            $feedback['status'] = false;
        }

        return $feedback;
    }
```
````
# HRMS docker how to use 
#sudo docker-compose up -d (sudo if user without root user) //docker-compose -f docker-compose-staging.yml up
#sudo docker exec -it hrms bash ((sudo if user without root user))
#composer install
#cp .env.example .env
#chmod -R 777 storage/
#Create db hrms using phpmyadmin
#Then import file evrydy (1-1-2023).zip

in env change to this
DB_CONNECTION=mysql
DB_HOST=hrms-db
DB_PORT=3306
DB_DATABASE=hrms
DB_USERNAME=root
DB_PASSWORD=


Be sure that when you run :   sudo docker ps -a
You Find 
CONTAINER ID   IMAGE                   COMMAND                  CREATED          STATUS                         PORTS                                       NAMES
5b64ad4890db   phpmyadmin/phpmyadmin   "/docker-entrypoint.…"   50 minutes ago      Up 50 minutes                  0.0.0.0:7000->80/tcp, :::7000->80/tcp       hrms-phpmyadmin
6ee831db3d14   shouts.dev/laravel      "docker-php-entrypoi…"   50 minutes ago      Up 50 minutes                  0.0.0.0:80->80/tcp, :::80->80/tcp           hrms
7fc4d2ebe7a8   mariadb:10.6.11         "docker-entrypoint.s…"   50 minutes ago      Up 50 minutes                  0.0.0.0:3306->3306/tcp, :::3306->3306/tcp   hrms-db
 
````
