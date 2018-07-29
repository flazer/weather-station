# Weatherstation Dashboard Devices

## Introduction

These are the sketches you can use to send data to the dashboard.
The code is written to run on Wemos D1 devices (ESP8266 compatible).

If you want to send your data via http(s)-request use the code in "client_http" otherwise there is also a mqtt-client.

## Dependencies

The sketches have some dependencies.

Please install them first:

BME280I2C - [https://github.com/finitespace/BME280](https://github.com/finitespace/BME280)

OneWire - [https://www.pjrc.com/teensy/td_libs_OneWire.html](https://www.pjrc.com/teensy/td_libs_OneWire.html)

ESP8266WiFi - [https://github.com/esp8266/Arduino](https://github.com/esp8266/Arduino)

If you want to use the MQTT version, please install the following lib, too:

PubSubClient - [https://github.com/knolleary/pubsubclient.git](https://github.com/knolleary/pubsubclient.git)

## Configure

Open the project and edit the settings.h file to the belongings you need. There are some differences between these two versions, but I'm sure you will know what you have to edit, when you took at look.  

## Blabla
Feel free to share, change and improve the code. 