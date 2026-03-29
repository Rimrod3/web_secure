# Configuration Apache

Ce dossier contient les fichiers de configuration Apache nécessaires au bon fonctionnement du projet.

## Installation

Pour que le site fonctionne correctement, vous devez copier ou créer un lien symbolique de ces fichiers vers les répertoires de configuration d'Apache.

### Fichier Virtual Host

Le fichier `vhost.conf.example` est un exemple de configuration de l'hôte virtuel pour ce projet. Copiez-le et adaptez-le à votre environnement si nécessaire.

**Exemple de commande :**

```bash
# Adaptez le chemin de destination à votre système (par exemple, /etc/apache2/sites-available/web-securite.conf)
sudo cp apache_config/vhost.conf.example /etc/apache2/sites-available/votre-projet.conf

# Puis activez le site (exemple pour Debian/Ubuntu)
sudo a2ensite votre-projet.conf
sudo systemctl reload apache2
```

### Fichier .htaccess

Le fichier `.htaccess` à la racine du projet est également essentiel et doit être présent. Assurez-vous que le module `mod_rewrite` d'Apache est activé.

**Exemple de commande :**
```bash
sudo a2enmod rewrite
sudo systemctl reload apache2
```
