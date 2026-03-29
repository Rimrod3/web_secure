# État d'avancement du projet - Web Sécurité

Ce document récapitule l'état actuel du projet par rapport aux exigences du cahier des charges (BAC-P-CYB-S04-DEVWEBSEC.xlsx).

## ✅ RÉALISÉ (Conformité OWASP & Sécurité)

### 1. Contrôle d'Accès (Broken Access Control)
- **Modèle de Rôles** : Distinction entre `admin` et `user`.
- **Identification** : Authentification via JWT (JSON Web Token) sécurisé.
- **Autorisations** : Vérification systématique des rôles dans les contrôleurs (`DashboardController`).
- **Répertoire Public** : Architecture avec point d'entrée unique (`index.php`) et accès restreint via `.htaccess`.

### 2. Cryptographie (Cryptographic Failures)
- **Hachage des Mots de Passe** : Utilisation d'**Argon2id** (le standard actuel le plus robuste) avec un **Pepper** secret (configuration via `.env`).
- **HTTPS Client** : Redirection forcée vers HTTPS et activation du flag `Secure` sur les cookies.
- **HSTS** : En-tête `Strict-Transport-Security` activé.
- **SSL MySQL** : Configuration d'une connexion stricte PDO vers la base de données via SSL, avec génération d'une "Chain of Trust" (CA Serveur -> Certificat Serveur BDD -> Certificat Client PHP).

### 3. Protection contre les Injections (Injection)
- **Requêtes Préparées** : Utilisation systématique de `PDO` avec `prepare/execute` pour toutes les interactions DB.
- **Validation des Entrées** : Utilitaire `Validator.php` pour filtrer pseudos, IDs et messages.
- **Validation Email (DNS)** : Vérification stricte du format email et appel DNS (`checkdnsrr`) pour s'assurer que le domaine du mail existe et possède un serveur MX.
- **Sanitization** : Nettoyage des balises HTML et limitation de taille sur toutes les entrées.

### 4. Architecture & Design (Insecure Design)
- **Pattern MVC** : Séparation stricte (Models, Views, Controllers).
- **Protection CSRF** : Implémentation de jetons CSRF (`csrf_token`) sur tous les formulaires sensibles (Login, Inscription, Tickets, Upload).
- **Sécurité des Cookies** : Flags `HttpOnly`, `Secure`, et `SameSite=Strict` activés.
- **Upload via AJAX** : L'envoi de la photo de profil se fait via une requête `fetch` asynchrone pour une expérience utilisateur fluide, sans rechargement de page visible.
- **Anti-Stéganographie / Polyglot** : Système d'upload de photo de profil ultra-sécurisé :
  - **Re-codage complet** : L'image est intégralement reconstruite pixel par pixel via la librairie `GD`, forçant une conversion au format **JPEG**. Cette opération détruit toute charge utile (payload), métadonnée (EXIF/XMP) ou structure polyglotte malveillante.
  - **Multi-format** : Support sécurisé des formats JPEG, PNG, GIF et WebP.
  - **Validation stricte** : Vérification du type MIME réel (`getimagesize`) et limite de taille à **10 Mo**.
  - **Anonymisation** : Génération de noms de fichiers aléatoires cryptographiquement sûrs pour éviter les fuites d'information ou l'écrasement de fichiers.
  - **Nettoyage automatique** : Suppression systématique de l'ancienne photo lors d'un nouvel upload.

### 5. Configuration de Sécurité (Security Misconfiguration)
- **En-têtes de Sécurité** : 
  - `Content-Security-Policy` (CSP) configurée.
  - `X-Frame-Options: DENY` (Anti-Clickjacking).
  - `X-Content-Type-Options: nosniff`.
- **Variables d'Environnement** : Externalisation des secrets (DB, JWT, Pepper) dans un fichier `.env`.

### 6. Authentification (Authentication Failures)
- **Anti Brute-Force** : Système de throttling IP avec bannissement progressif et paliers de respiration (ex: 6 échecs -> 30s, puis 2 essais libres, 9 échecs -> 60s, etc.).
- **Politique de Mots de Passe** : Minimum 12 caractères, complexité requise (Maj/Min/Chiffre/Symbole), interdiction du pseudo dans le mot de passe.
- **Gestion de Session** : Déconnexion effective (suppression du cookie) et expiration automatique du JWT (1h).
- **Redirection des Utilisateurs Authentifiés** : Les utilisateurs déjà connectés sont automatiquement redirigés depuis les pages de connexion et d'inscription vers le tableau de bord, prévenant les bugs d'affichage et les actions redondantes.

### 7. Journalisation et Surveillance (Logging & Monitoring Failures)
- **Géolocalisation** : Intégration de l'API `ip-api.com` pour identifier et journaliser l'origine géographique (Pays, Ville) des adresses IP lors des tentatives de connexion.
- **Traçabilité** : Sauvegarde des IP, User-Agent, et statuts (SUCCESS/FAILURE) pour les connexions et les actions administrateur.

---

## 🚀 À FAIRE (Prochaines Étapes prioritaires)

### 1. Durcissement de la Configuration (Security Misconfiguration)
- **Erreurs PHP** : Désactiver `display_errors` dans `index.php` pour la production (actuellement à 1).
- **Tokens Serveur** : Désactiver les signatures Apache (`ServerSignature Off`) et PHP (`expose_php = Off`).

### 2. Encodage de Sortie (Injection/XSS)
- Vérifier et systématiser l'utilisation de `htmlspecialchars()` ou équivalent dans toutes les vues PHP pour prévenir les injections XSS résiduelles lors de l'affichage de données issues de la BDD.

### 3. Audit & Tests (Software and Data Integrity Failures)
- **Tests d'Injection** : Créer un script de test automatisé (Python ou PHP CLI) pour valider fonctionnellement que les payloads XSS/SQL classiques sont bien bloqués par l'application.

pour les mail check mx
 get mx r 

ajouter des prefixes et sufixes pour la creation de hash securiser avec argon2

---

## 🔑 Identifiants de Test
- **Admin** : `admin` / `admin` (à changer en prod)
- **Utilisateur** : `user` / `user`
