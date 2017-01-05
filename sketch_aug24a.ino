// include the library code:
#include <TimerOne.h>
#include <MD5.h>
#include <string.h>
#include <ctype.h>
#include <stdio.h>

boolean flag;
int k=0;
int n=0;
int nref=255;
int nkp=1;
int nki=10;
int nkd=0;


//Controlador PID e Tempo Real
int ledPin = 11;
int analogPin = 3;
int y0,y1,y2,e0,e1,e2;
double kp=1, ki=10, kd=0  , T=0.001, ref=255;

void PID(void){
  int an = analogRead(0);
  digitalWrite(8,HIGH);
  e2=e1;
  e1=e0;
  
  e0=ref-(an/4);
  y2=y1;
  y1=y0;
  y0 = y2 + (kp * (e0 -e2)) + (ki * (e0 + (2*e1) + e2) * T/2) + (kd * (e0 - (2*e1) + e2) * 2/T);
  if(y0 > 255){ 
    
    analogWrite(ledPin,255);
  }else if(y0 < 0){ 
    analogWrite(ledPin,0);
  }else{
    analogWrite(ledPin,y0);
  }
  digitalWrite(8,LOW);
}

void setup() { 
  Serial.begin(9600); //starts serial communication
  pinMode(0,INPUT);
  pinMode(ledPin, OUTPUT);
  pinMode(8, OUTPUT);
  pinMode(7, OUTPUT);
  Timer1.initialize(1000);
  Timer1.attachInterrupt(PID);
}

#define pkgSIZE 70
//+1 -> \0
#define md5SIZE (32+1)
#define saltSIZE (10+1)    
static char salt[] = "tfgECO2016";

#define msgSIZE (pkgSIZE - md5SIZE + saltSIZE) 


#define HEADER 4
#define cmdSIZE (msgSIZE - HEADER)

int pos;
char pkg[pkgSIZE];
char pkgmd5[md5SIZE];
char pkgmsg[msgSIZE];
char pkgcmd[cmdSIZE];

boolean StringSerial(){
  char ch; 
  while(Serial.available() > 0){
    ch = Serial.read();
    Serial.print(ch);
    if(ch == '$'){
      pos=0;
    }
    pkg[pos] = ch;
    pos++;   
    if(ch == '\n'){
      int i;
      int md5POS;
      pkg[pos]='\0';

      //find msg
      for(i=0;i<pkgSIZE;i++){
        pkgmsg[i] = pkg[i];
        if(pkg[i] == '#'){
          md5POS = i+1;
          break;
        }
      }
      for(i= 0;i<saltSIZE;i++){
        pkgmsg[i+md5POS] = salt[i];
      }

      //find md5
      for(i = md5POS;i<pkgSIZE;i++){
        if(pkg[i] == '\n'){
          break;
        }
        pkgmd5[i-md5POS] = pkg[i];
      }          
      pkgmd5[i-md5POS] = '\0';


      //find cmd
      for(i=5;pkg[i] != '#';i++){
        pkgcmd[i-5] = pkg[i];
      }
      pkgcmd[i-5] = '\0';

      pos=0;
      return true;
    }
    if(pos > pkgSIZE){

      Serial.print("Send the message again!");
      pos=0;
      return false;
    }
  }

  return false;
}

void loop() {
  
  if(StringSerial()){   

    unsigned char *hash=MD5::make_hash(pkgmsg);
    char *md5 = MD5::make_digest(hash,16);

    flag = true;
    for(int i=0; i<32; i++){
      if(pkgmd5[i] != md5[i]){ 
        flag = false;
      }
    }
    if(flag){

      if(!strcmp(pkgcmd,"digitalON")){
        digitalWrite(7,HIGH);
      }
      else{
        if(!strcmp(pkgcmd,"digitalOFF")){
          digitalWrite(7,LOW);
        }
        else{
          if(!strcmp(pkgcmd,"get")){
            Serial.print(" Kp = ");Serial.print(kp);
            Serial.print(" Ki = ");Serial.print(ki);
            Serial.print(" Kd = ");Serial.print(kd);
          }
          else{
            k=0;
            n=0;
            while(pkgcmd[k] >= '0' && pkgcmd[k] <= '9'){
              n=n*10 + (pkgcmd[k] - '0');
              k++;
              nkd=n;
              if(pkgcmd[k] == '&'){
                k++;
                nref=n;
                n=0;
              }else{
                if(pkgcmd[k] == '+'){
                  k++;
                  nkp=n;
                  n=0;
                }else{  
                  if(pkgcmd[k] == '*'){
                    k++;
                    nki=n;
                    n=0;  
                  }  
                }
              }     
            }
            ref=nref;
            kp=nkp;
            ki=nki;
            kd=nkd;
          }
        }
      }
    }
    else{
      Serial.print("MD5 MISMATCH");
      Serial.print(" Send the message again!");
    }

  }
}




