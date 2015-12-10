#!/bin/bash

lftp -u tim@mazlo.de,of97cXJIZNW ftp.mazlo.de -e "set ftp:ssl-allow no; mirror -R app/views app/views ; quit"
lftp -u tim@mazlo.de,of97cXJIZNW ftp.mazlo.de -e "set ftp:ssl-allow no; mirror -R app/controllers app/controllers ; quit"
lftp -u tim@mazlo.de,of97cXJIZNW ftp.mazlo.de -e "set ftp:ssl-allow no; mirror -R app/models app/models ; quit"
