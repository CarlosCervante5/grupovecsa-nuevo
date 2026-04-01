#!/bin/bash
# Subir solo el functions.php del tema motormania

cd /Users/strega/Desktop/Aplicaciones/grupovecsa-diseño/vecsaboutique

echo "Subiendo functions.php del tema motormania..."
sftp -i ./vecsa_id_rsa vecsaboutique@184.168.21.174 <<EOF
cd public_html/wp-content/themes/motormania
put wp-content/themes/motormania/functions.php
bye
EOF

echo "Subida completada"
