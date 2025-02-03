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

**Résumé des endpoints :**

| Resource | Verbe | URI | Description | Arguments |
|----------|-------|-----|-------------|-----------|
| Authentification | POST | /api/auth | Authentifier un utilisateur | - |
| User | POST | /api/user | Créer un utilisateur | - |
| User | GET | /api/user/{id} | Récupérer un utilisateur | **url** `id`, **header** `Authorization` |
| User | PUT | /api/user/{id} | Modifier un utilisateur | **url** `id`, **header** `Authorization` |
| User | PATCH | /api/user/{id} | Modifier un utilisateur | **url** `id`, **header** `Authorization` |
| User | DELETE | /api/user/{id} | Supprimer un utilisateur | **url** `id`, **header** `Authorization` |
| Advice | GET | /api/advice | Récupérer la liste des conseils | **query** `month`, **query** `limit`, **query** `page`, **query** `sort`, **query** `order`, **header** `Authorization` |
| Advice | GET | /api/advice/{id} | Récupérer un unique conseil | **url** `id`, **header** `Authorization` |
| Advice | POST | /api/advice | Créer un conseil | **body** `title`, **body** `content`, **body** `month`, **header** `Authorization` |
| Advice | PUT | /api/advice/{id} | Modifier un conseil | **url** `id`, **body** `title`, **body** `content`, **body** `month`, **header** `Authorization` |
| Advice | PATCH | /api/advice/{id} | Modifier un conseil | **url** `id`, **body** `content`, **header** `Authorization` |
| Advice | DELETE | /api/advice/{id} | Supprimer un conseil | **url** `id`, **header** `Authorization` |

### Authentification

#### POST /api/auth : Authentifier un utilisateur

**Description** : Authentifier un utilisateur. Pour créer un utilisateur, voir la route `POST /api/user`.

**Exemple de requête :**

