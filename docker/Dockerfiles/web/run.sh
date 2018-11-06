#!/bin/bash

function clearCache {
	echo "limpiando cache y definiendo permisos..."
	    
	cd /var/www/html
	chown -R docker:www-data .
}

echo "Ejecutando composer install..."
composer install

echo "Ejecutando npm install..."
npm install

clearCache

sudo apache2-foreground
