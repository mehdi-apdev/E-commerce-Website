<?php
namespace App\Controllers;

use App\Core\BaseController;

/**
 * AdminController handles the redirection to the admin product management.
 */
class AdminController extends BaseController
{
    public function index()
    {
        redirect('adminProduct/index');
    }
}
