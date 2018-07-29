// Network settings
const char* ssid = "SSID";
const char* password = "PASSWORD";
const char* espName = "HOST_NAME";

// Minutes to sleep between updates
int minutes2sleep = 15;

// Use SSL
bool ssl_enabled = false;

// Device Token (unencrypted)
String token = "SECRET_DEVICE_TOKEN";

// Endpoint settings
const char* url = "YOUR_API_URL";
const char* fingerprint = "79 B1 29 4A 6F CD DB 96 C8 96 03 36 AA 2F F7 D6 08 82 43 71"; //certificate fingerprint (for https usage)
const char* contenttype = "application/x-www-form-urlencoded";

String userAgent = "Figge-WiFi Weatherstation - HTTP(S)-Client";
String clientVer = "0.3";
