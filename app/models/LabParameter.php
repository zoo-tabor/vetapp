<?php

require_once __DIR__ . '/../core/Model.php';

/**
 * Kanonický číselník laboratorních parametrů (biochemie + hematologie).
 *
 * Jediný zdroj pravdy o tom, jaké parametry existují a jak se jmenují.
 * Ruční zadávání, správa referenčních mezí, zobrazení i LDT import čtou
 * z této tabulky, takže se názvy parametrů nikde nerozbíhají.
 *
 * - lab_parameters: kanonický parametr (test_type + name je unikátní)
 * - lab_parameter_aliases: synonyma (RBC -> Erytrocyty, Urea -> Močovina, ...),
 *   včetně "self-aliasu" na vlastní kanonický název. Párování z importu se
 *   ukládá právě sem, takže je příště automatické.
 */
class LabParameter extends Model {

    const TEST_TYPES = ['biochemistry', 'hematology'];

    public function __construct() {
        parent::__construct();
        $this->table = 'lab_parameters';
    }

    /**
     * Normalizace názvu pro párování: malá písmena, bez diakritiky,
     * sjednocené mezery a oddělovače. Musí být použita všude stejně.
     */
    public static function normalize($name) {
        $name = trim((string)$name);
        if ($name === '') {
            return '';
        }

        $name = strtr($name, [
            'á' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e', 'ě' => 'e',
            'í' => 'i', 'ň' => 'n', 'ó' => 'o', 'ř' => 'r', 'š' => 's',
            'ť' => 't', 'ú' => 'u', 'ů' => 'u', 'ý' => 'y', 'ž' => 'z',
            'Á' => 'a', 'Č' => 'c', 'Ď' => 'd', 'É' => 'e', 'Ě' => 'e',
            'Í' => 'i', 'Ň' => 'n', 'Ó' => 'o', 'Ř' => 'r', 'Š' => 's',
            'Ť' => 't', 'Ú' => 'u', 'Ů' => 'u', 'Ý' => 'y', 'Ž' => 'z',
            'ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'ß' => 'ss',
            'Ä' => 'a', 'Ö' => 'o', 'Ü' => 'u',
            'γ' => 'y', 'μ' => 'u',
        ]);

        $name = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);

        // sjednotit oddělovače a mezery
        $name = str_replace(['_', '/', '\\'], ' ', $name);
        $name = preg_replace('/[\s\-]+/', ' ', $name);

