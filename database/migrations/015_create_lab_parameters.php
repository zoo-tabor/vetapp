<?php
/**
 * Zavádí kanonický číselník laboratorních parametrů a napojuje na něj
 * existující výsledky i referenční meze.
 *
 * 1. lab_parameters + lab_parameter_aliases
 * 2. seed kanonických parametrů (biochemie + hematologie) a synonym
 * 3. biochemistry_results / hematology_results / reference_ranges dostanou
 *    parameter_id; existující řádky se namapují přes aliasy, názvy se sjednotí
 *    na kanonické a duplicitní řádky se sloučí.
 */
return function(PDO $pdo) {

    // --- lokální normalizace (shodná s LabParameter::normalize) ---
    $normalize = function($name) {
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
        $name = str_replace(['_', '/', '\\'], ' ', $name);
        $name = preg_replace('/[\s\-]+/', ' ', $name);
        return trim($name);
    };

    // --- 1. tabulky ---
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lab_parameters (
            id INT(11) NOT NULL AUTO_INCREMENT,
            test_type ENUM('biochemistry','hematology') NOT NULL,
            name VARCHAR(100) NOT NULL,
            unit VARCHAR(50) NOT NULL DEFAULT '',
            sort_order INT(11) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
            updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (id),
            UNIQUE KEY uniq_param (test_type, name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS lab_parameter_aliases (
            id INT(11) NOT NULL AUTO_INCREMENT,
            parameter_id INT(11) NOT NULL,
            test_type ENUM('biochemistry','hematology') NOT NULL,
            alias VARCHAR(100) NOT NULL,
            alias_norm VARCHAR(100) NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (id),
            UNIQUE KEY uniq_alias (test_type, alias_norm),
            KEY parameter_id (parameter_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // --- 2. seed ---
    $biochem = [
        ['Amyláza', 'U/L'], ['Lipáza', 'U/L'], ['Glukóza', 'mmol/L'],
        ['Fruktozamin', 'µmol/L'], ['Triacylglyceridy', 'mmol/L'], ['Cholesterol', 'mmol/L'],
        ['Bilirubin celkový', 'µmol/L'], ['ALP', 'U/L'], ['GLDH', 'U/L'], ['y-GT', 'U/L'],
        ['ALT', 'U/L'], ['AST', 'U/L'], ['CK (Kreatinkináza)', 'U/L'],
        ['Celková bílkovina', 'g/L'], ['Albumin', 'g/L'], ['Globuliny', 'g/L'],
        ['A/G poměr', ''], ['SDMA', 'µg/dL'], ['Močovina', 'mmol/L'], ['Kreatinin', 'µmol/L'],
        ['Fosfor', 'mmol/L'], ['Hořčík', 'mmol/L'], ['Vápník', 'mmol/L'], ['Chloridy', 'mmol/L'],
        ['Sodík', 'mmol/L'], ['Draslík', 'mmol/L'], ['Na-/K-kvocient', ''], ['Železo', 'µmol/L'],
        ['T4', 'nmol/L'], ['FT4', 'pmol/L'], ['TSH', 'ng/mL'],
    ];
    $hemato = [
        ['Erytrocyty', '10^12/L'], ['Hematokrit', '%'], ['Hemoglobin', 'g/L'],
        ['Hypochromazie', '%'], ['Anizocytoza', '%'], ['MCHC', 'g/L'], ['MCH', 'pg'],
        ['MCV', 'fL'], ['Retikulocyty', '%'], ['IRF', '%'], ['Ret-He', 'pg'],
        ['Leukocyty', '10^9/L'], ['Neutrofily', '%'], ['Lymfocyty', '%'], ['Monocyty', '%'],
        ['Eozinofily', '%'], ['Bazofily', '%'], ['Tyčky', '%'],
        ['Neutrofily - absolutní', '10^9/L'], ['Lymfocyty - absolutní', '10^9/L'],
        ['Monocyty - absolutní', '10^9/L'], ['Eozinofily - absolutní', '10^9/L'],
        ['Bazofily - absolutní', '10^9/L'], ['Tyčky - absolutní', '10^9/L'],
        ['Trombocyty', '10^9/L'],
    ];

    // synonyma: kanonický název => [aliasy]
    $synonyms = [
        'biochemistry' => [
            'Močovina'           => ['Urea', 'BUN'],
            'Glukóza'            => ['Glucose', 'Glukoza', 'GLU'],
            'y-GT'               => ['GGT', 'gama-GT', 'γ-GT', 'GMT'],
            'CK (Kreatinkináza)' => ['CK', 'Kreatinkináza', 'CPK'],
            'Vápník'             => ['Ca', 'Calcium', 'Kalcium'],
            'Fosfor'             => ['P', 'Phosphor', 'Fosfát', 'Anorganický fosfát'],
            'Sodík'              => ['Na', 'Natrium', 'Sodium'],
            'Draslík'            => ['K', 'Kalium', 'Potassium'],
            'Hořčík'             => ['Mg', 'Magnesium'],
            'Chloridy'           => ['Cl', 'Chlorid', 'Chloride'],
            'Železo'             => ['Fe', 'Iron'],
            'Celková bílkovina'  => ['TP', 'Total protein', 'Celkova bilkovina', 'Bílkovina celková'],
            'Bilirubin celkový'  => ['Bilirubin', 'TBIL', 'Total bilirubin', 'Celkový bilirubin'],
            'Cholesterol'        => ['CHOL'],
            'Triacylglyceridy'   => ['Triglyceridy', 'TG', 'Triacylglyceroly'],
            'Kreatinin'          => ['Creatinine', 'CREA', 'Kreatin'],
            'Albumin'            => ['ALB'],
        ],
        'hematology' => [
            'Erytrocyty'  => ['RBC', 'Red blood cells', 'Červené krvinky'],
            'Leukocyty'   => ['WBC', 'White blood cells', 'Bílé krvinky'],
            'Hemoglobin'  => ['HGB', 'Hb', 'HB'],
            'Hematokrit'  => ['HCT', 'Ht', 'PCV'],
            'Trombocyty'  => ['PLT', 'Platelets', 'Thrombocyty', 'Krevní destičky'],
            'Neutrofily'  => ['Neutrophils', 'NEU', 'Segmenty'],
            'Lymfocyty'   => ['Lymphocytes', 'LYM'],
            'Monocyty'    => ['Monocytes', 'MON'],
            'Eozinofily'  => ['Eosinophils', 'EOS'],
            'Bazofily'    => ['Basophils', 'BAS'],
            'Retikulocyty' => ['Reticulocytes', 'RET'],
            'Tyčky'       => ['Tyčky (band)', 'Bands'],
        ],
    ];

    $insParam = $pdo->prepare(
        "INSERT INTO lab_parameters (test_type, name, unit, sort_order, is_active)
         VALUES (?, ?, ?, ?, 1)
         ON DUPLICATE KEY UPDATE unit = VALUES(unit), sort_order = VALUES(sort_order)"
    );
    $insAlias = $pdo->prepare(
        "INSERT INTO lab_parameter_aliases (parameter_id, test_type, alias, alias_norm)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE parameter_id = VALUES(parameter_id), alias = VALUES(alias)"
    );

    // aliasMap[test_type][alias_norm] = parameter_id  (in-memory zrcadlo aliasů)
    $aliasMap = ['biochemistry' => [], 'hematology' => []];
    // canonName[parameter_id] = kanonický název
    $canonName = [];

    $findParamId = function($testType, $name) use ($pdo) {
        $stmt = $pdo->prepare("SELECT id FROM lab_parameters WHERE test_type = ? AND name = ? LIMIT 1");
        $stmt->execute([$testType, $name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['id'] : null;
    };

    $addAlias = function($paramId, $testType, $alias) use ($insAlias, $normalize, &$aliasMap) {
        $norm = $normalize($alias);
        if ($norm === '') {
            return;
        }
        $insAlias->execute([$paramId, $testType, trim($alias), $norm]);
        $aliasMap[$testType][$norm] = $paramId;
    };

    $seed = function($testType, $list) use ($insParam, $findParamId, $addAlias, &$canonName) {
        $order = 0;
        foreach ($list as $item) {
            $order += 10;
            [$name, $unit] = $item;
            $insParam->execute([$testType, $name, $unit, $order]);
            $id = $findParamId($testType, $name);
            if ($id === null) {
                continue;
            }
            $canonName[$id] = $name;
            $addAlias($id, $testType, $name); // self-alias
        }
    };

    $seed('biochemistry', $biochem);
    $seed('hematology', $hemato);

    foreach ($synonyms as $testType => $map) {
        foreach ($map as $canonical => $aliases) {
            $id = $findParamId($testType, $canonical);
            if ($id === null) {
                continue;
            }
            foreach ($aliases as $alias) {
                $addAlias($id, $testType, $alias);
            }
        }
    }

    // --- helper: zajistí sloupec parameter_id ---
    $ensureParamIdColumn = function($table) use ($pdo) {
        $stmt = $pdo->query("SHOW COLUMNS FROM {$table} LIKE 'parameter_id'");
        if ($stmt->fetch() === false) {
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN parameter_id INT(11) DEFAULT NULL AFTER test_id");
            $pdo->exec("ALTER TABLE {$table} ADD KEY parameter_id (parameter_id)");
        }
    };

    // resolveOrCreate namapuje surový název na parameter_id, případně založí nový
    $resolveOrCreate = function($testType, $rawName, $unit) use (
        $normalize, &$aliasMap, &$canonName, $insParam, $findParamId, $addAlias, $pdo
    ) {
        $norm = $normalize($rawName);
        if ($norm === '') {
            return null;
        }
        if (isset($aliasMap[$testType][$norm])) {
            return $aliasMap[$testType][$norm];
        }

        // nový kanonický parametr z existujícího názvu
        $name = trim((string)$rawName);
        $stmt = $pdo->prepare("SELECT COALESCE(MAX(sort_order),0)+10 AS m FROM lab_parameters WHERE test_type = ?");
        $stmt->execute([$testType]);
        $order = (int)$stmt->fetchColumn();

        $insParam->execute([$testType, $name, trim((string)$unit), $order]);
        $id = $findParamId($testType, $name);
        if ($id === null) {
            return null;
        }
        $canonName[$id] = $name;
        $addAlias($id, $testType, $name);
        return $id;
    };

    // --- 3. napojení výsledků ---
    $resultTables = [
        'biochemistry_results' => 'biochemistry',
        'hematology_results'   => 'hematology',
    ];

    foreach ($resultTables as $table => $testType) {
        $ensureParamIdColumn($table);

        $rows = $pdo->query("SELECT id, parameter_name, unit FROM {$table}")->fetchAll(PDO::FETCH_ASSOC);
        $upd = $pdo->prepare("UPDATE {$table} SET parameter_id = ?, parameter_name = ? WHERE id = ?");

        foreach ($rows as $row) {
            $pid = $resolveOrCreate($testType, $row['parameter_name'], $row['unit'] ?? '');
            if ($pid === null) {
                continue;
            }
            $canon = $canonName[$pid] ?? $row['parameter_name'];
            $upd->execute([$pid, $canon, $row['id']]);
        }

        // sloučit duplicity v rámci testu: nejdřív zahodit prázdné hodnoty,
        // pokud existuje neprázdná; pak z ostatních nechat nejnovější (max id)
        $pdo->exec("
            DELETE r FROM {$table} r
            JOIN {$table} r2
              ON r.test_id = r2.test_id AND r.parameter_id = r2.parameter_id
            WHERE r.parameter_id IS NOT NULL
              AND (r.value IS NULL OR r.value = '')
              AND (r2.value IS NOT NULL AND r2.value <> '')
        ");
        $pdo->exec("
            DELETE r FROM {$table} r
            JOIN {$table} r2
              ON r.test_id = r2.test_id AND r.parameter_id = r2.parameter_id AND r.id < r2.id
            WHERE r.parameter_id IS NOT NULL
        ");

        // od teď jeden parametr max jednou v rámci testu
        try {
            $pdo->exec("ALTER TABLE {$table} ADD UNIQUE KEY uniq_test_param (test_id, parameter_id)");
        } catch (Exception $e) {}
    }

    // --- 3b. referenční meze ---
    $stmt = $pdo->query("SHOW TABLES LIKE 'reference_ranges'");
    if ($stmt->fetch() !== false) {
        $ensureParamIdColumn('reference_ranges');

        $rows = $pdo->query("SELECT id, test_type, parameter_name, unit FROM reference_ranges")->fetchAll(PDO::FETCH_ASSOC);
        $upd = $pdo->prepare("UPDATE reference_ranges SET parameter_id = ?, parameter_name = ? WHERE id = ?");

        foreach ($rows as $row) {
            $testType = $row['test_type'];
            if (!in_array($testType, ['biochemistry', 'hematology'], true)) {
                continue;
            }
            $pid = $resolveOrCreate($testType, $row['parameter_name'], $row['unit'] ?? '');
            if ($pid === null) {
                continue;
            }
            $canon = $canonName[$pid] ?? $row['parameter_name'];
            $upd->execute([$pid, $canon, $row['id']]);
        }

        // sloučit duplicitní meze (nechat nejnovější)
        $pdo->exec("
            DELETE r FROM reference_ranges r
            JOIN reference_ranges r2
              ON r.test_type = r2.test_type AND r.parameter_id = r2.parameter_id
             AND r.species = r2.species AND r.source = r2.source AND r.id < r2.id
            WHERE r.parameter_id IS NOT NULL
        ");

        // přepnout unikátní klíč na parameter_id
        try { $pdo->exec("ALTER TABLE reference_ranges DROP INDEX unique_reference"); } catch (Exception $e) {}
        try {
            $pdo->exec("ALTER TABLE reference_ranges
                        ADD UNIQUE KEY unique_reference_pid (test_type, parameter_id, species, source)");
        } catch (Exception $e) {}
    }
};
