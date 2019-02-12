/*
 * DHT.h
 *
 *  Created on: Jan 18, 2017
 *      Author: root
 */

#ifndef DHT_H_
#define DHT_H_
#include "Arduino.h"
/* DHT library

MIT license
written by Adafruit Industries
*/

// Uncomment to enable printing out nice debug messages.
//#define DHT_DEBUG

// Define where debug output will be printed.
#define DEBUG_PRINTER Serial

// Setup debug printing macros.
#ifdef DHT_DEBUG
  #define DEBUG_PRINT(...) { DEBUG_PRINTER.print(__VA_ARGS__); }
  #define DEBUG_PRINTLN(...) { DEBUG_PRINTER.println(__VA_ARGS__); }
#else
  #define DEBUG_PRINT(...) {}
  #define DEBUG_PRINTLN(...) {}
#endif


class DHT {
  public:
   DHT(uint8_t pin, uint8_t count=6);
   void begin(void);
   float readTemperature(bool force=false);
   float convertCtoF(float);
   float convertFtoC(float);
   float computeHeatIndex(float temperature, float percentHumidity, bool isFahrenheit=true);
   float readHumidity(bool force=false);
   bool read(bool force=false);

 private:
  uint8_t data[5];
  uint8_t _pin, _type;
  #ifdef __AVR
    // Use direct GPIO access on an 8-bit AVR so keep track of the port and bitmask
    // for the digital pin connected to the DHT.  Other platforms will use digitalRead.
    uint8_t _bit, _port;
  #endif
  uint32_t _lastreadtime, _maxcycles;
  bool _lastresult;

  uint32_t expectPulse(bool level);

};

class InterruptLock {
  public:
   InterruptLock() {
    noInterrupts();
   }
   ~InterruptLock() {
    interrupts();
   }

};

#endif /* DHT_H_ */
