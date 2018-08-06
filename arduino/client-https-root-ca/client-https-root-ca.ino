#include <BME280I2C.h>
#include <Wire.h>
#include <time.h>
#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <WiFiClientSecure.h>

#include "settings.h"
#include "CACert.h"

ESP8266WiFiMulti WiFiMulti;
WiFiClientSecure client;

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
  Serial.println();
  loadCert();
}


/**
 * Looping Louie
 */
void loop() {
  sendSensorData(); //send data
  goToBed(minutes2sleep); //sending into deep sleep
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
      Serial.println("Could not connect to WiFi. Sending device to bed.");
      goToBed(5);
    }
  }

  Serial.println("");
  Serial.println("WiFi connected");  
  Serial.print("IP address: ");
  Serial.println(WiFi.localIP());
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
  
  String payload = "token=" + token + "&temperature=" + String(temp) + "&humidity=" + String(hum) + "&pressure=" + String(pres);

  Serial.println("---");
  Serial.println("[HTTPS] Start connection info request...");

  Serial.println("[HTTPS] Sending data.");

  //Connect to server
  if (!client.connect(host, port)) {
    Serial.println("[HTTPS] ERROR: Connection failed. Trying again next time.");
    return;
  }

  // Verify validity of server's certificate
  if (client.verifyCertChain(host)) {
    Serial.println("[HTTPS] Server certificate verified.");
  } else {
    Serial.println("[HTTPS] ERROR: Certificate verification failed!");
    return;
  }

  Serial.print("[HTTPS] URL: ");
  Serial.println(url);
  Serial.print("[HTTPS] Payload: ");
  Serial.println(payload);
  Serial.println("[HTTPS] Requesting...");

  client.println("POST " + String(url) + " HTTP/1.1");
  client.println("Host: " + String(host));
  client.println("User-Agent: " + String(userAgent));
  client.println("Cache-Control: no-cache");
  client.println("Content-Type: " + String(contenttype));
  client.print("Content-Length: ");
  client.println(payload.length());
  client.println();
  client.println(payload);

  Serial.println("[HTTPS] Request sent.");

  while (client.connected()) {
    String line = client.readStringUntil('\n');
    if (line == "\r") {
      Serial.println("[HTTPS] headers received");
      break;
    }

    Serial.println("[HTTPS] Response: " + line);
  }

  Serial.println("[HTTPS] End connection.");
  Serial.println("---");
}


/**
 * Sending device into deep sleep
 */
void goToBed (int minutes) {
  #ifdef FORCE_DEEPSLEEP
    Serial.print("Uaaah. I'm tired. Going back to bed for ");
    Serial.print(minutes);
    Serial.print(" minutes. Good night!");
    ESP.deepSleep(minutes * 60 * 1000000);
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
  Serial.println(String(host) + String(url));
  Serial.println("#######################################");
  for (int i=0; i<2; i++) Serial.println();
}


/**
 * Load CA root certificate
 */
void loadCert() {
  // Synchronize time useing SNTP. This is necessary to verify that
  // the TLS certificates offered by the server are currently valid.
  Serial.println("[HTTPS] Setting time using SNTP");
  configTime(8 * 3600, 0, "pool.ntp.org", "time.nist.gov");
  time_t now = time(nullptr);
  while (now < 8 * 3600 * 2) {
    delay(500);
    Serial.print(".");
    now = time(nullptr);
  }
  Serial.println("");
  struct tm timeinfo;
  gmtime_r(&now, &timeinfo);
  Serial.print("[HTTPS] Current time: ");
  Serial.print(asctime(&timeinfo));

  // Load root certificate in DER format into WiFiClientSecure object
  bool res = client.setCACert_P(caCert, caCertLen);
  if (!res) {
    Serial.println("[HTTPS] Failed to load root CA certificate! Sending device to bed.");
    goToBed(5);
  }
}

