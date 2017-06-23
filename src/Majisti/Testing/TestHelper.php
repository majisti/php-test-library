<?php

namespace Majisti\Testing;

use Majisti\Testing\Database\DatabaseHelper;

class TestHelper
{
    /**
     * @var DatabaseHelper
     */
    private $databaseHelper;

    /**
     * @return DatabaseHelper
     */
    public function getDatabaseHelper()
    {
        return $this->databaseHelper;
    }

    /**
     * @param DatabaseHelper $databaseHelper
     */
    public function setDatabaseHelper($databaseHelper)
    {
        $this->databaseHelper = $databaseHelper;
    }
}
