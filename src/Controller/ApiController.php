<?php
declare(strict_types=1);

namespace CrudConnect\Controller;

use Cake\Controller\Controller;
use Cake\Event\EventInterface;
use Cake\View\JsonView;

/**
 * Base controller for REST API endpoints with JSON envelope helpers.
 */
class ApiController extends Controller
{
    /**
     * @return list<class-string<\Cake\View\View>>
     */
    public function viewClasses(): array
    {
        return [JsonView::class];
    }

    /**
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        $this->viewBuilder()->setClassName('Json');
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function sendJson(array $payload, int $status = 200): void
    {
        $this->response = $this->response->withStatus($status);

        $vars = array_merge(['status' => 'success'], $payload);
        $this->set($vars);
        $this->viewBuilder()->setOption('serialize', array_keys($vars));
    }

    /**
     * @param array<string, mixed> $extra
     */
    protected function sendError(int $status, string $message, array $extra = []): void
    {
        $this->response = $this->response->withStatus($status);

        $vars = array_merge(['status' => 'error', 'message' => $message], $extra);
        $this->set($vars);
        $this->viewBuilder()->setOption('serialize', array_keys($vars));
    }
}
