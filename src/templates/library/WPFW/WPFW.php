<?php
namespace WPFW;

class WPFW
{
    const ENABLE_ADMIN_FUNCTIONS = true;
    const ENABLE_CLEANUP_FUNCTIONS = true;

    protected $admin;
    protected $cleanup;

    public function __construct()
    {
        $this->requireFiles();
    }

    public function requireFiles()
    {
        if (self::ENABLE_ADMIN_FUNCTIONS) {
            require(WPFW_DIR . "/Admin.php");
            $this->admin = new Admin();
        }

        if (self::ENABLE_CLEANUP_FUNCTIONS) {
            require(WPFW_DIR . "/Cleanup.php");
            $this->cleanup = new Cleanup();
        }
    }
}
