<?php

namespace Workup\MenuBuilder\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Workup\MenuBuilder\MenuBuilder;
use Workup\MenuBuilder\Http\Traits\MenuHelpers;
use Workup\MenuBuilder\Http\Requests\MenuItemFormRequest;

class ItemController extends Controller
{
    use MenuHelpers;

    /**
     * Creates new MenuItem.
     **/
    public function store(MenuItemFormRequest $request): JsonResponse
    {
        $menuItemModel = MenuBuilder::getMenuItemClass();

        $data = $request->getValues();
        $data['order'] = $menuItemModel::max('id') + 1;

        $model = new $menuItemModel;
        foreach ($data as $key => $value) {
            $model->{$key} = $value;
        }
        $model->save();

        return response()->json(['success' => true], 200);
    }

    /**
     * Returns the menu item as JSON.
     **/
    public function show($menuItemId): JsonResponse
    {
        $menuItem = MenuBuilder::getMenuItemClass()::find($menuItemId);

        return isset($menuItem)
            ? response()->json($menuItem, 200)
            : response()->json(['error' => 'item_not_found'], 400);
    }

    /**
     * Updates a MenuItem.
     **/
    public function update(MenuItemFormRequest $request, $menuItemId): JsonResponse
    {
        $menuItem = MenuBuilder::getMenuItemClass()::find($menuItemId);

        if (! isset($menuItem)) {
            return response()->json(['error' => 'menu_item_not_found'], 400);
        }
        $data = $request->getValues();

        $menuItem->data = [];
        foreach ($data as $key => $value) {
            $menuItem->{$key} = $value;
        }

        $menuItem->save();
        return response()->json(['success' => true], 200);
    }

    /**
     * Deletes a MenuItem.
     **/
    public function destroy($menuItemId): JsonResponse
    {
        $menuItem = MenuBuilder::getMenuItemClass()::findOrFail($menuItemId);
        $menuItem->children()->delete();
        $menuItem->delete();
        return response()->json(['success' => true], 200);
    }

    /**
     * Get link types for locale.
     **/
    public function getMenuItemTypes(Request $request, $menuId): JsonResponse
    {
        $menu = MenuBuilder::getMenuClass()::find($menuId);
        if ($menu === null) {
            return response()->json(['error' => 'menu_not_found'], 404);
        }

        $locale = $request->get('locale');
        if ($locale === null) {
            return response()->json(['error' => 'locale_required'], 400);
        }

        $menuItemTypes = [];
        $menuItemTypesRaw = MenuBuilder::getMenuItemTypes();

        $formatAndAppendMenuItemType = function ($typeClass) use (&$menuItemTypes, $locale) {
            if (! class_exists($typeClass)) {
                return;
            }

            $data = [
                'name' => $typeClass::getName(),
                'type' => $typeClass::getType(),
                'fields' => MenuBuilder::getFieldsFromMenuItemTypeClass($typeClass) ?? [],
                'class' => $typeClass,
            ];

            if (method_exists($typeClass, 'getOptions')) {
                $options = $typeClass::getOptions($locale) ?? [];
                $data['options'] = array_map(function ($value, $key) {
                    return ['id' => (string) $key, 'label' => $value];
                }, array_values($options), array_keys($options));
            }

            $menuItemTypes[] = $data;
        };

        foreach ($menuItemTypesRaw as $typeClass) {
            $formatAndAppendMenuItemType($typeClass);
        }

        return response()->json($menuItemTypes, 200);
    }
}
