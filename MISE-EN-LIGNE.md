# Mettre Fabara en ligne avec Railway (≈ 10 minutes la première fois)

Railway lit votre code sur GitHub, le construit et lui donne une adresse https. Le tarif et l'offre d'essai changent : vérifiez-les sur railway.com/pricing (comptez quelques dollars par mois pour un petit site).

## 1. Ajouter les fichiers au projet (une seule fois)
Dans le Terminal, depuis le dossier `fabara` :
```bash
unzip -o ~/Downloads/deploiement.zip -d /tmp/dep && cp -R /tmp/dep/deploiement/. .
```
(Il copie `Dockerfile`, `.dockerignore` et le dossier `docker/`.)

## 2. Envoyer le site sur GitHub (nouveau dépôt privé)
Votre dépôt actuel « Fabara » ne contient pas le site lui-même (seulement les fichiers d'installation). On en crée un second, uniquement pour le site.

1. Sur github.com : **+ → New repository**, nom `fabara-site`, cochez **Private**, ne cochez rien d'autre, **Create**.
2. Dans le Terminal, toujours dans le dossier `fabara` (celui qui contient `artisan`) :
```bash
git init -b main
git add -A
git commit -m "Site Fabara"
git remote add origin https://github.com/fabara224-dotcom/fabara-site.git
git push -u origin main
```
Si GitHub demande un mot de passe, il faut un « Personal Access Token » (GitHub → Settings → Developer settings → Tokens, droit `repo`) à coller à la place. Ne le partagez jamais.

(Les fichiers `.env`, `vendor` et la base locale ne sont pas envoyés : c'est voulu.)

## 3. Créer le site sur Railway
1. Allez sur railway.com → **Login with GitHub**.
2. **New Project → Deploy from GitHub repo** → choisissez le dépôt `fabara-site`.
3. Ouvrez le service créé → onglet **Variables** → ajoutez :

| Nom | Valeur |
|---|---|
| `APP_KEY` | collez le résultat de `php artisan key:generate --show` (lancé dans le Terminal) |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `FORCE_HTTPS` | `true` |
| `SESSION_SECURE_COOKIE` | `true` |
| `ADMIN_EMAIL` | votre vrai email |
| `ADMIN_PASSWORD` | un mot de passe long et secret |
| `FABARA_WHATSAPP` | votre numéro avec indicatif, sans + (ex : 224620000000) |
| `FABARA_CURRENCY` | `GNF` |
| `APP_LOCALE` | `fr` |

4. Onglet **Settings → Volumes → New Volume**, point de montage : `/data` (garde la base et les photos).
5. Onglet **Settings → Networking → Generate Domain**. Ajoutez la variable `APP_URL` = cette adresse (https://…up.railway.app), puis laissez redéployer.

## 4. Premier lancement
Ouvrez l'adresse, puis `/connexion` avec `ADMIN_EMAIL` / `ADMIN_PASSWORD`.
Dans **Équipe et accès**, créez la gérante et les agents avec leurs propres mots de passe.
Dans **Paramètres**, remplissez nom, téléphone, adresse, email de contact.
Importez vos articles (Articles → Importer un fichier).

## 5. Votre nom de domaine (facultatif)
Achetez-le (Namecheap, OVH…), puis Railway → **Settings → Networking → Custom Domain**, et ajoutez chez le vendeur l'enregistrement qu'indique Railway. Mettez à jour `APP_URL`.

## Mises à jour
Chaque `git push` redéploie le site. La base et les photos ne sont pas touchées.

## Sauvegardes
Le volume contient tout. Exportez régulièrement (Comptabilité → exports) et gardez les fichiers ailleurs.
