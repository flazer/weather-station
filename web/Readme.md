# Weatherstation Dashboard

## Introduction

> This is a rough dashboard I've build in 2 days to show some sensor values like temperature, humidity and pressure. Each sensor is bound to a device. In my case it's a Wemos D1 / ESP8266 (Arduino), sending values every X minutes.

## Dependencies

> To save some time, I'm using [Laravel](https://laravel.com). It's a really nice framework, so I don't have to start completly from scratch. It's totally overpowered for this usecase and installs a lot of further dependencies, but at least it safed me a lot of time. 
To run Laravel, you need to install [composer](https://getcomposer.org/download/) and [npm](https://nodejs.org/en/download/). To do so, please follow their instructions.

## Requirements
- PHP >= 7.1.3
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension

https://laravel.com/docs/5.6/installation#server-requirements

## Install

> Clone this project and run "composer install" & "npm install".

> Rename .env.example to .env and run "php artisan key:generate". 
 
> Open .env and insert your MySQL-credentials.  

> Now run "php artisan migrate" to write all necessary tables and "php artisan db:seed" to add standard-values to your db.

> Run `npm run prod` to publish assets
  
> Now you have to add your devices. To do so, open your database and insert them into 'devices'. Slug must be unqiue, name will be displayed in your dashboard. The token is stored as a md5-hash, so choose a token, hash it and insert it. If you want to sort your devices, please add a number to sort. Status is used to disable devices.  

> If this is finished you have to add sensors to your new device. Have a look into "device_sensors" and insert your device_id & type_id.  

> If your page doesn't load, please check the rights or usergroups for laravel's storage-folders.

__API URL__
> The URL for the Controller to send Data is: http(s)://YOUR_WEBSERVER_IP_HOSTNAME/api/save

## Settings

There are some possibilities to configure the dashboard.

__Language__  

Because I'm german I've set the Language default to german. To change it back to english, just add the following to your .env:
> APP_LOCALE="de"

__Timezone__  

Default is set to UTC. To change it, just add the following to .env:
> APP_TIMEZONE="Europe/Berlin"

__Updateinterval__  

The dashboards tries to update its data every 15 minutes. You can change the timespan at:
> app/config/sensors.php 

You will notice, that there is also the possibility to change date- & timeformats. Feel free to do so.
If you don't want to change it directly in the configfile, you can also add the environment-identifier to your .env and change the values there (example: SENSORS_UPDATE_INTERVAL_SECS=60).


## Blabla
Feel free to change and improve the code. 
