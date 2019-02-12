
#include "TCC_Smart_Pole.h"

// Pinagem
#define RTC 52
#define CHUVA A11	//Pino sensor de chuva
#define MQ7 A9
#define MQ4 A8
#define LUZ A10
#define DHT_pin 31
#define NOISE A12
#define PARTICLE_VCC 4
#define PARTICLE 3
#define PARTICLE_GND 2

#define DEBUG_ON

#include "inetGSM.h"

//GPRS
InetGSM inet;

boolean started = false;
char smsbuffer[160];
char n[20];

byte valor;

//Criação de Objetos Globais
// CRIA OBJETO POSTE
SmartPole meuPoste;

//Modulo RTC DS1307 ligado as portas 24(SDA) e 22(SCL) do Arduino
DS1307 rtc(50, 48);

//Sendor de temperatura e humidade DHT11
DHT poste_dht(DHT_pin,"DHT11");

//Timer tick de 1 millisegundo
volatile uint16_t msTick = 0;
volatile uint16_t minTick = 0;

//Setup functions
void setup_RTC(){
	pinMode(RTC,OUTPUT);
	digitalWrite(RTC,HIGH);		// RTC Vcc
	// Inicializa RTC
	rtc.halt(false); //Aciona o relogio
	//As linhas abaixo setam a data e hora do modulo
	//e podem ser comentada apos a primeira utilizacao
	//rtc.setDOW(TUESDAY);      //Define o dia da semana
	//rtc.setTime(13, 16, 0);     //Define o horario
	//rtc.setDate(13, 12, 2016);   //Define o dia, mes e ano
}

void setup_DHT(){
	//DHT inicialização
	poste_dht.begin();
}

void setup_MQ(){
	// Sensores de gas
	pinMode(MQ7,INPUT);
	pinMode(MQ4,INPUT);
}

void setup_chuva(){
	pinMode(CHUVA,INPUT);
}

void setup_LDR(){
	pinMode(LUZ,INPUT);
}

void setup_noise(){
	pinMode(NOISE,INPUT);
}

void setup_particle(){
	pinMode(PARTICLE,INPUT);
	pinMode(PARTICLE_VCC,OUTPUT);
	pinMode(PARTICLE_GND,OUTPUT);
	digitalWrite(PARTICLE_VCC, HIGH);
	digitalWrite(PARTICLE_VCC, LOW);
}

void setup_timer_1s(){
	//sei(); // Enable interrupts globais
	//Setup Timer2 to fire every 1ms
	  TCCR2B = 0x00;        //Disbale Timer2 while we set it up
	  TCNT2  = 130;         //Reset Timer Count to 130 out of 255
	  TIFR2  = 0x00;        //Timer2 INT Flag Reg: Clear Timer Overflow Flag
	  TIMSK2 = 0x01;        //Timer2 INT Reg: Timer2 Overflow Interrupt Enable
	  TCCR2A = 0x00;        //Timer2 Control Reg A: Wave Gen Mode normal
	  TCCR2B = 0x05;        //Timer2 Control Reg B: Timer Prescaler set to 128
}

float read_particle(int DUST_SENSOR_DIGITAL_PIN)
{
	unsigned long duration;
	unsigned long starttime;
	unsigned long endtime;
	unsigned long sampletime_ms = 30000;
	unsigned long lowpulseoccupancy = 0;
	float ratio = 0;

	starttime = millis();

	while (1) {
		duration = pulseIn(DUST_SENSOR_DIGITAL_PIN, LOW);
		lowpulseoccupancy += duration/1000.0; //ms
		endtime = millis();

		if ((endtime-starttime) > sampletime_ms) {
			ratio = (100.0*lowpulseoccupancy)/((endtime-starttime));  // Integer percentage 0=>100
			//ratio = (100*lowpulseoccupancy)/sampletime_ms;
			Serial.println(ratio);
			float concentration = 1.1*pow(ratio,3)-3.8*pow(ratio,2)+520*ratio+0.62; // using spec sheet curve
			return(concentration);
		}
	}
}

