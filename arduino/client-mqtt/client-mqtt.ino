#include <BME280I2C.h>
#include <Wire.h>
#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>
#include <PubSubClient.h>

#include "settings.h"

ESP8266WiFiMulti WiFiMulti;
WiFiClient wifiClient;
PubSubClient client(wifiClient);

#define SERIAL_BAUD 115200
#define FORCE_DEEPSLEEP

BME280I2C bme;


/**
 * Le Setup
 */
void setup() {
  Serial.begin(SERIAL_BAUD);
  //while(!Serial) {} // Wait
  Wire.begin();

  splashScreen();
  delay(1000); //reprogramming

  Serial.println("---");
  Serial.println("Searching for sensor:");
  Serial.print("Result: ");
  while(!bme.begin())
  {
    Serial.println("Could not find BME280 sensor!");
    delay(1000);
  }

  switch(bme.chipModel()) {
     case BME280::ChipModel_BME280:
       Serial.println("Found BME280 sensor! Success.");
       break;
     case BME280::ChipModel_BMP280:
       Serial.println("Found BMP280 sensor! No Humidity available.");
       break;
     default:
       Serial.println("Found UNKNOWN sensor! Error!");
  }

  startWIFI();
}


/**
 * Looping Louie
 */
void loop() {
  runMQTT();
  sendSensorData();

  delay(500); //wait
  goToBed(minutes2sleep); //sending into deep sleep
}


/**
 * Building http-POST-request and send all necessary data              
 */
void sendSensorData () {
  float temp(NAN), hum(NAN), pres(NAN);
  
  BME280::TempUnit tempUnit(BME280::TempUnit_Celsius);
  BME280::PresUnit presUnit(BME280::PresUnit_bar);
  bme.read(pres, temp, hum, tempUnit, presUnit);
  pres = pres * 1000; // convert to millibar

  client.publish(topic_temperature, String(temp).c_str(), true);
  client.publish(topic_humidity, String(hum).c_str(), true);
  client.publish(topic_pressure, String(pres).c_str(), true);
}


/**
 * Establish WiFi-Connection
 * 
 * If connection times out (threshold 50 sec) 
 * device will sleep for 5 minutes and will restart afterwards.
 */
void startWIFI() {
  Serial.println("---");
  WiFi.mode(WIFI_STA);
  Serial.println("(Re)Connecting to Wifi-Network with following credentials:");
  Serial.print("SSID: ");
  Serial.println(ssid);
  Serial.print("Key: ");
  Serial.println(password);
  Serial.print("Device-Name: ");
  Serial.println(espName);
  
  WiFi.hostname(espName);
  WiFiMulti.addAP(ssid, password);

  int tryCnt = 0;
  
  while (WiFiMulti.run() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
    tryCnt++;

    if (tryCnt > 100) {
      Serial.println("");
      Serial.println("Could not connect to WiFi. Sending device to bed.");
      goToBed(5);
    }
  }

  Serial.println("");
  Serial.println("WiFi connected");  
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
  delay(300);
}


/**
 * Establish MQTT-Connection
 * 
 * If connection fails, device will sleep for 5 minutes and will restart afterwards.
 */
void runMQTT() {
  Serial.println("---");
  Serial.println("Starting MQTT-Client with following credentials:");
  Serial.print("Host: ");
  Serial.println(mqtt_server);
  Serial.print("User: ");
  Serial.println(mqtt_user);
  Serial.print("Password: ");
  Serial.println(mqtt_password);
  Serial.print("ClientId: ");
  Serial.println(mqtt_clientId);

  client.setServer(mqtt_server, 1883);
  
  while (!client.connected()) {
    Serial.print("Attempting connection... ");
    // Attempt to connect
    if (client.connect(mqtt_clientId, mqtt_user, mqtt_password)) {
      Serial.println("Success.");
      client.loop();
    } else {
      Serial.println("Failed.");
      Serial.println("Could not connect to MQTT-Server. Sending device to bed.");
      goToBed(5);
    }
  }
}


/**
 * Sending device into deep sleep
 */
void goToBed (int minutes) {
  #ifdef FORCE_DEEPSLEEP
    Serial.print("Uaaah. I'm tired. Going back to bed for ");
    Serial.print(minutes);
    Serial.println(" minutes. Good night!");
    Serial.println("---");
    ESP.deepSleep(minutes * 60 * 1000000);
    delay(100);
  #endif
}


/**
 * Dump some information on startup.
 */
void splashScreen() {
  for (int i=0; i<=5; i++) Serial.println();
  Serial.println("#######################################");
  Serial.print("# ");
  Serial.print(userAgent);
  Serial.print(" - v. ");
  Serial.println(clientVer);
  Serial.println("# -----------");
  Serial.println("# Chris Figge (flazer)");
  Serial.println("# Mail: info@flazer.net");
  Serial.println("# -----------");
  Serial.print("# DeviceName: ");
  Serial.println(espName);
  Serial.print("# Configured Endpoint: ");
  Serial.println(mqtt_server);
  Serial.println("#######################################");
  for (int i=0; i<2; i++) Serial.println();
}

