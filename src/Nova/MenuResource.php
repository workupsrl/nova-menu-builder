<?php

namespace Workup\MenuBuilder\Nova;

use Laravel\Nova\Panel;
use Laravel\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Select;
use Workup\MenuBuilder\MenuBuilder;
use Workup\MenuBuilder\Nova\Fields\MenuBuilderField;

class MenuResource extends Resource
{
    public static $model = Workup\Menus\Models\Menu::class;
    public static $search = ['name', 'slug'];
    public static $displayInNavigation = false;

    public function __construct($resource)
    {
        $this->resource = $resource;
        static::$model = MenuBuilder::getMenuClass();

        parent::__construct($resource);
    }

    public static function label(): string
    {
        return __('novaMenuBuilder.menuResourceLabel');
    }

    public static function singularLabel(): string
    {
        return __('novaMenuBuilder.menuResourceSingularLabel');
    }

    public static function uriKey(): string
    {
        return 'nova-menus';
    }

    public function title(): string
    {
        return $this->name . ' (' . $this->slug . ')';
    }

    public function fields(Request $request): array
    {
        $menusTableName = MenuBuilder::getMenusTableName();
        $menuOptions = collect(MenuBuilder::getMenus())
            ->mapWithKeys(function ($menu, $key) {
                return [$key => $menu['name']];
            })
            ->toArray();

        $maxDepth = 10;
        if ($this->slug) $maxDepth = MenuBuilder::getMenuConfig($this->slug)['max_depth'] ?? 10;

        return [
            Text::make(__('novaMenuBuilder.nameFieldName'), 'name')
                ->sortable()
                ->rules('required', 'max:255'),

            Select::make(__('novaMenuBuilder.menuResourceSingularLabel'), 'slug')
                ->options($menuOptions)
                ->onlyOnForms()
                ->creationRules('required', 'max:255', "unique_menu:$menusTableName,slug,NULL,id")
                ->updateRules('required', 'max:255', "unique_menu:$menusTableName,slug,{{resourceId}},id"),

            Text::make(__('novaMenuBuilder.menuResourceSingularLabel'), 'slug', function ($key) {
                $menu = MenuBuilder::getMenus()[$key] ?? null;
                if (!$menu) return "<s>{$key}</s>";
                return "<span class='whitespace-no-wrap'><b>{$menu['name']}</b> <i>({$key})</i></span>";
            })
                ->hideWhenCreating()
                ->hideWhenUpdating()
                ->asHtml(),

            Panel::make(__('novaMenuBuilder.menuItemsPanelName'), [
                MenuBuilderField::make('', 'menu_items')
                    ->hideWhenCreating()
                    ->maxDepth($maxDepth)
                    ->readonly(),
            ])
        ];
    }
}
