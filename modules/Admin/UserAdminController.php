<?php

class UserAdminController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check(ROLE_ADMIN);
        $this->viewPath = MOD_PATH . '/Admin/views';
        $this->db = Database::getInstance();
    }

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


}