String get_coord(){
	char coord[64];

	gsm.SendATCmdWaitResp("AT+SAPBR=3,1,'CONTYPE','GPRS'", 5000, 10, "", 3);
	//gsm.SendATCmdWaitResp("AT+SAPBR=3,1,'APN','CMNET'", 5000, 10, resp, 3);
	//memset(&resp[0], 0, sizeof(resp));
	gsm.SendATCmdWaitResp("AT+SAPBR=1,1", 5000, 10,"", 3);
	gsm.SendATCmdWaitResp("AT+SAPBR=2,1", 5000, 10, "", 3);
	gsm.SendATCmdWaitResp_GPS("AT+CIPGSMLOC=1,1", 5000, 10, coord, 3);
	gsm.SendATCmdWaitResp("AT+SAPBR=0,1", 5000, 10, "", 3);

	//Separate lat and long
	int n = 0;
	char lat[16]="";
	char lon[16]="";

	for(int i = 0; i<64; i++){

		if(coord[i]==','){
			n++;
			if(n==1){
				for(int j = 1;coord[i+j]!=',';j++){
					lon[j-1] = coord[i+j];
				}
			}
			else if(n==2){
				for(int j = 1;coord[i+j]!=',';j++){
					lat[j-1] = coord[i+j];
				}
				break;
			}
		}
	}

	Serial.println(lat);
	Serial.println(lon);
	strcpy(meuPoste.lat,lat);
	strcpy(meuPoste.lon,lon);
	//meuPoste.lat = lat;
	//meuPoste.lon = lon;

	Serial.println(meuPoste.lat);
	Serial.println(meuPoste.lon);

	return coord;
}

void read_sensors(){
	// Leitura dos sensores de gas
	meuPoste.gasMQ4 = ((((10000.0-300)/1024.0)*analogRead(MQ4))+300)/1000.00; //10 bit adc from 300 to 10000 ppb
	meuPoste.gasMQ7 = ((((2000.0-20)/1024.0)*analogRead(MQ7))+20)/1000.00; //10 bit adc from 10 to 2000 ppb
	// Leitura do DHT11
	meuPoste.temp = poste_dht.readTemperature();
	meuPoste.umid = poste_dht.readHumidity();
	// Leitura LDR
	meuPoste.light = (100.0/1024.0)*analogRead(LUZ); //intensidade da luz em percentual
	// Leitura Chuva
	meuPoste.rain = (100.0/1024.0)*(1023-analogRead(CHUVA)); //intensidade de chuva em percentual
	// Leitura Poluicao Sonora
	meuPoste.noise = analogRead(NOISE);
	// Leitura Poluicao Particulas Suspensas (mg/m3)
	meuPoste.particles = read_particle(PARTICLE);
	if(strlen(meuPoste.lat)==0) get_coord();
}

void print_sensors(){
	Serial.println("========= Dados dos Sensores ========");
	Serial.print("Hora : ");
	Serial.print(rtc.getTimeStr());
	Serial.print(" ");
	Serial.print("Data : ");
	Serial.print(rtc.getDateStr(FORMAT_SHORT));
	Serial.print(" ");
	Serial.println(rtc.getDOWStr(FORMAT_SHORT));
	Serial.print("Latitude: ");
	Serial.print(meuPoste.lat);
	Serial.print(" | Longitude: ");
	Serial.println(meuPoste.lon);
	Serial.print("Gas MQ4: ");
	Serial.println(meuPoste.gasMQ4);
	Serial.print("Gas MQ7: ");
	Serial.println(meuPoste.gasMQ7);
	Serial.print("Temperatura: ");
	Serial.println(meuPoste.temp);
	Serial.print("Umidade: ");
	Serial.println(meuPoste.umid);
	Serial.print("Incidencia de luz: ");
	Serial.println(meuPoste.light);
	Serial.print("Chuva: ");
	Serial.println(meuPoste.rain);
	Serial.print("Ruido: ");
	Serial.println(meuPoste.noise);
	Serial.print("Qualidade do Ar: ");
	Serial.println(meuPoste.particles);

}

