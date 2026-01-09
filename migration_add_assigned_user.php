<?php
/**
 * Migration: Add assigned_user column to animals table
 *
 * This migration adds a new column 'assigned_user' to the animals table
 * which will store the username of the assigned caretaker (ošetřovatel)
 * from the users table.
 */

require_once __DIR__ . '/app/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    echo "Starting migration: Add assigned_user column to animals table\n";
    echo str_repeat("-", 60) . "\n";

    // Check if column already exists
    $checkStmt = $db->query("SHOW COLUMNS FROM animals LIKE 'assigned_user'");
    $columnExists = $checkStmt->fetch();

    if ($columnExists) {
        echo "Column 'assigned_user' already exists in animals table.\n";
        echo "Migration skipped.\n";
        exit(0);
    }

    // Add the assigned_user column
    $sql = "ALTER TABLE animals
            ADD COLUMN assigned_user VARCHAR(100) NULL AFTER current_enclosure_id,
            ADD INDEX idx_assigned_user (assigned_user)";

    $db->exec($sql);

    echo "✓ Successfully added 'assigned_user' column to animals table\n";
    echo "✓ Column type: VARCHAR(100) NULL\n";
    echo "✓ Index created on assigned_user for better query performance\n";
    echo str_repeat("-", 60) . "\n";
    echo "Migration completed successfully!\n";

} catch (PDOException $e) {
    echo "✗ Migration failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
