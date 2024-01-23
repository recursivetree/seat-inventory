<?php

namespace RecursiveTree\Seat\Inventory\Helpers;

class LocaleHelper
{
    public static function getWorkspaceMessages(): array
    {
        $commonMessageKeys = [
            'success_label',
            'error_label',
        ];

        $workspaceMessageKeys = [
            'error_load_workspace',
            'select_workspace_title',
            'empty_workspace_message',
            'create_new_workspace_hint',
            'create_workspace_dialog_title',
            'workspace_name_field',
            'workspace_name_placeholder',
            'workspace_creation_success',
            'workspace_creation_error',
            'workspace_create_btn'
        ];

        $result = [];

        foreach ($commonMessageKeys as $key) {
            $result[$key] = self::convertLocalisationMessage('common', $key);
        }

        foreach ($workspaceMessageKeys as $key) {
            $result[$key] = self::convertLocalisationMessage('workspace', $key);
        }

        return $result;
    }

    public static function convertLocalisationMessage($namespace, $key): string
    {
        return html_entity_decode(trans('inventory::' . $namespace . '.' . $key), ENT_QUOTES);
    }
}