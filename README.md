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
retained. Authorization and validation remain on the normal CRUD action.
