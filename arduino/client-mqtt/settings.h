// Network settings
const char* ssid = "SSID";
const char* password = "PASSWORD";
const char* espName = "HOST_NAME";

// Endpoint settings
const char* mqtt_server = "SERVER_IP";
const char* mqtt_user = "USERNAME";
const char* mqtt_password = "PASSWORD";
const char* mqtt_clientId = "CLIENTID";

// Available Topics
const char* topic_humidity = "CLIENTID/humidity";
const char* topic_temperature = "CLIENTID/temperature";
const char* topic_pressure = "CLIENTID/pressure";

// Minutes to sleep between updates
int minutes2sleep = 15;

String userAgent = "Figge-WiFi Weatherstation - MQTT-Client";
String clientVer = "0.2";
