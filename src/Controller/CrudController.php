<?php
declare(strict_types=1);

namespace CrudConnect\Controller;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Event\EventInterface;
use Cake\Routing\Router;
use Crud\Controller\ControllerTrait;
use CrudConnect\Navigation\FormRedirect;

/**
 * Base controller for CRUD operations using Crud plugin.
 *
 * @property \Crud\Controller\Component\CrudComponent $Crud
 */
class CrudController extends AppController
{
    use ControllerTrait;

    /**
     * @var array<int, string>
     */
    protected array $sortableFields = [];

    protected string $lookupTemplatePath = 'Admin/Lookup';

    protected string $lookupView = 'default';

    protected int $limitPerPage = 10;

    protected int $limitPerLookup = 10;

    /**
     * @var array<int, string>
     */
    protected array $actions = [
        'Crud.Index',
        'Crud.Lookup',
        'Crud.Add',
        'Crud.Edit',
        'Crud.View',
        'Crud.Delete',
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $crudConfigs = [];

    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $crudConfigs = [
            'actions' => $this->actions,
            'listeners' => [
                'Crud.Redirect',
                'Crud.Search',
                ...($this->crudConfigs['listeners'] ?? []),
            ],
        ];
        $this->loadComponent('Crud.Crud', $crudConfigs);

        foreach (['Index', 'Add', 'Edit', 'View', 'Delete'] as $action) {
            $this->Crud->action($action)->setConfig('messages.success.element', 'success');
            $this->Crud->action($action)->setConfig('messages.error.element', 'error');
        }

        $this->Crud->addListener('relatedModels', 'Crud.RelatedModels');

        $request = $this->request;
        $action = $request->getParam('action');
        $sessionKey = 'CrudConnect.returnUrl.' . implode('.', array_map(
            static fn($part) => str_replace('.', '_', (string)$part),
            [$request->getParam('plugin'), $request->getParam('prefix'), $request->getParam('controller')],
        ));
        $this->Crud->on('beforeRender', function (EventInterface $event) use ($request, $action, $sessionKey): void {
            if (in_array($action, ['edit', 'add'], true) && $request->is('get')) {
                $indexUrl = Router::url(['action' => 'index']);
                $referer = FormRedirect::listingReferrer($request->referer(true), $indexUrl);
                if ($referer !== null) {
                    $request->getSession()->write($sessionKey, $referer);
                }
            }
            $this->set('returnUrl', $request->getSession()->read($sessionKey) ?? ['action' => 'index']);
        });

        $this->Crud->on('beforeRedirect', function (EventInterface $event) use ($request, $action, $sessionKey): void {
            $subject = $event->getSubject();
            if (!in_array($action, ['add', 'edit'], true) || empty($subject->success)) {
                return;
            }
            $default = $action === 'edit' ? 'stay' : 'add';
            $mode = Plugin::isLoaded('Uikit')
                ? ($request->getData('_uikit_form_redirect')
                    ?? Configure::read('Uikit.form_' . $action . '_redirect', $default))
                : 'index';
            $id = isset($subject->entity) ? $subject->entity->get('id') : null;
            $returnUrl = $request->getSession()->read($sessionKey) ?? ['action' => 'index'];
            $subject->url = FormRedirect::target($action, $mode, $id, $returnUrl);
        });
    }

    /**
     * @return \Cake\Http\Response|null
     */
    public function lookup()
    {
        $this->Crud->on('beforeLookup', function (EventInterface $event): void {
            $this->paginate['limit'] = $this->limitPerLookup;
        });

        $this->Crud->on('beforeRender', function (EventInterface $event): void {
            /** @var \Crud\Action\LookupAction $crudAction */
            $crudAction = $this->Crud->action();
            $this->viewBuilder()->setTemplatePath($this->lookupTemplatePath);
            $crudAction->viewVar('lookupResults');
            $this->set('lookup_per_page', $this->limitPerLookup);
            $crudAction->view($this->lookupView);
        });

        return $this->Crud->execute();
    }
}
