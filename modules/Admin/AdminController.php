<?php
/**
 * AdminController — handles all admin panel pages
 */
class AdminController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check(ROLE_ADMIN);
        $this->viewPath = MOD_PATH . '/Admin/views';
        $this->db = Database::getInstance();
    }

    // ══════════════════════════════════════════════════════════
    //  Dashboard
    // ══════════════════════════════════════════════════════════
    public function dashboard(): void
    {
        $stats = [
            'total_users'       => $this->db->query("SELECT COUNT(*) FROM users WHERE status=1")->fetchColumn(),
            'total_managers'    => $this->db->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='manager' AND u.status=1")->fetchColumn(),
            'total_srs'         => $this->db->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='sr' AND u.status=1")->fetchColumn(),
            'total_dsrs'        => $this->db->query("SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='dsr' AND u.status=1")->fetchColumn(),
            'total_products'    => $this->db->query("SELECT COUNT(*) FROM products WHERE status=1")->fetchColumn(),
            'total_companies'   => $this->db->query("SELECT COUNT(*) FROM companies WHERE status=1")->fetchColumn(),
            'total_dealers'     => $this->db->query("SELECT COUNT(*) FROM dealers WHERE status=1")->fetchColumn(),
            'pending_orders'    => $this->db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn(),
            'pending_approvals' => $this->db->query("SELECT COUNT(*) FROM approvals WHERE status='pending'")->fetchColumn(),
            'total_warehouses'  => $this->db->query("SELECT COUNT(*) FROM warehouses WHERE status=1")->fetchColumn(),
            'today_expenses'    => $this->db->query("SELECT COALESCE(SUM(amount),0) FROM expenses WHERE date=CURDATE()")->fetchColumn(),
            'today_attendance'  => $this->db->query("SELECT COUNT(*) FROM attendance WHERE date=CURDATE() AND status='present'")->fetchColumn(),
        ];

        $recentOrders = $this->db->query("
            SELECT o.*, u.name AS sr_name, d.name AS dealer_name
            FROM orders o
            LEFT JOIN users u ON u.id = o.sr_id
            LEFT JOIN dealers d ON d.id = o.dealer_id
            ORDER BY o.created_at DESC LIMIT 8
        ")->fetchAll();

        $recentLogs = $this->db->query("
            SELECT l.*, u.name AS user_name
            FROM activity_logs l
            LEFT JOIN users u ON u.id = l.user_id
            ORDER BY l.created_at DESC LIMIT 8
        ")->fetchAll();

        $pageTitle = 'Dashboard';
        $this->render('dashboard', compact('stats', 'recentOrders', 'recentLogs', 'pageTitle'));
    }

    // ══════════════════════════════════════════════════════════
    //  Warehouses CRUD
    // ══════════════════════════════════════════════════════════
    public function warehouses(): void
    {
        $items = $this->db->query("SELECT * FROM warehouses WHERE status=1 ORDER BY created_at DESC")->fetchAll();
        $this->render('warehouses/index', compact('items'), 'main');
    }

    public function warehouseCreate(): void
    {
        $this->render('warehouses/form', ['item' => null, 'pageTitle' => 'Add Warehouse']);
    }

    public function warehouseStore(): void
    {
        $this->verifyCsrf();
        $name     = trim($this->post('name', ''));
        $location = trim($this->post('location', ''));
        $phone    = trim($this->post('phone', ''));

        if (!$name || !$location) {
            $this->flash('error', 'Name and location are required.');
            $this->redirect('admin/warehouses/create');
            return;
        }

        $this->db->prepare("INSERT INTO warehouses (name, location, phone) VALUES (?,?,?)")
                 ->execute([$name, $location, $phone]);
        $this->flash('success', 'Warehouse created successfully.');
        $this->redirect('admin/warehouses');
    }

    public function warehouseEdit(string $id): void
    {
        $item = $this->db->prepare("SELECT * FROM warehouses WHERE id=?");
        $item->execute([$id]);
        $item = $item->fetch();
        if (!$item) { $this->flash('error', 'Warehouse not found.'); $this->redirect('admin/warehouses'); return; }
        $this->render('warehouses/form', ['item' => $item, 'pageTitle' => 'Edit Warehouse']);
    }

    public function warehouseUpdate(string $id): void
    {
        $this->verifyCsrf();
        $stmt = $this->db->prepare("UPDATE warehouses SET name=?, location=?, phone=?, status=? WHERE id=?");
        $stmt->execute([
            trim($this->post('name')),
            trim($this->post('location')),
            trim($this->post('phone')),
            $this->post('status', 1),
            $id
        ]);
        $this->flash('success', 'Warehouse updated.');
        $this->redirect('admin/warehouses');
    }

    public function warehouseDelete(string $id): void
    {
        $this->db->prepare("UPDATE warehouses SET status=0 WHERE id=?")->execute([$id]);
        $this->flash('success', 'Warehouse deleted.');
        $this->redirect('admin/warehouses');
    }

    // ══════════════════════════════════════════════════════════
    //  User CRUD helper (Managers / SRs / DSRs)
    // ══════════════════════════════════════════════════════════
    private function usersByRole(string $role): array
    {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name AS role_name, w.name AS warehouse_name, c.name AS company_name,
                   (SELECT GROUP_CONCAT(DISTINCT d.name ORDER BY d.name SEPARATOR ', ') 
                    FROM dealer_companies dc 
                    JOIN dealers d ON d.id = dc.dealer_id 
                    WHERE dc.sr_id = u.id) AS dealer_names
            FROM users u
            JOIN roles r ON r.id = u.role_id
            LEFT JOIN warehouses w ON w.id = u.warehouse_id
            LEFT JOIN companies c ON c.id = u.company_id
            WHERE r.slug = ? AND u.status=1
            ORDER BY u.created_at DESC
        ");
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    private function storeUser(string $roleSlug): void
    {
        $this->verifyCsrf();
        $name     = trim($this->post('name', ''));
        $email    = trim($this->post('email', ''));
        $phone    = trim($this->post('phone', ''));
        $password = $this->post('password', '');
        $whId     = $this->post('warehouse_id') ?: null;
        $companyId= $this->post('company_id') ?: null;
        $targetAmt= $this->post('target_amount') ?: 0;

        if (!$name || !$password) {
            $this->flash('error', 'Name and password are required.');
            $this->redirect("admin/{$roleSlug}s/create");
            return;
        }

        if (!$email) {
            $email = strtolower($roleSlug) . '_' . time() . '_' . rand(100, 999) . '@dms.local';
        } else {
            // Check unique email
            $exists = $this->db->prepare("SELECT id FROM users WHERE email=?");
            $exists->execute([$email]);
            if ($exists->fetch()) {
                $this->flash('error', 'Email already exists.');
                $this->redirect("admin/{$roleSlug}s/create");
                return;
            }
        }

        $roleId = $this->db->prepare("SELECT id FROM roles WHERE slug=?");
        $roleId->execute([$roleSlug]);
        $roleId = $roleId->fetchColumn();

        $this->db->prepare("INSERT INTO users (role_id, warehouse_id, company_id, name, email, phone, password, target_amount) VALUES (?,?,?,?,?,?,?,?)")
                 ->execute([$roleId, $whId, $companyId, $name, $email, $phone, password_hash($password, PASSWORD_BCRYPT), $targetAmt]);

        $this->flash('success', ucfirst($roleSlug) . ' created successfully.');
    }

    private function updateUser(string $id, string $roleSlug): void
    {
        $this->verifyCsrf();
        $data = [
            'name'         => trim($this->post('name')),
            'phone'        => trim($this->post('phone')),
            'warehouse_id' => $this->post('warehouse_id') ?: null,
            'company_id'   => $this->post('company_id') ?: null,
            'target_amount'=> $this->post('target_amount') ?: 0,
            'status'       => $this->post('status', 1),
        ];

        $email = trim($this->post('email', ''));
        if ($email !== '') {
            // Check unique email
            $exists = $this->db->prepare("SELECT id FROM users WHERE email=? AND id!=?");
            $exists->execute([$email, $id]);
            if ($exists->fetch()) {
                $this->flash('error', 'Email already exists.');
                $this->redirect("admin/{$roleSlug}s");
                return;
            }
            $data['email'] = $email;
        }
        if ($pwd = $this->post('password')) {
            $data['password'] = password_hash($pwd, PASSWORD_BCRYPT);
        }
        $set  = implode(' = ?, ', array_keys($data)) . ' = ?';
        $vals = array_values($data);
        $vals[] = $id;
        $this->db->prepare("UPDATE users SET {$set} WHERE id=?")->execute($vals);
        $this->flash('success', ucfirst($roleSlug) . ' updated.');
    }

    // ── Managers ──────────────────────────────────────────────
    public function managers(): void
    {
        $items = $this->usersByRole('manager');
        $this->render('managers/index', compact('items'), 'main');
    }
    public function managerCreate(): void
    {
        $warehouses = $this->db->query("SELECT * FROM warehouses WHERE status=1 ORDER BY name")->fetchAll();
        $this->render('managers/form', ['item' => null, 'warehouses' => $warehouses, 'pageTitle' => 'Add Manager']);
    }
    public function managerStore(): void
    {
        $this->storeUser('manager');
        $this->redirect('admin/managers');
    }
    public function managerEdit(string $id): void
    {
        $item = $this->db->prepare("SELECT * FROM users WHERE id=?");
        $item->execute([$id]); $item = $item->fetch();
        $warehouses = $this->db->query("SELECT * FROM warehouses WHERE status=1 ORDER BY name")->fetchAll();
        $this->render('managers/form', ['item' => $item, 'warehouses' => $warehouses, 'pageTitle' => 'Edit Manager']);
    }
    public function managerUpdate(string $id): void
    {
        $this->updateUser($id, 'manager');
        $this->redirect('admin/managers');
    }
    public function managerDelete(string $id): void
    {
        $this->db->prepare("UPDATE users SET status=0 WHERE id=?")->execute([$id]);
        $this->flash('success', 'Manager deleted.');
        $this->redirect('admin/managers');
    }

    // ── SRs ───────────────────────────────────────────────────
    public function srs(): void
    {
        $items = $this->usersByRole('sr');
        $this->render('srs/index', compact('items'), 'main');
    }
    public function srCreate(): void
    {
        $companies = $this->db->query("SELECT * FROM companies WHERE status=1 ORDER BY name")->fetchAll();
        $this->render('srs/form', ['item' => null, 'companies' => $companies, 'pageTitle' => 'Add SR']);
    }
    public function srStore(): void { $this->storeUser('sr'); $this->redirect('admin/srs'); }
    public function srEdit(string $id): void
    {
        $item = $this->db->prepare("SELECT * FROM users WHERE id=?"); $item->execute([$id]); $item = $item->fetch();
        $companies = $this->db->query("SELECT * FROM companies WHERE status=1 ORDER BY name")->fetchAll();
        $this->render('srs/form', ['item' => $item, 'companies' => $companies, 'pageTitle' => 'Edit SR']);
    }
    public function srUpdate(string $id): void { $this->updateUser($id, 'sr'); $this->redirect('admin/srs'); }
    public function srDelete(string $id): void
    {
        $this->db->prepare("UPDATE users SET status=0 WHERE id=?")->execute([$id]);
        $this->flash('success', 'SR deleted.'); $this->redirect('admin/srs');
    }

    public function apiSrOrdersCutoff(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $srId = (int)($_GET['sr_id'] ?? 0);
        if (!$srId) {
            echo json_encode(['success' => false, 'message' => 'Invalid SR ID']);
            exit;
        }

        $days = [];
        for ($i = 0; $i < 5; $i++) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $formattedDate = date('d M, Y (D)', strtotime("-$i days"));

            $qOrd = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE sr_id = ? AND DATE(created_at) = ?");
            $qOrd->execute([$srId, $date]);
            $orderCount = (int)$qOrd->fetchColumn();

            $qCutoff = $this->db->prepare("SELECT id FROM sr_order_cutoffs WHERE sr_id = ? AND cutoff_date = ? AND undone_by IS NULL");
            $qCutoff->execute([$srId, $date]);
            $isCompleted = (bool)$qCutoff->fetchColumn();

            $days[] = [
                'date'           => $date,
                'formatted_date' => $formattedDate,
                'order_count'    => $orderCount,
                'is_completed'   => $isCompleted,
            ];
        }

        echo json_encode(['success' => true, 'days' => $days]);
        exit;
    }

    public function apiToggleSrPriceCorrection(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input = json_decode(file_get_contents('php://input'), true);
        $srId = (int)($input['sr_id'] ?? 0);
        $canCorrect = (int)($input['can_correct'] ?? 0);

        if (!$srId) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        $up = $this->db->prepare("UPDATE users SET can_correct_price = ? WHERE id = ? AND role_id = (SELECT id FROM roles WHERE slug = 'sr')");
        $up->execute([$canCorrect, $srId]);

        echo json_encode(['success' => true]);
        exit;
    }

    public function apiToggleSrOrderCutoff(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $input     = json_decode(file_get_contents('php://input'), true);
        $srId      = (int)($input['sr_id'] ?? 0);
        $date      = trim($input['date'] ?? '');
        $completed = (bool)($input['completed'] ?? false);
        $adminId   = Auth::id() ?? 1;

        if (!$srId || !$date) {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
            exit;
        }

        if ($completed) {
            $check = $this->db->prepare("SELECT id FROM sr_order_cutoffs WHERE sr_id = ? AND cutoff_date = ?");
            $check->execute([$srId, $date]);
            $existingId = $check->fetchColumn();

            if ($existingId) {
                $up = $this->db->prepare("UPDATE sr_order_cutoffs SET undone_by = NULL, undone_at = NULL, cutoff_at = NOW() WHERE id = ?");
                $up->execute([$existingId]);
            } else {
                $ins = $this->db->prepare("INSERT INTO sr_order_cutoffs (sr_id, cutoff_date, cutoff_at, is_auto) VALUES (?, ?, NOW(), 0)");
                $ins->execute([$srId, $date]);
            }
        } else {
            $up = $this->db->prepare("UPDATE sr_order_cutoffs SET undone_by = ?, undone_at = NOW() WHERE sr_id = ? AND cutoff_date = ? AND undone_by IS NULL");
            $up->execute([$adminId, $srId, $date]);
        }

        echo json_encode([
            'success'      => true,
            'is_completed' => $completed,
            'message'      => $completed ? 'Order marked as completed' : 'Order completion undone'
        ]);
        exit;
    }

    // ── DSRs ──────────────────────────────────────────────────
    public function dsrs(): void
    {
        $items = $this->usersByRole('dsr');
        $this->render('dsrs/index', compact('items'), 'main');
    }
    public function dsrCreate(): void
    {
        $warehouses = $this->db->query("SELECT * FROM warehouses WHERE status=1 ORDER BY name")->fetchAll();
        $this->render('dsrs/form', ['item' => null, 'warehouses' => $warehouses, 'pageTitle' => 'Add DSR']);
    }
    public function dsrStore(): void { $this->storeUser('dsr'); $this->redirect('admin/dsrs'); }
    public function dsrEdit(string $id): void
    {
        $item = $this->db->prepare("SELECT * FROM users WHERE id=?"); $item->execute([$id]); $item = $item->fetch();
        $warehouses = $this->db->query("SELECT * FROM warehouses WHERE status=1 ORDER BY name")->fetchAll();
        $this->render('dsrs/form', ['item' => $item, 'warehouses' => $warehouses, 'pageTitle' => 'Edit DSR']);
    }
    public function dsrUpdate(string $id): void { $this->updateUser($id, 'dsr'); $this->redirect('admin/dsrs'); }
    public function dsrDelete(string $id): void
    {
        $this->db->prepare("UPDATE users SET status=0 WHERE id=?")->execute([$id]);
        $this->flash('success', 'DSR deleted.'); $this->redirect('admin/dsrs');
    }

    // ══════════════════════════════════════════════════════════
    //  Companies CRUD
    // ══════════════════════════════════════════════════════════
    public function companies(): void
    {
        $items = $this->db->query("SELECT * FROM companies WHERE status=1 ORDER BY created_at DESC")->fetchAll();
        $this->render('companies/index', compact('items'), 'main');
    }
    public function companyCreate(): void { $this->render('companies/form', ['item' => null, 'pageTitle' => 'Add Company']); }
    public function companyStore(): void
    {
        $this->verifyCsrf();
        $this->db->prepare("INSERT INTO companies (name, contact, email, phone, address) VALUES (?,?,?,?,?)")
                 ->execute([trim($this->post('name')), trim($this->post('contact')), trim($this->post('email')), trim($this->post('phone')), trim($this->post('address'))]);
        $this->flash('success', 'Company added.'); $this->redirect('admin/companies');
    }
    public function companyEdit(string $id): void
    {
        $s = $this->db->prepare("SELECT * FROM companies WHERE id=?"); $s->execute([$id]); $item = $s->fetch();
        $this->render('companies/form', ['item' => $item, 'pageTitle' => 'Edit Company']);
    }
    public function companyUpdate(string $id): void
    {
        $this->verifyCsrf();
        $this->db->prepare("UPDATE companies SET name=?,contact=?,email=?,phone=?,address=?,status=? WHERE id=?")
                 ->execute([trim($this->post('name')), trim($this->post('contact')), trim($this->post('email')), trim($this->post('phone')), trim($this->post('address')), $this->post('status',1), $id]);
        $this->flash('success', 'Company updated.'); $this->redirect('admin/companies');
    }
    public function companyDelete(string $id): void
    {
        $this->db->prepare("UPDATE companies SET status=0 WHERE id=?")->execute([$id]);
        $this->flash('success', 'Company deleted.'); $this->redirect('admin/companies');
    }

    // ══════════════════════════════════════════════════════════
    //  Dealers CRUD
    // ══════════════════════════════════════════════════════════
    public function dealers(): void
    {
        $items = $this->db->query("
            SELECT d.*, w.name AS warehouse_name 
            FROM dealers d 
            LEFT JOIN warehouses w ON w.id = d.warehouse_id 
            WHERE d.status=1
            ORDER BY d.created_at DESC
        ")->fetchAll();
        $this->render('dealers/index', compact('items'), 'main');
    }

    public function dealerCreate(): void
    {
        $warehouses = $this->db->query("SELECT * FROM warehouses WHERE status=1 ORDER BY name")->fetchAll();
        $companies  = $this->db->query("SELECT * FROM companies WHERE status=1 ORDER BY name")->fetchAll();
        $srs        = $this->db->query("SELECT id, name, company_id, warehouse_id FROM users WHERE role_id=3 AND status=1 ORDER BY name")->fetchAll();

        
        $this->render('dealers/form', [
            'item' => null, 
            'warehouses' => $warehouses, 
            'companies' => $companies, 
            'srs' => $srs, 
            'dealer_companies' => [], 
            'pageTitle' => 'Add Dealer'
        ]);
    }

    public function dealerStore(): void
    {
        $this->verifyCsrf();
        
        $username = trim($this->post('username')) ?: null;
        if ($username) {
            $chk = $this->db->prepare("SELECT id FROM dealers WHERE username=?");
            $chk->execute([$username]);
            if ($chk->fetch()) {
                $this->flash('error', 'Username "' . $username . '" is already taken.');
                $this->redirect('admin/dealers/create');
                return;
            }
        }

        $this->db->beginTransaction();
        
        try {
            $password = trim($this->post('password'));
            $hashedPassword = $password ? password_hash($password, PASSWORD_DEFAULT) : null;

            $this->db->prepare("INSERT INTO dealers (warehouse_id, name, username, password, phone, address, trade_license, business_name, happy_commission) VALUES (?,?,?,?,?,?,?,?,?)")
                     ->execute([
                         $this->post('warehouse_id') ?: null, 
                         trim($this->post('name')), 
                         $username,
                         $hashedPassword,
                         trim($this->post('phone')), 
                         trim($this->post('address')), 
                         trim($this->post('trade_license')), 
                         trim($this->post('business_name')), 
                         $this->post('happy_commission', 0.00)
                     ]);
            
            $dealerId = $this->db->lastInsertId();

            if (!$username) {
                $defaultUsername = 'dealer_' . $dealerId;
                $this->db->prepare("UPDATE dealers SET username=? WHERE id=?")->execute([$defaultUsername, $dealerId]);
            }
            
            $cIds = $_POST['company_id'] ?? [];
            $sIds = $_POST['sr_id'] ?? [];

            $stmt = $this->db->prepare("INSERT INTO dealer_companies (dealer_id, company_id, sr_id) VALUES (?,?,?)");
            $seen = [];
            foreach ($cIds as $idx => $cid) {
                $sid = $sIds[$idx] ?? '';
                if (!empty($cid) && !empty($sid)) {
                    $key = $cid . '-' . $sid;
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    $stmt->execute([$dealerId, $cid, $sid]);
                }
            }
            
            $this->db->commit();
            $this->flash('success', 'Dealer added.');
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->flash('error', 'Failed to save dealer: ' . $e->getMessage());
        }
        $this->redirect('admin/dealers');
    }

    public function dealerEdit(string $id): void
    {
        $s = $this->db->prepare("SELECT * FROM dealers WHERE id=?"); $s->execute([$id]); $item = $s->fetch();
        
        $warehouses = $this->db->query("SELECT * FROM warehouses WHERE status=1 ORDER BY name")->fetchAll();
        $companies  = $this->db->query("SELECT * FROM companies WHERE status=1 ORDER BY name")->fetchAll();
        $srs        = $this->db->query("SELECT id, name, company_id, warehouse_id FROM users WHERE role_id=3 AND status=1 ORDER BY name")->fetchAll();

        
        $dcStmt = $this->db->prepare("SELECT * FROM dealer_companies WHERE dealer_id=?");
        $dcStmt->execute([$id]);
        $dealer_companies = $dcStmt->fetchAll();
        
        $this->render('dealers/form', [
            'item' => $item, 
            'warehouses' => $warehouses, 
            'companies' => $companies, 
            'srs' => $srs, 
            'dealer_companies' => $dealer_companies,
            'pageTitle' => 'Edit Dealer'
        ]);
    }

    public function dealerUpdate(string $id): void
    {
        $this->verifyCsrf();
        
        $username = trim($this->post('username')) ?: null;
        if ($username) {
            $chk = $this->db->prepare("SELECT id FROM dealers WHERE username=? AND id!=?");
            $chk->execute([$username, $id]);
            if ($chk->fetch()) {
                $this->flash('error', 'Username "' . $username . '" is already taken by another dealer.');
                $this->redirect('admin/dealers/edit/' . $id);
                return;
            }
        }

        $this->db->beginTransaction();
        
        try {
            $password = trim($this->post('password'));

            $this->db->prepare("UPDATE dealers SET warehouse_id=?,name=?,username=?,phone=?,address=?,trade_license=?,business_name=?,happy_commission=?,status=? WHERE id=?")
                     ->execute([
                         $this->post('warehouse_id') ?: null, 
                         trim($this->post('name')), 
                         $username,
                         trim($this->post('phone')), 
                         trim($this->post('address')), 
                         trim($this->post('trade_license')), 
                         trim($this->post('business_name')), 
                         $this->post('happy_commission',0.00), 
                         $this->post('status',1), 
                         $id
                     ]);

            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $this->db->prepare("UPDATE dealers SET password=? WHERE id=?")->execute([$hashedPassword, $id]);
            }
            
            $this->db->prepare("DELETE FROM dealer_companies WHERE dealer_id=?")->execute([$id]);
            
            $cIds = $_POST['company_id'] ?? [];
            $sIds = $_POST['sr_id'] ?? [];
            
            $stmt = $this->db->prepare("INSERT INTO dealer_companies (dealer_id, company_id, sr_id) VALUES (?,?,?)");
            $seen = [];
            foreach ($cIds as $idx => $cid) {
                $sid = $sIds[$idx] ?? '';
                if (!empty($cid) && !empty($sid)) {
                    $key = $cid . '-' . $sid;
                    if (isset($seen[$key])) {
                        continue;
                    }
                    $seen[$key] = true;
                    $stmt->execute([$id, $cid, $sid]);
                }
            }
            
            $this->db->commit();
            $this->flash('success', 'Dealer updated.');
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->flash('error', 'Failed to update dealer: ' . $e->getMessage());
        }
        $this->redirect('admin/dealers');
    }

    public function dealerDelete(string $id): void
    {
        $this->db->prepare("UPDATE dealers SET status=0 WHERE id=?")->execute([$id]);
        $this->flash('success', 'Dealer deleted.'); $this->redirect('admin/dealers');
    }

    // ══════════════════════════════════════════════════════════
    //  Approvals
    // ══════════════════════════════════════════════════════════
    public function approvals(): void
    {
        $items = $this->db->query("
            SELECT a.*, u.name AS requester_name
            FROM approvals a
            JOIN users u ON u.id = a.requested_by
            ORDER BY a.created_at DESC
        ")->fetchAll();
        $this->render('approvals', compact('items'), 'main');
    }

    public function approvalApprove(string $id): void
    {
        // Fetch approval
        $stmt = $this->db->prepare("SELECT * FROM approvals WHERE id = ?");
        $stmt->execute([$id]);
        $approval = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($approval && $approval['status'] === 'pending') {
            if ($approval['module'] === 'products_price' && $approval['action'] === 'edit') {
                $newData = json_decode($approval['new_data'], true);
                $oldData = json_decode($approval['old_data'] ?? '{}', true);
                if ($newData) {
                    $updateStmt = $this->db->prepare("UPDATE products SET buying_price = ?, price = ? WHERE id = ?");
                    $updateStmt->execute([$newData['buying_price'], $newData['price'], $approval['record_id']]);

                    \Helpers::logProductPriceChange(
                        (int)$approval['record_id'],
                        isset($oldData['buying_price']) ? (float)$oldData['buying_price'] : null,
                        (float)$newData['buying_price'],
                        isset($oldData['price']) ? (float)$oldData['price'] : null,
                        (float)$newData['price'],
                        \Auth::id(),
                        'admin_approval',
                        'Approved price correction requested by user #' . $approval['requested_by']
                    );
                }
            }

            // Handle lot batch edit approval
            if ($approval['module'] === 'lots_batch' && $approval['action'] === 'edit') {
                $newData = json_decode($approval['new_data'], true);
                if ($newData) {
                    $wid = $this->db->query("SELECT id FROM warehouses LIMIT 1")->fetchColumn() ?: 1;
                    $orig_company_id = (int)$newData['original_company_id'];
                    $orig_lot_date   = $newData['original_lot_date'];
                    $new_company_id  = (int)($newData['company_id'] ?? $orig_company_id);
                    $new_lot_date    = $newData['lot_date'] ?? $orig_lot_date;

                    $this->db->beginTransaction();
                    try {
                        // Revert old lots inventory and delete them
                        $oldLots = $this->db->prepare("
                            SELECT l.id, l.product_id, l.qty_pieces
                            FROM lots l JOIN products p ON p.id = l.product_id
                            WHERE p.company_id = ? AND (l.lot_date = ? OR (l.lot_date IS NULL AND DATE(l.created_at) = ?))
                        ");
                        $oldLots->execute([$orig_company_id, $orig_lot_date, $orig_lot_date]);
                        foreach ($oldLots->fetchAll() as $o) {
                            $this->db->prepare(
                                "UPDATE inventory SET qty_pieces = GREATEST(0, qty_pieces - ?) WHERE product_id=? AND warehouse_id=? AND lot_id=?"
                            )->execute([$o['qty_pieces'], $o['product_id'], $wid, $o['id']]);
                            $this->db->prepare("DELETE FROM lots WHERE id=?")->execute([$o['id']]);
                        }

                        // Insert new lots
                        $lotStmt = $this->db->prepare(
                            "INSERT INTO lots (product_id, lot_date, expiry_date, qty_boxes, buying_price, lot_number, qty_pieces) VALUES (?,?,?,0,?,NULL,?)"
                        );
                        foreach ($newData['lots'] as $lot) {
                            $product_id   = (int)($lot['product_id'] ?? 0);
                            $qty_pieces   = (int)($lot['qty_pieces'] ?? 0);
                            $buying_price = (float)($lot['buying_price'] ?? 0);
                            $expiry_date  = $lot['expiry_date'] ?: null;
                            if (!$product_id) continue;

                            $lotStmt->execute([$product_id, $new_lot_date, $expiry_date, $buying_price, $qty_pieces]);
                            $lot_id = $this->db->lastInsertId();

                            $this->db->prepare(
                                "INSERT INTO inventory (warehouse_id, product_id, lot_id, qty_boxes, qty_pieces)
                                 VALUES (?,?,?,0,?)
                                 ON DUPLICATE KEY UPDATE qty_pieces = qty_pieces + VALUES(qty_pieces)"
                            )->execute([$wid, $product_id, $lot_id, $qty_pieces]);

                            $prod = $this->db->prepare("SELECT buying_price, price, pieces_per_box, dealer_percentage FROM products WHERE id=?");
                            $prod->execute([$product_id]);
                            $p = $prod->fetch();
                            if ($p) {
                                $ppb = max(1, (float)$p['pieces_per_box']);
                                $dp  = (float)$p['dealer_percentage'];
                                $selling_price = round($buying_price * (1 + $dp / 100) / $ppb, 2);
                                $this->db->prepare("UPDATE products SET buying_price=?, price=? WHERE id=?")
                                         ->execute([$buying_price, $selling_price, $product_id]);

                                if ($buying_price != (float)$p['buying_price'] || $selling_price != (float)$p['price']) {
                                    \Helpers::logProductPriceChange(
                                        $product_id,
                                        (float)$p['buying_price'],
                                        $buying_price,
                                        (float)$p['price'],
                                        $selling_price,
                                        \Auth::id(),
                                        'admin_approval',
                                        'Approved lot batch update by Admin'
                                    );
                                }
                            }
                        }
                        $this->db->commit();
                    } catch (\Exception $e) {
                        $this->db->rollBack();
                        $this->flash('error', 'Lot batch update failed: ' . $e->getMessage());
                        $this->redirect('admin/approvals');
                        return;
                    }
                }
            }

            $this->db->prepare("UPDATE approvals SET status='approved', approved_by=?, updated_at=NOW() WHERE id=?")
                     ->execute([Auth::id(), $id]);
            $this->flash('success', 'Request approved.');
        } else {
            $this->flash('error', 'Request could not be approved.');
        }
        
        $this->redirect('admin/approvals');
    }

    public function approvalReject(string $id): void
    {
        $this->db->prepare("UPDATE approvals SET status='rejected', approved_by=?, updated_at=NOW() WHERE id=?")
                 ->execute([Auth::id(), $id]);
        $this->flash('warning', 'Request rejected.');
        $this->redirect('admin/approvals');
    }

    // ══════════════════════════════════════════════════════════
    //  Reports
    // ══════════════════════════════════════════════════════════
    public function reports(): void
    {
        $from = $this->get('from', date('Y-m-01'));
        $to   = $this->get('to',   date('Y-m-d'));

        $orderStats = $this->db->prepare("
            SELECT DATE(created_at) AS day, COUNT(*) AS count, SUM(total_amount) AS revenue
            FROM orders WHERE created_at BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY day DESC
        ");
        $orderStats->execute([$from . ' 00:00:00', $to . ' 23:59:59']);
        $orderStats = $orderStats->fetchAll();

        $topProducts = $this->db->query("
            SELECT p.name, SUM(oi.quantity) AS qty, SUM(oi.total_price) AS revenue
            FROM order_items oi JOIN products p ON p.id=oi.product_id
            GROUP BY oi.product_id ORDER BY revenue DESC LIMIT 10
        ")->fetchAll();

        $this->render('reports', compact('orderStats', 'topProducts', 'from', 'to'), 'main');
    }

    // ══════════════════════════════════════════════════════════
    //  Database Sync
    // ══════════════════════════════════════════════════════════
    public function databaseSync(): void
    {
        // Ensure database_migrations table exists
        $this->ensureMigrationsTable();

        $updatesDir = ROOT_PATH . '/database/updates';
        if (!is_dir($updatesDir)) {
            mkdir($updatesDir, 0755, true);
        }

        // Scan all .sql files in database/updates
        $sqlFiles = glob($updatesDir . '/*.sql');
        sort($sqlFiles);

        // Fetch execution records from database_migrations
        $executedStmt = $this->db->query("SELECT * FROM database_migrations");
        $executedRows = $executedStmt->fetchAll(PDO::FETCH_ASSOC);

        $migrationMap = [];
        foreach ($executedRows as $row) {
            $migrationMap[$row['migration_file']] = $row;
        }

        $filesList = [];
        $pendingCount = 0;
        $syncedCount = 0;
        $failedCount = 0;

        foreach ($sqlFiles as $filePath) {
            $filename = basename($filePath);
            $record = $migrationMap[$filename] ?? null;

            $currentHash = md5_file($filePath);
            $status = $record ? $record['status'] : 'pending';
            $executedAt = $record ? $record['executed_at'] : null;
            $errorMessage = $record ? $record['error_message'] : null;
            $storedHash = $record ? ($record['file_hash'] ?? null) : null;

            $isModified = false;
            if ($status === 'success') {
                if ($storedHash !== null && $storedHash !== $currentHash) {
                    $status = 'modified';
                    $isModified = true;
                    $pendingCount++;
                } else {
                    $syncedCount++;
                }
            } elseif ($status === 'failed') {
                $failedCount++;
            } else {
                $pendingCount++;
            }

            $filesList[] = [
                'filename'      => $filename,
                'path'          => $filePath,
                'size'          => filesize($filePath),
                'status'        => $status, // 'success', 'failed', 'pending', or 'modified'
                'is_modified'   => $isModified,
                'executed_at'   => $executedAt,
                'error_message' => $errorMessage,
            ];
        }

        $pageTitle = 'Database Sync';
        $this->render('database_sync', compact(
            'filesList', 
            'pendingCount', 
            'syncedCount', 
            'failedCount', 
            'pageTitle'
        ));
    }

    public function databaseSyncRun(): void
    {
        $this->verifyCsrf();
        $this->ensureMigrationsTable();

        $targetFile = $this->post('file', ''); // specific file, 'all', or 'force:filename'
        $updatesDir = ROOT_PATH . '/database/updates';

        if (!is_dir($updatesDir)) {
            $this->flash('error', 'Updates directory not found.');
            $this->redirect('admin/database-sync');
            return;
        }

        $executedStmt = $this->db->query("SELECT * FROM database_migrations");
        $executedRows = $executedStmt->fetchAll(PDO::FETCH_ASSOC);
        $migrationMap = [];
        foreach ($executedRows as $row) {
            $migrationMap[$row['migration_file']] = $row;
        }

        $sqlFiles = glob($updatesDir . '/*.sql');
        sort($sqlFiles);

        $isForce = false;
        if (str_starts_with($targetFile, 'force:')) {
            $isForce = true;
            $targetFile = substr($targetFile, 6);
        }

        $filesToRun = [];
        foreach ($sqlFiles as $filePath) {
            $filename = basename($filePath);
            $record = $migrationMap[$filename] ?? null;
            $currentHash = md5_file($filePath);
            $status = $record ? $record['status'] : 'pending';
            $storedHash = $record ? ($record['file_hash'] ?? null) : null;

            $needsRun = ($status !== 'success')
                || ($storedHash !== null && $storedHash !== $currentHash)
                || ($storedHash === null && $status === 'success' && $isForce)
                || $isForce;

            if ($targetFile === 'all') {
                if ($needsRun) {
                    $filesToRun[] = ['filename' => $filename, 'path' => $filePath, 'hash' => $currentHash];
                }
            } elseif ($targetFile === $filename) {
                $filesToRun[] = ['filename' => $filename, 'path' => $filePath, 'hash' => $currentHash];
            }
        }

        if (empty($filesToRun)) {
            $this->flash('warning', 'No pending or modified migrations found to execute.');
            $this->redirect('admin/database-sync');
            return;
        }

        $successCount = 0;
        $failedFile = null;
        $errorDetails = null;

        foreach ($filesToRun as $fileInfo) {
            $filename = $fileInfo['filename'];
            $filePath = $fileInfo['path'];
            $fileHash = $fileInfo['hash'];
            $sqlContent = file_get_contents($filePath);

            try {
                $this->db->beginTransaction();
                $this->db->exec("SET FOREIGN_KEY_CHECKS = 0;");

                // Parse queries by semicolon safely
                $queries = array_filter(array_map('trim', explode(';', $sqlContent)));
                foreach ($queries as $query) {
                    // Ignore empty lines or comments-only blocks
                    $cleanQuery = preg_replace('/--.*$/m', '', $query);
                    $cleanQuery = preg_replace('/\/\*.*?\*\//s', '', $cleanQuery);
                    if (!empty(trim($cleanQuery))) {
                        try {
                            $this->db->exec($query);
                        } catch (\PDOException $pe) {
                            $errInfo = $pe->errorInfo[1] ?? 0;
                            // Ignore duplicate column (1060), duplicate key (1061), table exists (1050)
                            if (!in_array($errInfo, [1060, 1061, 1050])) {
                                throw $pe;
                            }
                        }
                    }
                }

                $this->db->exec("SET FOREIGN_KEY_CHECKS = 1;");

                // Record successful execution
                $stmt = $this->db->prepare("
                    INSERT INTO database_migrations (migration_file, status, file_hash, error_message, executed_at) 
                    VALUES (?, 'success', ?, NULL, NOW()) 
                    ON DUPLICATE KEY UPDATE status = 'success', file_hash = ?, error_message = NULL, executed_at = NOW()
                ");
                $stmt->execute([$filename, $fileHash, $fileHash]);

                if ($this->db->inTransaction()) {
                    $this->db->commit();
                }
                $successCount++;
            } catch (\Throwable $e) {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                }
                
                try {
                    $this->db->exec("SET FOREIGN_KEY_CHECKS = 1;");
                } catch (\Throwable $ex) {}

                $failedFile = $filename;
                $errorDetails = $e->getMessage();

                // Record failure
                $stmt = $this->db->prepare("
                    INSERT INTO database_migrations (migration_file, status, file_hash, error_message, executed_at) 
                    VALUES (?, 'failed', ?, ?, NOW()) 
                    ON DUPLICATE KEY UPDATE status = 'failed', file_hash = ?, error_message = ?, executed_at = NOW()
                ");
                $stmt->execute([$filename, $fileHash, $errorDetails, $fileHash, $errorDetails]);

                // Stop immediately on failure
                break;
            }
        }

        if ($failedFile !== null) {
            $msg = "Migration stopped! Failed on file '{$failedFile}': {$errorDetails}";
            if ($successCount > 0) {
                $msg = "Successfully applied {$successCount} migration(s), but stopped on '{$failedFile}': {$errorDetails}";
            }
            $this->flash('error', $msg);
        } else {
            $this->flash('success', "Successfully synced {$successCount} database update(s).");
        }

        $this->redirect('admin/database-sync');
    }

    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `database_migrations` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `migration_file` VARCHAR(255) NOT NULL UNIQUE,
            `status` ENUM('success', 'failed') NOT NULL DEFAULT 'success',
            `file_hash` VARCHAR(64) NULL,
            `error_message` TEXT NULL,
            `executed_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $this->db->exec($sql);

        try {
            $cols = $this->db->query("SHOW COLUMNS FROM `database_migrations` LIKE 'file_hash'")->fetch();
            if (!$cols) {
                $this->db->exec("ALTER TABLE `database_migrations` ADD COLUMN `file_hash` VARCHAR(64) NULL AFTER `status`;");
            }
        } catch (\Throwable $t) {}
    }

    public function databaseClear(): void
    {
        $this->verifyCsrf();
        $currentUserId = Auth::id();

        try {
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 0;");

            $tablesToTruncate = [
                'activity_logs', 'approvals', 'attendance', 'categories', 
                'companies', 'dealer_companies', 'dealers', 'dispatch_extras', 
                'dispatch_items', 'dispatch_schedule_srs', 'dispatch_schedules', 
                'dispatches', 'expenses', 'inventory', 'lots', 
                'order_items', 'orders', 'products', 'readysales', 'retailers', 
                'return_items', 'returns', 'sales_reports', 'settlements', 
                'van_stock'
            ];

            foreach ($tablesToTruncate as $table) {
                $this->db->exec("TRUNCATE TABLE `{$table}`");
            }

            // Truncate warehouses and insert a default one to avoid foreign key or Auth errors
            $this->db->exec("TRUNCATE TABLE `warehouses`");
            $this->db->exec("INSERT INTO `warehouses` (`id`, `name`, `location`, `status`) VALUES (1, 'Default Warehouse', 'Tejgaon, Dhaka', 1)");

            // Delete all users except the current admin
            $this->db->prepare("DELETE FROM users WHERE id != ?")->execute([$currentUserId]);
            
            // Link current admin to the default warehouse
            $this->db->prepare("UPDATE users SET warehouse_id = 1 WHERE id = ?")->execute([$currentUserId]);

            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1;");

            $this->flash('success', 'Database successfully cleared! Only your Admin user remains.');
        } catch (PDOException $e) {
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1;");
            $this->flash('error', 'Failed to clear database: ' . $e->getMessage());
        }

        $this->redirect('admin/database-sync');
    }

    // ══════════════════════════════════════════════════════════
    //  Clear Dispatch Data (keep all master data intact)
    // ══════════════════════════════════════════════════════════
    public function dispatchClear(): void
    {
        $this->verifyCsrf();

        try {
            $this->db->beginTransaction();

            // Disable FK checks inside the transaction for safe ordered deletes
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 0;");

            // 1. Child records of dispatch_items (none currently, but future-proof)
            // 2. dispatch_items → child of dispatches
            $this->db->exec("DELETE FROM `dispatch_items`");

            // 3. dispatch_extras → child of dispatch_schedules
            $this->db->exec("DELETE FROM `dispatch_extras`");

            // 4. dispatch_schedule_srs → child of dispatch_schedules
            $this->db->exec("DELETE FROM `dispatch_schedule_srs`");

            // 5. dispatch_schedules (now safe, all children removed)
            $this->db->exec("DELETE FROM `dispatch_schedules`");

            // 6. dispatches (now safe)
            $this->db->exec("DELETE FROM `dispatches`");

            // 7. van_stock — DSR/SR loaded stock from dispatches
            $this->db->exec("DELETE FROM `van_stock`");

            // 8. sr_order_cutoffs — date-based SR order completion flags tied to dispatch cycle
            $this->db->exec("DELETE FROM `sr_order_cutoffs`");

            // 9. readysales — ready/direct sales created during dispatch
            $this->db->exec("DELETE FROM `readysales`");

            // 10. Reset orders that were dispatched back to 'confirmed' so organize can re-process them
            $this->db->exec("UPDATE `orders` SET status = 'confirmed' WHERE status = 'dispatched'");

            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1;");

            $this->db->commit();

            $this->flash('success', 'Dispatch data cleared successfully. You can now start a fresh dispatch cycle.');
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            try { $this->db->exec("SET FOREIGN_KEY_CHECKS = 1;"); } catch (\Throwable $ex) {}
            $this->flash('error', 'Failed to clear dispatch data: ' . $e->getMessage());
        }

        $this->redirect('admin/database-sync');
    }


    private function parseSchemaSql(): array
    {
        $filePath = ROOT_PATH . '/database/migrations/schema.sql';
        if (!file_exists($filePath)) {
            return [];
        }

        $sql = file_get_contents($filePath);
        
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        // Extract CREATE TABLE blocks
        preg_match_all('/CREATE TABLE(?: IF NOT EXISTS)?\s+`?([a-zA-Z0-9_-]+)`?\s*\((.*?)\)\s*(?:ENGINE\s*=\s*\w+)?\s*(?:DEFAULT\s+CHARSET\s*=\s*\w+)?\s*;/si', $sql, $matches, PREG_SET_ORDER);

        $tables = [];
        foreach ($matches as $match) {
            $tableName = $match[1];
            $body = $match[2];
            
            // Split body by commas, keeping track of parenthesis depth
            $lines = [];
            $currentLine = '';
            $depth = 0;
            for ($i = 0; $i < strlen($body); $i++) {
                $char = $body[$i];
                if ($char === '(') $depth++;
                if ($char === ')') $depth--;
                
                if ($char === ',' && $depth === 0) {
                    $lines[] = trim($currentLine);
                    $currentLine = '';
                } else {
                    $currentLine .= $char;
                }
            }
            if (trim($currentLine) !== '') {
                $lines[] = trim($currentLine);
            }

            $columns = [];
            $constraints = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                if (preg_match('/^(CONSTRAINT|PRIMARY KEY|UNIQUE KEY|KEY|FOREIGN KEY|UNIQUE|INDEX)/i', $line)) {
                    $constraints[] = $line;
                } else if (preg_match('/^`?([a-zA-Z0-9_-]+)`?\s+(.+)$/', $line, $colMatch)) {
                    $colName = $colMatch[1];
                    $columns[$colName] = $line;
                }
            }

            $tables[$tableName] = [
                'full_sql' => $match[0],
                'columns' => $columns,
                'constraints' => $constraints
            ];
        }

        return $tables;
    }

    // ══════════════════════════════════════════════════════════
    //  Import Retailers
    // ══════════════════════════════════════════════════════════
    public function retailersImport(): void
    {
        $pageTitle = 'Import Retailers';
        $this->render('retailers_import', compact('pageTitle'));
    }

    public function retailersImportPost(): void
    {
        $this->verifyCsrf();

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Please select a valid CSV file.');
            $this->redirect('admin/retailers/import');
            return;
        }

        $fileTmp = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($fileTmp, 'r');
        if (!$handle) {
            $this->flash('error', 'Failed to open the uploaded file.');
            $this->redirect('admin/retailers/import');
            return;
        }

        // Read header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            $this->flash('error', 'The CSV file is empty.');
            $this->redirect('admin/retailers/import');
            return;
        }

        // Remove BOM if present
        if (substr($header[0], 0, 3) === "\xEF\xBB\xBF") {
            $header[0] = substr($header[0], 3);
        }

        // Normalize headers
        $header = array_map(function($h) {
            return strtolower(trim($h));
        }, $header);

        $nameIdx = -1;
        $phoneIdx = -1;
        $latIdx = -1;
        $lngIdx = -1;

        foreach ($header as $idx => $col) {
            if (in_array($col, ['name', 'store name', 'store', 'retailer'])) {
                $nameIdx = $idx;
            } elseif (in_array($col, ['phone', 'number', 'phone number', 'mobile'])) {
                $phoneIdx = $idx;
            } elseif (in_array($col, ['lat', 'latitude'])) {
                $latIdx = $idx;
            } elseif (in_array($col, ['lng', 'longitude'])) {
                $lngIdx = $idx;
            }
        }

        if ($nameIdx === -1) {
            fclose($handle);
            $this->flash('error', 'Could not find a column named "Name" or "Store Name" in the CSV.');
            $this->redirect('admin/retailers/import');
            return;
        }

        $inserted = 0;
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO retailers (name, phone, lat, lng, address) 
                VALUES (?, ?, ?, ?, ?)
            ");

            while (($row = fgetcsv($handle)) !== false) {
                if (empty($row) || (count($row) === 1 && $row[0] === null)) {
                    continue;
                }

                $name = isset($row[$nameIdx]) ? trim($row[$nameIdx]) : '';
                if ($name === '') {
                    continue; // Skip if name is empty
                }

                $phone = ($phoneIdx !== -1 && isset($row[$phoneIdx])) ? trim($row[$phoneIdx]) : null;
                $lat = ($latIdx !== -1 && isset($row[$latIdx]) && $row[$latIdx] !== '') ? floatval(trim($row[$latIdx])) : null;
                $lng = ($lngIdx !== -1 && isset($row[$lngIdx]) && $row[$lngIdx] !== '') ? floatval(trim($row[$lngIdx])) : null;
                $address = null;

                $stmt->execute([
                    $name,
                    $phone,
                    $lat,
                    $lng,
                    $address
                ]);
                $inserted++;
            }

            $this->db->commit();
            fclose($handle);

            $this->flash('success', "Successfully imported {$inserted} retailers.");
            $this->redirect('admin/retailers/import');

        } catch (Exception $e) {
            $this->db->rollBack();
            fclose($handle);
            $this->flash('error', 'Error importing data: ' . $e->getMessage());
            $this->redirect('admin/retailers/import');
        }
    }

    // ══════════════════════════════════════════════════════════
    //  Orders & Retailer Map View
    // ══════════════════════════════════════════════════════════
    public function orders(): void
    {
        $srs = $this->db->query("
            SELECT u.id, u.name 
            FROM users u 
            JOIN roles r ON r.id = u.role_id 
            WHERE r.slug='sr' AND u.status=1 
            ORDER BY u.name
        ")->fetchAll();

        $dsrs = $this->db->query("
            SELECT u.id, u.name 
            FROM users u 
            JOIN roles r ON r.id = u.role_id 
            WHERE r.slug='dsr' AND u.status=1 
            ORDER BY u.name
        ")->fetchAll();

        $warehouses = $this->db->query("
            SELECT id, name 
            FROM warehouses 
            WHERE status=1 
            ORDER BY name
        ")->fetchAll();

        $selectedDate = $_GET['date'] ?? date('Y-m-d');
        $pageTitle = 'Orders & Retailer Map';

        $this->render('orders', compact('srs', 'dsrs', 'warehouses', 'selectedDate', 'pageTitle'));
    }

    public function apiOrders(): void
    {
        $date        = trim($_GET['date'] ?? date('Y-m-d'));
        $srId        = !empty($_GET['sr_id']) ? (int)$_GET['sr_id'] : null;
        $dsrId       = !empty($_GET['dsr_id']) ? (int)$_GET['dsr_id'] : null;
        $warehouseId = !empty($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : null;
        $status      = !empty($_GET['status']) ? trim($_GET['status']) : null;
        $ocStatus    = !empty($_GET['oc_status']) ? trim($_GET['oc_status']) : null;
        $search      = !empty($_GET['search']) ? trim($_GET['search']) : null;

        $where = ["DATE(o.created_at) = ?"];
        $params = [$date];

        if ($srId) {
            $where[] = "o.sr_id = ?";
            $params[] = $srId;
        }
        if ($dsrId) {
            $where[] = "disp.dsr_id = ?";
            $params[] = $dsrId;
        }
        if ($warehouseId) {
            $where[] = "o.warehouse_id = ?";
            $params[] = $warehouseId;
        }
        if ($status) {
            $where[] = "o.status = ?";
            $params[] = $status;
        }
        if ($search) {
            $where[] = "(r.name LIKE ? OR r.phone LIKE ? OR d.name LIKE ? OR d.phone LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereSql = implode(" AND ", $where);

        $sql = "
            SELECT 
                o.id AS order_id,
                o.sr_id,
                o.warehouse_id,
                o.status AS order_status,
                o.total_amount,
                o.notes,
                o.created_at AS order_date,
                sr.name AS sr_name,
                w.name AS warehouse_name,
                disp.dsr_id,
                dsr.name AS dsr_name,
                disp.status AS dispatch_status,
                COALESCE(r.id, d.id) AS retailer_id,
                COALESCE(r.name, d.name, 'Unknown Retailer') AS retailer_name,
                COALESCE(r.phone, d.phone, 'N/A') AS phone,
                COALESCE(r.address, d.address, 'N/A') AS address,
                COALESCE(r.lat, d.lat) AS lat,
                COALESCE(r.lng, d.lng) AS lng,
                CASE WHEN r.id IS NOT NULL THEN 'retailer' ELSE 'dealer' END AS entity_type
            FROM orders o
            LEFT JOIN retailers r ON r.id = o.retailer_id
            LEFT JOIN dealers d ON d.id = o.dealer_id
            LEFT JOIN users sr ON sr.id = o.sr_id
            LEFT JOIN warehouses w ON w.id = o.warehouse_id
            LEFT JOIN dispatches disp ON disp.order_id = o.id
            LEFT JOIN users dsr ON dsr.id = disp.dsr_id
            WHERE {$whereSql}
            ORDER BY o.created_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = [];
        $summary = [
            'total_orders'    => count($orders),
            'total_amount'    => 0,
            'total_retailers' => 0,
            'checked_out_cnt' => 0,
            'ordered_cnt'     => 0
        ];

        $retailerIdsSeen = [];

        foreach ($orders as $ord) {
            $ordId = (int)$ord['order_id'];
            
            $isDelivered = ($ord['order_status'] === 'delivered' || $ord['dispatch_status'] === 'delivered');
            $calculatedOc = $isDelivered ? 'checked_out' : 'ordered';

            if ($ocStatus && $ocStatus !== $calculatedOc) {
                continue;
            }

            if ($isDelivered) {
                $summary['checked_out_cnt']++;
            } else {
                $summary['ordered_cnt']++;
            }

            $summary['total_amount'] += (float)$ord['total_amount'];

            $retKey = $ord['entity_type'] . '_' . $ord['retailer_id'];
            if (!isset($retailerIdsSeen[$retKey])) {
                $retailerIdsSeen[$retKey] = true;
                $summary['total_retailers']++;
            }

            $itemStmt = $this->db->prepare("
                SELECT oi.id, oi.product_id, oi.quantity, oi.unit_price, oi.total_price,
                       p.name AS product_name, p.sku, p.pieces_per_box
                FROM order_items oi
                JOIN products p ON p.id = oi.product_id
                WHERE oi.order_id = ?
            ");
            $itemStmt->execute([$ordId]);
            $rawItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

            $items = [];
            $totalBoxes = 0;
            $totalPieces = 0;

            foreach ($rawItems as $ri) {
                $ppb = (int)($ri['pieces_per_box'] ?: 1);
                $qty = (int)$ri['quantity'];
                $b = (int)floor($qty / $ppb);
                $p = $qty % $ppb;
                $totalBoxes += $b;
                $totalPieces += $p;

                $items[] = [
                    'id'             => (int)$ri['id'],
                    'product_id'     => (int)$ri['product_id'],
                    'product_name'   => $ri['product_name'],
                    'sku'            => $ri['sku'],
                    'pieces_per_box' => $ppb,
                    'quantity'       => $qty,
                    'boxes'          => $b,
                    'pieces'         => $p,
                    'unit_price'     => (float)$ri['unit_price'],
                    'total_price'    => (float)$ri['total_price']
                ];
            }

            $results[] = [
                'order_id'       => $ordId,
                'order_no'       => 'ORD-' . str_pad((string)$ordId, 5, '0', STR_PAD_LEFT),
                'retailer_id'    => (int)$ord['retailer_id'],
                'retailer_name'  => $ord['retailer_name'],
                'phone'          => $ord['phone'],
                'address'        => $ord['address'] ?: 'N/A',
                'lat'            => $ord['lat'] !== null ? (float)$ord['lat'] : null,
                'lng'            => $ord['lng'] !== null ? (float)$ord['lng'] : null,
                'sr_name'        => $ord['sr_name'] ?: 'N/A',
                'dsr_name'       => $ord['dsr_name'] ?: 'Not Assigned',
                'warehouse_name' => $ord['warehouse_name'] ?: 'N/A',
                'order_status'   => $ord['order_status'],
                'oc_status'      => $calculatedOc,
                'total_amount'   => (float)$ord['total_amount'],
                'notes'          => $ord['notes'],
                'order_date'     => date('h:i A, d M Y', strtotime($ord['order_date'])),
                'items_count'    => count($items),
                'total_boxes'    => $totalBoxes,
                'total_pieces'   => $totalPieces,
                'items'          => $items
            ];
        }

        $this->json([
            'success' => true,
            'summary' => $summary,
            'orders'  => $results
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  SR Tracking
    // ══════════════════════════════════════════════════════════
    public function srTracking(): void
    {
        $srList = $this->db->query("
            SELECT u.id, u.name
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE r.slug = 'sr' AND u.status = 1
            ORDER BY u.name ASC
        ")->fetchAll();

        $pageTitle = 'SR Tracking';
        $this->render('sr_tracking', compact('srList', 'pageTitle'), 'main');
    }

    /** GET /admin/api/sr-tracking/live
     *  Returns the latest location for every active SR.
     *  is_online = true if last ping is within 5 minutes.
     */
    public function apiSrTrackingLive(): void
    {
        $srs = $this->db->query("
            SELECT u.id, u.name,
                   sl.lat, sl.lng, sl.address, sl.recorded_at,
                   TIMESTAMPDIFF(SECOND, sl.recorded_at, NOW()) AS seconds_ago
            FROM users u
            JOIN roles r ON r.id = u.role_id AND r.slug = 'sr'
            LEFT JOIN sr_locations sl ON sl.id = (
                SELECT id FROM sr_locations
                WHERE sr_id = u.id
                ORDER BY recorded_at DESC LIMIT 1
            )
            WHERE u.status = 1
            ORDER BY u.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($srs as $sr) {
            $result[] = [
                'id'          => (int)$sr['id'],
                'name'        => $sr['name'],
                'lat'         => $sr['lat'] !== null ? (float)$sr['lat'] : null,
                'lng'         => $sr['lng'] !== null ? (float)$sr['lng'] : null,
                'address'     => $sr['address'],
                'recorded_at' => $sr['recorded_at'],
                'is_online'   => ($sr['seconds_ago'] !== null && (int)$sr['seconds_ago'] <= 300),
            ];
        }

        $this->json(['success' => true, 'srs' => $result]);
    }

    /** GET /admin/api/sr-tracking/history?sr_id=&date=&time_from=&time_to=
     *  Returns all location points for a specific SR on a given day.
     */
    public function apiSrTrackingHistory(): void
    {
        $srId     = (int)$this->get('sr_id', 0);
        $date     = $this->get('date', date('Y-m-d'));
        $timeFrom = $this->get('time_from', '00:00');
        $timeTo   = $this->get('time_to', '23:59');

        if (!$srId) {
            $this->json(['success' => false, 'message' => 'sr_id required']);
            return;
        }

        $from = $date . ' ' . $timeFrom . ':00';
        $to   = $date . ' ' . $timeTo   . ':59';

        $stmt = $this->db->prepare("
            SELECT id, lat, lng, address, accuracy, recorded_at
            FROM sr_locations
            WHERE sr_id = ?
              AND recorded_at BETWEEN ? AND ?
            ORDER BY recorded_at ASC
        ");
        $stmt->execute([$srId, $from, $to]);
        $points = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->json(['success' => true, 'points' => $points]);
    }

    // ══════════════════════════════════════════════════════════
    //  DSR Tracking
    // ══════════════════════════════════════════════════════════
    public function dsrTracking(): void
    {
        $dsrList = $this->db->query("
            SELECT u.id, u.name
            FROM users u
            JOIN roles r ON r.id = u.role_id
            WHERE r.slug = 'dsr' AND u.status = 1
            ORDER BY u.name ASC
        ")->fetchAll();

        $pageTitle = 'DSR Tracking';
        $this->render('dsr_tracking', compact('dsrList', 'pageTitle'), 'main');
    }

    /** GET /admin/api/dsr-tracking/live
     *  Returns the latest location for every active DSR.
     *  is_online = true if last ping is within 5 minutes.
     */
    public function apiDsrTrackingLive(): void
    {
        $dsrs = $this->db->query("
            SELECT u.id, u.name,
                   dl.lat, dl.lng, dl.address, dl.recorded_at,
                   TIMESTAMPDIFF(SECOND, dl.recorded_at, NOW()) AS seconds_ago
            FROM users u
            JOIN roles r ON r.id = u.role_id AND r.slug = 'dsr'
            LEFT JOIN dsr_locations dl ON dl.id = (
                SELECT id FROM dsr_locations
                WHERE dsr_id = u.id
                ORDER BY recorded_at DESC LIMIT 1
            )
            WHERE u.status = 1
            ORDER BY u.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($dsrs as $dsr) {
            $result[] = [
                'id'          => (int)$dsr['id'],
                'name'        => $dsr['name'],
                'lat'         => $dsr['lat'] !== null ? (float)$dsr['lat'] : null,
                'lng'         => $dsr['lng'] !== null ? (float)$dsr['lng'] : null,
                'address'     => $dsr['address'],
                'recorded_at' => $dsr['recorded_at'],
                'is_online'   => ($dsr['seconds_ago'] !== null && (int)$dsr['seconds_ago'] <= 300),
            ];
        }

        $this->json(['success' => true, 'dsrs' => $result]);
    }

    /** GET /admin/api/dsr-tracking/history?dsr_id=&date=&time_from=&time_to=
     *  Returns all location points for a specific DSR on a given day.
     */
    public function apiDsrTrackingHistory(): void
    {
        $dsrId    = (int)$this->get('dsr_id', 0);
        $date     = $this->get('date', date('Y-m-d'));
        $timeFrom = $this->get('time_from', '00:00');
        $timeTo   = $this->get('time_to', '23:59');

        if (!$dsrId) {
            $this->json(['success' => false, 'message' => 'dsr_id required']);
            return;
        }

        $from = $date . ' ' . $timeFrom . ':00';
        $to   = $date . ' ' . $timeTo   . ':59';

        $stmt = $this->db->prepare("
            SELECT id, lat, lng, address, accuracy, recorded_at
            FROM dsr_locations
            WHERE dsr_id = ?
              AND recorded_at BETWEEN ? AND ?
            ORDER BY recorded_at ASC
        ");
        $stmt->execute([$dsrId, $from, $to]);
        $points = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->json(['success' => true, 'points' => $points]);
    }

    // ══════════════════════════════════════════════════════════
    //  Session Management
    // ══════════════════════════════════════════════════════════
    public function sessions(): void
    {
        $filterRole = $this->get('role', '');
        $sessions = Auth::getActiveSessions($filterRole ?: null);
        $counts   = Auth::getSessionCounts();
        $currentToken = Auth::sessionToken();

        $pageTitle = 'Active Sessions';
        $this->render('sessions', compact('sessions', 'counts', 'filterRole', 'currentToken', 'pageTitle'));
    }

    public function sessionForceLogout(string $id): void
    {
        $this->verifyCsrf();

        $sessionId = (int) $id;
        if ($sessionId <= 0) {
            $this->flash('error', 'Invalid session ID.');
            $this->redirect('admin/sessions');
            return;
        }

        // Prevent admin from force-logging out their own current session
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT token, user_id FROM user_sessions WHERE id = ? LIMIT 1");
        $stmt->execute([$sessionId]);
        $session = $stmt->fetch();

        if ($session && $session['token'] === Auth::sessionToken()) {
            $this->flash('error', 'You cannot force-logout your own current session. Use the regular logout instead.');
            $this->redirect('admin/sessions');
            return;
        }

        if (Auth::forceLogout($sessionId)) {
            // Log the action
            $targetUserId = $session['user_id'] ?? 0;
            $db->prepare("INSERT INTO activity_logs (user_id, action, module, record_id, description, ip_address) VALUES (?, 'force_logout', 'sessions', ?, 'Admin force-logged out a session', ?)")
               ->execute([Auth::id(), $targetUserId, $_SERVER['REMOTE_ADDR'] ?? '']);

            $this->flash('success', 'Session terminated successfully.');
        } else {
            $this->flash('error', 'Session not found or already inactive.');
        }

        $this->redirect('admin/sessions');
    }

    // ══════════════════════════════════════════════════════════
    //  Custom Areas Map Management
    // ══════════════════════════════════════════════════════════
    public function customAreas(): void
    {
        $srs = $this->db->query("SELECT u.id, u.name FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='sr' AND u.status=1 ORDER BY u.name ASC")->fetchAll();
        $dsrs = $this->db->query("SELECT u.id, u.name FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='dsr' AND u.status=1 ORDER BY u.name ASC")->fetchAll();
        $warehouses = $this->db->query("SELECT id, name FROM warehouses WHERE status=1 ORDER BY name ASC")->fetchAll();

        $pageTitle = 'Custom Area Map Management';
        $this->render('custom_areas', compact('srs', 'dsrs', 'warehouses', 'pageTitle'));
    }

    public function apiCustomAreas(): void
    {
        header('Content-Type: application/json');
        $areas = $this->db->query("SELECT * FROM custom_areas WHERE status=1 ORDER BY created_at DESC")->fetchAll();
        foreach ($areas as &$area) {
            $area['coordinates'] = json_decode($area['coordinates']);
        }
        echo json_encode(['success' => true, 'data' => $areas]);
        exit;
    }

    public function apiCustomAreaStore(): void
    {
        header('Content-Type: application/json');
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        if (!$input) { $input = $_POST; }

        $name          = trim($input['name'] ?? '');
        $description   = trim($input['description'] ?? '');
        $type          = trim($input['type'] ?? 'polygon');
        $coordinates   = is_array($input['coordinates'] ?? null) ? json_encode($input['coordinates']) : ($input['coordinates'] ?? '');
        $strokeColor   = trim($input['stroke_color'] ?? '#3b82f6');
        $fillColor     = trim($input['fill_color'] ?? '#93c5fd');
        $fillOpacity   = floatval($input['fill_opacity'] ?? 0.35);
        $assignedType  = !empty($input['assigned_type']) ? trim($input['assigned_type']) : null;
        $assignedId    = !empty($input['assigned_id']) ? (int)$input['assigned_id'] : null;

        if (!$name || !$coordinates) {
            echo json_encode(['success' => false, 'message' => 'Area name and valid geometry coordinates are required.']);
            exit;
        }

        $stmt = $this->db->prepare("INSERT INTO custom_areas (name, description, type, coordinates, stroke_color, fill_color, fill_opacity, assigned_type, assigned_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $type, $coordinates, $strokeColor, $fillColor, $fillOpacity, $assignedType, $assignedId]);
        $id = $this->db->lastInsertId();

        echo json_encode(['success' => true, 'id' => $id, 'message' => 'Custom area saved successfully.']);
        exit;
    }

    public function apiCustomAreaUpdate(string $id): void
    {
        header('Content-Type: application/json');
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        if (!$input) { $input = $_POST; }

        $areaId = (int)$id;
        $name          = trim($input['name'] ?? '');
        $description   = trim($input['description'] ?? '');
        $type          = trim($input['type'] ?? 'polygon');
        $coordinates   = is_array($input['coordinates'] ?? null) ? json_encode($input['coordinates']) : ($input['coordinates'] ?? '');
        $strokeColor   = trim($input['stroke_color'] ?? '#3b82f6');
        $fillColor     = trim($input['fill_color'] ?? '#93c5fd');
        $fillOpacity   = floatval($input['fill_opacity'] ?? 0.35);
        $assignedType  = !empty($input['assigned_type']) ? trim($input['assigned_type']) : null;
        $assignedId    = !empty($input['assigned_id']) ? (int)$input['assigned_id'] : null;

        if (!$name || !$coordinates) {
            echo json_encode(['success' => false, 'message' => 'Area name and valid geometry coordinates are required.']);
            exit;
        }

        $stmt = $this->db->prepare("UPDATE custom_areas SET name=?, description=?, type=?, coordinates=?, stroke_color=?, fill_color=?, fill_opacity=?, assigned_type=?, assigned_id=? WHERE id=?");
        $stmt->execute([$name, $description, $type, $coordinates, $strokeColor, $fillColor, $fillOpacity, $assignedType, $assignedId, $areaId]);

        echo json_encode(['success' => true, 'message' => 'Custom area updated successfully.']);
        exit;
    }

    public function apiCustomAreaDelete(string $id): void
    {
        header('Content-Type: application/json');
        $areaId = (int)$id;
        $stmt = $this->db->prepare("DELETE FROM custom_areas WHERE id=?");
        $stmt->execute([$areaId]);

        echo json_encode(['success' => true, 'message' => 'Custom area deleted successfully.']);
        exit;
    }

    // ══════════════════════════════════════════════════════════
    //  Retailers Page
    // ══════════════════════════════════════════════════════════
    public function retailers(): void
    {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $search = trim($_GET['search'] ?? '');
        
        $where = " WHERE 1=1 ";
        $params = [];
        
        if ($search !== '') {
            $where .= " AND (name LIKE ? OR phone LIKE ? OR address LIKE ?) ";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $qCount = $this->db->prepare("SELECT COUNT(*) FROM retailers $where");
        $qCount->execute($params);
        $totalRows = (int)$qCount->fetchColumn();
        $totalPages = max(1, ceil($totalRows / $limit));

        $q = $this->db->prepare("
            SELECT * FROM retailers
            $where
            ORDER BY created_at DESC
            LIMIT $limit OFFSET $offset
        ");
        $q->execute($params);
        $items = $q->fetchAll(PDO::FETCH_ASSOC);

        // Map data: fetch all retailers with valid coordinates
        $qMap = $this->db->query("SELECT name, phone, address, lat, lng FROM retailers WHERE lat IS NOT NULL AND lng IS NOT NULL AND lat != 0 AND lng != 0");
        $mapData = $qMap->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = 'Retailers';
        $this->render('retailers/index', compact('items', 'page', 'totalPages', 'search', 'totalRows', 'mapData', 'pageTitle', 'offset'), 'main');
    }

    // ══════════════════════════════════════════════════════════
    //  Manager Logs
    // ══════════════════════════════════════════════════════════
    public function managerLogs(): void
    {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo = $_GET['date_to'] ?? '';
        $managerId = $_GET['manager_id'] ?? '';

        $where = " WHERE 1=1 ";
        $params = [];
        if (!empty($dateFrom)) {
            $where .= " AND DATE(ma.created_at) >= ? ";
            $params[] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where .= " AND DATE(ma.created_at) <= ? ";
            $params[] = $dateTo;
        }
        if (!empty($managerId)) {
            $where .= " AND ma.manager_id = ? ";
            $params[] = $managerId;
        }

        $qCount = $this->db->prepare("SELECT COUNT(*) FROM manager_activities ma $where");
        $qCount->execute($params);
        $totalLogs = $qCount->fetchColumn();
        $totalPages = ceil($totalLogs / $limit);

        $q = $this->db->prepare("
            SELECT ma.*, u.name AS manager_name
            FROM manager_activities ma
            LEFT JOIN users u ON u.id = ma.manager_id
            $where
            ORDER BY ma.created_at DESC
            LIMIT $limit OFFSET $offset
        ");
        $q->execute($params);
        $logs = $q->fetchAll();

        $managers = $this->db->query("SELECT u.id, u.name FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug='manager'")->fetchAll();

        $pageTitle = 'Manager Activities';
        $this->render('manager_logs', compact('logs', 'page', 'totalPages', 'managers', 'managerId', 'dateFrom', 'dateTo', 'pageTitle'), 'main');
    }
}


