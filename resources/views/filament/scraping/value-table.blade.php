@php /** @var array<string, string> $values */ @endphp

@if(empty($values))
    <p style="font-size:0.875rem;color:#6b7280;">Nic dalšího extrakce nenašla.</p>
@else
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
            <tbody>
                @foreach($values as $key => $value)
                    <tr style="border-bottom:1px solid #e5e7eb;">
                        <th style="text-align:left;padding:6px 12px 6px 0;font-weight:600;white-space:nowrap;vertical-align:top;color:#374151;">
                            {{ $key }}
                        </th>
                        <td style="padding:6px 0;color:#4b5563;word-break:break-word;">
                            {{ $value !== '' ? $value : '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
