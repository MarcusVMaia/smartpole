/*
 * SmartPole.h
 *
 *  Created on: Nov 16, 2016
 *      Author: root
 */

#ifndef SMARTPOLE_H_
#define SMARTPOLE_H_
#include <stdint.h>

class SmartPole {
public:
	SmartPole();
	virtual ~SmartPole();
	uint8_t temp, umid;
	char lat[16];
	char lon[16];
	float gasMQ4, gasMQ7,rain, light, noise;
	float particles;
};

#endif /* SMARTPOLE_H_ */
