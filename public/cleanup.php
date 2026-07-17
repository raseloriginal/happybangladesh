<?php
/**
 * ============================================================
 *  HappyBangladesh DMS — Database Cleanup Utility
 * ============================================================
 */
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/Config/config.php';

$action = $_POST['action'] ?? '';

if ($action === 'cleanup') {
    header('Content-Type: application/json');
    try {
        // Connect to the configured database
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true, // Emulate prepares to allow multi-queries
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        // 1. Disable Foreign Key Checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        
        // 2. Drop all existing tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            $pdo->exec("DROP TABLE IF EXISTS `$table`;");
        }
        
        // 3. Load and prepare schema SQL (strip CREATE DATABASE and USE statements to support custom database names)
        $schemaFile = ROOT_PATH . '/database/migrations/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found at: " . $schemaFile);
        }
        $schemaSql = file_get_contents($schemaFile);
        $schemaSql = preg_replace('/CREATE DATABASE IF NOT EXISTS.*?;/is', '', $schemaSql);
        $schemaSql = preg_replace('/USE `.*?`;/is', '', $schemaSql);
        
        if (trim($schemaSql) !== '') {
            $pdo->exec($schemaSql);
        }
        
        // 4. Load and prepare seed SQL (strip USE statements)
        $seedFile = ROOT_PATH . '/database/seeders/seed.sql';
        if (!file_exists($seedFile)) {
            throw new Exception("Seed file not found at: " . $seedFile);
        }
        $seedSql = file_get_contents($seedFile);
        $seedSql = preg_replace('/USE `.*?`;/is', '', $seedSql);
        
        if (trim($seedSql) !== '') {
            $pdo->exec($seedSql);
        }
        
        // 5. Re-enable Foreign Key Checks
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        
        echo json_encode([
            'success' => true,
            'message' => 'Database successfully cleaned up and re-seeded!',
            'details' => [
                'tables_dropped' => count($tables),
                'database' => DB_NAME
            ]
        ]);
        exit;
    } catch (Throwable $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Cleanup — HappyBangladesh DMS</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311042 100%);
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-gradient);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 600px;
            perspective: 1000px;
        }

        .card {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.15) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        .content-wrapper {
            position: relative;
            z-index: 1;
        }

        .icon-container {
            width: 90px;
            height: 90px;
            background: rgba(99, 102, 241, 0.1);
            border: 2px solid rgba(99, 102, 241, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 2.5rem;
            color: var(--primary);
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.2);
            transition: all 0.5s ease;
        }

        .icon-container.success {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.2);
            color: var(--success);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
            transform: scale(1.05);
        }

        .icon-container.error {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.2);
            color: var(--danger);
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            letter-spacing: -0.025em;
            background: linear-gradient(to right, #ffffff, #c7d2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p.subtitle {
            color: var(--text-muted);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            width: 100%;
            padding: 1rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            background: var(--primary);
            border: none;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.25);
        }

        .btn:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(99, 102, 241, 0.35);
        }

        .btn:active {
            transform: translateY(0);
        }

        .btn:disabled {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-muted);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
            border: 1px solid var(--glass-border);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            box-shadow: none;
            margin-top: 1rem;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            box-shadow: none;
        }

        /* Status log box */
        .log-container {
            display: none;
            margin-top: 2rem;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 1.25rem;
            text-align: left;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.875rem;
            max-height: 200px;
            overflow-y: auto;
            color: #38bdf8;
        }

        .log-item {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .log-item:last-child {
            margin-bottom: 0;
        }

        .log-item.success {
            color: var(--success);
        }

        .log-item.error {
            color: var(--danger);
        }

        /* Results table */
        .result-table-container {
            display: none;
            margin-top: 2rem;
            text-align: left;
            animation: fadeIn 0.5s ease forwards;
        }

        .credentials-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table-wrapper {
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
            background: rgba(15, 23, 42, 0.4);
        }

        th, td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--glass-border);
        }

        th {
            background: rgba(255, 255, 255, 0.03);
            font-weight: 600;
            color: var(--text-main);
        }

        tr:last-child td {
            border-bottom: none;
        }

        td {
            color: var(--text-muted);
        }

        td.highlight {
            color: var(--text-main);
            font-family: monospace;
        }

        /* Animations */
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .spin {
            animation: rotate 1.5s linear infinite;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Custom scrollbar */
        .log-container::-webkit-scrollbar {
            width: 6px;
        }
        .log-container::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }
        .log-container::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card" id="cleanupCard">
        <div class="content-wrapper">
            <div class="icon-container" id="statusIcon">
                <i class="fa-solid fa-database"></i>
            </div>
            
            <h1 id="titleText">Database Reset & Cleanup</h1>
            <p class="subtitle" id="subtitleText">
                This utility will truncate all tables, reset database schema, and seed default demonstration data for HappyBangladesh DMS.
            </p>

            <button class="btn" id="cleanupBtn">
                <i class="fa-solid fa-triangle-exclamation"></i> Run Database Cleanup
            </button>
            
            <div class="log-container" id="logBox"></div>

            <div class="result-table-container" id="credentialsBox">
                <div class="credentials-title">Default Credentials</div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Email</th>
                                <th>Password</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Admin</td>
                                <td class="highlight">admin@dms.com</td>
                                <td class="highlight">password123</td>
                            </tr>
                            <tr>
                                <td>Manager</td>
                                <td class="highlight">manager@dms.com</td>
                                <td class="highlight">password123</td>
                            </tr>
                            <tr>
                                <td>SR</td>
                                <td class="highlight">sr@dms.com</td>
                                <td class="highlight">password123</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <a href="<?php echo BASE_URL; ?>/login" class="btn btn-secondary" id="loginLink" style="display: none;">
                <i class="fa-solid fa-right-to-bracket"></i> Go to Portal Login
            </a>
        </div>
    </div>
