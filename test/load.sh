#!/bin/bash
rm -rf $(pwd)/plots
mkdir $(pwd)/plots
for ENDPOINT in "search" "reverse" "lookup"
do	
	for SIZE in 20 35 50
	do
		FILENAME=/plots/plot_$ENDPOINT'_'$SIZE
		echo ab -n20 -c20 -g\'$FILENAME\' localhost/nominatim/$ENDPOINT
		ab -n20 -c20 -g\'$FILENAME\' localhost/nominatim/$ENDPOINT
	done
done
