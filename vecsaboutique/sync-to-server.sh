#!/bin/bash
# Subir archivos al servidor

KEY_PATH="/Users/strega/Desktop/Aplicaciones/grupovecsa-diseño/vecsaboutique/vecsa_id_rsa"
REMOTE_USER="vecsaboutique"
REMOTE_HOST="184.168.21.174"
REMOTE_PATH="public_html"
LOCAL_PATH="/Users/strega/Desktop/Aplicaciones/grupovecsa-diseño/vecsaboutique"

echo "Subiendo archivos al servidor..."
sftp -i "$KEY_PATH" "$REMOTE_USER@$REMOTE_HOST" <<EOF
cd $REMOTE_PATH
lcd $LOCAL_PATH
put -r *
bye
EOF

echo "Subida completada"
