#!/bin/bash

# Script d'installation pour le projet Web Sécurité
#
# Ce script automatise :
# 1. L'installation des dépendances PHP.
# 2. La création du fichier .env.
# 3. L'importation de la base de données.
# 4. La configuration d'Apache (nécessite sudo).
# 5. La gestion des permissions des dossiers.

echo "Démarrage de l'installation du projet..."

# --- 1. Installation des dépendances Composer ---
if ! command -v composer &> /dev/null
then
    echo "Composer n'est pas installé. Veuillez l'installer avant de continuer."
    exit 1
fi
echo "Installation des dépendances PHP avec Composer..."
composer install --no-interaction --quiet

# --- 2. Configuration de l'environnement ---
if [ ! -f ".env" ]; then
    echo "Création du fichier .env à partir de .env.example."
    cp .env.example .env
    echo "Fichier .env créé. N'oubliez pas de le personnaliser si nécessaire."
else
    echo "Le fichier .env existe déjà."
fi

# --- 3. Importation de la base de données ---
if [ -f "database.sql" ]; then
    echo "Tentative d'importation de la base de données..."
    # Sourcer les variables d'environnement
    set -a
    source .env
    set +a

    if ! command -v mysql &> /dev/null
    then
        echo "La commande 'mysql' est introuvable. Impossible d'importer la base de données automatiquement."
        echo "Veuillez importer le fichier 'database.sql' manuellement dans la base de données '$DB_NAME'."
    else
        # Tenter de se connecter et d'importer
        mysql -h"$DB_HOST" -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < database.sql 2>/dev/null
        if [ $? -eq 0 ]; then
            echo "Base de données importée avec succès dans '$DB_NAME'."
        else
            echo "Échec de l'importation de la base de données. Veuillez vérifier vos identifiants dans le fichier .env et que la base de données '$DB_NAME' existe."
        fi
    fi
else
    echo "Fichier 'database.sql' introuvable, l'importation de la base de données est ignorée."
fi


# --- 4. Configuration d'Apache et des permissions (Nécessite Sudo) ---
echo -e "

"
echo "CONFIGURATION DU SERVEUR WEB (SUDO REQUIS)"
echo "Le script va maintenant tenter de configurer Apache et de définir les permissions."
echo "Un mot de passe administrateur (sudo) vous sera demandé."

# Vérifier si on est sur un système compatible Apache2/Debian
APACHE_SITES_AVAILABLE="/etc/apache2/sites-available"
if [ ! -d "$APACHE_SITES_AVAILABLE" ]; then
    echo "Ce script ne semble pas être sur un système Debian/Ubuntu. La configuration d'Apache est ignorée."
    echo "Veuillez copier manuellement 'apache_config/vhost.conf.example' vers le répertoire de configuration de votre serveur web."
    exit 0
fi

# Demander le nom du fichier de configuration
read -p "Entrez le nom pour le fichier de configuration Apache (ex: web-securite.conf): " VHOST_FILENAME

if [ -z "$VHOST_FILENAME" ]; then
    echo "Nom de fichier invalide. Abandon."
    exit 1
fi

APACHE_CONF_PATH="$APACHE_SITES_AVAILABLE/$VHOST_FILENAME"

sudo bash -c "
echo '--- Exécution des commandes avec sudo ---'

# Copie de la configuration vhost
echo 'Copie de la configuration vhost...'
cp apache_config/vhost.conf.example '$APACHE_CONF_PATH'

# Activation du site et du module rewrite
echo 'Activation du site et des modules...'
a2ensite '$VHOST_FILENAME'
a2enmod rewrite

# Définition des permissions pour le dossier uploads
echo 'Définition des permissions pour le dossier uploads/...'
if [ -d 'uploads' ]; then
    chown -R www-data:www-data uploads
    chmod -R 775 uploads
fi

# Rechargement d'Apache pour appliquer les changements
echo 'Rechargement du service Apache...'
systemctl reload apache2

echo '--- Fin des commandes sudo ---'
"

if [ $? -eq 0 ]; then
    echo "Configuration du serveur web terminée."
else
    echo "Une erreur est survenue lors de la configuration du serveur web."
fi


echo -e "

Installation terminée !"
echo "Le projet devrait maintenant être accessible."
echo "N'oubliez pas de modifier votre fichier /etc/hosts si nécessaire pour faire pointer le domaine de votre vhost vers 127.0.0.1."

exit 0
