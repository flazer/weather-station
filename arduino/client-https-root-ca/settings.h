// Network settings
const char* ssid = "SSID";
const char* password = "PASSWORD";
const char* espName = "HOST_NAME";

// Minutes to sleep between updates
int minutes2sleep = 15;

// Device Token (unencrypted)
String token = "SECRET_DEVICE_TOKEN";

// Endpoint settings
const char* host = "www.domain.tld"; // example: www.domain.tld
const int   port = 443; // Standard ssl-port: 443
const char* url  = "/test/fu.php"; // absolute path to your api-endpoint (example: /test/fu.php)

const char* contenttype = "application/x-www-form-urlencoded";

String userAgent = "Figge-WiFi Weatherstation - HTTPS-Root-CA-Client";
String clientVer = "0.1";
