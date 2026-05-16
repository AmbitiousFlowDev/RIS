<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Système d'Information</title>
    <link rel="stylesheet" href="assets/css/security-ui.css">

    <!-- Tailwind CDN -->

    <!-- Font Awesome -->

    <!-- Page styles -->
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
        }

        .right-panel {
            background: linear-gradient(135deg, #2563eb, #1e40af);
        }

        .brand-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.15);
        }

        .check-icon {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }

        .help-icon {
            position: absolute;
            right: 32px;
            bottom: 32px;
            width: 44px;
            height: 44px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50">

<div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

    <!-- Left Panel -->
    <div class="flex items-center justify-center px-6 py-12 bg-white">
        <div class="w-full max-w-md">

            <h2 class="text-3xl font-bold text-gray-900 mb-2">
                Connexion
            </h2>

            <p class="text-gray-500 mb-8">
                Bienvenue ! Veuillez vous connecter à votre compte
            </p>

            <?php if (isset($error) && !empty($error)): ?>
                <div class="mb-6 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="/index.php?controller=Auth&action=login" method="POST" class="space-y-5">

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-semibold text-gray-600 mb-2">
                        Nom utilisateur
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Entrez votre nom utilisateur"
                        required
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-900 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-600 mb-2">
                        Mot de passe
                    </label>

                    <div class="flex">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Entrez votre mot de passe"
                            required
                            class="w-full rounded-l-lg border border-gray-300 px-4 py-3 text-gray-900 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-100"
                        >

                        <button
                            type="button"
                            onclick="togglePassword()"
                            class="rounded-r-lg border border-l-0 border-gray-300 px-4 text-gray-500 transition hover:bg-gray-50"
                        >
                            <i class="fa-regular fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Options -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input
                            type="checkbox"
                            id="rememberMe"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                        >
                        Se souvenir de moi
                    </label>

                    <a href="#" class="text-sm text-blue-600 hover:underline">
                        Mot de passe oublié ?
                    </a>
                </div>

                <!-- Submit -->
                <button
                    type="submit"
                    class="w-full rounded-lg bg-blue-600 px-4 py-3 font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300"
                >
                    Se connecter
                </button>

                <!-- Register -->
                <div class="text-center text-sm">
                    <span class="text-gray-500">
                        Vous n'avez pas de compte ?
                    </span>

                    <a href="#" class="font-semibold text-blue-600 hover:underline">
                        Créer un compte
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="right-panel relative hidden lg:flex flex-col items-center justify-center px-12 text-white">
        <div class="max-w-md">

            <div class="mb-8">
                <div class="brand-icon">
                    <i class="fa-solid fa-chart-simple text-white"></i>
                </div>
            </div>

            <h2 class="text-3xl font-bold mb-4">
                Système d'Information
            </h2>

            <p class="mb-10 text-lg text-white/75 leading-relaxed">
                Gérez efficacement votre distribution avec notre plateforme complète
            </p>

            <ul class="space-y-5 text-white">
                <li class="flex items-center">
                    <span class="check-icon">
                        <i class="fa-solid fa-check text-sm"></i>
                    </span>
                    <span>Gestion des clients et commandes</span>
                </li>

                <li class="flex items-center">
                    <span class="check-icon">
                        <i class="fa-solid fa-check text-sm"></i>
                    </span>
                    <span>Suivi des stocks en temps réel</span>
                </li>

                <li class="flex items-center">
                    <span class="check-icon">
                        <i class="fa-solid fa-check text-sm"></i>
                    </span>
                    <span>Tableaux de bord analytiques</span>
                </li>
            </ul>
        </div>

        <div class="help-icon">
            <i class="fa-solid fa-question"></i>
        </div>
    </div>

</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
</script>

</body>
</html>