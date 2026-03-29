<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>فاتورة #{{ $invoice->serial_number ?? $invoice->id }}</title>
    <style>
        @font-face { font-family: 'DejaVu Sans'; src: url('{{ storage_path('fonts/DejaVuSans.ttf') }}') format("truetype"); }
        body { font-family: 'DejaVu Sans', sans-serif !important; direction: rtl; text-align: right; font-size: 12px; color: #333; }
        .header { width: 100%; border-bottom: 2px solid #ddd; margin-bottom: 20px; padding-bottom: 10px; }
        .header table { width: 100%; }
        .info-box { width: 100%; margin-bottom: 20px; border-collapse: separate; border-spacing: 5px; }
        .info-box td { width: 33%; padding: 8px; background-color: #f9f9f9; border: 1px solid #eee; vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background-color: #eee; border: 1px solid #ccc; padding: 8px; text-align: center; }
        .items-table td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        .total-table { width: 40%; margin-right: auto; border-collapse: collapse; }
        .total-table td { padding: 6px; border-bottom: 1px solid #eee; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
<div class="header">
    <table>
        <tr>
            <td style="font-weight: bold;">
                <img src="{{public_path('assets/admin/images/slogan.jpg')}}"  style="height:80px">

            </td>
            <td width="40%" style="text-align: left;">
                <h1 style="margin:0; font-size: 18px;">فاتورة مبيعات</h1>
                <p style="margin:2px 0;"> <strong>#{{ $invoice->serial_number ?? $invoice->id }}: رقم</strong></p>
                <p style="margin:2px 0;">التاريخ: {{ $invoice->invoice_date }}</p>
                <p style="margin:2px 0; font-weight: bold; color: {{ $invoice->line == 1 ? '#17a2b8' : '#ffc107' }}">Line {{ $invoice->line }}</p>
            </td>
        </tr>
    </table>
</div>

<table class="info-box text-start">
    <tr>
        <td>
            <strong>العميل (الصيدلية):</strong><br>
            {{ $invoice->pharmacist->name }}<br>
            مدينة : {{ $invoice->pharmacist->center->name ?? '-' }}
        </td>
        <td>
            <strong>الأطباء الموجهين:</strong><br>
            @if($invoice->doctors && $invoice->doctors->count() > 0)
                @foreach($invoice->doctors as $doctor)
                    د. {{ $doctor->name }} <br>
                    <span style="font-size: 10px; color: #666;">{{ $doctor->speciality ?? '' }}</span><br>
                @endforeach
            @else
                ---
            @endif
        </td>
        <td>
            <strong>فريق التوزيع:</strong><br>
            بيع: {{ $invoice->representative->name ?? '-' }}<br>
            دعاية: {{ $invoice->medicalRepresentative->name ?? '-' }}
        </td>
    </tr>
</table>

<table class="items-table">
    <thead>
    <tr>
        <th width="5%">#</th>
        <th width="45%" style="text-align: right;">الصنف</th>
        <th width="10%">الكمية</th>
        <th width="15%">السعر</th>
        <th width="10%">الخصم</th>
        <th width="15%">الإجمالي</th>
    </tr>
    </thead>
    <tbody>
    @foreach($invoice->details as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td style="text-align: right;">{{ $item->drug->name }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->unit_price, 2) }}</td>
            <td>{{ $item->pharmacist_discount_percentage }}%</td>
            <td>{{ number_format($item->row_total, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table class="total-table e">
    <tr>
        <td style="text-align: left;">{{ number_format($invoice->total_amount, 2) }}</td>
        <td>الإجمالي:</td>

    </tr>
    <tr>
        <td style="text-align: left; color: red;">- {{ number_format($invoice->total_discount, 2) }}</td>
        <td>الخصم:</td>

    </tr>
    <tr style="background-color: #f0f0f0; font-weight: bold;">
        <td style="text-align: left;">{{ number_format($invoice->final_total, 2) }} ج.م</td>
        <td>الصافي المستحق:</td>

    </tr>
    <tr>
        <td style="text-align: left; color: green; font-weight: bold;">{{ number_format($invoice->paid_amount, 2) }}</td>
        <td style="color: green;">المدفوع:</td>

    </tr>
    @if($invoice->remaining_amount > 0)
        <tr>
            <td style="text-align: left; color: red; font-weight: bold;">{{ number_format($invoice->remaining_amount, 2) }}</td>
            <td style="color: red;">المتبقي (آجل):</td>

        </tr>
    @endif
</table>

<div class="footer">
    تم طباعة الفاتورة بتاريخ {{ date('Y-m-d H:i') }}
</div>
</body>
</html>
