/*
 * PoleI2C.cpp
 *
 *  Created on: Nov 13, 2016
 *      Author: root
 */
#include "PoleI2C.h"

// Escrita de DADO no registrador REG do dispositivo ADR
void i2c_wr(uint8_t adr, uint8_t reg, uint8_t dado){
	Wire.beginTransmission(adr);
	Wire.write(reg);
	Wire.write(dado);
	Wire.endTransmission();
}

// Leitura de N dados a partir do registrador REG do dispositivo ADR
void i2c_rd_rep(uint8_t adr, uint8_t reg, uint8_t n, uint8_t *vet){
	uint8_t cont;
	Wire.beginTransmission(adr);
	Wire.write(reg);
	Wire.endTransmission(false);
	Wire.requestFrom(adr, n);
	for(cont = 0; cont<n; cont++) vet[cont] = Wire.read();
}


