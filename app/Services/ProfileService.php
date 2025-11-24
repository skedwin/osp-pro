<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProfileService
{
    /**
     * Get formatted profile data
     */
    public function getFormattedProfile(User $user): array
    {
        return Cache::remember("user_profile_{$user->id}", 3600, function () use ($user) {
            try {
                $profileData = $this->fetchProfileFromAPI($user);
                $rawCpd = $profileData['cpd'] ?? [];

                return [
                    'profile' => $this->formatProfileSummary($profileData['profile'] ?? []),
                    'education' => $profileData['education'] ?? [],
                    'registration' => $profileData['registration'] ?? [],
                    'license' => $profileData['license'] ?? [],
                    // Normalize CPD so views can iterate items reliably while also
                    // exposing a canonical summary for controllers to consume.
                    'cpd' => $this->normalizeCpd($rawCpd),
                    'cpd_summary' => $this->extractCpdSummary($rawCpd),
                    'avatar' => $this->getAvatarUrl($profileData['profile'] ?? [], $user),
                ];
            } catch (\Exception $e) {
                Log::error('ProfileService - Error formatting profile: ' . $e->getMessage());
                return $this->getEmptyProfileStructure();
            }
        });
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): bool
    {
        try {
            $updated = $this->updateProfileViaAPI($user, $data);
            
            if ($updated) {
                // Clear the cache
                Cache::forget("user_profile_{$user->id}");
                
                // Also update local user record if needed
                $this->updateLocalUser($user, $data);
                
                Log::info('ProfileService - Profile updated successfully', [
                    'user_id' => $user->id,
                    'updated_fields' => array_keys($data)
                ]);
            }
            
            return $updated;
            
        } catch (\Exception $e) {
            Log::error('ProfileService - Error updating profile: ' . $e->getMessage(), [
                'user_id' => $user->id
            ]);
            return false;
        }
    }

    /**
     * Format profile summary data
     */
    private function formatProfileSummary(array $profile): array
    {
        // Ensure all expected keys exist
        $defaultProfile = [
            'Name' => '',
            'Email' => '',
            'MobileNo' => '',
            'IdNumber' => '',
            'IndexNo' => '',
            'DateOfBirth' => '',
            'Gender' => '',
            'Address' => '',
            'PassportNumber' => '',
            'BirthCertNo' => '',
        ];

        return array_merge($defaultProfile, $profile);
    }

    /**
     * Get avatar URL
     */
    private function getAvatarUrl(array $profile, User $user): ?string
    {
        return $profile['profile_pic'] ?? $profile['avatar'] ?? $user->avatar_url ?? null;
    }

    /**
     * Fetch profile from external API
     */
    private function fetchProfileFromAPI(User $user): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $user->api_token,
                'Accept' => 'application/json',
            ])
            ->timeout(30)
            ->retry(2, 100)
            ->get(config('services.api.url') . '/practitioner/profile');

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('ProfileService - API response not successful', [
                'status' => $response->status()
            ]);
            
        } catch (\Exception $e) {
            Log::error('ProfileService - API call failed: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Update profile via external API
     */
    private function updateProfileViaAPI(User $user, array $data): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $user->api_token,
                'Accept' => 'application/json',
            ])
            ->timeout(30)
            ->post(config('services.api.url') . '/practitioner/profile/update', $data);

            return $response->successful();
            
        } catch (\Exception $e) {
            Log::error('ProfileService - Update API call failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update local user record with profile changes
     */
    private function updateLocalUser(User $user, array $data): void
    {
        $updatableFields = ['email', 'first_name', 'last_name'];
        $updateData = array_intersect_key($data, array_flip($updatableFields));
        
        if (!empty($updateData)) {
            $user->update($updateData);
        }
    }

    /**
     * Get empty profile structure
     */
    private function getEmptyProfileStructure(): array
    {
        return [
            'profile' => [],
            'education' => [],
            'registration' => [],
            'license' => [],
            'cpd' => [],
            'cpd_summary' => [],
            'avatar' => null,
        ];
    }

    /**
     * Normalize incoming CPD payload into an iterable array for views.
     * If the API returns a summary object, wrap it into a single-element array.
     * If the API already returned an array of items (activities or array-wrapped
     * summary), return it unchanged.
     */
    private function normalizeCpd(mixed $raw): array
    {
        if (empty($raw)) return [];

        // If associative summary (has current_points) -> wrap it
        if (is_array($raw) && array_key_exists('current_points', $raw)) {
            return [$raw];
        }

        // If it's a numeric-indexed array, return as-is
        if (is_array($raw) && array_values($raw) === $raw) {
            return $raw;
        }

        // Fallback: cast to array
        return (array) $raw;
    }

    /**
     * Extract a canonical CPD summary (associative) when possible.
     */
    private function extractCpdSummary(mixed $raw): array
    {
        if (empty($raw)) return [];

        if (is_array($raw) && array_key_exists('current_points', $raw)) {
            return $raw;
        }

        // If the API returned an array-wrapped summary (single element with
        // both current_points and cpd_requirement) treat that as the canonical summary.
        if (is_array($raw) && isset($raw[0]) && is_array($raw[0]) && array_key_exists('current_points', $raw[0]) && array_key_exists('cpd_requirement', $raw[0])) {
            return $raw[0];
        }

        return [];
    }
}