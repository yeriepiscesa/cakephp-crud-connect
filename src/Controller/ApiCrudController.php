<?php
declare(strict_types=1);

namespace CrudConnect\Controller;

use Cake\View\JsonView;
use Cake\View\XmlView;

/**
 * Base controller for CRUD-backed API endpoints.
 */
class ApiCrudController extends CrudController
{
    /** @var array<string, mixed> */
    protected array $crudConfigs = [
        'listeners' => [
            'Crud.Api',
            'Crud.ApiPagination',
        ],
    ];

    /**
     * @return list<class-string<\Cake\View\View>>
     */
    public function viewClasses(): array
    {
        return [JsonView::class, XmlView::class];
    }
}
