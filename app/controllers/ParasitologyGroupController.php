<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Workplace.php';
require_once __DIR__ . '/../models/ParasitologyGroup.php';

/**
 * Správa parazitologických skupin (Část 2 předělání parazitologie).
 *
 * Skupiny žijí uvnitř pracovní stránky parazitologie (seznam zvířat), proto
 * autorizace používá sekci 'animals' – stejně jako zakládání vyšetření
 * v ExaminationController. (Pozn.: odčervení historicky kontroluje sekci
 * 'parasitology'; sjednocení sekcí je mimo rozsah této změny.)
 */
class ParasitologyGroupController {

    /** Stránka se správou skupin daného pracoviště. */
    public function page($workplaceId) {
        Auth::requireLogin();
        $_SESSION['current_app'] = 'parasitology';

        if (!is_numeric($workplaceId)) {
            die('Neplatné ID pracoviště');
        }
        if (!userCan((int)$workplaceId, 'animals', 'view')) {
            die('Nemáte oprávnění k tomuto pracovišti');
        }

        $workplaceModel = new Workplace();
        $workplace = $workplaceModel->findById($workplaceId);
        if (!$workplace) {
            die('Pracoviště nenalezeno');
        }

        $groupModel = new ParasitologyGroup();
        $groups = $groupModel->getByWorkplace($workplaceId);

        // Členové po skupinách.
        $membersByGroup = [];
        foreach ($groups as $g) {
            $membersByGroup[$g['id']] = $groupModel->getMembers($g['id']);
        }

        // Aktivní zvířata pracoviště pro výběr do skupin + info o aktuální skupině.
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT a.id, a.name, a.identifier, a.species
            FROM animals a
            WHERE a.workplace_id = ? AND a.current_status = 'active'
            ORDER BY a.name ASC, a.identifier ASC
        ");
        $stmt->execute([$workplaceId]);
        $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $groupMap = $groupModel->getAnimalGroupMap(array_column($animals, 'id'));

        $canEdit = userCan((int)$workplaceId, 'animals', 'edit');

        View::render('parasitology/groups', [
            'layout'         => 'main',
            'title'          => 'Parazitologické skupiny - ' . $workplace['name'],
            'workplace'      => $workplace,
            'groups'         => $groups,
            'membersByGroup' => $membersByGroup,
            'animals'        => $animals,
            'groupMap'       => $groupMap,
            'canEdit'        => $canEdit,
        ]);
    }

    /** Založení nové skupiny (JSON). */
    public function create($workplaceId) {
        $this->guardEdit($workplaceId);

        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $name  = trim($input['name'] ?? '');
        $notes = trim($input['notes'] ?? '');

        if ($name === '') {
            $this->json(['success' => false, 'error' => 'Název skupiny nemůže být prázdný'], 400);
        }

        $groupModel = new ParasitologyGroup();
        try {
            $id = $groupModel->createGroup($workplaceId, $name, $notes);
            $this->json(['success' => true, 'id' => (int)$id]);
        } catch (Exception $e) {
            // Nejčastěji porušení UNIQUE(workplace_id, name).
            error_log('ParasitologyGroup create error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Skupina s tímto názvem už existuje'], 409);
        }
    }

    /** Přejmenování / úprava poznámky skupiny (JSON). */
    public function rename($workplaceId, $groupId) {
        $this->guardEdit($workplaceId);
        $group = $this->requireGroup($workplaceId, $groupId);

        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $name  = trim($input['name'] ?? '');
        $notes = trim($input['notes'] ?? '');

        if ($name === '') {
            $this->json(['success' => false, 'error' => 'Název skupiny nemůže být prázdný'], 400);
        }

        $groupModel = new ParasitologyGroup();
        try {
            $groupModel->renameGroup($group['id'], $name, $notes);
            $this->json(['success' => true]);
        } catch (Exception $e) {
            error_log('ParasitologyGroup rename error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Skupina s tímto názvem už existuje'], 409);
        }
    }

    /** Smazání skupiny (JSON). Členství se odstraní kaskádou. */
    public function delete($workplaceId, $groupId) {
        $this->guardEdit($workplaceId);
        $group = $this->requireGroup($workplaceId, $groupId);

        $groupModel = new ParasitologyGroup();
        $groupModel->deleteGroup($group['id']);
        $this->json(['success' => true]);
    }

    /** Přidání zvířat do skupiny (JSON). Zvířata se přesunou z jiné skupiny. */
    public function addMembers($workplaceId, $groupId) {
        $this->guardEdit($workplaceId);
        $group = $this->requireGroup($workplaceId, $groupId);

        $input     = json_decode(file_get_contents('php://input'), true) ?: [];
        $animalIds = $input['animal_ids'] ?? [];
        if (!is_array($animalIds) || empty($animalIds)) {
            $this->json(['success' => false, 'error' => 'Nebyla vybrána žádná zvířata'], 400);
        }

        $groupModel = new ParasitologyGroup();
        $count = $groupModel->addAnimals($group['id'], $workplaceId, $animalIds);
        $this->json(['success' => true, 'assigned' => $count]);
    }

    /** Odebrání jednoho zvířete ze skupiny (JSON). */
    public function removeMember($workplaceId, $groupId) {
        $this->guardEdit($workplaceId);
        $group = $this->requireGroup($workplaceId, $groupId);

        $input    = json_decode(file_get_contents('php://input'), true) ?: [];
        $animalId = (int)($input['animal_id'] ?? 0);
        if ($animalId <= 0) {
            $this->json(['success' => false, 'error' => 'Chybí ID zvířete'], 400);
        }

        $groupModel = new ParasitologyGroup();
        $groupModel->removeAnimal($group['id'], $animalId);
        $this->json(['success' => true]);
    }

    // ---- pomocné -------------------------------------------------------

    private function guardEdit($workplaceId) {
        Auth::requireLogin();
        header('Content-Type: application/json; charset=utf-8');
        if (!is_numeric($workplaceId) || !userCan((int)$workplaceId, 'animals', 'edit')) {
            $this->json(['success' => false, 'error' => 'Nemáte oprávnění upravovat toto pracoviště'], 403);
        }
    }

    private function requireGroup($workplaceId, $groupId) {
        $groupModel = new ParasitologyGroup();
        $group = $groupModel->findInWorkplace($groupId, $workplaceId);
        if (!$group) {
            $this->json(['success' => false, 'error' => 'Skupina nenalezena'], 404);
        }
        return $group;
    }

    private function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
