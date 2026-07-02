<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td class="content-cell" align="center" style="padding: 32px;">
<p style="color: #969696; font-size: 12px; margin-bottom: 8px;">
© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
</p>
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
