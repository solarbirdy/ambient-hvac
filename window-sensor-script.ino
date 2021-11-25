/*
  based on "Serial Call and Response in ASCII" for Arduino
  by Tom Igoe and Scott Fitzgerald who said
  Thanks to Greg Shakar and Scott Fitzgerald for the improvements
*/

int firstSensor = 0;    // first analog sensor
int secondSensor = 0;   // second analog sensor
int thirdSensor = 0;    // digital sensor
int inByte = 0;         // incoming serial byte

void setup() {
  int i;
  
  // start serial port at 9600 bps and wait for port to open:
  Serial.begin(9600);
  while (!Serial) {
    ; // wait for serial port to connect. Needed for native USB port only
  }

  // set all digital pins to input
  for (i=2; i<14; i++) {
    pinMode(i, INPUT);
  }
}

void loop() {
  int i;
  // if we get a valid byte, read analog ins:
  if (Serial.available() > 0) {
    // get incoming byte:
    inByte = Serial.read();

    Serial.print("{");
    for (i=2; i<14; i++) {
      Serial.print('"');
      Serial.print("digitalpin");
      Serial.print(i);
      Serial.print('"'); Serial.print(':'); Serial.print('"');
      Serial.print(digitalRead(i));
      Serial.print('"');
      if (i != 13) Serial.print(',');
      }
    Serial.println("}");
    }
  }
