<?php

namespace Aspectox\LaraJoom\Plugins\User\Profile\Extension;

use Illuminate\Support\Facades\DB;

class Profile
{
    public function getName(): string
    {
        return 'user.profile';
    }

    /**
     * Campos de registro (register-require_*) y perfil (profile-require_*).
     * Valores: 2 = requerido, 1 = opcional, 0 = deshabilitado.
     * Incluye "avatar" (agregado).
     */
    public function getFields(): array
    {
        $fields = [
            'address1', 'address2', 'city', 'region', 'country',
            'postal_code', 'phone', 'website', 'favoritebook',
            'aboutme', 'dob', 'tos', 'avatar',
        ];

        $result = [];
        foreach ($fields as $field) {
            $result['register-require_' . $field] = in_array($field, ['city', 'country', 'tos']) ? 1 : 1;
            $result['profile-require_' . $field]  = 1;
        }
        $result['register-require_tos'] = 0;

        return $result;
    }

    /**
     * Campos del formulario (de forms/profile.xml) + avatar.
     */
    public function getFormFields(): array
    {
        return [
            'address1', 'address2', 'city', 'region', 'country',
            'postal_code', 'phone', 'website', 'favoritebook',
            'aboutme', 'dob', 'tos', 'avatar',
        ];
    }
}
