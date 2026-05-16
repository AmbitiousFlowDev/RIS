<?php


class AuthController extends Controller
{
    use AuthTrait;

    public function __construct()
    {
        $this->attach(new AuditObserver());
        $this->attach(new SyncLogObserver());
    }

    public function login(array $data = [])
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->render('auth/login');
            return;
        }

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        if ($this->authenticate($username, $password)) {
            $this->notify('user.login', $this->currentUser());
            $this->redirect('controller=Dashboard&action=index');
        } else {
            $error = "Invalid username or password";
            $this->render('auth/login', compact('error'));
        }
    }

    public function loginForm()
    {
        if ($this->checkAuth()) {
            $this->redirect('controller=Dashboard&action=index');
        }

        $this->render('auth/login');
    }

    public function logout()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('controller=Dashboard&action=index');
        }

        $user = $this->currentUser();
        
        $this->notify('user.logout', $user);
    
        unset($_SESSION['user']);
        session_destroy();
        
        $this->redirect('controller=Auth&action=loginForm');
    }
}
