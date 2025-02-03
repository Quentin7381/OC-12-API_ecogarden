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
    "created_at": "1234567890" // Date de création du conseil (timestamp)
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

### Advice

#### GET /advice : Récupérer la liste des conseils

**Description** : Récupérer la liste des conseils

**Authentification** : Requise

**Arguments** :
- (query) `month` *int|array* : Mois de l'année
    - Optionnel
    - Entier entre 1 et 12 ou tableau d'entiers entre 1 et 12
- (query) `limit` *int* : Nombre maximum de conseils à renvoyer
    - Optionnel
    - Entier positif
    - Default: 10
- (query) `page` *int* : Page de conseils à renvoyer
    - Optionnel
    - Entier positif
    - Default: 1
- (query) `sort` *string* : Champ de tri
    - Optionnel
    - Default: "id"
    - Valeurs possibles: "id", "title", "author", "month", "created_at"
- (query) `order` *string* : Ordre de tri
    - Optionnel
    - Default: "asc"
    - Valeurs possibles: "asc", "desc"

**Exemple de requête :**
```json
GET /advice?month=1&limit=5&page=2&sort=title&order=desc
```

```json
GET /advice?month=1
```

**Exemple de réponse :**
```json
200 OK
{
    "total": 55,
    "limit": 5,
    "page": 2,
    "pages": 11,
    "content": [
        {
            "id": 6,
            "title": "Conseil 6",
            "content": "Contenu du conseil 6",
            "author": 1,
            "month": [1, 2, 3, 4],
            "created_at": "1234567890"
        }, 
        // (...)
    ]
}
```

**Erreurs possibles :**

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /auth endpoint"
}
```

```json
400 Bad Request
{
    "error": "Missing or invalid fields",
    "details": {
        "month": "Month must be an integer or an array of integers between 1 and 12"
    }
}
```

```json
404 Not Found
{
    "error": "No advice found"
}
```

#### GET /advice/{id} : Récupérer un unique conseil

**Description** : Récupérer la liste des conseils

**Authentification** : Requise

**Arguments** :
- (url) `id` *int* : Identifiant numérique du conseil
    - Requis
    - Doit correspondre à un conseil existant

**Exemple de requête :**
```json
GET /advice/1
```

**Exemple de réponse :**
```json
200 OK
{
    "id": 1,
    "title": "Conseil 1",
    "content": "Contenu du conseil 1",
    "author": 1,
    "month": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
}
```

**Erreurs possibles :**

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /auth endpoint"
}
```

```json
404 Not Found
{
    "error": "The advice you are trying to access does not exist"
}
```

#### POST /advice : Créer un conseil

**Description** : Créer un conseil

**Authentification** : Requise

**Arguments :**

- (body) `title` *string* : Titre du conseil
    - Unique
    - Requis
    - Minimum 5 caractères
    - Maximum 50 caractères
- (body) `content` *string* : Contenu du conseil
    - Requis
    - Minimum 10 caractères
    - Maximum 5000 caractères
- (body) `month` *array* : Mois de l'année où le conseil est valable
    - Requis
    - Tableau d'entiers
    - Minimum 1 élément
    - Maximum 12 éléments
    - Entiers entre 1 et 12

**Exemple de requête :**
```json
POST /advice
{
    "title": "Conseil 1",
    "content": "Contenu du conseil 1",
    "month": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
}
```

**Exemple de réponse :**
```json
201 Created
// Header
{
    "Location": "/advice/1"
}
// Body
{
    "id": 1,
    "title": "Conseil 1",
    "content": "Contenu du conseil 1",
    "author": 1,
    "month": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
}
```

**Erreurs possibles :**

```json
400 Bad Request
{
    "error": "Missing or invalid fields",
    "details": {
        "content": "Missing required field",
        "month": "Month must be an array of integers between 1 and 12"
    }
}
```

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /auth endpoint"
}
```

#### PUT|PATCH /advice/{id} : Modifier un conseil

**Description** : Modifier un conseil

**Authentification** : Requise

**Arguments** :
- (url) `id` : Identifiant numérique du conseil
- (body) `title` *string* : Titre du conseil
    - Unique
    - Requis
    - Minimum 5 caractères
    - Maximum 50 caractères
- (body) `content` *string* : Contenu du conseil
    - Requis
    - Minimum 10 caractères
    - Maximum 5000 caractères
- (body) `month` *array* : Mois de l'année où le conseil est valable
    - Requis
    - Tableau d'entiers
    - Minimum 1 élément
    - Maximum 12 éléments
    - Entiers entre 1 et 12

**Exemple de requête :**
```json
PUT /advice/1
{
    "title": "Conseil 1",
    "content": "Contenu du conseil 1",
    "month": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
}
```

```json
PATCH /advice/1
{
    "content": "Nouveau contenu du conseil 1"
}
```

**Exemple de réponse :**
```json
200 OK
{
    "id": 1,
    "title": "Conseil 1",
    "content": "Nouveau contenu du conseil 1",
    "author": 1,
    "month": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
}
```

**Erreurs possibles :**

```json
400 Bad Request
{
    "error": "Missing or invalid fields",
    "details": {
        "content": "Content must be at least 10 characters long"
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
    "error": "You tried to modify an advice that is not yours"
}
```

```json
404 Not Found
{
    "error": "The advice you are trying to modify does not exist"
}
```

#### DELETE /advice/{id} : Supprimer un conseil

**Description** : Supprimer un conseil

**Authentification** : Requise

**Arguments** :
- (url) `id` : Identifiant numérique du conseil
- (body) `title` *string* : Titre du conseil
    - Unique
    - Requis
    - Minimum 5 caractères
    - Maximum 50 caractères
- (body) `content` *string* : Contenu du conseil
    - Requis
    - Minimum 10 caractères
    - Maximum 5000 caractères
- (body) `month` *array* : Mois de l'année où le conseil est valable
    - Requis
    - Tableau d'entiers
    - Minimum 1 élément
    - Maximum 12 éléments
    - Entiers entre 1 et 12

**Exemple de requête :**
```json
DELETE /advice/1
```

**Exemple de réponse :**
```json
204 No Content
```

**Erreurs possibles :**

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /auth endpoint"
}
```

```json
403 Forbidden
{
    "error": "You tried to delete an advice that is not yours"
}
```

```json
404 Not Found
{
    "error": "The advice you are trying to delete does not exist"
}
```