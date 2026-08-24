<?php
/**
 * ZooTrack data fixes for the two institutions still missing map coordinates
 * (both were mis-entered):
 *
 *  1. CB-0040 "Sikalu Zoo" (entered as Hungary / Boraceva) is a DUPLICATE of the
 *     correctly-entered B07-0006 "SikaluZOO" (Slovenia / Radenci — the zoo is
 *     really in Slovenia). Merge the Hungarian record into the Slovenian one:
 *     move its holdings (animals) and copy its contact details
 *     (website / email / phone) where the target is empty, then delete the
 *     duplicate and its stale geocache row.
 *
 *  2. CB-0089 "Kontakt-Zoo Doreni" was a guess (Lithuania / Chapyali); it is
 *     actually in Belarus: village Čapjali (Чапялі, ul. Bogdanovka), Salihorsk
 *     (Soligorsk) District, Minsk Region. Fix country/subdivision/city and seed
 *     the verified map coordinates.
 *
 * Idempotent: the merge only runs while the duplicate still exists; the UPDATEs
 * set fixed values; geocache writes use ON DUPLICATE KEY UPDATE.
 */
return function(PDO $pdo) {

    // ── 1. Merge CB-0040 (Hungary duplicate) → B07-0006 (Slovenia) ──────────
    $dupId    = 'CB-0040';
    $targetId = 'B07-0006';

    $dup = $pdo->prepare("SELECT * FROM `zootrack_institutions` WHERE id = ?");
    $dup->execute([$dupId]);
    $dupRow = $dup->fetch(PDO::FETCH_ASSOC);

    $tgt = $pdo->prepare("SELECT * FROM `zootrack_institutions` WHERE id = ?");
    $tgt->execute([$targetId]);
    $tgtRow = $tgt->fetch(PDO::FETCH_ASSOC);

    if ($dupRow && $tgtRow) {
        // Copy contact fields into the target only where the target is empty.
        foreach (['website', 'email', 'phone'] as $f) {
            $tgtVal = trim((string)($tgtRow[$f] ?? ''));
            $dupVal = trim((string)($dupRow[$f] ?? ''));
            if ($tgtVal === '' && $dupVal !== '') {
                $pdo->prepare("UPDATE `zootrack_institutions` SET `$f` = ? WHERE id = ?")
                    ->execute([$dupVal, $targetId]);
            }
        }

        // Move holdings, skipping any species the target already holds
        // (unique key institution_id + species_id).
        $holds = $pdo->prepare("SELECT id, species_id FROM `zootrack_holdings` WHERE institution_id = ?");
        $holds->execute([$dupId]);
        $moveOne = $pdo->prepare("UPDATE `zootrack_holdings` SET institution_id = ? WHERE id = ?");
        $delOne  = $pdo->prepare("DELETE FROM `zootrack_holdings` WHERE id = ?");
        $exists  = $pdo->prepare("SELECT 1 FROM `zootrack_holdings` WHERE institution_id = ? AND species_id = ?");
        foreach ($holds->fetchAll(PDO::FETCH_ASSOC) as $h) {
            $exists->execute([$targetId, $h['species_id']]);
            if ($exists->fetchColumn()) {
                $delOne->execute([$h['id']]);            // target already holds this species
            } else {
                $moveOne->execute([$targetId, $h['id']]);
            }
        }

        // Delete the duplicate institution and its stale geocache entry.
        $pdo->prepare("DELETE FROM `zootrack_institutions` WHERE id = ?")->execute([$dupId]);
        $pdo->prepare("DELETE FROM `zootrack_geocache` WHERE country = ? AND city = ?")
            ->execute([$dupRow['country'], $dupRow['city']]);
    }

    // ── 2. Fix CB-0089 Doreni: Lithuania → Belarus, correct village + coords ─
    $pdo->prepare("
        UPDATE `zootrack_institutions`
        SET country = 'Belarus', subdivision = 'Minsk Region', city = 'Čapjali'
        WHERE id = 'CB-0089'
    ")->execute();

    // Drop the stale guessed Lithuania cache entry, seed the verified one.
    $pdo->prepare("DELETE FROM `zootrack_geocache` WHERE country = 'Lithuania' AND city = 'Chapyali'")->execute();
    $pdo->prepare("
        INSERT INTO `zootrack_geocache` (city, country, lat, lng, not_found)
        VALUES ('Čapjali', 'Belarus', 52.89414, 27.47513, 0)
        ON DUPLICATE KEY UPDATE lat = VALUES(lat), lng = VALUES(lng), not_found = 0
    ")->execute();
};
