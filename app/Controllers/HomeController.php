<?php
namespace App\Controllers;

class HomeController extends BaseController
{
    public function index(): void
    {
        $isAuthed = !empty($_SESSION['cf_auth']);
        $this->render('home', ['isAuthed' => $isAuthed]);
    }
}