</div>

<script>
    const cleanupBtn = document.getElementById('cleanupBtn');
    const loginLink = document.getElementById('loginLink');
    const logBox = document.getElementById('logBox');
    const statusIcon = document.getElementById('statusIcon');
    const titleText = document.getElementById('titleText');
    const subtitleText = document.getElementById('subtitleText');
    const credentialsBox = document.getElementById('credentialsBox');

    function addLog(message, type = 'info') {
        const item = document.createElement('div');
        item.className = `log-item ${type}`;
        
        let icon = '<i class="fa-solid fa-chevron-right"></i>';
        if (type === 'success') icon = '<i class="fa-solid fa-check"></i>';
        if (type === 'error') icon = '<i class="fa-solid fa-xmark"></i>';
        
        item.innerHTML = `${icon} <span>${message}</span>`;
        logBox.appendChild(item);
        logBox.scrollTop = logBox.scrollHeight;
    }

    cleanupBtn.addEventListener('click', async () => {
        if (!confirm('Are you sure you want to clean up the database? All transactional records and customizations will be permanently deleted.')) {
            return;
        }

        cleanupBtn.disabled = true;
        cleanupBtn.innerHTML = '<i class="fa-solid fa-spinner spin"></i> Processing Cleanup...';
        
        logBox.style.display = 'block';
        logBox.innerHTML = '';
        
        statusIcon.innerHTML = '<i class="fa-solid fa-circle-notch spin"></i>';
        statusIcon.className = 'icon-container';
        
        addLog('Connecting to database server...');
        
        try {
            const formData = new FormData();
            formData.append('action', 'cleanup');
            
            // Wait 500ms for realistic smooth animation feel
            await new Promise(resolve => setTimeout(resolve, 500));
            addLog('Disabling foreign key constraints...');
            
            await new Promise(resolve => setTimeout(resolve, 300));
            addLog('Dropping all existing database tables...');
            
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                addLog(`Successfully dropped existing tables.`);
                addLog('Reading migration schema & creating new tables...');
                await new Promise(resolve => setTimeout(resolve, 400));
                addLog('Successfully imported database schema.');
                addLog('Reading seed script & populating default data...');
                await new Promise(resolve => setTimeout(resolve, 400));
                addLog('Enabling foreign key constraints...');
                
                statusIcon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
                statusIcon.classList.add('success');
                
                titleText.textContent = 'Cleanup Completed!';
                subtitleText.textContent = 'The database was reset to its original state. You can now log into the portal using the default credentials below.';
                
                cleanupBtn.style.display = 'none';
                loginLink.style.display = 'inline-flex';
                credentialsBox.style.display = 'block';
                
                addLog('Database successfully re-seeded!', 'success');
            } else {
                throw new Error(result.message);
            }
            
        } catch (error) {
            statusIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
            statusIcon.classList.add('error');
            addLog(`Error: ${error.message}`, 'error');
            cleanupBtn.disabled = false;
            cleanupBtn.innerHTML = '<i class="fa-solid fa-rotate-right"></i> Retry Database Cleanup';
        }
    });
</script>
</body>
</html>
