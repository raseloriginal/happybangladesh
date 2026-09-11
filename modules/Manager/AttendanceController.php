<?php

class AttendanceController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check([ROLE_ADMIN, ROLE_MANAGER]);
        $this->viewPath = MOD_PATH . '/Manager/views';
        $this->db = Database::getInstance();
    }

    public function attendance(): void
        {
            $date = $this->get('date', date('Y-m-d'));

            // DSR attendance from QR scans
            $q = $this->db->prepare("
                SELECT da.*, u.name AS user_name, u.phone
                FROM dsr_attendance da
                JOIN users u ON u.id = da.dsr_id
                WHERE da.attendance_date = ?
                ORDER BY da.scan_time ASC
            ");
            $q->execute([$date]);
            $items = $q->fetchAll();

            // All DSRs
            try {
                $dsrs = $this->db->query("
                    SELECT u.id, u.name, u.phone FROM users u
                    WHERE u.role_id = (SELECT id FROM roles WHERE slug='dsr' LIMIT 1)
                    ORDER BY u.name
                ")->fetchAll();
            } catch (PDOException $e) { $dsrs = []; }

            // Active QR code
            try {
                $qrRow = $this->db->query("SELECT * FROM attendance_qr_codes WHERE is_active=1 ORDER BY id DESC LIMIT 1")->fetch();
            } catch (PDOException $e) { $qrRow = null; }

            // Users for manual form (kept for backwards compat)
            $users = $this->db->query("
                SELECT u.*, r.slug AS role_slug FROM users u JOIN roles r ON r.id=u.role_id
                WHERE r.slug IN ('sr','dsr') AND u.status=1 ORDER BY u.name
            ")->fetchAll();

            $this->render('attendance', compact('items', 'users', 'date', 'dsrs', 'qrRow'));
        }


    public function attendanceStore(): void
        {
            $this->verifyCsrf();
            $userId   = $this->post('user_id');
            $date     = $this->post('date', date('Y-m-d'));
            $status   = $this->post('status', 'present');
            $checkIn  = $this->post('check_in') ?: null;
            $checkOut = $this->post('check_out') ?: null;

            $exists = $this->db->prepare("SELECT id FROM attendance WHERE user_id=? AND date=?");
            $exists->execute([$userId, $date]);
            if ($exists->fetch()) {
                $this->db->prepare("UPDATE attendance SET status=?,check_in=?,check_out=? WHERE user_id=? AND date=?")
                         ->execute([$status, $checkIn, $checkOut, $userId, $date]);
            } else {
                $this->db->prepare("INSERT INTO attendance (user_id,date,check_in,check_out,status) VALUES (?,?,?,?,?)")
                         ->execute([$userId, $date, $checkIn, $checkOut, $status]);
            }
            $this->flash('success', 'Attendance saved.'); $this->redirect('manager/attendance?date='.$date);
        }


    public function apiAttendanceQrGet(): void
        {
            $row = $this->db->query("SELECT * FROM attendance_qr_codes WHERE is_active=1 ORDER BY id DESC LIMIT 1")->fetch();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'qr' => $row ?: null], JSON_UNESCAPED_UNICODE);
            exit;
        }


    public function apiAttendanceQrGenerate(): void
        {
            $this->db->exec("UPDATE attendance_qr_codes SET is_active=0");
            $code = 'HAPPYBANGLADESH_DSR_ATTENDANCE_' . strtoupper(bin2hex(random_bytes(6)));
            $this->db->prepare("INSERT INTO attendance_qr_codes (qr_code, generated_by, is_active) VALUES (?, ?, 1)")
                     ->execute([$code, Auth::id()]);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'qr_code' => $code], JSON_UNESCAPED_UNICODE);
            exit;
        }


}
