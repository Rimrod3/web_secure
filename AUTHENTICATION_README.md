# Contexte de l'authentification JWT

Ce document décrit le passage d'un système d'authentification basé sur les sessions à un système d'authentification basé sur les JSON Web Tokens (JWT).

## Fichiers modifiés et créés

- **`login.php` (modifié)** : Gère la connexion de l'utilisateur. Au lieu de démarrer une session, il génère maintenant un token JWT lors d'une connexion réussie et le place dans un cookie HTTP-only sécurisé.

- **`accueil.php` (modifié)** : Page d'accueil protégée. Elle n'utilise plus les sessions pour vérifier l'authentification. À la place, elle inclut le fichier `auth_middleware.php` pour valider le token JWT avant d'afficher le contenu.

- **`auth_middleware.php` (créé)** : Ce nouveau fichier contient la logique de validation du JWT. Il vérifie la présence du token dans les cookies, le décode et le valide. Si le token est invalide ou absent, l'utilisateur est redirigé vers `login.php`.

- **`logout.php` (créé)** : Gère la déconnexion. Il supprime le cookie contenant le token JWT, ce qui invalide de fait la "session" de l'utilisateur.

## Flux d'authentification

1.  L'utilisateur se connecte via le formulaire sur `login.php`.
2.  Si les identifiants sont corrects, le serveur crée un token JWT qui contient l'ID et le pseudo de l'utilisateur.
3.  Ce token est envoyé au navigateur dans un cookie `HttpOnly` et `Secure`.
4.  Pour accéder à `accueil.php`, le navigateur de l'utilisateur envoie automatiquement le cookie avec le token JWT.
5.  Le script `auth_middleware.php` (appelé par `accueil.php`) intercepte le token, le vérifie avec la clé secrète.
6.  Si le token est valide, l'accès à la page est autorisé. Sinon, l'utilisateur est renvoyé à la page de connexion.
7.  Lors de la déconnexion, le cookie du token est supprimé par `logout.php`.

## Clé secrète

Une clé secrète (`$secret_key`) est utilisée pour signer et vérifier les tokens. Pour l'instant, elle est codée en dur dans `login.php` et `auth_middleware.php`. **Pour un environnement de production, cette clé doit être beaucoup plus complexe et stockée de manière sécurisée en dehors des fichiers du projet (par exemple, dans une variable d'environnement).**
