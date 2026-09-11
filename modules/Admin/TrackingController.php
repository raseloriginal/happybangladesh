<?php

class TrackingController extends Controller
{
    protected string $viewPath;
    private PDO $db;

    public function __construct()
    {
        RoleMiddleware::check(ROLE_ADMIN);
        $this->viewPath = MOD_PATH . '/Admin/views';
        $this->db = Database::getInstance();
    }

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


}
