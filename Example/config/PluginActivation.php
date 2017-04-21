<?php

/**
 * Example Activation
 *
 * Activation class for Example plugin.
 * This is optional, and is required only if you want to perform tasks when your plugin is activated/deactivated.
 *
 * @package  Croogo
 * @author   Fahad Ibnay Heylaal <contact@fahad19.com>
 * @license  http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link     http://www.croogo.org
 */
namespace Croogo\Example\Config;

use Cake\ORM\TableRegistry;

class PluginActivation
{

/**
 * onActivate will be called if this returns true
 *
 * @param  object $controller Controller
 * @return boolean
 */
    public function beforeActivation(&$controller)
    {
        return true;
    }

/**
 * Called after activating the plugin in ExtensionsPluginsController::admin_toggle()
 *
 * @param object $controller Controller
 * @return void
 */
    public function onActivation(&$controller)
    {
        // ACL: set ACOs with permissions
        $controller->Croogo->addAco('Croogo\Example/Admin/Example/index'); // ExampleController::admin_index()
        $controller->Croogo->addAco('Croogo\Example/Example/index', ['registered', 'public']); // ExampleController::index()

        $this->Link = TableRegistry::get('Croogo/Menus.Links');

        // Main menu: add an Example link
        $mainMenu = $this->Link->Menus->findByAlias('main')->first();
        $this->Link->addBehavior('Tree', [
            'scope' => [
                'Links.menu_id' => $mainMenu->id,
            ],
        ]);
        $this->Link->save($this->Link->newEntity([
            // Menu in which the link should go
            'menu_id' => $mainMenu->id,
            // Link caption
            'title' => 'Example',
            // The link
            'link' => 'plugin:Croogo%2fExample/controller:Example/action:index',
            // Status : activated or not (0 or 1)
            'status' => 1,
            // Link class
            'class' => 'example',
            // Roles which link is visible. Empty string means visible to all
            'visibility_roles' => '["1","2","3"]',
        ]));
    }

/**
 * onDeactivate will be called if this returns true
 *
 * @param  object $controller Controller
 * @return boolean
 */
    public function beforeDeactivation(&$controller)
    {
        return true;
    }

/**
 * Called after deactivating the plugin in ExtensionsPluginsController::admin_toggle()
 *
 * @param object $controller Controller
 * @return void
 */
    public function onDeactivation(&$controller)
    {
        // ACL: remove ACOs with permissions
        $controller->Croogo->removeAco('Croogo\Example'); // Plugin ACOs and it's actions will be removed

        $this->Link = TableRegistry::get('Croogo/Menus.Links');

        // Main menu: delete Example link
        $link = $this->Link->find()
            ->where([
                'Links.link' => 'plugin:Croogo%2fExample/controller:Example/action:index',
            ])
            ->contain([
                'Menus' => [
                    'conditions' => [
                        'Menus.alias' => 'main',
                    ],
                ],
            ])
            ->first();
        if (empty($link)) {
            return;
        }
        $this->Link->addBehavior('Tree', [
            'scope' => [
                'Link.menu_id' => $link->menu_id,
            ],
        ]);
        if (isset($link->id)) {
            $this->Link->delete($link);
        }
    }
}
