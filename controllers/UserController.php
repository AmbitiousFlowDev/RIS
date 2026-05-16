<?php

class UserController extends Controller
{
    use AuthTrait;

    private User $userModel;
    private Employee $employee;

    public function __construct()
    {
        $this->userModel = new User();
        $this->employee = new Employee();
        $this->attach(new AuditObserver());
    }

    public function index()
    {
        $this->requireAccess('users');
        $users = $this->userModel->allWithEmployees();
        $employees = $this->employee->all();
        $currentUser = Auth::user();
        $this->render('users/index', compact('users', 'employees', 'currentUser'));
    }

    public function create(array $data = [])
    {
        $this->requireAccess('users');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userData = [
                'username' => $data['username'] ?? '',
                'password_hash' => password_hash((string)($data['password'] ?? ''), PASSWORD_DEFAULT),
                'user_category' => $data['user_category'] ?? 'EMPLOYEE',
                'employee_id' => (int)($data['employee_id'] ?? 0)
            ];

            $this->userModel->createUser($userData);
            $this->notify('user.created', ['username' => $userData['username']]);
            $this->redirect('controller=User&action=index');
        }
    }

    public function update(array $data = [])
    {
        $this->requireAccess('users');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int)($data['user_id'] ?? 0);
            
            $userData = [
                'username' => $data['username'] ?? '',
                'user_category' => $data['user_category'] ?? 'EMPLOYEE',
                'employee_id' => (int)($data['employee_id'] ?? 0)
            ];

            // If password is provided, update it
            if (!empty($data['password'])) {
                $userData['password_hash'] = password_hash((string)$data['password'], PASSWORD_DEFAULT);
            }

            $this->userModel->update($userId, $userData);
            $this->notify('user.updated', array_merge(['user_id' => $userId], $userData));
            $this->redirect('controller=User&action=index');
        }
    }

    public function delete(array $data = [])
    {
        $this->requireAccess('users');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int)($data['user_id'] ?? 0);
            $this->userModel->delete($userId);
            $this->notify('user.deleted', ['user_id' => $userId]);
            $this->redirect('controller=User&action=index');
        }
    }

    public function exportPDF()
    {
        $this->requireAccess('users');
        $users = $this->userModel->allWithEmployees();
        
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Title
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, 'Users List', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Table Header
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(147, 51, 234); // Purple
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(15, 10, 'ID', 1, 0, 'C', true);
        $pdf->Cell(50, 10, 'Username', 1, 0, 'C', true);
        $pdf->Cell(60, 10, 'Employee', 1, 0, 'C', true);
        $pdf->Cell(65, 10, 'Category', 1, 1, 'C', true);
        
        // Table Data
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFillColor(240, 240, 240);
        
        $fill = false;
        foreach ($users as $user) {
            $pdf->Cell(15, 8, $user['user_id'], 1, 0, 'C', $fill);
            $pdf->Cell(50, 8, $user['username'], 1, 0, 'L', $fill);
            $pdf->Cell(60, 8, substr($user['employee_name'] ?? 'N/A', 0, 28), 1, 0, 'L', $fill);
            $pdf->Cell(65, 8, $user['user_category'] ?? 'N/A', 1, 1, 'L', $fill);
            $fill = !$fill;
        }
        
        // Footer
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 0, 'C');
        
        $this->notify('user.exported', ['count' => count($users), 'format' => 'PDF']);
        
        $pdf->Output('D', 'users_' . date('Y-m-d') . '.pdf');
        exit;
    }
}
