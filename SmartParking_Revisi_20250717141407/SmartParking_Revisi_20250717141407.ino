// ===== PUSTAKA / LIBRARIES =====
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>

// ===== KONFIGURASI PENGGUNA =====
const char* ssid = "NAMA_WIFI_ANDA";
const char* password = "PASSWORD_WIFI_ANDA";
String serverName = "http://IP_LOKAL_ANDA/parkir-pintar"; // <-- GANTI DENGAN IP LOKAL & NAMA FOLDER ANDA

// ===== PENGATURAN PIN PERANGKAT KERAS =====
#define PIN_TOMBOL D3
#define PIN_TRIG_1 D5
#define PIN_ECHO_1 D6
#define PIN_TRIG_2 D7
#define PIN_ECHO_2 D8

// ===== PENGATURAN OLED =====
#define SCREEN_WIDTH 128
#define SCREEN_HEIGHT 64
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, -1);

// ===== PENGATURAN LOGIKA PARKIR =====
const int JARAK_MAKSIMAL_CM = 15;

// =========== FUNGSI SETUP ==========
void setup() {
  Serial.begin(115200);
  Serial.println("\nMemulai Sistem Parkir Pintar (Versi State-Aware)...");

  pinMode(PIN_TOMBOL, INPUT_PULLUP);
  pinMode(PIN_TRIG_1, OUTPUT);
  pinMode(PIN_ECHO_1, INPUT);
  pinMode(PIN_TRIG_2, OUTPUT);
  pinMode(PIN_ECHO_2, INPUT);

  if(!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) { 
    Serial.println(F("Inisialisasi OLED gagal"));
    for(;;);
  }

  display.clearDisplay();
  display.setTextSize(2);
  display.setTextColor(WHITE);
  display.setCursor(20, 10);
  display.println("PARKIR");
  display.setCursor(20, 30);
  display.println("PINTAR");
  display.display();
  delay(1000);

  WiFi.begin(ssid, password);
  Serial.print("Menyambungkan ke WiFi.");
  display.clearDisplay();
  display.setTextSize(1);
  display.setCursor(0,0);
  display.println("Menyambungkan ke WiFi...");
  display.display();
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
    display.print(".");
    display.display();
  }
  Serial.println("\nWiFi Terhubung!");
  Serial.print("Alamat IP ESP8266: ");
  Serial.println(WiFi.localIP());

  tampilkanLayarUtama();
  randomSeed(analogRead(0));
}

// =========== FUNGSI LOOP ===========
void loop() {
  if (digitalRead(PIN_TOMBOL) == LOW) {
    Serial.println("\nTombol ditekan! Memulai pengecekan sistem...");
    
    display.clearDisplay();
    display.setCursor(0,0);
    display.setTextSize(2);
    display.println("Sinkron\nData...");
    display.display();

    // Langkah 1: Bertanya ke server, slot mana yang sudah dikonfirmasi?
    bool slot1_terisi_server = false;
    bool slot2_terisi_server = false;
    cekSlotTerisiDariServer(slot1_terisi_server, slot2_terisi_server);
    
    display.clearDisplay();
    display.setCursor(0,0);
    display.println("Mengecek\nSensor...");
    display.display();
    delay(500);

    // Langkah 2: Mengecek sensor fisik
    bool slot1_terisi_fisik = getJarak(PIN_TRIG_1, PIN_ECHO_1) <= JARAK_MAKSIMAL_CM;
    bool slot2_terisi_fisik = getJarak(PIN_TRIG_2, PIN_ECHO_2) <= JARAK_MAKSIMAL_CM;

    // Langkah 3: Menggabungkan data. Slot dianggap kosong HANYA JIKA kosong secara fisik DAN logis.
    bool slot1_benar_kosong = !slot1_terisi_fisik && !slot1_terisi_server;
    bool slot2_benar_kosong = !slot2_terisi_fisik && !slot2_terisi_server;
    
    // Tentukan slot mana yang akan disarankan (prioritaskan slot 1)
    int slot_disarankan = 0;
    if (slot1_benar_kosong) {
        slot_disarankan = 1;
    } else if (slot2_benar_kosong) {
        slot_disarankan = 2;
    }

    // Jika ada slot yang bisa disarankan
    if (slot_disarankan > 0) {
        prosesBuatTiket(slot_disarankan);
    } else {
        // Jika tidak ada slot yang benar-benar kosong
        Serial.println("Semua slot penuh (kombinasi fisik & data server).");
        display.clearDisplay();
        display.setCursor(15, 20);
        display.setTextSize(2);
        display.println("SEMUA\nSLOT PENUH");
        display.display();
        delay(3000);
    }
    
    tampilkanLayarUtama();
    delay(500); // Mencegah pembacaan tombol ganda
  }
}

