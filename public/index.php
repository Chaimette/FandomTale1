<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Inscription - Fandom Tales</title>
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <h3>📚 Fandom Tales</h3>
            </div>
            <div class="nav-links">
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="stories.php">Histoires</a></li>
                    <li><a href="login.php">Connexion</a></li>
                </ul>
            </div>
        </nav>
    </header>
    
    <main>
        <div class="form-container">
            <h2>Créer un compte</h2>
            <p class="form-subtitle">Rejoignez la communauté Fandom Tales</p>
            
            <form action="register.php" method="POST" id="signupForm">
                <div class="input-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" name="username" id="username" placeholder="Votre nom d'utilisateur" required>
                </div>
                
                <div class="input-group">
                    <label for="email">Adresse email</label>
                    <input type="email" name="email" id="email" placeholder="votre@email.com" required>
                </div>
                
                <div class="input-row">
                    <div class="input-group">
                        <label for="first_name">Prénom</label>
                        <input type="text" name="first_name" id="first_name" placeholder="Prénom">
                    </div>
                    <div class="input-group">
                        <label for="last_name">Nom</label>
                        <input type="text" name="last_name" id="last_name" placeholder="Nom">
                    </div>
                </div>
                
                <div class="input-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" name="password" id="password" placeholder="Mot de passe sécurisé" required>
                </div>
                
                <div class="input-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirmez votre mot de passe" required>
                </div>
                
                <div class="input-group">
                    <label for="bio">Bio (optionnelle)</label>
                    <textarea name="bio" id="bio" rows="3" placeholder="Parlez-nous de vous et de vos fandoms préférés..."></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Créer mon compte</button>
            </form>
            
            <div class="form-footer">
                <p>Déjà membre ? <a href="login.php">Connectez-vous ici</a></p>
            </div>
        </div>
    </main>
    
    <footer>
        <p>&copy; 2024 Fandom Tales - Plateforme de fanfictions</p>
    </footer>
</body>
</html>