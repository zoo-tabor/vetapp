<?php
/**
 * Seed zootrack_geocache with hand-verified coordinates for institution
 * city+country pairs that Nominatim could not resolve (structured NOR free-text),
 * so they finally appear on the map.
 *
 * These 13 cities are villages/hamlets, districts, non-standard transliterations
 * or a typo ("Saint-Laurent die Autels") — resolved manually via targeted
 * Nominatim queries (corrected spelling / native script / parent municipality)
 * and verified against the expected country/region. Coordinates are
 * village-level (good enough for a clustered world map).
 *
 * Idempotent: INSERT ... ON DUPLICATE KEY UPDATE overwrites the stale
 * not_found=1 rows and no-ops if run again. Keyed on the city string exactly as
 * stored on the institution, so the map lookup (country|city) matches.
 *
 * NOT included (no reliable coordinates found — need manual input):
 *   - CB-0040 Sikalu Zoo, city "Boraceva" (Hungary)
 *   - CB-0089 Kontakt-Zoo Doreni, city "Chapyali" (Lithuania)
 */
return function(PDO $pdo) {
    // [city, country, lat, lng]
    $rows = [
        ['Deining-Großalfalterbach', 'Germany',    49.18699, 11.56301],
        ['Santa Maddalena Vallalta', 'Italy',      46.83469, 12.23909],
        ['Rotfelden im Schwarzwald', 'Germany',    48.60641,  8.69832],
        ['Kolobrzeg Zieleniewo',     'Poland',     54.14580, 15.55996],
        ['Stegna/Jantarz',           'Poland',     54.33528, 19.03226],
        ['Karobchytsy',              'Belarus',    53.60611, 23.74991],
        ['Zhyrovitsy',               'Belarus',    53.01386, 25.34456],
        ['Kharoshae',                'Belarus',    54.56386, 27.80073],
        ['Saint-Laurent die Autels', 'France',     47.29032, -1.18835],
        ['Staryy Oskol',             'Russia',     51.29804, 37.83320],
        ['Kirkkolakhti',             'Russia',     61.98948, 30.76819],
        ['Bebyayevo',                'Russia',     55.30807, 43.93757],
        ['Altiaghach',               'Azerbaijan', 40.85805, 48.93485],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO `zootrack_geocache` (city, country, lat, lng, not_found)
        VALUES (:city, :country, :lat, :lng, 0)
        ON DUPLICATE KEY UPDATE lat = VALUES(lat), lng = VALUES(lng), not_found = 0
    ");

    foreach ($rows as [$city, $country, $lat, $lng]) {
        $stmt->execute([
            ':city'    => $city,
            ':country' => $country,
            ':lat'     => $lat,
            ':lng'     => $lng,
        ]);
    }
};
