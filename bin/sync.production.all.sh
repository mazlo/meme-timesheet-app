#!/bin/bash

echo "Synching app data .."
bin/sync.production.app.sh
echo "Synching public data .."
bin/sync.production.public.sh

