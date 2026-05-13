<?php
return function(PDO $pdo) {
    // Wipe existing warehouse data for workplace_id = 1
    $pdo->exec("DELETE wm FROM warehouse_movements wm INNER JOIN warehouse_items wi ON wm.item_id = wi.id WHERE wi.workplace_id = 1");
    $pdo->exec("DELETE wb FROM warehouse_batches wb INNER JOIN warehouse_items wi ON wb.item_id = wi.id WHERE wi.workplace_id = 1");
    $pdo->exec("DELETE wc FROM warehouse_consumption wc INNER JOIN warehouse_items wi ON wc.item_id = wi.id WHERE wi.workplace_id = 1");
    $pdo->exec("DELETE FROM warehouse_items WHERE workplace_id = 1");

    // name | category | unit | current_stock | note | weekly_consumption | batches[[qty, expiry_date]]
    $items = [
        ['hoveží', 'food', 'kg', 1123.0, null, 124.0, [[330.0, '2026-09-01'],[269.0, '2026-10-01'],[524.0, '2026-11-01'],]],
        ['hoveží srdce', 'food', '', 312.0, null, null, [[173.0, '2026-09-01'],[139.0, '2026-10-01'],]],
        ['vepřové', 'food', 'kg', 108.0, null, 43.0, [[108.0, '2026-10-01'],]],
        ['krůtí srdíčka', 'food', 'kg', 4.0, null, 1.0, [[4.0, '2026-05-01'],]],
        ['jehněčí srdce', 'food', 'kg', 0.0, null, null, []],
        ['králíci', 'food', 'kg', 145.0, null, 40.0, [[145.0, '2026-09-01'],]],
        ['kuřata', 'food', 'kg', 237.0, null, 55.0, [[237.0, '2026-10-01'],]],
        ['myši', 'food', 'ks', 250.0, null, 247.0, [[250.0, '2026-09-01'],]],
        ['křečci', 'food', 'krabice', 1.6, null, 0.3, [[1.6, '2026-08-01'],]],
        ['potkani', 'food', 'ks', 69.0, null, 12.0, [[69.0, '2026-09-01'],]],
        ['kuřátka', 'food', 'krabice', 2.0, null, 1.2, [[2.0, '2026-09-01'],]],
        ['granule pro EMU (Wildlife/Energys/Versele Laga)', 'food', 'pytel', 8.5, null, 1.5, [[8.5, '2027-02-01'],]],
        ['granule pro Klokany', 'food', 'pytel', 2.5, null, 0.5, [[2.5, '2026-09-01'],]],
        ['granule pro PRIMÁTY', 'food', 'pytel', 0.2, null, 0.1, [[0.2, '2026-11-01'],]],
        ['granulát VERSELE LAGA CAVIA (morče)', 'food', 'pytel', 3.5, null, 0.5, [[1.5, '2027-01-01'],[2.0, '2027-02-01'],]],
        ['granulát VERSELE NATURE CUNI (mara)', 'food', 'pytel', 3.0, null, 0.5, [[2.0, '2027-01-01'],[1.0, '2027-02-01'],]],
        ['granulát VERSELE LAGA (Duck 3)', 'food', 'pytel', 6.0, null, 1.0, [[4.0, '2026-12-01'],[2.0, '2027-01-01'],]],
        ['granulát VERSELE LAGA (papoušci)', 'food', 'pytel', 0.0, null, 0.5, []],
        ['granulát PREMIN JELEN', 'food', 'pytel', 116.0, null, 14.0, [[116.0, '2026-08-01'],]],
        ['granulát PREMIN VELBLOUD', 'food', 'pytel', 14.0, null, 1.0, [[1.0, '2026-06-01'],[4.0, '2026-07-01'],[9.0, '2026-08-01'],]],
        ['Nutrimix pro prasata', 'food', 'pytel', 4.0, null, 1.25, [[4.0, '2026-11-01'],]],
        ['granulát Tr A', 'food', 'pytel', 7.0, null, 3.0, [[7.0, '2026-09-01'],]],
        ['granulát Tr B', 'food', 'pytel', 6.5, null, 2.5, [[6.5, '2026-08-01'],]],
        ['granulát Tr C', 'food', 'pytel', 6.0, null, 2.0, [[6.0, '2026-07-01'],]],
        ['vojtěškový granulát (vojtěškové úsušky melasované)', 'food', 'pytel', 5.0, null, 1.5, [[5.0, '2026-09-01'],]],
        ['liz Solsel Extra (s mědí)', 'food', 'kostky 10kg', 4.0, null, null, [[4.0, '2027-09-01'],]],
        ['liz Solsel Universal (bez mědi)', 'food', 'kostky 5kg', 1.0, null, null, [[1.0, '2026-12-01'],]],
        ['liz Solsel Universal (bez mědi)', 'food', 'kostky 10kg', 4.0, null, null, [[4.0, '2028-05-01'],]],
        ['liz Solsel Natural (NaCl)', 'food', 'kostky 10kg', 0.0, null, null, []],
        ['liz Solsel Natural (NaCl)', 'food', 'kostky 5kg', 10.0, null, null, [[10.0, '2027-10-01'],]],
        ['Lama bloc', 'food', '', 0.0, null, null, []],
        ['plotice (plevelné ryby)', 'food', 'kg', 0.0, null, null, []],
        ['korušky (gavún)', 'food', 'bedna (10kg/bedna)', 2.5, null, 0.75, [[2.5, '2026-09-01'],]],
        ['granulát VERSELE LAGA flamingo', 'food', 'pytel', 3.5, null, 0.7, [[0.5, '2026-12-01'],[3.0, '2027-01-01'],]],
        ['granule jeřáb', 'food', 'pytel', 1.5, null, 0.75, [[1.5, '2026-07-01'],]],
        ['konzervy pro kočky – senior', 'food', '', 0.0, null, null, []],
        ['kapsičky Royal Canin Renal', 'food', 'g', 0.0, null, null, []],
        ['Kapsičky Hills Kidney Care', 'food', 'g', 0.0, null, null, []],
        ['konzervy pro kočky', 'food', 'kg', 29.0, null, 3.0, [[12.5, '2027-09-01'],[16.8, '2027-12-01'],]],
        ['Beaphar Renal', 'food', 'kg', 0.0, null, null, []],
        ['konzervy pro kotě', 'food', 'kg', 0.0, null, null, []],
        ['granule pro kočky', 'food', 'pytel', 2.5, null, 0.25, [[0.5, '2027-05-01'],[2.0, '2027-05-01'],]],
        ['granule Wolf of Wilderness', 'food', 'pytel (12kg)', 3.4, null, 0.6, [[3.4, '2027-04-01'],]],
        ['lososový olej', 'food', 'l', 3.0, null, null, [[3.0, '2026-05-01'],]],
        ['Roboran H', 'food', 'balení (250g)', 11.0, null, 1.0, [[11.0, '2027-03-01'],]],
        ['Roboran C 25%', 'food', 'balení (250g)', 800.0, null, 70.0, [[300.0, '2027-04-01'],[500.0, '2027-06-01'],]],
        ['Roboran pro plazy', 'food', 'balení (1000g)', 3000.0, null, 280.0, [[3000.0, '2027-03-01'],]],
        ['ječmen', 'food', 'kg (odhad)', 110.0, null, 20.0, []],
        ['pšenice', 'food', 'kg (odhad)', 260.0, null, 40.0, []],
        ['slunečnice', 'food', 'kg', 22.0, null, 3.0, []],
        ['kukuřice', 'food', 'kg', 43.0, null, 1.0, []],
        ['ovesné vločky', 'food', 'kg', 85.0, null, 15.0, []],
        ['med lesní', 'food', 'kg', 1.1, null, 0.35, []],
        ['piškoty', 'food', 'kg', 4.9, null, 1.3, []],
        ['vejce', 'food', 'plato', 35.0, null, 9.0, []],
        ['z. morio (potemník brazilský)', 'food', 'l', 2.5, null, 1.5, []],
        ['t. molitor (potemník moučný)', 'food', 'l', 2.5, null, 1.5, []],
        ['cvrčci', 'food', 'l', 2.5, null, 1.5, []],
        ['sarančata', 'food', 'ks', 75.0, null, 50.0, []],
        ['sýry', 'food', 'kg', 45.0, null, null, []],
        ['okus', 'food', 'větve', 20.0, null, null, []],
        ['ořechy vlašské', 'food', 'kg', 11.5, null, 0.5, []],
        ['buráky', 'food', 'kg', 10.5, null, 0.75, []],
        ['štěpka', 'food', 'l', 50.0, null, null, []],
        ['seno', 'food', 'balík', 53.0, null, 4.0, []],
        ['sláma', 'food', 'balík', 23.0, null, 3.0, []],
        ['granule pro návštěvníky', 'food', 'pytel (25kg)', 0.0, null, null, []],
        ['Gelacan Fast', 'food', 'balení (500g)', 1700.0, null, null, [[1700.0, '2028-01-01'],]],
        ['Pšeničné klíčky', 'food', 'kg (pytel 50kg)', 33.0, null, 2.5, []],
        ['krmení pro ryby – dnové', 'food', 'kg', 11.8, null, null, []],
        ['krmení pro ryby – plovoucí', 'food', 'kg', 57.0, null, 1.2, []],
        ['Optimin pro drůbež', 'food', 'l', 13.0, null, 1.1, [[13.0, '2031-02-01'],]],
        ['Sušené mléko Nutrimix/Ovismilk', 'food', 'kg', 0.0, null, null, []],
        ['VERSELE LAGA – Australian Parrot', 'food', 'pytel', 2.6, null, 0.1, [[0.6, '2026-05-01'],[2.0, '2027-04-01'],]],
        ['VERSELE LAGA – Ara Parrot', 'food', 'pytel', 4.8, null, 0.2, [[4.8, '2027-05-01'],]],
        ['P15 – Tropical', 'food', 'pytel', 0.55, null, 0.07, [[0.55, '2026-07-01'],]],
        ['Insect blocker', 'food', '', 1.8, null, null, []],
        ['Geloren Dog L-XL', 'food', 'balení po 60ks', 94.0, null, 28.0, [[94.0, '2026-10-01'],]],
        ['mrkev', 'food', 'kg', 620.0, '26 beden', 1100.0, []],
        ['jablka', 'food', 'kg', 300.0, '39 beden', 600.0, []],
        ['Oves', 'food', 'kg', 60.0, null, 10.0, []],
        ['Aptus Sentrx Vet eye gel 3 ml', 'medicament', 'bal', 0.0, null, null, []],
        ['B-Komplex 100 ml', 'medicament', 'bal', 0.0, null, null, []],
        ['Dehinel pro kočky 230/20 mg 1tbl', 'medicament', 'bal', 1.0, null, null, [[1.0, '2028-01-01'],]],
        ['Dehinel Plus XL pro psy 175/504/525 mg 1tbl', 'medicament', 'bal', 3.5, null, null, [[3.5, '2027-04-01'],]],
        ['Dehinel Plus flavour pro psy 1tbl', 'medicament', '', 19.0, null, null, [[9.0, '2027-02-01'],[10.0, '2027-04-01'],]],
        ['Distocur 34 mg/ml 1 l', 'medicament', 'bal', 0.0, null, null, []],
        ['Drontal Dog Flavour 15/144/50 mg 1tbl', 'medicament', 'bal', 0.0, null, null, []],
        ['Ecomectin 18,7 mg/g', 'medicament', '', 18.75, null, null, [[18.75, '2028-10-01'],]],
        ['Enrogal 50 mg/ml 100 ml', 'medicament', 'bal', 0.0, null, null, []],
        ['Flogocid vet mast 20 g', 'medicament', 'bal', 0.0, null, null, []],
        ['Framykoin mast 10 g', 'medicament', 'bal', 0.0, null, null, []],
        ['Heřmánková mast 50 ml', 'medicament', 'bal', 0.0, null, null, []],
        ['Maxitrol oční kapky 5 ml', 'medicament', 'bal', 0.0, null, null, []],
        ['Meloxidolor 5 mg/ml 20 ml', 'medicament', 'bal', 0.0, null, null, []],
        ['Meloxidyl 1,5 mg/ml 100 ml', 'medicament', 'bal', 0.0, null, null, []],
        ['Milprazon chewable 16 mg/40 mg 1tbl', 'medicament', 'bal', 3.0, null, null, [[3.0, '2027-07-01'],]],
        ['Oftaquix 5mg/ml 5 ml', 'medicament', 'bal', 0.0, null, null, []],
        ['Opthalmo-Framykoin 5 g', 'medicament', 'bal', 0.0, null, null, []],
        ['Opthalmo-Framykoin comp. 5 g', 'medicament', 'bal', 0.0, null, null, []],
        ['Noromectin 140mg p.o. Pasta', 'medicament', '', 0.0, null, null, []],
        ['Panacur 187,5mg/g 24g', 'medicament', 'bal', 5.0, null, null, [[5.0, '2026-10-01'],]],
        ['Stomodine 30 ml', 'medicament', 'bal', 0.0, null, null, []],
        ['Tobrex 3mg/ml oční mast 3,5 g', 'medicament', 'bal', 0.0, null, null, []],
        ['Vidisic 2 mg/g oční gel 10 g', 'medicament', 'bal', 0.0, null, null, []],
        ['Mielosan mast', 'medicament', '', 0.0, null, null, []],
        ['Mirataz 20mg/g', 'medicament', '', 0.0, null, null, []],
        ['Nobivac DHP', 'medicament', '', 0.0, null, null, []],
        ['RCPCh', 'medicament', '', 0.0, null, null, []],
        ['Nobivac L4', 'medicament', '', 0.0, null, null, []],
    ];

    $stmtItem = $pdo->prepare(
        'INSERT INTO warehouse_items (workplace_id, name, category, unit, current_stock, notes, created_by) VALUES (1, ?, ?, ?, ?, ?, 1)'
    );
    $stmtBatch = $pdo->prepare(
        "INSERT INTO warehouse_batches (item_id, quantity, expiration_date, received_date, created_by) VALUES (?, ?, ?, '2026-05-13', 1)"
    );
    $stmtMove = $pdo->prepare(
        "INSERT INTO warehouse_movements (item_id, movement_type, quantity, movement_date, notes, created_by) VALUES (?, 'adjustment', ?, '2026-05-13', 'Import počátečního stavu skladu', 1)"
    );
    $stmtConsume = $pdo->prepare(
        'INSERT INTO warehouse_consumption (item_id, weekly_consumption, desired_weeks_stock, created_by) VALUES (?, ?, 8, 1)'
    );

    foreach ($items as [$name, $cat, $unit, $stock, $note, $weekly, $batches]) {
        $stmtItem->execute([$name, $cat, $unit, $stock, $note]);
        $id = (int) $pdo->lastInsertId();

        if ($stock > 0) {
            $stmtMove->execute([$id, $stock]);
        }

        foreach ($batches as [$bqty, $bdate]) {
            $stmtBatch->execute([$id, $bqty, $bdate]);
        }

        if ($weekly !== null) {
            $stmtConsume->execute([$id, $weekly]);
        }
    }
};
