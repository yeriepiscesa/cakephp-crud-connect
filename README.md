# CrudConnect Plugin

Shared base controllers for CakePHP applications using [FriendsOfCake/Crud](https://github.com/FriendsOfCake/crud) and [FriendsOfCake/Search](https://github.com/FriendsOfCake/search).

## Controllers

| Class | Purpose |
|---|---|
| `CrudConnect\Controller\CrudController` | Admin CRUD (index/add/edit/view/delete/lookup) |
| `CrudConnect\Controller\ApiController` | JSON API with `sendJson()` / `sendError()` helpers |
| `CrudConnect\Controller\ApiCrudController` | CRUD actions exposed as JSON/XML API |

## Usage in a domain plugin

```php
namespace MyPlugin\Controller;

use CrudConnect\Controller\CrudController as BaseCrudController;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

class CrudController extends BaseCrudController
{
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        $theme = (string)Configure::read('MyPlugin.theme', 'Uikit');
        $this->viewBuilder()->setTheme($theme);
    }
}
```

## Installation

```bash
composer require yeriepiscesa/cakephp-crud-connect
```

Load in `config/plugins.php`:

```php
'CrudConnect' => [],
'Crud' => [],
'Search' => [],
```

## Form redirects

When Uikit is loaded, the shared CRUD add/edit success redirects read
`Uikit.form_add_redirect` (`add`, `edit`, `index`) and
`Uikit.form_edit_redirect` (`stay`, `index`). Form templates may submit the
protected `_uikit_form_redirect` field via Uikit's `crud_buttons` element.
Unsupported values fall back to the default. The list return URL is stored per
plugin, prefix and controller, and only accepts a same-origin referrer pointing
to that controller's index. Search, filter and pagination query parameters are
retained. View pages also remember a listing referrer, and Uikit's view actions
use it for the back-to-list link. Authorization and validation remain on the
normal CRUD action.

## Listing search and filters

`CrudConnect\Controller\CrudController` enables the `Crud.Search` listener for
index and lookup actions. Add `Search.Search` to the table and register the
query parameters in its search manager. The listener passes the GET query to
FriendsOfCake Search; merely adding a form control does not filter records.
For example:

```php
$this->addBehavior('Search.Search');
$this->getBehavior('Search')->searchManager()
    ->add('search', 'Search.Like', [
        'fields' => ['name'], 'before' => true, 'after' => true,
    ])
    ->add('category_id', 'Search.Value', [
        'fields' => 'category_id', 'filterEmpty' => true,
    ]);
```

For filters on associated tables, use a `Search.Callback` that adds the
necessary join and condition and returns `true`. Keep authorization and tenant
scoping independent of optional search filters. For an example combining a
state and its country, see `BusinessUsers\Model\Table\CitiesTable`.

When using the Uikit theme, pass additional filter definitions to the shared
listing element. Keys are query parameter names and values are CakePHP Form
control options:

```php
echo $this->element('Uikit.table_controls', ['filters' => [
    'category_id' => [
        'type' => 'select', 'label' => __('Category'),
        'options' => $categories, 'empty' => __('All categories'),
        'class' => 'uk-select',
    ],
]]);
```

The main search and page-size controls remain visible. When at least one filter
is configured, the filter button opens a drawer containing the additional
filters and another search field. Applying the drawer resets pagination and retains other
query parameters; clearing it removes search and configured filters. Without
additional filters, the button and drawer are omitted. Existing query values
populate the form. A remote lookup select can supply `data-lookup-url`,
`data-label-fields`, and `data-search-field`; add `data-clearable` to allow
clearing a selection, load Uikit's
`admin/lookup-select.js` entry and provide the selected option in `options`
so it remains visible on reload. See the BusinessUsers states and cities
listings for complete examples. Search and lookup routes still require their
normal authorization.

For a multi-select, set `'multiple' => true` and provide all selected options
and values. Enable `'multiValue' => true` on `Search.Value` so GET arrays are
matched with `IN`; association callbacks must handle both arrays and scalar
values if existing links pass a single ID. The lookup entry requests matching
options from the server and allows individual selections to be removed. The
filter icon displays a red indicator when search or an additional filter has a
non-empty value.
