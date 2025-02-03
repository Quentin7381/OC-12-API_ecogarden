# Documentation de l'API EcoGarden

## Introduction

## Ressources

### User

Represente un utilisateur de l'application.

Exemple d'utilisateur :

```json
{
    "id": 1, // Identifiant de l'utilisateur
    "username": "user", // Nom d'utilisateur
    "password": "1234", // Mot de passe (hashé, et n'est jamais renvoyé)
    "postal_code": "75000", // Code postal de l'utilisateur
}
```

### Advice

Represente un conseil de jardinage.

Exemple de conseil :

```json
{
    "id": 1, // Identifiant du conseil
    "title": "Conseil 1", // Titre du conseil
    "content": "Contenu du conseil 1", // Contenu du conseil
    "author": 1, // Identifiant de l'auteur du conseil
    "month": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12], // Mois de l'année où le conseil est valable
}
```

### Meteo

// TODO

## Endpoints

### Authentification

#### POST /auth : Authentifier un utilisateur

**Description** : Authentifier un utilisateur. Pour créer un utilisateur, voir la route `POST /user`.

**Exemple de requête :**

```json
POST /auth
{
    "username": "user",
    "password": "1234"
}
```

**Exemple de réponse :**
```json
200 OK
{
    "token": "jwt-token"
}
```

**Erreurs possibles :**

```json	
400 Bad Request
{
    "error": "Missing or invalid fields",
    "details": {
        "password": "Missing required field"
    }
}
```

```json
401 Unauthorized
{
    "error": "Invalid credentials"
}
```

### User

#### POST /user : Créer un utilisateur

**Description** : Créer un utilisateur

**Authentification** : Non requise

**Exemple de requête :**

```json
POST /user
{
    "username": "user",
    "password": "1234",
    "postal_code": "75000"
}
```

**Exemple de réponse :**
```json
201 Created
// Header
{
    "Location": "/user/1"
}
// Body
{
    "id": 1,
    "username": "user",
    "postal_code": "75000"
}
```

Erreurs possibles :

```json
400 Bad Request
{
    "error": "Missing or invalid fields",
    "details": {
        "password": "Password must be at least 4 characters long",
        "postal_code": "Missing required field"
    }
}
```

```json
409 Conflict
{
    "error": "Username already taken"
}
```

#### GET /user/{id} : Récupérer un utilisateur

**Description** : Récupérer un utilisateur

**Authentification** : Requise

**Arguments** :
- `id` : Identifiant numérique de l'utilisateur

**Exemple de requête :**
```json
GET /user/1
```

**Exemple de réponse :**
```json
200 OK
{
    "id": 1,
    "username": "user",
    "postal_code": "75000"
}
```

**Erreurs possibles :**

```json
400 Bad Request
{
    "error": "Missing user id in URL. Endpoint should be /user/{id}"
}
```

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /auth endpoint"
}
```

```json
403 Forbidden
{
    "error": "You tried to access an account that is not yours"
}
```

```json
404 Not Found
{
    "error": "The user you are trying to access does not exist"
}
```

#### PUT|PATCH /user/{id} : Modifier un utilisateur

**Description** : Modifier un utilisateur

**Arguments** :
- `id` : Identifiant numérique de l'utilisateur

**Exemple de requête :**
```json
PUT /user/1
{
    "username": "user2",
    "password": "1234",
    "postal_code": "75001"
}
```

```json	
PATCH /user/1
{
    "postal_code": "75001"
}
```

**Exemple de réponse :**
```json
200 OK
{
    "id": 1,
    "username": "user2",
    "postal_code": "75001"
}
```

**Erreurs possibles :**

```json
400 Bad Request
{
    "error": "Missing user id in URL. Endpoint should be /user/{id}"
}

```json
400 Bad Request
{
    "error": "Missing or invalid fields",
    "details": {
        "password": "Password must be at least 4 characters long"
    }
}
```

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /auth endpoint"
}
```

```json
403 Forbidden
{
    "error": "You tried to modify an account that is not yours"
}
```

```json
404 Not Found
{
    "error": "The user you are trying to modify does not exist"
}
```

#### DELETE /user/{id} : Supprimer un utilisateur

**Description** : Supprimer un utilisateur

**Arguments** :
- `id` : Identifiant numérique de l'utilisateur

**Exemple de requête :**
```json
DELETE /user/1
```

**Exemple de réponse :**
```json
204 No Content
```

**Erreurs possibles :**

```json
400 Bad Request
{
    "error": "Missing user id in URL. Endpoint should be /user/{id}"
}

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /auth endpoint"
}
```

```json
403 Forbidden
{
    "error": "You tried to delete an account that is not yours"
}
```

```json
404 Not Found
{
    "error": "The user you are trying to delete does not exist"
}
```