# Installation du projet ecogreen

## Clés SSL pour tokens JWT

Pour générer les clés SSL pour les tokens JWT, il faut exécuter les commandes suivantes :

```bash
mkdir -p config/jwt
openssl genpkey -algorithm RSA -out config/jwt/private.pem -aes256
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```

Les mots de passe demandés lors de l'exécution des commandes sont requis.

Il faut ensuite le mot de passe utilisé dans `.env.local` :

```dotenv
JWT_PASSPHRASE=VotreMotDePasse
```

**Attention :** Ne pas renseigner ces informations dans le fichier `.env` car il est versionné.