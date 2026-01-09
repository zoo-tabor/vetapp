<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Workplace.php';

class WarehouseController {

    public function index() {
        Auth::requireLogin();

        $workplaceModel = new Workplace();
        $userWorkplaces = $workplaceModel->getUserWorkplaces(Auth::userId());

        // Sort workplaces: ZOO Tábor, Babice, Lipence, Deponace
        usort($userWorkplaces, function($a, $b) {
            $order = ['ZOO Tábor' => 1, 'Babice' => 2, 'Lipence' => 3, 'Deponace' => 4];

            $aPos = 999;
            $bPos = 999;

            foreach ($order as $name => $position) {
                if (stripos($a['name'], $name) !== false) $aPos = $position;
                if (stripos($b['name'], $name) !== false) $bPos = $position;
            }

            return $aPos - $bPos;
        });

        View::render('warehouse/dashboard', [
            'layout' => 'main',
            'title' => 'Sklad - Správa zásob',
            'workplaces' => $userWorkplaces
        ]);
    }

    public function workplace($workplaceId) {
        Auth::requireLogin();

        $db = Database::getInstance()->getConnection();

        // Verify user has access to this workplace
        $workplaceModel = new Workplace();
        $workplace = $workplaceModel->getById($workplaceId);

        if (!$workplace || !$workplaceModel->hasAccess(Auth::userId(), $workplaceId)) {
            $_SESSION['error'] = 'Nemáte přístup k tomuto pracovišti';
            header('Location: /warehouse');
            exit;
        }

        // Get inventory items for this workplace
        $stmt = $db->prepare("
            SELECT
                wi.*,
                wc.weekly_consumption,
                wc.desired_weeks_stock,
                u.full_name as created_by_name
            FROM warehouse_items wi
            LEFT JOIN warehouse_consumption wc ON wi.id = wc.item_id
            LEFT JOIN users u ON wi.created_by = u.id
            WHERE wi.workplace_id = ?
            ORDER BY wi.category, wi.name
        ");
        $stmt->execute([$workplaceId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get expiring items (within 30 days)
        $stmt = $db->prepare("
            SELECT
                wi.name,
                wi.category,
                wb.expiration_date,
                wb.quantity,
                wb.batch_number
            FROM warehouse_batches wb
            JOIN warehouse_items wi ON wb.item_id = wi.id
            WHERE wi.workplace_id = ?
            AND wb.expiration_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            AND wb.quantity > 0
            ORDER BY wb.expiration_date ASC
        ");
        $stmt->execute([$workplaceId]);
        $expiringItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get low stock items
        $lowStockItems = array_filter($items, function($item) {
            return $item['min_stock_level'] !== null &&
                   $item['current_stock'] <= $item['min_stock_level'];
        });

        View::render('warehouse/workplace', [
            'layout' => 'main',
            'title' => 'Sklad - ' . $workplace['name'],
            'workplace' => $workplace,
            'items' => $items,
            'expiringItems' => $expiringItems,
            'lowStockItems' => $lowStockItems
        ]);
    }

    public function central() {
        Auth::requireLogin();

        $db = Database::getInstance()->getConnection();
        $workplaceModel = new Workplace();

        // Get user's accessible workplaces
        $userWorkplaces = $workplaceModel->getUserWorkplaces(Auth::userId());
        $workplaceIds = array_column($userWorkplaces, 'id');

        // Get ALL items from all accessible workplaces (consolidated view)
        $placeholders = str_repeat('?,', count($workplaceIds) - 1) . '?';
        $stmt = $db->prepare("
            SELECT
                wi.*,
                w.name as workplace_name,
                wc.weekly_consumption,
                wc.desired_weeks_stock,
                u.full_name as created_by_name
            FROM warehouse_items wi
            LEFT JOIN workplaces w ON wi.workplace_id = w.id
            LEFT JOIN warehouse_consumption wc ON wi.id = wc.item_id
            LEFT JOIN users u ON wi.created_by = u.id
            WHERE wi.workplace_id IN ($placeholders)
            ORDER BY wi.category, wi.name
        ");
        $stmt->execute($workplaceIds);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get expiring items from all workplaces
        $stmt = $db->prepare("
            SELECT
                wi.name,
                wi.category,
                w.name as workplace_name,
                wb.expiration_date,
                wb.quantity,
                wb.batch_number
            FROM warehouse_batches wb
            JOIN warehouse_items wi ON wb.item_id = wi.id
            LEFT JOIN workplaces w ON wi.workplace_id = w.id
            WHERE wi.workplace_id IN ($placeholders)
            AND wb.expiration_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            AND wb.quantity > 0
            ORDER BY wb.expiration_date ASC
        ");
        $stmt->execute($workplaceIds);
        $expiringItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get low stock items
        $lowStockItems = array_filter($items, function($item) {
            return $item['min_stock_level'] !== null &&
                   $item['current_stock'] <= $item['min_stock_level'];
        });

        View::render('warehouse/workplace', [
            'layout' => 'main',
            'title' => 'Centrální sklad',
            'workplace' => ['id' => 'central', 'name' => 'Centrální sklad'],
            'items' => $items,
            'expiringItems' => $expiringItems,
            'lowStockItems' => $lowStockItems,
            'isCentral' => true
        ]);
    }

    public function createItem() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /warehouse');
            exit;
        }

        $workplaceId = (int)$_POST['workplace_id'];
        $itemCode = trim($_POST['item_code']);
        $category = $_POST['category'];
        $name = trim($_POST['name']);
        $unit = trim($_POST['unit']);
        $currentStock = (float)$_POST['current_stock'];
        $minStockLevel = !empty($_POST['min_stock_level']) ? (float)$_POST['min_stock_level'] : null;
        $maxStockLevel = !empty($_POST['max_stock_level']) ? (float)$_POST['max_stock_level'] : null;
        $supplier = trim($_POST['supplier'] ?? '');
        $storageLocation = trim($_POST['storage_location'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("
                INSERT INTO warehouse_items
                (workplace_id, item_code, name, category, unit, current_stock, min_stock_level, max_stock_level, supplier, storage_location, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $workplaceId,
                $itemCode,
                $name,
                $category,
                $unit,
                $currentStock,
                $minStockLevel,
                $maxStockLevel,
                $supplier ?: null,
                $storageLocation ?: null,
                $notes ?: null,
                Auth::userId()
            ]);

            $_SESSION['success'] = 'Položka byla úspěšně přidána';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Chyba při přidávání položky: ' . $e->getMessage();
        }

        $redirectUrl = $workplaceId ? "/warehouse/workplace/$workplaceId" : "/warehouse/central";
        header("Location: $redirectUrl");
        exit;
    }

    public function updateItem() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /warehouse');
            exit;
        }

        $itemId = (int)$_POST['item_id'];
        $itemCode = trim($_POST['item_code']);
        $category = $_POST['category'];
        $name = trim($_POST['name']);
        $unit = trim($_POST['unit']);
        $currentStock = (float)$_POST['current_stock'];
        $minStockLevel = !empty($_POST['min_stock_level']) ? (float)$_POST['min_stock_level'] : null;
        $maxStockLevel = !empty($_POST['max_stock_level']) ? (float)$_POST['max_stock_level'] : null;
        $supplier = trim($_POST['supplier'] ?? '');
        $storageLocation = trim($_POST['storage_location'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("
                UPDATE warehouse_items
                SET item_code = ?,
                    name = ?,
                    category = ?,
                    unit = ?,
                    current_stock = ?,
                    min_stock_level = ?,
                    max_stock_level = ?,
                    supplier = ?,
                    storage_location = ?,
                    notes = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $itemCode,
                $name,
                $category,
                $unit,
                $currentStock,
                $minStockLevel,
                $maxStockLevel,
                $supplier ?: null,
                $storageLocation ?: null,
                $notes ?: null,
                $itemId
            ]);

            $_SESSION['success'] = 'Položka byla úspěšně aktualizována';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Chyba při aktualizaci položky: ' . $e->getMessage();
        }

        // Redirect back to item detail or specified redirect
        $redirect = $_POST['redirect'] ?? "/warehouse/item/$itemId";
        header("Location: $redirect");
        exit;
    }

    public function addMovement() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /warehouse');
            exit;
        }

        $itemId = (int)$_POST['item_id'];
        $movementType = $_POST['movement_type'];
        $quantity = (float)$_POST['quantity'];
        $movementDate = $_POST['movement_date'];
        $referenceDocument = trim($_POST['reference_document'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        $db = Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // Get current stock and workplace
            $stmt = $db->prepare("SELECT current_stock, workplace_id FROM warehouse_items WHERE id = ?");
            $stmt->execute([$itemId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                throw new Exception('Položka nenalezena');
            }

            // Calculate quantity change based on movement type
            $quantityChange = 0;
            if ($movementType === 'in') {
                $quantityChange = $quantity;
            } elseif ($movementType === 'out') {
                $quantityChange = -$quantity;
            } elseif ($movementType === 'adjustment') {
                // For adjustment, quantity is the new total, so calculate difference
                $quantityChange = $quantity - $item['current_stock'];
            }

            // Insert movement record
            $stmt = $db->prepare("
                INSERT INTO warehouse_movements
                (item_id, movement_type, quantity, movement_date, reference_document, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $itemId,
                $movementType,
                $quantityChange,
                $movementDate,
                $referenceDocument ?: null,
                $notes ?: null,
                Auth::userId()
            ]);

            // Update item stock
            $newStock = $item['current_stock'] + $quantityChange;
            $stmt = $db->prepare("UPDATE warehouse_items SET current_stock = ? WHERE id = ?");
            $stmt->execute([$newStock, $itemId]);

            $db->commit();

            $_SESSION['success'] = 'Pohyb zásob byl zaznamenán';
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Chyba při zaznamenávání pohybu: ' . $e->getMessage();
        }

        // Redirect back
        $redirect = $_POST['redirect'] ?? '/warehouse';
        header("Location: $redirect");
        exit;
    }

    public function setConsumption() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /warehouse');
            exit;
        }

        $itemId = (int)$_POST['item_id'];
        $weeklyConsumption = (float)$_POST['weekly_consumption'];
        $desiredWeeksStock = (int)$_POST['desired_weeks_stock'];
        $notes = trim($_POST['notes'] ?? '');

        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("
                INSERT INTO warehouse_consumption
                (item_id, weekly_consumption, desired_weeks_stock, notes, created_by)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                weekly_consumption = ?,
                desired_weeks_stock = ?,
                notes = ?,
                updated_at = CURRENT_TIMESTAMP
            ");

            $stmt->execute([
                $itemId,
                $weeklyConsumption,
                $desiredWeeksStock,
                $notes ?: null,
                Auth::userId(),
                $weeklyConsumption,
                $desiredWeeksStock,
                $notes ?: null
            ]);

            $_SESSION['success'] = 'Týdenní spotřeba byla nastavena';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Chyba při nastavování spotřeby: ' . $e->getMessage();
        }

        $redirect = $_POST['redirect'] ?? '/warehouse';
        header("Location: $redirect");
        exit;
    }

    public function inventory($workplaceId) {
        Auth::requireLogin();

        $db = Database::getInstance()->getConnection();
        $workplaceModel = new Workplace();

        // Get workplace and verify access
        $workplace = $workplaceModel->getById($workplaceId);

        if (!$workplace || !$workplaceModel->hasAccess(Auth::userId(), $workplaceId)) {
            $_SESSION['error'] = 'Nemáte přístup k tomuto pracovišti';
            header('Location: /warehouse');
            exit;
        }

        // Check if user has edit permission or is admin
        if (!Auth::isAdmin()) {
            $stmt = $db->prepare("
                SELECT can_edit FROM user_workplace_permissions
                WHERE user_id = ? AND workplace_id = ?
            ");
            $stmt->execute([Auth::userId(), $workplaceId]);
            $permission = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$permission || !$permission['can_edit']) {
                $_SESSION['error'] = 'Nemáte oprávnění k inventuře tohoto pracoviště';
                header('Location: /warehouse/workplace/' . $workplaceId);
                exit;
            }
        }

        // Get all items for this workplace
        $stmt = $db->prepare("
            SELECT
                wi.*
            FROM warehouse_items wi
            WHERE wi.workplace_id = ?
            ORDER BY wi.category, wi.name
        ");
        $stmt->execute([$workplaceId]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        View::render('warehouse/inventory', [
            'layout' => 'main',
            'title' => 'Inventura - ' . $workplace['name'],
            'workplace' => $workplace,
            'items' => $items
        ]);
    }

    public function saveInventory() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /warehouse');
            exit;
        }

        $workplaceId = (int)$_POST['workplace_id'];
        $inventoryDate = $_POST['inventory_date'] ?? date('Y-m-d');
        $stockChanges = $_POST['stock'] ?? [];

        $db = Database::getInstance()->getConnection();
        $workplaceModel = new Workplace();

        // Verify access and permissions
        $workplace = $workplaceModel->getById($workplaceId);

        if (!$workplace || !$workplaceModel->hasAccess(Auth::userId(), $workplaceId)) {
            $_SESSION['error'] = 'Nemáte přístup k tomuto pracovišti';
            header('Location: /warehouse');
            exit;
        }

        // Check edit permission
        if (!Auth::isAdmin()) {
            $stmt = $db->prepare("
                SELECT can_edit FROM user_workplace_permissions
                WHERE user_id = ? AND workplace_id = ?
            ");
            $stmt->execute([Auth::userId(), $workplaceId]);
            $permission = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$permission || !$permission['can_edit']) {
                $_SESSION['error'] = 'Nemáte oprávnění k inventuře tohoto pracoviště';
                header('Location: /warehouse/workplace/' . $workplaceId);
                exit;
            }
        }

        try {
            $db->beginTransaction();

            $updatedCount = 0;

            foreach ($stockChanges as $itemId => $newStock) {
                $itemId = (int)$itemId;
                $newStock = (float)$newStock;

                // Get current stock
                $stmt = $db->prepare("SELECT current_stock, name FROM warehouse_items WHERE id = ? AND workplace_id = ?");
                $stmt->execute([$itemId, $workplaceId]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    continue; // Skip invalid items
                }

                $currentStock = (float)$item['current_stock'];

                // Only update if there's a change
                if (abs($newStock - $currentStock) > 0.001) {
                    // Update stock
                    $stmt = $db->prepare("UPDATE warehouse_items SET current_stock = ? WHERE id = ?");
                    $stmt->execute([$newStock, $itemId]);

                    // Record movement with inventory date
                    $difference = $newStock - $currentStock;
                    $stmt = $db->prepare("
                        INSERT INTO warehouse_movements
                        (item_id, movement_type, quantity, movement_date, notes, created_by)
                        VALUES (?, 'adjustment', ?, ?, 'Inventura', ?)
                    ");
                    $stmt->execute([$itemId, $difference, $inventoryDate, Auth::userId()]);

                    $updatedCount++;
                }
            }

            $db->commit();

            $formattedDate = date('d.m.Y', strtotime($inventoryDate));
            $_SESSION['success'] = "Inventura k datu $formattedDate dokončena. Aktualizováno položek: $updatedCount";
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Chyba při ukládání inventury: ' . $e->getMessage();
        }

        header('Location: /warehouse/workplace/' . $workplaceId);
        exit;
    }

    public function itemDetail($itemId) {
        Auth::requireLogin();

        $db = Database::getInstance()->getConnection();

        // Get item details
        $stmt = $db->prepare("
            SELECT
                wi.*,
                w.name as workplace_name,
                wc.weekly_consumption,
                wc.desired_weeks_stock,
                wc.notes as consumption_notes
            FROM warehouse_items wi
            LEFT JOIN workplaces w ON wi.workplace_id = w.id
            LEFT JOIN warehouse_consumption wc ON wi.id = wc.item_id
            WHERE wi.id = ?
        ");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            $_SESSION['error'] = 'Položka nenalezena';
            header('Location: /warehouse');
            exit;
        }

        // Get movement history
        $stmt = $db->prepare("
            SELECT
                wm.*,
                u.full_name as created_by_name
            FROM warehouse_movements wm
            LEFT JOIN users u ON wm.created_by = u.id
            WHERE wm.item_id = ?
            ORDER BY wm.movement_date DESC, wm.created_at DESC
            LIMIT 50
        ");
        $stmt->execute([$itemId]);
        $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate actual consumption from movements (last 8 weeks)
        // Note: This query is optimized with aggregate functions and date filtering
        // For best performance, ensure index exists on: warehouse_movements(item_id, movement_type, movement_date)
        $stmt = $db->prepare("
            SELECT
                COALESCE(SUM(ABS(quantity)), 0) as total_out,
                DATEDIFF(CURDATE(), MIN(movement_date)) as days_period
            FROM warehouse_movements
            WHERE item_id = ?
            AND movement_type = 'out'
            AND movement_date >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
        ");
        $stmt->execute([$itemId]);
        $consumption = $stmt->fetch(PDO::FETCH_ASSOC);

        $actualWeeklyConsumption = 0;
        if ($consumption['days_period'] > 0 && $consumption['total_out'] > 0) {
            $weeksInPeriod = $consumption['days_period'] / 7;
            $actualWeeklyConsumption = $consumption['total_out'] / $weeksInPeriod;
        }

        // Get batches
        $stmt = $db->prepare("
            SELECT * FROM warehouse_batches
            WHERE item_id = ?
            AND quantity > 0
            ORDER BY expiration_date ASC
        ");
        $stmt->execute([$itemId]);
        $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        View::render('warehouse/item_detail', [
            'layout' => 'main',
            'title' => 'Detail položky - ' . $item['name'],
            'item' => $item,
            'movements' => $movements,
            'batches' => $batches,
            'actualWeeklyConsumption' => $actualWeeklyConsumption
        ]);
    }
}
