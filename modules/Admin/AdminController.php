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
        $items = $this->db->query("SELECT * FROM warehouses ORDER BY created_at DESC")->fetchAll();
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
        $this->db->prepare("DELETE FROM warehouses WHERE id=?")->execute([$id]);
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
            WHERE r.slug = ?
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

        $this->db->prepare("INSERT INTO users (role_id, warehouse_id, company_id, name, email, phone, password) VALUES (?,?,?,?,?,?,?)")
                 ->execute([$roleId, $whId, $companyId, $name, $email, $phone, password_hash($password, PASSWORD_BCRYPT)]);

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
        $this->db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
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
        $this->db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        $this->flash('success', 'SR deleted.'); $this->redirect('admin/srs');
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
        $this->db->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        $this->flash('success', 'DSR deleted.'); $this->redirect('admin/dsrs');
    }

    // ══════════════════════════════════════════════════════════
    //  Companies CRUD
    // ══════════════════════════════════════════════════════════
    public function companies(): void
    {
        $items = $this->db->query("SELECT * FROM companies ORDER BY created_at DESC")->fetchAll();
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
        $this->db->prepare("DELETE FROM companies WHERE id=?")->execute([$id]);
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
        $this->db->beginTransaction();
        
        try {
            $this->db->prepare("INSERT INTO dealers (warehouse_id, name, phone, address, trade_license, business_name, happy_commission) VALUES (?,?,?,?,?,?,?)")
                     ->execute([
                         $this->post('warehouse_id') ?: null, 
                         trim($this->post('name')), 
                         trim($this->post('phone')), 
                         trim($this->post('address')), 
                         trim($this->post('trade_license')), 
                         trim($this->post('business_name')), 
                         $this->post('happy_commission', 0.00)
                     ]);
            
            $dealerId = $this->db->lastInsertId();
            
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
        $this->db->beginTransaction();
        
        try {
            $this->db->prepare("UPDATE dealers SET warehouse_id=?,name=?,phone=?,address=?,trade_license=?,business_name=?,happy_commission=?,status=? WHERE id=?")
                     ->execute([
                         $this->post('warehouse_id') ?: null, 
                         trim($this->post('name')), 
                         trim($this->post('phone')), 
                         trim($this->post('address')), 
                         trim($this->post('trade_license')), 
                         trim($this->post('business_name')), 
                         $this->post('happy_commission',0.00), 
                         $this->post('status',1), 
                         $id
                     ]);
            
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
        $this->db->prepare("DELETE FROM dealers WHERE id=?")->execute([$id]);
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
        $this->db->prepare("UPDATE approvals SET status='approved', approved_by=?, updated_at=NOW() WHERE id=?")
                 ->execute([Auth::id(), $id]);
        $this->flash('success', 'Request approved.');
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
        $schemaPath = ROOT_PATH . '/database/migrations/schema.sql';
        $schemaContent = file_exists($schemaPath) ? file_get_contents($schemaPath) : '';

        $parsedTables = $this->parseSchemaSql();
        
        $dbTables = $this->db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        
        $missingTables = [];
        $missingColumns = [];
        $proposedSqls = [];

        foreach ($parsedTables as $tableName => $tableData) {
            if (!in_array($tableName, $dbTables)) {
                $missingTables[] = $tableName;
                $proposedSqls[] = $tableData['full_sql'];
            } else {
                try {
                    $dbCols = $this->db->query("SHOW COLUMNS FROM `{$tableName}`")->fetchAll(PDO::FETCH_ASSOC);
                    $dbColNames = array_column($dbCols, 'Field');
                    
                    foreach ($tableData['columns'] as $colName => $colDefLine) {
                        if (!in_array($colName, $dbColNames)) {
                            $missingColumns[$tableName][] = $colName;
                            // Clean definition line (remove trailing commas if any)
                            $cleanDef = rtrim(trim($colDefLine), ',');
                            $proposedSqls[] = "ALTER TABLE `{$tableName}` ADD COLUMN {$cleanDef};";
                        }
                    }
                } catch (PDOException $e) {
                    // Ignore table errors if table not queryable
                }
            }
        }

        $proposedSql = implode("\n\n", $proposedSqls);

        $pageTitle = 'Database Sync';
        $this->render('database_sync', compact('schemaContent', 'missingTables', 'missingColumns', 'proposedSql', 'pageTitle'));
    }

    public function databaseSyncRun(): void
    {
        $this->verifyCsrf();
        $syncType = $this->post('sync_type');

        try {
            if ($syncType === 'schema') {
                $sql = $this->post('proposed_sql', '');
                if (empty(trim($sql))) {
                    $this->flash('warning', 'No schema updates were needed.');
                    $this->redirect('admin/database-sync');
                    return;
                }
                
                // PDO exec doesn't always support multi-queries in a single call depending on driver settings.
                // We should split queries by semicolon and execute them one by one.
                $queries = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($queries as $query) {
                    if (!empty($query)) {
                        $this->db->exec($query);
                    }
                }
                
                $this->flash('success', 'Database schema synced successfully.');
            } else if ($syncType === 'custom') {
                $sql = $this->post('custom_sql', '');
                if (!empty(trim($sql))) {
                    $queries = array_filter(array_map('trim', explode(';', $sql)));
                    foreach ($queries as $query) {
                        if (!empty($query)) {
                            $this->db->exec($query);
                        }
                    }
                    $this->flash('success', 'Custom SQL executed successfully.');
                }
            }
        } catch (PDOException $e) {
            $this->flash('error', 'Execution failed: ' . $e->getMessage());
        }

        $this->redirect('admin/database-sync');
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
                $address = "Imported dummy retailer";

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
}