        return trim($name);
    }

    /**
     * Kanonické parametry daného typu (nebo všechny), seřazené pro zobrazení.
     */
    public function all($testType = null) {
        if ($testType !== null) {
            $rows = $this->query(
                "SELECT * FROM lab_parameters WHERE test_type = ? AND is_active = 1
                 ORDER BY sort_order ASC, name ASC",
                [$testType]
            );
        } else {
            $rows = $this->query(
                "SELECT * FROM lab_parameters WHERE is_active = 1
                 ORDER BY test_type ASC, sort_order ASC, name ASC"
            );
        }

        // Ignorované (přeskakované) parametry se v číselníku nenabízejí.
        // Filtrujeme v PHP, aby kód fungoval i před spuštěním migrace 019 –
        // dokud sloupec is_ignored neexistuje, klíč v řádku chybí a bereme 0.
        return array_values(array_filter($rows, fn($r) => empty($r['is_ignored'])));
    }

    /**
     * Najde kanonický parametr podle přesného id.
     */
    public function find($id) {
        return $this->findById((int)$id);
    }

    /**
     * Pokusí se přiřadit surový název parametru ke kanonickému parametru
     * pomocí aliasů (včetně self-aliasu). Vrací řádek lab_parameters nebo null.
     */
    public function resolve($testType, $rawName) {
        $norm = self::normalize($rawName);
        if ($norm === '') {
            return null;
        }

        $rows = $this->query(
            "SELECT p.* FROM lab_parameter_aliases a
             JOIN lab_parameters p ON p.id = a.parameter_id
             WHERE a.test_type = ? AND a.alias_norm = ?
             LIMIT 1",
            [$testType, $norm]
        );

        return $rows[0] ?? null;
    }

    /**
     * Napáruje surový název bez ohledu na typ testu (biochemie/hematologie).
     *
     * Používá se v LDT importu, kde hlavička sekce není spolehlivá – o zařazení
     * parametru do biochemie/hematologie rozhoduje číselník (test_type
     * napárovaného parametru), ne hlavička v LDT. Vrací řádek lab_parameters
     * (včetně test_type a is_ignored) nebo null.
     *
     * $preferTestType posune na první místo shodu ve stejné sekci, kdyby
     * stejný normalizovaný alias existoval v obou sekcích.
     */
    public function resolveAny($rawName, $preferTestType = null) {
        $norm = self::normalize($rawName);
        if ($norm === '') {
            return null;
        }

        // Pozn.: is_ignored zde záměrně není v ORDER BY, aby resolveAny fungoval
        // i před migrací 019 (sloupec ještě nemusí existovat). Hodnota se vrací
        // přes p.* a čte se v PHP jako !empty($param['is_ignored']).
        $rows = $this->query(
            "SELECT p.* FROM lab_parameter_aliases a
             JOIN lab_parameters p ON p.id = a.parameter_id
             WHERE a.alias_norm = ?
             ORDER BY (p.test_type = ?) DESC, a.id ASC
             LIMIT 1",
            [$norm, $preferTestType ?? '']
        );

        return $rows[0] ?? null;
    }

    /**
     * Označí parametr jako ignorovaný (trvalé "přeskočit" pro import).
     *
     * Založí (nebo znovupoužije) kanonický parametr daného typu, nastaví
     * is_ignored = 1 a uloží alias pro surový název, takže se stejný název
     * příště přeskočí automaticky. Vrací řádek lab_parameters.
     */
    public function ignore($testType, $rawName, $unit = '') {
        if (!in_array($testType, self::TEST_TYPES, true)) {
            throw new InvalidArgumentException('Neplatný typ testu.');
        }
        $name = trim((string)$rawName);
        if ($name === '') {
            throw new InvalidArgumentException('Prázdný název parametru.');
        }

        $existing = $this->query(
            "SELECT * FROM lab_parameters WHERE test_type = ? AND name = ? LIMIT 1",
            [$testType, $name]
        );

        if (!empty($existing)) {
            $id = (int)$existing[0]['id'];
        } else {
            $id = $this->createParameter($testType, $name, $unit);
        }

        $this->execute("UPDATE lab_parameters SET is_ignored = 1 WHERE id = ?", [$id]);
        $this->addAlias($id, $testType, $rawName);

        return $this->find($id);
    }

    /**
     * Přiřadí surový název; když neexistuje, založí nový kanonický parametr
     * i s odpovídajícím aliasem. Vždy vrací řádek lab_parameters.
     */
    public function resolveOrCreate($testType, $rawName, $unit = '') {
        $existing = $this->resolve($testType, $rawName);
        if ($existing) {
            return $existing;
        }

        $id = $this->createParameter($testType, $rawName, $unit);
        return $this->find($id);
    }

    /**
     * Založí nový kanonický parametr a jeho self-alias. Vrací id.
     * Pokud parametr se stejným názvem už existuje, vrátí jeho id.
     */
    public function createParameter($testType, $name, $unit = '', $sortOrder = null) {
        $name = trim((string)$name);
        $unit = trim((string)$unit);

        if (!in_array($testType, self::TEST_TYPES, true) || $name === '') {
            throw new InvalidArgumentException('Neplatný parametr.');
        }

        // Už existuje pod stejným kanonickým názvem?
        $existing = $this->query(
            "SELECT * FROM lab_parameters WHERE test_type = ? AND name = ? LIMIT 1",
            [$testType, $name]
        );
        if (!empty($existing)) {
            $this->addAlias($existing[0]['id'], $testType, $name);
            return (int)$existing[0]['id'];
        }

        if ($sortOrder === null) {
            $max = $this->query(
                "SELECT COALESCE(MAX(sort_order), 0) AS m FROM lab_parameters WHERE test_type = ?",
                [$testType]
            );
            $sortOrder = (int)($max[0]['m'] ?? 0) + 10;
        }

        $this->execute(
            "INSERT INTO lab_parameters (test_type, name, unit, sort_order, is_active)
             VALUES (?, ?, ?, ?, 1)",
            [$testType, $name, $unit, $sortOrder]
        );
        $id = (int)$this->db->lastInsertId();

        // self-alias, aby resolve() vždy trefil vlastní název
        $this->addAlias($id, $testType, $name);

        return $id;
    }

    /**
     * Přidá alias (synonymum) ke kanonickému parametru. Idempotentní.
     */
    public function addAlias($parameterId, $testType, $alias) {
        $alias = trim((string)$alias);
        $norm = self::normalize($alias);
        if ($norm === '') {
            return;
        }

        $this->execute(
            "INSERT INTO lab_parameter_aliases (parameter_id, test_type, alias, alias_norm)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE parameter_id = VALUES(parameter_id), alias = VALUES(alias)",
            [(int)$parameterId, $testType, $alias, $norm]
        );
    }

    /**
     * Aliasy daného parametru (pro zobrazení / správu).
     */
    public function aliases($parameterId) {
        return $this->query(
            "SELECT * FROM lab_parameter_aliases WHERE parameter_id = ? ORDER BY alias ASC",
            [(int)$parameterId]
        );
    }
}
