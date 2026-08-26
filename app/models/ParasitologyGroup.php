<?php
require_once __DIR__ . '/../core/Model.php';

/**
 * Parazitologická skupina – editovatelná množina zvířat v rámci pracoviště.
 *
 * Členství: tabulka parasitology_group_members s UNIQUE(animal_id) → zvíře smí
 * být nejvýše v jedné skupině. Přiřazení zvířete do skupiny ho tedy automaticky
 * odebere z případné předchozí skupiny (viz addAnimals()).
 */
class ParasitologyGroup extends Model {

    protected $table = 'parasitology_groups';

    /** Skupiny pracoviště včetně počtu členů. */
    public function getByWorkplace($workplaceId) {
        return $this->query("
            SELECT g.*, COUNT(m.id) AS member_count
            FROM parasitology_groups g
            LEFT JOIN parasitology_group_members m ON m.group_id = g.id
            WHERE g.workplace_id = ? AND g.is_active = 1
            GROUP BY g.id
            ORDER BY g.name ASC
        ", [(int)$workplaceId]);
    }

    /** Skupina jen pokud patří danému pracovišti (autorizační pojistka). */
    public function findInWorkplace($groupId, $workplaceId) {
        $rows = $this->query(
            "SELECT * FROM parasitology_groups WHERE id = ? AND workplace_id = ?",
            [(int)$groupId, (int)$workplaceId]
        );
        return $rows[0] ?? null;
    }

    /** Členové skupiny (zvířata) s výběhem. */
    public function getMembers($groupId) {
        return $this->query("
            SELECT a.id, a.name, a.identifier, a.species, a.current_status,
                   e.name AS enclosure_name
            FROM parasitology_group_members m
            JOIN animals a ON a.id = m.animal_id
            LEFT JOIN enclosures e ON a.current_enclosure_id = e.id
            WHERE m.group_id = ?
            ORDER BY a.name ASC, a.identifier ASC
        ", [(int)$groupId]);
    }

    /** ID zvířat ve skupině. */
    public function getMemberAnimalIds($groupId) {
        $rows = $this->query(
            "SELECT animal_id FROM parasitology_group_members WHERE group_id = ?",
            [(int)$groupId]
        );
        return array_map(fn($r) => (int)$r['animal_id'], $rows);
    }

    /**
     * Mapa animal_id => ['id','name'] skupiny, ve které zvíře je.
     * Slouží k zobrazení příslušnosti zvířete v seznamech (bez N+1).
     */
    public function getAnimalGroupMap(array $animalIds) {
        $animalIds = array_values(array_filter(array_map('intval', $animalIds), fn($v) => $v > 0));
        if (empty($animalIds)) {
            return [];
        }
        $ph = implode(',', array_fill(0, count($animalIds), '?'));
        $rows = $this->query("
            SELECT m.animal_id, g.id AS group_id, g.name AS group_name
            FROM parasitology_group_members m
            JOIN parasitology_groups g ON g.id = m.group_id
            WHERE m.animal_id IN ($ph)
        ", $animalIds);

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['animal_id']] = [
                'id'   => (int)$r['group_id'],
                'name' => $r['group_name'],
            ];
        }
        return $map;
    }

    public function createGroup($workplaceId, $name, $notes = null) {
        return $this->create([
            'workplace_id' => (int)$workplaceId,
            'name'         => $name,
            'notes'        => ($notes !== null && $notes !== '') ? $notes : null,
        ]);
    }

    public function renameGroup($groupId, $name, $notes = null) {
        return $this->update((int)$groupId, [
            'name'  => $name,
            'notes' => ($notes !== null && $notes !== '') ? $notes : null,
        ]);
    }

    /** Smaže skupinu; členství se odstraní přes ON DELETE CASCADE. */
    public function deleteGroup($groupId) {
        return $this->delete((int)$groupId);
    }

    /**
     * Přiřadí zvířata do skupiny. Díky UNIQUE(animal_id) je případně přesune
     * z jiné skupiny. Přijme jen zvířata patřící do pracoviště skupiny.
     * Vrátí počet skutečně přiřazených zvířat.
     */
    public function addAnimals($groupId, $workplaceId, array $animalIds) {
        $animalIds = array_values(array_unique(array_filter(
            array_map('intval', $animalIds),
            fn($v) => $v > 0
        )));
        if (empty($animalIds)) {
            return 0;
        }

        // Ponechat jen zvířata z pracoviště této skupiny.
        $ph = implode(',', array_fill(0, count($animalIds), '?'));
        $valid = $this->query(
            "SELECT id FROM animals WHERE workplace_id = ? AND id IN ($ph)",
            array_merge([(int)$workplaceId], $animalIds)
        );
        $validIds = array_map(fn($r) => (int)$r['id'], $valid);
        if (empty($validIds)) {
            return 0;
        }

        $this->db->beginTransaction();
        try {
            $ins = $this->db->prepare("
                INSERT INTO parasitology_group_members (group_id, animal_id)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE group_id = VALUES(group_id)
            ");
            foreach ($validIds as $aid) {
                $ins->execute([(int)$groupId, $aid]);
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return count($validIds);
    }

    /** Odebere jedno zvíře ze skupiny. */
    public function removeAnimal($groupId, $animalId) {
        return $this->execute(
            "DELETE FROM parasitology_group_members WHERE group_id = ? AND animal_id = ?",
            [(int)$groupId, (int)$animalId]
        );
    }
}