// ======== FUNGSI-FUNGSI BANTU ========

/**
 * @brief Bertanya ke server untuk mendapatkan daftar slot yang sudah terisi.
 * @param slot1_terisi Variabel boolean yang akan diisi status slot 1.
 * @param slot2_terisi Variabel boolean yang akan diisi status slot 2.
 */
void cekSlotTerisiDariServer(bool &slot1_terisi, bool &slot2_terisi) {
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;
    String url = serverName + "/includes/get_status_slot.php";
    
    Serial.println("Meminta status slot dari: " + url);
    http.begin(client, url);
    int httpCode = http.GET();

    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      Serial.println("Respons JSON: " + payload);

      DynamicJsonDocument doc(256);
      DeserializationError error = deserializeJson(doc, payload);

      if (error) {
        Serial.print("deserializeJson() gagal: ");
        Serial.println(error.c_str());
        return;
      }
      
      JsonArray slot_terisi_array = doc["slot_terisi"];
      for (int slot : slot_terisi_array) {
        if (slot == 1) slot1_terisi = true;
        if (slot == 2) slot2_terisi = true;
      }
      Serial.printf("Status dari server: Slot 1 Terisi=%d, Slot 2 Terisi=%d\n", slot1_terisi, slot2_terisi);
      
    } else {
      Serial.printf("HTTP GET gagal, error: %d\n", httpCode);
    }
    http.end();
  } else {
    Serial.println("Tidak bisa cek status, WiFi tidak terhubung.");
  }
}

/**
 * @brief Membuat tiket, menampilkan di OLED, dan mengirim pra-registrasi ke server.
 * @param slot_saran Nomor slot yang disarankan untuk pengguna.
 */
void prosesBuatTiket(int slot_saran) {
  int kodeParkir = random(1000, 9999);
  
  // Kirim data ke web server dengan slot = 0 (belum dikonfirmasi)
  kirimKeWebServer(kodeParkir, 0);

  // Tampilkan saran & kode di OLED
  display.clearDisplay();
  display.setTextSize(1);
  display.setCursor(0, 0);
  display.println("Parkir Tersedia");
  display.setCursor(0, 12);
  display.print("Saran Slot: ");
  display.setTextSize(2);
  display.println(slot_saran);
  display.setTextSize(1);
  display.setCursor(0, 35);
  display.println("Kode Anda:");
  display.setTextSize(2);
  display.setCursor(28, 48);
  display.println(kodeParkir);
  display.display();
  
  delay(10000); // Tampilkan tiket selama 10 detik
}

/**
 * @brief Mengirim data tiket pra-registrasi ke server.
 * @param kode Kode unik parkir.
 * @param nomorSlot Nomor slot (selalu 0 untuk pra-registrasi).
 */
void kirimKeWebServer(int kode, int nomorSlot) {
  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;
    String urlTujuan = serverName + "/catat_masuk.php?kode=" + String(kode) + "&slot=" + String(nomorSlot);
    
    Serial.print("Mengirim HTTP GET ke: ");
    Serial.println(urlTujuan);

    http.begin(client, urlTujuan);
    int httpCode = http.GET();

    if (httpCode > 0) {
      String payload = http.getString();
      Serial.println("Kode Respons HTTP: " + String(httpCode));
      Serial.println("Respons dari Server: " + payload);
    } else {
      Serial.println("Gagal mengirim data, error: " + http.errorToString(httpCode));
    }
    http.end();
  } else {
    Serial.println("WiFi tidak terhubung. Data gagal dikirim.");
  }
}

/**
 * @brief Mengukur jarak menggunakan sensor ultrasonik.
 * @return Jarak dalam centimeter. Mengembalikan 0 jika gagal baca.
 */
long getJarak(int trigPin, int echoPin) {
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);
  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);
  long durasi = pulseIn(echoPin, HIGH, 30000);
  if (durasi == 0) {
    return 0;
  }
  return durasi * 0.034 / 2;
}

/**
 * @brief Menampilkan layar sambutan di OLED.
 */
void tampilkanLayarUtama() {
  display.clearDisplay();
  display.setTextColor(WHITE);
  display.setTextSize(2);
  display.setCursor(18, 8);
  display.println("Selamat");
  display.setCursor(24, 28);
  display.println("Datang!");
  display.setTextSize(1);
  display.setCursor(0, 50);
  display.println(" Tekan Tombol u/ Masuk");
  display.display();
}