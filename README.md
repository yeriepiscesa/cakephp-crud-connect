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
