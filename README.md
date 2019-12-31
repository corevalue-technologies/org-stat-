# org-stat
Github Org Stats!

##Pre-requisite
- PHP 7.x
- https://getcomposer.org/download/


##Setup
Run composer to install dependencies.
```
composer install
```


Add your github API keys to the config file. You can set up a personal access token [here](https://github.com/settings/tokens).

```
cp .env.example .env
vim .env
```
- update **GITHUB_PERSONAL_ACCESS_TOKEN_USER** 
- update **GITHUB_PERSONAL_ACCESS_TOKEN_PASSWORD** 
- update **GITHUB_ORGANISATION**
 
##Running the import script

Import your repository commit data into the local SQLite database by running the import script from your command line:
```
php -f src/import.php
```

##Viewing the web interface
You can access the web interface through PHP's built in web-server. To use PHP's webserver run the following command from your command line:
```
php artisan serve
```

You should then be able to access the web interface through [http://localhost:8000](http://localhost:8000)

##Reference
- https://developer.github.com/v3/
- https://github.com/GrahamCampbell/Laravel-GitHub
- https://laravel.com/docs/6.x


## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
