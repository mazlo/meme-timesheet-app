#!/bin/bash

lftp -u tim@mazlo.de,of97cXJIZNW ftp.mazlo.de -e "set ftp:ssl-allow no; mirror -R public public ; quit"
