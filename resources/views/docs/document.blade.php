<html>
    <head>
    <style>
        .top_rw {
            background-color:#f4f4f4; 
        }
        button {
            padding: 5px 10px;
            font-size: 14px;
        }
        .logo {
            max-width: 200px;
            max-height: 100px;
        }
        td {
            position: relative;
        }
        .stamp {
            max-width: 240px;
            max-height: 160px;
            position: absolute;
            top: -100px;
            left: -160px;
        }
        .invoice-box {
            width: 100%;
            margin: auto;
            padding:10px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 14px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-bottom: solid 1px #ccc;
        }
        .invoice-box table td {
            padding: 5px;
            vertical-align:middle;
        }
        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }
        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }
        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }
        .invoice-box table tr.heading th {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            font-size:12px;
        }
        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.item td{
            border-bottom: 1px solid #eee;
        }
        .invoice-box table tr.item.last td {
            border-bottom: none;
        }
        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
        /** RTL **/
        .rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }
        .rtl table {
            text-align: right;
        }
        .rtl table tr td:nth-child(2) {
            text-align: left;
        }
    </style>
    </head>
    <body>
        <div class='invoice-box'>
            <table cellpadding='0' cellspacing='0'>
                <thead>
                    <tr>
                        <th colspan='2'></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @include('docs._header', ['title' => $title, 'patientSession' => $patientSession, 'hospital' => $hospital])
                    {!! $content !!}
                    @include('docs._footer', ['patientSession' => $patientSession, 'hospital' => $hospital])
                </tbody>
            </table>
        </div>
    </body>
</html>