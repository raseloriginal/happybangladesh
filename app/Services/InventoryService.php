<?php

class InventoryService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // TODO: Extract van stock and inventory calculation logic here
}
