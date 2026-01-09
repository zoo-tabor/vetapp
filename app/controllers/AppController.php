<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';

class AppController {

    public function switchApp($app) {
        Auth::requireLogin();

        // Validate app name
        $validApps = ['animals', 'parasitology', 'biochemistry', 'urineanalysis', 'vaccination', 'warehouse'];
        if (!in_array($app, $validApps)) {
            View::redirect('/');
            return;
        }

        // Set session
        $_SESSION['current_app'] = $app;

        // Redirect to app's home page
        if ($app === 'animals') {
            View::redirect('/animals');
        } elseif ($app === 'warehouse') {
            View::redirect('/warehouse');
        } elseif ($app === 'biochemistry') {
            View::redirect('/biochemistry');
        } elseif ($app === 'urineanalysis') {
            View::redirect('/urineanalysis');
        } elseif ($app === 'vaccination') {
            View::redirect('/vaccination-plan');
        } else {
            View::redirect('/');
        }
    }
}
