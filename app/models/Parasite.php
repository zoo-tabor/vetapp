<?php
require_once __DIR__ . '/../core/Model.php';

class Parasite extends Model {
    
    protected $table = 'parasites';
    
    public function getAllActive() {
        return $this->findAll(['is_active' => 1], 'scientific_name ASC');
    }
    
    public function getByCategory($category = null) {
        if ($category) {
            return $this->findAll(['category' => $category, 'is_active' => 1], 'scientific_name ASC');
        }
        return $this->getAllActive();
    }
    
    public function getCategories() {
        $sql = "
            SELECT DISTINCT category
            FROM parasites
            WHERE is_active = 1 AND category IS NOT NULL
            ORDER BY category ASC
        ";
        return $this->query($sql);
    }
    
    public function search($searchTerm) {
        $sql = "
            SELECT *
            FROM parasites
            WHERE is_active = 1
            AND (
                scientific_name LIKE ?
                OR common_name LIKE ?
                OR category LIKE ?
            )
            ORDER BY scientific_name ASC
        ";
        $term = '%' . $searchTerm . '%';
        return $this->query($sql, [$term, $term, $term]);
    }
}