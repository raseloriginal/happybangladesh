<?php
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = Database::getInstance();

    // Check if table is empty
    $count = $db->query("SELECT COUNT(*) FROM custom_areas")->fetchColumn();
    if ($count == 0) {
        $samplePolygon1 = [
            "type" => "Polygon",
            "coordinates" => [
                [
                    [88.7300, 24.3050],
                    [88.7550, 24.3120],
                    [88.7750, 24.2880],
                    [88.7500, 24.2750],
                    [88.7300, 24.3050]
                ]
            ]
        ];

        $samplePolygon2 = [
            "type" => "Polygon",
            "coordinates" => [
                [
                    [88.7450, 24.2750],
                    [88.7750, 24.2880],
                    [88.7880, 24.2620],
                    [88.7700, 24.2250],
                    [88.7400, 24.2400],
                    [88.7450, 24.2750]
                ]
            ]
        ];

        $stmt = $db->prepare("INSERT INTO custom_areas (name, description, type, coordinates, stroke_color, fill_color, fill_opacity) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            'Sardah Govt. College Zone',
            'Northern coverage area covering Sardah Police Academy & College area',
            'polygon',
            json_encode($samplePolygon1),
            '#22c55e',
            '#86efac',
            0.45
        ]);

        $stmt->execute([
            'Charghat Central & Sluice Gate Zone',
            'Central distribution area around Charghat market & river junction',
            'polygon',
            json_encode($samplePolygon2),
            '#3b82f6',
            '#93c5fd',
            0.40
        ]);

        echo "SAMPLES_SEEDED_SUCCESSFULLY\n";
    } else {
        echo "TABLE_ALREADY_HAS_DATA\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
