# REST API Using Passport in Laravel 9

In this guide, we will be using Passport for a REST API Presentation.

## Prerequisites
- Setup the Laravel project
- php -v ^8.0.2 installed

## Steps
1. Clone the Project and Go to the project directory and open a terminal window.

 Run `composer install` and wait till all packages gets installed.

 Once all packages gets installed,configure your DB Details in .env file and 

 Now Run `php artisan key:generate`

Once key is generated ,next step is to migrate the tables.
`php artisan migrate`

We are using passport for oauth2 authentication so have to install it by the below command.
`php artisan passport:install`

Once this all done.Start serve your application
 
`php artisan serve` to start the Laravel project.

2. Go to the following URL for the API Endpoint.
    `https://documenter.getpostman.com/view/25994758/2s93CLtENP`

3. Try hitting the API Endpoints and test how the REST API works in POSTMAN:
   


Happy coding!