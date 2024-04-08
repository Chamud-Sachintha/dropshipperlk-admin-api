<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <title>Document</title>
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
            vertical-align: top
        }
    </style>
</head>

<body>
    <div class="row">
        @foreach ($data as $item)
            <div class="col-6 mb-3">
                <table class="tg">
                    <thead>
                        <tr>
                            <th class="tg-0lax" colspan="8" style="font-size:12px; font-weight:bold;"><center> IK Online Store </center></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="tg-0lax" colspan="8" style="font-size:12px; font-weight:bold;" ><center>Seller: {{ $item['sellerName'] }}</center></td>
                            
                        </tr>
                        <tr>
                            <td class="tg-0lax " colspan="8" style="font-size:18px; font-weight:bold;">Customer Details :<br>
                                 {{ $item['customerName'] }},
                                {{ $item['customerAddress'] }} ,
                                {{ $item['customerMobile'] }}</td>
                        </tr>
                       
                        <tr>
                            <td class="tg-0lax"colspan="6" style="font-size:12px; font-weight:bold;">{{ $item['productName'] }}</td>
                            <td class="tg-0lax" style="font-size:12px; font-weight:bold;">QTY: {{ $item['quantity'] }}</td>
                           
                            <td class="tg-0lax"  style="font-size:12px; font-weight:bold;">Rs.{{ $item['totalAmount'] }}</td>
                        </tr>
                       
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
</body>

</html>