<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Workplace.php';
require_once __DIR__ . '/../models/ParasitologyGroup.php';
require_once __DIR__ . '/../models/Examination.php';

/**
 * Import parazitologických výsledků ze SVÚ Jihlava (.xlsx) – Část 4.
 *
 * Tok: upload → parse (uloženo v session) → náhled s ručním párováním popisu
 * vzorku na zvíře/skupinu (předvyplněno z paměti aliasů, ale VŽDY k ruční
 * kontrole) → provedení (založí individuální / skupinová vyšetření přes
 * Examination model a uloží aliasy).
 *
 * Autorizace sekcí 'animals' edit (stejně jako zakládání vyšetření).
 */
class ParasitologyImportController {

    private const SESSION_KEY = 'paras_import';

    // ---- Stránky ---------------------------------------------------------

    public function index($workplaceId) {
        $this->requireEdit($workplaceId);
        $workplace = $this->workplace($workplaceId);

        View::render('parasitology/import', [
            'layout'    => 'main',
            'title'     => 'Import parazitologie - ' . $workplace['name'],
            'workplace' => $workplace,
        ]);
    }

    public function upload($workplaceId) {
        $this->requireEdit($workplaceId);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/workplace/$workplaceId/parasitology-import");
        }

        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Chyba při nahrávání souboru.';
            $this->redirect("/workplace/$workplaceId/parasitology-import");
        }

        $file = $_FILES['import_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            $_SESSION['error'] = 'Nepodporovaný formát. Importovat lze pouze .xlsx soubory ze SVÚ.';
            $this->redirect("/workplace/$workplaceId/parasitology-import");
        }

        try {
            $parsed = $this->parseSvuXlsx($file['tmp_name']);
            if (empty($parsed['samples'])) {
                $_SESSION['error'] = 'V souboru nebyly nalezeny žádné vzorky. Zkontrolujte, že jde o protokol SVÚ.';
                $this->redirect("/workplace/$workplaceId/parasitology-import");
            }

            $_SESSION[self::SESSION_KEY] = [
                'workplace_id' => (int)$workplaceId,
                'filename'     => $file['name'],
                'parsed'       => $parsed,
            ];
            $this->redirect("/workplace/$workplaceId/parasitology-import/preview");
        } catch (Exception $e) {
            error_log('Parasitology import parse error: ' . $e->getMessage());
            $_SESSION['error'] = 'Chyba při zpracování souboru: ' . $e->getMessage();
            $this->redirect("/workplace/$workplaceId/parasitology-import");
        }
    }

    public function preview($workplaceId) {
        $this->requireEdit($workplaceId);
        $workplace = $this->workplace($workplaceId);

        $sess = $_SESSION[self::SESSION_KEY] ?? null;
        if (!$sess || (int)$sess['workplace_id'] !== (int)$workplaceId) {
            $_SESSION['error'] = 'Žádná data k náhledu. Nahrajte soubor.';
            $this->redirect("/workplace/$workplaceId/parasitology-import");
        }

        $parsed = $sess['parsed'];
        $db = Database::getInstance()->getConnection();

        // Zvířata a skupiny pracoviště pro našeptávač + popisky.
        $stmt = $db->prepare("
            SELECT id, name, identifier, species
            FROM animals
            WHERE workplace_id = ? AND current_status = 'active'
            ORDER BY name ASC, identifier ASC
        ");
        $stmt->execute([$workplaceId]);
        $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $groupModel = new ParasitologyGroup();
        $groups = $groupModel->getByWorkplace($workplaceId);

        // Popisky pro našeptávač (musí být jednoznačné kvůli mapování zpět).
        $animalLabels = [];
        foreach ($animals as $a) {
            $animalLabels[(int)$a['id']] = $this->animalLabel($a);
        }
        $groupLabels = [];
        foreach ($groups as $g) {
            $groupLabels[(int)$g['id']] = $this->groupLabel($g);
        }

        // Aliasy (návrhy párování) pro toto pracoviště.
        $aliases = $this->getAliasMap($workplaceId);

        // Ke každému vzorku dopočítat návrh (label), pokud cíl stále existuje.
        foreach ($parsed['samples'] as &$s) {
            $s['suggestion'] = '';
            $norm = $this->normalizeSampleText($s['description'] ?? '');
            if ($norm !== '' && isset($aliases[$norm])) {
                $al = $aliases[$norm];
                if ($al['type'] === 'animal' && isset($animalLabels[$al['id']])) {
                    $s['suggestion'] = $animalLabels[$al['id']];
                } elseif ($al['type'] === 'group' && isset($groupLabels[$al['id']])) {
                    $s['suggestion'] = $groupLabels[$al['id']];
                }
            }
        }
        unset($s);

        View::render('parasitology/import_preview', [
            'layout'       => 'main',
            'title'        => 'Náhled importu - ' . $workplace['name'],
            'workplace'    => $workplace,
            'filename'     => $sess['filename'],
            'parsed'       => $parsed,
            'animals'      => $animals,
            'groups'       => $groups,
            'animalLabels' => $animalLabels,
            'groupLabels'  => $groupLabels,
        ]);
    }

    public function execute($workplaceId) {
        $this->requireEdit($workplaceId);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect("/workplace/$workplaceId/parasitology-import");
        }

        $sess = $_SESSION[self::SESSION_KEY] ?? null;
        if (!$sess || (int)$sess['workplace_id'] !== (int)$workplaceId) {
            $_SESSION['error'] = 'Žádná data k importu.';
            $this->redirect("/workplace/$workplaceId/parasitology-import");
        }

        $parsed  = $sess['parsed'];
        $samples = $parsed['samples'];
        $date    = $parsed['date'] ?: date('Y-m-d');
        $protocol = $parsed['protocol'] ?? '';

        $pairType = $_POST['pair_type'] ?? [];
        $pairId   = $_POST['pair_id'] ?? [];

        $examModel  = new Examination();
        $groupModel = new ParasitologyGroup();
        $db = Database::getInstance()->getConnection();

        $created = 0;
        $skipped = 0;
        $duplicates = 0;
        $errors = [];

        foreach ($samples as $i => $sample) {
            $type = $pairType[$i] ?? 'skip';
            $id   = (int)($pairId[$i] ?? 0);

            if ($type !== 'animal' && $type !== 'group') {
                $skipped++;
                continue;
            }

            // Autorizace cíle vůči pracovišti.
            $memberIds = [];
            if ($type === 'animal') {
                $chk = $db->prepare("SELECT workplace_id FROM animals WHERE id = ?");
                $chk->execute([$id]);
                if ((int)$chk->fetchColumn() !== (int)$workplaceId) {
                    $errors[] = "Vzorek „{$sample['description']}\": zvíře nepatří do pracoviště.";
                    continue;
                }
            } else {
                $group = $groupModel->findInWorkplace($id, $workplaceId);
                if (!$group) {
                    $errors[] = "Vzorek „{$sample['description']}\": skupina nepatří do pracoviště.";
                    continue;
                }
                $memberIds = $groupModel->getMemberAnimalIds($id);
            }

            $notesBase = 'SVÚ Jihlava';
            if ($protocol) $notesBase .= ', protokol ' . $protocol;
            if (!empty($sample['zoo_no'])) $notesBase .= ', vz. ' . $sample['zoo_no'];
            if (!empty($sample['material'])) $notesBase .= ', ' . $sample['material'];

            foreach ($sample['results'] as $r) {
                $finding = trim($r['finding'] ?? '');
                if ($finding === '') continue;

                $isNeg = (mb_stripos($finding, 'negativní') !== false);
                $findingStatus = $isNeg ? 'negative' : 'positive';
                $parasite = $isNeg ? null : $finding;
                $note = trim($r['note'] ?? '');
                $intensity = $isNeg ? 'neg.' : ($note !== '' ? $note : '+');
                $sampleType = $this->mapSampleType($r['method'] ?? '', $note);

                // Dedupe – neimportovat stejný záznam dvakrát.
                if ($this->examinationExists($db, $workplaceId, $type, $id, $date, 'SVÚ Jihlava', $sampleType, $parasite)) {
                    $duplicates++;
                    continue;
                }

                $data = [
                    'examination_date' => $date,
                    'sample_type'      => $sampleType,
                    'institution'      => 'SVÚ Jihlava',
                    'parasite_found'   => $parasite,
                    'finding_status'   => $findingStatus,
                    'intensity'        => $intensity,
                    'notes'            => $notesBase,
                    'created_by'       => Auth::userId(),
                ];

                try {
                    if ($type === 'animal') {
                        $data['animal_id'] = $id;
                        $examModel->createExamination($data);
                    } else {
                        $examModel->createGroupExamination($id, $workplaceId, $memberIds, $data);
                    }
                    $created++;
                } catch (Exception $e) {
                    error_log('Parasitology import insert error: ' . $e->getMessage());
                    $errors[] = "Vzorek „{$sample['description']}\": chyba uložení výsledku.";
                }
            }

            // Uložit alias (návrh pro příště).
            $this->saveAlias($workplaceId, $sample['description'] ?? '', $type, $id);
        }

        unset($_SESSION[self::SESSION_KEY]);

        $msg = "Import dokončen: $created záznamů vytvořeno";
        if ($duplicates > 0) $msg .= ", $duplicates přeskočeno (duplicita)";
        if ($skipped > 0) $msg .= ", $skipped vzorků nespárováno";
        $_SESSION['success'] = $msg . '.';
        if (!empty($errors)) {
            $_SESSION['error'] = implode(' ', array_slice($errors, 0, 8));
        }

        // Zpět na import stránku (zobrazí souhrn); odtud odkaz na seznam zvířat.
        $this->redirect("/workplace/$workplaceId/parasitology-import");
    }

    // ---- Párování / aliasy ----------------------------------------------

    private function normalizeSampleText($s) {
        $s = mb_strtolower(trim((string)$s));
        return preg_replace('/\s+/u', ' ', $s);
    }

    private function getAliasMap($workplaceId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT sample_text_norm, target_type, target_id
            FROM parasitology_import_aliases WHERE workplace_id = ?
        ");
        $stmt->execute([$workplaceId]);
        $map = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $map[$row['sample_text_norm']] = ['type' => $row['target_type'], 'id' => (int)$row['target_id']];
        }
        return $map;
    }

    private function saveAlias($workplaceId, $description, $type, $id) {
        $norm = $this->normalizeSampleText($description);
        if ($norm === '') return;
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO parasitology_import_aliases (workplace_id, sample_text_norm, target_type, target_id)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE target_type = VALUES(target_type), target_id = VALUES(target_id)
        ");
        $stmt->execute([(int)$workplaceId, $norm, $type, (int)$id]);
    }

    private function animalLabel($a) {
        $name = ($a['name'] !== '' && $a['name'] !== null) ? $a['name'] : ('#' . $a['identifier']);
        $label = '🐾 ' . $name;
        if (!empty($a['species'])) $label .= ' – ' . $a['species'];
        if (!empty($a['identifier'])) $label .= ' · ' . $a['identifier'];
        $label .= ' · id' . (int)$a['id'];
        return $label;
    }

    private function groupLabel($g) {
        return '👪 ' . $g['name'] . ' · skupina (' . (int)($g['member_count'] ?? 0) . ')';
    }

    private function mapSampleType($method, $note) {
        $m = trim($method);
        if (mb_stripos($m, 'Kvantitativní') !== false) {
            if (stripos($note, 'OPG') !== false) return 'OPG';
            if (stripos($note, 'EPG') !== false) return 'EPG';
            return 'OPG';
        }
        return $m !== '' ? mb_substr($m, 0, 50) : 'Vyšetření';
    }

    private function examinationExists($db, $workplaceId, $type, $targetId, $date, $institution, $sampleType, $parasite) {
        $col = $type === 'animal' ? 'animal_id' : 'group_id';
        $sql = "SELECT id FROM examinations
                WHERE workplace_id = ? AND $col = ? AND examination_date = ?
                  AND institution <=> ? AND sample_type <=> ? AND parasite_found <=> ?
                LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([(int)$workplaceId, (int)$targetId, $date, $institution, $sampleType, $parasite]);
        return (bool)$stmt->fetchColumn();
    }

    // ---- Parser SVÚ .xlsx (odladěno proti reálným vzorkům) ---------------

    private function parseSvuXlsx($path) {
        $rows = $this->readXlsxRows($path);

        $result = ['date' => null, 'protocol' => null, 'receipt' => null, 'samples' => []];
        $samples = [];   // pr => [...]
        $results = [];   // pr => [ [method,finding,note], ... ]

        $mode = 'header';
        $lastSamplePr = null;
        $currentPr = null;

        $isFooter = function($a) {
            if ($a === null || $a === '') return true;
            $skip = ['Státní veterinární ústav', 'Laboratoře SVÚ', 'Zkušební protokol č',
                     'Č. dokumentu', 'Číslo příjmu', 'Odesílatel', 'Důvod vyšetření',
                     'KÚ (ZSJ)', 'Analýza(y) provedena', 'Majitel', 'Zoologická zahrada',
                     'Dukelských', 'Praha', 'Česká republika', 'Doručeno dne', 'ZKUŠEBNÍ PROTOKOL',
                     'Identifikace zakázky'];
            foreach ($skip as $s) {
                if (mb_stripos($a, $s) !== false) return true;
            }
            return false;
        };

        foreach ($rows as $r) {
            $a = isset($r[0]) ? $this->normRow($r[0]) : '';
            $b = isset($r[1]) ? $this->normRow($r[1]) : '';
            $c = isset($r[2]) ? $this->normRow($r[2]) : '';

            if ($result['date'] === null && mb_stripos($a, 'Datum odběru') !== false) {
                $result['date'] = $this->parseCzDate($a);
            }
            if ($result['protocol'] === null && mb_stripos($a, 'ZKUŠEBNÍ PROTOKOL') !== false) {
                if (preg_match('/(\d{4,6}\/\d{2})/', $a, $m)) $result['protocol'] = $m[1];
            }
            if ($result['receipt'] === null && mb_stripos($a, 'Číslo příjmu') !== false) {
                if (preg_match('/(\d{6,})/', $a, $m)) $result['receipt'] = $m[1];
            }

            if (mb_stripos($a, 'Vzorky') === 0) { $mode = 'samples'; continue; }
            if (mb_stripos($a, 'Parazitologické vyšetření') !== false) { $mode = 'results'; continue; }
            if (mb_stripos($a, 'Výsledky vyšetření') !== false) { $mode = 'results_wait'; continue; }
            if (mb_stripos($a, 'Závěr') === 0) { $mode = 'done'; continue; }

            if ($mode === 'samples') {
                if ($isFooter($a)) continue;
                if (preg_match('/^PR\s+(\d+)\s+(.*)$/u', $a, $m)) {
                    $pr = 'PR ' . $m[1];
                    $rest = $m[2];
                    $material = '';
                    if (preg_match('/^(\S+)/u', $rest, $mm)) $material = $mm[1];
                    $count = null;
                    if (preg_match('/(\d+)\s*$/', $rest, $mc)) $count = (int)$mc[1];
                    $samples[$pr] = ['pr' => $pr, 'zoo_no' => null, 'description' => null,
                                     'material' => $material, 'count' => $count];
                    $lastSamplePr = $pr;
                    continue;
                }
                // "26603/1 - popis" nebo "26637/1, popis" (oddělovač -/, ; popis smí mít čárky)
                if ($lastSamplePr !== null && preg_match('/^(\d+\/\d+)\s*[-,]\s*(.+)$/u', $a, $m)) {
                    $samples[$lastSamplePr]['zoo_no'] = $this->normRow($m[1]);
                    $samples[$lastSamplePr]['description'] = $this->normRow($m[2]);
                    $lastSamplePr = null;
                    continue;
                }
                continue;
            }

            if ($mode === 'results') {
                if ($isFooter($a)) continue;
                if (preg_match('/^PR\s+(\d+)\s*$/u', $a)) {
                    $currentPr = 'PR ' . preg_replace('/\D/', '', $a);
                    continue;
                }
                if ($a === 'Metoda') continue;
                if ($currentPr !== null && $a !== '' && $b !== '') {
                    $results[$currentPr][] = ['method' => $a, 'finding' => $b, 'note' => $c];
                    continue;
                }
                continue;
            }
        }

        $prCodes = array_unique(array_merge(array_keys($samples), array_keys($results)));
        foreach ($prCodes as $pr) {
            $s = $samples[$pr] ?? ['pr' => $pr, 'zoo_no' => null, 'description' => null, 'material' => null, 'count' => null];
            // Bez výsledků nebo bez popisu vzorek nezobrazujeme (nepotřebný řádek).
            $s['results'] = $results[$pr] ?? [];
            if (empty($s['results'])) continue;
            $result['samples'][] = $s;
        }

        return $result;
    }

    private function normRow($s) {
        return trim(preg_replace('/\s+/u', ' ', (string)$s));
    }

    private function parseCzDate($s) {
        if (preg_match('/(\d{1,2})\.\s*(\d{1,2})\.\s*(\d{4})/', (string)$s, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }
        return null;
    }

    private function readXlsxRows($path) {
        if (!class_exists('ZipArchive')) {
            throw new Exception('Na serveru není dostupné rozšíření ZIP (ZipArchive), nelze číst .xlsx.');
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new Exception('Soubor .xlsx nelze otevřít (poškozený ZIP?).');
        }

        $shared = [];
        $ss = $zip->getFromName('xl/sharedStrings.xml');
        if ($ss !== false && $ss !== '') {
            $xml = @simplexml_load_string($ss);
            if ($xml !== false) {
                foreach ($xml->si as $si) {
                    $shared[] = $this->siText($si);
                }
            }
        }

        $sheetName = 'xl/worksheets/sheet1.xml';
        if ($zip->locateName($sheetName) === false) {
            $sheetName = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $n = $zip->getNameIndex($i);
                if (strpos($n, 'xl/worksheets/') === 0 && substr($n, -4) === '.xml') {
                    $sheetName = $n;
                    break;
                }
            }
        }
        if ($sheetName === null) {
            $zip->close();
            throw new Exception('XLSX neobsahuje list s daty.');
        }
        $sheetXml = $zip->getFromName($sheetName);
        $zip->close();

        $xml = @simplexml_load_string($sheetXml);
        if ($xml === false) {
            throw new Exception('Nelze načíst XML listu.');
        }

        $rows = [];
        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $ref = (string)$c['r'];
                $col = $ref !== '' ? $this->colIndex($ref) : count($cells);
                $type = (string)$c['t'];
                $val = null;
                if ($type === 's') {
                    $idx = (int)$c->v;
                    $val = $shared[$idx] ?? '';
                } elseif ($type === 'inlineStr') {
                    $val = isset($c->is->t) ? (string)$c->is->t : '';
                } else {
                    $val = isset($c->v) ? (string)$c->v : null;
                }
                $cells[$col] = $val !== null ? trim($val) : null;
            }
            $rows[] = $cells;
        }
        return $rows;
    }

    private function siText($si) {
        $text = '';
        if (isset($si->t)) $text .= (string)$si->t;
        if (isset($si->r)) {
            foreach ($si->r as $r) $text .= (string)$r->t;
        }
        return $text;
    }

    private function colIndex($ref) {
        if (!preg_match('/^([A-Z]+)/', $ref, $m)) return 0;
        $letters = $m[1];
        $n = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $n = $n * 26 + (ord($letters[$i]) - 64);
        }
        return $n - 1;
    }

    // ---- Pomocné ---------------------------------------------------------

    private function requireEdit($workplaceId) {
        Auth::requireLogin();
        $_SESSION['current_app'] = 'parasitology';
        if (!is_numeric($workplaceId) || !userCan((int)$workplaceId, 'animals', 'edit')) {
            die('Nemáte oprávnění importovat do tohoto pracoviště');
        }
    }

    private function workplace($workplaceId) {
        $workplaceModel = new Workplace();
        $workplace = $workplaceModel->findById($workplaceId);
        if (!$workplace) {
            die('Pracoviště nenalezeno');
        }
        return $workplace;
    }

    private function redirect($path) {
        header('Location: ' . $path);
        exit;
    }
}
