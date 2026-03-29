# Rapport d'Audit de Sécurité - Web Secure
**Date :** 11 Mars 2026
**Statut :** Refactorisation MVC et Sécurisation Critique Terminée

## 1. Architecture Logicielle
- **Modèle MVC (Model-View-Controller) :** Isolation complète de la logique métier, de l'accès aux données et de l'affichage. Empêche l'exécution de code logique dans les fichiers de présentation.
- **Point d'Entrée Unique (`index.php`) :** Centralisation du routage, empêchant l'accès direct aux fichiers sensibles du système.
- **Gestion des Dépendances (Composer) :** Utilisation de standards industriels (PSR-4) et de bibliothèques éprouvées (`firebase/php-jwt`, `vlucas/phpdotenv`).

## 2. Protection des Données & Authentification
- **Hachage des Mots de Passe :** Utilisation de l'algorithme **Bcrypt** (natif PHP `password_hash`).
- **Secret Pepper (Grain de poivre) :** Ajout d'une clé secrète serveur (`PASSWD_PEPPER`) avant le hachage pour protéger contre les tables de correspondance (Rainbow Tables) même en cas de fuite de la base de données.
- **Complexité des Mots de Passe :** Validation stricte côté serveur (12 car. min, Majuscule, Minuscule, Chiffre, Symbole).
- **JWT (JSON Web Tokens) :** Authentification sans état (Stateless) signée avec une clé secrète forte, stockée dans des cookies sécurisés.

## 3. Sécurité des Transports & Sessions
- **Forçage HTTPS :** Redirection automatique de tout le trafic HTTP (80) vers HTTPS (443) via Apache.
- **HSTS (Strict-Transport-Security) :** Instruction au navigateur de ne communiquer qu'en HTTPS pendant 1 an.
- **Sécurité des Cookies :**
    - `HttpOnly` : Empêche l'accès au cookie via JavaScript (Protection contre XSS).
    - `Secure` : Le cookie n'est envoyé que sur des connexions chiffrées.
    - `SameSite=Strict` : Empêche l'envoi du cookie lors de requêtes provenant de sites tiers (Protection contre CSRF).
- **Protection CSRF :** Utilisation de jetons (tokens) non-prédictibles générés pour chaque session et vérifiés à chaque requête `POST`.

## 4. Défense Contre les Attaques Communes
- **Anti Brute-Force (IP Throttling) :** Verrouillage automatique de l'IP après 3 tentatives infructueuses pendant 15 minutes.
- **Validation & Assainissement (Input Validation) :**
    - Utilisation d'un `Validator` centralisé.
    - Pseudos restreints aux caractères alphanumériques (Protection contre Injection).
    - `strip_tags()` sur les messages pour neutraliser les injections HTML/XSS.
- **Requêtes Préparées (PDO) :** Utilisation systématique des paramètres liés pour toutes les requêtes SQL, rendant les **injections SQL impossibles**.

## 5. En-têtes de Sécurité HTTP
- **Content Security Policy (CSP) :** Restriction des sources de scripts et de styles à `'self'`. Bloque l'exécution de scripts tiers non autorisés.
- **X-Frame-Options (DENY) :** Empêche l'intégration du site dans des iframes pour contrer le Clickjacking.
- **X-Content-Type-Options (nosniff) :** Empêche le navigateur d'interpréter les fichiers comme un type MIME différent de celui déclaré.

## 6. Journalisation & Surveillance
- **Logs d'Actions Admin :** Traçabilité complète des certifications/dé-certifications et suppressions d'utilisateurs.
- **Logs de Connexion enrichis :** Enregistrement des succès/échecs avec :
    - Adresse IP.
    - User-Agent.
    - **Géolocalisation** (Pays, Ville) pour identifier les tentatives de connexion géographiquement suspectes.

---
**Conclusion :** L'application présente une posture de sécurité robuste conforme aux recommandations de l'OWASP. La séparation des responsabilités via l'architecture MVC permet une maintenance sécurisée à long terme.
