@extends('layouts.app', ['title' => $title])

@section('content')
@php
    $cpdStats = $cpdStats ?? [];
    $cpdHistory = $cpdHistory ?? [];
    $cpdEvents = $cpdEvents ?? [];
    $currentYear = $currentYear ?? date('Y');
    $cpdItems    = data_get($bioProfile, 'cpd', []);
    
    // Calculate total points and activities count from CPD history for current year
    $currentYearTotal = 0;
    $currentYearActivitiesCount = 0;
    
    // Initialize monthly progress data
    $monthlyProgress = array_fill(1, 12, 0);
    $monthlyActivities = array_fill(1, 12, 0);
    
    if (!empty($cpdHistory)) {
        foreach ($cpdHistory as $activity) {
            $activityDate = \Carbon\Carbon::parse(data_get($activity, 'activity_date', now()));
            $activityYear = $activityDate->year;
            $activityMonth = $activityDate->month;
            $status = strtolower(data_get($activity, 'approval_status', ''));
            
            if ($activityYear == $currentYear && $status === 'approved') {
                $points = floatval(data_get($activity, 'points_earned', 0));
                $currentYearTotal += $points;
                $currentYearActivitiesCount++;
                
                $monthlyProgress[$activityMonth] += $points;
                $monthlyActivities[$activityMonth]++;
            }
        }
    }
    
    // Calculate cumulative progress for trend line
    $cumulativeProgress = [];
    $runningTotal = 0;
    $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    for ($month = 1; $month <= 12; $month++) {
        $runningTotal += $monthlyProgress[$month];
        $cumulativeProgress[$month] = $runningTotal;
    }
    
    // Calculate progress metrics
    $requiredPoints = $cpdStats['required_points'] ?? 20;
    $progressPercentage = $requiredPoints > 0 ? min(($currentYearTotal / $requiredPoints) * 100, 100) : 0;
    $pointsRemaining = max($requiredPoints - $currentYearTotal, 0);
    
    // Calculate trend data
    $currentMonth = date('n');
    $monthsPassed = min($currentMonth, 12);
    $expectedProgressPerMonth = $requiredPoints / 12;
    $expectedProgressToDate = $expectedProgressPerMonth * $monthsPassed;
    $aheadOfSchedule = $currentYearTotal > $expectedProgressToDate;
    
    // Calculate status color
    $statusColor = 'blue';
    $statusBg = 'bg-blue-50';
    $statusText = 'text-blue-700';
    $statusBorder = 'border-blue-200';
    
    if (($cpdStats['status'] ?? '') === 'Completed') {
        $statusColor = 'green';
        $statusBg = 'bg-green-50';
        $statusText = 'text-green-700';
        $statusBorder = 'border-green-200';
    } elseif (($cpdStats['status'] ?? '') === 'On Track') {
        $statusColor = 'blue';
        $statusBg = 'bg-blue-50';
        $statusText = 'text-blue-700';
        $statusBorder = 'border-blue-200';
    } elseif (($cpdStats['status'] ?? '') === 'In Progress') {
        $statusColor = 'amber';
        $statusBg = 'bg-amber-50';
        $statusText = 'text-amber-700';
        $statusBorder = 'border-amber-200';
    } elseif (($cpdStats['status'] ?? '') === 'Needs Focus') {
        $statusColor = 'red';
        $statusBg = 'bg-red-50';
        $statusText = 'text-red-700';
        $statusBorder = 'border-red-200';
    }
        // Precompute CSS style strings and monthly heights to keep templates clean
        $progressBarWidth = 'width: '.min($progressPercentage ?? 0, 100).'%';
        $progressBarWidthExact = 'width: '.($progressPercentage ?? 0).'%';

        $monthlyYourHeights = [];
        $monthlyExpectedHeights = [];
        $monthlyYourStyles = [];
        $monthlyExpectedStyles = [];
        $maxHeight = 120;
        for ($m = 1; $m <= 12; $m++) {
            $monthProgress = $cumulativeProgress[$m] ?? 0;
            $expectedForMonth = $expectedProgressPerMonth * $m;
            $monthlyYourHeights[$m] = $requiredPoints > 0 ? ($monthProgress / $requiredPoints) * $maxHeight : 0;
            $monthlyExpectedHeights[$m] = $requiredPoints > 0 ? ($expectedForMonth / $requiredPoints) * $maxHeight : 0;
            // Precompute style strings to avoid inline concatenation in partials (keeps CSS language server happy)
            $monthlyYourStyles[$m] = 'height: '.($monthlyYourHeights[$m]).'px; background: linear-gradient(to top, #3b82f6, #6366f1);';
            $monthlyExpectedStyles[$m] = 'height: '.($monthlyExpectedHeights[$m]).'px;';
        }
@endphp

<div class="rounded-2xl border border-slate-200 bg-white p-5 lg:p-6" 
     x-data="{
         claimTokenModalOpen: false,
         eventToken: '',
         isSubmitting: false,
         responseMessage: '',
         responseType: '',
         async claimToken() {
             if (!this.eventToken.trim()) {
                 this.responseType = 'error';
                 this.responseMessage = 'Please enter an event token.';
                 return;
             }
             
             this.isSubmitting = true;
             this.responseMessage = '';
             
             try {
                 const formData = new FormData();
                 formData.append('event_token', this.eventToken);
                 formData.append('_token', '{{ csrf_token() }}');
                 
                 const response = await fetch('{{ route('practitioner.cpd.claim-token') }}', {
                     method: 'POST',
                     headers: {
                         'X-Requested-With': 'XMLHttpRequest',
                         'Accept': 'application/json'
                     },
                     body: formData
                 });
                 
                 const data = await response.json();
                 
                 if (response.ok && data.success) {
                     this.responseType = 'success';
                     this.responseMessage = data.message || 'Token claimed successfully!';
                     this.eventToken = '';
                     
                     setTimeout(() => {
                         this.claimTokenModalOpen = false;
                         this.responseMessage = '';
                         window.location.reload();
                     }, 2000);
                 } else {
                     this.responseType = 'error';
                     this.responseMessage = data.message || 'Failed to claim token. Please try again.';
                 }
             } catch (error) {
                 this.responseType = 'error';
                 this.responseMessage = 'An error occurred. Please try again.';
                 console.error('Error:', error);
             } finally {
                 this.isSubmitting = false;
             }
         },
         closeModal() {
             this.claimTokenModalOpen = false;
             this.eventToken = '';
             this.responseMessage = '';
             this.responseType = '';
         }
     }">
    
    <div class="space-y-6">
        @include('practitioner.cpd.partials.cpd.header')
        @include('practitioner.cpd.partials.cpd.stats')
        @include('practitioner.cpd.partials.cpd.progress')
        @include('practitioner.cpd.partials.cpd.table')
    </div>
    </div>

    @include('practitioner.cpd.partials.cpd.modal')
@endsection