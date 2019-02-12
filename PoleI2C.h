/*
 * PoleI2C.h
 *
 *  Created on: Nov 13, 2016
 *      Author: root
 */

#ifndef POLEI2C_H_
#define POLEI2C_H_

#include "Wire.h"
#include <stdint.h>

// Escrita de DADO no registrador REG do dispositivo ADR
void i2c_wr(uint8_t adr, uint8_t reg, uint8_t dado);

// Leitura de N dados a partir do registrador REG do dispositivo ADR
void i2c_rd_rep(uint8_t adr, uint8_t reg, uint8_t n, uint8_t *vet);

#endif /* POLEI2C_H_ */
