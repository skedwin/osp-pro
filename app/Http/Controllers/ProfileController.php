<?php

namespace App\Http\Controllers;

use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Display the practitioner's profile
     */
    public function index(): \Illuminate\View\View
    {
        try {
            $user = Auth::user();
            
            // Get formatted profile data from service
            $bioProfile = $this->profileService->getFormattedProfile($user);
            
            return view('dashboard.profile', [
                'bioProfile' => $bioProfile,
                'title' => 'Profile'
            ]);
            
        } catch (\Exception $e) {
            Log::error('ProfileController@index - Error fetching profile: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e
            ]);
            
            // Fallback with empty structure
            return view('dashboard.profile', [
                'bioProfile' => $this->getEmptyProfileStructure(),
                'title' => 'Profile',
                'error' => 'Unable to load profile data at this time.'
            ]);
        }
    }

    /**
     * Update the practitioner's profile
     */
    public function update(Request $request): \Illuminate\Http\RedirectResponse|JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Validate request based on section
            $validator = $this->validateProfileUpdate($request);
            
            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                
                return redirect()->route('dashboard.profile')
                    ->withErrors($validator)
                    ->withInput();
            }

            // Update profile via service
            $updated = $this->profileService->updateProfile($user, $validator->validated());
            
            if ($updated) {
                $successMessage = 'Profile updated successfully!';
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $successMessage,
                        'data' => $updated
                    ]);
                }
                
                return redirect()->route('dashboard.profile')
                    ->with('success', $successMessage);
            } else {
                throw new \Exception('Profile update failed');
            }
            
        } catch (ValidationException $e) {
            Log::warning('ProfileController@update - Validation error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'input' => $request->except(['password', 'password_confirmation'])
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->route('dashboard.profile')
                ->withErrors($e->errors())
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('ProfileController@update - Error updating profile: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'input' => $request->except(['password', 'password_confirmation']),
                'exception' => $e
            ]);
            
            $errorMessage = 'An error occurred while updating profile. Please try again.';
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            
            return redirect()->route('dashboard.profile')
                ->with('error', $errorMessage);
        }
    }

    /**
     * Validate profile update request based on section
     */
    protected function validateProfileUpdate(Request $request): \Illuminate\Validation\Validator
    {
        $section = $request->get('section', 'general');
        
        $rules = [
            'email' => 'required|email|max:255',
            'mobile_no' => 'required|string|max:20',
        ];

        // Add section-specific rules
        switch ($section) {
            case 'personal_info':
                $rules = array_merge($rules, [
                    'first_name' => 'required|string|max:100',
                    'last_name' => 'required|string|max:100',
                    'date_of_birth' => 'nullable|date|before:today',
                    'gender' => 'nullable|in:M,F',
                ]);
                break;
                
            case 'address':
                $rules = array_merge($rules, [
                    'address' => 'nullable|string|max:500',
                    'passport_number' => 'nullable|string|max:50',
                    'birth_cert_no' => 'nullable|string|max:50',
                ]);
                break;
                
            case 'general':
            default:
                $rules = array_merge($rules, [
                    'address' => 'nullable|string|max:500',
                    'profile_pic' => 'nullable|url|max:1000',
                ]);
                break;
        }

        $messages = [
            'date_of_birth.before' => 'Date of birth must be a date in the past.',
            'email.required' => 'Email address is required.',
            'mobile_no.required' => 'Mobile number is required.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    /**
     * Get empty profile structure for fallback
     */
    protected function getEmptyProfileStructure(): array
    {
        return [
            'profile' => [],
            'education' => [],
            'registration' => [],
            'license' => [],
            'cpd' => [],
            'avatar' => null,
        ];
    }

    /**
     * Get profile data via new API endpoint
     */
    protected function fetchProfileFromAPI($user): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $user->api_token,
                'Accept' => 'application/json',
                'User-Agent' => 'NCNMIS-Portal/1.0',
            ])
            ->timeout(30)
            ->retry(3, 100)
            ->get('https://api.nckenya.com/bio', [
                'id_no' => $user->id_no,
                'index_id' => $user->index_id,
                'index_no' => $user->index_no,
                'licence_no' => $user->licence_no,
                'reg_no' => $user->reg_no,
                'cadre' => $user->cadre,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('ProfileController - New API response not successful', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            
        } catch (\Exception $e) {
            Log::error('ProfileController - New API call failed: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Update profile via API (for backward compatibility)
     */
    protected function updateProfileViaAPI($user, array $data): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $user->api_token,
                'Accept' => 'application/json',
                'User-Agent' => 'NCNMIS-Portal/1.0',
            ])
            ->timeout(30)
            ->post(config('services.api.url') . '/practitioner/profile/update', $data);

            return $response->successful();
            
        } catch (\Exception $e) {
            Log::error('ProfileController - Profile update API call failed: ' . $e->getMessage());
            return false;
        }
    }
}