String generate_string(){
	char msg2send[256]="";
	// Create data packet
	// "dados,TEMP,UMID,RAIN,MQ4,MQ7,LATITUDE,LONGITUDE\0"
	char temp[10];

	memset(&temp[0], 0, sizeof(temp));
	sprintf(temp,"%d",meuPoste.temp);
	strcat(msg2send,"TEMPERATURA=");
	strcat(msg2send,temp);
	strcat(msg2send,"&");

	memset(&temp[0], 0, sizeof(temp));
	sprintf(temp,"%d",meuPoste.umid);
	strcat(msg2send,"UMIDADE=");
	strcat(msg2send,temp);
	strcat(msg2send,"&");

	memset(&temp[0], 0, sizeof(temp));
	sprintf(temp,"%d.%02d",(int)meuPoste.rain,(int)(meuPoste.rain*100) - ((int)meuPoste.rain)*100);
	strcat(msg2send,"CHUVA=");
	strcat(msg2send,temp);
	strcat(msg2send,"&");

	memset(&temp[0], 0, sizeof(temp));
	sprintf(temp,"%d.%02d",(int)meuPoste.light,(int)(meuPoste.light*100) - ((int)meuPoste.light)*100);
	strcat(msg2send,"LUZ=");
	strcat(msg2send,temp);
	strcat(msg2send,"&");

	memset(&temp[0], 0, sizeof(temp));
	sprintf(temp,"%d.%02d",(int)meuPoste.noise,(int)(meuPoste.noise*100) - ((int)meuPoste.noise)*100);
	strcat(msg2send,"RUIDO=");
	strcat(msg2send,temp);
	strcat(msg2send,"&");

	memset(&temp[0], 0, sizeof(temp));
	sprintf(temp,"%d.%02d",(int)meuPoste.particles,(int)(meuPoste.particles*100) - ((int)meuPoste.particles)*100);
	strcat(msg2send,"AR=");
	strcat(msg2send,temp);
	strcat(msg2send,"&");

	memset(&temp[0], 0, sizeof(temp));
	sprintf(temp,"%d.%02d",(int)meuPoste.gasMQ4,(int)(meuPoste.gasMQ4*100) - ((int)meuPoste.gasMQ4)*100);
	strcat(msg2send,"GASMQ4=");
	strcat(msg2send,temp);
	strcat(msg2send,"&");

	memset(&temp[0], 0, sizeof(temp));
	sprintf(temp,"%d.%02d",(int)meuPoste.gasMQ7,(int)(meuPoste.gasMQ7*100) - ((int)meuPoste.gasMQ7)*100);
	strcat(msg2send,"GASMQ7=");
	strcat(msg2send,temp);
	strcat(msg2send,"&");

	strcat(msg2send,"LATITUDE=");
	strcat(msg2send,meuPoste.lat);
	strcat(msg2send,"&");

	strcat(msg2send,"LONGITUDE=");
	strcat(msg2send,meuPoste.lon);

	return msg2send;
}

void SubmitHttpRequest(){
	char msg[50];
	memset(msg,0,sizeof(msg));
	int numdata;
	if (inet.attachGPRS("timbrasil.br", "tim", "tim"))
	  Serial.println(F("status=Conectado..."));
	else Serial.println(F("status=Nao conectado !!"));
	delay(1000);
	String valor = generate_string();
	char temp_string[valor.length()+1];
	valor.toCharArray(temp_string, valor.length()+1);
	Serial.println(temp_string);
	numdata = inet.httpPOST("smartpoleunb.com", 80, "/add.php",temp_string, msg, 50);
	//numdata = inet.httpGET("posteunb.epizy.com", 80, "/add.php?TEMPERATURA=23", msg, 50);
	Serial.println(msg);
	delay(5000);
}

void powerUpOrDown()
{
  //Liga o GSM Shield
  Serial.print(F("Liga GSM..."));
  pinMode(6, OUTPUT);
  digitalWrite(6, LOW);
  delay(1000);
  digitalWrite(6, HIGH);
  delay(1000);
  Serial.println(F("OK!"));
  digitalWrite(6, LOW);
  delay(500);
}

void setup_GPRS(){
	powerUpOrDown();
	Serial.println(F("Testando GSM Shield SIM900"));
	if (gsm.begin(2400))
	{
	  Serial.println(F("\nstatus=READY"));
	  started = true;
	}
	else Serial.println(F("\nstatus=IDLE"));
}


void setup(void){
	Serial.begin(19200);
	delay(500);

	Serial.println("Setup RTC\n");
	setup_RTC();
	Serial.println("Setup DHT\n");
	setup_DHT();
	Serial.println("Setup MQ\n");
	setup_MQ();
	Serial.println("Setup Motion\n");
	Serial.println("Setup GPRS\n");
	setup_GPRS();// Connect to GPRS
	Serial.println("Setup Timer\n"); //DANDO GALHO
	setup_timer_1s();
	Serial.println("End of setup\n");

	delay(500);
}

// Timer Interrupt
//Timer2 Overflow Interrupt Vector, called every 1ms
ISR(TIMER2_OVF_vect) {
  msTick++;               //Increments the interrupt counter
  if(msTick > 59999){
    msTick = 0;           //Resets the interrupt counter
    minTick++;              //Increment the minute counter
	Serial.println(minTick);
  }
  TCNT2 = 130;           //Reset Timer to 130 out of 255
  TIFR2 = 0x00;          //Timer2 INT Flag Reg: Clear Timer Overflow Flag
};


void loop(void)
{

	if(minTick>14){
		read_sensors();
		SubmitHttpRequest();
		minTick = 0;
	}

	if (Serial.available())
	    switch(Serial.read())
	   {
	     case 'h':
	    	 Serial.print("HTTP\n");
	    	 SubmitHttpRequest();
	    	 break;
	     case 'r':
	    	 Serial.print("Read\n");
	    	 read_sensors();
	    	 print_sensors();
	    	 break;
	     case 'l':
	    	 Serial.println(get_coord());
	   }

}
