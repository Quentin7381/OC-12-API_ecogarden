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

## Authentification

// TODO

## Endpoints

### Sans authentification

#### POST /user : Créer un utilisateur

**Description** : Créer un utilisateur

**Exemple de requête :**

```json
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

#### POST /auth : Authentifier un utilisateur

**Description** : Authentifier un utilisateur

**Exemple de requête :**

```json
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

### Avec authentification

