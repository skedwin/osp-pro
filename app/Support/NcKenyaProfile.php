<?php

namespace App\Support;

use Illuminate\Support\Arr;

class NcKenyaProfile
{
    /**
     * Normalize the payload returned by NC Kenya endpoints into a consistent shape.
     */
    public static function fromPayload(?array $payload, string $source = 'api'): ?array
    {
        if (!is_array($payload)) {
            return null;
        }

        $status = data_get($payload, 'status');
        $message = data_get($payload, 'message', $payload);

        if (!is_array($message)) {
            return [
                'source' => $source,
                'status' => $status,
                'raw' => $payload,
            ];
        }

        $structured = $message;
        $education = data_get($structured, 'education', []);
        $registration = data_get($structured, 'registration', []);
        $licenses = data_get($structured, 'license', []);
        $cpd = data_get($structured, 'cpd', []);
        $avatar = data_get($structured, 'ProfilePic');

        unset(
            $structured['education'],
            $structured['registration'],
            $structured['license'],
            $structured['cpd'],
            $structured['ProfilePic']
        );

        return [
            'source' => $source,
            'status' => $status,
            'profile' => $structured,
            'education' => $education,
            'registration' => $registration,
            'license' => $licenses,
            'cpd' => $cpd,
            'avatar' => $avatar,
        ];
    }

    /**
     * Build default lookup parameters from a normalized profile payload.
     */
    public static function extractLookup(array $normalized): array
    {
        $profile = data_get($normalized, 'profile', []);
        $licenses = collect(data_get($normalized, 'license', []));
        $registration = collect(data_get($normalized, 'registration', []));

        $defaultLicense = $licenses->first(function ($item) {
            return filled(data_get($item, 'license_no'));
        }) ?? [];

        $defaultRegistration = $registration->first(function ($item) {
            return filled(data_get($item, 'reg_no'));
        }) ?? [];

        return array_filter([
            'id_no' => data_get($profile, 'IdNumber'),
            'index_no' => data_get($profile, 'IndexNo'),
            'licence_no' => data_get($defaultLicense, 'license_no'),
            'reg_no' => data_get($defaultRegistration, 'reg_no'),
            'cadre' => data_get($defaultRegistration, 'cadre'),
        ], fn ($value) => filled($value));
    }
}

