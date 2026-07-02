@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="https://laravel.com/img/notification-logo.png" class="logo" alt="Laravel Logo">
@else
<span style="font-size: 24px; font-weight: 700; color: #ffffff; text-decoration: none;">{{ $slot }}</span>
@endif
</a>
</td>
</tr>
