@php
    $pixelId = config('services.facebook_pixel.id') 
        ?? ($config['facebook_pixel_id'] ?? null)
        ?? ($paymentConfig['facebook_pixel_id'] ?? null)
        ?? (\App\Http\Controllers\PricingController::getPageConfig()['facebook_pixel_id'] ?? null)
        ?? env('FACEBOOK_PIXEL_ID')
        ?? env('FB_PIXEL_ID');
@endphp

@if(!empty($pixelId))
<!-- Meta Pixel Base Code (Subeditor24 Marketing & Analytics) -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');

@if(Auth::check())
// Advanced Matching for Authenticated Users (High Accuracy Tracking)
fbq('init', '{{ $pixelId }}', {
    em: '{{ strtolower(trim(Auth::user()->email)) }}',
    @if(Auth::user()->phone)
    ph: '{{ preg_replace('/[^\d]/', '', Auth::user()->phone) }}'
    @endif
});
@else
fbq('init', '{{ $pixelId }}');
@endif

// 1. All Visits & Page Views
fbq('track', 'PageView');

// 2. New User Registration & Free Trial Signup Event
@if(session('meta_registration_event'))
fbq('track', 'CompleteRegistration', {
    content_name: '7 Days Free Trial Signup',
    status: 'trial'
});
fbq('track', 'Lead', {
    content_name: 'New SaaS User Signup',
    value: 0.00,
    currency: 'BDT'
});
@endif

// 3. User Login Event
@if(session('meta_login_event'))
fbq('trackCustom', 'UserLogin', {
    user_role: '{{ Auth::user()->role ?? "client" }}'
});
@endif
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"
/></noscript>
<!-- End Meta Pixel Code -->
@endif
