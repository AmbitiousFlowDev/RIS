<?php

trait AuthTrait
{
    public function checkAuth(): bool
    {
        return isset($_SESSION['user']) && !empty($_SESSION['user']);
    }
    public function requireAuth(): void
    {
        if (!$this->checkAuth()) {
            header("Location: index.php?controller=Auth&action=loginForm");
            exit;
        }
    }

    /**
     * Require current user to have access to the given resource.
     * Redirects to dashboard with error if not allowed.
     * @param string $resource One of: dashboard, products, orders, clients, employees, users, audit
     */
    public function requireAccess(string $resource): void
    {
        $this->requireAuth();
        $user = $this->currentUser();
        if (!$user || !$user->canAccess($resource)) {
            header('Location: index.php?controller=Dashboard&action=index&error=forbidden');
            exit;
        }
    }
    public function currentUser(): ?UserInterface
    {
        $user = $_SESSION['user'] ?? null;
        return $user instanceof UserInterface ? $user : null;
    }
    public function authenticate(string $username, string $password): bool
    {
        $userModel = new User();
        $user = $userModel->findByCredentials($username, $password);

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user'] = $user;
            return true;
        }

        return false;
    }
    public function logout(): void
    {
        unset($_SESSION['user']);
        session_destroy();
        header("Location: login.php");
        exit;
    }
}
