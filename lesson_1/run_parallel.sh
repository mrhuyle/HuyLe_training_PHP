#!/bin/bash

# Directory containing your PHP scripts and data files
DIR="/htdocs/exercise"

# Loop through the part files and execute the PHP script for each
for i in {1..12}; do
    php $DIR/lesson_1/process_file.php $DIR/data/user_part$i.csv &
done
