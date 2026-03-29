<?php

namespace App\Controllers;

class PageController extends BaseController {

    public function professor() {
        $this->view('pages/professor');
    }

    public function localization() {
        $this->view('pages/localization');
    }

    public function about() {
        $this->view('pages/about');
    }
}