```json
POST /api/auth
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
    // "_links": {
    //     "your_user": { "href": "/api/user/1" }
    //     "your_advices": { "href": "/api/advice/?user=1" }
    //     "your_weather": { "href": "/api/weather/?user=1" }
    // }
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
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "Invalid credentials"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

### User

#### POST /api/user : Créer un utilisateur

**Description** : Créer un utilisateur

**Authentification** : Non requise

**Exemple de requête :**

```json
POST /api/user
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
    "Location": "/api/user/1"
}
// Body
{
    "id": 1,
    "username": "user",
    "postal_code": "75000"
    // "_links": {
    //     "self": { "href": "/api/user/1" },
    //     "update": { "href": "/api/user/1" },
    //     "delete": { "href": "/api/user/1" }
    // }
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
    // "_links": {
    //     "fix": { "href": "/api/user" }
    // }
}
```

```json
409 Conflict
{
    "error": "Username already taken"
    // "_links": {
    //     "fix": { "href": "/api/user" }
    // }
}
```

#### GET /api/user/{id} : Récupérer un utilisateur

**Description** : Récupérer un utilisateur

**Authentification** : Requise

**Arguments** :
- **url** `id` : Identifiant numérique de l'utilisateur
- **header** `Authorization` : Token JWT

**Exemple de requête :**
```json
GET /api/user/1
// Header
{
    "Authorization": "Bearer jwt-token"
}
```

**Exemple de réponse :**
```json
200 OK
{
    "id": 1,
    "username": "user",
    "postal_code": "75000"
    // "_links": {
    //     "self": { "href": "/api/user/1" },
    //     "update": { "href": "/api/user/1" },
    //     "delete": { "href": "/api/user/1" }
    // }
}
```

**Erreurs possibles :**

```json
400 Bad Request
{
    "error": "Missing user id in URL. Endpoint should be /api/user/{id}"
    // "_links": {
    //     "fix": { "href": "/api/user" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /api/auth endpoint"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "Invalid JWT token provided"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
403 Forbidden
{
    "error": "You tried to access an account that is not yours"
    // "_links": {
    //     "fix": { "href": "/api/user/1" }
    // }
}
```

```json
404 Not Found
{
    "error": "The user you are trying to access does not exist"
    // "_links": {
    //     "fix": { "href": "/api/user" }
    // }
}
```

#### PUT|PATCH /api/user/{id} : Modifier un utilisateur

**Description** : Modifier un utilisateur

**Arguments** :
- **url** `id` : Identifiant numérique de l'utilisateur
- **header** `Authorization` : Token JWT

**Exemple de requête :**
```json
PUT /api/user/1
// Header
{
    "Authorization": "Bearer jwt-token"
}
{
    "username": "user2",
    "password": "1234",
    "postal_code": "75001"
}
```

```json	
PATCH /api/user/1
// Header
{
    "Authorization": "Bearer jwt-token"
}
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
    // "_links": {
    //     "self": { "href": "/api/user/1" },
    //     "delete": { "href": "/api/user/1" }
    // }
}
```

**Erreurs possibles :**

```json
400 Bad Request
{
    "error": "Missing user id in URL. Endpoint should be /api/user/{id}"
    // "_links": {
    //     "fix": { "href": "/api/user" }
    // }
}
```

```json
400 Bad Request
{
    "error": "Missing or invalid fields",
    "details": {
        "password": "Password must be at least 4 characters long"
    }
    // "_links": {
    //     "fix": { "href": "/api/user/1" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /api/auth endpoint"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "Invalid JWT token provided"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
403 Forbidden
{
    "error": "You tried to modify an account that is not yours"
    // "_links": {
    //     "fix": { "href": "/api/user/1" }
    // }
}
```

```json
404 Not Found
{
    "error": "The user you are trying to modify does not exist"
    // "_links": {
    //     "fix": { "href": "/api/user" }
    // }
}
```

#### DELETE /api/user/{id} : Supprimer un utilisateur

**Description** : Supprimer un utilisateur

**Arguments** :
- **url** `id` : Identifiant numérique de l'utilisateur
- **header** `Authorization` : Token JWT

**Exemple de requête :**
```json
DELETE /api/user/1
// Header
{
    "Authorization": "Bearer jwt-token"
}
```

**Exemple de réponse :**
```json
204 No Content
// "_links": {
//     "self": { "href": "/api/user/1" }
    // }
```

**Erreurs possibles :**

```json
400 Bad Request
{
    "error": "Missing user id in URL. Endpoint should be /api/user/{id}"
    // "_links": {
    //     "fix": { "href": "/api/user" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /api/auth endpoint"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "Invalid JWT token provided"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
403 Forbidden
{
    "error": "You tried to delete an account that is not yours"
    // "_links": {
    //     "fix": { "href": "/api/user/1" }
    // }
}
```

```json
404 Not Found
{
    "error": "The user you are trying to delete does not exist"
    // "_links": {
    //     "fix": { "href": "/api/user" }
    // }
}
```

### Advice

#### GET /api/advice : Récupérer la liste des conseils

**Description** : Récupérer la liste des conseils

**Authentification** : Requise

**Arguments** :
- **query** `month` *int|array* : Mois de l'année
    - Optionnel
    - Entier entre 1 et 12 ou tableau d'entiers entre 1 et 12
- **query** `limit` *int* : Nombre maximum de conseils à renvoyer
    - Optionnel
    - Entier positif
    - Default: 10
- **query** `page` *int* : Page de conseils à renvoyer
    - Optionnel
    - Entier positif
    - Default: 1
- **query** `sort` *string* : Champ de tri
    - Optionnel
    - Default: "id"
    - Valeurs possibles: "id", "title", "author", "month", "created_at"
- **query** `order` *string* : Ordre de tri
    - Optionnel
    - Default: "asc"
    - Valeurs possibles: "asc", "desc"
- **header** `Authorization` : Token JWT

**Exemple de requête :**
```json
GET /api/advice?month=1&limit=5&page=2&sort=title&order=desc
// Header
{
    "Authorization": "Bearer jwt-token"
}
```

```json
GET /api/advice?month=1
// Header
{
    "Authorization": "Bearer jwt-token"
}
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
            // "_links": {
            //     "self": { "href": "/api/advice/6" },
            //     "update": { "href": "/api/advice/6" },
            //     "delete": { "href": "/api/advice/6" }
            // }
        }, 
        // (...)
    ]
}
```

**Erreurs possibles :**

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /api/auth endpoint"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "Invalid JWT token provided"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
400 Bad Request
{
    "error": "Missing or invalid fields",
    "details": {
        "month": "Month must be an integer or an array of integers between 1 and 12"
    }
    // "_links": {
    //     "fix": { "href": "/api/advice" }
    // }
}
```

```json
404 Not Found
{
    "error": "No advice found"
    // "_links": {
    //     "fix": { "href": "/api/advice" }
    // }
}
```

#### GET /api/advice/{id} : Récupérer un unique conseil

**Description** : Récupérer la liste des conseils

**Authentification** : Requise

**Arguments** :
- **url** `id` *int* : Identifiant numérique du conseil
    - Requis
    - Doit correspondre à un conseil existant
- **header** `Authorization` : Token JWT

**Exemple de requête :**
```json
GET /api/advice/1
// Header
{
    "Authorization": "Bearer jwt-token"
}
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
    // "_links": {
    //     "self": { "href": "/api/advice/1" },
    //     "update": { "href": "/api/advice/1" },
    //     "delete": { "href": "/api/advice/1" }
    // }
}
```

