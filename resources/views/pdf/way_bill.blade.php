<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <title>Way Bill</title>
    <style type="text/css">
        .tg {
            border-collapse: collapse;
            border-spacing: 0;
        }

        .tg td {
            border-color: black;
            border-style: solid;
            border-width: 1px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            overflow: hidden;
            padding: 10px 5px;
            word-break: normal;
        }

        .tg th {
            border-color: black;
            border-style: solid;
            border-width: 1px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: normal;
            overflow: hidden;
            padding: 10px 5px;
            word-break: normal;
        }

        .tg .tg-0lax {
            text-align: left;
            vertical-align: top;
        }

        /* Prevent page break inside table */
        @media print {
            table, tr, td {
                page-break-inside: avoid;
            }

            .page-break {
                page-break-before: always;
            }
        }

        .page-container {
            page-break-after: always;
        }

        .page-container:last-child {
            page-break-after: auto;
        }
    </style>
</head>

<body>
    @foreach ($chunks as $chunk)
        <div class="page-container">
            @foreach ($chunk as $item)
                <div class="col-12 mb-3 pb-6">
                    <table class="tg" style="width:700px; height:230px;">
                        <tbody>
                            <tr>
                                <td class="tg-0lax" colspan="8" style="font-size:12px; font-weight:bold;">
                                    <center>Seller: {{ $item['sellerName'] }}</center>
                                </td>
                            </tr>
                            <tr>
                                <td class="tg-0lax " colspan="8" style="font-size:16px; font-weight:bold;">
                                    Customer Details :<br>
                                    {{ $item['customerName'] }},
                                    {{ $item['customerAddress'] }} - {{ $item['customerCity'] }} ,<br>
                                    {{ $item['customerMobile'] }} / {{ $item['customerMobile2'] }}
                                </td>
                            </tr>
                            <tr>
                                <td class="tg-0lax" colspan="6" style="font-size:12px; font-weight:bold;">
                                    @foreach($item['productName'] as $index => $pro)
                                        {{ $pro }}
                                        @if ($index < count($item['productName']) - 1)
                                            /
                                        @endif
                                    @endforeach
                                </td>
                                <td class="tg-0lax" style="font-size:12px; font-weight:bold;">QTY: {{ $item['quantity'] }}</td>
                                <td class="tg-0lax" style="font-size:12px; font-weight:bold;">Rs.{{ $item['totalAmount'] }}</td>
                            </tr>
                            <tr>
                                <td class="tg-0lax text-center" colspan="8" style="font-size:16px; font-weight:bold;">
                                    <img src="data:image/png;base64, {!! $item['barcode'] !!}" alt="Barcode">
                                    <p class="text-center">{{ $item['wayBillNumber'] }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endforeach
</body>

</html>
