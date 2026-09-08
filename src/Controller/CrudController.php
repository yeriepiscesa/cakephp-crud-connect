<?php
declare(strict_types=1);

namespace CrudConnect\Controller;

use Cake\Event\EventInterface;

/**
 * Base controller for CRUD operations using Crud plugin.
 *
 * @property \Crud\Controller\Component\CrudComponent $Crud
 */
class CrudController extends AppController
{
    use \Crud\Controller\ControllerTrait;

    /** @var array<int, string> */
    protected array $sortableFields = [];

    protected string $lookupTemplatePath = 'Admin/Lookup';

    protected string $lookupView = 'default';

    protected int $limitPerPage = 10;

    protected int $limitPerLookup = 10;

    /** @var array<int, string> */
    protected array $actions = [
        'Crud.Index',
        'Crud.Lookup',
        'Crud.Add',
        'Crud.Edit',
        'Crud.View',
        'Crud.Delete',
    ];

    /** @var array<string, mixed> */
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
        $controllerName = $request->getParam('controller');

        $this->Crud->on('beforePaginate', function (EventInterface $event): void {
            $paginate = [
                'limit' => $this->limitPerPage,
            ];
            if (!empty($this->sortableFields)) {
                $paginate['sortableFields'] = $this->sortableFields;
            }
            $this->paginate = $paginate;
        });

        $this->Crud->on('beforeRender', function (EventInterface $event) use ($request, $action, $controllerName): void {
            if (in_array($action, ['edit', 'add'], true)) {
                $referer = $request->referer(true);
                if ($referer && !str_contains($referer, '/edit') && !str_contains($referer, '/add')) {
                    $request->getSession()->write($controllerName . '.returnUrl', $referer);
                }
            }
            $this->set('returnUrl', $request->getSession()->read($controllerName . '.returnUrl') ?? ['prefix' => 'Admin', 'action' => 'index']);
        });

        $this->Crud->on('beforeRedirect', function (EventInterface $event) use ($request, $action, $controllerName): void {
            $subject = $event->getSubject();

            if (in_array($action, ['add', 'edit'], true)) {
                $returnUrl = $request->getSession()->read($controllerName . '.returnUrl');
                if ($returnUrl) {
                    $subject->url = $returnUrl;
                    $request->getSession()->delete($controllerName . '.returnUrl');
                }
            }
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
