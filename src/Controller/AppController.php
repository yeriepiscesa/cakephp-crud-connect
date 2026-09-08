<?php
declare(strict_types=1);

namespace CrudConnect\Controller;

use Cake\Controller\Controller;

/**
 * Minimal controller base for CrudConnect CRUD controllers.
 */
class AppController extends Controller
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');

        $this->set([
            'devScripts' => [],
            'prodScripts' => [],
        ]);
    }
}