**Erreurs possibles :**

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /api/auth endpoint"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "Invalid JWT token provided"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
404 Not Found
{
    "error": "The advice you are trying to access does not exist"
    // "_links": {
    //     "fix": { "href": "/api/advice" }
    // }
}
```

#### POST /api/advice : Créer un conseil

**Description** : Créer un conseil

**Authentification** : Requise

**Arguments :**

- **body** `title` *string* : Titre du conseil
    - Unique
    - Requis
    - Minimum 5 caractères
    - Maximum 50 caractères
- **body** `content` *string* : Contenu du conseil
    - Requis
    - Minimum 10 caractères
    - Maximum 5000 caractères
- **body** `month` *array* : Mois de l'année où le conseil est valable
    - Requis
    - Tableau d'entiers
    - Minimum 1 élément
    - Maximum 12 éléments
    - Entiers entre 1 et 12
- **header** `Authorization` : Token JWT

**Exemple de requête :**
```json
POST /api/advice
// Header
{
    "Authorization": "Bearer jwt-token"
}
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
    "Location": "/api/advice/1"
}
// Body
{
    "id": 1,
    "title": "Conseil 1",
    "content": "Contenu du conseil 1",
    "author": 1,
    "month": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
    // "_links": {
    //     "self": { "href": "/api/advice/1" },
    //     "update": { "href": "/api/advice/1" },
    //     "delete": { "href": "/api/advice/1" }
    // }
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
    // "_links": {
    //     "fix": { "href": "/api/advice" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /api/auth endpoint"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "Invalid JWT token provided"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

#### PUT|PATCH /api/advice/{id} : Modifier un conseil

**Description** : Modifier un conseil

**Authentification** : Requise

**Arguments** :
- **url** `id` : Identifiant numérique du conseil
- **body** `title` *string* : Titre du conseil
    - Unique
    - Requis
    - Minimum 5 caractères
    - Maximum 50 caractères
- **body** `content` *string* : Contenu du conseil
    - Requis
    - Minimum 10 caractères
    - Maximum 5000 caractères
- **body** `month` *array* : Mois de l'année où le conseil est valable
    - Requis
    - Tableau d'entiers
    - Minimum 1 élément
    - Maximum 12 éléments
    - Entiers entre 1 et 12
- **header** `Authorization` : Token JWT

**Exemple de requête :**
```json
PUT /api/advice/1
// Header
{
    "Authorization": "Bearer jwt-token"
}
{
    "title": "Conseil 1",
    "content": "Contenu du conseil 1",
    "month": [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
}
```

```json
PATCH /api/advice/1
// Header
{
    "Authorization": "Bearer jwt-token"
}
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
    // "_links": {
    //     "self": { "href": "/api/advice/1" },
    //     "delete": { "href": "/api/advice/1" }
    // }
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
    // "_links": {
    //     "fix": { "href": "/api/advice/1" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /api/auth endpoint"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "Invalid JWT token provided"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
403 Forbidden
{
    "error": "You tried to modify an advice that is not yours"
    // "_links": {
    //     "fix": { "href": "/api/advice/1" }
    // }
}
```

```json
404 Not Found
{
    "error": "The advice you are trying to modify does not exist"
    // "_links": {
    //     "fix": { "href": "/api/advice" }
    // }
}
```

#### DELETE /api/advice/{id} : Supprimer un conseil

**Description** : Supprimer un conseil

**Authentification** : Requise

**Arguments** :
- **url** `id` : Identifiant numérique du conseil
- **body** `title` *string* : Titre du conseil
    - Unique
    - Requis
    - Minimum 5 caractères
    - Maximum 50 caractères
- **body** `content` *string* : Contenu du conseil
    - Requis
    - Minimum 10 caractères
    - Maximum 5000 caractères
- **body** `month` *array* : Mois de l'année où le conseil est valable
    - Requis
    - Tableau d'entiers
    - Minimum 1 élément
    - Maximum 12 éléments
    - Entiers entre 1 et 12
- **header** `Authorization` : Token JWT

**Exemple de requête :**
```json
DELETE /api/advice/1
// Header
{
    "Authorization": "Bearer jwt-token"
}
```

**Exemple de réponse :**
```json
204 No Content
// "_links": {
//     "self": { "href": "/api/advice/1" }
// }
```

**Erreurs possibles :**

```json
401 Unauthorized
{
    "error": "No JWT token provided. You can obtain one by authenticating with the /api/auth endpoint"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
401 Unauthorized
{
    "error": "Invalid JWT token provided"
    // "_links": {
    //     "fix": { "href": "/api/auth" }
    // }
}
```

```json
403 Forbidden
{
    "error": "You tried to delete an advice that is not yours"
    // "_links": {
    //     "fix": { "href": "/api/advice/?user=1" }
    // }
}
```

```json
404 Not Found
{
    "error": "The advice you are trying to delete does not exist"
    // "_links": {
    //     "fix": { "href": "/api/advice" }
    // }
}
```